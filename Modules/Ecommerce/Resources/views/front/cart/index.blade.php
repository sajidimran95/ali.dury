@extends('ecommerce::front.layouts.store')
@section('title', 'Cart')
@section('content')
@php
    $itemCount = $cart->items->sum('quantity');
    $freeMin = (float) ($freeShipMin ?? 750);
    $subtotal = (float) ($totals['subtotal'] ?? 0);
    $shipPct = $freeMin > 0 ? min(100, round(($subtotal / $freeMin) * 100)) : 100;
    $shipLeft = max(0, $freeMin - $subtotal);
@endphp
<div class="max-w-[1440px] mx-auto px-4 py-8 md:py-12">
    <p class="text-[11px] uppercase tracking-[0.18em] text-[#8b95b8] font-bold mb-2">Bag</p>
    <div class="flex flex-wrap items-end justify-between gap-3 mb-8">
        <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">Your cart</h1>
        @if($cart->items->isNotEmpty())
            <p class="text-sm text-[#8b95b8]">{{ (int) $itemCount }} {{ (int) $itemCount === 1 ? 'case' : 'cases' }}</p>
        @endif
    </div>

    @if($cart->items->isEmpty())
        <div class="wn-empty p-12 text-center">
            <p class="text-[#8b95b8] mb-6">Your cart is empty.</p>
            <a href="{{ route('ecommerce.shop') }}" class="wn-add inline-flex px-6" style="height:44px">Continue shopping</a>
        </div>
    @else
        <div class="grid lg:grid-cols-5 gap-8">
            <div class="lg:col-span-3 space-y-3">
                @foreach($cart->items as $item)
                    @php $product = $item->product; @endphp
                    <div class="wn-cart-item">
                        <a href="{{ $product ? route('ecommerce.product', $product->ecom_slug ?: $product->id) : '#' }}" class="wn-cart-thumb">
                            @if($product)
                                <img src="{{ $product->image_url }}" alt=""
                                     onerror="this.onerror=null;this.src='{{ asset('img/default.png') }}';">
                            @endif
                        </a>
                        <div class="flex-1 min-w-0">
                            <div class="font-extrabold text-sm uppercase tracking-wide truncate">{{ optional($product)->name }}</div>
                            <div class="text-xs text-[#8b95b8] mt-1">${{ number_format($item->unit_price, 2) }} / case</div>

                            <form action="{{ route('ecommerce.cart.update', absolute: false) }}" method="post" class="cart-qty-form mt-3">
                                @csrf
                                <input type="hidden" name="item_id" value="{{ $item->id }}">
                                <div class="wn-qty">
                                    <button type="button" class="qty-minus" aria-label="Decrease quantity">−</button>
                                    <input type="number" name="quantity" value="{{ (int) $item->quantity }}" min="1" step="1" class="qty-input">
                                    <button type="button" class="qty-plus" aria-label="Increase quantity">+</button>
                                </div>
                            </form>
                        </div>
                        <div class="text-right shrink-0 ml-auto">
                            <div class="font-extrabold tabular-nums text-lg">${{ number_format($item->quantity * $item->unit_price, 2) }}</div>
                            <form action="{{ route('ecommerce.cart.remove', absolute: false) }}" method="post" class="mt-2">
                                @csrf
                                <input type="hidden" name="item_id" value="{{ $item->id }}">
                                <button class="wn-remove" type="submit">Remove</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <aside class="lg:col-span-2">
                <div class="wn-summary p-6 sticky top-28 space-y-4">
                    <h2 class="text-xl font-extrabold tracking-tight">Order summary</h2>

                    <div class="rounded-lg bg-[#0c1224] border border-[#252d4d] p-3">
                        @if($shipLeft <= 0)
                            <p class="text-xs font-bold text-[#3ee07a]">Free shipping unlocked</p>
                        @else
                            <p class="text-xs text-[#8b95b8]">
                                Add <span class="text-white font-bold">${{ number_format($shipLeft, 2) }}</span> for free shipping
                            </p>
                        @endif
                        <div class="wn-ship"><span style="width: {{ $shipPct }}%"></span></div>
                    </div>

                    <form action="{{ route('ecommerce.cart.coupon', absolute: false) }}" method="post" class="flex gap-2">
                        @csrf
                        <input type="text" name="coupon_code" placeholder="Coupon code"
                               class="flex-1 input-w" value="{{ $cart->coupon_code }}">
                        <button class="wn-apply" type="submit">Apply</button>
                    </form>
                    <div class="space-y-2 text-sm border-t border-[#252d4d] pt-4">
                        <div class="flex justify-between"><span class="text-[#8b95b8]">Subtotal</span><span class="tabular-nums">${{ number_format($totals['subtotal'], 2) }}</span></div>
                        <div class="flex justify-between"><span class="text-[#8b95b8]">Shipping</span><span class="tabular-nums">${{ number_format($totals['shipping'], 2) }}</span></div>
                        <div class="flex justify-between"><span class="text-[#8b95b8]">Tax</span><span class="tabular-nums">${{ number_format($totals['tax'], 2) }}</span></div>
                        <div class="flex justify-between font-extrabold text-base pt-2 border-t border-[#252d4d]"><span>Total</span><span class="tabular-nums">${{ number_format($totals['total'], 2) }}</span></div>
                    </div>
                    <a href="{{ route('ecommerce.checkout') }}" class="wn-checkout w-full">Checkout</a>
                    <a href="{{ route('ecommerce.shop') }}" class="block text-center text-sm font-semibold text-[#8b95b8] hover:text-white">Continue shopping</a>
                </div>
            </aside>
        </div>
    @endif
</div>
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
