@extends('ecommerce::front.layouts.store')
@section('title', 'Cart')
@section('content')
<div class="max-w-5xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-semibold mb-6">Your cart</h1>
    @if($cart->items->isEmpty())
        <p class="text-slate-500">Your cart is empty. <a href="{{ route('ecommerce.shop') }}" class="text-accent">Continue shopping</a></p>
    @else
        <div class="space-y-4 mb-8">
            @foreach($cart->items as $item)
                <div class="rounded-2xl bg-white border border-slate-200 p-4 flex flex-wrap gap-4 items-center justify-between">
                    <div>
                        <div class="font-medium">{{ optional($item->product)->name }}</div>
                        <div class="text-sm text-slate-500">${{ number_format($item->unit_price, 2) }} each</div>
                    </div>
                    <form action="{{ route('ecommerce.cart.update') }}" method="post" class="flex items-center gap-2">
                        @csrf
                        <input type="hidden" name="item_id" value="{{ $item->id }}">
                        <input type="number" name="quantity" value="{{ $item->quantity }}" min="0" step="1" class="w-20 border rounded-lg px-2 py-1.5 text-sm">
                        <button class="text-sm text-accent">Update</button>
                    </form>
                    <div class="font-semibold">${{ number_format($item->quantity * $item->unit_price, 2) }}</div>
                    <form action="{{ route('ecommerce.cart.remove') }}" method="post">
                        @csrf
                        <input type="hidden" name="item_id" value="{{ $item->id }}">
                        <button class="text-sm text-rose-600">Remove</button>
                    </form>
                </div>
            @endforeach
        </div>
        <form action="{{ route('ecommerce.cart.coupon') }}" method="post" class="flex gap-2 mb-6 max-w-md">
            @csrf
            <input type="text" name="coupon_code" placeholder="Coupon code" class="flex-1 border rounded-full px-4 py-2 text-sm" value="{{ $cart->coupon_code }}">
            <button class="rounded-full bg-slate-100 px-4 text-sm">Apply</button>
        </form>
        <div class="rounded-2xl bg-white border border-slate-200 p-6 max-w-md ml-auto space-y-2 text-sm">
            <div class="flex justify-between"><span>Subtotal</span><span>${{ number_format($totals['subtotal'], 2) }}</span></div>
            <div class="flex justify-between"><span>Shipping</span><span>${{ number_format($totals['shipping'], 2) }}</span></div>
            <div class="flex justify-between"><span>Tax</span><span>${{ number_format($totals['tax'], 2) }}</span></div>
            <div class="flex justify-between font-semibold text-base border-t pt-2"><span>Total</span><span>${{ number_format($totals['total'], 2) }}</span></div>
            <a href="{{ route('ecommerce.checkout') }}" class="mt-4 block text-center rounded-full bg-ink text-white py-3">Checkout</a>
        </div>
    @endif
</div>
@endsection
