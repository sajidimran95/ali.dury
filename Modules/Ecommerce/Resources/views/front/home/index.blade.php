@extends('ecommerce::front.layouts.store')

@section('title', $ecom_settings->seo_title ?: ($ecom_settings->store_name ?? 'Wholesale Store'))

@section('content')
@php
    $storeName = $ecom_settings->store_name ?: ($store ?? 'Wholesale');
    $heroFallbackTitle = $ecom_settings->seo_title ?: ($storeName."\nWholesale Distributor");
    $heroFallbackSub = $ecom_settings->seo_description
        ?: 'Competitive pricing for licensed retailers.';

    $heroSlides = collect();
    foreach ($banners as $banner) {
        $img = $banner->image ? uploadAsset($banner->image, 'uploads/img') : null;
        if (! $img && ! $banner->title) {
            continue;
        }
        $heroSlides->push((object) [
            'headline' => $banner->title ?: $heroFallbackTitle,
            'sub' => $banner->subtitle ?: $heroFallbackSub,
            'cta' => $banner->button_text ?: 'Shop All Products',
            'ctaHref' => $banner->button_link ?: route('ecommerce.shop'),
            'cta2' => 'Apply for Account',
            'cta2Href' => route('ecommerce.register'),
            'image' => $img,
            'overlay' => 'rgba(26,61,43,0.7)',
        ]);
    }
    if ($heroSlides->isEmpty()) {
        $localHeroes = [
            ['uploads/img/greencloud/photo-1556742049-0cfed4f6a45d.jpg', 'rgba(26,61,43,0.7)'],
            ['uploads/img/greencloud/photo-1461532257246-777de18cd58b.jpg', 'rgba(30,70,40,0.72)'],
            ['uploads/img/greencloud/photo-1585435465945-bef5a93f8849.jpg', 'rgba(20,40,60,0.72)'],
        ];
        foreach ($localHeroes as $i => [$path, $overlay]) {
            $heroSlides->push((object) [
                'headline' => $i === 0 ? $heroFallbackTitle : ($i === 1 ? "New Arrivals\nJust Dropped" : "Top Products\nin Stock Now"),
                'sub' => $i === 0 ? $heroFallbackSub : ($i === 1 ? 'Fresh inventory added to the wholesale catalog.' : 'Best-selling brands at wholesale prices.'),
                'cta' => $i === 0 ? 'Shop All Products' : ($i === 1 ? 'View New Arrivals' : 'Shop Catalog'),
                'ctaHref' => $i === 1 ? route('ecommerce.new_arrivals') : route('ecommerce.shop'),
                'cta2' => 'Apply for Account',
                'cta2Href' => route('ecommerce.register'),
                'image' => asset($path),
                'overlay' => $overlay,
            ]);
        }
    }

    $promoTiles = $banners->take(5);
    $promoProducts = $featured->filter(fn ($p) => $p->ecom_online_price && $p->ecom_online_price > $p->ecom_price)->take(5);
    if ($promoProducts->isEmpty()) {
        $promoProducts = $featured->take(5);
    }
    $navBrands = (isset($home_brands) && $home_brands instanceof \Illuminate\Support\Collection && $home_brands->count())
        ? $home_brands
        : ($nav_brands ?? collect());
    $covers = $category_covers ?? [];
    $coverImgUrl = fn ($cat) => isset($covers[$cat->id]) ? uploadAsset($covers[$cat->id], 'uploads/img') : null;

    $trustItems = [
        ['icon' => '🏭', 'title' => 'Direct Wholesale', 'sub' => 'Factory-direct pricing'],
        ['icon' => '📦', 'title' => 'Fast Fulfillment', 'sub' => 'Same-day orders before 2PM'],
        ['icon' => '✅', 'title' => 'Verified Products', 'sub' => 'Quality-checked inventory'],
        ['icon' => '🔒', 'title' => 'Licensed Retailers', 'sub' => 'Verified B2B accounts only'],
        ['icon' => '🚚', 'title' => 'Free Shipping', 'sub' => 'On qualifying orders'],
    ];
