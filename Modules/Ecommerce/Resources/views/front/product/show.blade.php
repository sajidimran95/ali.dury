@extends('ecommerce::front.layouts.store')
@section('title', $product->ecom_seo_title ?: $product->name)
@section('meta_description', $product->ecom_seo_description ?: \Illuminate\Support\Str::limit(strip_tags($product->ecom_short_description ?? ''), 160))

@section('content')
@php
    $price = $product->ecom_price;
    $compare = $product->ecom_online_price;
    $onSale = $compare && $compare > $price;
    $showPrices = auth('customer')->check();
@endphp

<div class="max-w-[1400px] mx-auto px-4 py-8 md:py-10">
    <nav class="text-xs text-[#8b95b8] mb-5 flex flex-wrap gap-2">
        <a href="{{ route('ecommerce.home') }}" class="hover:text-white">Home</a>
        <span>/</span>
        <a href="{{ route('ecommerce.shop') }}" class="hover:text-white">Shop</a>
        @if($product->category)
            <span>/</span>
            <a href="{{ route('ecommerce.category', $product->category->id) }}" class="hover:text-white">{{ $product->category->name }}</a>
        @endif
        <span>/</span>
        <span class="text-white">{{ $product->name }}</span>
    </nav>

    <div class="grid lg:grid-cols-2 gap-8 lg:gap-12">
        <div>
            <div class="wn-panel overflow-hidden aspect-square relative">
                <img id="mainImage" src="{{ $product->image_url }}" alt="{{ $product->name }}" class="absolute inset-0 w-full h-full object-contain p-6"
                     onerror="this.onerror=null;this.src='{{ asset('img/default.png') }}';">
                @if($onSale)
                    <span class="absolute top-3 left-3 rounded bg-[#f23488] text-white text-[10px] font-bold uppercase px-2.5 py-1">Sale</span>
                @endif
            </div>
            @if(!empty($product->ecom_gallery))
                <div class="flex gap-2 mt-3 overflow-x-auto">
                    <button type="button" onclick="document.getElementById('mainImage').src='{{ $product->image_url }}'" class="w-20 h-20 rounded-lg overflow-hidden border border-[#252d4d] bg-[#0c1224] shrink-0">
                        <img src="{{ $product->image_url }}" class="w-full h-full object-contain p-1" alt="">
                    </button>
                    @foreach($product->ecom_gallery as $img)
                        <button type="button" onclick="document.getElementById('mainImage').src='{{ asset('uploads/img/'.$img) }}'" class="w-20 h-20 rounded-lg overflow-hidden border border-[#252d4d] bg-[#0c1224] shrink-0">
                            <img src="{{ asset('uploads/img/'.$img) }}" class="w-full h-full object-contain p-1" alt="">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div>
            @if(optional($product->brand)->name)
                <p class="text-[11px] uppercase tracking-[0.18em] text-[#8b95b8] font-bold mb-2">{{ $product->brand->name }}</p>
            @endif
            <h1 class="text-3xl md:text-4xl font-extrabold leading-tight mb-2">{{ $product->name }}</h1>
            <p class="text-sm text-[#8b95b8] mb-4">SKU {{ $product->sku }}</p>

            <div class="flex items-baseline gap-2 mb-2">
                @if($showPrices)
                    <span class="text-3xl font-extrabold tabular-nums">${{ number_format($price, 2) }}</span>
                    <span class="text-xs font-semibold text-[#8b95b8] uppercase">wholesale</span>
                    @if($onSale)
                        <span class="text-lg text-[#8b95b8] line-through tabular-nums">${{ number_format($compare, 2) }}</span>
                    @endif
                @else
                    <a href="{{ route('ecommerce.login') }}" class="text-lg font-extrabold tracking-wide text-[#8b95b8]">LOGIN TO SEE PRICE</a>
                @endif
            </div>
            <div class="gc-price-box text-sm mb-5">
                <p class="text-[#8b95b8]">Min. order: 1 case · {{ strip_tags($product->ecom_short_description ?: '1 case') }}</p>
            </div>

            <p class="text-sm mb-4 {{ ($stock > 0 || !$product->enable_stock) ? 'text-[#3ee07a]' : 'text-rose-400' }}">
                {{ ($stock > 0 || !$product->enable_stock) ? '✓ IN STOCK' : 'Out of stock' }}
            </p>

            <p class="text-[#c5cce6] leading-relaxed mb-6">{{ strip_tags((string) $product->ecom_short_description) }}</p>

            @if($variation && ($stock > 0 || !$product->enable_stock))
            <form action="{{ route('ecommerce.cart.add', absolute: false) }}" method="post" class="flex flex-wrap items-center gap-3 mb-5 mt-4 product-card-form" id="addToCartForm">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="variation_id" value="{{ $variation->id }}">
                <div class="wn-qty">
                    <button type="button" id="qtyMinus" class="pc-qty-minus" aria-label="Decrease quantity">−</button>
                    <input type="number" name="quantity" id="qtyInput" value="1" min="1" step="1" class="pc-qty-input">
                    <button type="button" id="qtyPlus" class="pc-qty-plus" aria-label="Increase quantity">+</button>
                </div>
                <button type="submit" class="wn-add ecom-add-cart px-8" style="height:42px;flex:0 0 auto">ADD CASE</button>
                <button type="submit" name="buy_now" value="1" class="wn-checkout px-6" style="height:42px">Buy Now</button>
            </form>
            @endif

            @auth('customer')
            <form action="{{ route('ecommerce.wishlist.toggle', absolute: false) }}" method="post" class="mb-6">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="variation_id" value="{{ optional($variation)->id }}">
                <button class="text-sm font-bold text-[#f23488]">Add to wishlist</button>
            </form>
            @endauth

            @php
                $detailHtml = $product->ecom_long_description ?: $product->product_description;
                $detailIsHtml = is_string($detailHtml) && preg_match('/<\s*\w+/', $detailHtml);
            @endphp
            <div class="wn-panel p-5 text-sm text-[#c5cce6] leading-relaxed {{ $detailIsHtml ? 'ecom-html' : 'whitespace-pre-line' }}">
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
<section class="max-w-[1400px] mx-auto px-4 pb-10">
    <h2 class="text-2xl font-extrabold mb-5">You may also like</h2>
    @include('ecommerce::front.partials.product_grid', ['products' => $related])
</section>
@endif

<section class="max-w-[1400px] mx-auto px-4 pb-14">
    <h2 class="text-2xl font-extrabold mb-5">Recently viewed</h2>
    @include('ecommerce::front.partials.product_grid', ['products' => $recently_viewed ?? collect()])
</section>
@endsection
