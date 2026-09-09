@extends('sale.layout')
@section('title', $custName)
@section('header', 'Customer')
@section('header_left')
<a href="{{ route('sale.customers') }}" class="h-9 w-9 flex items-center justify-center text-inherit no-underline" aria-label="Back">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 18l-6-6 6-6"/></svg>
</a>
@endsection
@section('header_right')
<button type="button" class="h-9 w-9 border-0 bg-transparent" id="saleCustMoreBtn" aria-label="Options" onclick="return saleOpenSheet('saleCustMoreSheet', event)">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><circle cx="5" cy="12" r="1.7"/><circle cx="12" cy="12" r="1.7"/><circle cx="19" cy="12" r="1.7"/></svg>
</button>
@endsection
@section('content')
@include('sale.customers.partials.head')

<nav class="sale-cust-tabs">
    <a href="{{ route('sale.customers.show', [$customer->id, 'tab' => 'details']) }}" class="{{ $tab === 'details' ? 'is-on' : '' }}">Details</a>
    <a href="{{ route('sale.customers.show', [$customer->id, 'tab' => 'invoices']) }}" class="{{ $tab === 'invoices' ? 'is-on' : '' }}">Open Invoices</a>
    <a href="{{ route('sale.customers.show', [$customer->id, 'tab' => 'history']) }}" class="{{ $tab === 'history' ? 'is-on' : '' }}">History</a>
</nav>
<div class="sale-cust-panel">
    @if($tab === 'details')
        @php
            $rows = [
                'Name' => $customer->name,
                'Business name' => $customer->supplier_business_name,
                'First name' => $customer->first_name,
                'Last name' => $customer->last_name,
                'Account #' => $customer->contact_id,
                'Customer group' => $groupName ?? '',
                'Mobile' => $customer->mobile,
                'Alternate number' => $customer->alternate_number,
                'Landline' => $customer->landline,
                'Email' => $customer->email,
                'Tax number' => $customer->tax_number,
                'Address line 1' => $customer->address_line_1,
                'Address line 2' => $customer->address_line_2,
                'City' => $customer->city,
                'State' => $customer->state,
                'Zip' => $customer->zip_code,
                'Country' => $customer->country,
                'Shipping address' => $custAddr,
                'Credit limit' => $customer->credit_limit === null ? 'No limit' : '$'.number_format((float) $customer->credit_limit, 2),
                'Pay term' => trim(($customer->pay_term_number ? $customer->pay_term_number.' ' : '').($customer->pay_term_type ?? '')),
                'Open balance' => '$'.number_format((float) $openBalance, 2),
                'Status' => $customer->contact_status,
                'DOB' => $customer->dob,
                'Custom field 1' => $customer->custom_field1,
                'Custom field 2' => $customer->custom_field2,
                'Custom field 3' => $customer->custom_field3,
                'Custom field 4' => $customer->custom_field4,
                'Custom field 5' => $customer->custom_field5,
                'Custom field 6' => $customer->custom_field6,
                'Custom field 7' => $customer->custom_field7,
                'Custom field 8' => $customer->custom_field8,
                'Custom field 9' => $customer->custom_field9,
                'Custom field 10' => $customer->custom_field10,
            ];
        @endphp
        <div class="space-y-3">
            @foreach($rows as $label => $val)
                @php $text = is_string($val) || is_numeric($val) ? trim((string) $val) : ''; @endphp
                @if($text !== '')
                    <div>
                        <div class="text-[12px] font-bold text-slate-400">{{ $label }}</div>
                        <div class="text-[15px] text-slate-800">{{ $text }}</div>
                    </div>
                @endif
            @endforeach
        </div>
    @elseif($tab === 'history')
        <div class="text-center text-xs text-slate-400 mb-2">Results: {{ $history->count() }}</div>
        @forelse($history as $row)
            @include('sale.customers.partials.history_card', ['row' => $row])
        @empty
            <div class="sale-empty text-slate-500 text-sm">No history for this customer.</div>
        @endforelse
    @else
        @php
            $maxAbs = max(0.01, abs($aging['current']), abs($aging['d30']), abs($aging['d60']), abs($aging['d90']));
            $cols = [
                ['key' => 'current', 'label' => 'Current', 'color' => '#22c55e', 'soft' => '#bbf7d0'],
                ['key' => 'd30', 'label' => '30 Days', 'color' => '#eab308', 'soft' => '#fde68a'],
                ['key' => 'd60', 'label' => '60 Days', 'color' => '#f97316', 'soft' => '#fed7aa'],
                ['key' => 'd90', 'label' => '90 Days', 'color' => '#ef4444', 'soft' => '#fecaca'],
            ];
        @endphp
        <div class="sale-open-bal">
            <span>Open Balance</span>
            <b>${{ number_format((float) $openBalance, 2) }}</b>
        </div>
        <div class="sale-aging">
            @foreach($cols as $col)
                @php
                    $val = (float) $aging[$col['key']];
                    $pct = min(100, round(abs($val) / $maxAbs * 100));
                    $up = $val >= 0;
                @endphp
                <div class="sale-aging__col">
                    <div class="sale-aging__val" style="color: {{ $col['color'] }}">${{ number_format($val, 2) }}</div>
                    <div class="sale-aging__track">
                        <div class="sale-aging__bar" style="background: {{ $col['soft'] }}; height: {{ $pct }}%; {{ $up ? 'bottom:50%' : 'top:50%' }}"></div>
                        <span class="sale-aging__dot" style="background: {{ $col['color'] }}; {{ $up ? 'bottom: calc(50% + '.($pct * 0.9).'% - 6px)' : 'top: calc(50% + '.($pct * 0.9).'% - 6px)' }}"></span>
                    </div>
                    <div class="sale-aging__lbl">{{ $col['label'] }}</div>
                </div>
            @endforeach
        </div>
        <div class="sale-aging-cards">
            @foreach($cols as $col)
                @if((float) $aging[$col['key']] != 0.0)
                    <div class="sale-aging-card">
                        <em style="color: {{ $col['color'] }}">{{ $col['label'] }}</em>
                        <b>${{ number_format((float) $aging[$col['key']], 2) }}</b>
                    </div>
                @endif
            @endforeach
        </div>
        @if($openInvoices->isEmpty())
            <div class="text-center text-slate-400 text-sm mt-6">No open invoices.</div>
        @endif
    @endif
