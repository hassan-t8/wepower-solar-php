/**
 * Careers apply modal wiring — port of CareersModal.jsx behavior.
 * Opened via .js-apply-job buttons (specific job, with dept/location/
 * experience passed through data attributes) or #generalApplyBtn.
 */
(function () {
  const overlay = document.getElementById('cmOverlay');
  if (!overlay) return;

  const closeBtn = document.getElementById('cmCloseBtn');
  const closeDoneBtn = document.getElementById('cmCloseDoneBtn');
  const form = document.getElementById('careersForm');
  const formWrap = document.getElementById('cmFormWrap');
  const successPane = document.getElementById('cmSuccess');
  const errorBox = document.getElementById('careersError');
  const positionSelect = document.getElementById('cmPositionSelect');
  const sideJob = document.getElementById('cmSideJob');
  const sideGeneral = document.getElementById('cmSideGeneral');
  const sideRole = document.getElementById('cmSideRole');
  const sideMeta = document.getElementById('cmSideMeta');

  function metaSpan(icon, text) {
    return text ? '<span><i class="bi ' + icon + '"></i>' + text.replace(/</g, '&lt;') + '</span>' : '';
  }

  function openModal(jobTitle, dept, location, experience) {
    form.reset();
    formWrap.hidden = false;
    successPane.hidden = true;
    errorBox.hidden = true;

    if (jobTitle) {
      positionSelect.value = jobTitle;
      sideRole.textContent = jobTitle;
      sideMeta.innerHTML = metaSpan('bi-building', dept) + metaSpan('bi-geo-alt', location) + metaSpan('bi-briefcase', experience);
      sideJob.hidden = false;
      sideGeneral.hidden = true;
    } else {
      positionSelect.value = '';
      sideJob.hidden = true;
      sideGeneral.hidden = false;
    }

    overlay.hidden = false;
    document.body.style.overflow = 'hidden';
  }

  function closeModal() {
    overlay.hidden = true;
    document.body.style.overflow = '';
  }

  document.querySelectorAll('.js-apply-job').forEach((btn) => {
    btn.addEventListener('click', () => {
      openModal(btn.dataset.jobTitle, btn.dataset.jobDept, btn.dataset.jobLocation, btn.dataset.jobExperience);
    });
  });

  const generalBtn = document.getElementById('generalApplyBtn');
  if (generalBtn) generalBtn.addEventListener('click', () => openModal(null));

  closeBtn.addEventListener('click', closeModal);
  closeDoneBtn.addEventListener('click', closeModal);
  overlay.addEventListener('click', (e) => { if (e.target === overlay) closeModal(); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !overlay.hidden) closeModal(); });

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    if (!form.full_name.value || !form.email.value || !form.phone.value || !form.position.value) {
      errorBox.hidden = false;
      errorBox.querySelector('span').textContent = (window.WP_I18N && window.WP_I18N.careersRequiredErr) || 'Name, email, phone and position are required.';
      return;
    }
    submitForm(form, '/api/careers.php', {
      isMultipart: true,
      onSuccess() {
        formWrap.hidden = true;
        successPane.hidden = false;
      },
    });
  });
})();
