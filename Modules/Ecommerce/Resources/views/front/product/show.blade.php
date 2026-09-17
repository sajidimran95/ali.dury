@extends('ecommerce::front.layouts.store')
@section('title', $product->ecom_seo_title ?: $product->name)
@section('meta_description', $product->ecom_seo_description ?: \Illuminate\Support\Str::limit(strip_tags($product->ecom_short_description ?? ''), 160))

@section('content')
@php
    $price = $product->ecom_price;
    $compare = $product->ecom_online_price;
    $onSale = $compare && $compare > $price;
@endphp

<div class="max-w-[1400px] mx-auto px-4 py-8 md:py-10">
    <nav class="text-xs text-black/40 mb-5 flex flex-wrap gap-2">
        <a href="{{ url('/') }}" class="hover:text-brand">Home</a>
        <span>/</span>
        <a href="{{ route('ecommerce.shop') }}" class="hover:text-brand">Shop</a>
        @if($product->category)
            <span>/</span>
            <a href="{{ route('ecommerce.category', $product->category->id) }}" class="hover:text-brand">{{ $product->category->name }}</a>
        @endif
        <span>/</span>
        <span class="text-ink">{{ $product->name }}</span>
    </nav>

    <div class="grid lg:grid-cols-2 gap-8 lg:gap-12">
        <div>
            <div class="rounded-xl overflow-hidden bg-white border border-line aspect-square relative">
                <img id="mainImage" src="{{ $product->image_url }}" alt="{{ $product->name }}" class="absolute inset-0 w-full h-full object-cover"
                     onerror="this.onerror=null;this.src='{{ asset('img/default.png') }}';">
                @if($onSale)
                    <span class="absolute top-3 left-3 rounded bg-brand text-white text-[10px] font-bold uppercase px-2.5 py-1">Sale</span>
                @endif
            </div>
            @if(!empty($product->ecom_gallery))
                <div class="flex gap-2 mt-3 overflow-x-auto">
                    <button type="button" onclick="document.getElementById('mainImage').src='{{ $product->image_url }}'" class="w-20 h-20 rounded-lg overflow-hidden border border-line shrink-0">
                        <img src="{{ $product->image_url }}" class="w-full h-full object-cover" alt="">
                    </button>
                    @foreach($product->ecom_gallery as $img)
                        <button type="button" onclick="document.getElementById('mainImage').src='{{ asset('uploads/img/'.$img) }}'" class="w-20 h-20 rounded-lg overflow-hidden border border-line shrink-0">
                            <img src="{{ asset('uploads/img/'.$img) }}" class="w-full h-full object-cover" alt="">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div>
            @if(optional($product->brand)->name)
                <p class="text-[11px] uppercase tracking-[0.18em] text-black/40 font-bold mb-2">{{ $product->brand->name }}</p>
            @endif
            <h1 class="text-3xl md:text-4xl font-extrabold leading-tight mb-2">{{ $product->name }}</h1>
            <p class="text-sm text-black/45 mb-4">SKU {{ $product->sku }}</p>

            <div class="flex items-baseline gap-3 mb-3">
                <span class="text-3xl font-extrabold text-brand tabular-nums">${{ number_format($price, 2) }}</span>
                @if($onSale)
                    <span class="text-lg text-black/35 line-through tabular-nums">${{ number_format($compare, 2) }}</span>
                @endif
            </div>

            <p class="text-sm mb-4 {{ ($stock > 0 || !$product->enable_stock) ? 'text-emerald-700' : 'text-rose-600' }}">
                {{ ($stock > 0 || !$product->enable_stock) ? 'In stock — ready to ship' : 'Out of stock' }}
            </p>

            <p class="text-black/65 leading-relaxed mb-6">{{ $product->ecom_short_description }}</p>

            @if($variation && ($stock > 0 || !$product->enable_stock))
            <div class="mb-2">
                <div class="text-sm font-medium text-ink">Unit: {{ optional($product->unit)->actual_name ?? optional($product->unit)->short_name ?? 'Piece' }}</div>
                <div class="mt-2 border-t border-line"></div>
            </div>
            <form action="{{ route('ecommerce.cart.add', absolute: false) }}" method="post" class="flex flex-wrap items-center gap-3 mb-5 mt-4" id="addToCartForm">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="variation_id" value="{{ $variation->id }}">

                <div class="inline-flex items-center gap-2 rounded-full bg-[#f0f0f0] px-1.5 py-1.5">
                    <button type="button" id="qtyMinus"
                            class="h-9 w-9 rounded-full bg-white border border-[#ddd] text-ink text-lg leading-none flex items-center justify-center hover:bg-mist"
                            aria-label="Decrease quantity">−</button>
                    <input type="number" name="quantity" id="qtyInput" value="1" min="1" step="1"
                           class="w-10 bg-transparent border-0 text-center text-sm font-medium tabular-nums outline-none appearance-none [-moz-appearance:textfield]">
                    <button type="button" id="qtyPlus"
                            class="h-9 w-9 rounded-full bg-white border border-[#ddd] text-ink text-lg leading-none flex items-center justify-center hover:bg-mist"
                            aria-label="Increase quantity">+</button>
                </div>

                <button type="submit" class="rounded-full bg-ink text-white px-8 py-3 text-sm font-semibold hover:bg-black transition">
                    Add To Cart
                </button>
                <button type="submit" name="buy_now" value="1" class="rounded-full border border-ink px-6 py-3 text-sm font-semibold hover:bg-mist transition">
                    Buy now
                </button>
            </form>
            @endif

            @if(!($variation && ($stock > 0 || !$product->enable_stock)))
            <form method="post" action="{{ route('ecommerce.stock_alert') }}" class="mb-6 space-y-2">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <p class="text-sm font-semibold">Currently Out of Stock</p>
                <div class="flex gap-2">
                    <input type="email" name="email" required placeholder="Email for restock alert" class="flex-1 border rounded-lg px-3 py-2 text-sm" value="{{ optional(auth('customer')->user())->email }}">
                    <button class="px-4 py-2 rounded-lg text-white text-sm font-bold" style="background:#4CAF79">Notify Me</button>
                </div>
            </form>
            @endif

            @auth('customer')
            <form action="{{ route('ecommerce.wishlist.toggle', absolute: false) }}" method="post" class="mb-6">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="variation_id" value="{{ optional($variation)->id }}">
                <button class="text-sm font-bold text-brand">Add to wishlist</button>
            </form>
            @endauth

            <div class="rounded-xl bg-white border border-line p-5 text-sm text-black/70 leading-relaxed whitespace-pre-line">
                {!! nl2br(e($product->ecom_long_description ?: $product->product_description)) !!}
            </div>
        </div>
    </div>
