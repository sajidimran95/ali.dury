@extends('ecommerce::front.layouts.store')
@section('title', 'My Account')
@section('content')
@php
    $badge = match($approval) {
        'pending' => 'bg-amber-100 text-amber-900',
        'rejected' => 'bg-rose-100 text-rose-900',
        default => 'bg-emerald-100 text-emerald-900',
    };
@endphp
<div class="max-w-5xl mx-auto px-4 py-10">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <h1 class="text-3xl font-extrabold">Hello, {{ $contact->ecom_first_name ?: $contact->name }}</h1>
        <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $badge }}">
            {{ \Modules\Ecommerce\Support\WholesaleAccount::label($approval) }}
        </span>
    </div>

    @if($approval === 'pending')
        <div class="rounded-xl border border-amber-200 bg-amber-50 text-amber-950 text-sm p-4 mb-6">
            Wholesale approval pending. <a href="{{ route('ecommerce.account.licenses') }}" class="font-bold underline">Review / upload licenses</a> — checkout unlocks after admin approval.
        </div>
    @endif

    <div class="grid md:grid-cols-3 gap-4 mb-10 text-sm">
        <a href="{{ route('ecommerce.account.orders') }}" class="rounded-xl border border-line bg-white p-4 font-semibold hover:border-brand">Orders</a>
        <a href="{{ route('ecommerce.account.licenses') }}" class="rounded-xl border border-line bg-white p-4 font-semibold hover:border-brand">Licenses</a>
        <a href="{{ route('ecommerce.account.profile') }}" class="rounded-xl border border-line bg-white p-4 font-semibold hover:border-brand">Profile</a>
        <a href="{{ route('ecommerce.account.addresses') }}" class="rounded-xl border border-line bg-white p-4 font-semibold hover:border-brand">Addresses</a>
        <a href="{{ route('ecommerce.wishlist') }}" class="rounded-xl border border-line bg-white p-4 font-semibold hover:border-brand">Wishlist</a>
        <a href="{{ route('ecommerce.quick_order') }}" class="rounded-xl border border-line bg-white p-4 font-semibold hover:border-brand">Quick Order</a>
        <a href="{{ route('ecommerce.account.password') }}" class="rounded-xl border border-line bg-white p-4 font-semibold hover:border-brand">Password</a>
        <form action="{{ route('ecommerce.logout', absolute: false) }}" method="post" class="md:col-span-1">
            @csrf
            <button class="rounded-xl border border-line bg-white p-4 w-full text-left font-semibold hover:border-brand">Logout</button>
        </form>
    </div>

    <h2 class="font-extrabold mb-3">Recent orders</h2>
    <div class="space-y-2">
        @forelse($orders as $order)
            <div class="rounded-xl border border-line bg-white p-4 flex justify-between text-sm gap-3">
                <span class="font-semibold">{{ $order->invoice_no }}</span>
                <span class="tabular-nums">${{ number_format($order->final_total, 2) }}</span>
                <span class="text-black/45">{{ \Carbon\Carbon::parse($order->transaction_date)->toFormattedDateString() }}</span>
            </div>
        @empty
            <p class="text-black/45 text-sm">No orders yet.</p>
        @endforelse
    </div>
</div>
@endsection
