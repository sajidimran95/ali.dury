@extends('ecommerce::front.layouts.auth')
@section('title', 'Reset password')
@section('content')
<div class="auth-v2-form">
    <h1 class="auth-v2-title">Reset password</h1>
    <form method="post" action="{{ route('ecommerce.password.update', absolute: false) }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="auth-v2-field">
            <label class="login-field-labels">Email Address<span class="asterisk">*</span></label>
            <input class="auth-v2-input" type="email" name="email" required value="{{ $email }}">
        </div>
        <div class="auth-v2-field">
            <label class="login-field-labels">New password<span class="asterisk">*</span></label>
            <input class="auth-v2-input" type="password" name="password" required minlength="7">
        </div>
        <div class="auth-v2-field">
            <label class="login-field-labels">Confirm password<span class="asterisk">*</span></label>
            <input class="auth-v2-input" type="password" name="password_confirmation" required>
        </div>
        <div class="auth-v2-btn-row">
            <button class="auth-v2-btn auth-v2-btn--primary" type="submit">Update password</button>
        </div>
    </form>
</div>
@endsection
