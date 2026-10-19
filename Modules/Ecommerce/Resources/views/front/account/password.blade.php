@extends('ecommerce::front.layouts.store')
@section('title', 'Change password')
@section('content')
@component('ecommerce::front.account.partials.shell', ['current' => 'ecommerce.account.password'])
    <div class="account-info-wrapper"><h4>Change your password</h4></div>
    <form method="post">
        @csrf
        <div class="billing-info"><label>Current password</label><input type="password" name="current_password" required></div>
        <div class="billing-info"><label>New password</label><input type="password" name="password" required></div>
        <div class="billing-info"><label>Confirm password</label><input type="password" name="password_confirmation" required></div>
        <div class="billing-back-btn"><button class="billing-btn" type="submit">Update</button></div>
    </form>
@endcomponent
@endsection
