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
  const pwForm = document.getElementById('pwForm');
  const pwSubmitBtn = document.getElementById('pwSubmitBtn');
  const pwAlert = document.getElementById('pwAlert');
  function pwShowAlert(type, msg) {
    pwAlert.hidden = false;
    pwAlert.className = 'adm-alert ' + type;
    pwAlert.style.marginBottom = '20px';
    pwAlert.innerHTML = '<i class="bi ' + (type === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle') + '"></i>' + esc(msg);
  }
  if (pwForm) {
    pwForm.addEventListener('input', () => {
      const dirty = Array.from(pwForm.elements).some((el) => el.type === 'password' && el.value.length > 0);
      pwSubmitBtn.disabled = !dirty;
    });
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
      const newPw = pwForm.new_password.value;
      const confirmPw = pwForm.confirm_password.value;
      if (newPw !== confirmPw) { pwShowAlert('error', 'New passwords do not match.'); return; }
      try {
        const res = await fetch('/admin/api/change-password.php', {
          method: 'POST', credentials: 'include',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ current_password: pwForm.current_password.value, new_password: newPw }),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error);
        pwShowAlert('success', data.message);
        pwForm.reset();
        pwSubmitBtn.disabled = true;
      } catch (err) {
        pwShowAlert('error', err.message);
      }
    });
  }
})();
