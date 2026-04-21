/* DIVI Design Plus — Animations */
(function () {
  'use strict';

  function injectOrbLayers() {
    document.querySelectorAll('.ddp-orbs').forEach(function(el) {
      if (el.querySelector('.ddp-orb')) return;
      [1, 2, 3].forEach(function(i) {
        var orb = document.createElement('div');
        orb.className = 'ddp-orb ddp-orb-' + i;
        el.insertBefore(orb, el.firstChild);
      });
    });
  }

  function injectAuroraLayers() {
    document.querySelectorAll('.ddp-aurora').forEach(function (el) {
      if (el.querySelector('.ddp-aurora-layer')) return;
      var layer = document.createElement('div');
      layer.className = 'ddp-aurora-layer';
      el.insertBefore(layer, el.firstChild);
    });
  }

  function init() {
    injectAuroraLayers();
    injectOrbLayers();

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
