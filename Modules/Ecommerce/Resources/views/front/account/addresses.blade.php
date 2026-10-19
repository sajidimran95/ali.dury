@extends('ecommerce::front.layouts.store')
@section('title', 'Addresses')
@section('content')
@component('ecommerce::front.account.partials.shell', ['current' => 'ecommerce.account.addresses'])
    <div class="account-info-wrapper"><h4>Modify your address book entries</h4></div>
    @forelse($addresses as $address)
        <div class="entries-info" style="padding:16px 0;border-bottom:1px solid #eaeaea">
            <p><strong>{{ $address->first_name }} {{ $address->last_name }}</strong> @if($address->is_default) (Default) @endif</p>
            <p>{{ $address->formatted() }}</p>
        </div>
    @empty
        <p style="font-size:14px;color:#808080;margin-bottom:16px">No addresses saved.</p>
    @endforelse
    <form method="post" style="margin-top:24px">
        @csrf
        <h4 style="margin-bottom:16px;text-transform:uppercase;font-size:15px">Add address</h4>
        <div class="billing-info"><label>First name</label><input name="first_name" required></div>
        <div class="billing-info"><label>Last name</label><input name="last_name" required></div>
        <div class="billing-info"><label>Company</label><input name="company"></div>
        <div class="billing-info"><label>Address line 1</label><input name="address_line_1" required></div>
        <div class="billing-info"><label>Address line 2</label><input name="address_line_2"></div>
        <div class="billing-info"><label>City</label><input name="city" required></div>
        <div class="billing-info"><label>State</label><input name="state" required></div>
        <div class="billing-info"><label>ZIP</label><input name="zip_code" required></div>
        <input type="hidden" name="country" value="United States">
        <label style="font-size:14px;display:flex;gap:8px;align-items:center;margin:12px 0"><input type="checkbox" name="is_default" value="1"> Default shipping</label>
        <div class="billing-back-btn"><button class="billing-btn" type="submit">Save address</button></div>
    </form>
@endcomponent
@endsection