@endphp

{{-- Hero carousel (Figma ei) --}}
<section class="relative h-[480px] md:h-[560px] overflow-hidden" aria-label="Featured promotions carousel" id="homeHero">
    @foreach($heroSlides as $i => $slide)
        <div class="hero-slide absolute inset-0 bg-center bg-cover transition-opacity duration-700 {{ $i === 0 ? 'opacity-100' : 'opacity-0' }}"
             data-hero-slide
             style="background-image:url('{{ $slide->image }}');background-color:#1a3d2b"
             aria-hidden="{{ $i === 0 ? 'false' : 'true' }}"></div>
    @endforeach
    <div class="absolute inset-0" style="background:rgba(26,61,43,0.7)" aria-hidden="true"></div>
    <div class="relative h-full max-w-screen-xl mx-auto px-6 flex flex-col justify-center">
        @foreach($heroSlides as $i => $slide)
            <div class="hero-copy max-w-xl transition-all duration-500 {{ $i === 0 ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-3 absolute' }}" data-hero-copy>
                <span class="inline-block text-xs font-bold uppercase tracking-[0.2em] mb-3 px-3 py-1 rounded-full" style="background:#4CAF79;color:white">Wholesale Only — Licensed Retailers</span>
                <h1 class="font-display text-3xl md:text-5xl font-bold text-white mb-4 leading-tight whitespace-pre-line">{{ $slide->headline }}</h1>
                <p class="text-base md:text-lg text-white opacity-90 mb-7 max-w-md">{{ $slide->sub }}</p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ $slide->ctaHref }}" class="px-6 py-3 rounded-lg font-semibold text-sm text-white transition-all hover:opacity-90 active:scale-95" style="background:#4CAF79">{{ $slide->cta }}</a>
                    <a href="{{ $slide->cta2Href }}" class="px-6 py-3 rounded-lg font-semibold text-sm border-2 text-white transition-all hover:bg-white hover:text-[#2E6A49]" style="border-color:white">{{ $slide->cta2 }}</a>
                </div>
            </div>
        @endforeach
    </div>
    @if($heroSlides->count() > 1)
        <div class="absolute bottom-5 left-1/2 -translate-x-1/2 flex gap-2" role="tablist" aria-label="Hero slides">
            @foreach($heroSlides as $i => $slide)
                <button type="button" class="hero-dot rounded-full transition-all duration-300 {{ $i === 0 ? 'w-6 h-2.5 bg-white' : 'w-2.5 h-2.5 bg-white/50' }}" data-hero-dot aria-label="Slide {{ $i + 1 }}"></button>
            @endforeach
        </div>
        <button type="button" class="absolute left-3 top-1/2 -translate-y-1/2 h-10 w-10 rounded-full bg-black/30 text-white hover:bg-black/50 flex items-center justify-center" data-hero-prev aria-label="Previous slide">‹</button>
        <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 h-10 w-10 rounded-full bg-black/30 text-white hover:bg-black/50 flex items-center justify-center" data-hero-next aria-label="Next slide">›</button>
    @endif
</section>

{{-- Current Promotions --}}
<section class="py-10 md:py-14 px-4 bg-white">
    <div class="max-w-screen-xl mx-auto">
        <div class="flex items-end justify-between mb-6">
            <h2 class="section-title font-display text-xl md:text-2xl font-bold" style="color:#1a1a1a">Current Promotions</h2>
            <a href="{{ route('ecommerce.promotions') }}" class="text-sm font-semibold transition-colors hover:underline shrink-0" style="color:#1a73e8">View All Promotions →</a>
        </div>
        @if($promoTiles->count())
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 md:gap-4 mb-8">
                @foreach($promoTiles as $tile)
                    @php $timg = $tile->image ? uploadAsset($tile->image, 'uploads/img') : null; @endphp
                    <a href="{{ $tile->button_link ?: route('ecommerce.promotions') }}" class="tile-hover relative group rounded-xl overflow-hidden aspect-square block">
                        @if($timg)
                            <img src="{{ $timg }}" alt="" class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                        @else
                            <div class="absolute inset-0" style="background:#2E6A49"></div>
                        @endif
                        <div class="img-overlay absolute inset-0"></div>
                        @if($tile->button_text)
                            <span class="absolute top-2 left-2 text-white text-xs font-bold px-2 py-0.5 rounded" style="background:#e53e3e">{{ $tile->button_text }}</span>
                        @endif
                        <div class="absolute bottom-0 left-0 right-0 p-3">
                            <div class="text-white font-display font-bold text-sm leading-snug drop-shadow">{{ $tile->title }}</div>
                            @if($tile->subtitle)
                                <div class="text-white text-xs opacity-90 mt-0.5">{{ $tile->subtitle }}</div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
        @include('ecommerce::front.partials.product_grid', [
            'products' => $promoProducts,
            'gridClass' => 'grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 md:gap-4',
        ])
    </div>
