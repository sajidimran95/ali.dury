<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#070b16">
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700;800;900&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/gc-storefront.css') }}?v={{ config('app.asset_version', '1') }}&gc=19">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: '#f23488',
                        brandDark: '#f00678',
                        ink: '#eef2ff',
                        slatebar: '#070b16',
                        mist: '#10162a',
                        line: '#2a3358'
                    },
                    fontFamily: {
                        sans: ['Inter', 'Poppins', 'system-ui', 'sans-serif'],
                        serif: ['Bebas Neue', 'Impact', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: Inter, Poppins, system-ui, sans-serif; background: #070b16; color: #eef2ff; }
        .mega.is-active > .cat-link, .cat-link:hover { color: #fff !important; }
        .btn-brand { background: #f23488; color: #fff; font-weight: 600; border-radius: 8px; padding: .7rem 1.25rem; }
        .btn-brand:hover { background: #ff4b98; }
        .input-w:focus { border-color: #f23488; box-shadow: 0 0 0 3px rgba(242,52,136,.18); }
        .app-tab.active, .app-tab:active { color: #f23488; }
        .app-chip.active { background: #241428; color: #ff8fc8; border-color: #f23488; }
        .cat-nav-wrap {
            position: relative;
            overflow-x: auto;
            overflow-y: hidden;
            scrollbar-width: none;
        }
        .cat-nav-wrap::-webkit-scrollbar { display: none; }
        .gc-nav { overflow: visible; }
        .gc-menubar {
            display: flex; flex-wrap: nowrap; align-items: stretch; justify-content: flex-start;
            width: max-content; min-width: 100%; margin: 0; padding: 0; list-style: none;
        }
        .gc-menubar > li { flex: 0 0 auto; min-width: auto; position: relative; }
        .mega { position: relative; }
        .mega > a { position: relative; z-index: 2; }
        .cat-link.gc-nav-item {
            display: flex; align-items: center; justify-content: center; gap: 6px;
            height: 48px; padding: 0 14px; color: #d7def8;
            font-size: 12px; font-weight: 700; line-height: 1; text-align: center;
            white-space: nowrap; letter-spacing: .03em; text-transform: uppercase;
        }
        .cat-link.gc-nav-item:hover, .mega:hover > .cat-link, .mega.is-active > .cat-link { background: rgba(255,78,200,.12); color: #fff !important; }
        .gc-nav-item .gc-nav-text { display: block; }
        .mega-panel {
            display: none;
            position: absolute;
            left: 0;
            right: auto;
            top: 100%;
            z-index: 120;
            min-width: 220px;
            width: max-content;
            max-width: 280px;
            background: #12182e;
            color: #eef2ff;
            border: 1px solid #2a3358;
            box-shadow: 0 16px 40px rgba(0,0,0,.16);
            border-radius: 0 0 10px 10px;
            padding: 8px 0;
        }
        .mega:hover > .mega-panel,
        .mega.is-open > .mega-panel,
        .mega-panel.is-open { display: block; }
        .mega-panel a { display: block; padding: 8px 16px; font-size: 13px; color: #d7def8; }
        .mega-panel a:hover { background: #1a2140; color: #ff8fc8; }
        .brand-tile {
            display: flex; align-items: center; justify-content: center;
            min-height: 64px; border: 1px solid #ececec; border-radius: 8px;
            background: #fafafa; padding: 8px; text-align: center;
            transition: border-color .15s, box-shadow .15s;
        }
        .brand-tile:hover { border-color: #3d9b6d; box-shadow: 0 2px 10px rgba(61,155,109,.12); }
        .input-w {
            width: 100%; border: 1px solid #d4d4d4; border-radius: 8px; padding: .7rem .9rem; font-size: .875rem;
            background: #fff; outline: none;
        }
        .pc-qty-input::-webkit-outer-spin-button,
        .pc-qty-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        .pc-qty-input { -moz-appearance: textfield; }
        body.gc-store .wn-add,
        body.ecom-account-panel .wn-add,
        body.ecom-account-panel a.wn-add,
        body.ecom-account-panel button.wn-add,
        body.ecom-account-panel .product-card-form .ecom-add-cart {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            flex: 1 1 auto;
            height: 36px;
            padding: 0 10px;
            border: 0;
            border-radius: 6px;
            background: #0066f0 !important;
            color: #fff !important;
            -webkit-text-fill-color: #fff !important;
            text-decoration: none !important;
            font-weight: 800;
            font-size: 11px;
            letter-spacing: .06em;
            cursor: pointer;
        }
        body.ecom-account-panel .wn-add:hover {
            background: #1a78ff !important;
            color: #fff !important;
        }
        .product-card { transition: box-shadow .2s, transform .2s; }
        .product-card:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(0,0,0,.08); }

        /* App-like mobile shell */
        .app-bottom-nav {
            display: flex; flex-direction: column;
            position: fixed; left: 0; right: 0; bottom: 0; z-index: 90;
            background: rgba(7,11,22,.96);
            backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px);
            border-top: 1px solid #2a3358;
            padding-bottom: env(safe-area-inset-bottom, 0);
        }
        .app-tab {
            flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: 2px; min-height: 56px; color: #8b95b8; font-size: 10px; font-weight: 600;
            text-decoration: none; background: transparent; border: 0; cursor: pointer;
            -webkit-tap-highlight-color: transparent;
        }
        .app-tab.active, .app-tab:active { color: #f23488; }
        .app-chip-scroll {
            display: flex; gap: 8px; overflow-x: auto; -webkit-overflow-scrolling: touch;
            scrollbar-width: none; padding: 0 16px 10px;
        }
        .app-chip-scroll::-webkit-scrollbar { display: none; }
        .app-chip {
            flex: 0 0 auto; padding: 8px 14px; border-radius: 999px; background: #151a2e;
            font-size: 13px; font-weight: 600; color: #eef2ff; white-space: nowrap;
            border: 1px solid transparent; text-decoration: none;
        }
        .app-chip.active { background: #241428; color: #ff8fc8; border-color: #f23488; }
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
            background: #10162a; border-radius: 18px 18px 0 0;
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
            background: #e85d04;
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
            flex-shrink: 0; background: #e85d04; color: #fff; border: 0;
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
                background: rgba(7,11,22,.96);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                border-bottom: 1px solid #252d4d;
            }
            body.ecom-account-panel .ecom-acc-top__back {
                width: 40px; height: 40px; border-radius: 999px;
                display: inline-flex; align-items: center; justify-content: center;
                background: #1a2140; color: #eef2ff; text-decoration: none; flex-shrink: 0;
                font-size: 18px; line-height: 1; border: 0;
            }
            body.ecom-account-panel .ecom-acc-top__title {
                flex: 1; min-width: 0;
                font-size: 16px; font-weight: 800; letter-spacing: -0.02em;
                white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            }
            body.ecom-account-panel .ecom-acc-hero {
                padding: 16px 16px 12px;
                background: linear-gradient(180deg, #12182e 0%, #0b1020 100%);
            }
            body.ecom-account-panel .ecom-acc-hero__name {
                font-size: 22px; font-weight: 800; letter-spacing: -0.03em; line-height: 1.2;
            }
            body.ecom-account-panel .ecom-acc-hero__meta {
                margin-top: 6px; font-size: 13px; color: #8b95b8;
            }
            body.ecom-account-panel .ecom-acc-list {
                margin: 0 12px 12px;
                background: #12182e;
                border: 1px solid #252d4d;
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
                background: #12182e;
                border-bottom: 1px solid #252d4d;
                font-size: 15px;
                font-weight: 600;
                color: #eef2ff;
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
                background: #1a2140;
            }
            body.ecom-account-panel .ecom-acc-list__icon {
                width: 34px; height: 34px; border-radius: 10px;
                display: inline-flex; align-items: center; justify-content: center;
                background: #241428; color: #f23488; flex-shrink: 0;
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
                background: #12182e;
                border: 1px solid #252d4d;
                border-radius: 14px;
                padding: 14px;
            }
            body.ecom-account-panel .ecom-acc-order {
                display: flex;
                flex-direction: column;
                gap: 4px;
                padding: 12px 14px;
                border-bottom: 1px solid #252d4d;
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
            body.ecom-account-panel .ecom-acc-form button[type=submit]:not(.wn-add) {
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
            .hidden.lg\:block { display: none !important; }
            header.app-mobile-only { display: block !important; }
            .app-bottom-nav { display: flex !important; }
            .app-sheet.app-mobile-only { display: none; }
            .app-sheet.app-mobile-only.is-open { display: block; }
        }
    </style>
    @stack('head')
</head>
@php
    $nav_categories = $nav_categories ?? collect();
    $new_arrivals_category = $new_arrivals_category ?? null;
    $has_new_arrivals = $has_new_arrivals ?? false;
    $has_promotions = $has_promotions ?? false;
    $store = $ecom_settings->store_name ?? 'Store';
    $storeEmail = trim((string) ($ecom_settings->contact_email ?? ''));
    $storePhone = trim((string) ($ecom_settings->contact_phone ?? ''));
    $storeLogoUrl = null;
    $storeLogoCandidates = [];
    if (! empty($ecom_settings->store_logo)) {
        $storeLogoCandidates[] = $ecom_settings->store_logo;
    }
    try {
        $loc = null;
        if (! empty($ecom_settings->location_id)) {
            $loc = \App\BusinessLocation::find($ecom_settings->location_id);
        }
        if ($storePhone === '' && $loc) {
            $storePhone = trim((string) ($loc->mobile ?: $loc->alternate_number ?: ''));
        }
        if ($storeEmail === '' && $loc && ! empty($loc->email)) {
            $storeEmail = trim((string) $loc->email);
        }
        $biz = \App\Business::find($ecom_settings->business_id ?? null);
        if ($biz && ! empty($biz->logo)) {
            $storeLogoCandidates[] = 'business_logos/'.$biz->logo;
        }
    } catch (\Throwable $e) {
        // ignore
    }
    $storePhoneHref = $storePhone !== '' ? preg_replace('/[^0-9+]/', '', $storePhone) : '';
    $extra = is_array($ecom_settings->extra ?? null) ? $ecom_settings->extra : [];
    $storeHours = trim((string) ($extra['store_hours'] ?? ''));
    if ($storeHours === '') {
        $storeHours = 'Mon–Fri 9:30AM–5:30PM  |  Sat 10AM–4PM';
    }
    foreach ($storeLogoCandidates as $rel) {
        $rel = ltrim(str_replace('\\', '/', (string) $rel), '/');
        if ($rel !== '' && file_exists(public_path('uploads/'.$rel))) {
            $storeLogoUrl = asset('uploads/'.$rel);
            break;
        }
    }
    $routeName = optional(request()->route())->getName();
    $isHome = $routeName === 'ecommerce.home';
    $isShop = in_array($routeName, ['ecommerce.shop', 'ecommerce.category', 'ecommerce.product', 'ecommerce.collection'], true);
    $isCart = $routeName === 'ecommerce.cart' || $routeName === 'ecommerce.checkout';
    $isAccount = str_starts_with((string) $routeName, 'ecommerce.account')
        || in_array($routeName, ['ecommerce.login', 'ecommerce.register', 'ecommerce.wishlist'], true);
    $isAccountPanel = str_starts_with((string) $routeName, 'ecommerce.account')
        || in_array($routeName, ['ecommerce.wishlist', 'ecommerce.quick_order'], true);
@endphp
<body class="antialiased app-mobile gc-store{{ ! empty($isAccountPanel) ? ' ecom-account-panel' : '' }}">

<div id="gcAgeGate" class="gc-age" role="dialog" aria-label="Age verification">
    <div class="gc-age__card">
        <div class="mx-auto mb-4 h-14 w-14 rounded-full bg-[#f23488] flex items-center justify-center text-2xl">21+</div>
        <h2 class="gc-serif text-2xl mb-2">Age Verification Required</h2>
        <p class="text-sm text-white/75 mb-1">You must be 21 years of age or older to enter this site.</p>
        <p class="text-xs text-white/55 mb-5">This site sells tobacco, nicotine, hemp and age-restricted products for licensed retailers only.</p>
        <p class="font-semibold mb-4">Are you 21 or older?</p>
        <div class="grid gap-2">
            <button type="button" id="gcAgeYes" class="rounded-lg bg-[#f23488] text-white font-bold py-3">Yes, I am 21 or older — enter site</button>
            <button type="button" id="gcAgeNo" class="rounded-lg border border-white/25 text-white/80 py-3">No, I am under 21 — exit site</button>
        </div>
        <p class="text-[11px] text-white/45 mt-4">By entering, you confirm you are 21+ and agree to our
            <a href="{{ route('ecommerce.page', 'terms-of-service') }}" class="underline">Terms of Service</a> and
            <a href="{{ route('ecommerce.page', 'privacy-policy') }}" class="underline">Privacy Policy</a>.</p>
    </div>
</div>

{{-- Mobile app header --}}
<header class="app-mobile-only sticky top-0 z-50 bg-[#070b16]/95 backdrop-blur border-b border-line pt-[env(safe-area-inset-top,0)]">
    <div class="px-3 h-14 flex items-center gap-2">
        <a href="{{ route('ecommerce.home') }}" class="shrink-0 flex items-center" aria-label="{{ $store }}">
            @if($storeLogoUrl)
                <img src="{{ $storeLogoUrl }}" alt="{{ $store }}" class="h-9 w-auto max-w-[140px] object-contain object-left">
            @else
                <span class="wn-logo"><strong>{{ $store }}</strong><span>Convenience store wholesale</span></span>
            @endif
        </a>
        <form action="{{ route('ecommerce.shop') }}" method="get" class="flex-1 min-w-0">
            <div class="wn-search">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search products, brands..."
                       class="flex-1 bg-transparent border-0 outline-none text-[13px] px-2 min-w-0">
            </div>
        </form>
    </div>
        <div class="app-chip-scroll">
            <a href="{{ route('ecommerce.promotions') }}" class="app-chip {{ $routeName === 'ecommerce.promotions' || request()->boolean('featured') ? 'active' : '' }}">Promotions</a>
            <a href="{{ route('ecommerce.shop', ['sort' => 'newest', 'new_only' => 1]) }}" class="app-chip {{ request()->boolean('new_only') ? 'active' : '' }}">New Arrivals</a>
            @forelse($nav_categories as $cat)
                @php
                    $subIds = ($cat->sub_categories ?? collect())->pluck('id')->map(fn ($id) => (string) $id)->all();
                    $chipOn = request('category') == $cat->id
                        || (string) request()->route('slug') === (string) $cat->id
                        || in_array((string) request('category'), $subIds, true)
                        || in_array((string) request()->route('slug'), $subIds, true);
                @endphp
                <a href="{{ route('ecommerce.category', $cat->id) }}" class="app-chip {{ $chipOn ? 'active' : '' }}">
                    {{ $cat->name }}
                </a>
            @empty
                <a href="{{ route('ecommerce.shop') }}" class="app-chip">Shop</a>
                <a href="{{ route('ecommerce.brands') }}" class="app-chip">Brands</a>
                <a href="{{ route('ecommerce.shop') }}" class="app-chip">Categories</a>
                <a href="{{ route('ecommerce.promotions') }}" class="app-chip">Featured</a>
            @endforelse
        </div>
</header>

{{-- Sticky desktop chrome --}}
<div class="hidden lg:block sticky top-0 z-50 overflow-visible">
<div class="gc-util">
    <div class="max-w-[1440px] mx-auto px-4 h-[36px] flex items-center justify-between gap-3 text-[12px]">
        <div class="flex items-center gap-5">
            @if($storePhone !== '')
                <a href="tel:{{ $storePhoneHref }}" class="text-white/80 hover:text-white">{{ $storePhone }}</a>
            @endif
            @if($storeEmail !== '')
                <a href="mailto:{{ $storeEmail }}" class="text-white/80 hover:text-white">{{ $storeEmail }}</a>
            @endif
            <span class="text-[#3ee07a] font-semibold">Free Shipping on Orders $750+</span>
        </div>
        <div class="flex items-center gap-5 font-semibold">
            @auth('customer')
                <a href="{{ route('ecommerce.account') }}" class="text-[#3ee07a] hover:text-white">Account</a>
                <form action="{{ route('ecommerce.logout', absolute: false) }}" method="post" class="inline">
                    @csrf
                    <button type="submit" class="text-white/85 hover:text-white font-semibold">Logout</button>
                </form>
            @else
                <a href="{{ route('ecommerce.register') }}" class="text-[#3ee07a] hover:text-white">Wholesale Account</a>
                <a href="{{ route('ecommerce.login') }}" class="text-white/85 hover:text-white">Login</a>
            @endauth
            <a href="{{ route('ecommerce.cart') }}" class="text-white/85 hover:text-white">Cart @if(($cart_totals['count'] ?? 0) > 0)<span class="text-[#f23488]">({{ $cart_totals['count'] }})</span>@endif</a>
        </div>
    </div>
</div>

<header class="bg-[#0b1020] overflow-visible">
    <div class="max-w-[1440px] mx-auto px-4">
        <div class="h-[72px] flex items-center gap-5 relative z-[60]">
            <a href="{{ route('ecommerce.home') }}" class="shrink-0 flex items-center" aria-label="{{ $store }}">
                @if($storeLogoUrl)
                    <img src="{{ $storeLogoUrl }}" alt="{{ $store }}" class="h-11 w-auto max-w-[220px] object-contain object-left">
                @else
                    <span class="wn-logo"><strong>{{ $store }}</strong><span>Convenience store wholesale distributor</span></span>
                @endif
            </a>

            <form action="{{ route('ecommerce.shop') }}" method="get" class="flex flex-1 max-w-3xl">
                <div class="wn-search">
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Search products, brands, categories...">
                    <button aria-label="Search">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="8" r="5.5"/><path d="M12 12l4 4"/></svg>
                    </button>
                </div>
            </form>

            <a href="{{ route('ecommerce.quick_order') }}" class="wn-quick ml-auto">⚡ QUICK ORDER</a>
        </div>
    </div>

    <div class="gc-nav relative z-[80] text-white overflow-visible">
        <div class="max-w-[1440px] mx-auto px-4 cat-nav-wrap" id="catNavWrap">
            <ul class="gc-menubar">
                <li class="!flex-[0_0_44px]">
                    <a href="{{ route('ecommerce.shop') }}" class="cat-link gc-nav-item" aria-label="All departments">☰</a>
                </li>
                @foreach($nav_categories as $cat)
                    @php $subs = $cat->sub_categories ?? collect(); @endphp
                    <li class="{{ $subs->count() ? 'mega' : '' }}">
                        <a href="{{ route('ecommerce.category', $cat->id) }}" class="cat-link gc-nav-item">
                            <span class="gc-nav-text">{{ $cat->name }}</span>
                            @if($subs->count())
                                <svg width="11" height="11" viewBox="0 0 20 20" fill="currentColor" class="opacity-80 shrink-0"><path d="M5 8l5 5 5-5"/></svg>
                            @endif
                        </a>
                        @if($subs->count())
                        <div class="mega-panel">
                            <a href="{{ route('ecommerce.category', $cat->id) }}">All {{ $cat->name }}</a>
                            @foreach($subs as $sub)
                                <a href="{{ route('ecommerce.category', $sub->id) }}">{{ $sub->name }}</a>
                            @endforeach
                        </div>
                        @endif
                    </li>
                @endforeach
                @if($nav_categories->isEmpty())
                    <li><a href="{{ route('ecommerce.shop') }}" class="cat-link gc-nav-item"><span>Shop</span></a></li>
                    <li><a href="{{ route('ecommerce.brands') }}" class="cat-link gc-nav-item"><span>Brands</span></a></li>
                @endif
                <li>
                    <a href="{{ route('ecommerce.promotions') }}" class="cat-link gc-nav-item wn-nav-special deals">Deals</a>
                </li>
                <li>
                    <a href="{{ route('ecommerce.shop', ['sort' => 'newest', 'new_only' => 1]) }}" class="cat-link gc-nav-item wn-nav-special new">New</a>
                </li>
                <li>
                    <a href="{{ route('ecommerce.promotions') }}" class="cat-link gc-nav-item wn-nav-special clearance">Clearance</a>
                </li>
            </ul>
        </div>
    </div>
</header>
</div>

@if(session('success'))
    <div class="bg-[#10281c] text-[#7dffb3] text-sm text-center py-2.5 border-b border-[#1e4a32]">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="bg-[#2a1218] text-[#ff8aa3] text-sm text-center py-2.5 border-b border-[#5a2230]">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="bg-[#2a1218] text-[#ff8aa3] text-sm text-center py-2.5 border-b border-[#5a2230]">{{ $errors->first() }}</div>
@endif

<main>@yield('content')</main>

<footer class="desktop-footer mt-16 bg-[#050814] text-white">
    <div class="max-w-[1400px] mx-auto px-4 py-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-10 gc-footer-grid">
        <div>
            <div class="flex items-center gap-2 mb-3">
                <span class="font-extrabold text-[18px] tracking-tight text-white leading-none">{{ $store }}</span>
            </div>
            @if(!empty($ecom_settings->seo_description))
                <p class="text-white/65 text-sm leading-relaxed">{{ $ecom_settings->seo_description }}</p>
            @endif
            <p class="text-xs text-white/50 mt-3">Hours<br>{!! nl2br(e($storeHours)) !!}</p>
            <p class="text-sm mt-3 break-all">
                @if($storePhone !== '')
                    <a class="text-white" href="tel:{{ $storePhoneHref }}">{{ $storePhone }}</a><br>
                @endif
                @if($storeEmail !== '')
                    <a class="text-white/80" href="mailto:{{ $storeEmail }}">{{ $storeEmail }}</a>
                @endif
            </p>
        </div>
        <div>
            <h3 class="font-serif text-lg mb-3">Shop</h3>
            <div class="space-y-2 text-sm text-white/80">
                <a class="block hover:text-white" href="{{ route('ecommerce.shop', ['sort'=>'newest', 'new_only' => 1]) }}">All New Arrivals</a>
                <a class="block hover:text-white" href="{{ route('ecommerce.shop', ['sort'=>'newest', 'new_only' => 1]) }}">New arrivals</a>
                <a class="block hover:text-white" href="{{ route('ecommerce.shop', ['sort'=>'price_asc', 'new_only' => 1]) }}">Best value</a>
                @foreach($nav_categories as $cat)
                    <a class="block hover:text-white" href="{{ route('ecommerce.category', $cat->id) }}">{{ $cat->name }}</a>
                @endforeach
                @if($nav_categories->isEmpty())
                    <a class="block hover:text-white" href="{{ route('ecommerce.shop') }}">Shop</a>
                    <a class="block hover:text-white" href="{{ route('ecommerce.brands') }}">Brands</a>
                    <a class="block hover:text-white" href="{{ route('ecommerce.shop') }}">Categories</a>
                @endif
                <a class="block hover:text-white" href="{{ route('ecommerce.brands') }}">All Brands</a>
                <a class="block hover:text-white" href="{{ route('ecommerce.promotions') }}">Promotions &amp; Deals</a>
            </div>
        </div>
        <div>
            <h3 class="font-serif text-lg mb-3">Account</h3>
            <div class="space-y-2 text-sm text-white/80">
                <a class="block hover:text-white" href="{{ route('ecommerce.account') }}">My Account</a>
                <a class="block hover:text-white" href="{{ route('ecommerce.account.orders') }}">Order History</a>
                <a class="block hover:text-white" href="{{ route('ecommerce.account.addresses') }}">Saved Addresses</a>
                <a class="block hover:text-white" href="{{ route('ecommerce.register') }}">Wholesale Application</a>
                <a class="block hover:text-white" href="{{ route('ecommerce.cart') }}">Shopping Cart</a>
                <a class="block hover:text-white" href="{{ route('ecommerce.checkout') }}">Checkout</a>
            </div>
        </div>
        <div>
            <h3 class="font-serif text-lg mb-3">Company</h3>
            <div class="space-y-2 text-sm text-white/80">
                <a class="block hover:text-white" href="{{ route('ecommerce.page', 'about-us') }}">About Us</a>
                <a class="block hover:text-white" href="{{ route('ecommerce.page', 'wholesale-faq') }}">Wholesale FAQ</a>
                <a class="block hover:text-white" href="{{ route('ecommerce.page', 'shipping-policy') }}">Shipping Policy</a>
                <a class="block hover:text-white" href="{{ route('ecommerce.page', 'return-policy') }}">Return Policy</a>
                <a class="block hover:text-white" href="{{ route('ecommerce.page', 'privacy-policy') }}">Privacy Policy</a>
                <a class="block hover:text-white" href="{{ route('ecommerce.page', 'terms-of-service') }}">Terms of Service</a>
                <a class="block hover:text-white" href="{{ route('ecommerce.page', 'age-compliance') }}">Age Compliance</a>
                <a class="block hover:text-white" href="{{ route('ecommerce.contact') }}">Contact Us</a>
            </div>
            <form class="mt-5" onsubmit="event.preventDefault(); this.querySelector('button').textContent='Subscribed';">
                <p class="text-xs text-white/60 mb-2">Stay informed — new arrivals, deals &amp; industry news</p>
                <div class="flex flex-wrap gap-2 min-w-0">
                    <input type="email" required placeholder="Your business email" class="min-w-0 flex-1 rounded-lg px-3 py-2 text-sm text-ink">
                    <button type="submit" class="rounded-lg bg-[#f23488] px-3 py-2 text-sm font-bold shrink-0">Subscribe</button>
                </div>
                <p class="text-[11px] text-white/40 mt-2">For licensed wholesale accounts only.</p>
            </form>
        </div>
    </div>
    <div class="border-t border-white/10">
        <div class="max-w-[1400px] mx-auto px-4 py-4 text-xs text-white/45">
            © {{ date('Y') }} {{ $store }}. All rights reserved.
        </div>
    </div>
</footer>

{{-- Mobile bottom tabs --}}
<nav class="app-bottom-nav app-mobile-only" aria-label="Primary">
    <div class="flex items-stretch">
        <a href="{{ route('ecommerce.home') }}" class="app-tab {{ $isHome ? 'active' : '' }}">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 10.5L12 3l9 7.5V20a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1v-9.5z"/></svg>
            Home
        </a>
        <a href="{{ route('ecommerce.shop') }}" class="app-tab {{ $isShop ? 'active' : '' }}">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            Shop
        </a>
        <button type="button" id="openCatSheet" class="app-tab">
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
                <a href="{{ route('ecommerce.track') }}" class="rounded-xl border border-line bg-mist px-3 py-3 font-semibold text-sm">Track order</a>
                @auth('customer')
                    <a href="{{ route('ecommerce.account') }}" class="rounded-xl border border-line bg-mist px-3 py-3 font-semibold text-sm">My account</a>
                    <a href="{{ route('ecommerce.contact') }}" class="rounded-xl border border-line bg-mist px-3 py-3 font-semibold text-sm">Contact</a>
                @else
                    <a href="{{ route('ecommerce.login') }}" class="rounded-xl border border-line bg-mist px-3 py-3 font-semibold text-sm">Login</a>
                    <a href="{{ route('ecommerce.register') }}" class="rounded-xl border border-line bg-mist px-3 py-3 font-semibold text-sm text-brand">Sign Up</a>
                @endauth
            </div>
            <div class="text-xs font-bold uppercase tracking-wider text-black/40 mb-2">Departments</div>
            <div class="divide-y divide-line rounded-xl border border-line overflow-hidden bg-[#12182e]">
                <a href="{{ route('ecommerce.promotions') }}" class="flex items-center justify-between px-4 py-3.5 font-semibold text-[15px] active:bg-mist">
                    <span>Promotions</span>
                    <span class="text-black/30">›</span>
                </a>
                <a href="{{ route('ecommerce.shop', ['sort' => 'newest', 'new_only' => 1]) }}" class="flex items-center justify-between px-4 py-3.5 font-semibold text-[15px] active:bg-mist">
                    <span>All New Arrivals</span>
                    <span class="text-black/30">›</span>
                </a>
                <a href="{{ route('ecommerce.shop', ['sort' => 'newest', 'new_only' => 1]) }}" class="block px-8 py-2.5 text-sm text-black/70 active:bg-mist">New arrivals</a>
                <a href="{{ route('ecommerce.shop', ['sort' => 'price_asc', 'new_only' => 1]) }}" class="block px-8 py-2.5 text-sm text-black/70 active:bg-mist">Best value</a>
                @forelse($nav_categories as $cat)
                    @php $subs = $cat->sub_categories ?? collect(); @endphp
                    <a href="{{ route('ecommerce.category', $cat->id) }}" class="flex items-center justify-between px-4 py-3.5 font-semibold text-[15px] active:bg-mist">
                        <span>{{ $cat->name }}</span>
                        <span class="text-black/30">›</span>
                    </a>
                    @foreach($subs as $sub)
                        <a href="{{ route('ecommerce.category', $sub->id) }}" class="block px-8 py-2.5 text-sm text-black/70 active:bg-mist">{{ $sub->name }}</a>
                    @endforeach
                @empty
                    <a href="{{ route('ecommerce.shop') }}" class="flex items-center justify-between px-4 py-3.5 font-semibold text-[15px] active:bg-mist">
                        <span>Shop</span>
                        <span class="text-black/30">›</span>
                    </a>
                    <a href="{{ route('ecommerce.brands') }}" class="flex items-center justify-between px-4 py-3.5 font-semibold text-[15px] active:bg-mist">
                        <span>Brands</span>
                        <span class="text-black/30">›</span>
                    </a>
                    <a href="{{ route('ecommerce.shop') }}" class="flex items-center justify-between px-4 py-3.5 font-semibold text-[15px] active:bg-mist">
                        <span>Categories</span>
                        <span class="text-black/30">›</span>
                    </a>
                @endforelse
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
    const openBtn = document.getElementById('openCatSheet');
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
    openBtn?.addEventListener('click', open);
    sheet?.querySelectorAll('[data-close-sheet]').forEach(el => el.addEventListener('click', close));
})();

document.querySelectorAll('.gc-menubar .mega').forEach(function (item) {
    item.addEventListener('mouseenter', function () { item.classList.add('is-open'); });
    item.addEventListener('mouseleave', function () { item.classList.remove('is-open'); });
});

document.querySelectorAll('form[method="post"], form[method="POST"]').forEach(function (form) {
    form.addEventListener('submit', function () {
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (!meta) return;
        var input = form.querySelector('input[name="_token"]');
        if (input) input.value = meta.getAttribute('content');
    });
});

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

(function () {
    const gate = document.getElementById('gcAgeGate');
    if (gate && !localStorage.getItem('gc_age_ok')) {
        gate.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }
    document.getElementById('gcAgeYes')?.addEventListener('click', () => {
        localStorage.setItem('gc_age_ok', '1');
        gate?.classList.remove('is-open');
        document.body.style.overflow = '';
    });
    document.getElementById('gcAgeNo')?.addEventListener('click', () => {
        window.location.href = 'https://www.google.com';
    });
    const msgs = [
        { t: 'Summer Clearance — Up to 40% Off Select Items', a: 'Shop Clearance →' },
        { t: 'Now Carrying Lost Mary, RAZ & HQD — Top-Selling Disposables', a: 'Shop Disposables →' },
        { t: 'New THCA & Delta-8 Arrivals — Premium Quality at Wholesale Prices', a: 'View New Arrivals →' },
    ];
    let i = 0;
    const text = document.querySelector('[data-ann-text]');
    const cta = text?.nextElementSibling;
    const show = () => {
        if (!text) return;
        text.textContent = msgs[i].t;
        if (cta) cta.textContent = msgs[i].a;
    };
    document.querySelector('[data-ann-next]')?.addEventListener('click', () => { i = (i + 1) % msgs.length; show(); });
    document.querySelector('[data-ann-prev]')?.addEventListener('click', () => { i = (i + msgs.length - 1) % msgs.length; show(); });
})();
</script>
@stack('scripts')
<script>
window.__ECOM_PWA__ = {
    swUrl: @json(url('/ecom-pwa/sw.js')),
    manifestUrl: @json(url('/ecom-pwa/manifest.webmanifest')),
    iosHint: 'On iPhone/iPad: tap Share → Add to Home Screen.',
    installHint: 'Use your browser menu → Install app / Add to Home Screen.'
};
</script>
<script src="{{ asset('js/ecom-pwa.js') }}?v={{ config('app.asset_version', config('constants.asset_version', '1')) }}" defer></script>
</body>
</html>
