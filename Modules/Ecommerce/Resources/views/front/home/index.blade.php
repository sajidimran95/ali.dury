@extends('ecommerce::front.layouts.store')

@section('title', $ecom_settings->seo_title ?: ($ecom_settings->store_name ?? 'Store'))

@section('content')
@php
    $hero = $banners->first();
    $promoBanners = $banners->slice(1)->values();
    $covers = $category_covers ?? [];
@endphp

{{-- Full-bleed hero --}}
<section class="relative min-h-[78vh] md:min-h-[88vh] flex items-end overflow-hidden bg-ink text-white">
    @if($hero && $hero->image)
        <img src="{{ asset('uploads/img/'.$hero->image) }}" alt="" class="absolute inset-0 w-full h-full object-cover scale-105 hero-zoom">
    @elseif($featured->first())
        <img src="{{ $featured->first()->image_url }}" alt="" class="absolute inset-0 w-full h-full object-cover scale-105 hero-zoom">
    @endif
    <div class="absolute inset-0 bg-gradient-to-t from-ink via-ink/55 to-ink/20"></div>
    <div class="relative max-w-7xl mx-auto px-4 pb-16 md:pb-24 pt-32 w-full">
        <p class="text-[11px] uppercase tracking-[0.3em] text-white/70 mb-4 fade-up">{{ $ecom_settings->store_name }}</p>
        <h1 class="font-display text-5xl md:text-7xl lg:text-[5.2rem] leading-[0.95] max-w-3xl mb-5 fade-up" style="animation-delay:.08s">
            {{ $hero->title ?? 'Everyday essentials, elevated' }}
        </h1>
        <p class="text-white/75 text-base md:text-lg max-w-xl mb-8 leading-relaxed fade-up" style="animation-delay:.16s">
            {{ $hero->subtitle ?? 'Curated products with live POS inventory and fast USA shipping.' }}
        </p>
        <div class="flex flex-wrap gap-3 fade-up" style="animation-delay:.24s">
            <a href="{{ route('ecommerce.shop') }}" class="rounded-full bg-white text-ink px-8 py-3.5 text-sm font-semibold hover:bg-sand transition">
                {{ $hero->button_text ?? 'Shop now' }}
            </a>
            @if($nav_categories->first())
                <a href="{{ route('ecommerce.category', $nav_categories->first()->id) }}" class="rounded-full border border-white/30 px-8 py-3.5 text-sm font-medium hover:bg-white/10 transition">
                    Browse categories
                </a>
            @endif
        </div>
    </div>
</section>

{{-- Trust strip --}}
<section class="border-b border-black/5 bg-white">
    <div class="max-w-7xl mx-auto px-4 py-6 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
        <div class="flex items-start gap-3">
            <span class="mt-0.5 h-9 w-9 rounded-full bg-mist flex items-center justify-center shrink-0">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 11l6-8 6 8H3z"/><path d="M5 11v3h8v-3"/></svg>
            </span>
            <div><div class="font-semibold">Free shipping</div><div class="text-black/45 text-xs mt-0.5">Orders over $50</div></div>
        </div>
        <div class="flex items-start gap-3">
            <span class="mt-0.5 h-9 w-9 rounded-full bg-mist flex items-center justify-center shrink-0">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 4h10v10H4z"/><path d="M7 8h4M7 11h3"/></svg>
            </span>
            <div><div class="font-semibold">Live inventory</div><div class="text-black/45 text-xs mt-0.5">Synced with POS</div></div>
        </div>
        <div class="flex items-start gap-3">
            <span class="mt-0.5 h-9 w-9 rounded-full bg-mist flex items-center justify-center shrink-0">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M8 3l5 2v4c0 3-2.2 5.2-5 6-2.8-.8-5-3-5-6V5l5-2z"/></svg>
            </span>
            <div><div class="font-semibold">Secure checkout</div><div class="text-black/45 text-xs mt-0.5">Protected payments</div></div>
        </div>
        <div class="flex items-start gap-3">
            <span class="mt-0.5 h-9 w-9 rounded-full bg-mist flex items-center justify-center shrink-0">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 8h10M11 5l3 3-3 3"/></svg>
            </span>
            <div><div class="font-semibold">Easy returns</div><div class="text-black/45 text-xs mt-0.5">14-day window</div></div>
        </div>
    </div>