</section>

{{-- Featured Products --}}
<section class="py-10 md:py-14 px-4" style="background:#F4F4F4">
    <div class="max-w-screen-xl mx-auto">
        <div class="flex items-end justify-between mb-6">
            <h2 class="section-title font-display text-xl md:text-2xl font-bold" style="color:#1a1a1a">Featured Products</h2>
            <a href="{{ route('ecommerce.shop') }}" class="text-sm font-semibold transition-colors hover:underline shrink-0" style="color:#1a73e8">View All →</a>
        </div>
        @include('ecommerce::front.partials.product_grid', ['products' => $featured->take(8)])
    </div>
</section>

{{-- New Arrivals --}}
@if($new_arrivals->count())
<section class="py-10 md:py-12 px-4 bg-white">
    <div class="max-w-screen-xl mx-auto">
        <div class="flex items-end justify-between mb-6">
            <h2 class="section-title font-display text-xl md:text-2xl font-bold" style="color:#1a1a1a">New Arrivals</h2>
            <a href="{{ route('ecommerce.new_arrivals') }}" class="text-sm font-semibold transition-colors hover:underline shrink-0" style="color:#1a73e8">View All →</a>
        </div>
        @include('ecommerce::front.partials.product_grid', [
            'products' => $new_arrivals->take(4),
            'gridClass' => 'grid grid-cols-2 md:grid-cols-4 gap-4',
        ])
    </div>
</section>
@endif

