@extends('ecommerce::front.layouts.store')
@section('title', 'Shopping Cart')

@section('content')
@php
    $itemCount = $cart->items->count();
    $subtotal = (float) ($totals['subtotal'] ?? 0);
    $shippingAmt = (float) ($totals['shipping'] ?? 0);
    $taxAmt = (float) ($totals['tax'] ?? 0);
    $discountAmt = (float) ($totals['discount'] ?? 0);
    $totalAmt = (float) ($totals['total'] ?? 0);
    $freeShipMin = (float) ($freeShipMin ?? 500);
    $shippingFree = $shippingAmt <= 0 && $subtotal > 0;
    $remainingForFree = max(0, $freeShipMin - $subtotal);
@endphp

@if($itemCount === 0)
<main id="main-content" class="min-h-screen flex flex-col items-center justify-center py-20 px-4" style="background:#F4F4F4">
    <div class="text-center max-w-sm">
        <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4" style="background:#e8f5ee">
            <svg width="36" height="36" viewBox="0 0 36 36" fill="none" aria-hidden="true">
                <path d="M3 3h4l4 18H28l3.5-12H7" stroke="#4CAF79" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="14" cy="30" r="2.5" fill="#4CAF79"/>
                <circle cx="26" cy="30" r="2.5" fill="#4CAF79"/>
            </svg>
        </div>
        <h1 class="font-display text-2xl font-bold mb-2" style="color:#1a1a1a">Your cart is empty</h1>
        <p class="text-sm mb-6" style="color:#666">Browse our wholesale catalog and add products to get started.</p>
        <a href="{{ route('ecommerce.shop') }}"
           class="inline-flex px-8 py-3 rounded-lg text-white font-semibold text-sm transition-all hover:opacity-90"
           style="background:#4CAF79">Browse Products</a>
    </div>
