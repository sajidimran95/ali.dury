@extends('ecommerce::front.layouts.store')
@section('title', 'Shop by Brand')
@section('content')
<div class="max-w-screen-xl mx-auto px-4 py-10 md:py-14">
    <p class="text-xs font-bold uppercase tracking-widest mb-1 text-[#8b95b8]">Featured Brands</p>
    <h1 class="text-3xl font-extrabold mb-2">Shop by Brand</h1>
    <p class="text-sm text-[#8b95b8] mb-8">Can't find a brand you carry? Request it below.</p>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 mb-12">
        @forelse($brands as $brand)
            <a href="{{ route('ecommerce.brand.show', $brand->id) }}" class="wn-tile text-center">
                <p class="font-semibold text-sm">{{ $brand->name }}</p>
                <p class="text-xs mt-1 text-[#3ee07a]">{{ (int) ($counts[$brand->id] ?? 0) }} products</p>
            </a>
        @empty
            <p class="col-span-full text-sm text-[#8b95b8]">No brands listed yet.</p>
        @endforelse
    </div>
    <div class="max-w-lg wn-panel p-6">
        <h2 class="text-xl font-extrabold mb-4">Request a Brand</h2>
        <form method="post" action="{{ route('ecommerce.brand.request', absolute: false) }}" class="space-y-3">
            @csrf
            <input name="name" required placeholder="Your name" class="wn-field">
            <input type="email" name="email" required placeholder="Business email" class="wn-field">
            <textarea name="message" required rows="3" placeholder="Brand name and pack sizes you need" class="wn-field"></textarea>
            <button class="btn-brand">Submit request</button>
        </form>
    </div>
</div>
@endsection
