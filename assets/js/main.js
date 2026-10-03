/**
 * Site-wide behavior: scroll header state, mobile nav toggle, promo video
 * popup, and the shared AJAX form-submission helper used by every public
 * form (Apply modal, Contact, Careers modal, Load Calculator).
 */
(function () {
  // ---- Scrolled header state (mirrors Header.jsx's scroll listener) ----
  const header = document.getElementById('siteHeader');
  if (header) {
    const onScroll = () => header.classList.toggle('scrolled', window.scrollY > 30);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  // ---- Mobile nav toggle ----
  const toggle = document.getElementById('hdrToggle');
  const nav = document.getElementById('hdrNav');
  if (toggle && header && nav) {
    toggle.addEventListener('click', () => {
      const isOpen = header.classList.toggle('open');
      toggle.classList.toggle('active', isOpen);
    });
    nav.querySelectorAll('a, button').forEach((el) => {
      // The language button only opens its dropdown; closing the mobile menu here
      // would hide the dropdown before it could be used.
      if (el.closest('.lang-switch-btn') || el.matches('.lang-switch-btn')) return;
      el.addEventListener('click', () => {
        header.classList.remove('open');
        toggle.classList.remove('active');
      });
    });
  }

  // ---- Promo video popup ----
  const vpOverlay = document.getElementById('vpOverlay');
  if (vpOverlay) {
    const videoWrap = document.getElementById('vpVideoWrap');
    const loader = document.getElementById('vpLoader');
    const muteBtn = document.getElementById('vpMuteBtn');
    const closeBtn = document.getElementById('vpCloseBtn');
    const videoId = videoWrap.dataset.videoId;
    let muted = true;
    let iframe = null;

    function buildSrc() {
      return (
        'https://www.youtube-nocookie.com/embed/' + videoId +
        '?autoplay=1&mute=' + (muted ? 1 : 0) + '&rel=0&modestbranding=1&enablejsapi=1'
      );
    }

    function mountIframe() {
      if (iframe) iframe.remove();
      loader.hidden = false;
      iframe = document.createElement('iframe');
      iframe.src = buildSrc();
      iframe.title = 'WePower Solar';
      iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
      iframe.allowFullscreen = true;
      iframe.addEventListener('load', () => { loader.hidden = true; });
      videoWrap.appendChild(iframe);
    }

    function open() {
      vpOverlay.hidden = false;
      mountIframe();
    }
    function close() {
      vpOverlay.hidden = true;
      if (iframe) { iframe.remove(); iframe = null; }
    }

    muteBtn.addEventListener('click', () => {
      muted = !muted;
      muteBtn.innerHTML = '<i class="bi ' + (muted ? 'bi-volume-mute-fill' : 'bi-volume-up-fill') + '"></i>';
      mountIframe();
    });
    closeBtn.addEventListener('click', close);
    vpOverlay.addEventListener('click', (e) => { if (e.target === vpOverlay) close(); });

    // Show once per page load, 1.2s after load — matches VideoPopup.jsx
    setTimeout(open, 1200);
  }

  // ---- Shared AJAX form-submission helper ----
  // Usage: submitForm(formEl, '/api/contact.php', { isMultipart:false, onSuccess(data){...} })
  window.submitForm = async function (formEl, endpoint, opts) {
    opts = opts || {};
    const submitBtn = formEl.querySelector('[type="submit"]');
    const errorBox = formEl.parentElement.querySelector('.apply-error, .form-error, .cm-error');

    if (errorBox) errorBox.hidden = true;
    if (submitBtn) { submitBtn.disabled = true; submitBtn.dataset.origHtml = submitBtn.innerHTML; submitBtn.innerHTML = '<span class="btn-spinner"></span> Sending…'; }

    try {
      const formData = new FormData(formEl);
      let res;
      if (opts.isMultipart) {
        res = await fetch(endpoint, { method: 'POST', body: formData });
      } else {
        const obj = {};
        formData.forEach((v, k) => { obj[k] = v; });
        res = await fetch(endpoint, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(obj),
        });
      }
      const data = await res.json();
      if (!res.ok) throw new Error(data.error || 'Something went wrong.');

      if (opts.onSuccess) opts.onSuccess(data);
      showToast(data.message || 'Submitted successfully!', 'success');
    } catch (err) {
      if (errorBox) {
        errorBox.hidden = false;
        const span = errorBox.querySelector('span');
        if (span) span.textContent = err.message;
        else errorBox.textContent = err.message;
      } else {
        showToast(err.message, 'error');
      }
    } finally {
      if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = submitBtn.dataset.origHtml; }
    }
  };
})();
