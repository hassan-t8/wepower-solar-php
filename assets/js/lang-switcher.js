/**
 * Language switcher dropdown (EN / اردو) in the header. The links inside
 * the menu do a real navigation to ?lang=en|ur — includes/lang.php reads
 * that on the next request and sets the wp_lang cookie — so this script
 * only handles the dropdown open/close UI, not the actual switching.
 */
(function () {
  const wrap = document.getElementById('langSwitch');
  const btn = document.getElementById('langSwitchBtn');
  const menu = document.getElementById('langSwitchMenu');
  if (!wrap || !btn || !menu) return;

  function setOpen(open) {
    menu.hidden = !open;
    btn.setAttribute('aria-expanded', String(open));
    btn.querySelector('.lang-switch-caret')?.classList.toggle('open', open);
  }

  btn.addEventListener('click', () => {
    const next = menu.hidden;
    setOpen(next);
    if (next) window.dispatchEvent(new CustomEvent('wp-lang-switch-open'));
  });

  document.addEventListener('click', (e) => {
    if (!wrap.contains(e.target)) setOpen(false);
  });
})();
