@extends('ecommerce::front.layouts.store')
@section('title', 'Password')
@section('content')
<div class="ecom-acc max-w-md mx-auto px-4 py-10">
    @include('ecommerce::front.account.partials.mobile_topbar', ['title' => 'Password'])
    <h1 class="text-2xl font-semibold mb-6 ecom-acc-desktop-only">Change password</h1>
    <div class="ecom-acc-card ecom-acc-form ecom-acc-mobile-only">
        <form method="post" class="space-y-3">
            @csrf
            <input type="password" name="current_password" placeholder="Current password" required class="input-w">
            <input type="password" name="password" placeholder="New password" required class="input-w">
            <input type="password" name="password_confirmation" placeholder="Confirm password" required class="input-w">
            <button type="submit" class="btn-brand">Update</button>
        </form>
    </div>
    <form method="post" class="wn-panel p-6 space-y-3 ecom-acc-desktop-only">
        @csrf
        <input type="password" name="current_password" placeholder="Current password" required class="wn-field">
        <input type="password" name="password" placeholder="New password" required class="wn-field">
        <input type="password" name="password_confirmation" placeholder="Confirm password" required class="wn-field">
        <button class="btn-brand w-full">Update</button>
    </form>
</div>
@endsection
