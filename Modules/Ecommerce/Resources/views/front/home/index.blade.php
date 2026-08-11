@extends('ecommerce::front.layouts.store')

@section('title', $ecom_settings->seo_title ?: ($ecom_settings->store_name ?? 'Wholesale Store'))

@section('content')
@php
    $hero = $banners->first();
    $side = $banners->slice(1)->values();
    $covers = $category_covers ?? [];
    $promo = $featured->take(4);
    $navBrands = $nav_brands ?? collect();
@endphp

{{-- Hero banner grid --}}
<section class="max-w-[1400px] mx-auto px-3 sm:px-4 py-3 md:py-6">
    @php
        $leftBanner = $side->get(0);
        $rightBanners = $side->skip(1)->take(2)->values();
        // Mobile under main: 2 side banners (left + first right, or first two sides)
        $mobileSideBanners = $side->take(2)->values();
    @endphp
    <div class="grid lg:grid-cols-12 gap-3 md:gap-4">
        {{-- Left side (desktop only) --}}
        <div class="lg:col-span-3 hidden lg:block">
            @if($leftBanner)
                <a href="{{ $leftBanner->button_link ?: route('ecommerce.shop') }}" class="block relative rounded-xl overflow-hidden aspect-[3/4] bg-ink group h-full min-h-[420px]">
                    @if($leftBanner->image)
                        <img src="{{ asset('uploads/img/'.$leftBanner->image) }}" alt="" class="absolute inset-0 w-full h-full object-cover transition group-hover:scale-105">
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                    <div class="absolute bottom-0 p-4 text-white">
                        <div class="font-bold text-lg leading-tight">{{ $leftBanner->title }}</div>
                        <div class="text-xs text-white/75 mt-1">{{ $leftBanner->button_text }}</div>
                    </div>
                </a>
            @elseif($featured->first())
                <a href="{{ route('ecommerce.shop') }}" class="block relative rounded-xl overflow-hidden aspect-[3/4] min-h-[420px]">
                    <img src="{{ $featured->first()->image_url }}" alt="" class="absolute inset-0 w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                    <div class="absolute bottom-0 p-4 text-white font-bold">Featured deal</div>
                </a>
            @endif
        </div>

        {{-- Main / top banner --}}
        <div class="lg:col-span-6">
            <a href="{{ ($hero->button_link ?? null) ?: route('ecommerce.shop') }}" class="block relative rounded-2xl lg:rounded-xl overflow-hidden min-h-[220px] sm:min-h-[280px] md:min-h-[420px] bg-slatebar text-white group">
                @if($hero && $hero->image)
                    <img src="{{ asset('uploads/img/'.$hero->image) }}" alt="" class="absolute inset-0 w-full h-full object-cover transition duration-700 group-hover:scale-[1.03]">
                @elseif($featured->first())
                    <img src="{{ $featured->first()->image_url }}" alt="" class="absolute inset-0 w-full h-full object-cover">
                @endif
                <div class="absolute inset-0 bg-gradient-to-r from-black/75 via-black/40 to-transparent"></div>
                <div class="relative p-5 sm:p-6 md:p-10 max-w-lg h-full flex flex-col justify-end min-h-[220px] sm:min-h-[280px] md:min-h-[420px]">
                    <p class="text-[11px] uppercase tracking-[0.2em] text-brand font-bold mb-2">Wholesale</p>
                    <h1 class="text-2xl sm:text-3xl md:text-5xl font-extrabold leading-tight mb-2 sm:mb-3">
                        {{ $hero->title ?? 'Big brands. Better wholesale pricing.' }}
                    </h1>
                    <p class="text-white/80 text-sm md:text-base mb-4 sm:mb-5 line-clamp-2 sm:line-clamp-none">
                        {{ $hero->subtitle ?? 'Live POS inventory for candy, snacks, tobacco, and more.' }}
                    </p>
                    <span class="inline-flex self-start btn-brand text-sm">{{ $hero->button_text ?? 'Shop now' }}</span>
                </div>
            </a>

            {{-- Mobile only: 2 side banners under main --}}
            <div class="grid grid-cols-2 gap-2.5 mt-3 lg:hidden">
                @forelse($mobileSideBanners as $banner)
                    <a href="{{ $banner->button_link ?: route('ecommerce.shop') }}" class="relative rounded-xl overflow-hidden min-h-[120px] group">
                        @if($banner->image)
                            <img src="{{ asset('uploads/img/'.$banner->image) }}" alt="" class="absolute inset-0 w-full h-full object-cover">
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-black/75 to-transparent"></div>
                        <div class="absolute bottom-0 p-3 text-white">
                            <div class="font-bold text-sm leading-tight">{{ $banner->title }}</div>
                            @if($banner->button_text)
                                <div class="text-[10px] text-white/75 mt-0.5">{{ $banner->button_text }} →</div>
                            @endif
                        </div>
                    </a>
                @empty
                    @foreach($categories->take(2) as $cat)
                        @php $thumb = isset($covers[$cat->id]) ? asset('uploads/img/'.$covers[$cat->id]) : null; @endphp
                        <a href="{{ route('ecommerce.category', $cat->id) }}" class="relative rounded-xl overflow-hidden min-h-[120px] bg-mist">
                            @if($thumb)<img src="{{ $thumb }}" class="absolute inset-0 w-full h-full object-cover" alt="">@endif
                            <div class="absolute inset-0 bg-black/40"></div>
                            <div class="absolute bottom-0 p-3 text-white font-bold text-sm">{{ $cat->name }}</div>
                        </a>
                    @endforeach
                @endforelse
            </div>
        </div>

        {{-- Right side stack (desktop only) --}}
        <div class="hidden lg:grid lg:col-span-3 grid-cols-1 gap-3">
            @forelse($rightBanners as $banner)
                <a href="{{ $banner->button_link ?: route('ecommerce.shop') }}" class="relative rounded-xl overflow-hidden min-h-[130px] md:min-h-[200px] group">
                    @if($banner->image)
                        <img src="{{ asset('uploads/img/'.$banner->image) }}" alt="" class="absolute inset-0 w-full h-full object-cover transition group-hover:scale-105">
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/75 to-transparent"></div>
                    <div class="absolute bottom-0 p-4 text-white">
                        <div class="font-bold">{{ $banner->title }}</div>
                        <div class="text-xs text-white/75 mt-1">{{ $banner->button_text }} →</div>
                    </div>
                </a>
            @empty
                @foreach($categories->take(2) as $cat)
                    @php $thumb = isset($covers[$cat->id]) ? asset('uploads/img/'.$covers[$cat->id]) : null; @endphp
                    <a href="{{ route('ecommerce.category', $cat->id) }}" class="relative rounded-xl overflow-hidden min-h-[130px] bg-mist">
                        @if($thumb)<img src="{{ $thumb }}" class="absolute inset-0 w-full h-full object-cover" alt="">@endif
                        <div class="absolute inset-0 bg-black/40"></div>
                        <div class="absolute bottom-0 p-4 text-white font-bold">{{ $cat->name }}</div>
                    </a>
                @endforeach
            @endforelse
        </div>
    </div>
