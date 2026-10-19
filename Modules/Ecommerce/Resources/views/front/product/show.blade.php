@extends('ecommerce::front.layouts.store')
@section('title', $product->ecom_seo_title ?: $product->name)
@section('meta_description', $product->ecom_seo_description ?: \Illuminate\Support\Str::limit(strip_tags($product->ecom_short_description ?? ''), 160))

@section('content')
@php
    $price = $product->ecom_price;
    $compare = $product->ecom_online_price;
    $onSale = $compare && $compare > $price;
    $showPrices = auth('customer')->check();
    $gallerySrc = function ($img) {
        return preg_match('#^https?://#i', (string) $img) ? $img : asset('uploads/img/'.$img);
    };
@endphp

<div class="gs-pdp gs-wrap py-8 md:py-10">
    <nav class="gs-breadcrumb">
        <a href="{{ route('ecommerce.home') }}">Home</a>
        <span>›</span>
        <a href="{{ route('ecommerce.shop') }}">Shop</a>
        @if($product->category)
            <span>›</span>
            <a href="{{ route('ecommerce.category', $product->category->id) }}">{{ $product->category->name }}</a>
        @endif
        <span>›</span>
        <span class="text-black">{{ $product->name }}</span>
    </nav>

    <div class="grid lg:grid-cols-2 gap-8 lg:gap-12">
        <div>
            <div class="wn-panel overflow-hidden aspect-square relative">
                <img id="mainImage" src="{{ $product->image_url }}" alt="{{ $product->name }}" class="absolute inset-0 w-full h-full object-contain p-6"
                     onerror="this.onerror=null;this.src='{{ \Modules\Ecommerce\Support\Storefront::placeholder() }}';">
                @if($onSale)
                    <span class="absolute top-3 left-3 rounded bg-[#dd772d] text-white text-[10px] font-bold uppercase px-2.5 py-1">Sale</span>
                @endif
            </div>
            @if(!empty($product->ecom_gallery))
                <div class="flex gap-2 mt-3 overflow-x-auto">
                    <button type="button" onclick="document.getElementById('mainImage').src='{{ $product->image_url }}'" class="w-20 h-20 rounded-lg overflow-hidden border border-[#eee] bg-white shrink-0">
                        <img src="{{ $product->image_url }}" class="w-full h-full object-contain p-1" alt="">
                    </button>
                    @foreach($product->ecom_gallery as $img)
                        <button type="button" onclick="document.getElementById('mainImage').src='{{ $gallerySrc($img) }}'" class="w-20 h-20 rounded-lg overflow-hidden border border-[#eee] bg-white shrink-0">
                            <img src="{{ $gallerySrc($img) }}" class="w-full h-full object-contain p-1" alt="">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div>
            @if(optional($product->brand)->name)
                <p class="text-[11px] uppercase tracking-[0.18em] text-black/40 font-bold mb-2">{{ $product->brand->name }}</p>
            @endif
            <h1 class="leading-tight mb-2">{{ $product->name }}</h1>
            <p class="text-sm text-black/45 mb-4">SKU {{ $product->sku }}</p>

            <div class="flex items-baseline gap-2 mb-4">
                @if($showPrices)
                    <span class="text-3xl font-extrabold tabular-nums text-[#dd772d]">${{ number_format($price, 2) }}</span>
                    @if($onSale)
                        <span class="text-lg text-black/40 line-through tabular-nums">${{ number_format($compare, 2) }}</span>
                    @endif
                @else
                    <a href="{{ route('ecommerce.login') }}" class="text-lg font-extrabold tracking-wide text-[#dd772d]">LOGIN TO SEE PRICE</a>
                @endif
            </div>

            <p class="text-sm mb-4 {{ ($stock > 0 || !$product->enable_stock) ? 'text-[#139c57]' : 'text-rose-500' }}">
                {{ ($stock > 0 || !$product->enable_stock) ? '✓ IN STOCK' : 'Out of stock' }}
            </p>

            <p class="text-black/70 leading-relaxed mb-6">{{ strip_tags((string) $product->ecom_short_description) }}</p>

            @if($variation && ($stock > 0 || !$product->enable_stock))
            <form action="{{ route('ecommerce.cart.add', absolute: false) }}" method="post" class="flex flex-wrap items-center gap-3 mb-5 mt-4 product-card-form" id="addToCartForm">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="variation_id" value="{{ $variation->id }}">
                <div class="pc-classic__stepper" style="max-width:140px">
                    <button type="button" id="qtyMinus" class="pc-classic__stepper-btn pc-qty-minus" aria-label="Decrease quantity">−</button>
                    <input type="number" name="quantity" id="qtyInput" value="1" min="1" step="1" class="pc-classic__stepper-input pc-qty-input">
                    <button type="button" id="qtyPlus" class="pc-classic__stepper-btn pc-qty-plus" aria-label="Increase quantity">+</button>
                </div>
                <button type="submit" class="pc-classic__btn ecom-add-cart px-8">Add to Cart</button>
            </form>
            @endif

            @php
                $detailHtml = $product->ecom_long_description ?: $product->product_description;
                $detailIsHtml = is_string($detailHtml) && preg_match('/<\s*\w+/', $detailHtml);
            @endphp
            <div class="wn-panel p-5 text-sm text-black/70 leading-relaxed {{ $detailIsHtml ? 'ecom-html' : 'whitespace-pre-line' }}">
                @if($detailIsHtml)
                    {!! $detailHtml !!}
                @else
                    {!! nl2br(e($detailHtml)) !!}
                @endif
            </div>
        </div>
    </div>
</div>

@if(($related ?? collect())->count())
<section class="gs-wrap pb-10">
    <h2 class="gs-section-title mb-5">You may also like</h2>
    @include('ecommerce::front.partials.product_grid', ['products' => $related])
</section>
@endif
@endsection
