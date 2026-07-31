/**
 * POS Progressive Web App — register SW + native install prompt.
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

  function registerSw() {
    var swUrl = cfg.swUrl || '/pos-pwa/sw.js';
    navigator.serviceWorker.register(swUrl, { scope: '/' })
      .catch(function (err) {
        if (window.console && console.warn) {
          console.warn('POS PWA SW registration failed', err);
        }
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

    // iOS / browsers without beforeinstallprompt
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
        // Show install tip button on iOS (no beforeinstallprompt)
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