</section>

{{-- Promotional products (orange band) --}}
@if($promo->count())
<section class="bg-brand py-10 md:py-12 my-2">
    <div class="max-w-[1400px] mx-auto px-4">
        <div class="flex items-end justify-between mb-6 gap-3">
            <h2 class="text-2xl md:text-3xl font-extrabold text-white uppercase tracking-wide">Promotional Products</h2>
            <a href="{{ route('ecommerce.shop') }}" class="text-sm font-bold text-white/90 hover:text-white underline underline-offset-2">View all</a>
        </div>
        @include('ecommerce::front.partials.product_grid', ['products' => $promo])
    </div>
</section>
@endif

{{-- Brand logos strip --}}
@if($navBrands->count())
<section class="bg-white border-y border-line">
    <div class="max-w-[1400px] mx-auto px-4 py-5">
        <div class="flex items-center gap-4 overflow-x-auto">
            <span class="text-xs font-extrabold uppercase tracking-wider text-black/40 shrink-0">Brands</span>
            @foreach($navBrands as $brand)
                <a href="{{ route('ecommerce.shop', ['brand' => $brand->id]) }}"
                   class="shrink-0 px-4 py-2 rounded-lg border border-line text-xs font-bold text-ink/70 hover:border-brand hover:text-brand whitespace-nowrap">
                    {{ $brand->name }}
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Mosaic / lifestyle --}}
<section class="max-w-[1400px] mx-auto px-4 py-8">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        @foreach($featured->take(4) as $i => $p)
            <a href="{{ route('ecommerce.product', $p->ecom_slug ?: $p->id) }}"
               class="relative overflow-hidden rounded-xl {{ $i === 0 ? 'md:col-span-2 md:row-span-2 min-h-[280px]' : 'min-h-[140px]' }} group">
                <img src="{{ $p->image_url }}" alt="" class="absolute inset-0 w-full h-full object-cover transition duration-500 group-hover:scale-105">
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                <div class="absolute bottom-0 p-3 text-white text-sm font-bold">{{ $p->name }}</div>
            </a>
        @endforeach
    </div>
</section>

{{-- Mid promo banners --}}
@if($side->count())
<section class="max-w-[1400px] mx-auto px-4 pb-6">
    <div class="grid md:grid-cols-2 gap-3 md:gap-4">
        @foreach($side->take(2) as $banner)
            <a href="{{ $banner->button_link ?: route('ecommerce.shop') }}" class="relative rounded-xl overflow-hidden min-h-[180px] md:min-h-[220px] group">
                @if($banner->image)
                    <img src="{{ asset('uploads/img/'.$banner->image) }}" alt="" class="absolute inset-0 w-full h-full object-cover transition group-hover:scale-105">
                @endif
                <div class="absolute inset-0 bg-gradient-to-r from-black/70 to-transparent"></div>
                <div class="relative p-6 text-white max-w-sm">
                    <h3 class="text-2xl font-extrabold">{{ $banner->title }}</h3>
                    <p class="text-sm text-white/75 mt-2">{{ $banner->subtitle }}</p>
                    <span class="inline-flex mt-4 rounded-md bg-white text-ink px-4 py-2 text-xs font-bold">{{ $banner->button_text ?: 'Shop now' }}</span>
                </div>
            </a>
        @endforeach
    </div>
