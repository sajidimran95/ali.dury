@extends('ecommerce::front.layouts.store')
@section('title', 'Profile')
@section('content')
<div class="ecom-acc max-w-md mx-auto px-4 py-10">
    @include('ecommerce::front.account.partials.mobile_topbar', ['title' => 'Profile'])
    <h1 class="text-2xl font-semibold mb-6 ecom-acc-desktop-only">Profile</h1>
    <div class="ecom-acc-card ecom-acc-form ecom-acc-mobile-only">
        <form method="post" class="space-y-3">
            @csrf
            <input name="first_name" value="{{ old('first_name', $contact->ecom_first_name) }}" placeholder="First name" class="input-w" required>
            <input name="last_name" value="{{ old('last_name', $contact->ecom_last_name) }}" placeholder="Last name" class="input-w" required>
            <input name="email" type="email" value="{{ old('email', $contact->email) }}" class="input-w" required>
            <input name="mobile" value="{{ old('mobile', $contact->mobile) }}" class="input-w" required>
            <button type="submit" class="btn-brand">Save</button>
        </form>
    </div>
    <form method="post" class="wn-panel p-6 space-y-3 ecom-acc-desktop-only">
        @csrf
        <input name="first_name" value="{{ old('first_name', $contact->ecom_first_name) }}" placeholder="First name" class="wn-field" required>
        <input name="last_name" value="{{ old('last_name', $contact->ecom_last_name) }}" placeholder="Last name" class="wn-field" required>
        <input name="email" type="email" value="{{ old('email', $contact->email) }}" class="wn-field" required>
        <input name="mobile" value="{{ old('mobile', $contact->mobile) }}" class="wn-field" required>
        <button class="btn-brand w-full">Save</button>
    </form>
</div>
@endsection