</main>
@else
<main id="main-content" class="min-h-screen py-8 px-4" style="background:#F4F4F4">
    <div class="max-w-screen-xl mx-auto">
        <nav class="text-sm mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center gap-1.5">
                <li><a href="{{ url('/') }}" class="hover:underline" style="color:#1a73e8">Home</a></li>
                <li style="color:#999" aria-hidden="true">›</li>
                <li style="color:#666">Shopping Cart</li>
            </ol>
        </nav>

        <h1 class="font-display text-2xl font-bold mb-6" style="color:#1a1a1a">
            Shopping Cart
            <span class="text-base font-body font-normal" style="color:#999">
                ({{ $itemCount }} {{ $itemCount === 1 ? 'item' : 'items' }})
            </span>
        </h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-3">
                @if(! $shippingFree && $remainingForFree > 0)
                    <div class="rounded-xl p-3 flex items-center gap-2 text-sm" style="background:#e8f5ee;border:1px solid #c8e6d4">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                            <path d="M2 6h10l2 4H2V6z" stroke="#2E6A49" stroke-width="1.2" stroke-linejoin="round"/>
                            <circle cx="5" cy="12" r="1.5" fill="#2E6A49"/>
                            <circle cx="11" cy="12" r="1.5" fill="#2E6A49"/>
                            <path d="M2 6V3h7l1 3" stroke="#2E6A49" stroke-width="1.2" stroke-linecap="round"/>
                        </svg>
                        <span style="color:#2E6A49">Add <strong>${{ number_format($remainingForFree, 2) }}</strong> more to qualify for free shipping!</span>
                    </div>
                @elseif($shippingFree)
                    <div class="rounded-xl p-3 flex items-center gap-2 text-sm" style="background:#e8f5ee;border:1px solid #c8e6d4">
                        <svg width="16" height="16" fill="none" viewBox="0 0 16 16" aria-hidden="true">
                            <circle cx="8" cy="8" r="8" fill="#4CAF79"/>
                            <path d="M5 8l2 2 4-4" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span style="color:#2E6A49"><strong>Free shipping</strong> applied to your order!</span>
                    </div>
                @endif

                @foreach($cart->items as $item)
                    @php
                        $product = $item->product;
                        $variation = $item->variation;
                        $brand = optional($product)->brand;
                        $slug = $product ? ($product->ecom_slug ?: $product->id) : '#';
                        $lineTotal = $item->quantity * $item->unit_price;
                        $onSale = $product && $product->ecom_discount_price && $product->ecom_online_price
                            && $product->ecom_discount_price < $product->ecom_online_price;
                        $variantName = $variation && optional($variation)->name && ! in_array(strtolower((string) $variation->name), ['dummy', 'default'], true)
                            ? $variation->name
                            : null;
                    @endphp
                    <div class="bg-white rounded-xl border border-[#e8e8e8] p-4 flex gap-4">
                        <a href="{{ $product ? route('ecommerce.product', $slug) : '#' }}" class="shrink-0">
                            <img src="{{ $product->image_url ?? asset('img/default.png') }}" alt="{{ optional($product)->name }}"
                                 class="w-20 h-20 object-cover rounded-lg bg-[#f5f5f5]"
                                 onerror="this.onerror=null;this.src='{{ asset('img/default.png') }}';">
                        </a>
                        <div class="flex-1 min-w-0">
                            @if($brand)
                                <a href="{{ route('ecommerce.shop', ['brand' => $brand->id]) }}"
                                   class="text-xs font-semibold uppercase tracking-wide hover:underline" style="color:#2E6A49">{{ $brand->name }}</a>
                            @endif
                            <a href="{{ $product ? route('ecommerce.product', $slug) : '#' }}"
                               class="block font-semibold text-sm leading-snug hover:text-[#4CAF79] transition-colors mt-0.5 mb-1 line-clamp-2"
                               style="color:#1a1a1a">{{ optional($product)->name }}</a>
                            @if($variantName)
                                <p class="text-xs font-medium mb-0.5" style="color:#4CAF79">Variant: {{ $variantName }}</p>
                            @endif
                            @if(optional($product)->sku)
                                <p class="text-xs" style="color:#999">SKU: {{ $product->sku }}</p>
                            @endif

                            <div class="flex items-center justify-between mt-3 flex-wrap gap-2">
                                <form action="{{ route('ecommerce.cart.update', absolute: false) }}" method="post" class="cart-qty-form flex items-center border border-[#e0e0e0] rounded-lg overflow-hidden">
                                    @csrf
                                    <input type="hidden" name="item_id" value="{{ $item->id }}">
                                    <button type="button" class="qty-minus px-2.5 py-1.5 hover:bg-[#f5f5f5] transition-colors text-sm font-bold" style="color:#2E6A49" aria-label="Decrease quantity">−</button>
                                    <input type="number" name="quantity" value="{{ (int) $item->quantity }}" min="1"
                                           class="qty-input w-10 text-center text-sm font-semibold focus:outline-none border-0 bg-transparent appearance-none [-moz-appearance:textfield]"
                                           aria-label="Quantity">
                                    <button type="button" class="qty-plus px-2.5 py-1.5 hover:bg-[#f5f5f5] transition-colors text-sm font-bold" style="color:#2E6A49" aria-label="Increase quantity">+</button>
                                </form>
                                <div class="text-right">
                                    <p class="font-bold text-base" style="color:{{ $onSale ? '#e53e3e' : '#1a1a1a' }}">${{ number_format($lineTotal, 2) }}</p>
                                    <p class="text-xs" style="color:#999">${{ number_format($item->unit_price, 2) }} each</p>
                                </div>
                            </div>
                        </div>
                        <form action="{{ route('ecommerce.cart.remove', absolute: false) }}" method="post" class="shrink-0 self-start">
                            @csrf
                            <input type="hidden" name="item_id" value="{{ $item->id }}">
                            <button type="submit" class="p-1.5 rounded-lg hover:bg-red-50 hover:text-red-500 transition-colors" style="color:#999" aria-label="Remove item">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4l8 8M12 4L4 12"/></svg>
                            </button>
                        </form>
                    </div>
                @endforeach

                <div class="flex justify-end">
                    <form action="{{ route('ecommerce.cart.clear', absolute: false) }}" method="post" onsubmit="return confirm('Clear all items from your cart?');">
                        @csrf
                        <button type="submit" class="text-xs font-semibold hover:underline transition-colors" style="color:#e53e3e">Clear Cart</button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl border border-[#e8e8e8] p-5 sticky top-24">
                    <h2 class="font-display font-bold text-base mb-4" style="color:#1a1a1a">Order Summary</h2>

                    <div class="space-y-2 text-sm mb-4">
                        <div class="flex justify-between">
                            <span style="color:#666">Subtotal ({{ $itemCount }} {{ $itemCount === 1 ? 'item' : 'items' }})</span>
                            <span class="font-semibold" style="color:#1a1a1a">${{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span style="color:#666">Shipping</span>
                            <span class="font-semibold {{ $shippingFree ? 'text-[#4CAF79]' : '' }}" style="color:{{ $shippingFree ? '#4CAF79' : '#1a1a1a' }}">
                                {{ $shippingFree ? 'FREE' : '$'.number_format($shippingAmt, 2) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span style="color:#666">Est. Tax{{ $taxRateLabel ? ' ('.$taxRateLabel.'%)' : '' }}</span>
                            <span class="font-semibold" style="color:#1a1a1a">${{ number_format($taxAmt, 2) }}</span>
                        </div>
                        @if($discountAmt > 0)
                            <div class="flex justify-between">
                                <span style="color:#666">Discount</span>
                                <span class="font-semibold" style="color:#4CAF79">−${{ number_format($discountAmt, 2) }}</span>
                            </div>
                        @endif
                        <div class="border-t border-[#f0f0f0] pt-3 flex justify-between">
                            <span class="font-bold" style="color:#1a1a1a">Estimated Total</span>
                            <span class="font-bold text-base" style="color:#2E6A49">${{ number_format($totalAmt, 2) }}</span>
                        </div>
                    </div>

                    <form action="{{ route('ecommerce.cart.coupon', absolute: false) }}" method="post" class="flex gap-2 mb-4">
                        @csrf
                        <input type="text" name="coupon_code" value="{{ $cart->coupon_code }}" placeholder="Coupon code"
                               class="flex-1 border border-[#e0e0e0] rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#4CAF79]">
                        <button type="submit" class="px-3 py-2 rounded-lg text-sm font-semibold border border-[#e0e0e0] hover:border-[#4CAF79] hover:text-[#4CAF79] transition-all">Apply</button>
                    </form>

                    <a href="{{ route('ecommerce.checkout') }}"
                       class="block w-full py-3.5 rounded-lg text-white text-center font-semibold text-sm transition-all hover:opacity-90 active:scale-95 mb-3"
                       style="background:#4CAF79">Proceed to Checkout →</a>
                    <a href="{{ route('ecommerce.shop') }}"
                       class="block w-full py-2.5 rounded-lg text-center text-sm font-medium border border-[#e0e0e0] transition-all hover:border-[#4CAF79] hover:text-[#4CAF79]"
                       style="color:#666">Continue Shopping</a>

                    <div class="mt-4 pt-4 border-t border-[#f0f0f0]">
                        <p class="text-xs text-center font-medium mb-2" style="color:#999">Secure Checkout</p>
                        <div class="flex justify-center gap-2 opacity-60">
                            @foreach(['VISA', 'MC', 'AMEX', 'ACH'] as $method)
                                <span class="text-xs border border-[#e0e0e0] rounded px-1.5 py-0.5 font-bold" style="color:#666">{{ $method }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endif
@endsection

@push('head')
<style>
.qty-input::-webkit-outer-spin-button,
.qty-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
.qty-input { -moz-appearance: textfield; }
</style>
@endpush

@push('scripts')
<script>
document.querySelectorAll('.cart-qty-form').forEach(form => {
    const input = form.querySelector('.qty-input');
    const minus = form.querySelector('.qty-minus');
    const plus = form.querySelector('.qty-plus');
    if (!input || !minus || !plus) return;

    function setQty(v) {
        const n = Math.max(1, parseInt(v, 10) || 1);
        input.value = n;
        form.submit();
    }

    minus.addEventListener('click', () => setQty((parseInt(input.value, 10) || 1) - 1));
    plus.addEventListener('click', () => setQty((parseInt(input.value, 10) || 1) + 1));
    input.addEventListener('change', () => setQty(input.value));
});
</script>
@endpush
