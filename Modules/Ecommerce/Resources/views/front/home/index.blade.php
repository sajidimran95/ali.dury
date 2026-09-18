@extends('ecommerce::front.layouts.store')

@section('title', 'Greencloud Wholesale | Wisconsin Smoke & Vape Distributor')

@section('content')
@php
    $navBrands = $nav_brands ?? collect();
    $heroSlides = [
        [
            'kicker' => 'Wholesale only — licensed retailers',
            'title_html' => "Wisconsin's Premier<br>Wholesale<br>Distributor",
            'sub' => 'Smoke, vape, hemp & accessories — competitive pricing for licensed retailers.',
            'cta' => 'Shop All Products',
            'cta2' => 'Apply for Account',
            'href' => route('ecommerce.shop'),
            'href2' => route('ecommerce.register'),
            'img' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1600&q=70',
        ],
        [
            'kicker' => 'Wholesale only — licensed retailers',
            'title_html' => 'Top Disposable Vapes<br>in Stock Now',
            'sub' => 'Lost Mary, RAZ, HQD and more — best-selling brands at wholesale prices.',
            'cta' => 'Shop Disposables',
            'cta2' => 'View All Brands',
            'href' => route('ecommerce.shop'),
            'href2' => route('ecommerce.brands'),
            'img' => 'https://images.unsplash.com/photo-1484480974693-6ca0a78fb36b?auto=format&fit=crop&w=1600&q=70',
        ],
        [
            'kicker' => 'Wholesale only — licensed retailers',
            'title_html' => 'Glass, Papers & Accessories<br>Ready to Ship',
            'sub' => 'RAW, GRAV, Clipper and more — stock your shelves today.',
            'cta' => 'Shop Accessories',
            'cta2' => 'View Promotions',
            'href' => route('ecommerce.shop'),
            'href2' => route('ecommerce.promotions'),
            'img' => 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=1600&q=70',
        ],
    ];
    $brandImages = [
        'default' => 'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?auto=format&fit=crop&w=400&q=60',
    ];
    $catImages = [
        'default' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&w=400&q=60',
    ];
    $promos = [
        ['tag' => '40% OFF', 'title' => 'Summer Clearance', 'sub' => 'Up to 40% off', 'img' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=600&q=60'],
        ['tag' => 'NEW', 'title' => 'New Disposable Vapes', 'sub' => 'Latest arrivals in stock', 'img' => 'https://images.unsplash.com/photo-1484480974693-6ca0a78fb36b?auto=format&fit=crop&w=600&q=60'],
        ['tag' => 'HOT', 'title' => 'Delta & THCA Collection', 'sub' => 'Premium hemp-derived', 'img' => 'https://images.unsplash.com/photo-1556740749-887f6717d7e4?auto=format&fit=crop&w=600&q=60'],
        ['tag' => 'SALE', 'title' => 'Glass & Water Pipes', 'sub' => 'Top-quality borosilicate', 'img' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&w=600&q=60'],
        ['tag' => 'BUNDLE', 'title' => 'RAW Bundle Deals', 'sub' => 'Papers, trays & more', 'img' => 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=600&q=60'],
    ];
@endphp

<section class="gc-hero" id="gcHero">
    @foreach($heroSlides as $i => $slide)
        <div class="absolute inset-0 {{ $i === 0 ? '' : 'hidden' }}" data-hero-slide>
            <img src="{{ $slide['img'] }}" alt="">
            <div class="gc-hero__inner">
                <span class="gc-badge">{{ $slide['kicker'] }}</span>
                <h1 class="gc-serif font-bold text-[40px] md:text-[48px] max-w-xl mt-5 leading-[1.15] tracking-wide">{!! $slide['title_html'] !!}</h1>
                <p class="max-w-xl mt-4 text-white/85 text-[16px]">{{ $slide['sub'] }}</p>
                <div class="flex flex-wrap gap-3 mt-8">
                    <a href="{{ $slide['href'] }}" class="btn-brand">{{ $slide['cta'] }}</a>
                    <a href="{{ $slide['href2'] }}" class="rounded-lg border border-white/50 text-white font-semibold px-5 py-3 min-h-[44px] inline-flex items-center">{{ $slide['cta2'] }}</a>
                </div>
            </div>
        </div>
    @endforeach
    <button type="button" class="absolute left-3 top-1/2 -translate-y-1/2 z-10 h-10 w-10 rounded-full bg-black/40 text-white" data-hero-prev>‹</button>
    <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 z-10 h-10 w-10 rounded-full bg-black/40 text-white" data-hero-next>›</button>
    <div class="absolute bottom-5 left-0 right-0 z-10 flex justify-center gap-2" id="gcHeroDots">
        @foreach($heroSlides as $i => $slide)
            <button type="button" class="h-2.5 w-2.5 rounded-full {{ $i === 0 ? 'bg-white' : 'bg-white/40' }}" data-hero-dot="{{ $i }}" aria-label="Slide {{ $i + 1 }}"></button>
        @endforeach
    </div>
</section>

<section class="bg-white py-12">
    <div class="max-w-[1400px] mx-auto px-4">
        <div class="flex items-end justify-between mb-6">
            <div>
                <h2 class="gc-section-title uppercase">Current Promotions</h2>
                <div class="gc-underline"></div>
            </div>
            <a href="{{ route('ecommerce.promotions') }}" class="text-sm font-semibold text-[#2e6a49]">View All Promotions →</a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            @foreach($promos as $p)
                <a href="{{ route('ecommerce.promotions') }}" class="gc-promo-card min-h-[170px]">
                    <img src="{{ $p['img'] }}" alt="">
                    <div class="absolute inset-0 bg-black/35"></div>
                    <span class="absolute top-2 left-2 text-[10px] font-bold bg-[#4caf79] px-2 py-0.5 rounded">{{ $p['tag'] }}</span>
                    <span class="absolute bottom-3 left-3 right-3">
                        <span class="block font-bold">{{ $p['title'] }}</span>
                        <span class="block text-xs text-white/80">{{ $p['sub'] }}</span>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>

<section class="py-10">
    <div class="max-w-[1400px] mx-auto px-4">
        <div class="flex items-end justify-between mb-6">
            <div>
                <h2 class="gc-section-title uppercase">Featured Products</h2>
                <div class="gc-underline"></div>
            </div>
            <a href="{{ route('ecommerce.shop') }}" class="text-sm font-semibold text-[#2e6a49]">View All Products →</a>
        </div>
        @include('ecommerce::front.partials.product_grid', ['products' => $featured])
    </div>
</section>

<section class="bg-white py-10">
    <div class="max-w-[1400px] mx-auto px-4">
        <div class="flex items-end justify-between mb-6">
            <div>
                <h2 class="gc-section-title uppercase">New Arrivals</h2>
                <div class="gc-underline"></div>
            </div>
            <a href="{{ route('ecommerce.shop', ['sort' => 'newest']) }}" class="text-sm font-semibold text-[#2e6a49]">View All New Arrivals →</a>
        </div>
        @include('ecommerce::front.partials.product_grid', ['products' => $new_arrivals])
    </div>
</section>

@if($navBrands->count())
<section class="py-10">
    <div class="max-w-[1400px] mx-auto px-4">
        <div class="flex items-end justify-between mb-6">
            <div>
                <h2 class="gc-section-title uppercase">Shop by Brand</h2>
                <div class="gc-underline"></div>
            </div>
            <a href="{{ route('ecommerce.brands') }}" class="text-sm font-semibold text-[#2e6a49]">View All Brands →</a>
        </div>
        <div class="flex gap-3 overflow-x-auto pb-2">
            @foreach($navBrands as $brand)
                <a href="{{ route('ecommerce.shop', ['brand' => $brand->id]) }}" class="gc-brand-tile">
                    <img src="{{ $brandImages[$brand->name] ?? $brandImages['default'] }}" alt="{{ $brand->name }}">
                    <div class="px-2 py-3">
                        <div class="font-bold text-sm">{{ $brand->name }}</div>
                        <div class="text-xs text-black/45 mt-0.5">{{ $brand->products_count ?? '' }}{{ isset($brand->products_count) ? ' products' : 'Shop now' }}</div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

@if($categories->count())
<section class="bg-white py-10">
    <div class="max-w-[1400px] mx-auto px-4">
        <div class="flex items-end justify-between mb-6">
            <div>
                <h2 class="gc-section-title uppercase">Shop by Category</h2>
                <div class="gc-underline"></div>
            </div>
            <a href="{{ route('ecommerce.shop') }}" class="text-sm font-semibold text-[#2e6a49]">View All Categories →</a>
        </div>
        <div class="flex gap-3 overflow-x-auto pb-2">
            @foreach($categories as $cat)
                <a href="{{ route('ecommerce.category', $cat->id) }}" class="gc-cat-tile">
                    <img src="{{ $catImages[$cat->name] ?? $catImages['default'] }}" alt="{{ $cat->name }}">
                    <div class="px-2 py-3">
                        <div class="font-bold text-sm">{{ $cat->name }}</div>
                        <div class="text-xs text-black/45 mt-0.5">Shop now</div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="gc-cta-band">
    <div class="max-w-[1400px] mx-auto px-4 py-12 md:flex md:items-center md:justify-between gap-6">
        <div>
            <h2 class="gc-serif text-3xl md:text-4xl">Ready to Stock Your Shelves?</h2>
            <p class="text-white/80 mt-2 max-w-xl">Apply for a verified wholesale account and unlock tiered pricing, exclusive deals, and priority fulfillment.</p>
        </div>
        <div class="flex flex-wrap gap-3 mt-6 md:mt-0">
            <a href="{{ route('ecommerce.register') }}" class="btn-brand">Apply for Wholesale Account</a>
            <a href="{{ route('ecommerce.contact') }}" class="rounded-lg border border-white/40 px-5 py-3 font-semibold">Contact Sales Team</a>
        </div>
    </div>
</section>

<section class="gc-why py-10">
    <div class="max-w-[1400px] mx-auto px-4 grid grid-cols-2 md:grid-cols-5 gap-6 text-center text-sm">
        <div>
            <div class="mx-auto mb-2 h-10 w-10 rounded-full bg-[#edf7f1] text-[#2e6a49] flex items-center justify-center">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9h12M9 3v12"/></svg>
            </div>
            <div class="font-bold">Direct Wholesale</div>
            <div class="text-black/50">Factory-direct pricing</div>
        </div>
        <div>
            <div class="mx-auto mb-2 h-10 w-10 rounded-full bg-[#edf7f1] text-[#2e6a49] flex items-center justify-center">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 13h12l2-6H5L3 13z"/><circle cx="7" cy="16" r="1.4"/><circle cx="14" cy="16" r="1.4"/></svg>
            </div>
            <div class="font-bold">Fast Fulfillment</div>
            <div class="text-black/50">Same-day orders before 2PM</div>
        </div>
        <div>
            <div class="mx-auto mb-2 h-10 w-10 rounded-full bg-[#edf7f1] text-[#2e6a49] flex items-center justify-center">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 2l7 3v5c0 4-3 7-7 8-4-1-7-4-7-8V5l7-3z"/></svg>
            </div>
            <div class="font-bold">Verified Products</div>
            <div class="text-black/50">COAs on all hemp products</div>
        </div>
        <div>
            <div class="mx-auto mb-2 h-10 w-10 rounded-full bg-[#edf7f1] text-[#2e6a49] flex items-center justify-center">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="8" width="12" height="8" rx="1"/><path d="M6 8V6a3 3 0 016 0v2"/></svg>
            </div>
            <div class="font-bold">Licensed Retailers</div>
            <div class="text-black/50">Verified B2B accounts only</div>
        </div>
        <div>
            <div class="mx-auto mb-2 h-10 w-10 rounded-full bg-[#edf7f1] text-[#2e6a49] flex items-center justify-center">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9h12M9 4l6 5-6 5"/></svg>
            </div>
            <div class="font-bold">Free Shipping</div>
            <div class="text-black/50">On orders over $500</div>
        </div>
    </div>
</section>

<section class="py-10 pb-16">
    <div class="max-w-[1400px] mx-auto px-4">
        <div class="flex items-end justify-between mb-6">
            <div>
                <h2 class="gc-section-title uppercase">Best Sellers</h2>
                <div class="gc-underline"></div>
            </div>
            <a href="{{ route('ecommerce.shop') }}" class="text-sm font-semibold text-[#2e6a49]">View All →</a>
        </div>
        @include('ecommerce::front.partials.product_grid', ['products' => $best_sellers])
    </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    const slides = [...document.querySelectorAll('[data-hero-slide]')];
    if (!slides.length) return;
    let i = 0;
    const show = (n) => {
        slides.forEach((s, idx) => s.classList.toggle('hidden', idx !== n));
        document.querySelectorAll('[data-hero-dot]').forEach((d, idx) => {
            d.classList.toggle('bg-white', idx === n);
            d.classList.toggle('bg-white/40', idx !== n);
        });
    };
    document.querySelector('[data-hero-next]')?.addEventListener('click', () => { i = (i + 1) % slides.length; show(i); });
    document.querySelector('[data-hero-prev]')?.addEventListener('click', () => { i = (i + slides.length - 1) % slides.length; show(i); });
    document.querySelectorAll('[data-hero-dot]').forEach((d) => d.addEventListener('click', () => { i = Number(d.dataset.heroDot); show(i); }));
    setInterval(() => { i = (i + 1) % slides.length; show(i); }, 7000);
})();
</script>
@endpush
