@extends('ecommerce::front.layouts.store')
@section('title', 'Password')
@section('content')
<div class="max-w-md mx-auto px-4 py-10">
    <h1 class="text-2xl font-semibold mb-6">Change password</h1>
    <form method="post" class="rounded-2xl border bg-white p-6 space-y-3">
        @csrf
        <input type="password" name="current_password" placeholder="Current password" required class="w-full border rounded-xl px-4 py-2.5 text-sm">
        <input type="password" name="password" placeholder="New password" required class="w-full border rounded-xl px-4 py-2.5 text-sm">
        <input type="password" name="password_confirmation" placeholder="Confirm password" required class="w-full border rounded-xl px-4 py-2.5 text-sm">
        <button class="w-full rounded-full bg-ink text-white py-2.5">Update</button>
    </form>
</div>
@endsection
