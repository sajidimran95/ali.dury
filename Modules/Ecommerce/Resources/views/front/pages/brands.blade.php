@extends('ecommerce::front.layouts.store')
@section('title', 'Shop by Brand')
@section('content')
@php use Modules\Ecommerce\Support\Storefront; @endphp
<div class="max-w-screen-xl mx-auto px-4 py-10 md:py-14">
    <p class="text-xs font-bold uppercase tracking-widest mb-1" style="color:#4CAF79">Featured Brands</p>
    <h1 class="font-display text-3xl font-bold mb-2">Shop by Brand</h1>
    <p class="text-sm text-[#666] mb-8">Can't find a brand you carry? Request it below — we expand the catalog weekly.</p>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 mb-12">
        @forelse($brands as $brand)
            <a href="{{ route('ecommerce.brand.show', $brand->id) }}" class="rounded-xl border border-[#e8e8e8] bg-white p-4 text-center hover:border-[#4CAF79] hover:shadow-sm transition">
                <p class="font-semibold text-sm">{{ $brand->name }}</p>
                <p class="text-xs mt-1" style="color:#4CAF79">{{ (int) ($counts[$brand->id] ?? 0) }} products</p>
            </a>
        @empty
            <p class="col-span-full text-sm text-[#666]">No brands listed yet.</p>
        @endforelse
    </div>
    <div class="max-w-lg bg-white border border-[#e0e0e0] rounded-2xl p-6">
        <h2 class="font-display text-xl font-bold mb-4">Request a Brand</h2>
        <form method="post" action="{{ route('ecommerce.brand.request') }}" class="space-y-3">
            @csrf
            <input name="name" required placeholder="Your name" class="w-full border border-[#e0e0e0] rounded-lg px-3 py-2.5 text-sm">
            <input type="email" name="email" required placeholder="Business email" class="w-full border border-[#e0e0e0] rounded-lg px-3 py-2.5 text-sm">
            <textarea name="message" required rows="3" placeholder="Brand name and pack sizes you need" class="w-full border border-[#e0e0e0] rounded-lg px-3 py-2.5 text-sm"></textarea>
            <button class="btn-brand">Submit request</button>
        </form>
    </div>
</div>
@endsection
