@extends('ecommerce::front.layouts.store')
@section('title', 'Checkout')
@section('content')
<div class="gs-wrap py-10 md:py-12">
    <p class="text-[11px] uppercase tracking-[0.18em] text-[#8b95b8] font-bold mb-2">Secure checkout</p>
    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-8">Checkout</h1>

    <div class="grid lg:grid-cols-5 gap-8">
        <form action="{{ route('ecommerce.checkout.place', absolute: false) }}" method="post" class="lg:col-span-3 space-y-5">
            @csrf
            <section class="wn-panel p-6 space-y-4">
                <h2 class="text-xl font-extrabold">Contact</h2>
                <div class="grid md:grid-cols-2 gap-3">
                    <input name="email" type="email" required value="{{ old('email', optional(auth('customer')->user())->email) }}" placeholder="Email" class="wn-field">
                    <input name="phone" value="{{ old('phone', optional(auth('customer')->user())->mobile) }}" placeholder="Phone" class="wn-field">
                </div>
            </section>

            <section class="wn-panel p-6 space-y-4">
                <h2 class="text-xl font-extrabold">Shipping address</h2>
                <div class="grid md:grid-cols-2 gap-3">
                    <input name="shipping[first_name]" required value="{{ old('shipping.first_name') }}" placeholder="First name" class="wn-field">
                    <input name="shipping[last_name]" required value="{{ old('shipping.last_name') }}" placeholder="Last name" class="wn-field">
                </div>
                <input name="shipping[company]" value="{{ old('shipping.company') }}" placeholder="Company (optional)" class="wn-field">
                <input name="shipping[address_line_1]" required value="{{ old('shipping.address_line_1') }}" placeholder="Address line 1" class="wn-field">
                <input name="shipping[address_line_2]" value="{{ old('shipping.address_line_2') }}" placeholder="Address line 2" class="wn-field">
                <div class="grid md:grid-cols-3 gap-3">
                    <input name="shipping[city]" required value="{{ old('shipping.city') }}" placeholder="City" class="wn-field">
                    <select name="shipping[state]" required class="wn-field">
                        <option value="">State</option>
                        @foreach($us_states as $code => $name)
                            <option value="{{ $code }}" @selected(old('shipping.state')===$code)>{{ $name }}</option>
                        @endforeach
                    </select>
                    <input name="shipping[zip_code]" required value="{{ old('shipping.zip_code') }}" placeholder="ZIP" class="wn-field">
                </div>
                <input type="hidden" name="shipping[country]" value="United States">
                <label class="inline-flex items-center gap-2 text-sm text-[#8b95b8]"><input type="checkbox" name="billing_same" value="1" checked class="rounded"> Billing same as shipping</label>
            </section>

            <section class="wn-panel p-6 space-y-3">
                <h2 class="text-xl font-extrabold">Payment</h2>
                @if($settings->enable_cod)
                    <label class="flex items-center gap-2 text-sm"><input type="radio" name="payment_method" value="cod" checked> Cash on Delivery</label>
                @endif
                @if($settings->enable_stripe)
                    <label class="flex items-center gap-2 text-sm"><input type="radio" name="payment_method" value="stripe"> Stripe</label>
                @endif
                @if($settings->enable_paypal)
                    <label class="flex items-center gap-2 text-sm"><input type="radio" name="payment_method" value="paypal"> PayPal</label>
                @endif
                <textarea name="note" rows="2" placeholder="Order note" class="wn-field">{{ old('note') }}</textarea>
                <input type="hidden" name="coupon_code" value="{{ $cart->coupon_code }}">
                <button class="wn-checkout w-full mt-2" type="submit">Place order</button>
            </section>
        </form>

        <aside class="lg:col-span-2">
            <div class="wn-summary p-6 sticky top-28 space-y-3 text-sm">
                <h2 class="text-xl font-extrabold mb-2">Order summary</h2>
                @foreach($cart->items as $item)
                    <div class="flex justify-between gap-3">
                        <span class="text-[#8b95b8]">{{ optional($item->product)->name }} × {{ $item->quantity }}</span>
                        <span class="tabular-nums">${{ number_format($item->quantity * $item->unit_price, 2) }}</span>
                    </div>
                @endforeach
                <div class="border-t border-[#252d4d] pt-3 space-y-1">
                    <div class="flex justify-between"><span class="text-[#8b95b8]">Subtotal</span><span class="tabular-nums">${{ number_format($totals['subtotal'], 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-[#8b95b8]">Discount</span><span class="tabular-nums">-${{ number_format($discount, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-[#8b95b8]">Shipping</span><span class="tabular-nums">${{ number_format($totals['shipping'], 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-[#8b95b8]">Tax</span><span class="tabular-nums">${{ number_format($totals['tax'], 2) }}</span></div>
                    <div class="flex justify-between font-extrabold text-base pt-2"><span>Total</span><span class="tabular-nums">${{ number_format($totals['total'], 2) }}</span></div>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
