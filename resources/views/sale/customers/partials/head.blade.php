@php
    $custName = $custName ?? ($customer->supplier_business_name ?: $customer->name);
    $custAddr = $custAddr ?? '';
@endphp
<div class="sale-cust-head">
    <h1>{{ $custName }}</h1>
    @if($customer->contact_id)
        <span class="sale-acct-pill">Account #: {{ $customer->contact_id }}</span>
    @endif
    @if(!empty($layout) && $layout === 'split')
        <div class="flex justify-between gap-3 mt-2 text-sm">
            <span></span>
            <div class="text-right">
                <div class="sale-cust-head__addr-label">Address</div>
                <div class="sale-cust-head__addr !mt-0">{{ $custAddr !== '' ? $custAddr : '—' }}</div>
            </div>
        </div>
    @elseif($custAddr !== '')
        <div class="sale-cust-head__addr">{{ $custAddr }}</div>
    @endif
</div>
