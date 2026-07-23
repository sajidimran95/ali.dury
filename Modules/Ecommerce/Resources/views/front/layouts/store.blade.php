<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
    </style>
    @stack('head')
</head>
<body class="antialiased">
@php
    $nav_categories = $nav_categories ?? collect();
    $store = $ecom_settings->store_name ?? 'Japs Wholesale';
@endphp

{{-- Top promo / contact bar --}}
<div class="bg-brand text-white text-[11px] sm:text-xs tracking-wide">
    <div class="max-w-[1400px] mx-auto px-4 py-2 flex flex-wrap items-center justify-between gap-2">
        <p class="font-semibold uppercase">Welcome to {{ $store }}</p>
        <p class="opacity-95">Wholesale distribution · USA · <a href="{{ route('ecommerce.contact') }}" class="underline underline-offset-2">Contact us</a></p>
    </div>
</div>

{{-- Nicotine / compliance warning --}}
<div class="bg-[#ececec] text-[#444] text-[11px] sm:text-xs text-center px-4 py-2 border-b border-black/5">
    <strong>WARNING:</strong> This product contains nicotine. Nicotine is an addictive chemical. Wholesale accounts only — age verification required.
</div>

{{-- Main header --}}
<header class="bg-white border-b border-line sticky top-0 z-50">
    <div class="max-w-[1400px] mx-auto px-4">
        <div class="h-[72px] flex items-center gap-4 lg:gap-6">
            <button id="navToggle" class="lg:hidden p-2 -ml-2" aria-label="Menu">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h16M3 11h16M3 16h16"/></svg>
            </button>

            <a href="{{ url('/') }}" class="shrink-0 flex items-center gap-2">
                <span class="h-9 w-9 rounded-full bg-brand text-white flex items-center justify-center font-extrabold text-sm">J</span>
                <span class="leading-tight hidden sm:block">
                    <span class="block font-extrabold text-[15px] text-ink">{{ $store }}</span>
                    <span class="block text-[10px] uppercase tracking-wider text-black/45">Wholesale Distribution</span>
                </span>
            </a>

            <nav class="hidden lg:flex items-center gap-5 text-sm font-semibold">
                <a href="{{ url('/') }}" class="hover:text-brand">Home</a>
                <a href="{{ route('ecommerce.shop') }}" class="hover:text-brand">Shop</a>
                <a href="{{ route('ecommerce.shop', ['sort' => 'newest']) }}" class="hover:text-brand">Quick Order</a>
                <div class="relative group">
                    <button type="button" class="hover:text-brand inline-flex items-center gap-1">More
                        <svg width="12" height="12" viewBox="0 0 20 20" fill="currentColor"><path d="M5 8l5 5 5-5"/></svg>
                    </button>
                    <div class="hidden group-hover:block absolute left-0 top-full pt-2 z-40">
                        <div class="bg-white border border-line rounded-lg shadow-lg py-2 min-w-[180px] text-sm font-medium">
                            <a href="{{ route('ecommerce.track') }}" class="block px-4 py-2 hover:bg-mist">Track order</a>
                            <a href="{{ route('ecommerce.contact') }}" class="block px-4 py-2 hover:bg-mist">Contact</a>
                            <a href="{{ route('ecommerce.account') }}" class="block px-4 py-2 hover:bg-mist">My account</a>
                        </div>
                    </div>
                </div>
            </nav>

            <form action="{{ route('ecommerce.shop') }}" method="get" class="hidden md:flex flex-1 max-w-xl mx-auto">
                <div class="flex w-full rounded-full border border-line overflow-hidden bg-mist focus-within:bg-white focus-within:border-brand">
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Search products, SKU, brands..."
                           class="flex-1 bg-transparent border-0 outline-none text-sm px-4 py-2.5">
                    <button class="bg-brand text-white px-4 hover:bg-[#c44d03]" aria-label="Search">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="8" r="5.5"/><path d="M12 12l4 4"/></svg>
                    </button>
                </div>
            </form>

            <div class="ml-auto flex items-center gap-3 sm:gap-5 text-sm font-semibold shrink-0">
                @auth('customer')
                    <a href="{{ route('ecommerce.wishlist') }}" class="hidden sm:inline-flex items-center gap-1.5 hover:text-brand" title="Wishlist">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 15s-6-3.8-6-7.2A3.3 3.3 0 019 4.8a3.3 3.3 0 016 3C15 11.2 9 15 9 15z"/></svg>
                        <span class="hidden lg:inline">Wishlist</span>
                    </a>
                    <a href="{{ route('ecommerce.account') }}" class="hidden sm:inline-flex items-center gap-1.5 hover:text-brand" title="Account">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="6" r="3"/><path d="M3 16c1.5-3 10.5-3 12 0"/></svg>
                        <span class="hidden lg:inline">Account</span>
                    </a>
                @else
                    <a href="{{ route('ecommerce.login') }}" class="hidden sm:inline-flex items-center gap-1.5 hover:text-brand" title="Account">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="6" r="3"/><path d="M3 16c1.5-3 10.5-3 12 0"/></svg>
                        <span class="hidden md:inline">Login</span>
                    </a>
                @endauth
                <a href="{{ route('ecommerce.cart') }}" class="inline-flex items-center gap-2 hover:text-brand">
                    <span class="relative">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 4h12l-1 9H4L3 4z"/><path d="M7 7V4a2 2 0 014 0v3"/></svg>
                        <span class="absolute -top-2 -right-2 bg-brand text-white text-[10px] rounded-full min-w-[16px] h-[16px] inline-flex items-center justify-center px-1">{{ $cart_totals['count'] ?? 0 }}</span>
                    </span>
                    <span class="hidden sm:inline tabular-nums">${{ number_format($cart_totals['subtotal'] ?? 0, 2) }}</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Category mega nav --}}
    <div class="border-t border-line bg-white hidden lg:block relative z-40">
        <div class="max-w-[1400px] mx-auto px-4 cat-nav-wrap" id="catNavWrap">
            <ul class="flex items-stretch gap-0 text-[13px] font-semibold">
                @foreach($nav_categories as $cat)
                    @php
                        $subs = $cat->sub_categories ?? collect();
                        $megaBrands = $cat->mega_brands ?? collect();
                    @endphp
                    <li class="mega">
                        <a href="{{ route('ecommerce.category', $cat->id) }}"
                           class="cat-link inline-flex items-center gap-1 px-3 py-3.5 whitespace-nowrap {{ (request('category') == $cat->id || (string) request()->route('slug') === (string) $cat->id) ? 'text-brand' : '' }}">
                            {{ $cat->name }}
                            <svg width="10" height="10" viewBox="0 0 20 20" fill="currentColor" class="opacity-50"><path d="M5 8l5 5 5-5"/></svg>
                        </a>

                        <div class="mega-panel">
                            {{-- Brands (left) --}}
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

                            {{-- Subcategories (right) --}}
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

    {{-- Mobile drawer --}}
    <div id="mobileNav" class="hidden lg:hidden border-t border-line bg-white px-4 py-4 space-y-1">
        <form action="{{ route('ecommerce.shop') }}" method="get" class="mb-3">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search products..." class="input-w">
        </form>
        <a href="{{ url('/') }}" class="block py-2.5 font-semibold">Home</a>
        <a href="{{ route('ecommerce.shop') }}" class="block py-2.5 font-semibold">Shop</a>
        @foreach($nav_categories as $cat)
            <a href="{{ route('ecommerce.category', $cat->id) }}" class="block py-2 pl-1">{{ $cat->name }}</a>
            @foreach(($cat->sub_categories ?? []) as $sub)
                <a href="{{ route('ecommerce.category', $sub->id) }}" class="block py-1.5 pl-4 text-sm text-black/60">{{ $sub->name }}</a>
            @endforeach
        @endforeach
        <a href="{{ route('ecommerce.login') }}" class="block py-2.5 font-semibold text-brand">Login or Sign Up</a>
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

<footer class="mt-16 bg-slatebar text-white">
    <div class="max-w-[1400px] mx-auto px-4 py-12 grid md:grid-cols-4 gap-8">
        <div class="md:col-span-2">
            <div class="font-extrabold text-xl mb-2">{{ $store }}</div>
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

<script>
document.getElementById('navToggle')?.addEventListener('click', () => {
    document.getElementById('mobileNav')?.classList.toggle('hidden');
});

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
</body>
</html>