</section>

@if($categories->count())
<section class="max-w-7xl mx-auto px-4 py-16 md:py-20">
    <div class="flex items-end justify-between mb-8 gap-4">
        <div>
            <p class="text-[11px] uppercase tracking-[0.22em] text-black/40 mb-2">Departments</p>
            <h2 class="font-display text-4xl md:text-5xl">Shop by category</h2>
        </div>
        <a href="{{ route('ecommerce.shop') }}" class="text-sm font-medium text-accent hover:text-accent2">View all</a>
    </div>
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 md:gap-4">
        @foreach($categories as $cat)
            @php
                $cover = $covers[$cat->id] ?? null;
                $thumb = $cover
                    ? asset('uploads/img/'.$cover)
                    : optional($featured->firstWhere('category_id', $cat->id) ?? $new_arrivals->firstWhere('category_id', $cat->id))->image_url;
            @endphp
            <a href="{{ route('ecommerce.category', $cat->id) }}" class="group relative aspect-[3/4] rounded-2xl overflow-hidden bg-sand">
                @if($thumb)
                    <img src="{{ $thumb }}" alt="{{ $cat->name }}" class="absolute inset-0 w-full h-full object-cover transition duration-700 group-hover:scale-105">
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/15 to-transparent"></div>
                <div class="absolute inset-x-0 bottom-0 p-4 text-white">
                    <div class="font-semibold">{{ $cat->name }}</div>
                    <div class="text-xs text-white/70 mt-1 opacity-0 translate-y-1 group-hover:opacity-100 group-hover:translate-y-0 transition">Shop now →</div>
                </div>
            </a>
        @endforeach
    </div>
</section>
@endif

<section class="max-w-7xl mx-auto px-4 pb-6">
    <div class="flex items-end justify-between mb-8 gap-4">
        <div>
            <p class="text-[11px] uppercase tracking-[0.22em] text-black/40 mb-2">Featured</p>
            <h2 class="font-display text-4xl md:text-5xl">Staff picks</h2>
        </div>
        <a href="{{ route('ecommerce.shop') }}" class="text-sm font-medium text-accent">Shop all</a>
    </div>
    @include('ecommerce::front.partials.product_grid', ['products' => $featured])
</section>

@if($promoBanners->count())
<section class="max-w-7xl mx-auto px-4 py-14 md:py-16">
    <div class="grid md:grid-cols-2 gap-4 md:gap-5">
        @foreach($promoBanners as $banner)
            <a href="{{ $banner->button_link ?: route('ecommerce.shop') }}" class="group relative min-h-[280px] md:min-h-[340px] rounded-[1.5rem] overflow-hidden text-white">
                @if($banner->image)
                    <img src="{{ asset('uploads/img/'.$banner->image) }}" alt="" class="absolute inset-0 w-full h-full object-cover transition duration-700 group-hover:scale-105">
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/30 to-transparent"></div>
                <div class="absolute inset-x-0 bottom-0 p-6 md:p-8">
                    <h3 class="font-display text-3xl md:text-4xl mb-2">{{ $banner->title }}</h3>
                    <p class="text-sm text-white/75 max-w-sm mb-4">{{ $banner->subtitle }}</p>
                    <span class="inline-flex rounded-full bg-white text-ink px-5 py-2 text-xs font-semibold">{{ $banner->button_text ?: 'Shop now' }}</span>
                </div>
            </a>
        @endforeach
    </div>
</section>
@endif

<section class="bg-white border-y border-black/5">
    <div class="max-w-7xl mx-auto px-4 py-16 md:py-20">
        <div class="flex items-end justify-between mb-8 gap-4">
            <div>
                <p class="text-[11px] uppercase tracking-[0.22em] text-black/40 mb-2">Just in</p>
                <h2 class="font-display text-4xl md:text-5xl">New arrivals</h2>
            </div>
        </div>
        @include('ecommerce::front.partials.product_grid', ['products' => $new_arrivals])
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 py-16">
    <div class="flex items-end justify-between mb-8 gap-4">
        <div>
            <p class="text-[11px] uppercase tracking-[0.22em] text-black/40 mb-2">Popular</p>
            <h2 class="font-display text-4xl md:text-5xl">Best sellers</h2>
        </div>
    </div>
    @include('ecommerce::front.partials.product_grid', ['products' => $best_sellers])
