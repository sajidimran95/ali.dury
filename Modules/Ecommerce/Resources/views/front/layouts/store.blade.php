<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#dd772d">
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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: '#dd772d',
                        brandDark: '#c4621f',
                        ink: '#060606',
                        slatebar: '#ffffff',
                        mist: '#f5f5f5',
                        line: '#e6e6e6'
                    },
                    fontFamily: {
                        sans: ['Montserrat', 'Poppins', 'Roboto', 'system-ui', 'sans-serif'],
                        serif: ['Roboto', 'Montserrat', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: Montserrat, Poppins, Roboto, system-ui, sans-serif; background: #fff; color: #111; }
        .pc-qty-input::-webkit-outer-spin-button,
        .pc-qty-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        .pc-qty-input { -moz-appearance: textfield; }
        @media (min-width: 1024px) {
            .app-mobile-only { display: none !important; }
            .app-bottom-nav { display: none !important; }
            .app-sheet { display: none !important; }
        }
        @media (max-width: 1023px) {
            .hidden.lg\:block { display: none !important; }
            header.app-mobile-only { display: block !important; }
            .app-bottom-nav { display: flex !important; }
        }
        body.gs-store .header-categories .category-item {
            color: rgb(81, 35, 2) !important;
            font-family: Roboto, sans-serif !important;
            font-size: 18px !important;
            font-weight: 500 !important;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/gs-storefront.css') }}?v={{ config('app.asset_version', '1') }}&gs=26">
    <link rel="stylesheet" href="{{ asset('css/gs-shop.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('css/gs-cart.css') }}?v=1">
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
        if ($rel !== '' && preg_match('#^https?://#i', $rel)) {
            $storeLogoUrl = $rel;
            break;
        }
        if ($rel !== '' && file_exists(public_path('uploads/'.$rel))) {
            $storeLogoUrl = asset('uploads/'.$rel);
            break;
        }
    }
    if (! $storeLogoUrl) {
        $gsMediaFile = storage_path('app/ecom_gs_media.json');
        if (is_file($gsMediaFile)) {
            $gsMedia = json_decode(file_get_contents($gsMediaFile), true) ?: [];
            if (! empty($gsMedia['logo'])) {
                $storeLogoUrl = $gsMedia['logo'];
            }
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
<body class="antialiased app-mobile gc-store gs-store{{ ! empty($isAccountPanel) ? ' ecom-account-panel' : '' }}">

@unless(in_array($routeName, ['ecommerce.shop', 'ecommerce.category', 'ecommerce.cart'], true) || request()->is('*/cart') || request()->is('cart'))
<div class="gs-ticker">WELCOME TO {{ strtoupper($store) }}@if($storePhone !== '') | {{ $storePhone }}@endif</div>
<div class="gs-warn">WARNING: This product contains nicotine. Nicotine is an addictive chemical.</div>
@endunless

<div id="gcAgeGate" class="gc-age" role="dialog" aria-label="Age verification">
    <div class="gc-age__card">
        @if($storeLogoUrl)
            <img src="{{ $storeLogoUrl }}" alt="{{ $store }}" class="mx-auto mb-3 h-10 w-auto object-contain">
        @endif
        <h2 class="text-xl font-semibold mb-2">Age Verification</h2>
        <p class="text-sm text-black/70 mb-5">This Website offers tobacco products and is strictly for adult use only. Before you proceed further, please confirm that you are 21 years of age or older.</p>
        <div class="flex justify-center gap-2">
            <button type="button" id="gcAgeYes" class="rounded-lg bg-[#dd772d] text-white font-semibold px-5 py-2">21+ Enter</button>
            <button type="button" id="gcAgeNo" class="rounded-lg bg-[#f2f2f2] text-black font-semibold px-5 py-2">Under 21</button>
        </div>
    </div>
</div>

{{-- Mobile app header --}}
<header class="app-mobile-only sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-[#eee] pt-[env(safe-area-inset-top,0)]">
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

@php
    $gsChevron = '<svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"></path></svg>';
@endphp
<div class="hidden lg:block sticky top-0 z-50 gs-header">
<header class="header-tmpl">
    <nav class="header-tmpl-nav">
        <div class="header-branding">
            <a href="{{ route('ecommerce.home') }}" aria-label="{{ $store }}">
                @if($storeLogoUrl)
                    <img src="{{ $storeLogoUrl }}" alt="{{ $store }}">
                @else
                    <span class="gs-logo-fallback">{{ $store }}</span>
                @endif
            </a>
        </div>
        <div class="header-navigation">
            <a href="{{ route('ecommerce.home') }}" class="navigation-item {{ $isHome ? 'active' : '' }}">Home</a>
            <a href="{{ route('ecommerce.shop') }}" class="navigation-item {{ $isShop ? 'active' : '' }}">Shop</a>
            <a href="{{ route('ecommerce.quick_order') }}" class="navigation-item">Quick Order</a>
            <div class="navigation-dropdown">
                <button type="button" class="navigation-item" aria-expanded="false">
                    More {!! $gsChevron !!}
                </button>
                <div class="dropdown-list">
                    <a href="{{ route('ecommerce.page', 'about-us') }}">About Us</a>
                    <a href="{{ route('ecommerce.contact') }}">Contact Us</a>
                </div>
            </div>
        </div>
        <div class="header-actions">
            <div class="action-block action-search-wrap">
                <form action="{{ route('ecommerce.shop') }}" method="get" class="action-search">
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Search">
                    <button type="submit" class="accent-button" aria-label="Search">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3-3"/></svg>
                    </button>
                </form>
            </div>
            <div class="action-block">
                <a href="{{ route('ecommerce.cart') }}" class="action-item">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h12l-1 9H5L4 6z"/><path d="M7 6V5a2.5 2.5 0 015 0v1"/></svg>
                    <span>View Cart</span>
                    @if(($cart_totals['count'] ?? 0) > 0)
                        <span>({{ $cart_totals['count'] }})</span>
                    @endif
                </a>
            </div>
            <div class="action-block">
                @auth('customer')
                    <a href="{{ route('ecommerce.account') }}" class="action-item">Account</a>
                @else
                    <a href="{{ route('ecommerce.login') }}" class="action-item">Login</a>
                    <span class="auth-or">or</span>
                    <a href="{{ route('ecommerce.register') }}" class="action-item">Sign Up</a>
                @endauth
            </div>
        </div>
    </nav>
    <div class="header-categories">
        @foreach($nav_categories as $cat)
            @php
                $subs = collect($cat->sub_categories ?? []);
                $megaBrands = collect($cat->mega_brands ?? []);
            @endphp
            <div class="mega {{ $subs->count() ? 'has-sub' : '' }}">
                <a href="{{ route('ecommerce.category', $cat->id) }}" class="category-item {{ $subs->count() ? 'has-sub-category' : '' }}">
                    {{ $cat->name }}
                    @if($subs->count())
                        {!! $gsChevron !!}
                    @endif
                </a>
                @if($subs->count() || $megaBrands->count())
                <div class="mega-menu">
                    <div class="dropdown-content">
                        <div class="brand-info">
                            <p class="brand-title">Brands</p>
                            @if($megaBrands->count())
                            <div class="mega-menu-brands-list">
                                @foreach($megaBrands->take(8) as $brand)
                                    <div class="mega-menu-brand-item">
                                        <a class="mega-menu-brand-link" href="{{ route('ecommerce.shop', ['brand' => $brand->id]) }}" title="{{ $brand->name }}">
                                            @if(!empty($brand->image))
                                                <img class="mega-menu-brand-image" src="{{ $brand->image }}" alt="{{ $brand->name }}">
                                            @else
                                                <span class="brand-alt">{{ $brand->name }}</span>
                                            @endif
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        <ul>
                            <b>{{ $cat->name }}</b>
                            @foreach($subs as $sub)
                                <li><a href="{{ route('ecommerce.category', $sub->id) }}">{{ $sub->name }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif
            </div>
        @endforeach
    </div>
</header>
</div>

@if(session('success'))
    <div class="bg-[#e8fff2] text-[#139c57] text-sm text-center py-2.5 border-b border-[#b7ebc8]">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="bg-[#fff1f0] text-[#cf1322] text-sm text-center py-2.5 border-b border-[#ffa39e]">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="bg-[#fff1f0] text-[#cf1322] text-sm text-center py-2.5 border-b border-[#ffa39e]">{{ $errors->first() }}</div>
@endif

<main>@yield('content')</main>

<footer class="desktop-footer mt-16 text-white">
    <div class="gs-footer-inner py-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
        <div>
            <h3 class="text-sm font-bold tracking-widest mb-4">SOCIALS</h3>
            <p class="text-white/60 text-sm leading-relaxed mb-4">{{ $ecom_settings->seo_description ?? $store }}</p>
        </div>
        <div>
            <h3 class="text-sm font-bold tracking-widest mb-4">HOME</h3>
            <a class="block text-sm mb-2 hover:text-white" href="{{ route('ecommerce.home') }}">Home</a>
            <h3 class="text-sm font-bold tracking-widest mt-6 mb-4">SHOP</h3>
            <a class="block text-sm mb-2 hover:text-white" href="{{ route('ecommerce.shop') }}">Shop</a>
            <a class="block text-sm mb-2 hover:text-white" href="{{ route('ecommerce.brands') }}">Brands</a>
        </div>
        <div>
            <h3 class="text-sm font-bold tracking-widest mb-4">QUICK LINKS</h3>
            <a class="block text-sm mb-2 hover:text-white" href="{{ route('ecommerce.page', 'about-us') }}">About Us</a>
            <a class="block text-sm mb-2 hover:text-white" href="{{ route('ecommerce.contact') }}">Contact Us</a>
            <a class="block text-sm mb-2 hover:text-white" href="{{ route('ecommerce.page', 'privacy-policy') }}">Privacy Policy</a>
            <a class="block text-sm mb-2 hover:text-white" href="{{ route('ecommerce.page', 'terms-of-service') }}">Terms of Service</a>
            <a class="block text-sm mb-2 hover:text-white" href="{{ route('ecommerce.account') }}">My Account</a>
        </div>
        <div>
            <h3 class="text-sm font-bold tracking-widest mb-4">CONTACT US</h3>
            @if($storePhone !== '')
                <p class="text-sm mb-2"><a href="tel:{{ $storePhoneHref }}">{{ $storePhone }}</a></p>
            @endif
            @if($storeEmail !== '')
                <p class="text-sm mb-2"><a href="mailto:{{ $storeEmail }}">{{ $storeEmail }}</a></p>
            @endif
            <p class="text-xs text-white/50 mt-3">{!! nl2br(e($storeHours)) !!}</p>
        </div>
    </div>
    <div class="border-t border-white/10">
        <div class="gs-wrap py-4 text-xs text-white/45">
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
            <div class="divide-y divide-line rounded-xl border border-line overflow-hidden bg-white">
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
    const setQty = (v) => {
        const n = parseInt(v, 10);
        if (!n || n < 1) { input.value = ''; return; }
        input.value = String(n);
    };
    minus.addEventListener('click', () => setQty((parseInt(input.value, 10) || 1) - 1));
    plus.addEventListener('click', () => setQty((parseInt(input.value, 10) || 0) + 1));
    input.addEventListener('change', () => setQty(input.value));
    form.addEventListener('submit', () => { if (!parseInt(input.value, 10)) input.value = '1'; });
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
