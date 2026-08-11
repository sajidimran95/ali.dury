<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#e85d04">
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: '#e85d04',
                        brandDark: '#d00000',
                        ink: '#1a1a1a',
                        slatebar: '#2b2b2b',
                        mist: '#f5f5f5',
                        line: '#e5e5e5'
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: Inter, system-ui, sans-serif; background: #f7f7f7; color: #1a1a1a; }
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
            border: 1px solid #e5e5e5;
            border-top: none;
            box-shadow: 0 16px 40px rgba(0,0,0,.12);
            border-radius: 0 0 12px 12px;
            grid-template-columns: 1.4fr 0.9fr;
        }
        .mega-panel.is-open { display: grid; }
        .mega.is-active > .cat-link,
        .cat-link:hover { color: #e85d04; }
        .brand-tile {
            display: flex; align-items: center; justify-content: center;
            min-height: 64px; border: 1px solid #ececec; border-radius: 8px;
            background: #fafafa; padding: 8px; text-align: center;
            transition: border-color .15s, box-shadow .15s;
        }
        .brand-tile:hover { border-color: #e85d04; box-shadow: 0 2px 10px rgba(232,93,4,.12); }
        .input-w {
            width: 100%; border: 1px solid #d4d4d4; border-radius: 8px; padding: .7rem .9rem; font-size: .875rem;
            background: #fff; outline: none;
        }
        .input-w:focus { border-color: #e85d04; box-shadow: 0 0 0 3px rgba(232,93,4,.15); }
        .btn-brand {
            background: #e85d04; color: #fff; font-weight: 600; border-radius: 8px;
            padding: .7rem 1.25rem; transition: background .15s;
        }
        .btn-brand:hover { background: #c44d03; }
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
        .app-tab.active, .app-tab:active { color: #e85d04; }
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
        .app-chip.active { background: #fff4ec; color: #e85d04; border-color: #f4c7a8; }
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
                background: #fff4ec; color: #e85d04; flex-shrink: 0;
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
    $store = $ecom_settings->store_name ?? 'Japs Wholesale';
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
    $storeLogoCandidates[] = 'ecommerce/japs-wholesale-logo.svg';
    try {
        $bizLogo = \App\Business::where('id', $ecom_business_id ?? null)->value('logo')
            ?: \App\Business::whereNotNull('logo')->where('logo', '!=', '')->orderBy('id')->value('logo');
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

{{-- Desktop promo bars --}}
<div class="hidden lg:block bg-brand text-white text-[11px] sm:text-xs tracking-wide">
    <div class="max-w-[1400px] mx-auto px-4 py-2 flex flex-wrap items-center justify-between gap-2">
        <p class="font-semibold uppercase">Welcome to {{ $store }}</p>
        <p class="opacity-95">Wholesale distribution · USA · <a href="{{ route('ecommerce.contact') }}" class="underline underline-offset-2">Contact us</a></p>
    </div>
</div>
<div class="hidden lg:block bg-[#ececec] text-[#444] text-[11px] sm:text-xs text-center px-4 py-2 border-b border-black/5">
    <strong>WARNING:</strong> This product contains nicotine. Nicotine is an addictive chemical. Wholesale accounts only — age verification required.
</div>

{{-- Mobile app header --}}
<header class="app-mobile-only sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-line pt-[env(safe-area-inset-top,0)]">
    <div class="px-3 h-14 flex items-center gap-2">
        <a href="{{ url('/') }}" class="shrink-0 flex items-center" aria-label="{{ $store }}">
            @if($storeLogoUrl)
                <img src="{{ $storeLogoUrl }}" alt="{{ $store }}" class="h-9 w-auto max-w-[140px] object-contain object-left">
            @else
                <span class="h-9 w-9 rounded-full bg-brand text-white flex items-center justify-center font-extrabold text-sm">J</span>
            @endif
        </a>
        <form action="{{ route('ecommerce.shop') }}" method="get" class="flex-1 min-w-0">
            <div class="flex items-center rounded-full bg-mist border border-line px-3 h-10">
                <svg width="16" height="16" class="text-black/40 shrink-0" fill="none" stroke="currentColor" stroke-width="2"><circle cx="7" cy="7" r="5"/><path d="M11 11l3.5 3.5"/></svg>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search SKU, brands..."
                       class="flex-1 bg-transparent border-0 outline-none text-[13px] px-2 min-w-0">
            </div>
        </form>
        {{-- Cart only in bottom nav on mobile (no header cart icon) --}}
    </div>
    @if($nav_categories->count())
        <div class="app-chip-scroll">
            <a href="{{ route('ecommerce.shop') }}" class="app-chip {{ $routeName === 'ecommerce.shop' && ! request('category') ? 'active' : '' }}">All</a>
            @foreach($nav_categories as $cat)
                <a href="{{ route('ecommerce.category', $cat->id) }}"
                   class="app-chip {{ (request('category') == $cat->id || (string) request()->route('slug') === (string) $cat->id) ? 'active' : '' }}">
                    {{ $cat->name }}
                </a>
            @endforeach
        </div>
    @endif
</header>

{{-- Desktop header --}}
<header class="hidden lg:block bg-white border-b border-line sticky top-0 z-50">
    <div class="max-w-[1400px] mx-auto px-4">
        <div class="h-[72px] flex items-center gap-4 lg:gap-6 relative z-[60]">
            <a href="{{ url('/') }}" class="shrink-0 flex items-center" aria-label="{{ $store }}">
                @if($storeLogoUrl)
                    <img src="{{ $storeLogoUrl }}" alt="{{ $store }}" class="h-11 sm:h-12 w-auto max-w-[220px] object-contain object-left">
                @else
                    <span class="flex items-center gap-2">
                        <span class="h-9 w-9 rounded-full bg-brand text-white flex items-center justify-center font-extrabold text-sm">J</span>
                        <span class="leading-tight">
                            <span class="block font-extrabold text-[15px] text-ink">{{ $store }}</span>
                            <span class="block text-[10px] uppercase tracking-wider text-black/45">Wholesale Distribution</span>
                        </span>
                    </span>
                @endif
            </a>

            <nav class="flex items-center gap-6 text-[17px] xl:text-[18px] font-bold tracking-tight relative z-[70]">
                <a href="{{ url('/') }}" class="hover:text-brand">Home</a>
                <a href="{{ route('ecommerce.shop') }}" class="hover:text-brand">Shop</a>
                <a href="{{ route('ecommerce.quick_order') }}" class="hover:text-brand">Quick Order</a>
                <div class="relative group z-[80]">
                    <button type="button" class="hover:text-brand inline-flex items-center gap-1">More
                        <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path d="M5 8l5 5 5-5"/></svg>
                    </button>
                    <div class="invisible opacity-0 pointer-events-none group-hover:visible group-hover:opacity-100 group-hover:pointer-events-auto absolute left-0 top-full pt-1 z-[90] transition">
                        <div class="bg-white border border-line rounded-lg shadow-xl py-2 min-w-[180px] text-sm font-medium">
                            <a href="{{ route('ecommerce.track') }}" class="block px-4 py-2 hover:bg-mist">Track order</a>
                            <a href="{{ route('ecommerce.contact') }}" class="block px-4 py-2 hover:bg-mist">Contact</a>
                            <a href="{{ route('ecommerce.account') }}" class="block px-4 py-2 hover:bg-mist">My account</a>
                        </div>
                    </div>
                </div>
            </nav>

            <form action="{{ route('ecommerce.shop') }}" method="get" class="flex flex-1 max-w-xl mx-auto">
                <div class="flex w-full rounded-full border border-line overflow-hidden bg-mist focus-within:bg-white focus-within:border-brand">
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Search"
                           class="flex-1 bg-transparent border-0 outline-none text-[13px] px-4 py-2.5">
                    <button class="bg-brand text-white px-4 hover:bg-[#c44d03]" aria-label="Search">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="8" r="5.5"/><path d="M12 12l4 4"/></svg>
                    </button>
                </div>
            </form>

            <div class="ml-auto flex items-center gap-3 sm:gap-4 text-[15px] font-semibold shrink-0">
                @auth('customer')
                    <a href="{{ route('ecommerce.wishlist') }}" class="inline-flex items-center gap-1.5 hover:text-brand" title="Wishlist">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 15s-6-3.8-6-7.2A3.3 3.3 0 019 4.8a3.3 3.3 0 016 3C15 11.2 9 15 9 15z"/></svg>
                        <span class="hidden xl:inline">Wishlist</span>
                    </a>
                    <a href="{{ route('ecommerce.account') }}" class="inline-flex items-center gap-1.5 hover:text-brand" title="Account">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="6" r="3"/><path d="M3 16c1.5-3 10.5-3 12 0"/></svg>
                        <span class="hidden xl:inline">Account</span>
                    </a>
                @else
                    <div class="inline-flex items-center gap-1.5">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" class="opacity-70"><circle cx="9" cy="6" r="3"/><path d="M3 16c1.5-3 10.5-3 12 0"/></svg>
                        <a href="{{ route('ecommerce.login') }}" class="hover:text-brand">Login</a>
                        <span class="text-black/35 font-normal">or</span>
                        <a href="{{ route('ecommerce.register') }}" class="hover:text-brand">Sign Up</a>
                    </div>
                @endauth
                <a href="{{ route('ecommerce.cart') }}" class="inline-flex items-center gap-2 hover:text-brand">
                    <span class="relative">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 4h12l-1 9H4L3 4z"/><path d="M7 7V4a2 2 0 014 0v3"/></svg>
                        <span class="absolute -top-2 -right-2 bg-brand text-white text-[10px] rounded-full min-w-[16px] h-[16px] inline-flex items-center justify-center px-1">{{ $cart_totals['count'] ?? 0 }}</span>
                    </span>
                    <span>View Cart</span>
                    <span class="tabular-nums text-black/55">${{ number_format($cart_totals['subtotal'] ?? 0, 2) }}</span>
                </a>
            </div>
        </div>
    </div>

    <div class="border-t border-line bg-white relative z-10">
        <div class="max-w-[1400px] mx-auto px-4 cat-nav-wrap" id="catNavWrap">
            <ul class="flex items-stretch gap-0 text-[15px] xl:text-[16px] font-bold">
                @foreach($nav_categories as $cat)
                    @php
                        $subs = $cat->sub_categories ?? collect();
                        $megaBrands = $cat->mega_brands ?? collect();
                    @endphp
                    <li class="mega">
                        <a href="{{ route('ecommerce.category', $cat->id) }}"
                           class="cat-link inline-flex items-center gap-1 px-3.5 py-3.5 whitespace-nowrap {{ (request('category') == $cat->id || (string) request()->route('slug') === (string) $cat->id) ? 'text-brand' : '' }}">
                            {{ $cat->name }}
                            <svg width="11" height="11" viewBox="0 0 20 20" fill="currentColor" class="opacity-50"><path d="M5 8l5 5 5-5"/></svg>
                        </a>

                        <div class="mega-panel">
                            <div class="p-5 border-r border-line">
                                <div class="text-brand text-lg font-extrabold mb-4">Brands</div>
                                @if($megaBrands->count())
                                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-2.5">
                                        @foreach($megaBrands as $brand)
                                            <a href="{{ route('ecommerce.shop', ['category' => $cat->id, 'brand' => $brand->id]) }}"
                                               class="brand-tile" title="{{ $brand->name }}">
                                                @if(!empty($brand->image))
                                                    <img src="{{ asset('uploads/img/'.$brand->image) }}" alt="{{ $brand->name }}"
                                                         class="max-h-10 max-w-full object-contain">
                                                @else
                                                    <span class="text-[11px] font-bold text-ink leading-tight px-1">{{ $brand->name }}</span>
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-black/45">Brands coming soon for this department.</p>
                                @endif
                            </div>
                            <div class="p-5 bg-[#fcfcfc]">
                                <div class="text-ink text-base font-extrabold mb-3">{{ $cat->name }}</div>
                                <div class="space-y-2 text-sm">
                                    <a href="{{ route('ecommerce.category', $cat->id) }}" class="block font-semibold hover:text-brand">All {{ $cat->name }}</a>
                                    @forelse($subs as $sub)
                                        <a href="{{ route('ecommerce.category', $sub->id) }}" class="block text-black/70 hover:text-brand">{{ $sub->name }}</a>
                                    @empty
                                        <a href="{{ route('ecommerce.shop', ['category' => $cat->id, 'sort' => 'newest']) }}" class="block text-black/70 hover:text-brand">New arrivals</a>
                                        <a href="{{ route('ecommerce.shop', ['category' => $cat->id, 'sort' => 'price_asc']) }}" class="block text-black/70 hover:text-brand">Best value</a>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
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

<main>@yield('content')</main>

<footer class="desktop-footer mt-16 bg-slatebar text-white">
    <div class="max-w-[1400px] mx-auto px-4 py-12 grid md:grid-cols-4 gap-8">
        <div class="md:col-span-2">
            @if($storeLogoUrl)
                <a href="{{ url('/') }}" class="inline-block mb-3">
                    <img src="{{ $storeLogoUrl }}" alt="{{ $store }}" class="h-10 w-auto max-w-[200px] object-contain brightness-0 invert">
                </a>
            @else
                <div class="font-extrabold text-xl mb-2">{{ $store }}</div>
            @endif
            <p class="text-white/65 text-sm max-w-md leading-relaxed">
                {{ $ecom_settings->seo_description ?? 'Professional wholesale distribution with live inventory, bulk-ready catalog, and secure checkout.' }}
            </p>
            <div class="flex flex-wrap gap-2 mt-5">
                <a href="{{ route('ecommerce.register') }}" class="btn-brand text-xs">Open account</a>
                <a href="{{ route('ecommerce.contact') }}" class="rounded-lg border border-white/25 px-4 py-2 text-xs font-semibold hover:bg-white/10">Contact</a>
            </div>
        </div>
        <div>
            <div class="text-xs font-bold uppercase tracking-wider text-brand mb-3">Information</div>
            <div class="space-y-2 text-sm text-white/80">
                <a class="block hover:text-white" href="{{ route('ecommerce.shop') }}">Shop all</a>
                @foreach($nav_categories->take(5) as $cat)
                    <a class="block hover:text-white" href="{{ route('ecommerce.category', $cat->id) }}">{{ $cat->name }}</a>
                @endforeach
            </div>
        </div>
        <div>
            <div class="text-xs font-bold uppercase tracking-wider text-brand mb-3">Customer Service</div>
            <div class="space-y-2 text-sm text-white/80">
                <a class="block hover:text-white" href="{{ route('ecommerce.login') }}">Login</a>
                <a class="block hover:text-white" href="{{ route('ecommerce.register') }}">Wholesale sign up</a>
                <a class="block hover:text-white" href="{{ route('ecommerce.track') }}">Track order</a>
                <a class="block hover:text-white" href="{{ route('ecommerce.contact') }}">Contact us</a>
                <a class="block hover:text-white" href="{{ route('ecommerce.account') }}">My account</a>
            </div>
        </div>
    </div>
    <div class="border-t border-white/10">
        <div class="max-w-[1400px] mx-auto px-4 py-4 text-xs text-white/45 flex flex-wrap items-center justify-between gap-3">
            <span>© {{ date('Y') }} {{ $store }}. All rights reserved. · Wholesale · Age-restricted products</span>
            <div class="flex items-center gap-2 text-[10px] font-bold tracking-wide">
                <span class="px-2 py-1 rounded bg-white/10">VISA</span>
                <span class="px-2 py-1 rounded bg-white/10">MC</span>
                <span class="px-2 py-1 rounded bg-white/10">AMEX</span>
                <span class="px-2 py-1 rounded bg-white/10">DISC</span>
                <span class="px-2 py-1 rounded bg-white/10">COD</span>
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