{{-- Shop by Brand --}}
@if($navBrands->count())
<section class="py-10 md:py-14 px-4" style="background:#F4F4F4">
    <div class="max-w-screen-xl mx-auto">
        <div class="flex items-end justify-between mb-6">
            <h2 class="section-title font-display text-xl md:text-2xl font-bold" style="color:#1a1a1a">Shop by Brand</h2>
            <a href="{{ route('ecommerce.brands') }}" class="text-sm font-semibold transition-colors hover:underline shrink-0" style="color:#1a73e8">View All Brands →</a>
        </div>
        <div class="scroll-row-wrap" data-scroll-row>
            <button type="button" class="scroll-row-btn scroll-row-btn--left" data-scroll-left aria-label="Scroll left">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M10 12L6 8l4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <div class="scroll-row" data-scroll-track>
                @foreach($navBrands->take(10) as $brand)
                    @php
                        $brandImg = uploadAsset($brand->image ?? null, 'uploads/img');
                    @endphp
                    <a href="{{ route('ecommerce.shop', ['brand' => $brand->id]) }}"
                       class="tile-hover shrink-0 bg-white rounded-xl overflow-hidden border border-[#e8e8e8] flex flex-col items-center group"
                       style="width:180px;min-width:180px">
                        <div class="w-full h-[110px] bg-[#f5f5f5] overflow-hidden flex items-center justify-center">
                            @if($brandImg)
                                <img src="{{ $brandImg }}" alt="{{ $brand->name }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                            @else
                                <span class="font-display font-bold text-sm px-3 text-center" style="color:#2E6A49">{{ $brand->name }}</span>
                            @endif
                        </div>
                        <div class="p-3 text-center w-full">
                            <div class="font-display font-bold text-sm" style="color:#2E6A49">{{ $brand->name }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
            <button type="button" class="scroll-row-btn scroll-row-btn--right" data-scroll-right aria-label="Scroll right">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </div>
    </div>
</section>
@endif

{{-- Shop by Category --}}
@if($categories->count())
<section class="py-10 md:py-14 px-4 bg-white">
    <div class="max-w-screen-xl mx-auto">
        <div class="flex items-end justify-between mb-6">
            <h2 class="section-title font-display text-xl md:text-2xl font-bold" style="color:#1a1a1a">Shop by Category</h2>
            <a href="{{ route('ecommerce.categories') }}" class="text-sm font-semibold transition-colors hover:underline shrink-0" style="color:#1a73e8">View All →</a>
        </div>
        <div class="scroll-row-wrap" data-scroll-row>
            <button type="button" class="scroll-row-btn scroll-row-btn--left" data-scroll-left aria-label="Scroll left">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M10 12L6 8l4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <div class="scroll-row" data-scroll-track>
                @foreach($categories->take(10) as $cat)
                    @php
                        $thumb = $coverImgUrl($cat) ?: optional($featured->firstWhere('category_id', $cat->id))->image_url;
                    @endphp
                    <a href="{{ route('ecommerce.category', $cat->id) }}"
                       class="tile-hover shrink-0 bg-white rounded-xl overflow-hidden border border-[#e8e8e8] flex flex-col items-center group"
                       style="width:180px;min-width:180px">
                        <div class="w-full h-[110px] bg-[#f5f5f5] overflow-hidden">
                            @if($thumb)
                                <img src="{{ $thumb }}" alt="{{ $cat->name }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                            @endif
                        </div>
                        <div class="p-3 text-center w-full">
                            <div class="font-semibold text-sm" style="color:#1a1a1a">{{ $cat->name }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
            <button type="button" class="scroll-row-btn scroll-row-btn--right" data-scroll-right aria-label="Scroll right">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </div>
    </div>
</section>
@endif

{{-- CTA band --}}
<section class="py-14 md:py-20 px-4 text-white" style="background:#2E6A49">
    <div class="max-w-screen-xl mx-auto text-center">
        <span class="inline-block text-xs font-bold uppercase tracking-[0.2em] mb-4 px-3 py-1 rounded-full" style="background:#4CAF79;color:white">Wholesale Accounts</span>
        <h2 class="font-display text-3xl md:text-4xl font-bold mb-4">Ready to Stock Your Shelves?</h2>
        <p class="text-white/85 text-base md:text-lg max-w-2xl mx-auto mb-8">Apply for a verified wholesale account and unlock tiered pricing, exclusive deals, and priority fulfillment.</p>
        <div class="flex flex-wrap gap-3 justify-center">
            <a href="{{ route('ecommerce.register') }}" class="px-6 py-3 rounded-lg font-semibold text-sm transition-all hover:opacity-90" style="background:#4CAF79;color:white">Apply for Wholesale Account</a>
            <a href="{{ route('ecommerce.contact') }}" class="px-6 py-3 rounded-lg font-semibold text-sm border-2 text-white transition-all hover:bg-white/10" style="border-color:rgba(255,255,255,0.5)">Contact Sales Team</a>
        </div>
    </div>
</section>

{{-- Trust strip --}}
<section class="bg-white border-y border-[#e8e8e8] py-5" aria-label="Why buy from us">
    <div class="max-w-screen-xl mx-auto px-4">
        <div class="flex flex-wrap items-center justify-center md:justify-between gap-4">
            @foreach($trustItems as $item)
                <div class="flex items-center gap-2.5 min-w-[140px]">
                    <span class="text-xl" aria-hidden="true">{{ $item['icon'] }}</span>
                    <div>
                        <div class="text-xs font-bold" style="color:#1a1a1a">{{ $item['title'] }}</div>
                        <div class="text-xs" style="color:#666">{{ $item['sub'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Best Sellers --}}
<section class="py-10 md:py-14 px-4" style="background:#F4F4F4">
    <div class="max-w-screen-xl mx-auto">
        <div class="flex items-end justify-between mb-6">
            <h2 class="section-title font-display text-xl md:text-2xl font-bold" style="color:#1a1a1a">Best Sellers</h2>
            <a href="{{ route('ecommerce.shop', ['sort' => 'bestselling']) }}" class="text-sm font-semibold transition-colors hover:underline shrink-0" style="color:#1a73e8">View All →</a>
        </div>
        @include('ecommerce::front.partials.product_grid', [
            'products' => $best_sellers->take(5),
            'gridClass' => 'grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4',
        ])
    </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    document.querySelectorAll('[data-scroll-row]').forEach((wrap) => {
        const track = wrap.querySelector('[data-scroll-track]');
        const btnL = wrap.querySelector('[data-scroll-left]');
        const btnR = wrap.querySelector('[data-scroll-right]');
        if (!track || !btnL || !btnR) return;

        const sync = () => {
            const max = Math.max(0, track.scrollWidth - track.clientWidth);
            btnL.classList.toggle('is-visible', track.scrollLeft > 4);
            btnR.classList.toggle('is-visible', max > 8 && track.scrollLeft < max - 4);
        };

        const scrollByDir = (dir) => {
            track.scrollBy({ left: dir === 'right' ? 600 : -600, behavior: 'smooth' });
        };

        btnL.addEventListener('click', (e) => { e.preventDefault(); scrollByDir('left'); });
        btnR.addEventListener('click', (e) => { e.preventDefault(); scrollByDir('right'); });
        track.addEventListener('scroll', sync, { passive: true });
        window.addEventListener('resize', sync);

        // Shift+wheel or trackpad horizontal → native; map vertical wheel to horizontal when over row
        track.addEventListener('wheel', (e) => {
            if (Math.abs(e.deltaY) > Math.abs(e.deltaX) && track.scrollWidth > track.clientWidth + 8) {
                e.preventDefault();
                track.scrollLeft += e.deltaY;
                sync();
            }
        }, { passive: false });

        track.querySelectorAll('img').forEach((img) => {
            if (!img.complete) img.addEventListener('load', sync);
        });

        sync();
        requestAnimationFrame(sync);
        setTimeout(sync, 100);
        setTimeout(sync, 500);
    });
})();

(function () {
    const root = document.getElementById('homeHero');
    if (!root) return;
    const slides = [...root.querySelectorAll('[data-hero-slide]')];
    const copies = [...root.querySelectorAll('[data-hero-copy]')];
    const dots = [...root.querySelectorAll('[data-hero-dot]')];
    if (slides.length < 2) return;
    let i = 0;
    const show = (n) => {
        slides.forEach((el, idx) => {
            el.classList.toggle('opacity-100', idx === n);
            el.classList.toggle('opacity-0', idx !== n);
            el.setAttribute('aria-hidden', idx === n ? 'false' : 'true');
        });
        copies.forEach((el, idx) => {
            el.classList.toggle('opacity-100', idx === n);
            el.classList.toggle('translate-y-0', idx === n);
            el.classList.toggle('opacity-0', idx !== n);
            el.classList.toggle('translate-y-3', idx !== n);
            el.classList.toggle('absolute', idx !== n);
        });
        dots.forEach((el, idx) => {
            el.classList.toggle('w-6', idx === n);
            el.classList.toggle('bg-white', idx === n);
            el.classList.toggle('w-2.5', idx !== n);
            el.classList.toggle('bg-white/50', idx !== n);
        });
        i = n;
    };
    root.querySelector('[data-hero-next]')?.addEventListener('click', () => show((i + 1) % slides.length));
    root.querySelector('[data-hero-prev]')?.addEventListener('click', () => show((i - 1 + slides.length) % slides.length));
    dots.forEach((el, idx) => el.addEventListener('click', () => show(idx)));
    setInterval(() => show((i + 1) % slides.length), 6000);
})();
</script>
@endpush
