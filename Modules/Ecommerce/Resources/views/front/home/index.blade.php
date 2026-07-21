@extends('ecommerce::front.layouts.store')

@section('title', $ecom_settings->store_name ?? 'Store')

@section('content')
<section class="hero-gradient text-white">
    <div class="max-w-7xl mx-auto px-4 py-20 md:py-28 grid md:grid-cols-2 gap-10 items-center">
        <div>
            <p class="text-sky-200 text-sm uppercase tracking-[0.2em] mb-3">{{ $ecom_settings->store_name }}</p>
            <h1 class="text-4xl md:text-5xl font-semibold leading-tight mb-4">
                {{ optional($banners->first())->title ?? 'Discover products built for everyday excellence' }}
            </h1>
            <p class="text-slate-200 mb-8 max-w-md">
                {{ optional($banners->first())->subtitle ?? 'A modern storefront powered by your POS inventory.' }}
            </p>
            <div class="flex gap-3">
                <a href="{{ route('ecommerce.shop') }}" class="rounded-full bg-white text-ink px-6 py-3 text-sm font-medium">Shop now</a>
                @if(optional($banners->first())->button_link)
                    <a href="{{ $banners->first()->button_link }}" class="rounded-full border border-white/40 px-6 py-3 text-sm">
                        {{ $banners->first()->button_text ?: 'Learn more' }}
                    </a>
                @endif
            </div>
        </div>
        <div class="hidden md:block">
            @if(optional($banners->first())->image)
                <img src="{{ asset('uploads/img/'.$banners->first()->image) }}" alt="" class="rounded-2xl shadow-2xl w-full object-cover max-h-[420px]">
            @else
                <div class="rounded-2xl bg-white/10 border border-white/20 h-[360px]"></div>
            @endif
        </div>
    </div>
</section>

@if($categories->count())
<section class="max-w-7xl mx-auto px-4 py-14">
    <h2 class="text-2xl font-semibold mb-6">Shop by category</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($categories as $cat)
            <a href="{{ route('ecommerce.category', $cat->slug ?? $cat->id) }}" class="rounded-2xl bg-white border border-slate-200 p-5 hover:border-accent/40">
                <div class="font-medium">{{ $cat->name }}</div>
            </a>
        @endforeach
    </div>
</section>
@endif

<section class="max-w-7xl mx-auto px-4 py-6">
    <div class="flex items-end justify-between mb-6">
        <h2 class="text-2xl font-semibold">Featured</h2>
        <a href="{{ route('ecommerce.shop') }}" class="text-sm text-accent">View all</a>
    </div>
    @include('ecommerce::front.partials.product_grid', ['products' => $featured])
</section>

<section class="max-w-7xl mx-auto px-4 py-14">
    <h2 class="text-2xl font-semibold mb-6">New arrivals</h2>
    @include('ecommerce::front.partials.product_grid', ['products' => $new_arrivals])
</section>

@if($reviews->count())
<section class="bg-white border-y border-slate-200">
    <div class="max-w-7xl mx-auto px-4 py-14">
        <h2 class="text-2xl font-semibold mb-6">Customer reviews</h2>
        <div class="grid md:grid-cols-3 gap-4">
            @foreach($reviews as $review)
                <div class="rounded-2xl border border-slate-200 p-5">
                    <div class="text-amber-500 text-sm mb-2">{{ str_repeat('★', $review->rating) }}</div>
                    <p class="text-sm text-slate-600 mb-3">{{ $review->review }}</p>
                    <div class="text-xs text-slate-400">{{ $review->author_name }} · {{ optional($review->product)->name }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
