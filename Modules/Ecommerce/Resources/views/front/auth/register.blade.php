@extends('ecommerce::front.layouts.store')
@section('title', 'Register')
@section('content')
<div class="max-w-md mx-auto px-4 py-16 md:py-20">
    <p class="text-[11px] uppercase tracking-[0.22em] text-black/40 mb-2 text-center">Account</p>
    <h1 class="font-display text-4xl text-center mb-8">Create account</h1>
    <form method="post" action="{{ route('ecommerce.register.post') }}" class="rounded-2xl bg-white border border-black/5 p-6 md:p-8 space-y-4">
        @csrf
        <div class="grid grid-cols-2 gap-3">
            <input name="first_name" value="{{ old('first_name') }}" placeholder="First name" required class="border border-black/10 rounded-xl px-4 py-2.5 text-sm bg-mist/40 focus:bg-white outline-none">
            <input name="last_name" value="{{ old('last_name') }}" placeholder="Last name" required class="border border-black/10 rounded-xl px-4 py-2.5 text-sm bg-mist/40 focus:bg-white outline-none">
        </div>
        <input name="mobile" value="{{ old('mobile') }}" placeholder="Mobile" required class="w-full border border-black/10 rounded-xl px-4 py-2.5 text-sm bg-mist/40 focus:bg-white outline-none">
        <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required class="w-full border border-black/10 rounded-xl px-4 py-2.5 text-sm bg-mist/40 focus:bg-white outline-none">
        <input type="password" name="password" placeholder="Password" required class="w-full border border-black/10 rounded-xl px-4 py-2.5 text-sm bg-mist/40 focus:bg-white outline-none">
        <input type="password" name="password_confirmation" placeholder="Confirm password" required class="w-full border border-black/10 rounded-xl px-4 py-2.5 text-sm bg-mist/40 focus:bg-white outline-none">
        <button class="w-full rounded-full bg-ink text-white py-3 text-sm font-semibold hover:bg-accent transition">Register</button>
    </form>
    <p class="text-center text-sm mt-5 text-black/55">Already have an account? <a href="{{ route('ecommerce.login') }}" class="text-accent font-medium">Sign in</a></p>
</div>
@endsection
