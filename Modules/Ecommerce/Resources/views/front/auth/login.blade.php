@extends('ecommerce::front.layouts.auth')
@section('title', 'Login')
@section('content')
@php $store = $ecom_settings->store_name ?? 'Japs Wholesale'; @endphp
<div class="auth-v2-form">
    <h1 class="auth-v2-title">Login to {{ $store }}</h1>
    <div class="auth-v2-switch-link">Don’t have an account? <a class="auth-v2-link" href="{{ route('ecommerce.register') }}">Sign Up</a></div>
    <form method="post" action="{{ route('ecommerce.login.post', absolute: false) }}">
        @csrf
        <div class="auth-v2-field">
            <label class="login-field-labels">Email Address<span class="asterisk">*</span></label>
            <input class="auth-v2-input" name="login" value="{{ old('login') }}" placeholder="Enter Email" required>
        </div>
        <div class="auth-v2-field">
            <label class="login-field-labels">Password<span class="asterisk">*</span></label>
            <input class="auth-v2-input" type="password" name="password" placeholder="Password" required>
        </div>
        <div class="auth-v2-forgot"><a href="{{ route('ecommerce.password.request') }}">Forgot Password?</a></div>
        <div class="auth-v2-btn-row">
            <button class="auth-v2-btn auth-v2-btn--primary" type="submit">Login</button>
        </div>
    </form>
</div>
@endsection
