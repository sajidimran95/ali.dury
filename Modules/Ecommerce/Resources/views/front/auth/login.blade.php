@extends('ecommerce::front.layouts.store')
@section('title', 'Login')
@section('content')
<div class="max-w-md mx-auto px-4 py-16 md:py-20">
    <p class="text-[11px] uppercase tracking-[0.22em] text-black/40 mb-2 text-center">Account</p>
    <h1 class="font-display text-4xl text-center mb-8">Sign in</h1>
    <form method="post" action="{{ route('ecommerce.login.post') }}" class="rounded-2xl bg-white border border-black/5 p-6 md:p-8 space-y-4">
        @csrf
        <input name="login" value="{{ old('login') }}" placeholder="Email or phone" required
               class="w-full border border-black/10 rounded-xl px-4 py-2.5 text-sm bg-mist/40 focus:bg-white outline-none focus:ring-2 focus:ring-accent/30">
        <input type="password" name="password" placeholder="Password" required
               class="w-full border border-black/10 rounded-xl px-4 py-2.5 text-sm bg-mist/40 focus:bg-white outline-none focus:ring-2 focus:ring-accent/30">
        <label class="text-sm flex items-center gap-2 text-black/70"><input type="checkbox" name="remember" value="1" class="rounded"> Remember me</label>
        <button class="w-full rounded-full bg-ink text-white py-3 text-sm font-semibold hover:bg-accent transition">Login</button>
    </form>
    <p class="text-center text-sm mt-5 text-black/55">No account? <a href="{{ route('ecommerce.register') }}" class="text-accent font-medium">Register</a></p>
</div>
@endsection
