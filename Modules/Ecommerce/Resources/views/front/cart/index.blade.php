@extends('ecommerce::front.layouts.store')
@section('title', 'Cart')
@section('content')
<div class="max-w-6xl mx-auto px-4 py-10 md:py-14">
    <p class="text-[11px] uppercase tracking-[0.22em] text-black/40 mb-2">Bag</p>
    <h1 class="font-display text-4xl md:text-5xl mb-8">Your cart</h1>

    @if($cart->items->isEmpty())
        <div class="rounded-2xl bg-white border border-black/5 p-12 text-center">
            <p class="text-black/50 mb-6">Your cart is empty.</p>
            <a href="{{ route('ecommerce.shop') }}" class="inline-flex rounded-full bg-ink text-white px-7 py-3 text-sm font-semibold hover:bg-accent transition">Continue shopping</a>
        </div>
    @else
        <div class="grid lg:grid-cols-5 gap-8">
            <div class="lg:col-span-3 space-y-4">
                @foreach($cart->items as $item)
                    @php $product = $item->product; @endphp
                    <div class="rounded-2xl bg-white border border-black/5 p-4 flex gap-4 items-center">
                        <a href="{{ $product ? route('ecommerce.product', $product->ecom_slug ?: $product->id) : '#' }}" class="w-20 h-20 rounded-xl overflow-hidden bg-mist shrink-0">
                            @if($product)
                                <img src="{{ $product->image_url }}" alt="" class="w-full h-full object-cover">
                            @endif
                        </a>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-sm truncate">{{ optional($product)->name }}</div>
                            <div class="text-xs text-black/45 mt-0.5">${{ number_format($item->unit_price, 2) }} each</div>
                            <form action="{{ route('ecommerce.cart.update') }}" method="post" class="flex items-center gap-2 mt-3">
                                @csrf
                                <input type="hidden" name="item_id" value="{{ $item->id }}">
                                <input type="number" name="quantity" value="{{ $item->quantity }}" min="0" step="1"
                                       class="w-20 rounded-full border border-black/10 px-3 py-1.5 text-sm">
                                <button class="text-xs font-medium text-accent">Update</button>
                            </form>
                        </div>
                        <div class="text-right shrink-0">
                            <div class="font-semibold tabular-nums">${{ number_format($item->quantity * $item->unit_price, 2) }}</div>
                            <form action="{{ route('ecommerce.cart.remove') }}" method="post" class="mt-2">
                                @csrf
                                <input type="hidden" name="item_id" value="{{ $item->id }}">
                                <button class="text-xs text-rose-600">Remove</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <aside class="lg:col-span-2">
                <div class="rounded-2xl bg-white border border-black/5 p-6 sticky top-28 space-y-4">
                    <h2 class="font-display text-2xl">Summary</h2>
                    <form action="{{ route('ecommerce.cart.coupon') }}" method="post" class="flex gap-2">
                        @csrf
                        <input type="text" name="coupon_code" placeholder="Coupon code"
                               class="flex-1 rounded-full border border-black/10 px-4 py-2.5 text-sm" value="{{ $cart->coupon_code }}">
                        <button class="rounded-full bg-mist px-4 text-sm font-medium hover:bg-sand">Apply</button>
                    </form>
                    <div class="space-y-2 text-sm border-t border-black/5 pt-4">
                        <div class="flex justify-between"><span class="text-black/50">Subtotal</span><span class="tabular-nums">${{ number_format($totals['subtotal'], 2) }}</span></div>
                        <div class="flex justify-between"><span class="text-black/50">Shipping</span><span class="tabular-nums">${{ number_format($totals['shipping'], 2) }}</span></div>
                        <div class="flex justify-between"><span class="text-black/50">Tax</span><span class="tabular-nums">${{ number_format($totals['tax'], 2) }}</span></div>
                        <div class="flex justify-between font-semibold text-base pt-2 border-t border-black/5"><span>Total</span><span class="tabular-nums">${{ number_format($totals['total'], 2) }}</span></div>
                    </div>
                    <a href="{{ route('ecommerce.checkout') }}" class="block text-center rounded-full bg-ink text-white py-3.5 text-sm font-semibold hover:bg-accent transition">Checkout</a>
                    <a href="{{ route('ecommerce.shop') }}" class="block text-center text-sm text-accent">Continue shopping</a>
                </div>
            </aside>
        </div>
    @endif
</div>
@endsection
