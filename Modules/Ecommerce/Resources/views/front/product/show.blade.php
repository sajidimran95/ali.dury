@extends('ecommerce::front.layouts.store')
@section('title', $product->ecom_seo_title ?: $product->name)
@section('meta_description', $product->ecom_seo_description ?: \Illuminate\Support\Str::limit(strip_tags($product->ecom_short_description ?? ''), 160))

@section('content')
@php
    $slug = $product->ecom_slug ?: $product->id;
    $price = $product->ecom_price;
    $compare = $product->ecom_online_price;
    $onSale = $compare && $compare > $price;
@endphp

<div class="max-w-7xl mx-auto px-4 py-8 md:py-12">
    <nav class="text-xs text-black/40 mb-6 flex flex-wrap gap-2">
        <a href="{{ url('/') }}" class="hover:text-accent">Home</a>
        <span>/</span>
        <a href="{{ route('ecommerce.shop') }}" class="hover:text-accent">Shop</a>
        @if($product->category)
            <span>/</span>
            <a href="{{ route('ecommerce.category', $product->category->id) }}" class="hover:text-accent">{{ $product->category->name }}</a>
        @endif
        <span>/</span>
        <span class="text-ink">{{ $product->name }}</span>
    </nav>

    <div class="grid lg:grid-cols-2 gap-10 lg:gap-14">
        <div>
            <div class="rounded-[1.5rem] overflow-hidden bg-white border border-black/5 aspect-square relative">
                <img id="mainImage" src="{{ $product->image_url }}" alt="{{ $product->name }}" class="absolute inset-0 w-full h-full object-cover">
                @if($onSale)
                    <span class="absolute top-4 left-4 rounded-full bg-accent text-white text-[10px] font-semibold uppercase tracking-wide px-3 py-1">Sale</span>
                @endif
            </div>
            @if(!empty($product->ecom_gallery))
                <div class="flex gap-2 mt-3 overflow-x-auto">
                    <button type="button" onclick="document.getElementById('mainImage').src='{{ $product->image_url }}'" class="w-20 h-20 rounded-xl overflow-hidden border border-black/10 shrink-0">
                        <img src="{{ $product->image_url }}" class="w-full h-full object-cover" alt="">
                    </button>
                    @foreach($product->ecom_gallery as $img)
                        <button type="button" onclick="document.getElementById('mainImage').src='{{ asset('uploads/img/'.$img) }}'" class="w-20 h-20 rounded-xl overflow-hidden border border-black/10 shrink-0">
                            <img src="{{ asset('uploads/img/'.$img) }}" class="w-full h-full object-cover" alt="">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div>
            @if(optional($product->brand)->name)
                <p class="text-[11px] uppercase tracking-[0.2em] text-black/40 mb-2">{{ $product->brand->name }}</p>
            @endif
            <h1 class="font-display text-4xl md:text-5xl leading-tight mb-3">{{ $product->name }}</h1>
            <p class="text-sm text-black/45 mb-5">SKU {{ $product->sku }}</p>

            <div class="flex items-baseline gap-3 mb-4">
                <span class="text-3xl font-semibold tabular-nums">${{ number_format($price, 2) }}</span>
                @if($onSale)
                    <span class="text-lg text-black/35 line-through tabular-nums">${{ number_format($compare, 2) }}</span>
                @endif
            </div>

            <p class="text-sm mb-5 {{ ($stock > 0 || !$product->enable_stock) ? 'text-emerald-700' : 'text-rose-600' }}">
                {{ ($stock > 0 || !$product->enable_stock) ? 'In stock — ready to ship' : 'Out of stock' }}
            </p>

            <p class="text-black/65 leading-relaxed mb-8">{{ $product->ecom_short_description }}</p>

            @if($variation && ($stock > 0 || !$product->enable_stock))
            <form action="{{ route('ecommerce.cart.add', absolute: false) }}" method="post" class="flex flex-wrap gap-3 mb-4">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="variation_id" value="{{ $variation->id }}">
                <input type="number" name="quantity" value="1" min="1" step="1"
                       class="w-24 rounded-full border border-black/10 bg-white px-4 py-3 text-sm">
                <button class="rounded-full bg-ink text-white px-8 py-3 text-sm font-semibold hover:bg-accent transition">Add to cart</button>
                <button name="buy_now" value="1" class="rounded-full border border-ink px-8 py-3 text-sm font-semibold hover:bg-mist transition">Buy now</button>
            </form>
            @endif

            @auth('customer')
            <form action="{{ route('ecommerce.wishlist.toggle') }}" method="post" class="mb-8">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="variation_id" value="{{ optional($variation)->id }}">
                <button class="text-sm font-medium text-accent hover:text-accent2">Add to wishlist</button>
            </form>
            @endauth

            <div class="rounded-2xl bg-white border border-black/5 p-5 text-sm text-black/70 leading-relaxed whitespace-pre-line">
                {!! nl2br(e($product->ecom_long_description ?: $product->product_description)) !!}
            </div>
        </div>
    </div>
</div>

@if($related->count())
<section class="max-w-7xl mx-auto px-4 pb-10">
    <h2 class="font-display text-3xl mb-6">You may also like</h2>
    @include('ecommerce::front.partials.product_grid', ['products' => $related])
</section>
@endif

@if($recently_viewed->count())
<section class="max-w-7xl mx-auto px-4 pb-16">
    <h2 class="font-display text-3xl mb-6">Recently viewed</h2>
    @include('ecommerce::front.partials.product_grid', ['products' => $recently_viewed])
</section>
@endif

@push('head')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => $product->name,
    'sku' => $product->sku,
    'image' => $product->image_url,
    'description' => $product->ecom_short_description,
    'offers' => [
        '@type' => 'Offer',
        'priceCurrency' => 'USD',
        'price' => $price,
        'availability' => ($stock > 0 || !$product->enable_stock) ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
    ],
], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush
@endsection
