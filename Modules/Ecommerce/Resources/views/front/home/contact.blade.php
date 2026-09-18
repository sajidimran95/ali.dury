@extends('ecommerce::front.layouts.store')
@section('title', 'Contact Sales | Greencloud Wholesale')
@section('content')
<div class="max-w-lg mx-auto px-4 py-12">
    <h1 class="gc-serif text-4xl mb-2">Contact Us</h1>
    <p class="text-sm text-black/55 mb-6">Sales desk: (414) 252-0586 ·  info@greencloudwholesale.com · Wauwatosa, WI 53213</p>
    <form method="post" action="{{ route('ecommerce.contact.send', absolute: false) }}" class="rounded-2xl border bg-white p-6 space-y-3">
        @csrf
        <input name="name" required placeholder="Name" class="w-full border rounded-xl px-4 py-2.5 text-sm">
        <input type="email" name="email" required placeholder="Email" class="w-full border rounded-xl px-4 py-2.5 text-sm">
        <textarea name="message" required rows="4" placeholder="Message" class="w-full border rounded-xl px-4 py-2.5 text-sm"></textarea>
        <button class="btn-brand w-full">Send</button>
    </form>
</div>
@endsection
