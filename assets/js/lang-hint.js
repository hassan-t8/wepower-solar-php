/**
 * One-time bilingual nudge pointing at the language switcher, shown on
 * first visit (localStorage flag) — port of LanguageHint.jsx.
 */
(function () {
  const hint = document.getElementById('langHint');
  if (!hint) return;

  const SEEN_KEY = 'wp_lang_hint_seen';
  const closeBtn = document.getElementById('langHintClose');
  const gotItBtn = document.getElementById('langHintBtn');

  function dismiss() {
    hint.hidden = true;
    try { localStorage.setItem(SEEN_KEY, '1'); } catch (e) {}
  }

  let seen = false;
  try { seen = !!localStorage.getItem(SEEN_KEY); } catch (e) {}

  if (!seen) {
    setTimeout(() => { hint.hidden = false; }, 1200);
  }

  closeBtn?.addEventListener('click', dismiss);
  gotItBtn?.addEventListener('click', dismiss);

  // If the visitor opens the switcher themselves, the nudge has done its
  // job — dismiss it so it can't sit on top of the dropdown menu.
  window.addEventListener('wp-lang-switch-open', dismiss);
})();
