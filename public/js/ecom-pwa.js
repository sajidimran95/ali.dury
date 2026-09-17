/**
 * Ecommerce storefront PWA — install bar on all devices.
 * Hidden permanently only after this device installs / opens as app.
 */
(function () {
  'use strict';

  var cfg = window.__ECOM_PWA__ || {};
  var STORAGE_KEY = 'ecom_pwa_installed';
  var bar = null;

  function isStandalone() {
    return window.matchMedia('(display-mode: standalone)').matches
      || window.matchMedia('(display-mode: minimal-ui)').matches
      || window.navigator.standalone === true;
  }

  function wasInstalled() {
    try {
      return localStorage.getItem(STORAGE_KEY) === '1';
    } catch (e) {
      return false;
    }
  }

  function markInstalled() {
    try {
      localStorage.setItem(STORAGE_KEY, '1');
    } catch (e) { /* ignore */ }
  }

  function shouldShowBar() {
    return !isStandalone() && !wasInstalled();
  }

  function setBarVisible(show) {
    bar = bar || document.getElementById('ecom_pwa_install_bar');
    if (!bar) return;
    if (show && shouldShowBar()) {
      bar.hidden = false;
      bar.setAttribute('aria-hidden', 'false');
      document.body.classList.add('ecom-pwa-bar-visible');
    } else {
      bar.hidden = true;
      bar.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('ecom-pwa-bar-visible');
    }
  }

  function hideBarPermanently() {
    markInstalled();
    setBarVisible(false);
  }

  function registerSw() {
    if (!('serviceWorker' in navigator)) return;
    var swUrl = cfg.swUrl || '/ecom-pwa/sw.js';
    var keepCache = cfg.cacheName || '';
    navigator.serviceWorker.register(swUrl, { scope: '/' }).then(function (reg) {
      if (reg && typeof reg.update === 'function') {
        reg.update().catch(function () {});
      }
      // Drop stale caches that may still hold an old ecom-pwa.js with preventDefault()
      if (window.caches && caches.keys) {
        caches.keys().then(function (keys) {
          keys.forEach(function (k) {
            if (k.indexOf('japspos-ecom-pwa-') !== 0) return;
            if (keepCache && k === keepCache) return;
            caches.delete(k).catch(function () {});
          });
        }).catch(function () {});
      }
    }).catch(function (err) {
      if (window.console && console.warn) {
        console.warn('Ecommerce PWA SW registration failed', err);
      }
    });
  }

  // Do not call preventDefault() on beforeinstallprompt — that suppresses Chrome's
  // install banner and logs the console warning about needing to call prompt().
  window.addEventListener('appinstalled', function () {
    hideBarPermanently();
  });

  document.addEventListener('click', function (e) {
    var installBtn = e.target.closest('#ecom_pwa_install_btn');
    var dismissBtn = e.target.closest('#ecom_pwa_dismiss_btn');

    if (dismissBtn) {
      // Soft hide for this page only — will show again next visit until installed
      e.preventDefault();
      setBarVisible(false);
      return;
    }

    if (!installBtn) return;
    e.preventDefault();

    if (isStandalone()) {
      hideBarPermanently();
      return;
    }

    var isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);
    var tip = isIos
      ? (cfg.iosHint || 'On iPhone/iPad: tap Share → Add to Home Screen.')
      : (cfg.installHint || 'Use your browser menu → Install app / Add to Home Screen.');
    alert(tip);
  });

  function boot() {
    registerSw();

    if (isStandalone() || wasInstalled()) {
      markInstalled();
      document.documentElement.classList.add('ecom-pwa-standalone');
      if (document.body) document.body.classList.add('ecom-pwa-standalone');
      setBarVisible(false);
      return;
    }

    // All devices (mobile + desktop) until this device installs
    setBarVisible(true);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
