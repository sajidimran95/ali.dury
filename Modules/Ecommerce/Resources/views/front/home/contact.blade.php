@extends('ecommerce::front.layouts.store')
@section('title', 'Contact Sales')
@section('content')
<div class="max-w-lg mx-auto px-4 py-12">
    <h1 class="text-4xl font-extrabold mb-2">Contact Us</h1>
    <p class="text-sm text-[#8b95b8] mb-6">Questions about wholesale pricing, shipping, or your account.</p>
    <form method="post" action="{{ route('ecommerce.contact.send', absolute: false) }}" class="wn-panel p-6 space-y-3">
        @csrf
        <input name="name" required placeholder="Name" class="wn-field">
        <input type="email" name="email" required placeholder="Email" class="wn-field">
        <textarea name="message" required rows="4" placeholder="Message" class="wn-field"></textarea>
        <button class="btn-brand w-full">Send</button>
    </form>
</div>
@endsection
