@extends('ecommerce::front.layouts.store')
@section('title', $product->ecom_seo_title ?: $product->name)
@section('meta_description', $product->ecom_seo_description ?: \Illuminate\Support\Str::limit(strip_tags($product->ecom_short_description ?? ''), 160))

@section('content')
@php $slug = $product->ecom_slug ?: $product->id; @endphp
<div class="max-w-7xl mx-auto px-4 py-10 grid md:grid-cols-2 gap-10">
    <div>
        <div class="rounded-2xl overflow-hidden bg-white border border-slate-200 aspect-square">
            <img id="mainImage" src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
        </div>
        @if(!empty($product->ecom_gallery))
            <div class="flex gap-2 mt-3 overflow-x-auto">
                <button type="button" onclick="document.getElementById('mainImage').src='{{ $product->image_url }}'" class="w-16 h-16 rounded-lg overflow-hidden border">
                    <img src="{{ $product->image_url }}" class="w-full h-full object-cover">
                </button>
                @foreach($product->ecom_gallery as $img)
                    <button type="button" onclick="document.getElementById('mainImage').src='{{ asset('uploads/img/'.$img) }}'" class="w-16 h-16 rounded-lg overflow-hidden border">
                        <img src="{{ asset('uploads/img/'.$img) }}" class="w-full h-full object-cover">
                    </button>
                @endforeach
            </div>
        @endif
    </div>
    <div>
        <p class="text-xs uppercase tracking-widest text-slate-400 mb-2">{{ optional($product->brand)->name }}</p>
        <h1 class="text-3xl font-semibold mb-2">{{ $product->name }}</h1>
        <p class="text-sm text-slate-500 mb-4">SKU: {{ $product->sku }}</p>
        <div class="flex items-baseline gap-3 mb-4">
            <span class="text-2xl font-semibold">${{ number_format($product->ecom_price, 2) }}</span>
            @if($product->ecom_online_price && $product->ecom_online_price > $product->ecom_price)
                <span class="text-slate-400 line-through">${{ number_format($product->ecom_online_price, 2) }}</span>
            @endif
        </div>
        <p class="text-sm mb-4 {{ $stock > 0 || !$product->enable_stock ? 'text-emerald-600' : 'text-rose-600' }}">
            {{ ($stock > 0 || !$product->enable_stock) ? 'In stock' : 'Out of stock' }}
        </p>
        <p class="text-slate-600 mb-6">{{ $product->ecom_short_description }}</p>

        @if($variation)
        <form action="{{ route('ecommerce.cart.add') }}" method="post" class="flex flex-wrap gap-3 mb-4">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="variation_id" value="{{ $variation->id }}">
            <input type="number" name="quantity" value="1" min="1" step="1" class="w-24 border rounded-full px-4 py-2.5 text-sm">
            <button class="rounded-full bg-ink text-white px-6 py-2.5 text-sm">Add to cart</button>
            <button name="buy_now" value="1" class="rounded-full border border-ink px-6 py-2.5 text-sm">Buy now</button>
        </form>
        @endif

        @auth('customer')
        <form action="{{ route('ecommerce.wishlist.toggle') }}" method="post" class="mb-6">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="variation_id" value="{{ optional($variation)->id }}">
            <button class="text-sm text-accent">Add to wishlist</button>
        </form>
        @endauth

        <div class="prose prose-sm max-w-none text-slate-600">
            {!! nl2br(e($product->ecom_long_description ?: $product->product_description)) !!}
        </div>
    </div>
</div>

@if($related->count())
<section class="max-w-7xl mx-auto px-4 pb-10">
    <h2 class="text-xl font-semibold mb-4">Related products</h2>
    @include('ecommerce::front.partials.product_grid', ['products' => $related])
</section>
@endif

@if($recently_viewed->count())
<section class="max-w-7xl mx-auto px-4 pb-16">
    <h2 class="text-xl font-semibold mb-4">Recently viewed</h2>
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
        'price' => $product->ecom_price,
        'availability' => ($stock > 0 || !$product->enable_stock) ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
    ],
], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush
@endsection
