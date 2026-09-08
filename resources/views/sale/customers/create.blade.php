@extends('sale.layout')
@section('title', 'Add customer')
@section('header', 'Add customer')
@section('content')
@if(!empty($canList))
<a href="{{ route('sale.customers') }}" class="inline-flex items-center gap-1.5 text-sm font-bold mb-3" style="color:#e11d48">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M15 18l-6-6 6-6"/></svg>
    Back
</a>
@endif

<form method="POST" action="{{ route('sale.customers.store') }}" class="sale-card space-y-3" id="saleAddCustomerForm">
    @csrf
    <div class="sale-sec-title">New customer</div>

    <div class="flex gap-4 text-sm font-semibold">
        <label class="inline-flex items-center gap-2">
            <input type="radio" name="contact_type_radio" value="individual" {{ old('contact_type_radio', 'individual') === 'individual' ? 'checked' : '' }}>
            Individual
        </label>
        <label class="inline-flex items-center gap-2">
            <input type="radio" name="contact_type_radio" value="business" {{ old('contact_type_radio') === 'business' ? 'checked' : '' }}>
            Business
        </label>
    </div>

    <div>
        <label class="text-xs font-bold text-slate-500 mb-1.5 block">First name *</label>
        <input type="text" name="first_name" value="{{ old('first_name') }}" required class="sale-input" placeholder="First name" autofocus>
    </div>
    <div>
        <label class="text-xs font-bold text-slate-500 mb-1.5 block">Last name</label>
        <input type="text" name="last_name" value="{{ old('last_name') }}" class="sale-input" placeholder="Last name">
    </div>
    <div id="saleBizNameWrap" class="{{ old('contact_type_radio') === 'business' ? '' : 'hidden' }}">
        <label class="text-xs font-bold text-slate-500 mb-1.5 block">Business name</label>
        <input type="text" name="supplier_business_name" value="{{ old('supplier_business_name') }}" class="sale-input" placeholder="Business name">
    </div>
    <div>
        <label class="text-xs font-bold text-slate-500 mb-1.5 block">Contact ID</label>
        <input type="text" name="contact_id" value="{{ old('contact_id') }}" class="sale-input" placeholder="Leave empty to auto generate">
    </div>
    <div>
        <label class="text-xs font-bold text-slate-500 mb-1.5 block">Customer group</label>
        <select name="customer_group_id" class="sale-input">
            @foreach($customer_groups as $id => $name)
                <option value="{{ $id }}" @selected((string) old('customer_group_id') === (string) $id)>{{ $name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-xs font-bold text-slate-500 mb-1.5 block">Mobile</label>
        <input type="text" name="mobile" value="{{ old('mobile') }}" class="sale-input" placeholder="Mobile">
    </div>
    <div>
        <label class="text-xs font-bold text-slate-500 mb-1.5 block">Alternate number</label>
        <input type="text" name="alternate_number" value="{{ old('alternate_number') }}" class="sale-input">
    </div>
    <div>
        <label class="text-xs font-bold text-slate-500 mb-1.5 block">Landline</label>
        <input type="text" name="landline" value="{{ old('landline') }}" class="sale-input">
    </div>
    <div>
        <label class="text-xs font-bold text-slate-500 mb-1.5 block">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" class="sale-input">
    </div>
    <div>
        <label class="text-xs font-bold text-slate-500 mb-1.5 block">Tax number</label>
        <input type="text" name="tax_number" value="{{ old('tax_number') }}" class="sale-input">
    </div>
    <div>
        <label class="text-xs font-bold text-slate-500 mb-1.5 block">Address line 1</label>
        <input type="text" name="address_line_1" value="{{ old('address_line_1') }}" class="sale-input">
    </div>
    <div>
        <label class="text-xs font-bold text-slate-500 mb-1.5 block">Address line 2</label>
        <input type="text" name="address_line_2" value="{{ old('address_line_2') }}" class="sale-input">
    </div>
    <div class="grid grid-cols-2 gap-2">
        <div>
            <label class="text-xs font-bold text-slate-500 mb-1.5 block">City</label>
            <input type="text" name="city" value="{{ old('city') }}" class="sale-input">
        </div>
        <div>
            <label class="text-xs font-bold text-slate-500 mb-1.5 block">State</label>
            <input type="text" name="state" value="{{ old('state') }}" class="sale-input">
        </div>
    </div>
    <div class="grid grid-cols-2 gap-2">
        <div>
            <label class="text-xs font-bold text-slate-500 mb-1.5 block">Zip</label>
            <input type="text" name="zip_code" value="{{ old('zip_code') }}" class="sale-input">
        </div>
        <div>
            <label class="text-xs font-bold text-slate-500 mb-1.5 block">Country</label>
            <input type="text" name="country" value="{{ old('country') }}" class="sale-input">
        </div>
    </div>
    <div>
        <label class="text-xs font-bold text-slate-500 mb-1.5 block">Shipping address</label>
        <textarea name="shipping_address" rows="2" class="sale-input">{{ old('shipping_address') }}</textarea>
    </div>
    <div>
        <label class="text-xs font-bold text-slate-500 mb-1.5 block">Credit limit</label>
        <input type="number" step="0.01" name="credit_limit" value="{{ old('credit_limit') }}" class="sale-input" placeholder="Blank = no limit">
    </div>

    <div class="rounded-xl border border-slate-200 p-3">
        <div class="text-xs font-bold text-slate-500 mb-2">Active Customer App</div>
        <label class="mr-4 text-sm"><input type="radio" name="allow_customer_app" value="1" {{ old('allow_customer_app') === '1' ? 'checked' : '' }}> Yes</label>
        <label class="text-sm"><input type="radio" name="allow_customer_app" value="0" {{ old('allow_customer_app', '0') === '0' ? 'checked' : '' }}> No</label>
        <div id="saleAppPassWrap" class="mt-2 {{ old('allow_customer_app') === '1' ? '' : 'hidden' }}">
            <label class="text-xs font-bold text-slate-500 mb-1.5 block">App password</label>
            <input type="text" name="customer_app_password" class="sale-input" placeholder="Blank = use mobile">
        </div>
    </div>

    <button type="submit" class="sale-order-build__checkout !w-full">Save customer</button>
</form>
@endsection

@push('scripts')
<script>
(function () {
    const wrap = document.getElementById('saleBizNameWrap');
    const pass = document.getElementById('saleAppPassWrap');
    document.querySelectorAll('input[name="contact_type_radio"]').forEach(r => {
    r.addEventListener('change', () => {
        const v = document.querySelector('input[name="contact_type_radio"]:checked');
        wrap.classList.toggle('hidden', !v || v.value !== 'business');
    });
    });
    document.querySelectorAll('input[name="allow_customer_app"]').forEach(r => {
        r.addEventListener('change', () => {
            if (r.checked) pass.classList.toggle('hidden', r.value !== '1');
        });
    });
})();
</script>
@endpush
