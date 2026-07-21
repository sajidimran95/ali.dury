@extends('ecommerce::front.layouts.store')
@section('title', 'Addresses')
@section('content')
<div class="max-w-3xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-semibold mb-6">Saved addresses</h1>
    <div class="space-y-3 mb-8">
        @foreach($addresses as $address)
            <div class="rounded-2xl border bg-white p-4 text-sm">
                <div class="font-medium">{{ $address->first_name }} {{ $address->last_name }} @if($address->is_default)<span class="text-accent text-xs">Default</span>@endif</div>
                <div class="text-slate-600">{{ $address->formatted() }}</div>
            </div>
        @endforeach
    </div>
    <form method="post" class="rounded-2xl border bg-white p-6 space-y-3">
        @csrf
        <h2 class="font-semibold">Add address</h2>
        <div class="grid md:grid-cols-2 gap-3">
            <input name="first_name" required placeholder="First name" class="border rounded-xl px-4 py-2.5 text-sm">
            <input name="last_name" required placeholder="Last name" class="border rounded-xl px-4 py-2.5 text-sm">
        </div>
        <input name="company" placeholder="Company" class="w-full border rounded-xl px-4 py-2.5 text-sm">
        <input name="address_line_1" required placeholder="Address line 1" class="w-full border rounded-xl px-4 py-2.5 text-sm">
        <input name="address_line_2" placeholder="Address line 2" class="w-full border rounded-xl px-4 py-2.5 text-sm">
        <div class="grid md:grid-cols-3 gap-3">
            <input name="city" required placeholder="City" class="border rounded-xl px-4 py-2.5 text-sm">
            <input name="state" required placeholder="State" class="border rounded-xl px-4 py-2.5 text-sm">
            <input name="zip_code" required placeholder="ZIP" class="border rounded-xl px-4 py-2.5 text-sm">
        </div>
        <input type="hidden" name="country" value="United States">
        <label class="text-sm flex items-center gap-2"><input type="checkbox" name="is_default" value="1"> Default shipping</label>
        <button class="rounded-full bg-ink text-white px-6 py-2.5 text-sm">Save address</button>
    </form>
</div>
@endsection
