/**
 * POS Progressive Web App — register SW + native install prompt.
 * Forces already-installed clients to pick up a new worker (barcode/live POS data).
 */
(function () {
  'use strict';

  if (!('serviceWorker' in navigator)) return;

  var cfg = window.__POS_PWA__ || {};
  if (!cfg.enabled) return;

  var deferredPrompt = null;
  var installBtn = null;

  function isStandalone() {
    return window.matchMedia('(display-mode: standalone)').matches
      || window.matchMedia('(display-mode: minimal-ui)').matches
      || window.navigator.standalone === true;
  }

  function setInstallVisibility(show) {
    installBtn = installBtn || document.getElementById('pos_pwa_install_btn');
    if (!installBtn) return;
    if (show && !isStandalone()) {
      installBtn.classList.remove('tw-hidden');
      installBtn.style.display = '';
    } else {
      installBtn.classList.add('tw-hidden');
    }
  }

  function skipWaiting(worker) {
    if (!worker) return;
    try {
      worker.postMessage({ type: 'SKIP_WAITING' });
    } catch (e) {}
  }

  function registerSw() {
    var swUrl = cfg.swUrl || '/pos-pwa/sw.js';
    navigator.serviceWorker.register(swUrl, { scope: '/' })
      .then(function (reg) {
        if (reg.waiting) {
          skipWaiting(reg.waiting);
        }
        reg.addEventListener('updatefound', function () {
          var nw = reg.installing;
          if (!nw) return;
          nw.addEventListener('statechange', function () {
            if (nw.state === 'installed' && navigator.serviceWorker.controller) {
              skipWaiting(nw);
            }
          });
        });
        if (typeof reg.update === 'function') {
          reg.update();
        }
      })
      .catch(function (err) {
        if (window.console && console.warn) {
          console.warn('POS PWA SW registration failed', err);
        }
      });

    var reloaded = false;
    try {
      reloaded = sessionStorage.getItem('pos_pwa_reloaded') === '1';
      if (reloaded) {
        sessionStorage.removeItem('pos_pwa_reloaded');
      }
    } catch (e) {}

    navigator.serviceWorker.addEventListener('controllerchange', function () {
      if (reloaded) return;
      try {
        sessionStorage.setItem('pos_pwa_reloaded', '1');
      } catch (e2) {}
      window.location.reload();
    });
  }

  window.addEventListener('beforeinstallprompt', function (e) {
    e.preventDefault();
    deferredPrompt = e;
    setInstallVisibility(true);
  });

  window.addEventListener('appinstalled', function () {
    deferredPrompt = null;
    setInstallVisibility(false);
  });

  document.addEventListener('click', function (e) {
    var btn = e.target.closest('#pos_pwa_install_btn');
    if (!btn) return;
    e.preventDefault();

    if (deferredPrompt) {
      deferredPrompt.prompt();
      deferredPrompt.userChoice.finally(function () {
        deferredPrompt = null;
        setInstallVisibility(false);
      });
      return;
    }

    var tip = cfg.iosHint || 'On iPhone/iPad: tap Share → Add to Home Screen.';
    if (/iphone|ipad|ipod/i.test(navigator.userAgent)) {
      alert(tip);
    } else if (isStandalone()) {
      setInstallVisibility(false);
    } else {
      alert(cfg.installHint || 'Use your browser menu → Install app / Add to Home Screen.');
    }
  });

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      registerSw();
      if (isStandalone()) {
        document.documentElement.classList.add('pos-pwa-standalone');
        document.body && document.body.classList.add('pos-pwa-standalone');
        setInstallVisibility(false);
      } else if (/iphone|ipad|ipod/i.test(navigator.userAgent)) {
        setInstallVisibility(true);
      }
    });
  } else {
    registerSw();
    if (isStandalone()) {
      document.documentElement.classList.add('pos-pwa-standalone');
      document.body && document.body.classList.add('pos-pwa-standalone');
    } else if (/iphone|ipad|ipod/i.test(navigator.userAgent)) {
      setInstallVisibility(true);
    }
  }
})();
