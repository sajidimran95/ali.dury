@extends('ecommerce::front.layouts.store')
@section('title', 'Contact')
@section('content')
<div class="max-w-screen-lg mx-auto px-4 py-10 md:py-14 grid lg:grid-cols-2 gap-10">
    <div>
        <p class="text-xs font-bold uppercase tracking-widest mb-1" style="color:#4CAF79">Sales</p>
        <h1 class="font-display text-3xl font-bold mb-4">Send Us a Message</h1>
        <p class="text-sm text-[#555] mb-6">{{ $ecom_settings->seo_description ?: ($ecom_settings->store_name.' wholesale support.') }}</p>
        <ul class="text-sm space-y-2 text-[#444]">
            @if($ecom_settings->contact_email)
                <li><strong>Email:</strong> {{ $ecom_settings->contact_email }}</li>
            @endif
            @if($ecom_settings->contact_phone)
                <li><strong>Phone:</strong> {{ $ecom_settings->contact_phone }}</li>
            @endif
            <li>Use this form for catalog, account, and order questions.</li>
        </ul>
    </div>
    <form method="post" action="{{ route('ecommerce.contact.send') }}" class="bg-white border border-[#e0e0e0] rounded-2xl p-6 space-y-3">
        @csrf
        <input name="name" required placeholder="Name *" class="w-full border border-[#e0e0e0] rounded-lg px-3 py-2.5 text-sm" value="{{ old('name') }}">
        <input type="email" name="email" required placeholder="Email *" class="w-full border border-[#e0e0e0] rounded-lg px-3 py-2.5 text-sm" value="{{ old('email') }}">
        <input name="phone" placeholder="Phone" class="w-full border border-[#e0e0e0] rounded-lg px-3 py-2.5 text-sm" value="{{ old('phone') }}">
        <input name="subject" placeholder="Subject" class="w-full border border-[#e0e0e0] rounded-lg px-3 py-2.5 text-sm" value="{{ old('subject') }}">
        <textarea name="message" required rows="5" placeholder="Message *" class="w-full border border-[#e0e0e0] rounded-lg px-3 py-2.5 text-sm">{{ old('message') }}</textarea>
        <button class="w-full py-3 rounded-lg text-white font-bold text-sm" style="background:#4CAF79">Send Message</button>
    </form>
</div>
@endsection
