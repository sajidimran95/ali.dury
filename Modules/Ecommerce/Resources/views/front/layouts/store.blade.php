<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $ecom_settings->seo_title ?? $ecom_settings->store_name ?? 'Store')</title>
    <meta name="description" content="@yield('meta_description', $ecom_settings->seo_description ?? '')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        ink: '#0a0f1a',
                        mist: '#f6f4f1',
                        sand: '#efeae3',
                        accent: '#0f766e',
                        accent2: '#115e59',
                        soft: '#ffffff'
                    },
                    fontFamily: {
                        display: ['"Instrument Serif"', 'Georgia', 'serif'],
                        body: ['"DM Sans"', 'system-ui', 'sans-serif']
                    },
                    boxShadow: {
                        soft: '0 10px 40px rgba(10,15,26,.08)'
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: "DM Sans", system-ui, sans-serif; background: #f6f4f1; color: #0a0f1a; }
        .font-display { font-family: "Instrument Serif", Georgia, serif; }
        .hero-wash {
            background:
                radial-gradient(900px 500px at 85% 15%, rgba(15,118,110,.28), transparent 55%),
                radial-gradient(700px 420px at 10% 80%, rgba(17,94,89,.18), transparent 50%),
                linear-gradient(135deg, #07111f 0%, #0f2740 48%, #134e4a 100%);
        }
        .product-card { transition: transform .25s ease, box-shadow .25s ease; }
        .product-card:hover { transform: translateY(-4px); box-shadow: 0 18px 40px rgba(10,15,26,.12); }
        .nav-link { position: relative; }
        .nav-link:after {
            content: ""; position: absolute; left: 0; right: 0; bottom: -6px; height: 2px;
            background: #0f766e; transform: scaleX(0); transition: transform .2s ease;
        }
        .nav-link:hover:after, .nav-link.is-active:after { transform: scaleX(1); }
        .marquee { animation: marquee 28s linear infinite; }
        @keyframes marquee { from { transform: translateX(0); } to { transform: translateX(-50%); } }
        .hero-zoom { animation: heroZoom 18s ease-in-out infinite alternate; }
        @keyframes heroZoom { from { transform: scale(1.05); } to { transform: scale(1.12); } }
        .fade-up { animation: fadeUp .7s ease both; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: none; } }
        @media (prefers-reduced-motion: reduce) {
            .hero-zoom, .fade-up, .marquee { animation: none !important; }
        }
    </style>
    @stack('head')
</head>
<body class="antialiased">
@php $nav_categories = $nav_categories ?? collect(); @endphp

{{-- Top promo bar --}}
<div class="bg-ink text-white text-xs tracking-wide">
    <div class="max-w-7xl mx-auto px-4 py-2 flex items-center justify-between gap-4">
        <p class="opacity-90">Free shipping on orders over $50 · USA delivery</p>
        <div class="hidden sm:flex items-center gap-4 opacity-90">
            <a href="{{ route('ecommerce.track') }}" class="hover:opacity-100">Track order</a>
            <a href="{{ route('ecommerce.contact') }}" class="hover:opacity-100">Help</a>
        </div>
    </div>
</div>

<header class="sticky top-0 z-50 bg-soft/95 backdrop-blur border-b border-black/5">
    <div class="max-w-7xl mx-auto px-4">
        <div class="h-16 md:h-[4.5rem] flex items-center gap-4 md:gap-8">
            <button id="navToggle" class="md:hidden p-2 -ml-2" aria-label="Open menu">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 6h16M3 11h16M3 16h16"/></svg>
            </button>

            <a href="{{ url('/') }}" class="font-display text-2xl md:text-[1.75rem] tracking-tight shrink-0">
                {{ $ecom_settings->store_name ?? 'Store' }}
            </a>

            <nav class="hidden lg:flex items-center gap-6 text-[13px] font-medium ml-2">
                <a href="{{ route('ecommerce.shop') }}" class="nav-link {{ request()->routeIs('ecommerce.shop') ? 'is-active' : '' }}">All Products</a>
                @foreach($nav_categories->take(6) as $cat)
                    <a href="{{ route('ecommerce.category', $cat->id) }}" class="nav-link {{ (request('category') == $cat->id || request()->route('slug') == $cat->id || request()->route('slug') == $cat->slug) ? 'is-active' : '' }}">{{ $cat->name }}</a>
                @endforeach
            </nav>

            <div class="ml-auto flex items-center gap-2 md:gap-3">
                <form action="{{ route('ecommerce.shop') }}" method="get" class="hidden md:flex items-center bg-mist rounded-full px-3 py-2 w-56 lg:w-72 border border-black/5">
                    <svg class="opacity-50 shrink-0" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="7" cy="7" r="5"/><path d="M11 11l3.5 3.5"/></svg>
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Search products"
                           class="bg-transparent border-0 outline-none text-sm px-2 w-full placeholder:text-black/40">
                </form>

                @auth('customer')
                    <a href="{{ route('ecommerce.wishlist') }}" class="hidden sm:inline-flex h-10 w-10 items-center justify-center rounded-full hover:bg-mist" title="Wishlist">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M9 15s-6-3.8-6-7.2A3.3 3.3 0 019 4.8a3.3 3.3 0 016 3C15 11.2 9 15 9 15z"/></svg>
                    </a>
                    <a href="{{ route('ecommerce.account') }}" class="hidden sm:inline-flex h-10 w-10 items-center justify-center rounded-full hover:bg-mist" title="Account">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="9" cy="6" r="3"/><path d="M3 16c1.5-3 10.5-3 12 0"/></svg>
                    </a>
                @else
                    <a href="{{ route('ecommerce.login') }}" class="hidden sm:inline-flex h-10 px-3 items-center text-sm font-medium hover:text-accent">Sign in</a>
                @endauth

                <a href="{{ route('ecommerce.cart') }}" class="inline-flex items-center gap-2 rounded-full bg-ink text-white h-10 px-4 text-sm font-medium hover:bg-accent2 transition">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 4h12l-1 9H4L3 4z"/><path d="M7 7V4a2 2 0 014 0v3"/></svg>
                    <span class="tabular-nums">{{ $cart_totals['count'] ?? 0 }}</span>
                </a>
            </div>
        </div>

        {{-- Secondary category strip --}}
        <div class="hidden md:flex items-center gap-1 pb-3 -mx-1 overflow-x-auto text-[12px]">
            <a href="{{ route('ecommerce.shop') }}" class="shrink-0 px-3 py-1.5 rounded-full {{ !request('category') && request()->routeIs('ecommerce.shop') ? 'bg-ink text-white' : 'bg-mist hover:bg-sand' }}">Shop all</a>
            @foreach($nav_categories as $cat)
                <a href="{{ route('ecommerce.category', $cat->id) }}"
                   class="shrink-0 px-3 py-1.5 rounded-full {{ (request('category') == $cat->id || (string) request()->route('slug') === (string) $cat->id || request()->route('slug') === $cat->slug) ? 'bg-ink text-white' : 'bg-mist hover:bg-sand' }}">
                    {{ $cat->name }}
                </a>
            @endforeach
            <a href="{{ route('ecommerce.shop', ['sort' => 'newest']) }}" class="shrink-0 px-3 py-1.5 rounded-full bg-mist hover:bg-sand">New</a>
        </div>
    </div>

    <div id="mobileNav" class="hidden md:hidden border-t border-black/5 bg-soft px-4 py-4 space-y-1">
        <form action="{{ route('ecommerce.shop') }}" method="get" class="mb-3">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search products"
                   class="w-full rounded-xl border border-black/10 px-3 py-2.5 text-sm">
        </form>
        <a href="{{ route('ecommerce.shop') }}" class="block py-2.5 font-medium">All Products</a>
        @foreach($nav_categories as $cat)
            <a href="{{ route('ecommerce.category', $cat->id) }}" class="block py-2.5">{{ $cat->name }}</a>
        @endforeach
        <a href="{{ route('ecommerce.contact') }}" class="block py-2.5">Contact</a>
        <a href="{{ route('ecommerce.login') }}" class="block py-2.5">Account</a>
    </div>
</header>

@if(session('success'))
    <div class="bg-emerald-50 text-emerald-900 text-sm text-center py-2.5 border-b border-emerald-100">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="bg-rose-50 text-rose-900 text-sm text-center py-2.5 border-b border-rose-100">{{ session('error') }}</div>
@endif

<main>@yield('content')</main>

<footer class="mt-20 bg-ink text-white">
    <div class="max-w-7xl mx-auto px-4 py-14 grid md:grid-cols-4 gap-10">
        <div class="md:col-span-2">
            <div class="font-display text-3xl mb-3">{{ $ecom_settings->store_name ?? 'Store' }}</div>
            <p class="text-white/65 text-sm max-w-md leading-relaxed">
                {{ $ecom_settings->seo_description ?? 'A modern storefront powered by live POS inventory — curated products, transparent pricing, USA shipping.' }}
            </p>
        </div>
        <div>
            <div class="text-sm font-semibold mb-3 tracking-wide uppercase text-white/50">Shop</div>
            <div class="space-y-2 text-sm text-white/80">
                <a class="block hover:text-white" href="{{ route('ecommerce.shop') }}">All products</a>
                @foreach($nav_categories->take(5) as $cat)
                    <a class="block hover:text-white" href="{{ route('ecommerce.category', $cat->id) }}">{{ $cat->name }}</a>
                @endforeach
            </div>
        </div>
        <div>
            <div class="text-sm font-semibold mb-3 tracking-wide uppercase text-white/50">Help</div>
            <div class="space-y-2 text-sm text-white/80">
                <a class="block hover:text-white" href="{{ route('ecommerce.contact') }}">Contact</a>
                <a class="block hover:text-white" href="{{ route('ecommerce.track') }}">Track order</a>
                <a class="block hover:text-white" href="{{ route('ecommerce.login') }}">Account</a>
                <a class="block hover:text-white" href="{{ route('ecommerce.register') }}">Register</a>
            </div>
        </div>
    </div>
    <div class="border-t border-white/10">
        <div class="max-w-7xl mx-auto px-4 py-4 text-xs text-white/45 flex flex-wrap justify-between gap-2">
            <span>© {{ date('Y') }} {{ $ecom_settings->store_name ?? 'Store' }}. All rights reserved.</span>
            <span>Inventory synced with POS</span>
        </div>
    </div>
</footer>

<script>
document.getElementById('navToggle')?.addEventListener('click', () => {
    document.getElementById('mobileNav')?.classList.toggle('hidden');
});
</script>
@stack('scripts')
</body>
</html>
