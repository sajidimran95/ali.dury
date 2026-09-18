<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4CAF79">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="{{ $ecom_settings->store_name ?? 'Store' }}">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="manifest" href="{{ url('/ecom-pwa/manifest.webmanifest') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('pwa/ecom-icon-192.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('pwa/ecom-icon-512.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('pwa/ecom-apple-touch-icon.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('pwa/ecom-apple-touch-icon.png') }}">
    <title>@yield('title', $ecom_settings->seo_title ?? $ecom_settings->store_name ?? 'Wholesale Store')</title>
    <meta name="description" content="@yield('meta_description', $ecom_settings->seo_description ?? '')">
    <link rel="stylesheet" href="{{ asset('fonts/greencloud/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/greencloud-store.css') }}">
    <link rel="stylesheet" href="{{ asset('css/greencloud-utilities.css') }}">
    <link rel="stylesheet" href="{{ asset('css/greencloud-theme.css') }}?v={{ config('app.asset_version', config('constants.asset_version', '1')) }}">
    <style>
        body { font-family: Poppins, system-ui, sans-serif; background: #f4f4f4; color: #1a1a1a; }
        .cat-nav-wrap { position: relative; }
        .mega { position: static; }
        .mega > a { position: relative; z-index: 2; }
        .mega-panel {
            display: none;
            position: absolute;
            left: 0;
            right: 0;
            top: 100%;
            z-index: 60;
            width: 100%;
            background: #fff;
            border-top: 2px solid #4CAF79;
            box-shadow: 0 16px 40px rgba(0,0,0,.18);
        }
        .mega-panel.is-open { display: block; }
        .section-title { position: relative; padding-bottom: 0.6rem; }
        .section-title:after {
            content: ''; position: absolute; left: 0; bottom: 0;
            width: 48px; height: 3px; background: #4CAF79;
        }
        .tile-hover { transition: transform .2s, box-shadow .2s; }
        .tile-hover:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,.12); }
        .img-overlay { background: linear-gradient(#0000 0%, #0000001a 40%, #000000a6 100%); }
        .scroll-row-wrap {
            position: relative;
            max-width: 100%;
            min-width: 0;
        }
        .scroll-row {
            display: flex;
            flex-wrap: nowrap;
            gap: 1rem;
            overflow-x: auto;
            overflow-y: hidden;
            scroll-behavior: smooth;
            scrollbar-width: none;
            -ms-overflow-style: none;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 0.75rem;
            max-width: 100%;
            min-width: 0;
        }
        .scroll-row::-webkit-scrollbar { display: none; }
        .scroll-row-btn {
            position: absolute;
            top: 50%;
            z-index: 10;
            width: 2.25rem;
            height: 2.25rem;
            margin-top: -1.125rem;
            border-radius: 9999px;
            background: #fff;
            border: 1px solid #e0e0e0;
            box-shadow: 0 4px 12px rgba(0,0,0,.1);
            align-items: center;
            justify-content: center;
            color: #1a1a1a;
            cursor: pointer;
            display: none;
            transition: border-color .2s, color .2s;
        }
        .scroll-row-btn.is-visible { display: flex; }
        .scroll-row-btn:hover { border-color: #4CAF79; color: #4CAF79; }
        .scroll-row-btn--left { left: 0; transform: translateX(-0.75rem); }
        .scroll-row-btn--right { right: 0; transform: translateX(0.75rem); }
        .age-gate-overlay {
            z-index: 9999; backdrop-filter: blur(4px); background: #000000bf;
            display: flex; justify-content: center; align-items: center;
            position: fixed; inset: 0;
        }
        .max-w-screen-xl { max-width: 1280px; }
        #storeHeader.is-scrolled { box-shadow: 0 10px 30px rgba(0,0,0,.12); }
        .cat-nav-bar {
            background: #2E6A49;
            overflow: visible;
        }
        .cat-nav-bar .cat-nav-track {
            display: flex;
            align-items: center;
            flex-wrap: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .cat-nav-bar .cat-nav-track::-webkit-scrollbar { display: none; }
        .cat-nav-bar .cat-nav-track > li { flex: 0 0 auto; }
        .cat-nav-bar a.cat-link,
        .cat-nav-bar a.promo-link {
            color: #fff !important;
            font-weight: 600;
            font-size: 0.8125rem;
            padding: 0.85rem 0.85rem;
            white-space: nowrap;
            transition: background 0.15s;
        }
        .cat-nav-bar a.cat-link svg { opacity: 0.7; }
        .cat-nav-bar a.promo-link:hover,
        .cat-nav-bar a.cat-link:hover,
        .cat-nav-bar .mega.is-active > a.cat-link {
            background: rgba(255,255,255,0.12);
            color: #fff !important;
        }
        .cat-nav-bar a.cat-link.is-current {
            background: rgba(76,175,121,0.35);
        }
        .brand-tile {
            display: flex; align-items: center; justify-content: center;
            min-height: 64px; border: 1px solid #ececec; border-radius: 8px;
            background: #fafafa; padding: 8px; text-align: center;
            transition: border-color .15s, box-shadow .15s;
        }
        .brand-tile:hover { border-color: #4CAF79; box-shadow: 0 2px 10px rgba(76,175,121,.12); }
        .input-w {
            width: 100%; border: 1px solid #d4d4d4; border-radius: 8px; padding: .7rem .9rem; font-size: .875rem;
            background: #fff; outline: none;
        }
        .input-w:focus { border-color: #4CAF79; box-shadow: 0 0 0 3px rgba(76,175,121,.15); }
        .btn-brand {
            background: #4CAF79; color: #fff; font-weight: 600; border-radius: 8px;
            padding: .7rem 1.25rem; transition: background .15s;
        }
        .btn-brand:hover { background: #2E6A49; }
        .pc-qty-input::-webkit-outer-spin-button,
        .pc-qty-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        .pc-qty-input { -moz-appearance: textfield; }
        .product-card { transition: box-shadow .2s, transform .2s; }
        .product-card:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(0,0,0,.08); }

        /* App-like mobile shell */
        .app-bottom-nav {
            position: fixed; left: 0; right: 0; bottom: 0; z-index: 90;
            background: rgba(255,255,255,.94);
            backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px);
            border-top: 1px solid #e8e8e8;
            padding-bottom: env(safe-area-inset-bottom, 0);
        }
        .app-tab {
            flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: 2px; min-height: 56px; color: #6b6b6b; font-size: 10px; font-weight: 600;
            text-decoration: none; background: transparent; border: 0; cursor: pointer;
            -webkit-tap-highlight-color: transparent;
        }
        .app-tab.active, .app-tab:active { color: #4CAF79; }
        .app-chip-scroll {
            display: flex; gap: 8px; overflow-x: auto; -webkit-overflow-scrolling: touch;
            scrollbar-width: none; padding: 0 16px 10px;
        }
        .app-chip-scroll::-webkit-scrollbar { display: none; }
        .app-chip {
            flex: 0 0 auto; padding: 8px 14px; border-radius: 999px; background: #f0f0f0;
            font-size: 13px; font-weight: 600; color: #1a1a1a; white-space: nowrap;
            border: 1px solid transparent; text-decoration: none;
        }
        .app-chip.active { background: #e8f5ee; color: #2E6A49; border-color: #cce4eb; }
        .app-sheet {
            position: fixed; inset: 0; z-index: 90; display: none;
        }
        .app-sheet.is-open { display: block; }
        .app-sheet__backdrop {
            position: absolute; inset: 0; background: rgba(0,0,0,.45);
        }
        .app-sheet__panel {
            position: absolute; left: 0; right: 0; bottom: 0;
            max-height: min(88vh, 720px);
            background: #fff; border-radius: 18px 18px 0 0;
            box-shadow: 0 -12px 40px rgba(0,0,0,.18);
            padding-bottom: calc(12px + env(safe-area-inset-bottom, 0));
            transform: translateY(100%);
            transition: transform .28s ease;
            overflow: auto;
        }
        .app-sheet.is-open .app-sheet__panel { transform: translateY(0); }
        .app-sheet__handle {
            width: 40px; height: 4px; border-radius: 999px; background: #d4d4d4;
            margin: 10px auto 6px;
        }
        /* Ecommerce install bar — fixed on all devices until installed */
        .ecom-pwa-install-bar {
            position: fixed !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 20px;
            z-index: 100;
            width: 100%;
            max-width: 100%;
            margin: 0;
            padding: 0 16px;
            box-sizing: border-box;
            pointer-events: none;
            display: flex;
            justify-content: center;
            /* Keep fixed layer stable on iOS Safari */
            -webkit-transform: translate3d(0, 0, 0);
            transform: translate3d(0, 0, 0);
            backface-visibility: hidden;
        }
        .ecom-pwa-install-bar[hidden] { display: none !important; }
        .ecom-pwa-install-bar:not([hidden]) { display: flex !important; }
        .ecom-pwa-install-bar__inner {
            pointer-events: auto;
            display: flex; align-items: center; gap: 14px;
            width: 100%;
            max-width: 560px;
            background: #1a1a1a; color: #fff;
            border-radius: 16px;
            padding: 16px 18px;
            box-shadow: 0 12px 36px rgba(0,0,0,.32);
            min-height: 72px;
            box-sizing: border-box;
        }
        .ecom-pwa-install-bar__logo {
            flex-shrink: 0;
            width: 48px;
            height: 48px;
            border-radius: 12px;
            object-fit: cover;
            background: #4CAF79;
            display: block;
        }
        .ecom-pwa-install-bar__text { flex: 1; min-width: 0; }
        .ecom-pwa-install-bar__text strong {
            display: block; font-size: 16px; font-weight: 800; line-height: 1.25;
            letter-spacing: -0.01em;
        }
        .ecom-pwa-install-bar__text span {
            display: block; font-size: 13px; color: rgba(255,255,255,.78); margin-top: 4px; line-height: 1.35;
        }
        .ecom-pwa-install-bar__btn {
            flex-shrink: 0; background: #4CAF79; color: #fff; border: 0;
            border-radius: 10px;
            padding: 12px 18px;
            font-size: 14px; font-weight: 800;
            cursor: pointer;
            min-height: 46px;
            line-height: 1.2;
        }
        .ecom-pwa-install-bar__close {
            flex-shrink: 0;
            width: 40px; height: 40px; border: 0; border-radius: 999px;
            background: rgba(255,255,255,.12); color: #fff;
            font-size: 22px; line-height: 1; cursor: pointer;
        }
        body.ecom-pwa-standalone .ecom-pwa-install-bar { display: none !important; }
        body.ecom-pwa-bar-visible main {
            padding-bottom: calc(100px + env(safe-area-inset-bottom, 0));
        }

        @media (max-width: 1023px) {
            body.app-mobile { overscroll-behavior-y: none; }
            body.app-mobile main { padding-bottom: calc(72px + env(safe-area-inset-bottom, 0)); }
            body.app-mobile.ecom-pwa-bar-visible main {
                padding-bottom: calc(72px + 78px + env(safe-area-inset-bottom, 0));
            }
            body.app-mobile .desktop-footer { display: none; }
            .product-card:hover { transform: none; box-shadow: none; }

            /* Mobile: sit above bottom tabs and stay fixed while scrolling */
            .ecom-pwa-install-bar {
                position: fixed !important;
                left: 0 !important;
                right: 0 !important;
                bottom: calc(64px + env(safe-area-inset-bottom, 0px)) !important;
                width: 100% !important;
                max-width: 100vw !important;
                padding: 0 10px 8px !important;
                z-index: 100 !important;
            }
            .ecom-pwa-install-bar__inner {
                max-width: none;
                min-height: 0;
                gap: 8px;
                padding: 8px 10px;
                border-radius: 10px;
                box-shadow: 0 6px 18px rgba(0,0,0,.28);
            }
            .ecom-pwa-install-bar__logo {
                width: 36px;
                height: 36px;
                border-radius: 9px;
            }
            .ecom-pwa-install-bar__text strong {
                font-size: 12px;
                font-weight: 700;
                line-height: 1.2;
            }
            .ecom-pwa-install-bar__text span {
                font-size: 10px;
                margin-top: 1px;
                line-height: 1.25;
            }
            .ecom-pwa-install-bar__btn {
                border-radius: 7px;
                padding: 7px 10px;
                font-size: 11px;
                font-weight: 700;
                min-height: 32px;
            }
            .ecom-pwa-install-bar__close {
                width: 28px;
                height: 28px;
                font-size: 16px;
            }

            /* Customer account panel — app layout (mobile only) */
            body.ecom-account-panel .ecom-acc {
                padding: 0;
                max-width: none;
                margin: 0;
            }
            body.ecom-account-panel .ecom-acc-top {
                position: sticky;
                top: calc(3.5rem + env(safe-area-inset-top, 0px));
                z-index: 35;
                display: flex;
                align-items: center;
                gap: 8px;
                min-height: 48px;
                padding: 8px 12px;
                background: rgba(255,255,255,.96);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                border-bottom: 1px solid #e8e8e8;
            }
            body.ecom-account-panel .ecom-acc-top__back {
                width: 40px; height: 40px; border-radius: 999px;
                display: inline-flex; align-items: center; justify-content: center;
                background: #f0f0f0; color: #1a1a1a; text-decoration: none; flex-shrink: 0;
                font-size: 18px; line-height: 1; border: 0;
            }
            body.ecom-account-panel .ecom-acc-top__title {
                flex: 1; min-width: 0;
                font-size: 16px; font-weight: 800; letter-spacing: -0.02em;
                white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            }
            body.ecom-account-panel .ecom-acc-hero {
                padding: 16px 16px 12px;
                background: linear-gradient(180deg, #fff 0%, #f7f7f7 100%);
            }
            body.ecom-account-panel .ecom-acc-hero__name {
                font-size: 22px; font-weight: 800; letter-spacing: -0.03em; line-height: 1.2;
            }
            body.ecom-account-panel .ecom-acc-hero__meta {
                margin-top: 6px; font-size: 13px; color: #6b6b6b;
            }
            body.ecom-account-panel .ecom-acc-list {
                margin: 0 12px 12px;
                background: #fff;
                border: 1px solid #e8e8e8;
                border-radius: 14px;
                overflow: hidden;
            }
            body.ecom-account-panel .ecom-acc-list a,
            body.ecom-account-panel .ecom-acc-list button {
                display: flex;
                align-items: center;
                gap: 12px;
                width: 100%;
                text-align: left;
                padding: 14px 14px;
                border: 0;
                background: #fff;
                border-bottom: 1px solid #f0f0f0;
                font-size: 15px;
                font-weight: 600;
                color: #1a1a1a;
                text-decoration: none;
                cursor: pointer;
                -webkit-tap-highlight-color: transparent;
            }
            body.ecom-account-panel .ecom-acc-list a:last-child,
            body.ecom-account-panel .ecom-acc-list form:last-child button {
                border-bottom: 0;
            }
            body.ecom-account-panel .ecom-acc-list a:active,
            body.ecom-account-panel .ecom-acc-list button:active {
                background: #f5f5f5;
            }
            body.ecom-account-panel .ecom-acc-list__icon {
                width: 34px; height: 34px; border-radius: 10px;
                display: inline-flex; align-items: center; justify-content: center;
                background: #e8f5ee; color: #2E6A49; flex-shrink: 0;
                font-size: 14px;
            }
            body.ecom-account-panel .ecom-acc-list__chev {
                margin-left: auto; color: #c0c0c0; font-weight: 400; font-size: 18px;
            }
            body.ecom-account-panel .ecom-acc-list__danger .ecom-acc-list__icon {
                background: #fdecec; color: #c0392b;
            }
            body.ecom-account-panel .ecom-acc-section {
                padding: 4px 16px 8px;
                font-size: 12px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .08em;
                color: #8a8a8a;
            }
            body.ecom-account-panel .ecom-acc-card {
                margin: 0 12px 12px;
                background: #fff;
                border: 1px solid #e8e8e8;
                border-radius: 14px;
                padding: 14px;
            }
            body.ecom-account-panel .ecom-acc-order {
                display: flex;
                flex-direction: column;
                gap: 4px;
                padding: 12px 14px;
                border-bottom: 1px solid #f0f0f0;
            }
            body.ecom-account-panel .ecom-acc-order:last-child { border-bottom: 0; }
            body.ecom-account-panel .ecom-acc-order__row {
                display: flex; justify-content: space-between; gap: 8px; align-items: center;
            }
            body.ecom-account-panel .ecom-acc-form .input-w,
            body.ecom-account-panel .ecom-acc-form input,
            body.ecom-account-panel .ecom-acc-form select,
            body.ecom-account-panel .ecom-acc-form textarea {
                border-radius: 12px !important;
                min-height: 46px;
                font-size: 15px;
            }
            body.ecom-account-panel .ecom-acc-form .btn-brand,
            body.ecom-account-panel .ecom-acc-form button[type=submit] {
                min-height: 48px;
                border-radius: 12px;
                width: 100%;
                font-weight: 700;
            }
            body.ecom-account-panel .ecom-acc-table-wrap {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            body.ecom-account-panel .ecom-acc-banner {
                margin: 0 12px 12px;
                border-radius: 12px;
                padding: 12px 14px;
                font-size: 13px;
                line-height: 1.4;
            }
        }
        @media (min-width: 1024px) {
            .app-mobile-only { display: none !important; }
            .app-bottom-nav { display: none !important; }
            .app-sheet { display: none !important; }
            body.ecom-account-panel .ecom-acc-top { display: none; }
            body.ecom-account-panel .ecom-acc-mobile-only { display: none !important; }
            body.ecom-account-panel .ecom-acc-desktop-only { display: block; }
        }
        @media (max-width: 1023px) {
            body.ecom-account-panel .ecom-acc-desktop-only { display: none !important; }
        }
    </style>
    @stack('head')
</head>
@php
    $nav_categories = $nav_categories ?? collect();
    $store = $ecom_settings->store_name ?? 'Store';
    $routeName = optional(request()->route())->getName();
    $isHome = request()->is('/') || $routeName === 'ecommerce.home';
    $isShop = in_array($routeName, ['ecommerce.shop', 'ecommerce.category', 'ecommerce.product', 'ecommerce.collection'], true);
    $isCart = $routeName === 'ecommerce.cart' || $routeName === 'ecommerce.checkout';
    $isAccount = str_starts_with((string) $routeName, 'ecommerce.account')
        || in_array($routeName, ['ecommerce.login', 'ecommerce.register', 'ecommerce.wishlist'], true);
    $isAccountPanel = str_starts_with((string) $routeName, 'ecommerce.account')
        || in_array($routeName, ['ecommerce.wishlist', 'ecommerce.quick_order'], true);

    $storeLogoUrl = null;
    $storeLogoCandidates = [];
    if (! empty($ecom_settings->store_logo)) {
        $storeLogoCandidates[] = $ecom_settings->store_logo;
    }
    try {
        $bizLogo = \App\Business::where('id', $ecom_business_id ?? null)->value('logo');
        if (! empty($bizLogo)) {
            $storeLogoCandidates[] = 'business_logos/'.$bizLogo;
        }
    } catch (\Throwable $e) {
        // ignore
    }
    foreach ($storeLogoCandidates as $rel) {
        $rel = ltrim(str_replace('\\', '/', (string) $rel), '/');
        if ($rel !== '' && file_exists(public_path('uploads/'.$rel))) {
            $storeLogoUrl = asset('uploads/'.$rel);
            break;
        }
    }
@endphp
<body class="antialiased app-mobile{{ ! empty($isAccountPanel) ? ' ecom-account-panel' : '' }}">
<a class="skip-link" href="#main-content">Skip to main content</a>
<div id="ageGate" class="age-gate-overlay" hidden>
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4 text-center">
        <div class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center text-white text-2xl font-bold" style="background:#2E6A49">{{ mb_substr($store,0,1) }}</div>
        <p class="font-display text-2xl font-bold mb-1" style="color:#2E6A49">{{ $store }}</p>
        <p class="text-sm mb-4" style="color:#666">Age Verification Required</p>
        <div class="rounded-xl p-4 mb-5 text-sm" style="background:#f4f4f4;color:#555">You must be 21 years of age or older to enter this site. Products are for licensed retailers only.</div>
        <p class="font-semibold mb-4" style="color:#1a1a1a">Are you 21 or older?</p>
        <div class="flex gap-3 justify-center">
            <button type="button" id="ageYes" class="px-6 py-3 rounded-lg text-white font-bold text-sm" style="background:#4CAF79">Yes, I am 21+</button>
            <a href="https://www.google.com" class="px-6 py-3 rounded-lg border font-semibold text-sm" style="border-color:#e0e0e0;color:#1a1a1a">No, Exit</a>
        </div>
        <p class="text-xs mt-4" style="color:#888"><a href="{{ route('ecommerce.terms') }}" class="underline" style="color:#1a73e8">Terms of Service</a> · <a href="{{ route('ecommerce.privacy') }}" class="underline" style="color:#1a73e8">Privacy Policy</a></p>
    </div>
</div>


@php
    $layoutAnnouncements = [];
    $layoutExtra = is_array($ecom_settings->extra ?? null)
        ? $ecom_settings->extra
        : (json_decode($ecom_settings->extra ?? '{}', true) ?: []);
    if (! empty($layoutExtra['announcements']) && is_array($layoutExtra['announcements'])) {
        foreach ($layoutExtra['announcements'] as $ann) {
            $text = trim((string) ($ann['text'] ?? $ann['t'] ?? ''));
            if ($text === '') {
                continue;
            }
            $cta = trim((string) ($ann['cta'] ?? $ann['a'] ?? 'Learn more'));
            if ($cta !== '' && ! str_ends_with($cta, '→') && ! str_ends_with($cta, '->')) {
                $cta .= ' →';
            }
            $layoutAnnouncements[] = [
                't' => $text,
                'a' => $cta !== '' ? $cta : 'Learn more →',
                'href' => $ann['href'] ?? route('ecommerce.shop'),
            ];
        }
    }
    if ($layoutAnnouncements === []) {
        $layoutAnnouncements = [
            ['t' => 'Summer Clearance — Up to 40% Off Select Items', 'a' => 'Shop Clearance →', 'href' => route('ecommerce.shop', ['on_sale' => 1])],
            ['t' => 'Now Carrying Lost Mary, RAZ & HQD — Top-Selling Disposables', 'a' => 'Shop Disposables →', 'href' => route('ecommerce.shop')],
            ['t' => 'New THCA & Delta-8 Arrivals — Premium Quality at Wholesale Prices', 'a' => 'View New Arrivals →', 'href' => route('ecommerce.shop', ['new_only' => 1, 'sort' => 'newest'])],
        ];
    }
    $announceFirst = $layoutAnnouncements[0];
    $tagline = $ecom_settings->seo_title ?: ($store.' — Wholesale Distribution');
    $contactEmail = $ecom_settings->contact_email ?: 'info@'.(parse_url(config('app.url'), PHP_URL_HOST) ?: 'japspos.test');
    $contactPhone = $ecom_settings->contact_phone ?: '';
    $hours = $layoutExtra['hours'] ?? 'Mon–Fri 9:30AM–5:30PM  |  Sat 10AM–4PM';
    $storeShort = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::limit(preg_replace('/\s+Wholesale.*/i', '', $store) ?: $store, 16, ''));
    $footerAddress = $layoutExtra['address'] ?? '1301 Glenview Pl';
    $footerCity = $layoutExtra['city_line'] ?? 'Wauwatosa, WI 53213';
    $footerPhone = $contactPhone ?: '(414) 252-0586';
    $footerPhoneTel = preg_replace('/\D+/', '', $footerPhone);
    $footerBlurb = $ecom_settings->seo_description
        ?: 'Wisconsin’s premier smoke & vape wholesale distributor. Serving licensed retailers with quality products at competitive prices since 2018.';
    $footerHours = $layoutExtra['hours_rows'] ?? [
        ['Mon – Fri', '9:30 AM – 5:30 PM'],
        ['Saturday', '10:00 AM – 4:00 PM'],
        ['Sunday', 'Closed', true],
    ];
@endphp

<header class="sticky top-0 z-50 shadow-sm" role="banner" id="storeHeader">
    {{-- Promotional announcements (matches Greencloud reference chrome) --}}
    <div class="text-white" style="background:#2E6A49" role="region" aria-label="Promotional announcements" aria-live="polite" id="layoutPromoAnnounce">
        <div class="max-w-screen-xl mx-auto px-4 h-[44px] flex items-center justify-center gap-3 text-sm font-semibold">
            <button type="button" class="opacity-70 hover:opacity-100" data-ann-prev aria-label="Previous announcement">‹</button>
            <a href="{{ $announceFirst['href'] }}" class="text-white no-underline truncate max-w-[min(100%,42rem)]" data-ann-text>{{ $announceFirst['t'] }}</a>
            <a href="{{ $announceFirst['href'] }}" class="rounded-full bg-white text-xs font-bold px-3 py-1 shrink-0" style="color:#2E6A49" data-ann-cta>{{ $announceFirst['a'] }}</a>
            <button type="button" class="opacity-70 hover:opacity-100" data-ann-next aria-label="Next announcement">›</button>
        </div>
    </div>

    {{-- Utility bar --}}
    <div class="hidden md:block text-xs py-1.5 px-4 text-white" style="background:#1a3d2b">
        <div class="max-w-screen-xl mx-auto flex items-center justify-between">
            <span class="opacity-80">{{ $tagline }}</span>
            <div class="flex items-center gap-6 opacity-90">
                @if($contactPhone)
                    <a href="tel:{{ preg_replace('/\D+/', '', $contactPhone) }}" class="hover:opacity-100 transition-opacity">{{ $contactPhone }}</a>
                @endif
                <a href="mailto:{{ $contactEmail }}" class="hover:opacity-100 transition-opacity">{{ $contactEmail }}</a>
                <span>{{ $hours }}</span>
            </div>
        </div>
    </div>

    {{-- Main bar: logo | search | account/cart --}}
    <div class="bg-white px-4 py-3">
        <div class="max-w-screen-xl mx-auto flex items-center gap-4">
            <button type="button" class="lg:hidden p-2 rounded-lg hover:bg-[#f0f9f4] open-cat-sheet" aria-label="Open menu" style="color:#2E6A49">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>

            <a href="{{ url('/') }}" class="flex items-center gap-2.5 shrink-0" aria-label="{{ $store }} homepage">
                @if($storeLogoUrl)
                    <img src="{{ $storeLogoUrl }}" alt="{{ $store }}" class="h-10 w-auto max-w-[160px] object-contain object-left">
                @else
                    <span class="h-10 w-10 rounded-full flex items-center justify-center text-white font-bold" style="background:#2E6A49">{{ mb_substr($store,0,1) }}</span>
                    <span class="hidden sm:block leading-tight">
                        <span class="block font-display font-bold" style="color:#2E6A49;font-size:1.1rem;letter-spacing:0.06em">{{ $storeShort }}</span>
                        <span class="block text-xs font-semibold tracking-[0.2em] uppercase" style="color:#4CAF79">Wholesale</span>
                    </span>
                @endif
            </a>

            <form action="{{ route('ecommerce.shop') }}" method="get" class="flex-1 hidden sm:block">
                <div class="relative">
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Search products, brands, categories..."
                           class="w-full border-2 rounded-lg px-4 py-2.5 pr-11 text-sm outline-none"
                           style="border-color:#e0e0e0"
                           onfocus="this.style.borderColor='#4CAF79'" onblur="this.style.borderColor='#e0e0e0'">
                    <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer" style="color:#2E6A49" aria-label="Search">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="8" r="5.5"/><path d="M12 12l4 4"/></svg>
                    </button>
                </div>
            </form>

            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ auth('customer')->check() ? route('ecommerce.account') : route('ecommerce.login') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg hover:bg-[#f0f9f4] text-sm font-semibold" style="color:#2E6A49">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="7" r="3"/><path d="M3 16c1.5-3 10.5-3 12 0"/></svg>
                    <span class="hidden md:inline">Account</span>
                </a>
                <a href="{{ route('ecommerce.cart') }}"
                   class="relative inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 hover:opacity-90 active:scale-95"
                   style="background:#4CAF79;color:white" aria-label="Cart — {{ $cart_totals['count'] ?? 0 }} items">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 4h12l-1 9H4L3 4z"/><path d="M7 7V4a2 2 0 014 0v3"/></svg>
                    <span class="hidden md:inline">Cart</span>
                    @if(($cart_totals['count'] ?? 0) > 0)
                        <span class="absolute -top-2 -right-2 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center" style="background:#e53e3e" aria-hidden="true">{{ ($cart_totals['count'] ?? 0) > 99 ? '99+' : $cart_totals['count'] }}</span>
                    @endif
                </a>
            </div>
        </div>
    </div>

    {{-- Mobile search --}}
    <div class="sm:hidden bg-white border-b border-[#e0e0e0] px-4 py-2">
        <form action="{{ route('ecommerce.shop') }}" method="get">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search products..."
                   class="w-full border-2 rounded-lg px-4 py-2 text-sm outline-none" style="border-color:#e0e0e0">
        </form>
    </div>

    {{-- Dark green menubar (Figma-style: few top items, rest under More) --}}
    @php
        $navBarCategories = ($nav_categories ?? collect())->take(6);
        $navMoreCategories = ($nav_categories ?? collect())->slice(6)->values();
    @endphp
    <nav class="hidden lg:block relative cat-nav-bar" style="background:#2E6A49" aria-label="Main navigation" id="catNavWrap">
        <div class="max-w-screen-xl mx-auto px-4">
            <ul class="cat-nav-track" role="menubar">
                <li role="none">
                    <a href="{{ route('ecommerce.shop', ['on_sale' => 1]) }}" role="menuitem"
                       class="promo-link inline-flex items-center gap-1.5"
                       style="color:white">🔥 Promotions</a>
                </li>
                <li role="none">
                    <a href="{{ route('ecommerce.shop', ['new_only' => 1, 'sort' => 'newest']) }}" role="menuitem"
                       class="cat-link inline-flex items-center gap-1.5"
                       style="color:white">New Arrivals</a>
                </li>
                @foreach($navBarCategories as $cat)
                    @php
                        $subs = $cat->sub_categories ?? collect();
                        $megaBrands = $cat->mega_brands ?? collect();
                    @endphp
                    <li class="mega" role="none">
                        <a href="{{ route('ecommerce.category', $cat->id) }}" role="menuitem"
                           class="cat-link inline-flex items-center gap-1.5 {{ (request('category') == $cat->id || (string) request()->route('slug') === (string) $cat->id) ? 'is-current' : '' }}"
                           style="color:white"
                           @if($subs->count() || $megaBrands->count()) aria-haspopup="menu" @endif>
                            {{ $cat->name }}
                            @if($subs->count() || $megaBrands->count())
                                <svg width="11" height="11" viewBox="0 0 20 20" fill="currentColor" class="opacity-70"><path d="M5 8l5 5 5-5"/></svg>
                            @endif
                        </a>
                        @if($subs->count() || $megaBrands->count())
                        <div class="mega-panel">
                            <div class="max-w-screen-xl mx-auto px-4 py-6 grid gap-6" style="grid-template-columns: 1.2fr {{ $megaBrands->count() ? '0.8fr' : '' }};">
                                <div>
                                    <div class="font-bold text-xs uppercase tracking-widest mb-3 pb-2 border-b" style="color:#2E6A49;border-color:#e8f5ee">{{ $cat->name }}</div>
                                    <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
                                        <a href="{{ route('ecommerce.category', $cat->id) }}" class="block font-semibold hover:text-[#4CAF79]" style="color:#1a1a1a">All {{ $cat->name }}</a>
                                        @foreach($subs->take(14) as $sub)
                                            <a href="{{ route('ecommerce.category', $sub->id) }}" class="block hover:text-[#4CAF79]" style="color:#555">{{ $sub->name }}</a>
                                        @endforeach
                                    </div>
                                </div>
                                @if($megaBrands->count())
                                <div>
                                    <div class="font-bold text-xs uppercase tracking-widest mb-3 pb-2 border-b" style="color:#2E6A49;border-color:#e8f5ee">Brands</div>
                                    <div class="space-y-2 text-sm">
                                        @foreach($megaBrands->take(8) as $brand)
                                            <a href="{{ route('ecommerce.shop', ['category' => $cat->id, 'brand' => $brand->id]) }}" class="block hover:text-[#4CAF79]" style="color:#555">{{ $brand->name }}</a>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                            <div class="max-w-screen-xl mx-auto px-4 pb-4">
                                <a href="{{ route('ecommerce.category', $cat->id) }}" class="text-sm font-semibold" style="color:#1a73e8">View all in {{ $cat->name }} →</a>
                            </div>
                        </div>
                        @endif
                    </li>
                @endforeach
                @if($navMoreCategories->count())
                    <li class="mega" role="none">
                        <a href="{{ route('ecommerce.categories') }}" role="menuitem"
                           class="cat-link inline-flex items-center gap-1.5" style="color:white" aria-haspopup="menu">
                            More
                            <svg width="11" height="11" viewBox="0 0 20 20" fill="currentColor" class="opacity-70"><path d="M5 8l5 5 5-5"/></svg>
                        </a>
                        <div class="mega-panel">
                            <div class="max-w-screen-xl mx-auto px-4 py-6">
                                <div class="font-bold text-xs uppercase tracking-widest mb-3 pb-2 border-b" style="color:#2E6A49;border-color:#e8f5ee">More categories</div>
                                <div class="grid grid-cols-3 md:grid-cols-4 gap-x-6 gap-y-2 text-sm">
                                    @foreach($navMoreCategories as $cat)
                                        <a href="{{ route('ecommerce.category', $cat->id) }}" class="block hover:text-[#4CAF79]" style="color:#555">{{ $cat->name }}</a>
                                    @endforeach
                                    <a href="{{ route('ecommerce.categories') }}" class="block font-semibold" style="color:#1a73e8">View all categories →</a>
                                </div>
                            </div>
                        </div>
                    </li>
                @endif
            </ul>
        </div>
    </nav>
</header>

@if(session('success'))
    <div class="bg-emerald-50 text-emerald-900 text-sm text-center py-2.5 border-b border-emerald-100">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="bg-rose-50 text-rose-900 text-sm text-center py-2.5 border-b border-rose-100">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="bg-rose-50 text-rose-900 text-sm text-center py-2.5 border-b border-rose-100">{{ $errors->first() }}</div>
@endif

<main id="main-content">@yield('content')</main>


<footer style="background:#1a3d2b;color:#d1e8da" role="contentinfo">
    <div class="max-w-screen-xl mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8">
            {{-- Brand + contact --}}
            <div class="lg:col-span-2">
                <a href="{{ url('/') }}" class="flex items-center gap-3 mb-4" aria-label="{{ $store }} homepage">
                    @if($storeLogoUrl)
                        <img src="{{ $storeLogoUrl }}" alt="{{ $store }}" class="h-11 w-auto max-w-[160px] object-contain">
                    @else
                        <span class="h-11 w-11 rounded-full flex items-center justify-center text-white font-bold shrink-0" style="background:#4CAF79" aria-hidden="true">{{ mb_substr($store,0,1) }}</span>
                        <div>
                            <div class="font-display font-bold text-white text-lg tracking-wide">{{ $storeShort ?? $store }}</div>
                            <div class="text-xs tracking-[0.18em] uppercase" style="color:#4CAF79">Wholesale</div>
                        </div>
                    @endif
                </a>
                <p class="text-sm leading-relaxed mb-6 opacity-80 max-w-xs">{{ $footerBlurb }}</p>

                <address class="not-italic text-sm space-y-2 opacity-90" aria-label="Contact information">
                    <div class="flex items-start gap-2">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="currentColor" class="mt-0.5 shrink-0 opacity-70" aria-hidden="true">
                            <path d="M7 1C4.79 1 3 2.79 3 5C3 8.5 7 13 7 13C7 13 11 8.5 11 5C11 2.79 9.21 1 7 1ZM7 6.5C6.17 6.5 5.5 5.83 5.5 5C5.5 4.17 6.17 3.5 7 3.5C7.83 3.5 8.5 4.17 8.5 5C8.5 5.83 7.83 6.5 7 6.5Z"/>
                        </svg>
                        <span>{{ $footerAddress }}<br>{{ $footerCity }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="currentColor" class="opacity-70 shrink-0" aria-hidden="true">
                            <path d="M2 1h3l1 3-1.5 1.5a9 9 0 003 3L9 7l3 1v3a2 2 0 01-2 2C5.5 13 1 8.5 1 3a2 2 0 011-2z"/>
                        </svg>
                        <a href="tel:{{ $footerPhoneTel }}" class="hover:text-white transition-colors">{{ $footerPhone }}</a>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="currentColor" class="opacity-70 shrink-0" aria-hidden="true">
                            <rect x="1" y="3" width="12" height="8" rx="1" stroke="currentColor" stroke-width="1" fill="none"/>
                            <path d="M1 4l6 4.5L13 4" stroke="currentColor" stroke-width="1"/>
                        </svg>
                        <a href="mailto:{{ $contactEmail }}" class="hover:text-white transition-colors break-all">{{ $contactEmail }}</a>
                    </div>
                </address>

                <div class="mt-4 text-sm" aria-label="Business hours">
                    <p class="font-semibold text-white mb-1.5">Hours</p>
                    <dl class="space-y-0.5 opacity-80">
                        @foreach($footerHours as $row)
                            <div class="flex gap-3">
                                <dt>{{ $row[0] }}</dt>
                                <dd @if(! empty($row[2])) class="text-[#e57373]" @endif>{{ $row[1] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </div>

            {{-- Shop --}}
            <div>
                <h3 class="font-display font-semibold text-white text-sm mb-4 tracking-wide">Shop</h3>
                <ul class="space-y-2">
                    <li><a class="text-sm opacity-80 hover:opacity-100 hover:text-white transition-all hover:pl-1 block" href="{{ route('ecommerce.shop', ['new_only' => 1, 'sort' => 'newest']) }}">New Arrivals</a></li>
                    @foreach(($nav_categories ?? collect())->take(5) as $cat)
                        <li><a class="text-sm opacity-80 hover:opacity-100 hover:text-white transition-all hover:pl-1 block" href="{{ route('ecommerce.category', $cat->id) }}">{{ $cat->name }}</a></li>
                    @endforeach
                    <li><a class="text-sm opacity-80 hover:opacity-100 hover:text-white transition-all hover:pl-1 block" href="{{ route('ecommerce.brands') }}">All Brands</a></li>
                    <li><a class="text-sm opacity-80 hover:opacity-100 hover:text-white transition-all hover:pl-1 block" href="{{ route('ecommerce.shop', ['on_sale' => 1]) }}">Promotions & Deals</a></li>
                </ul>
            </div>

            {{-- Account --}}
            <div>
                <h3 class="font-display font-semibold text-white text-sm mb-4 tracking-wide">Account</h3>
                <ul class="space-y-2">
                    <li><a class="text-sm opacity-80 hover:opacity-100 hover:text-white transition-all hover:pl-1 block" href="{{ route('ecommerce.account') }}">My Account</a></li>
                    <li><a class="text-sm opacity-80 hover:opacity-100 hover:text-white transition-all hover:pl-1 block" href="{{ route('ecommerce.account.orders') }}">Order History</a></li>
                    <li><a class="text-sm opacity-80 hover:opacity-100 hover:text-white transition-all hover:pl-1 block" href="{{ route('ecommerce.account.addresses') }}">Saved Addresses</a></li>
                    <li><a class="text-sm opacity-80 hover:opacity-100 hover:text-white transition-all hover:pl-1 block" href="{{ route('ecommerce.register') }}">Wholesale Application</a></li>
                    <li><a class="text-sm opacity-80 hover:opacity-100 hover:text-white transition-all hover:pl-1 block" href="{{ route('ecommerce.cart') }}">Shopping Cart</a></li>
                    <li><a class="text-sm opacity-80 hover:opacity-100 hover:text-white transition-all hover:pl-1 block" href="{{ route('ecommerce.checkout') }}">Checkout</a></li>
                </ul>
            </div>

            {{-- Company --}}
            <div>
                <h3 class="font-display font-semibold text-white text-sm mb-4 tracking-wide">Company</h3>
                <ul class="space-y-2">
                    <li><a class="text-sm opacity-80 hover:opacity-100 hover:text-white transition-all hover:pl-1 block" href="{{ route('ecommerce.about') }}">About Us</a></li>
                    <li><a class="text-sm opacity-80 hover:opacity-100 hover:text-white transition-all hover:pl-1 block" href="{{ route('ecommerce.faq') }}">Wholesale FAQ</a></li>
                    <li><a class="text-sm opacity-80 hover:opacity-100 hover:text-white transition-all hover:pl-1 block" href="{{ route('ecommerce.shipping') }}">Shipping Policy</a></li>
                    <li><a class="text-sm opacity-80 hover:opacity-100 hover:text-white transition-all hover:pl-1 block" href="{{ route('ecommerce.returns') }}">Return Policy</a></li>
                    <li><a class="text-sm opacity-80 hover:opacity-100 hover:text-white transition-all hover:pl-1 block" href="{{ route('ecommerce.privacy') }}">Privacy Policy</a></li>
                    <li><a class="text-sm opacity-80 hover:opacity-100 hover:text-white transition-all hover:pl-1 block" href="{{ route('ecommerce.terms') }}">Terms of Service</a></li>
                    <li><a class="text-sm opacity-80 hover:opacity-100 hover:text-white transition-all hover:pl-1 block" href="{{ route('ecommerce.compliance') }}">Age Compliance</a></li>
                    <li><a class="text-sm opacity-80 hover:opacity-100 hover:text-white transition-all hover:pl-1 block" href="{{ route('ecommerce.contact') }}">Contact Us</a></li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Newsletter --}}
    <div class="border-t border-[rgba(255,255,255,0.08)]" style="background:#15301f">
        <div class="max-w-screen-xl mx-auto px-4 py-6">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <p class="font-semibold text-white text-sm">Stay informed — new arrivals, deals & industry news</p>
                    <p class="text-xs opacity-60 mt-0.5">For licensed wholesale accounts only.</p>
                </div>
                <form method="post" action="{{ route('ecommerce.subscribe') }}" class="flex gap-2 w-full md:w-auto" aria-label="Newsletter signup">
                    @csrf
                    <label for="footer-email" class="sr-only">Email address</label>
                    <input id="footer-email" type="email" name="email" required placeholder="Your business email"
                           class="flex-1 md:w-64 px-4 py-2.5 rounded-lg text-sm text-[#1a1a1a] focus:outline-none focus:ring-2 focus:ring-[#4CAF79]"
                           style="background:rgba(255,255,255,0.92)">
                    <button type="submit" class="px-5 py-2.5 rounded-lg text-sm font-semibold text-white transition-all hover:opacity-90 active:scale-95 shrink-0" style="background:#4CAF79">Subscribe</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Legal --}}
    <div class="border-t border-[rgba(255,255,255,0.06)]" style="background:#0f2418">
        <div class="max-w-screen-xl mx-auto px-4 py-4 flex flex-col md:flex-row items-center justify-between gap-2 text-xs opacity-60">
            <p>© {{ date('Y') }} {{ $store }}. All rights reserved. &nbsp;|&nbsp; {{ $footerCity }}</p>
            <div class="flex items-center gap-4">
                <span class="inline-flex items-center gap-1">
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="currentColor" aria-hidden="true"><path d="M10 2H2a1 1 0 00-1 1v6a1 1 0 001 1h8a1 1 0 001-1V3a1 1 0 00-1-1zm0 2L6 7 2 4V3l4 3 4-3v1z"/></svg>
                    For wholesale inquiries only
                </span>
                <span class="inline-flex items-center gap-1">
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="currentColor" aria-hidden="true"><path d="M6 1a5 5 0 100 10A5 5 0 006 1zm0 2v4l3 1.5-.6 1L5 8V3h1z"/></svg>
                    21+ only — age-restricted products
                </span>
            </div>
        </div>
    </div>
</footer>


{{-- Mobile bottom tabs --}}
<nav class="app-bottom-nav app-mobile-only" aria-label="Primary">
    <div class="flex items-stretch">
        <a href="{{ url('/') }}" class="app-tab {{ $isHome ? 'active' : '' }}">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 10.5L12 3l9 7.5V20a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1v-9.5z"/></svg>
            Home
        </a>
        <a href="{{ route('ecommerce.shop') }}" class="app-tab {{ $isShop ? 'active' : '' }}">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            Shop
        </a>
        <button type="button" class="app-tab open-cat-sheet">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            Menu
        </button>
        <a href="{{ route('ecommerce.cart') }}" class="app-tab {{ $isCart ? 'active' : '' }}">
            <span class="relative inline-flex">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 4h12l-1 9H4L3 4z"/><path d="M7 7V4a2 2 0 014 0v3"/></svg>
                @if(($cart_totals['count'] ?? 0) > 0)
                    <span class="absolute -top-1 -right-2 bg-brand text-white text-[9px] font-bold rounded-full min-w-[14px] h-[14px] inline-flex items-center justify-center px-0.5">{{ $cart_totals['count'] }}</span>
                @endif
            </span>
            Cart
        </a>
        <a href="{{ auth('customer')->check() ? route('ecommerce.account') : route('ecommerce.login') }}"
           class="app-tab {{ $isAccount ? 'active' : '' }}">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="8" r="3.5"/><path d="M4 19c1.8-3.5 12.2-3.5 14 0"/></svg>
            Account
        </a>
    </div>
</nav>

{{-- Download app bar last in DOM so it paints above tabs (fixed on mobile + desktop) --}}
<div id="ecom_pwa_install_bar" class="ecom-pwa-install-bar" hidden aria-hidden="true" role="region" aria-label="Install app">
    <div class="ecom-pwa-install-bar__inner">
        <img src="{{ asset('pwa/ecom-icon-192.png') }}" alt="" class="ecom-pwa-install-bar__logo" width="48" height="48">
        <div class="ecom-pwa-install-bar__text">
            <strong>Download this app</strong>
            <span>Install for a faster mobile experience</span>
        </div>
        <button type="button" id="ecom_pwa_install_btn" class="ecom-pwa-install-bar__btn">Download</button>
        <button type="button" id="ecom_pwa_dismiss_btn" class="ecom-pwa-install-bar__close" aria-label="Dismiss">×</button>
    </div>
</div>

{{-- Mobile menu sheet --}}
<div id="catSheet" class="app-sheet app-mobile-only" aria-hidden="true">
    <div class="app-sheet__backdrop" data-close-sheet></div>
    <div class="app-sheet__panel" role="dialog" aria-label="Menu">
        <div class="app-sheet__handle"></div>
        <div class="px-4 pb-2">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-extrabold">Browse</h2>
                <button type="button" class="h-9 w-9 rounded-full bg-mist inline-flex items-center justify-center" data-close-sheet aria-label="Close">✕</button>
            </div>
            <div class="grid grid-cols-2 gap-2 mb-4">
                <a href="{{ route('ecommerce.quick_order') }}" class="rounded-xl border border-line bg-mist px-3 py-3 font-semibold text-sm">Quick Order</a>
                <a href="{{ route('ecommerce.shop', ['on_sale' => 1]) }}" class="rounded-xl border border-line bg-mist px-3 py-3 font-semibold text-sm">Promotions</a>
                <a href="{{ route('ecommerce.brands') }}" class="rounded-xl border border-line bg-mist px-3 py-3 font-semibold text-sm">Brands</a>
                <a href="{{ route('ecommerce.contact') }}" class="rounded-xl border border-line bg-mist px-3 py-3 font-semibold text-sm">Contact</a>
                @auth('customer')
                    <a href="{{ route('ecommerce.account') }}" class="rounded-xl border border-line bg-mist px-3 py-3 font-semibold text-sm">My account</a>
                    <a href="{{ route('ecommerce.contact') }}" class="rounded-xl border border-line bg-mist px-3 py-3 font-semibold text-sm">Contact</a>
                @else
                    <a href="{{ route('ecommerce.login') }}" class="rounded-xl border border-line bg-mist px-3 py-3 font-semibold text-sm">Login</a>
                    <a href="{{ route('ecommerce.register') }}" class="rounded-xl border border-line bg-mist px-3 py-3 font-semibold text-sm text-brand">Sign Up</a>
                @endauth
            </div>
            <div class="text-xs font-bold uppercase tracking-wider text-black/40 mb-2">Departments</div>
            <div class="divide-y divide-line rounded-xl border border-line overflow-hidden bg-white">
                @foreach($nav_categories as $cat)
                    <a href="{{ route('ecommerce.category', $cat->id) }}" class="flex items-center justify-between px-4 py-3.5 font-semibold text-[15px] active:bg-mist">
                        <span>{{ $cat->name }}</span>
                        <span class="text-black/30">›</span>
                    </a>
                @endforeach
            </div>
            <p class="text-[11px] text-black/40 text-center mt-4 px-2">
                WARNING: This product contains nicotine. Nicotine is an addictive chemical.
            </p>
        </div>
    </div>
</div>

<script>
(function () {
    const sheet = document.getElementById('catSheet');
    const open = () => {
        if (!sheet) return;
        sheet.classList.add('is-open');
        sheet.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    };
    const close = () => {
        if (!sheet) return;
        sheet.classList.remove('is-open');
        sheet.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };
    document.querySelectorAll('.open-cat-sheet').forEach(btn => btn.addEventListener('click', open));
    sheet?.querySelectorAll('[data-close-sheet]').forEach(el => el.addEventListener('click', close));
})();

(function () {
    const header = document.getElementById('storeHeader');
    if (!header) return;
    const onScroll = () => header.classList.toggle('is-scrolled', window.scrollY > 4);
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
})();

(function () {
    const wrap = document.getElementById('catNavWrap');
    if (!wrap) return;
    const items = [...wrap.querySelectorAll('.mega')];
    const panels = [...wrap.querySelectorAll('.mega-panel')];

    function closeAll() {
        panels.forEach(p => p.classList.remove('is-open'));
        items.forEach(i => i.classList.remove('is-active'));
    }

    items.forEach(item => {
        item.addEventListener('mouseenter', () => {
            closeAll();
            item.classList.add('is-active');
            item.querySelector('.mega-panel')?.classList.add('is-open');
        });
    });

    wrap.addEventListener('mouseleave', closeAll);
})();

document.querySelectorAll('.product-card-form').forEach(form => {
    const input = form.querySelector('.pc-qty-input');
    const minus = form.querySelector('.pc-qty-minus');
    const plus = form.querySelector('.pc-qty-plus');
    if (!input || !minus || !plus) return;
    const setQty = (v) => { input.value = Math.max(1, parseInt(v, 10) || 1); };
    minus.addEventListener('click', () => setQty((parseInt(input.value, 10) || 1) - 1));
    plus.addEventListener('click', () => setQty((parseInt(input.value, 10) || 1) + 1));
    input.addEventListener('change', () => setQty(input.value));
});
</script>
@stack('scripts')
@php
    $ecomPwaAssetVersion = preg_replace(
        '/[^a-zA-Z0-9._-]/',
        '',
        (string) config('app.asset_version', config('constants.asset_version', '1'))
    );
    $ecomPwaCacheName = 'japspos-ecom-pwa-v'.$ecomPwaAssetVersion;
@endphp
<script>
window.__ECOM_PWA__ = {
    swUrl: @json(url('/ecom-pwa/sw.js')),
    manifestUrl: @json(url('/ecom-pwa/manifest.webmanifest')),
    cacheName: @json($ecomPwaCacheName),
    iosHint: 'On iPhone/iPad: tap Share → Add to Home Screen.',
    installHint: 'Use your browser menu → Install app / Add to Home Screen.'
};
</script>
<script src="{{ asset('js/ecom-pwa.js') }}?v={{ $ecomPwaAssetVersion }}" defer></script>
<script>
(function () {
    const key = 'gc_age_ok';
    const gate = document.getElementById('ageGate');
    if (!gate) return;
    if (!localStorage.getItem(key)) {
        gate.hidden = false;
        document.body.style.overflow = 'hidden';
    }
    document.getElementById('ageYes')?.addEventListener('click', function () {
        localStorage.setItem(key, '1');
        gate.hidden = true;
        document.body.style.overflow = '';
    });
})();
(function () {
    const msgs = @json($layoutAnnouncements);
    let i = 0;
    const text = document.querySelector('#layoutPromoAnnounce [data-ann-text]');
    const cta = document.querySelector('#layoutPromoAnnounce [data-ann-cta]');
    if (!text || !cta || !msgs.length) return;
    const show = () => {
        const m = msgs[i];
        text.textContent = m.t;
        text.setAttribute('href', m.href || '#');
        cta.textContent = m.a;
        cta.setAttribute('href', m.href || '#');
    };
    document.querySelector('#layoutPromoAnnounce [data-ann-next]')?.addEventListener('click', () => {
        i = (i + 1) % msgs.length;
        show();
    });
    document.querySelector('#layoutPromoAnnounce [data-ann-prev]')?.addEventListener('click', () => {
        i = (i + msgs.length - 1) % msgs.length;
        show();
    });
})();
</script>
</body>
</html>
