@extends('ecommerce::front.layouts.store')
@section('title', 'My Account')
@section('content')
@php
    $contact = $contact ?? auth('customer')->user();
    $first = $contact->ecom_first_name ?: $contact->name;
@endphp
@component('ecommerce::front.account.partials.shell', ['current' => 'ecommerce.account'])
    <div class="account-info-wrapper">
        <h4>Hello, {{ $first }}</h4>
        <h5 style="margin-top:8px;font-weight:400;color:#666">{{ $contact->email }}</h5>
        <p style="margin-top:12px;font-size:14px">Status: {{ \Modules\Ecommerce\Support\WholesaleAccount::label($approval) }}</p>
    </div>
    <p style="font-size:14px;margin-bottom:16px">Use the sections below to update your profile, password, addresses, orders, and licenses.</p>
    <div class="billing-back-btn">
        <a class="billing-btn" href="{{ route('ecommerce.account.profile') }}" style="text-decoration:none">Edit account information</a>
    </div>
@endcomponent
@endsection
