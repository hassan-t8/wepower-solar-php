/**
 * Animated number counters — vanilla JS port of the Counter component
 * (ui.jsx). Counts up from 0 to data-value when scrolled into view.
 */
(function () {
  const els = document.querySelectorAll('.js-counter');
  if (!els.length) return;

  function animate(el) {
    const target = parseFloat(el.dataset.value) || 0;
    const suffix = el.dataset.suffix || '';
    const duration = 1200;
    const start = performance.now();

    function tick(now) {
      const progress = Math.min((now - start) / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3); // ease-out cubic
      const current = Math.round(target * eased);
      el.textContent = current + suffix;
      if (progress < 1) requestAnimationFrame(tick);
      else el.textContent = target + suffix;
    }
    requestAnimationFrame(tick);
  }

  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          animate(entry.target);
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.4 });
    els.forEach((el) => observer.observe(el));
  } else {
    els.forEach(animate);
  }
})();
