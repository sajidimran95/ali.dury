@extends('ecommerce::front.layouts.store')
@section('title', 'Track order')
@section('content')
<div class="max-w-xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-semibold mb-6">Track your order</h1>
    <form method="get" class="flex gap-2 mb-8">
        <input name="token" value="{{ $token }}" placeholder="Tracking token" class="flex-1 border rounded-full px-4 py-2.5 text-sm">
        <button class="rounded-full bg-ink text-white px-5 text-sm">Track</button>
    </form>
    @if($meta && $order)
        <div class="rounded-2xl border bg-white p-6">
            <div class="font-medium mb-2">{{ $order->invoice_no }} · {{ ucfirst($meta->fulfillment_status) }}</div>
            <ol class="space-y-3 text-sm">
                @foreach(($meta->timeline ?? []) as $step)
                    <li class="flex justify-between border-b pb-2">
                        <span>{{ $step['label'] ?? $step['status'] }}</span>
                        <span class="text-slate-400">{{ $step['at'] ?? '' }}</span>
                    </li>
                @endforeach
            </ol>
        </div>
    @elseif($token)
        <p class="text-rose-600 text-sm">Order not found.</p>
    @endif
</div>
@endsection
