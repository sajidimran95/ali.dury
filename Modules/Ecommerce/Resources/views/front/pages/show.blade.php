@extends('ecommerce::front.layouts.store')
@section('title', $page['title'])
@section('content')
<div class="max-w-screen-md mx-auto px-4 py-10 md:py-16">
    <p class="text-xs font-bold uppercase tracking-widest mb-2" style="color:#4CAF79">{{ $store ?? ($ecom_settings->store_name ?? 'Store') }}</p>
    <h1 class="font-display text-3xl md:text-4xl font-bold mb-3">{{ $page['title'] }}</h1>
    <p class="text-[#555] mb-8">{{ $page['lede'] }}</p>
    <div class="bg-white border border-[#e0e0e0] rounded-2xl p-6 md:p-8 text-sm leading-relaxed whitespace-pre-line text-[#1a1a1a]">{{ $page['body'] }}</div>
    <div class="mt-8 flex flex-wrap gap-3">
        <a href="{{ route('ecommerce.register') }}" class="inline-flex px-6 py-3 rounded-lg font-bold text-sm text-white" style="background:#4CAF79">Apply for Wholesale Account</a>
        <a href="{{ route('ecommerce.contact') }}" class="inline-flex px-6 py-3 rounded-lg font-semibold text-sm border border-[#e0e0e0]">Contact Sales</a>
    </div>
</div>
@endsection
