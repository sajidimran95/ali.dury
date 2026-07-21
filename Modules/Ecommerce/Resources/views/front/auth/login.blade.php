@extends('ecommerce::front.layouts.store')
@section('title', 'Login')
@section('content')
<div class="max-w-md mx-auto px-4 py-16">
    <h1 class="text-2xl font-semibold mb-6 text-center">Sign in</h1>
    <form method="post" action="{{ route('ecommerce.login.post') }}" class="rounded-2xl bg-white border border-slate-200 p-6 space-y-4">
        @csrf
        <input name="login" value="{{ old('login') }}" placeholder="Email or phone" required class="w-full border rounded-xl px-4 py-2.5 text-sm">
        <input type="password" name="password" placeholder="Password" required class="w-full border rounded-xl px-4 py-2.5 text-sm">
        <label class="text-sm flex items-center gap-2"><input type="checkbox" name="remember" value="1"> Remember me</label>
        <button class="w-full rounded-full bg-ink text-white py-2.5">Login</button>
    </form>
    <p class="text-center text-sm mt-4">No account? <a href="{{ route('ecommerce.register') }}" class="text-accent">Register</a></p>
</div>
@endsection
