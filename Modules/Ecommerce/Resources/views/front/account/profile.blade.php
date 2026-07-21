@extends('ecommerce::front.layouts.store')
@section('title', 'Profile')
@section('content')
<div class="max-w-md mx-auto px-4 py-10">
    <h1 class="text-2xl font-semibold mb-6">Profile</h1>
    <form method="post" class="rounded-2xl border bg-white p-6 space-y-3">
        @csrf
        <input name="first_name" value="{{ old('first_name', $contact->ecom_first_name) }}" placeholder="First name" class="w-full border rounded-xl px-4 py-2.5 text-sm" required>
        <input name="last_name" value="{{ old('last_name', $contact->ecom_last_name) }}" placeholder="Last name" class="w-full border rounded-xl px-4 py-2.5 text-sm" required>
        <input name="email" type="email" value="{{ old('email', $contact->email) }}" class="w-full border rounded-xl px-4 py-2.5 text-sm" required>
        <input name="mobile" value="{{ old('mobile', $contact->mobile) }}" class="w-full border rounded-xl px-4 py-2.5 text-sm" required>
        <button class="w-full rounded-full bg-ink text-white py-2.5">Save</button>
    </form>
</div>
@endsection
