@extends('ecommerce::front.layouts.auth')
@section('title', 'Forgot Password')
@section('content')
<div class="auth-v2-form">
    <h1 class="auth-v2-title">Forgot Password?</h1>
    <div class="auth-v2-switch-link">Remembered it? <a class="auth-v2-link" href="{{ route('ecommerce.login') }}">Login</a></div>
    <form method="post" action="{{ route('ecommerce.password.email', absolute: false) }}">
        @csrf
        <div class="auth-v2-field">
            <label class="login-field-labels">Email Address<span class="asterisk">*</span></label>
            <input class="auth-v2-input" type="email" name="email" placeholder="Enter Email" required>
        </div>
        <div class="auth-v2-btn-row">
            <button class="auth-v2-btn auth-v2-btn--primary" type="submit">Send reset link</button>
        </div>
    </form>
    @if(session('reset_debug_url'))
        <p class="auth-v2-switch-link" style="margin-top:12px">Reset link: <a class="auth-v2-link" href="{{ session('reset_debug_url') }}">Continue</a></p>
    @endif
</div>
@endsection
