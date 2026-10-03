/**
 * Shared client-side validation for every public form marked [data-validate]
 * (Get a Quote modal, Careers modal, Contact, Load Calculator).
 *
 * - [data-phone] inputs get a country-code picker (Pakistan +92 by default).
 *   The visible input only holds the national number (digits only, length
 *   capped per country); a hidden input named "phone" carries the full
 *   "+92 3001234567" value, so FormData / form.phone.value keep working.
 * - [data-email] inputs are checked against a stricter pattern than the
 *   browser's type=email (which accepts "a@b").
 * - Any other [required] field must be non-empty.
 *
 * Runs in the capture phase on document, so an invalid form never reaches
 * the per-form submit handlers in apply-modal.js / careers-modal.js / etc.
 */
(function () {
  const i18n = window.WP_I18N || {};
  const msg = (key, fallback) => i18n[key] || fallback;

  // min/max = digits in the national number (no trunk "0", no country code).
  const COUNTRIES = [
    { iso: 'PK', name: 'Pakistan', dial: '92', min: 10, max: 10, mobilePrefix: '3' },
    { iso: 'AE', name: 'United Arab Emirates', dial: '971', min: 8, max: 9 },
    { iso: 'SA', name: 'Saudi Arabia', dial: '966', min: 8, max: 9 },
    { iso: 'QA', name: 'Qatar', dial: '974', min: 8, max: 8 },
    { iso: 'OM', name: 'Oman', dial: '968', min: 8, max: 8 },
    { iso: 'KW', name: 'Kuwait', dial: '965', min: 8, max: 8 },
    { iso: 'BH', name: 'Bahrain', dial: '973', min: 8, max: 8 },
    { iso: 'GB', name: 'United Kingdom', dial: '44', min: 10, max: 10 },
    { iso: 'US', name: 'United States', dial: '1', min: 10, max: 10 },
    { iso: 'CA', name: 'Canada', dial: '1', min: 10, max: 10 },
    { iso: 'AU', name: 'Australia', dial: '61', min: 9, max: 9 },
    { iso: 'DE', name: 'Germany', dial: '49', min: 10, max: 11 },
    { iso: 'IT', name: 'Italy', dial: '39', min: 9, max: 10 },
    { iso: 'FR', name: 'France', dial: '33', min: 9, max: 9 },
    { iso: 'TR', name: 'Türkiye', dial: '90', min: 10, max: 10 },
    { iso: 'MY', name: 'Malaysia', dial: '60', min: 9, max: 10 },
    { iso: 'CN', name: 'China', dial: '86', min: 11, max: 11 },
    { iso: 'AF', name: 'Afghanistan', dial: '93', min: 9, max: 9 },
  ];
  const byIso = (iso) => COUNTRIES.find((c) => c.iso === iso) || COUNTRIES[0];

  const EMAIL_RE = /^[A-Za-z0-9._%+-]+@[A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?(?:\.[A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?)*\.[A-Za-z]{2,}$/;

  function isValidEmail(v) {
    if (!v || v.length > 254 || !EMAIL_RE.test(v)) return false;
    const local = v.split('@')[0];
    return local.length <= 64 && !local.startsWith('.') && !local.endsWith('.') && !v.includes('..');
  }

  // Urdu/Arabic keyboards type Eastern Arabic digits — map them to 0-9.
  function toAsciiDigits(s) {
    return s
      .replace(/[۰-۹]/g, (d) => String(d.charCodeAt(0) - 0x06F0))
      .replace(/[٠-٩]/g, (d) => String(d.charCodeAt(0) - 0x0660));
  }

  // Digits only, pasted country code and trunk "0" removed, capped to max length.
  function sanitizeNational(raw, country) {
    let d = toAsciiDigits(raw).replace(/\D/g, '');
    if (d.length > country.max && d.startsWith(country.dial)) d = d.slice(country.dial.length);
    d = d.replace(/^0+/, '');
    return d.slice(0, country.max);
  }

  function phoneError(digits, country, required) {
    if (!digits) return required ? msg('phoneRequired', 'Please enter your phone number.') : '';
    if (country.mobilePrefix) {
      if (digits.length !== country.max || !digits.startsWith(country.mobilePrefix)) {
        return msg('phoneInvalidPk', 'Enter a valid Pakistani mobile number: 10 digits starting with 3 (e.g. 300 1234567).');
      }
      return '';
    }
    if (digits.length < country.min || digits.length > country.max) {
      return msg('phoneInvalid', 'Enter a valid phone number ({min}–{max} digits).')
        .replace('{min}', country.min).replace('{max}', country.max);
    }
    return '';
  }

  // ---- Inline error display ----
  function setError(input, text) {
    const field = input.closest('.field') || input.parentElement;
    let box = field.querySelector('.field-error');
    if (!text) {
      field.classList.remove('has-error');
      if (box) box.remove();
      input.removeAttribute('aria-invalid');
      return;
    }
    if (!box) {
      box = document.createElement('div');
      box.className = 'field-error';
      box.setAttribute('role', 'alert');
      field.appendChild(box);
    }
    box.textContent = text;
    field.classList.add('has-error');
    input.setAttribute('aria-invalid', 'true');
  }

  // ---- Phone picker ----
  function enhancePhone(input) {
    const required = input.required;
    const pkPlaceholder = input.placeholder || '3xx xxxxxxx';

    const hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.name = input.name;
    input.removeAttribute('name');
    input.required = false; // validated by us; a nameless required input would confuse native checks
    input.dataset.required = required ? '1' : '';

    const group = document.createElement('div');
    group.className = 'phone-group';

    const cc = document.createElement('div');
    cc.className = 'phone-cc';
    const ccLabel = document.createElement('span');
    ccLabel.className = 'phone-cc-label';
    const select = document.createElement('select');
    select.setAttribute('aria-label', msg('countryCode', 'Country code'));
    COUNTRIES.forEach((c) => {
      const o = document.createElement('option');
      o.value = c.iso;
      o.textContent = c.name + ' (+' + c.dial + ')';
      if (c.iso === 'PK') o.defaultSelected = true;
      select.appendChild(o);
    });
    cc.append(ccLabel, select, Object.assign(document.createElement('i'), { className: 'bi bi-chevron-down' }));

    input.parentNode.insertBefore(group, input);
    group.append(cc, input);
    group.after(hidden);

    function sync() {
      const country = byIso(select.value);
      ccLabel.textContent = '+' + country.dial;
      input.placeholder = country.iso === 'PK' ? pkPlaceholder : '';
      const digits = sanitizeNational(input.value, country);
      if (input.value !== digits) input.value = digits;
      hidden.value = digits ? '+' + country.dial + ' ' + digits : '';
    }

    input.addEventListener('input', () => { sync(); if (input.closest('.has-error')) validatePhone(input); });
    input.addEventListener('blur', () => { if (input.value) validatePhone(input); });
    select.addEventListener('change', () => { sync(); if (input.value) validatePhone(input); input.focus(); });

    input._phone = { select, hidden, sync };
    sync();
  }

  function validatePhone(input) {
    const p = input._phone;
    p.sync();
    const err = phoneError(input.value, byIso(p.select.value), input.dataset.required === '1');
    setError(input, err);
    return !err;
  }

  function validateEmail(input) {
    const v = input.value.trim();
    input.value = v;
    let err = '';
    if (!v) err = input.required ? msg('emailRequired', 'Please enter your email address.') : '';
    else if (!isValidEmail(v)) err = msg('emailInvalid', 'Please enter a valid email address (e.g. name@example.com).');
    setError(input, err);
    return !err;
  }

  function validateRequired(el) {
    const empty = el.type === 'file' ? !el.files.length : !String(el.value).trim();
    const err = empty ? msg('fieldRequired', 'This field is required.') : '';
    setError(el, err);
    return !err;
  }

  function validateForm(form) {
    let firstBad = null;
    form.querySelectorAll('input, select, textarea').forEach((el) => {
      let ok = true;
      if (el._phone) ok = validatePhone(el);
      else if (el.hasAttribute('data-email')) ok = validateEmail(el);
      else if (el.required && !el.closest('.phone-cc')) ok = validateRequired(el);
      if (!ok && !firstBad) firstBad = el;
    });
    if (firstBad) {
      firstBad.focus({ preventScroll: true });
      firstBad.scrollIntoView({ block: 'center', behavior: 'smooth' });
    }
    return !firstBad;
  }

  const forms = document.querySelectorAll('form[data-validate]');
  forms.forEach((form) => {
    form.querySelectorAll('input[data-phone]').forEach(enhancePhone);

    form.querySelectorAll('[data-email]').forEach((el) => {
      el.addEventListener('blur', () => { if (el.value) validateEmail(el); });
      el.addEventListener('input', () => { if (el.closest('.has-error')) validateEmail(el); });
    });
    form.querySelectorAll('[required]').forEach((el) => {
      if (el.hasAttribute('data-email')) return;
      el.addEventListener(el.tagName === 'SELECT' || el.type === 'file' ? 'change' : 'input', () => {
        if (el.closest('.has-error')) validateRequired(el);
      });
    });

    // form.reset() (called when a modal opens) restores defaults; clear errors and re-sync phone.
    form.addEventListener('reset', () => {
      setTimeout(() => {
        form.querySelectorAll('.field-error').forEach((b) => b.remove());
        form.querySelectorAll('.has-error').forEach((f) => f.classList.remove('has-error'));
        form.querySelectorAll('[aria-invalid]').forEach((el) => el.removeAttribute('aria-invalid'));
        form.querySelectorAll('input[data-phone]').forEach((el) => el._phone && el._phone.sync());
      });
    });
  });

  document.addEventListener('submit', (e) => {
    const form = e.target;
    if (!form.matches || !form.matches('form[data-validate]')) return;
    if (!validateForm(form)) {
      e.preventDefault();
      e.stopImmediatePropagation();
    }
  }, true);
})();
