/**
 * Settings page — tab switching, per-section dirty-tracked save forms,
 * logo upload, SMTP test, promo video toggle/preview, password change.
 * Port of AdminSettings.jsx.
 */
(function () {
  function esc(s) {
    const d = document.createElement('div');
    d.textContent = s == null ? '' : String(s);
    return d.innerHTML;
  }

  // ---- Tabs ----
  document.querySelectorAll('.adm-tab-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.adm-tab-btn').forEach((b) => b.classList.remove('active'));
      btn.classList.add('active');
      const idx = btn.dataset.tab;
      document.querySelectorAll('.adm-settings-tab').forEach((panel) => {
        panel.hidden = panel.dataset.tabPanel !== idx;
      });
    });
  });

  function showAlert(type, msg) {
    const box = document.getElementById('settingsAlert');
    box.hidden = false;
    box.className = 'adm-alert ' + type;
    box.style.marginBottom = '20px';
    box.innerHTML = '<i class="bi ' + (type === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle') + '"></i>' + esc(msg);
    setTimeout(() => { box.hidden = true; }, 4500);
  }

  // ---- Per-section save forms, with dirty tracking ----
  document.querySelectorAll('.settings-section-form').forEach((form) => {
    const keys = form.dataset.keys.split(',');
    const submitBtn = form.querySelector('[type="submit"]');
    const initial = {};
    keys.forEach((k) => { initial[k] = fieldValue(form, k); });

    function fieldValue(f, key) {
      const el = f.elements[key];
      if (!el) return '';
      if (el.type === 'checkbox') return el.checked ? 'true' : 'false';
      return el.value;
    }

    function checkDirty() {
      const dirty = keys.some((k) => fieldValue(form, k) !== initial[k]);
      submitBtn.disabled = !dirty;
    }
    form.addEventListener('input', checkDirty);
    form.addEventListener('change', checkDirty);

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const payload = {};
      keys.forEach((k) => { payload[k] = fieldValue(form, k); });

      const orig = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="adm-spinner" style="border-color:rgba(255,255,255,.35);border-top-color:#fff;width:14px;height:14px"></span> Saving…';

      try {
        const res = await fetch('/admin/api/settings-save.php', {
          method: 'POST', credentials: 'include',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        });
        if (!res.ok) throw new Error('Save failed');
        keys.forEach((k) => { initial[k] = fieldValue(form, k); });
        showAlert('success', 'Settings saved!');
      } catch (err) {
        showAlert('error', err.message);
      }
      submitBtn.innerHTML = orig;
      checkDirty();
    });
  });

  // ---- Logo upload ----
  const logoFileInput = document.getElementById('logoFileInput');
  const logoUploadBtn = document.getElementById('logoUploadBtn');
  const logoPreview = document.getElementById('logoPreview');
  const logoStatusText = document.getElementById('logoStatusText');
  if (logoFileInput) {
    logoFileInput.addEventListener('change', () => {
      const file = logoFileInput.files[0];
      logoUploadBtn.disabled = !file;
      if (file) {
        logoPreview.src = URL.createObjectURL(file);
        logoStatusText.innerHTML = '<span style="color:var(--green-700);font-weight:600"><i class="bi bi-check-circle"></i> Ready to upload: ' + esc(file.name) + '</span>';
      }
    });
    logoUploadBtn.addEventListener('click', async () => {
      const file = logoFileInput.files[0];
      if (!file) return;
      const orig = logoUploadBtn.innerHTML;
      logoUploadBtn.disabled = true;
      logoUploadBtn.innerHTML = '<span class="adm-spinner" style="border-color:rgba(255,255,255,.35);border-top-color:#fff;width:14px;height:14px"></span> Uploading…';
      try {
        const fd = new FormData();
        fd.append('logo', file);
        const res = await fetch('/admin/api/logo-upload.php', { method: 'POST', credentials: 'include', body: fd });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || 'Upload failed');
        logoPreview.src = data.url;
        logoStatusText.textContent = 'Current logo';
        logoFileInput.value = '';
        showAlert('success', 'Logo uploaded! Visitors will see the new logo on their next page load.');
      } catch (err) {
        showAlert('error', err.message);
      }
      logoUploadBtn.innerHTML = orig;
      logoUploadBtn.disabled = true;
    });
  }

  // ---- SMTP password eye toggle ----
  const smtpPassInput = document.getElementById('smtpPassInput');
  const smtpPassToggle = document.getElementById('smtpPassToggle');
  if (smtpPassToggle) {
    smtpPassToggle.addEventListener('click', () => {
      const show = smtpPassInput.type === 'password';
      smtpPassInput.type = show ? 'text' : 'password';
      smtpPassToggle.innerHTML = '<i class="bi ' + (show ? 'bi-eye-slash' : 'bi-eye') + '"></i>';
    });
  }

  // ---- SMTP test ----
  const smtpTestBtn = document.getElementById('smtpTestBtn');
  if (smtpTestBtn) {
    smtpTestBtn.addEventListener('click', async () => {
      const orig = smtpTestBtn.innerHTML;
      smtpTestBtn.disabled = true;
      smtpTestBtn.innerHTML = '<span class="adm-spinner" style="border-color:var(--green-200);border-top-color:var(--green-600)"></span> Testing…';
      try {
        const res = await fetch('/admin/api/smtp-test.php', { method: 'POST', credentials: 'include' });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error);
        showAlert('success', data.message);
      } catch (err) {
        showAlert('error', err.message);
      }
      smtpTestBtn.disabled = false;
      smtpTestBtn.innerHTML = orig;
    });
  }

  // ---- Promo video toggle + embeddable test link ----
  const promoToggle = document.getElementById('promoEnabledToggle');
  const promoEnabledInput = document.getElementById('promoVideoEnabledInput');
  const promoEnabledLabel = document.getElementById('promoEnabledLabel');
  if (promoToggle) {
    promoToggle.addEventListener('click', () => {
      const nowOn = promoEnabledInput.value !== 'true';
      promoEnabledInput.value = nowOn ? 'true' : 'false';
      promoToggle.style.background = nowOn ? 'var(--green-500)' : 'var(--gray-300)';
      promoToggle.firstElementChild.style.left = nowOn ? '25px' : '3px';
      promoEnabledLabel.textContent = nowOn ? 'Popup is enabled' : 'Popup is disabled';
      promoToggle.closest('form').dispatchEvent(new Event('change', { bubbles: true }));
    });
  }
  const promoVideoUrlInput = document.getElementById('promoVideoUrlInput');
  const promoTestLinkBox = document.getElementById('promoVideoTestLink');
  function updatePromoTestLink() {
    const url = promoVideoUrlInput.value || '';
    const m = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&?\s]+)/);
    if (m) {
      promoTestLinkBox.innerHTML = `<a href="https://www.youtube-nocookie.com/embed/${m[1]}?autoplay=0" target="_blank" rel="noreferrer" style="font-size:.82rem;color:var(--green-700);font-weight:600;display:inline-flex;align-items:center;gap:6px"><i class="bi bi-box-arrow-up-right"></i> Test if this video is embeddable</a>`;
    } else {
      promoTestLinkBox.innerHTML = '';
    }
  }
  if (promoVideoUrlInput) {
    promoVideoUrlInput.addEventListener('input', updatePromoTestLink);
    updatePromoTestLink();
  }

  // ---- Password change ----
  // ---- Change password ----
  // The button is always clickable (browser autofill doesn't count as typing, so a
  // "disabled until you type" button looked dead). Every outcome shows a message.
  const pwForm = document.getElementById('pwForm');
  const pwSubmitBtn = document.getElementById('pwSubmitBtn');
  const pwAlert = document.getElementById('pwAlert');
  function pwShowAlert(type, msg) {
    pwAlert.hidden = false;
    pwAlert.className = 'adm-alert ' + type;
    pwAlert.style.marginBottom = '20px';
    pwAlert.innerHTML = '<i class="bi ' + (type === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle') + '"></i>' + esc(msg);
    pwAlert.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
  }
  function pwFieldError(input, msg) {
    const field = input.closest('.field');
    let box = field.querySelector('.field-error');
    if (!msg) { field.classList.remove('has-error'); if (box) box.remove(); return; }
    if (!box) { box = document.createElement('div'); box.className = 'field-error'; field.appendChild(box); }
    box.textContent = msg;
    field.classList.add('has-error');
  }
  function pwStrength(v) {
    let score = 0;
    if (v.length >= 8) score++;
    if (v.length >= 12) score++;
    if (/[a-z]/.test(v) && /[A-Z]/.test(v)) score++;
    if (/\d/.test(v)) score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;
    return Math.min(4, score); // 0..4
  }
  if (pwForm) {
    const cur = pwForm.current_password, nw = pwForm.new_password, cf = pwForm.confirm_password;
    const bar = document.getElementById('pwMeterBar');
    const hint = document.getElementById('pwHint');
    const LABELS = ['Too short', 'Weak', 'Okay', 'Good', 'Strong'];
    nw.addEventListener('input', () => {
      const s = nw.value ? pwStrength(nw.value) : 0;
      bar.style.width = nw.value ? ((s + 1) * 20) + '%' : '0';
      bar.dataset.level = String(s);
      hint.textContent = nw.value
        ? 'Strength: ' + (nw.value.length < 8 ? LABELS[0] : LABELS[s])
        : 'Use 8+ characters. Mixing letters, numbers and symbols makes it stronger.';
    });
    [cur, nw, cf].forEach((el) => el.addEventListener('input', () => pwFieldError(el, '')));
    pwForm.querySelectorAll('.pw-eye-toggle').forEach((btn) => {
      btn.addEventListener('click', () => {
        const input = btn.previousElementSibling;
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.innerHTML = '<i class="bi ' + (show ? 'bi-eye-slash' : 'bi-eye') + '"></i>';
      });
    });

    pwForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      pwAlert.hidden = true;
      // client-side checks first, each shown under its field
      let bad = null;
      const fail = (el, msg) => { pwFieldError(el, msg); bad = bad || el; };
      [cur, nw, cf].forEach((el) => pwFieldError(el, ''));
      if (!cur.value) fail(cur, 'Enter your current password.');
      if (!nw.value) fail(nw, 'Enter a new password.');
      else if (nw.value.length < 8) fail(nw, 'Use at least 8 characters.');
      else if (nw.value.length > 72) fail(nw, 'Use 72 characters or fewer.');
      else if (nw.value === cur.value) fail(nw, 'The new password must be different from the current one.');
      if (!cf.value) fail(cf, 'Type the new password again.');
      else if (nw.value && cf.value !== nw.value) fail(cf, 'Passwords do not match.');
      if (bad) { bad.focus(); return; }

      const orig = pwSubmitBtn.innerHTML;
      pwSubmitBtn.disabled = true;
      pwSubmitBtn.innerHTML = '<span class="btn-spinner"></span> Updating…';
      try {
        const res = await fetch('/admin/api/change-password.php', {
          method: 'POST', credentials: 'include',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ current_password: cur.value, new_password: nw.value }),
        });
        let data = {};
        try { data = await res.json(); } catch (_) { /* non-JSON (e.g. server error page) */ }
        if (res.status === 401 && data.error === 'Unauthorized') {
          throw new Error('Your session has expired. Please log in again, then change the password.');
        }
        if (!res.ok) {
          if (res.status === 401) { pwFieldError(cur, data.error || 'Current password is incorrect.'); cur.focus(); }
          throw new Error(data.error || ('Could not update the password (server error ' + res.status + '). Please try again.'));
        }
        pwShowAlert('success', 'Password updated. Use the new password next time you log in.');
        if (window.showToast) showToast('Password updated', 'success');
        pwForm.reset();
        bar.style.width = '0';
        hint.textContent = 'Use 8+ characters. Mixing letters, numbers and symbols makes it stronger.';
      } catch (err) {
        pwShowAlert('error', err.message === 'Failed to fetch' ? 'No connection to the server. Check your internet and try again.' : err.message);
      } finally {
        pwSubmitBtn.disabled = false;
        pwSubmitBtn.innerHTML = orig;
      }
    });
  }
})();
