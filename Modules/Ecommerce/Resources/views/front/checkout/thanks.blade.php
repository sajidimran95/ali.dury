@extends('ecommerce::front.layouts.store')
@section('title', 'Order confirmed')
@section('content')
<div class="max-w-2xl mx-auto px-4 py-16 text-center">
    <h1 class="text-3xl font-semibold mb-3">Thank you</h1>
    <p class="text-slate-600 mb-6">Your order <strong>{{ $order->invoice_no }}</strong> has been received.</p>
    <p class="text-sm text-slate-500 mb-8">Tracking token: <code class="bg-slate-100 px-2 py-1 rounded">{{ $meta->tracking_token }}</code></p>
    <div class="flex justify-center gap-3">
        <a href="{{ route('ecommerce.track', ['token' => $meta->tracking_token]) }}" class="btn-brand">Track order</a>
        <a href="{{ route('ecommerce.shop') }}" class="btn-outline">Continue shopping</a>
    </div>
</div>
@endsection