</section>
@endif

<section class="max-w-[1400px] mx-auto px-4 py-8">
    <div class="flex items-end justify-between mb-5 gap-3">
        <div>
            <h2 class="text-2xl md:text-3xl font-extrabold uppercase">New Arrivals</h2>
            <p class="text-sm text-black/50 mt-1">Just added to the wholesale catalog</p>
        </div>
        <a href="{{ route('ecommerce.shop', ['sort' => 'newest']) }}" class="text-sm font-bold text-brand">Shop all</a>
    </div>
    @include('ecommerce::front.partials.product_grid', ['products' => $new_arrivals])
</section>

{{-- Full-width lifestyle CTA --}}
<section class="max-w-[1400px] mx-auto px-4 py-4">
    <div class="relative rounded-2xl overflow-hidden min-h-[220px] md:min-h-[280px] text-white">
        @if($hero && $hero->image)
            <img src="{{ asset('uploads/img/'.$hero->image) }}" alt="" class="absolute inset-0 w-full h-full object-cover">
        @endif
        <div class="absolute inset-0 bg-ink/60"></div>
        <div class="relative p-8 md:p-12 max-w-xl">
            <p class="text-[11px] uppercase tracking-[0.2em] text-brand font-bold mb-2">Trade partners</p>
            <h2 class="text-3xl md:text-4xl font-extrabold mb-3">Wholesale pricing. Retail-grade stock.</h2>
            <p class="text-white/75 text-sm mb-5">Open an account to unlock bulk ordering, live inventory, and fast USA shipping.</p>
            <a href="{{ route('ecommerce.register') }}" class="inline-flex btn-brand">Open wholesale account</a>
        </div>
    </div>
</section>

<section class="bg-white border-y border-line my-6">
    <div class="max-w-[1400px] mx-auto px-4 py-10">
        <div class="flex items-end justify-between mb-5 gap-3">
            <div>
                <h2 class="text-2xl md:text-3xl font-extrabold uppercase">Featured Products</h2>
                <p class="text-sm text-black/50 mt-1">Staff picks for your store</p>
            </div>
        </div>
        @include('ecommerce::front.partials.product_grid', ['products' => $featured])
    </div>
</section>

<section class="max-w-[1400px] mx-auto px-4 py-8">
    <div class="flex items-end justify-between mb-5 gap-3">
        <div>
            <h2 class="text-2xl md:text-3xl font-extrabold uppercase">Hot Sellers</h2>
            <p class="text-sm text-black/50 mt-1">Moving fast across accounts</p>
        </div>
    </div>
    @include('ecommerce::front.partials.product_grid', ['products' => $best_sellers])
</section>

@if($categories->count())
<section class="max-w-[1400px] mx-auto px-4 pb-10">
    <h2 class="text-2xl md:text-3xl font-extrabold uppercase mb-5">Shop by Department</h2>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
        @foreach($categories as $cat)
            @php
                $cover = $covers[$cat->id] ?? null;
                $thumb = $cover ? asset('uploads/img/'.$cover) : optional($featured->firstWhere('category_id', $cat->id))->image_url;
            @endphp
            <a href="{{ route('ecommerce.category', $cat->id) }}" class="group relative aspect-[4/3] rounded-xl overflow-hidden bg-mist border border-line">
                @if($thumb)
                    <img src="{{ $thumb }}" alt="{{ $cat->name }}" class="absolute inset-0 w-full h-full object-cover transition duration-500 group-hover:scale-105">
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/20 to-transparent"></div>
                <div class="absolute bottom-0 p-3 text-white font-bold">{{ $cat->name }}</div>
            </a>
        @endforeach
    </div>
</section>
@endif

<section class="max-w-[1400px] mx-auto px-4 pb-14">
    <div class="rounded-2xl bg-slatebar text-white px-6 py-10 md:px-10 grid md:grid-cols-2 gap-6 items-center">
        <div>
            <h2 class="text-2xl md:text-3xl font-extrabold mb-2">Ready to order wholesale?</h2>
            <p class="text-white/65 text-sm">Create your account with business, shipping, and license details.</p>
        </div>
        <div class="flex flex-wrap gap-3 md:justify-end">
            <a href="{{ route('ecommerce.register') }}" class="btn-brand">Sign up</a>
            <a href="{{ route('ecommerce.login') }}" class="rounded-lg border border-white/30 px-5 py-2.5 text-sm font-semibold hover:bg-white/10">Login</a>
        </div>
    </div>
</section>
@endsection
