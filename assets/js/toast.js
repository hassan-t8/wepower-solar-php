/**
 * Toast notifications — vanilla JS port of Toast.jsx / ToastContainer.
 * Usage: showToast('Message here', 'success' | 'error' | 'info')
 */
(function () {
  let wrap = null;

  function ensureWrap() {
    if (wrap) return wrap;
    wrap = document.createElement('div');
    wrap.className = 'toast-wrap';
    wrap.setAttribute('role', 'region');
    wrap.setAttribute('aria-live', 'polite');
    document.body.appendChild(wrap);
    return wrap;
  }

  const ICONS = {
    success: 'bi-check-circle-fill',
    error: 'bi-exclamation-circle-fill',
    info: 'bi-info-circle-fill',
  };

  window.showToast = function (msg, type) {
    type = type || 'info';
    const el = document.createElement('div');
    el.className = 'toast toast-' + type;
    el.setAttribute('role', 'alert');
    el.innerHTML =
      '<i class="bi ' + (ICONS[type] || ICONS.info) + '"></i>' +
      '<span></span>' +
      '<button class="toast-close" aria-label="Dismiss"><i class="bi bi-x-lg"></i></button>';
    el.querySelector('span').textContent = msg;

    const remove = () => {
      el.style.animation = 'none';
      el.style.opacity = '0';
      setTimeout(() => el.remove(), 200);
    };
    el.querySelector('.toast-close').addEventListener('click', remove);

    ensureWrap().appendChild(el);
    setTimeout(remove, 5000);
  };
})();
