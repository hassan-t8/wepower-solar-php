/**
 * FAQ accordion — vanilla JS port of the FaqItem component
 * (was duplicated in 3 React files; now shared here + config/site.php's
 * single $faq array, included via one partial wherever needed).
 */
(function () {
  document.querySelectorAll('.faq-item').forEach((item) => {
    const btn = item.querySelector('.faq-q');
    if (!btn) return;
    btn.addEventListener('click', () => {
      const isOpen = item.classList.toggle('open');
      const icon = btn.querySelector('i');
      if (icon) icon.className = 'bi ' + (isOpen ? 'bi-dash-lg' : 'bi-plus-lg');
      const answer = item.querySelector('.faq-a');
      if (answer) answer.hidden = !isOpen;
    });
  });
})();
