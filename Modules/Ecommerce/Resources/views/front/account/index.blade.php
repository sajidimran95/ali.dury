@extends('ecommerce::front.layouts.store')
@section('title', 'My Account')
@section('content')
<div class="max-w-5xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-semibold mb-6">Hello, {{ $contact->ecom_first_name ?: $contact->name }}</h1>
    <div class="grid md:grid-cols-4 gap-4 mb-10 text-sm">
        <a href="{{ route('ecommerce.account.orders') }}" class="rounded-2xl border bg-white p-4">Orders</a>
        <a href="{{ route('ecommerce.account.profile') }}" class="rounded-2xl border bg-white p-4">Profile</a>
        <a href="{{ route('ecommerce.account.addresses') }}" class="rounded-2xl border bg-white p-4">Addresses</a>
        <a href="{{ route('ecommerce.wishlist') }}" class="rounded-2xl border bg-white p-4">Wishlist</a>
        <a href="{{ route('ecommerce.account.password') }}" class="rounded-2xl border bg-white p-4">Password</a>
        <form action="{{ route('ecommerce.logout') }}" method="post">@csrf<button class="rounded-2xl border bg-white p-4 w-full text-left">Logout</button></form>
    </div>
    <h2 class="font-semibold mb-3">Recent orders</h2>
    <div class="space-y-2">
        @forelse($orders as $order)
            <div class="rounded-xl border bg-white p-4 flex justify-between text-sm">
                <span>{{ $order->invoice_no }}</span>
                <span>${{ number_format($order->final_total, 2) }}</span>
                <span>{{ \Carbon\Carbon::parse($order->transaction_date)->toFormattedDateString() }}</span>
            </div>
        @empty
            <p class="text-slate-500 text-sm">No orders yet.</p>
        @endforelse
    </div>
</div>
@endsection