</div>

<div id="saleCustMoreSheet" class="sale-sheet" hidden>
    <div class="sale-sheet__panel">
        <div class="sale-sheet__head is-center">
            <div class="sale-sheet__title">Options</div>
            <button type="button" class="sale-sheet__close" onclick="saleCloseSheet('saleCustMoreSheet')">×</button>
        </div>
        <div class="sale-sheet__body !pt-1">
            <a class="sale-act-row" href="{{ route('sale.orders.create', ['contact_id' => $customer->id]) }}">
                <svg viewBox="0 0 24 24"><path d="M8 4h8v3H8z"/><path d="M6 7h12v13H6z"/></svg>
                Create Order
            </a>
            <a class="sale-act-row" href="{{ route('sale.customers.payment', $customer->id) }}">
                <svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="12" rx="2"/></svg>
                Create Payment
            </a>
            <a class="sale-act-row" href="{{ route('sale.customers.invoices', $customer->id) }}">
                <svg viewBox="0 0 24 24"><path d="M7 3h10v18H7z"/></svg>
                Open Invoices
            </a>
            <a class="sale-act-row" href="{{ route('sale.customers.history', $customer->id) }}">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"/><path d="M12 8v4l3 2"/></svg>
                History
            </a>
            @if($custAddr !== '')
            <a class="sale-act-row" href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($custAddr) }}" target="_blank" rel="noopener">
                <svg viewBox="0 0 24 24"><path d="M12 21s7-4.5 7-11a7 7 0 1 0-14 0c0 6.5 7 11 7 11z"/></svg>
                Directions
            </a>
            @endif
        </div>
        <div class="sale-sheet__foot">
            <button type="button" class="sale-more-cancel" onclick="saleCloseSheet('saleCustMoreSheet')">CANCEL</button>
        </div>
    </div>
</div>
@endsection
