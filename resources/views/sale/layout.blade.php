<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f766e">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Sales">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="manifest" href="{{ url('/sale/pwa/manifest.webmanifest') }}">
    <link rel="icon" type="image/png" href="{{ asset('pwa/sale-icon-192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('pwa/sale-icon-192.png') }}">
    <title>@yield('title', 'Sales App') — {{ config('app.name', 'JAPS POS') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        sale: { DEFAULT: '#0f766e', dark: '#0d5f59', soft: '#ccfbf1', ink: '#0b1220', mist: '#f1f5f9', line: '#e2e8f0' }
                    },
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
        body { font-family: Inter, system-ui, sans-serif; background: #f1f5f9; color: #0b1220; margin: 0; }
        .sale-input {
            width: 100%; border: 1px solid #e2e8f0; border-radius: 12px; padding: .75rem .9rem;
            font-size: .9375rem; background: #fff; outline: none; box-sizing: border-box;
        }
        .sale-input:focus { border-color: #0f766e; box-shadow: 0 0 0 3px rgba(15,118,110,.15); }
        .sale-btn {
            background: #0f766e; color: #fff; font-weight: 700; border: 0; border-radius: 12px;
            padding: .85rem 1.25rem; width: 100%; font-size: .95rem; cursor: pointer;
        }
        .sale-btn:disabled { opacity: .55; cursor: not-allowed; }
        .sale-btn-ghost {
            background: #fff; color: #0f766e; border: 1px solid #cbd5e1; font-weight: 700;
            border-radius: 12px; padding: .85rem 1.25rem; width: 100%; cursor: pointer;
        }
        .sale-btn-sm {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            background: #0f766e; color: #fff; font-weight: 700; border: 0; border-radius: 10px;
            padding: .55rem 1rem; font-size: .875rem; cursor: pointer; text-decoration: none;
            width: auto;
        }
        .sale-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 14px;
            box-shadow: 0 1px 2px rgba(15,23,42,.03);
        }
        .sale-badge {
            display: inline-flex; align-items: center; border-radius: 999px;
            padding: 3px 9px; font-size: 11px; font-weight: 700;
        }
        .sale-badge--paid { background: #d1fae5; color: #065f46; }
        .sale-badge--due { background: #fee2e2; color: #991b1b; }
        .sale-badge--partial { background: #fef3c7; color: #92400e; }

        /* App UI primitives */
        .sale-ico {
            display: inline-flex; align-items: center; justify-content: center;
            flex-shrink: 0; width: 1em; height: 1em;
        }
        .sale-ico svg { width: 100%; height: 100%; display: block; }
        .sale-sec-title {
            display: flex; align-items: center; gap: 10px;
            font-size: 14px; font-weight: 800; color: #0b1220; margin-bottom: 12px;
        }
        .sale-sec-title__ico {
            width: 34px; height: 34px; border-radius: 10px;
            background: #ccfbf1; color: #0f766e;
            display: flex; align-items: center; justify-content: center;
        }
        .sale-sec-title__ico svg { width: 18px; height: 18px; }
        .sale-order-row {
            display: flex; align-items: center; gap: 12px;
            background: #fff; border: 1px solid #e2e8f0; border-radius: 16px;
            padding: 12px 14px; text-decoration: none; color: inherit;
            box-shadow: 0 1px 2px rgba(15,23,42,.03);
            transition: background .12s ease, border-color .12s ease;
        }
        .sale-order-row:active, .sale-order-row:hover { background: #f8fafc; border-color: #cbd5e1; }
        .sale-order-row__ico {
            width: 44px; height: 44px; border-radius: 12px;
            background: #f0fdfa; color: #0f766e;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .sale-order-row__ico svg { width: 22px; height: 22px; }
        .sale-order-row__body { min-width: 0; flex: 1; }
        .sale-order-row__meta { min-width: 0; text-align: right; flex-shrink: 0; }
        .sale-menu-row {
            display: flex; align-items: center; gap: 12px;
            width: 100%; padding: 14px 4px; text-decoration: none; color: inherit;
            border: 0; background: transparent; border-bottom: 1px solid #f1f5f9;
            font: inherit; text-align: left; cursor: pointer;
        }
        .sale-menu-row:last-child { border-bottom: 0; }
        .sale-menu-row__ico {
            width: 40px; height: 40px; border-radius: 12px;
            background: #f1f5f9; color: #0f766e;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .sale-menu-row__ico svg { width: 20px; height: 20px; }
        .sale-menu-row__ico--danger { background: #fff1f2; color: #e11d48; }
        .sale-menu-row__text { flex: 1; min-width: 0; }
        .sale-menu-row__text strong { display: block; font-size: 14px; font-weight: 700; }
        .sale-menu-row__text small { display: block; font-size: 12px; color: #64748b; margin-top: 1px; }
        .sale-menu-row__chev { color: #94a3b8; font-size: 18px; font-weight: 700; }
        .sale-empty {
            text-align: center; padding: 36px 16px;
        }
        .sale-empty__ico {
            width: 64px; height: 64px; margin: 0 auto 12px; border-radius: 20px;
            background: #f0fdfa; color: #0f766e;
            display: flex; align-items: center; justify-content: center;
        }
        .sale-empty__ico svg { width: 30px; height: 30px; }
        .sale-page-tool {
            display: flex; align-items: center; justify-content: space-between; gap: 10px;
            margin-bottom: 12px;
        }
        .sale-chip {
            display: inline-flex; align-items: center; gap: 6px;
            background: #fff; border: 1px solid #e2e8f0; border-radius: 999px;
            padding: 6px 12px; font-size: 12px; font-weight: 700; color: #475569;
        }

        /* App pagination (no Bootstrap) */
        .sale-pager {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 10px 12px;
        }
        .sale-pager__meta {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
        }
        .sale-pager__btns {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
        }
        .sale-pager__btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0 10px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #0f766e;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            box-sizing: border-box;
        }
        .sale-pager__btn:hover { background: #f0fdfa; }
        .sale-pager__btn--active {
            background: #0f766e;
            border-color: #0f766e;
            color: #fff;
        }
        .sale-pager__btn--disabled {
            color: #94a3b8;
            border-color: #f1f5f9;
            background: #f8fafc;
            pointer-events: none;
        }

        /* —— Mobile app chrome —— */
        .sale-bottom-nav {
            position: fixed; left: 0; right: 0; bottom: 0; z-index: 80;
            background: rgba(255,255,255,.98); backdrop-filter: blur(12px);
            border-top: 1px solid #e2e8f0;
            padding-bottom: env(safe-area-inset-bottom, 0);
            box-shadow: 0 -4px 20px rgba(15,23,42,.06);
        }
        .sale-bottom-nav__inner {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: flex-end;
            max-width: 560px;
            margin: 0 auto;
            padding: 2px 4px 2px;
            gap: 0;
        }
        .sale-bottom-nav__side {
            display: flex;
            align-items: flex-end;
            justify-content: space-evenly;
            min-width: 0;
        }
        .sale-bottom-nav__center {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            padding: 0 6px 6px;
            flex-shrink: 0;
        }
        .sale-tab {
            flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: flex-end;
            gap: 3px; min-height: 58px; color: #64748b; font-size: 10px; font-weight: 700;
            text-decoration: none; background: transparent; border: 0;
            padding: 0 4px 6px;
            -webkit-tap-highlight-color: transparent;
            max-width: 88px;
        }
        .sale-tab svg {
            width: 22px; height: 22px; display: block; stroke: currentColor; fill: none;
            stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round;
        }
        .sale-tab.active { color: #0f766e; }
        .sale-tab.active svg { stroke-width: 2.2; }
        .sale-tab-fab {
            width: 54px; height: 54px; border-radius: 999px;
            background: #0f766e; color: #fff;
            display: flex; align-items: center; justify-content: center;
            margin-top: -22px; margin-bottom: 2px;
            box-shadow: 0 6px 16px rgba(15,118,110,.4);
            text-decoration: none;
        }
        .sale-tab-fab svg { width: 26px; height: 26px; stroke: #fff; stroke-width: 2.2; fill: none; }
        .sale-tab-fab.active,
        .sale-tab-fab:active { background: #0d5f59; }
        .sale-bottom-nav__center-label {
            font-size: 10px; font-weight: 700; color: #64748b; line-height: 1;
        }
        .sale-bottom-nav__center-label.active { color: #0f766e; }
        .sale-main-app { padding-bottom: calc(82px + env(safe-area-inset-bottom, 0)); min-height: 100dvh; }

        /* Dashboard */
        .sale-dash-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 16px;
        }
        .sale-stat {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 14px;
        }
        .sale-stat--primary {
            grid-column: 1 / -1;
            background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%);
            border: 0;
            color: #fff;
        }
        .sale-stat--primary .sale-stat__label,
        .sale-stat--primary .sale-stat__sub { color: rgba(255,255,255,.8); }
        .sale-stat__label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #64748b; }
        .sale-stat__value { font-size: 1.35rem; font-weight: 800; margin-top: 4px; line-height: 1.2; }
        .sale-stat__sub { font-size: 12px; color: #94a3b8; margin-top: 4px; font-weight: 600; }
        .sale-dash-actions {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 10px;
        }
        .sale-action-tile {
            display: flex; align-items: center; gap: 10px;
            background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
            padding: 12px; text-decoration: none; color: inherit;
        }
        .sale-action-tile--main {
            background: #ccfbf1; border-color: #99f6e4;
        }
        .sale-action-tile__icon {
            width: 40px; height: 40px; border-radius: 12px;
            background: #0f766e; color: #fff;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .sale-action-tile__icon--soft {
            background: #e2e8f0; color: #0f766e;
        }
        .sale-action-tile__text { min-width: 0; display: flex; flex-direction: column; gap: 1px; }
        .sale-action-tile__text strong { font-size: 13px; font-weight: 800; }
        .sale-action-tile__text small { font-size: 11px; color: #64748b; font-weight: 600; }
        .sale-install {
            position: fixed;
            left: 0;
            right: 0;
            bottom: calc(72px + env(safe-area-inset-bottom, 0));
            top: auto;
            z-index: 100;
            padding: 0 16px 8px;
            pointer-events: none;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            box-sizing: border-box;
        }
        .sale-install[hidden] { display: none !important; }
        .sale-install__inner {
            pointer-events: auto;
            width: 100%;
            max-width: 420px;
            display: flex; align-items: center; gap: 10px;
            background: #0b1220; color: #fff; border-radius: 14px; padding: 12px 14px;
            box-shadow: 0 12px 40px rgba(0,0,0,.35);
        }
        .sale-install__logo { width: 36px; height: 36px; border-radius: 9px; background: #0f766e; }
        .sale-install__btn {
            flex-shrink: 0; background: #0f766e; color: #fff; border: 0; border-radius: 8px;
            padding: 8px 12px; font-weight: 700; font-size: 12px; cursor: pointer;
        }
        .sale-install__close {
            width: 28px; height: 28px; border: 0; border-radius: 999px;
            background: rgba(255,255,255,.12); color: #fff; font-size: 16px; cursor: pointer;
        }
        body.sale-pwa-standalone .sale-install { display: none !important; }

        /* Mobile only / desktop only */
        .sale-m-only { display: block; }
        .sale-d-only { display: none !important; }
        .sale-nav-link {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 8px 14px; border-radius: 10px; font-size: 14px; font-weight: 700;
            color: #475569; text-decoration: none;
        }
        .sale-nav-link:hover { background: #f1f5f9; color: #0f766e; }
        .sale-nav-link.active { background: #ccfbf1; color: #0f766e; }

        @media (min-width: 1024px) {
            body { background: #eef2f6; overflow-x: hidden; }
            .sale-m-only { display: none !important; }
            .sale-d-only { display: block !important; }
            .sale-d-flex { display: flex !important; }
            .sale-d-only.sale-desk-side {
                display: flex !important;
                flex-direction: column;
                height: 100dvh;
                min-height: 100dvh;
            }
            .sale-desk-side .sale-side-footer {
                margin-top: auto;
                padding-top: 16px;
                flex-shrink: 0;
            }
            .sale-bottom-nav { display: none !important; }
            .sale-main-app {
                padding: 0 !important;
                padding-bottom: 48px !important;
                min-height: calc(100dvh - 64px);
                width: 100%;
                max-width: none;
                box-sizing: border-box;
            }
            /* Install: bottom center — login full width; logged-in desktop offset for sidebar */
            .sale-install {
                left: 0;
                right: 0;
                top: auto;
                bottom: 24px;
                padding: 0 24px;
                align-items: flex-end;
                justify-content: center;
            }
            body.sale-authed .sale-install {
                left: 240px;
            }
            .sale-install__inner {
                width: 100%;
                max-width: 400px;
                min-width: 0;
            }
            .sale-btn { width: auto; min-width: 160px; }
            .sale-page {
                display: block;
                width: 100%;
                max-width: none !important;
                margin: 0 !important;
                padding: 24px 28px 40px;
                box-sizing: border-box;
            }
            .sale-card {
                display: block;
                width: 100% !important;
                max-width: none !important;
                box-sizing: border-box;
                padding: 20px;
                border-radius: 16px;
                box-shadow: 0 1px 2px rgba(15,23,42,.04);
            }
            .sale-desk-shell {
                display: flex;
                width: 100%;
                max-width: none;
                min-height: 100dvh;
                box-sizing: border-box;
            }
            .sale-desk-side {
                width: 240px;
                flex-shrink: 0;
                background: #0b1220;
                color: #fff;
                padding: 24px 16px;
                position: sticky;
                top: 0;
                height: 100dvh;
                display: flex;
                flex-direction: column;
            }
            .sale-desk-main {
                flex: 1 1 auto;
                min-width: 0;
                width: auto;
                max-width: none;
                box-sizing: border-box;
            }
            .sale-desk-top {
                height: 64px;
                background: #fff;
                border-bottom: 1px solid #e2e8f0;
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 0 28px;
                position: sticky;
                top: 0;
                z-index: 40;
            }
            .sale-side-link {
                display: flex; align-items: center; gap: 12px;
                padding: 10px 10px; border-radius: 12px;
                color: rgba(255,255,255,.72); text-decoration: none;
                font-weight: 600; font-size: 14px; margin-bottom: 6px;
            }
            .sale-side-link:hover { background: rgba(255,255,255,.08); color: #fff; }
            .sale-side-link.active { background: #0f766e; color: #fff; }
            .sale-side-ico {
                width: 36px; height: 36px; border-radius: 10px;
                background: rgba(255,255,255,.1);
                display: flex; align-items: center; justify-content: center;
                flex-shrink: 0;
            }
            .sale-side-link.active .sale-side-ico { background: rgba(255,255,255,.18); }
            .sale-side-ico svg {
                width: 18px; height: 18px; display: block;
                stroke: currentColor; fill: none; stroke-width: 1.8;
                stroke-linecap: round; stroke-linejoin: round;
            }
            .sale-side-logout {
                display: flex;
                align-items: center;
                gap: 10px;
                width: 100%;
                padding: 11px 12px;
                border-radius: 10px;
                border: 1px solid rgba(255,255,255,.12);
                background: transparent;
                color: #fda4af;
                font-weight: 600;
                font-size: 14px;
                cursor: pointer;
                text-align: left;
                font-family: inherit;
            }
            .sale-side-logout:hover {
                background: rgba(244, 63, 94, .15);
                border-color: rgba(244, 63, 94, .35);
                color: #fecdd3;
            }
            .sale-table { width: 100%; border-collapse: collapse; font-size: 14px; }
            .sale-table th {
                text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: .06em;
                color: #64748b; padding: 10px 12px; border-bottom: 1px solid #e2e8f0; font-weight: 700;
            }
            .sale-table td { padding: 14px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
            .sale-table tr:hover td { background: #f8fafc; }
            .sale-flash { margin: 12px 28px 0; max-width: none; }

            /* Desktop page grids — full main width */
            .sale-layout-2 {
                display: grid;
                grid-template-columns: minmax(0, 1fr) minmax(300px, 360px);
                gap: 24px;
                align-items: start;
                width: 100%;
            }
            .sale-layout-2 > * { min-width: 0; width: 100%; }
            .sale-create-form {
                display: block;
                width: 100%;
                max-width: none;
                margin: 0;
                min-height: calc(100dvh - 64px - 48px);
            }
            .sale-layout-create {
                display: grid !important;
                grid-template-columns: minmax(0, 1fr) 380px;
                gap: 24px;
                align-items: stretch;
                width: 100% !important;
                max-width: none !important;
                margin: 0 !important;
                min-height: calc(100dvh - 64px - 72px);
                box-sizing: border-box;
            }
            .sale-layout-create > * { min-width: 0; width: 100%; }
            .sale-create-main {
                display: flex;
                flex-direction: column;
                gap: 16px;
                width: 100%;
                min-height: 100%;
            }
            .sale-create-products {
                flex: 1 1 auto;
                min-height: 420px;
                display: flex;
                flex-direction: column;
            }
            .sale-create-side {
                width: 100%;
            }
            .sale-layout-account {
                display: grid;
                grid-template-columns: minmax(0, 1fr) minmax(280px, 360px);
                gap: 24px;
                align-items: start;
                width: 100%;
            }
            .sale-layout-fields {
                display: grid;
                grid-template-columns: 220px minmax(0, 1fr);
                gap: 16px;
                align-items: start;
            }
            .sale-layout-fields .sale-field-full { grid-column: auto; }
            .sale-stack { display: flex; flex-direction: column; gap: 16px; width: 100%; }
            .sale-sticky-panel { position: sticky; top: 88px; }
            /* Create page: edge-to-edge content inside main */
            body.sale-page-create .sale-page {
                padding: 20px 24px 24px;
                width: 100%;
                max-width: none !important;
            }
            .sale-dash-stats {
                grid-template-columns: repeat(4, 1fr);
            }
            .sale-stat--primary { grid-column: auto; }
            .sale-dash-actions {
                grid-template-columns: 320px 220px;
            }
        }

        @media (max-width: 1023px) {
            .sale-d-flex { display: none !important; }
            .sale-desk-shell { display: block; }
            .sale-desk-side, .sale-desk-top { display: none !important; }
        }

        .product-pick:active, .cust-pick:active { background: #f8fafc; }

        /* Mobile create stack (overridden by desktop grid) */
        .sale-create-form { width: 100%; }
        .sale-layout-create {
            display: flex;
            flex-direction: column;
            gap: 12px;
            width: 100%;
        }
        .sale-create-main { display: flex; flex-direction: column; gap: 12px; width: 100%; }
        .sale-create-side { width: 100%; }
        .sale-layout-fields { display: block; width: 100%; }
    </style>
    @stack('head')
</head>
<body class="antialiased {{ auth()->check() ? 'sale-authed' : 'sale-guest' }} {{ (optional(request()->route())->getName() === 'sale.orders.create') ? 'sale-page-create' : '' }}">
@php
    $routeName = optional(request()->route())->getName();
    $isHome = $routeName === 'sale.home';
    $isOrders = in_array($routeName, ['sale.orders', 'sale.orders.show'], true);
    $isCreate = $routeName === 'sale.orders.create';
    $isCustomers = in_array($routeName, ['sale.customers', 'sale.customers.create'], true);
    $isAccount = $routeName === 'sale.account';
    $customerMenuUrl = route('sale.customers');
    $authUser = auth()->user();
    $userInitial = 'S';
    $userName = '';
    if ($authUser) {
        $userInitial = strtoupper(mb_substr($authUser->first_name ?? $authUser->username ?? 'S', 0, 1));
        $userName = trim(($authUser->first_name ?? '').' '.($authUser->last_name ?? '')) ?: ($authUser->username ?? '');
    }
@endphp

@auth
<div class="sale-desk-shell">
    {{-- Desktop sidebar --}}
    <aside class="sale-desk-side sale-d-only">
        <div class="flex items-center gap-3 px-2 mb-8">
            <img src="{{ asset('pwa/sale-icon-192.png') }}" alt="" class="h-10 w-10 rounded-xl bg-sale">
            <div>
                <div class="font-extrabold text-[15px] leading-tight">Sales App</div>
                <div class="text-[11px] text-white/50 font-semibold">Representative</div>
            </div>
        </div>
        <nav>
            <a href="{{ route('sale.home') }}" class="sale-side-link {{ $isHome ? 'active' : '' }}">
                <span class="sale-side-ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-9.5z"/></svg>
                </span>
                Dashboard
            </a>
            <a href="{{ route('sale.orders') }}" class="sale-side-link {{ $isOrders ? 'active' : '' }}">
                <span class="sale-side-ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                </span>
                Order list
            </a>
            <a href="{{ route('sale.orders.create') }}" class="sale-side-link {{ $isCreate ? 'active' : '' }}">
                <span class="sale-side-ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/></svg>
                </span>
                Create order
            </a>
            <a href="{{ $customerMenuUrl }}" class="sale-side-link {{ $isCustomers ? 'active' : '' }}">
                <span class="sale-side-ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M2 19c1.2-3.2 6.8-3.2 8 0"/><circle cx="17" cy="8" r="2.5"/><path d="M14 19c.8-2.4 5.2-2.4 6 0"/></svg>
                </span>
                Customers
            </a>
            <a href="{{ route('sale.account') }}" class="sale-side-link {{ $isAccount ? 'active' : '' }}">
                <span class="sale-side-ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c1.5-4 14.5-4 16 0"/></svg>
                </span>
                Account
            </a>
        </nav>
        <div class="sale-side-footer px-1">
            <div class="px-2 mb-3">
                <div class="text-[11px] text-white/40 mb-1">Signed in</div>
                <div class="text-sm font-semibold truncate">{{ $userName }}</div>
            </div>
            <form method="POST" action="{{ route('sale.logout') }}">
                @csrf
                <button type="submit" class="sale-side-logout">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 18 18" aria-hidden="true">
                        <path d="M7 3H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h3"/>
                        <path d="M12 12l3-3-3-3M7 9h8"/>
                    </svg>
                    Sign out
                </button>
            </form>
        </div>
    </aside>

    <div class="sale-desk-main">
        {{-- Desktop top bar --}}
        <header class="sale-desk-top sale-d-flex">
            <div>
                <div class="text-[11px] uppercase tracking-wider font-bold text-slate-400">Sales workstation</div>
                <div class="font-extrabold text-lg leading-tight">@yield('header', 'Orders')</div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('sale.orders.create') }}" class="sale-btn-sm sale-d-only">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
                    Create order
                </a>
                <div class="h-10 w-10 rounded-full bg-sale text-white flex items-center justify-center font-bold">{{ $userInitial }}</div>
            </div>
        </header>

        {{-- Mobile app header --}}
        <header class="sale-m-only sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-sale-line pt-[env(safe-area-inset-top,0)]">
            <div class="h-14 px-4 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <div class="text-[10px] uppercase tracking-wider font-bold text-slate-400">Sales rep</div>
                    <div class="font-extrabold text-[15px] truncate">@yield('header', 'Orders')</div>
                </div>
                <div class="shrink-0 h-9 w-9 rounded-full bg-sale text-white flex items-center justify-center font-bold text-sm">{{ $userInitial }}</div>
            </div>
        </header>

        @if(session('status'))
            @php $st = session('status'); @endphp
            <div class="sale-flash mx-3 mt-3 rounded-xl px-3 py-2.5 text-sm font-semibold {{ !empty($st['success']) ? 'bg-emerald-50 text-emerald-900 border border-emerald-200' : 'bg-rose-50 text-rose-900 border border-rose-200' }}">
                {{ is_array($st) ? ($st['msg'] ?? '') : $st }}
            </div>
        @endif

        <main class="sale-main-app px-3 py-3 w-full">
            <div class="sale-page w-full">
                @yield('content')
            </div>
        </main>
    </div>
</div>

{{-- Mobile bottom: left | Create (center) | right --}}
<nav class="sale-bottom-nav sale-m-only" aria-label="Primary">
    <div class="sale-bottom-nav__inner">
        <div class="sale-bottom-nav__side">
            <a href="{{ route('sale.home') }}" class="sale-tab {{ $isHome ? 'active' : '' }}">
                <svg viewBox="0 0 24 24"><path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-9.5z"/></svg>
                Home
            </a>
            <a href="{{ route('sale.orders') }}" class="sale-tab {{ $isOrders ? 'active' : '' }}">
                <svg viewBox="0 0 24 24"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                Orders
            </a>
        </div>
        <div class="sale-bottom-nav__center">
            <a href="{{ route('sale.orders.create') }}" class="sale-tab-fab {{ $isCreate ? 'active' : '' }}" aria-label="Create order">
                <svg viewBox="0 0 24 24" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
            </a>
            <span class="sale-bottom-nav__center-label {{ $isCreate ? 'active' : '' }}">Create</span>
        </div>
        <div class="sale-bottom-nav__side">
            <a href="{{ $customerMenuUrl }}" class="sale-tab {{ $isCustomers ? 'active' : '' }}">
                <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M2 19c1.2-3.2 6.8-3.2 8 0"/><circle cx="17" cy="8" r="2.5"/><path d="M14 19c.8-2.4 5.2-2.4 6 0"/></svg>
                Customers
            </a>
            <a href="{{ route('sale.account') }}" class="sale-tab {{ $isAccount ? 'active' : '' }}">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c1.5-4 14.5-4 16 0"/></svg>
                Account
            </a>
        </div>
    </div>
</nav>

@else
    @if(session('status'))
        @php $st = session('status'); @endphp
        <div class="mx-3 mt-3 rounded-xl px-3 py-2.5 text-sm font-semibold {{ !empty($st['success']) ? 'bg-emerald-50 text-emerald-900 border border-emerald-200' : 'bg-rose-50 text-rose-900 border border-rose-200' }}">
            {{ is_array($st) ? ($st['msg'] ?? '') : $st }}
        </div>
    @endif
    <main>
        @yield('content')
    </main>
@endauth

{{-- PWA install: always available (login + logged-in) --}}
<div id="sale_pwa_install_bar" class="sale-install" hidden>
    <div class="sale-install__inner">
        <img src="{{ asset('pwa/sale-icon-192.png') }}" alt="" class="sale-install__logo" width="36" height="36">
        <div class="flex-1 min-w-0">
            <div class="text-[12px] font-bold">Install Sales app</div>
            <div class="text-[10px] text-white/70">Faster access — open from home screen</div>
        </div>
        <button type="button" id="sale_pwa_install_btn" class="sale-install__btn">Install</button>
        <button type="button" id="sale_pwa_dismiss_btn" class="sale-install__close" aria-label="Dismiss">×</button>
    </div>
</div>

<script>
window.__SALE_PWA__ = { swUrl: @json(url('/sale/pwa/sw.js')), startUrl: @json(url('/sale/login')) };
</script>
<script src="{{ asset('js/sale-pwa.js') }}?v={{ config('app.asset_version', '1') }}" defer></script>
@stack('scripts')
</body>
</html>
