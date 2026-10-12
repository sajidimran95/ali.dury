@extends('ecommerce::front.layouts.store')
@section('title', 'Forgot password')
@section('content')
<div class="max-w-md mx-auto px-4 py-14">
    <h1 class="text-3xl font-extrabold mb-2">Forgot password</h1>
    <p class="text-sm text-[#8b95b8] mb-6">Enter the email on your wholesale account. We’ll create a reset link if it exists.</p>
    <form method="post" action="{{ route('ecommerce.password.email', absolute: false) }}" class="wn-panel p-6 space-y-3">
        @csrf
        <input type="email" name="email" required placeholder="Business email" class="wn-field">
        <button class="btn-brand w-full">Send reset link</button>
    </form>
    @if(session('reset_debug_url'))
        <p class="text-sm mt-4 p-3 wn-panel">Reset link (valid 2 hours): <a class="underline font-semibold text-[#f23488]" href="{{ session('reset_debug_url') }}">Continue</a></p>
    @endif
</div>
@endsection
