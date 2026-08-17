/**
 * "Get a Quote" modal wiring — open/close/prefill/submit.
 * Any element with [data-open-apply-modal] opens it; an optional
 * data-service="Service Title" attribute pre-fills the service dropdown
 * (mirrors openApply(serviceTitle) in the original React AppContext).
 */
(function () {
  const overlay = document.getElementById('applyOverlay');
  if (!overlay) return;

  const modal = document.getElementById('applyModal');
  const closeBtn = document.getElementById('applyCloseBtn');
  const doneBtn = document.getElementById('applyDoneBtn');
  const form = document.getElementById('applyForm');
  const formWrap = document.getElementById('applyFormWrap');
  const successPane = document.getElementById('applySuccess');
  const successMsg = document.getElementById('applySuccessMsg');
  const serviceSelect = document.getElementById('applyServiceSelect');

  function openModal(serviceTitle) {
    form.reset();
    formWrap.hidden = false;
    successPane.hidden = true;
    document.getElementById('applyError').hidden = true;
    if (serviceTitle && serviceSelect) serviceSelect.value = serviceTitle;
    overlay.hidden = false;
    document.body.style.overflow = 'hidden';
  }

  function closeModal() {
    overlay.hidden = true;
    document.body.style.overflow = '';
  }

  document.querySelectorAll('[data-open-apply-modal]').forEach((btn) => {
    btn.addEventListener('click', () => openModal(btn.dataset.service || ''));
  });

  closeBtn.addEventListener('click', closeModal);
  overlay.addEventListener('click', (e) => { if (e.target === overlay) closeModal(); });
  doneBtn.addEventListener('click', closeModal);
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !overlay.hidden) closeModal();
  });

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    submitForm(form, '/api/apply.php', {
      isMultipart: false,
      onSuccess(data) {
        const i18n = window.WP_I18N || {};
        const name = form.querySelector('[name="name"]').value;
        const template = i18n.applySuccessBody || 'Thank you, {name}. Our solar team will reach out shortly to confirm your free consultation.';
        successMsg.textContent = template.replace('{name}', name || i18n.applyThereFallback || 'there');
        formWrap.hidden = true;
        successPane.hidden = false;
      },
    });
  });
})();
