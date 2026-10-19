@extends('ecommerce::front.layouts.store')

@section('title', ($ecom_settings->seo_title ?? $ecom_settings->store_name ?? 'Wholesale Store'))

@section('content')
@php
    $navBrands = $nav_brands ?? collect();
    $heroSlides = \Modules\Ecommerce\Support\Storefront::videos();
    $mediaFile = storage_path('app/ecom_gs_media.json');
    $media = is_file($mediaFile) ? (json_decode(file_get_contents($mediaFile), true) ?: []) : [];
    $brandCovers = is_file(storage_path('app/ecom_brand_covers.json'))
        ? (json_decode(file_get_contents(storage_path('app/ecom_brand_covers.json')), true) ?: [])
        : [];
    $leftBanner = $media['banners'][0] ?? null;
    $rightStack = array_values(array_filter([
        $media['banners'][1] ?? null,
        $media['banners'][2] ?? null,
    ]));
    $tetra = $media['tetra'] ?? [];
    $row2 = $media['row2'] ?? [];
    $row3 = $media['row3'] ?? [];
    $row5 = $media['row5'] ?? [];
    $rowLast = $media['row_last'] ?? [];
    $bogoProducts = ($featured ?? collect())->take(15);
@endphp

<div class="gs-home">
    <section class="gs-banners" id="wnHero">
        <a href="{{ route('ecommerce.shop') }}" class="gs-banner-tile hidden md:block">
            @if($leftBanner)
                <img src="{{ $leftBanner }}" alt="Featured">
            @endif
        </a>
        <div class="gs-banner-main">
            @foreach($heroSlides as $i => $slide)
                @if(!empty($slide->is_video) || \Illuminate\Support\Str::endsWith(strtolower((string) $slide->image), ['.mp4', '.webm', '.ogg']))
                    <video class="wn-hero-slide {{ $i===0 ? 'is-on' : '' }}" muted loop playsinline
                           poster="{{ $slide->poster ?? '' }}" @if($i===0) autoplay @endif>
                        <source src="{{ $slide->image }}" type="video/mp4">
                    </video>
                @else
                    <img class="wn-hero-slide {{ $i===0 ? 'is-on' : '' }}" src="{{ $slide->image }}" alt="{{ $slide->title ?? 'Banner' }}">
                @endif
            @endforeach
            @if($heroSlides->count() > 1)
                <div class="wn-hero-dots" id="wnHeroDots" style="position:absolute;left:16px;bottom:12px;z-index:4;display:flex;gap:6px">
                    @foreach($heroSlides as $i => $slide)
                        <button type="button" class="{{ $i===0 ? 'is-on' : '' }}" data-hero-dot="{{ $i }}"
                                style="width:8px;height:8px;border-radius:99px;background:{{ $i===0 ? '#dd772d' : '#fff' }}"></button>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="gs-banner-stack hidden md:grid">
            @foreach($rightStack as $img)
                <a href="{{ route('ecommerce.shop') }}" class="gs-banner-tile"><img src="{{ $img }}" alt=""></a>
            @endforeach
        </div>
    </section>

    <section class="gs-section">
        <div class="gs-section-head">
            <h2 class="gs-bogo" style="padding:0;margin:0;text-align:left;flex:1">BUY 1 GET 1 FREE</h2>
            <a href="{{ route('ecommerce.promotions') }}" class="gs-view-all">View All</a>
        </div>
        @include('ecommerce::front.partials.product_grid', ['products' => $bogoProducts, 'gridClass' => 'gs-card-row', 'minCards' => 0])
    </section>

    @if($navBrands->isNotEmpty())
    <section class="gs-brands">
        @foreach($navBrands as $brand)
            @php $logo = $brandCovers[$brand->id] ?? $brandCovers[(string) $brand->id] ?? null; @endphp
            <a href="{{ route('ecommerce.shop', ['brand' => $brand->id]) }}" class="gs-brand-cell">
                @if($logo)
                    <img src="{{ $logo }}" alt="{{ $brand->name }}">
                @else
                    {{ $brand->name }}
                @endif
            </a>
        @endforeach
    </section>
    @endif

    @if(count($tetra) >= 4)
    <section class="gs-four">
        @foreach(array_slice($tetra, 0, 4) as $img)
            <a href="{{ route('ecommerce.shop') }}"><img src="{{ $img }}" alt=""></a>
        @endforeach
    </section>
    @endif

    @if(count($row2))
    <section class="gs-img-row cols-2">
        @foreach($row2 as $img)
            <a href="{{ route('ecommerce.shop') }}"><img src="{{ $img }}" alt=""></a>
        @endforeach
    </section>
    @endif

    <section class="gs-section">
        <div class="gs-section-head">
            <h2 class="gs-section-title">New Arrivals</h2>
            <a href="{{ route('ecommerce.shop', ['sort' => 'newest', 'new_only' => 1]) }}" class="gs-view-all">View All</a>
        </div>
        @include('ecommerce::front.partials.product_grid', ['products' => $new_arrivals, 'gridClass' => 'gs-card-row', 'minCards' => 0])
    </section>

    @if(count($row3))
    <section class="gs-img-row cols-3">
        @foreach($row3 as $img)
            <a href="{{ route('ecommerce.shop') }}"><img src="{{ $img }}" alt=""></a>
        @endforeach
    </section>
    @endif

    <section class="gs-section">
        <div class="gs-section-head">
            <h2 class="gs-section-title">Featured Products</h2>
            <a href="{{ route('ecommerce.shop') }}" class="gs-view-all">View All</a>
        </div>
        @include('ecommerce::front.partials.product_grid', ['products' => $featured, 'gridClass' => 'gs-card-row', 'minCards' => 0])
    </section>

    <section class="gs-section">
        <div class="gs-section-head">
            <h2 class="gs-section-title">Hot Sellers Products</h2>
            <a href="{{ route('ecommerce.shop') }}" class="gs-view-all">View All</a>
        </div>
        @include('ecommerce::front.partials.product_grid', ['products' => $best_sellers, 'gridClass' => 'gs-card-row', 'minCards' => 0])
    </section>

    @if(count($row5))
    <section class="gs-img-row cols-5">
        @foreach($row5 as $img)
            <a href="{{ route('ecommerce.shop') }}"><img src="{{ $img }}" alt=""></a>
        @endforeach
    </section>
    @endif

    @if(count($rowLast))
    <section class="gs-img-row cols-last">
        @foreach($rowLast as $img)
            <a href="{{ route('ecommerce.shop') }}"><img src="{{ $img }}" alt=""></a>
        @endforeach
    </section>
    @endif
</div>
@endsection

@push('scripts')
<script>
(function () {
    const root = document.getElementById('wnHero');
    if (root) {
        const slides = [...root.querySelectorAll('.wn-hero-slide')];
        const dots = [...root.querySelectorAll('[data-hero-dot]')];
        let i = 0;
        const show = (n) => {
            const cur = slides[i];
            if (cur && cur.tagName === 'VIDEO') cur.pause();
            cur?.classList.remove('is-on');
            if (dots[i]) { dots[i].classList.remove('is-on'); dots[i].style.background = '#fff'; }
            i = n;
            slides[i]?.classList.add('is-on');
            if (dots[i]) { dots[i].classList.add('is-on'); dots[i].style.background = '#dd772d'; }
            const v = slides[i];
            if (v && v.tagName === 'VIDEO') { v.muted = true; v.play().catch(() => {}); }
        };
        if (slides[0] && slides[0].tagName === 'VIDEO') { slides[0].muted = true; slides[0].play().catch(() => {}); }
        dots.forEach((d) => d.addEventListener('click', () => show(Number(d.dataset.heroDot))));
        if (slides.length > 1) setInterval(() => show((i + 1) % slides.length), 5000);
    }
})();
</script>
@endpush