</section>

@if($collections->count())
<section class="max-w-7xl mx-auto px-4 pb-10">
    <h2 class="font-display text-4xl md:text-5xl mb-8">Collections</h2>
    <div class="grid md:grid-cols-3 gap-4">
        @foreach($collections as $i => $collection)
            @php
                $shot = $promoBanners[$i]->image ?? ($hero->image ?? null);
            @endphp
            <a href="{{ route('ecommerce.collection', $collection->slug) }}" class="group relative min-h-[220px] rounded-2xl overflow-hidden bg-ink text-white">
                @if($shot)
                    <img src="{{ asset('uploads/img/'.$shot) }}" alt="" class="absolute inset-0 w-full h-full object-cover opacity-60 transition duration-700 group-hover:scale-105 group-hover:opacity-70">
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-ink/90 to-transparent"></div>
                <div class="absolute inset-x-0 bottom-0 p-6">
                    <div class="font-display text-2xl">{{ $collection->name }}</div>
                    <p class="text-white/65 text-sm mt-2">{{ \Illuminate\Support\Str::limit($collection->description, 70) }}</p>
                </div>
            </a>
        @endforeach
    </div>
</section>
@endif

@if($reviews->count())
<section class="max-w-7xl mx-auto px-4 py-10">
    <h2 class="font-display text-4xl mb-8">What customers say</h2>
    <div class="grid md:grid-cols-3 gap-4">
        @foreach($reviews as $review)
            <div class="rounded-2xl bg-white border border-black/5 p-6">
                <div class="text-amber-500 text-sm mb-3">{{ str_repeat('★', $review->rating) }}</div>
                <p class="text-sm text-black/70 leading-relaxed mb-4">“{{ $review->review }}”</p>
                <div class="text-xs text-black/40">{{ $review->author_name }} · {{ optional($review->product)->name }}</div>
            </div>
        @endforeach
    </div>
</section>
@endif

{{-- Lifestyle band --}}
<section class="relative my-8 md:my-12">
    <div class="max-w-7xl mx-auto px-4">
        <div class="relative rounded-[2rem] overflow-hidden min-h-[320px] md:min-h-[420px] text-white">
            @if($hero && $hero->image)
                <img src="{{ asset('uploads/img/'.$hero->image) }}" alt="" class="absolute inset-0 w-full h-full object-cover">
            @endif
            <div class="absolute inset-0 bg-ink/55"></div>
            <div class="relative p-8 md:p-14 max-w-xl">
                <p class="text-[11px] uppercase tracking-[0.22em] text-white/60 mb-3">Why Japs Store</p>
                <h2 class="font-display text-4xl md:text-5xl mb-4">Retail-grade stock, delivered online</h2>
                <p class="text-white/75 text-sm leading-relaxed mb-6">Every product you see is pulled from the same inventory we sell in-store — no ghost stock, transparent pricing, USA shipping.</p>
                <a href="{{ route('ecommerce.shop') }}" class="inline-flex rounded-full bg-white text-ink px-6 py-3 text-sm font-semibold">Start shopping</a>
            </div>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 py-14 md:py-16">
    <div class="rounded-[2rem] bg-ink text-white px-6 py-12 md:px-12 md:py-14 grid md:grid-cols-2 gap-8 items-center">
        <div>
            <h2 class="font-display text-4xl mb-3">Join the list</h2>
            <p class="text-white/65 text-sm max-w-md">New drops, restocks, and member-only offers. No spam.</p>
        </div>
        <form action="{{ route('ecommerce.contact.send') }}" method="post" class="flex flex-col sm:flex-row gap-2">
            @csrf
            <input type="hidden" name="name" value="Newsletter">
            <input type="hidden" name="message" value="Newsletter signup">
            <input type="email" name="email" required placeholder="Email address"
                   class="flex-1 rounded-full px-5 py-3 text-sm text-ink outline-none">
            <button class="rounded-full bg-accent px-6 py-3 text-sm font-semibold hover:bg-accent2 transition">Subscribe</button>
        </form>
    </div>
</section>
@endsection
