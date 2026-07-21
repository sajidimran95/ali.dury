@extends('ecommerce::front.layouts.store')
@section('title', 'Contact')
@section('content')
<div class="max-w-lg mx-auto px-4 py-10">
    <h1 class="text-2xl font-semibold mb-6">Contact us</h1>
    <form method="post" action="{{ route('ecommerce.contact.send') }}" class="rounded-2xl border bg-white p-6 space-y-3">
        @csrf
        <input name="name" required placeholder="Name" class="w-full border rounded-xl px-4 py-2.5 text-sm">
        <input type="email" name="email" required placeholder="Email" class="w-full border rounded-xl px-4 py-2.5 text-sm">
        <textarea name="message" required rows="4" placeholder="Message" class="w-full border rounded-xl px-4 py-2.5 text-sm"></textarea>
        <button class="w-full rounded-full bg-ink text-white py-2.5">Send</button>
    </form>
</div>
@endsection
