@extends('ecommerce::front.layouts.store')

@section('title', ($ecom_settings->seo_title ?? $ecom_settings->store_name ?? 'Wholesale Store'))

@section('content')
@php
    $navBrands = $nav_brands ?? collect();
    $categories = $categories ?? collect();
    $store = $store ?? ($ecom_settings->store_name ?? 'Wholesale Store');
    $loggedIn = auth('customer')->check();
    $heroSlides = \Modules\Ecommerce\Support\Storefront::videos();
    $fallbackBrands = $navBrands->values()->take(8);
    $fallbackCats = $categories->take(8);
    $coverFile = storage_path('app/ecom_category_covers.json');
    $category_covers = $category_covers ?? (is_file($coverFile) ? (json_decode(file_get_contents($coverFile), true) ?: []) : []);
    $collections = $collections ?? collect();
    $new_arrivals = $new_arrivals ?? collect();
    $best_sellers = $best_sellers ?? collect();
@endphp

<div class="max-w-[1440px] mx-auto px-4 pt-5 pb-10 space-y-8">
    <section class="wn-hero-banner" id="wnHero">
        <div class="wn-hero-media" aria-hidden="true">
            @foreach($heroSlides as $i => $slide)
                <video class="{{ $i===0 ? 'is-on' : '' }}" muted loop playsinline webkit-playsinline
                       preload="{{ $i===0 ? 'auto' : 'metadata' }}"
                       poster="{{ $slide->poster ?? '' }}"
                       @if($i===0) autoplay @endif>
                    <source src="{{ $slide->image }}" type="video/mp4">
                </video>
            @endforeach
        </div>
        <div class="relative z-[2] px-6 md:px-10 py-10 md:py-14 max-w-xl">
            <h1>
                <span class="a">STOCK UP.</span><br>
                <span class="b">STAND OUT.</span>
            </h1>
            <div class="wn-hero-sub">VAPE · CIGAR · SMOKE</div>
            <div class="wn-trust">
                <div>
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="8" width="12" height="8" rx="1"/><path d="M6 8V6a3 3 0 016 0v2"/></svg>
                    TOP BRANDS<br>GREAT PRICES
                </div>
                <div>
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 13h12l2-6H5L3 13z"/><circle cx="7" cy="16" r="1.3"/><circle cx="14" cy="16" r="1.3"/></svg>
                    FAST SHIPPING<br>RELIABLE SERVICE
                </div>
                <div>
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 14l4-8h8l-4 8H4z"/><path d="M8 6V4h4"/></svg>
                    EXCLUSIVE DEALS<br>FOR RETAILERS
                </div>
            </div>
            <div class="flex flex-wrap gap-3 mt-8">
                <a href="{{ route('ecommerce.shop') }}" class="wn-add px-5" style="height:44px;flex:0 0 auto">Shop catalog</a>
                <a href="{{ route('ecommerce.quick_order') }}" class="wn-quick">⚡ Quick Order</a>
            </div>
        </div>
        <div class="wn-hot">
            <strong>HOT<br>DEALS</strong>
            <span>LIMITED TIME<br>ONLY!</span>
        </div>
        @if($heroSlides->count() > 1)
            <div class="wn-hero-dots" id="wnHeroDots">
                @foreach($heroSlides as $i => $slide)
                    <button type="button" class="{{ $i===0 ? 'is-on' : '' }}" data-hero-dot="{{ $i }}" aria-label="{{ $slide->title ?? 'Video' }} {{ $i+1 }}"></button>
                @endforeach
            </div>
        @endif
    </section>

    @if($fallbackBrands->isNotEmpty())
    <section class="wn-brand-row" id="wnBrandRow">
        <button type="button" class="wn-brand-nav" data-brand-prev aria-label="Previous brands">‹</button>
        <div class="flex-1 grid grid-cols-4 md:grid-cols-8 gap-2 min-w-0">
            @foreach($fallbackBrands as $brand)
                <a href="{{ !empty($brand->id) ? route('ecommerce.shop', ['brand' => $brand->id]) : route('ecommerce.brands') }}" class="wn-brand-cell">
                    {{ $brand->name }}
                </a>
            @endforeach
        </div>
        <button type="button" class="wn-brand-nav" data-brand-next aria-label="Next brands">›</button>
    </section>
    @endif

    @if($fallbackCats->isNotEmpty())
    <section class="wn-cats">
        @foreach($fallbackCats as $i => $cat)
            @php
                $cover = (!empty($cat->id) && isset($category_covers[$cat->id]))
                    ? asset('uploads/img/'.$category_covers[$cat->id])
                    : null;
            @endphp
            <a href="{{ !empty($cat->id) ? route('ecommerce.category', $cat->id) : route('ecommerce.shop') }}" class="wn-cat" data-tone="{{ $i % 6 }}">
                <h3>{{ $cat->name }}</h3>
                <p>SHOP NOW →</p>
                @if($cover)
                    <img src="{{ $cover }}" alt="">
                @endif
            </a>
        @endforeach
    </section>
    @endif

    <section>
        <div class="flex items-end justify-between mb-4">
            <h2 class="gc-section-title uppercase">Featured Products</h2>
            <a href="{{ route('ecommerce.shop') }}" class="text-sm font-semibold text-[#9aa6d1] hover:text-white">View All Products →</a>
        </div>
        @include('ecommerce::front.partials.product_grid', ['products' => $featured, 'gridClass' => 'grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3', 'minCards' => 0])
    </section>

    <section>
        <div class="flex items-end justify-between mb-4">
            <h2 class="gc-section-title uppercase">New Arrivals</h2>
            <a href="{{ route('ecommerce.shop', ['sort' => 'newest']) }}" class="text-sm font-semibold text-[#9aa6d1] hover:text-white">See all →</a>
        </div>
        @include('ecommerce::front.partials.product_grid', ['products' => $new_arrivals, 'gridClass' => 'grid grid-cols-2 md:grid-cols-4 gap-3', 'minCards' => 0])
    </section>

    <section>
        <div class="flex items-end justify-between mb-4">
            <h2 class="gc-section-title uppercase">Best Sellers</h2>
            <a href="{{ route('ecommerce.shop') }}" class="text-sm font-semibold text-[#9aa6d1] hover:text-white">Shop all →</a>
        </div>
        @include('ecommerce::front.partials.product_grid', ['products' => $best_sellers, 'gridClass' => 'grid grid-cols-2 md:grid-cols-4 gap-3', 'minCards' => 0])
    </section>

    @if($collections->isNotEmpty())
    <section class="grid md:grid-cols-3 gap-3">
        @foreach($collections as $col)
            <a href="{{ route('ecommerce.collection', $col->slug) }}" class="wn-panel p-6 block hover:border-[#f23488]">
                <p class="text-[11px] uppercase tracking-[0.16em] text-[#8b95b8] font-bold mb-1">Collection</p>
                <h3 class="text-xl font-extrabold">{{ $col->name }}</h3>
                <p class="text-sm text-[#8b95b8] mt-2">{{ $col->description }}</p>
                <span class="inline-block mt-4 text-[#f23488] font-bold text-sm">Shop now →</span>
            </a>
        @endforeach
    </section>
    @endif
</div>
@endsection

@push('scripts')
<script>
(function () {
    const root = document.getElementById('wnHero');
    if (!root) return;
    const videos = [...root.querySelectorAll('.wn-hero-media video')];
    const dots = [...root.querySelectorAll('[data-hero-dot]')];
    if (!videos.length) return;
    let i = 0;
    const show = (n) => {
        videos[i]?.pause();
        videos[i]?.classList.remove('is-on');
        dots[i]?.classList.remove('is-on');
        i = n;
        videos[i]?.classList.add('is-on');
        dots[i]?.classList.add('is-on');
        const v = videos[i];
        if (v) {
            v.muted = true;
            v.play().catch(() => {});
        }
    };
    videos[0].muted = true;
    videos[0].play().catch(() => {});
    dots.forEach((d) => d.addEventListener('click', () => show(Number(d.dataset.heroDot))));
    if (videos.length > 1) {
        setInterval(() => show((i + 1) % videos.length), 9000);
    }
})();
</script>
@endpush
