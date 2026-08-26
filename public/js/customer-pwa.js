/**
 * Customer portal PWA — install bar + SW (scope /customer only).
 * Shows on login and all /customer pages.
 */
(function () {
  'use strict';
  var cfg = window.__CUSTOMER_PWA__ || {};
  var KEY = 'customer_pwa_installed';
  var DISMISS_KEY = 'customer_pwa_dismissed_at';
  var DISMISS_MS = 7 * 24 * 60 * 60 * 1000; // 7 days
  var deferred = null;

  function ensureHeadLinks() {
    if (!document.querySelector('link[rel="manifest"][data-customer-pwa]')) {
      var m = document.createElement('link');
      m.rel = 'manifest';
      m.href = cfg.manifestUrl || '/customer/pwa/manifest.webmanifest';
      m.setAttribute('data-customer-pwa', '1');
      document.head.appendChild(m);
    }
    if (!document.querySelector('link[rel="apple-touch-icon"][data-customer-pwa]')) {
      var a = document.createElement('link');
      a.rel = 'apple-touch-icon';
      a.href = cfg.iconUrl || '/pwa/customer-icon-192.png';
      a.setAttribute('data-customer-pwa', '1');
      document.head.appendChild(a);
    }
    var meta = document.querySelector('meta[name="theme-color"]');
    if (!meta) {
      meta = document.createElement('meta');
      meta.name = 'theme-color';
      document.head.appendChild(meta);
    }
    meta.content = '#e11d48';
  }

  function isStandalone() {
    return window.matchMedia('(display-mode: standalone)').matches
      || window.matchMedia('(display-mode: minimal-ui)').matches
      || window.navigator.standalone === true;
  }
  function installed() {
    try { return localStorage.getItem(KEY) === '1'; } catch (e) { return false; }
  }
  function dismissedRecently() {
    try {
      var t = parseInt(localStorage.getItem(DISMISS_KEY) || '0', 10);
      return t > 0 && (Date.now() - t) < DISMISS_MS;
    } catch (e) { return false; }
  }
  function mark() {
    try { localStorage.setItem(KEY, '1'); } catch (e) {}
  }
  function markDismiss() {
    try { localStorage.setItem(DISMISS_KEY, String(Date.now())); } catch (e) {}
  }
  function setBar(show) {
    var bar = document.getElementById('customer_pwa_install_bar');
    if (!bar) return;
    if (show && !isStandalone() && !installed()) {
      bar.hidden = false;
    } else {
      bar.hidden = true;
    }
  }

  ensureHeadLinks();

  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register(cfg.swUrl || '/customer/pwa/sw.js', { scope: '/customer/' }).catch(function () {});
  }

  window.addEventListener('beforeinstallprompt', function (e) {
    e.preventDefault();
    deferred = e;
    if (!dismissedRecently()) setBar(true);
  });
  window.addEventListener('appinstalled', function () {
    mark();
    setBar(false);
  });

  document.addEventListener('click', function (e) {
    if (e.target.closest('#customer_pwa_dismiss_btn')) {
      markDismiss();
      setBar(false);
      return;
    }
    var btn = e.target.closest('#customer_pwa_install_btn');
    if (!btn) return;
    e.preventDefault();
    if (deferred) {
      deferred.prompt();
      deferred.userChoice.then(function (c) {
        deferred = null;
        if (c && c.outcome === 'accepted') { mark(); setBar(false); }
      });
      return;
    }
    if (/iphone|ipad|ipod/i.test(navigator.userAgent)) {
      alert('On iPhone: Share → Add to Home Screen');
    } else {
      alert('Use browser menu → Install app / Add to Home Screen');
    }
  });

  function boot() {
    if (isStandalone() || installed()) {
      mark();
      document.body && document.body.classList.add('customer-pwa-standalone');
      setBar(false);
      return;
    }
    if (!dismissedRecently()) {
      setBar(true);
    }
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
  else boot();
})();
