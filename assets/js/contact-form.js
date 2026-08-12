/**
 * Contact page form wiring — port of the submit handler in Contact.jsx,
 * including the desktop-inline vs mobile-popup success states (CSS in
 * pages.css already handles which one is visible per breakpoint via
 * .csm-overlay / .csm-inline).
 */
(function () {
  const form = document.getElementById('contactForm');
  if (!form) return;

  const formWrap = document.getElementById('contactFormWrap');
  const popup = document.getElementById('contactSuccessPopup');
  const inline = document.getElementById('contactSuccessInline');

  function showSuccess() {
    formWrap.hidden = true;
    popup.hidden = false;
    inline.hidden = false;
  }

  function resetForm() {
    form.reset();
    formWrap.hidden = false;
    popup.hidden = true;
    inline.hidden = true;
  }

  document.getElementById('contactBackBtn').addEventListener('click', resetForm);
  document.getElementById('contactAnotherBtn').addEventListener('click', resetForm);
  popup.addEventListener('click', (e) => { if (e.target === popup) resetForm(); });

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    submitForm(form, '/api/contact.php', {
      isMultipart: false,
      onSuccess: showSuccess,
    });
  });
})();
