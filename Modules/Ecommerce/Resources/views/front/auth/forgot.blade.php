@extends('ecommerce::front.layouts.store')
@section('title', 'Forgot password')
@section('content')
<div class="max-w-md mx-auto px-4 py-14">
    <h1 class="font-display text-3xl font-bold mb-2">Forgot password</h1>
    <p class="text-sm text-[#666] mb-6">Enter the email on your wholesale account. We’ll create a reset link if it exists.</p>
    <form method="post" action="{{ route('ecommerce.password.email') }}" class="bg-white border border-[#e0e0e0] rounded-2xl p-6 space-y-3">
        @csrf
        <input type="email" name="email" required placeholder="Business email" class="w-full border rounded-lg px-3 py-2.5 text-sm">
        <button class="w-full py-3 rounded-lg text-white font-bold text-sm" style="background:#4CAF79">Send reset link</button>
    </form>
    @if(session('reset_debug_url'))
        <p class="text-sm mt-4 p-3 bg-[#f0f9f4] rounded-lg">Reset link (valid 2 hours): <a class="underline font-semibold" href="{{ session('reset_debug_url') }}">Continue</a></p>
    @endif
</div>
@endsection
