@extends('ecommerce::front.layouts.store')
@section('title', 'Reset password')
@section('content')
<div class="max-w-md mx-auto px-4 py-14">
    <h1 class="text-3xl font-extrabold mb-6">Reset password</h1>
    <form method="post" action="{{ route('ecommerce.password.update', absolute: false) }}" class="wn-panel p-6 space-y-3">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="email" name="email" required value="{{ $email }}" class="wn-field">
        <input type="password" name="password" required minlength="7" placeholder="New password" class="wn-field">
        <input type="password" name="password_confirmation" required placeholder="Confirm password" class="wn-field">
        <button class="btn-brand w-full">Update password</button>
    </form>
</div>
@endsection
