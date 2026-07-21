@extends('ecommerce::front.layouts.store')
@section('title', 'Orders')
@section('content')
<div class="max-w-4xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-semibold mb-6">Order history</h1>
    <div class="space-y-3">
        @foreach($orders as $order)
            @php $meta = $metas[$order->id] ?? null; @endphp
            <div class="rounded-2xl border bg-white p-4 text-sm flex flex-wrap justify-between gap-2">
                <div>
                    <div class="font-medium">{{ $order->invoice_no }}</div>
                    <div class="text-slate-500">{{ $meta->fulfillment_status ?? 'pending' }}</div>
                </div>
                <div>${{ number_format($order->final_total, 2) }}</div>
                @if($meta)
                    <a class="text-accent" href="{{ route('ecommerce.track', ['token' => $meta->tracking_token]) }}">Track</a>
                @endif
            </div>
        @endforeach
    </div>
    <div class="mt-6">{{ $orders->links() }}</div>
</div>
@endsection
