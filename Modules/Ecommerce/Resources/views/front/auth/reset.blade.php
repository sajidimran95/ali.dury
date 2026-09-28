@extends('ecommerce::front.layouts.store')
@section('title', 'Reset password')
@section('content')
<div class="max-w-md mx-auto px-4 py-14">
    <h1 class="font-display text-3xl font-bold mb-6">Reset password</h1>
    <form method="post" action="{{ route('ecommerce.password.update', absolute: false) }}" class="bg-white border border-[#e0e0e0] rounded-2xl p-6 space-y-3">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="email" name="email" required value="{{ $email }}" class="w-full border rounded-lg px-3 py-2.5 text-sm">
        <input type="password" name="password" required minlength="7" placeholder="New password" class="w-full border rounded-lg px-3 py-2.5 text-sm">
        <input type="password" name="password_confirmation" required placeholder="Confirm password" class="w-full border rounded-lg px-3 py-2.5 text-sm">
        <button class="w-full py-3 rounded-lg text-white font-bold text-sm" style="background:#f97316">Update password</button>
    </form>
</div>
@endsection
