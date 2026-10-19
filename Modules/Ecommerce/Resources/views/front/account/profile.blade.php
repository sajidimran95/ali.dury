@extends('ecommerce::front.layouts.store')
@section('title', 'Account information')
@section('content')
@component('ecommerce::front.account.partials.shell', ['current' => 'ecommerce.account.profile'])
    <div class="account-info-wrapper"><h4>Edit your account information</h4></div>
    <form method="post">
        @csrf
        <div class="billing-info"><label>First name</label><input name="first_name" value="{{ old('first_name', $contact->ecom_first_name) }}" required></div>
        <div class="billing-info"><label>Last name</label><input name="last_name" value="{{ old('last_name', $contact->ecom_last_name) }}" required></div>
        <div class="billing-info"><label>Email</label><input name="email" type="email" value="{{ old('email', $contact->email) }}" required></div>
        <div class="billing-info"><label>Mobile</label><input name="mobile" value="{{ old('mobile', $contact->mobile) }}" required></div>
        <div class="billing-back-btn"><button class="billing-btn" type="submit">Save</button></div>
    </form>
@endcomponent
@endsection
