/* DIVI Design Plus — Scroll Reveal via Intersection Observer */
(function () {
  'use strict';

  function init() {
    var targets = document.querySelectorAll('.ddp-reveal, .ddp-slide-up, .ddp-fade-in');
    if (!targets.length || !('IntersectionObserver' in window)) {
      // Fallback: make everything visible immediately
      targets.forEach(function (el) { el.classList.add('is-visible'); });
      return;
    }

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
          }
        });
      },
      {
        threshold:  0.12,
        rootMargin: '0px 0px -48px 0px',
      }
    );

    targets.forEach(function (el) { observer.observe(el); });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
