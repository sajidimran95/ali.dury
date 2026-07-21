<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $ecom_settings->seo_title ?? $ecom_settings->store_name ?? 'Store')</title>
    <meta name="description" content="@yield('meta_description', $ecom_settings->seo_description ?? '')">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        ink: '#0b1220',
                        mist: '#f4f7fb',
                        accent: '#1d4ed8',
                        accent2: '#0ea5e9'
                    },
                    fontFamily: {
                        display: ['"Segoe UI"', 'system-ui', 'sans-serif'],
                        body: ['"Segoe UI"', 'system-ui', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: "Segoe UI", system-ui, sans-serif; }
        .hero-gradient { background: radial-gradient(1200px 600px at 10% 0%, #dbeafe 0%, transparent 50%), linear-gradient(135deg, #0b1220 0%, #1e3a5f 55%, #0ea5e9 140%); }
        .product-card:hover { transform: translateY(-3px); }
        .product-card { transition: transform .2s ease, box-shadow .2s ease; }
    </style>
    @stack('head')
</head>
<body class="bg-mist text-ink antialiased">
<header class="sticky top-0 z-40 bg-white/90 backdrop-blur border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center gap-4">
        <a href="{{ url('/') }}" class="font-semibold text-lg tracking-tight shrink-0">
            {{ $ecom_settings->store_name ?? 'Store' }}
        </a>
        <form action="{{ route('ecommerce.shop') }}" method="get" class="hidden md:flex flex-1 max-w-xl mx-auto">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search products..."
                   class="w-full rounded-l-full border border-slate-200 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-accent/30">
            <button class="rounded-r-full bg-ink text-white px-5 text-sm">Search</button>
        </form>
        <nav class="flex items-center gap-3 text-sm ml-auto">
            <a href="{{ route('ecommerce.shop') }}" class="hidden sm:inline hover:text-accent">Shop</a>
            @auth('customer')
                <a href="{{ route('ecommerce.wishlist') }}" class="hover:text-accent">Wishlist</a>
                <a href="{{ route('ecommerce.account') }}" class="hover:text-accent">Account</a>
            @else
                <a href="{{ route('ecommerce.login') }}" class="hover:text-accent">Wishlist</a>
                <a href="{{ route('ecommerce.login') }}" class="hover:text-accent">Account</a>
            @endauth
            <a href="{{ route('ecommerce.cart') }}" class="inline-flex items-center gap-1 rounded-full bg-ink text-white px-3 py-1.5">
                Cart
                <span class="bg-white/20 rounded-full px-1.5 text-xs">{{ $cart_totals['count'] ?? 0 }}</span>
            </a>
            <button id="navToggle" class="md:hidden p-2" aria-label="Menu">☰</button>
        </nav>
    </div>
    <div id="mobileNav" class="hidden md:hidden border-t px-4 py-3 space-y-2 bg-white">
        <a href="{{ route('ecommerce.shop') }}" class="block">Shop</a>
        <a href="{{ route('ecommerce.contact') }}" class="block">Contact</a>
        <a href="{{ route('ecommerce.track') }}" class="block">Track Order</a>
    </div>
</header>

@if(session('success'))
    <div class="bg-emerald-50 text-emerald-800 text-sm text-center py-2">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="bg-rose-50 text-rose-800 text-sm text-center py-2">{{ session('error') }}</div>
@endif

<main>@yield('content')</main>

<footer class="mt-16 border-t border-slate-200 bg-white">
    <div class="max-w-7xl mx-auto px-4 py-10 grid md:grid-cols-3 gap-8 text-sm text-slate-600">
        <div>
            <div class="font-semibold text-ink mb-2">{{ $ecom_settings->store_name ?? 'Store' }}</div>
            <p>{{ $ecom_settings->seo_description ?? 'Premium products, delivered across the USA.' }}</p>
        </div>
        <div>
            <div class="font-semibold text-ink mb-2">Help</div>
            <a class="block hover:text-accent" href="{{ route('ecommerce.contact') }}">Contact</a>
            <a class="block hover:text-accent" href="{{ route('ecommerce.track') }}">Track Order</a>
        </div>
        <div>
            <div class="font-semibold text-ink mb-2">Account</div>
            <a class="block hover:text-accent" href="{{ route('ecommerce.login') }}">Login</a>
            <a class="block hover:text-accent" href="{{ route('ecommerce.register') }}">Register</a>
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