</div>

@if($related->count())
<section class="max-w-[1400px] mx-auto px-4 pb-10">
    <h2 class="text-2xl font-extrabold mb-5">You may also like</h2>
    @include('ecommerce::front.partials.product_grid', ['products' => $related])
</section>
@endif

@if($recently_viewed->count())
<section class="max-w-[1400px] mx-auto px-4 pb-14">
    <h2 class="text-2xl font-extrabold mb-5">Recently viewed</h2>
    @include('ecommerce::front.partials.product_grid', ['products' => $recently_viewed])
</section>
@endif

@push('head')
<style>
#qtyInput::-webkit-outer-spin-button,
#qtyInput::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
#qtyInput { -moz-appearance: textfield; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    const input = document.getElementById('qtyInput');
    const minus = document.getElementById('qtyMinus');
    const plus = document.getElementById('qtyPlus');
    if (!input || !minus || !plus) return;

    function setQty(v) {
        const n = Math.max(1, parseInt(v, 10) || 1);
        input.value = n;
    }

    minus.addEventListener('click', () => setQty((parseInt(input.value, 10) || 1) - 1));
    plus.addEventListener('click', () => setQty((parseInt(input.value, 10) || 1) + 1));
    input.addEventListener('change', () => setQty(input.value));
})();
</script>
@endpush
@endsection
