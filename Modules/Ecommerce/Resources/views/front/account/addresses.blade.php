@extends('ecommerce::front.layouts.store')
@section('title', 'Addresses')
@section('content')
<div class="ecom-acc max-w-3xl mx-auto px-4 py-10">
    @include('ecommerce::front.account.partials.mobile_topbar', ['title' => 'Addresses'])
    <h1 class="text-2xl font-semibold mb-6 ecom-acc-desktop-only">Saved addresses</h1>

    <div class="ecom-acc-list ecom-acc-mobile-only mb-3">
        @forelse($addresses as $address)
            <div class="ecom-acc-order">
                <div class="font-bold text-[15px]">
                    {{ $address->first_name }} {{ $address->last_name }}
                    @if($address->is_default)<span class="text-brand text-[11px] ml-1">Default</span>@endif
                </div>
                <div class="text-[13px] text-black/55 leading-snug">{{ $address->formatted() }}</div>
            </div>
        @empty
            <div class="px-4 py-6 text-sm text-center text-black/45">No addresses saved.</div>
        @endforelse
    </div>

    <div class="ecom-acc-card ecom-acc-form ecom-acc-mobile-only">
        <div class="font-extrabold mb-3 text-[15px]">Add address</div>
        <form method="post" class="space-y-3">
            @csrf
            <div class="grid grid-cols-2 gap-2">
                <input name="first_name" required placeholder="First name" class="input-w">
                <input name="last_name" required placeholder="Last name" class="input-w">
            </div>
            <input name="company" placeholder="Company" class="input-w">
            <input name="address_line_1" required placeholder="Address line 1" class="input-w">
            <input name="address_line_2" placeholder="Address line 2" class="input-w">
            <div class="grid grid-cols-3 gap-2">
                <input name="city" required placeholder="City" class="input-w">
                <input name="state" required placeholder="State" class="input-w">
                <input name="zip_code" required placeholder="ZIP" class="input-w">
            </div>
            <input type="hidden" name="country" value="United States">
            <label class="text-sm flex items-center gap-2"><input type="checkbox" name="is_default" value="1"> Default shipping</label>
            <button type="submit" class="btn-brand">Save address</button>
        </form>
    </div>

    <div class="ecom-acc-desktop-only">
        <div class="space-y-3 mb-8">
            @foreach($addresses as $address)
                <div class="wn-panel p-4 text-sm">
                    <div class="font-medium">{{ $address->first_name }} {{ $address->last_name }} @if($address->is_default)<span class="text-brand text-xs">Default</span>@endif</div>
                    <div class="text-[#8b95b8]">{{ $address->formatted() }}</div>
                </div>
            @endforeach
        </div>
        <form method="post" class="wn-panel p-6 space-y-3">
            @csrf
            <h2 class="font-semibold">Add address</h2>
            <div class="grid md:grid-cols-2 gap-3">
                <input name="first_name" required placeholder="First name" class="wn-field">
                <input name="last_name" required placeholder="Last name" class="wn-field">
            </div>
            <input name="company" placeholder="Company" class="wn-field">
            <input name="address_line_1" required placeholder="Address line 1" class="wn-field">
            <input name="address_line_2" placeholder="Address line 2" class="wn-field">
            <div class="grid md:grid-cols-3 gap-3">
                <input name="city" required placeholder="City" class="wn-field">
                <input name="state" required placeholder="State" class="wn-field">
                <input name="zip_code" required placeholder="ZIP" class="wn-field">
            </div>
            <input type="hidden" name="country" value="United States">
            <label class="text-sm flex items-center gap-2"><input type="checkbox" name="is_default" value="1"> Default shipping</label>
            <button class="btn-brand">Save address</button>
        </form>
    </div>
</div>
@endsection
