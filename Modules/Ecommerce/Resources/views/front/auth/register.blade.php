@extends('ecommerce::front.layouts.store')
@section('title', 'Register')
@section('content')
<div class="max-w-md mx-auto px-4 py-16">
    <h1 class="text-2xl font-semibold mb-6 text-center">Create account</h1>
    <form method="post" action="{{ route('ecommerce.register.post') }}" class="rounded-2xl bg-white border border-slate-200 p-6 space-y-4">
        @csrf
        <div class="grid grid-cols-2 gap-3">
            <input name="first_name" value="{{ old('first_name') }}" placeholder="First name" required class="border rounded-xl px-4 py-2.5 text-sm">
            <input name="last_name" value="{{ old('last_name') }}" placeholder="Last name" required class="border rounded-xl px-4 py-2.5 text-sm">
        </div>
        <input name="mobile" value="{{ old('mobile') }}" placeholder="Mobile" required class="w-full border rounded-xl px-4 py-2.5 text-sm">
        <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required class="w-full border rounded-xl px-4 py-2.5 text-sm">
        <input type="password" name="password" placeholder="Password" required class="w-full border rounded-xl px-4 py-2.5 text-sm">
        <input type="password" name="password_confirmation" placeholder="Confirm password" required class="w-full border rounded-xl px-4 py-2.5 text-sm">
        <button class="w-full rounded-full bg-ink text-white py-2.5">Register</button>
    </form>
</div>
@endsection
