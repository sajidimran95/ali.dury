@extends('ecommerce::front.layouts.store')
@section('title', 'Orders')
@section('content')
@component('ecommerce::front.account.partials.shell', ['current' => 'ecommerce.account.orders'])
    <div class="account-info-wrapper"><h4>Order history</h4></div>
    @forelse($orders as $order)
        @php $meta = $metas[$order->id] ?? null; @endphp
        <div class="order-row">
            <div>
                <strong>{{ $order->invoice_no }}</strong>
                <div style="color:#808080;font-size:12px">{{ $meta->fulfillment_status ?? 'pending' }} · {{ \Carbon\Carbon::parse($order->transaction_date)->toFormattedDateString() }}</div>
            </div>
            <div>${{ number_format($order->final_total, 2) }}</div>
            @if($meta)
                <a href="{{ route('ecommerce.track', ['token' => $meta->tracking_token]) }}" style="color:#dd772d">Track</a>
            @endif
        </div>
    @empty
        <p style="font-size:14px;color:#808080">No orders yet.</p>
    @endforelse
    <div style="margin-top:16px">{{ $orders->links() }}</div>
@endcomponent
@endsection
