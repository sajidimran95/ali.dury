@extends('sale.layout')
@section('title', 'Open Invoices')
@section('header', 'Open Invoices')
@section('header_left')
<a href="{{ route('sale.customers.show', $customer->id) }}" class="h-9 w-9 flex items-center justify-center text-inherit no-underline" aria-label="Back">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 18l-6-6 6-6"/></svg>
</a>
@endsection
@section('header_right')
<button type="button" class="h-9 w-9 border-0 bg-transparent" onclick="document.getElementById('saleInvSearch').classList.toggle('hidden')" aria-label="Search">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
</button>
@endsection
@section('content')
<form method="GET" action="{{ route('sale.customers.invoices', $customer->id) }}" id="saleInvSearch" class="{{ ($q ?? '') !== '' ? '' : 'hidden' }} mb-2">
    <input type="search" name="q" value="{{ $q ?? '' }}" class="sale-input" placeholder="Search">
</form>
<div class="text-center text-xs text-slate-400 mb-1">Results: {{ $openInvoices->count() }}</div>
<div class="sale-inv-cols">
    <span>Company/Amount/Account #</span>
    <span>Transaction #/Date/Status</span>
</div>
@forelse($openInvoices as $row)
    <button type="button" class="sale-inv-card w-full text-left" data-inv="{{ $row['id'] }}" onclick="saleOpenInvOptions({{ $row['id'] }})">
        <div class="sale-inv-card__row">
            <div>
                <h3>{{ $row['name'] }}</h3>
                <div class="meta">Amount: ${{ number_format((float) $row['amount'], 2) }}</div>
                <div class="mt-2"><span class="sale-acct-pill">Account #: {{ $row['acct'] }}</span></div>
            </div>
            <div class="text-right">
                <div class="font-extrabold">#: {{ $row['invoice_no'] }} <span style="color:#e53935">›</span></div>
                <div class="meta">{{ $row['date'] }}</div>
                <div class="mt-2"><span class="sale-status-sent">{{ $row['status'] }}</span></div>
            </div>
        </div>
    </button>
@empty
    <div class="sale-card sale-empty text-slate-500 text-sm">No open invoices.</div>
@endforelse
<div class="sale-inv-total">
    <span>TOTAL</span>
    <span>${{ number_format((float) $openInvoices->sum('balance'), 2) }}</span>
</div>

<div id="saleInvOptSheet" class="sale-sheet" hidden>
    <div class="sale-sheet__panel">
        <div class="sale-sheet__head is-center">
            <div class="sale-sheet__title">Options</div>
            <button type="button" class="sale-sheet__close" onclick="saleCloseSheet('saleInvOptSheet')">×</button>
        </div>
        <div class="sale-sheet__body !pt-1">
            <button type="button" class="sale-act-row" id="saleInvShare">
                <svg viewBox="0 0 24 24"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.6 13.5l6.8 3.9M15.4 6.6L8.6 10.5"/></svg>
                Share
            </button>
            <a class="sale-act-row" id="saleInvPrint" href="#" target="_blank" rel="noopener">
                <svg viewBox="0 0 24 24"><path d="M6 9V3h12v6"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v7H6z"/></svg>
                Print
            </a>
            <a class="sale-act-row" id="saleInvPay" href="#">
                <svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="12" rx="2"/><path d="M6 11h4"/></svg>
                Create Payment
            </a>
            <a class="sale-act-row" href="{{ route('sale.customers.show', $customer->id) }}">
                <svg viewBox="0 0 24 24"><path d="M8 6h8M8 10h8M8 14h5"/><path d="M5 4h14v16H5z"/></svg>
                Details
            </a>
        </div>
        <div class="sale-sheet__foot">
            <button type="button" class="sale-more-cancel" onclick="saleCloseSheet('saleInvOptSheet')">CANCEL</button>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
var saleInvPayBase = @json(route('sale.customers.payment', $customer->id));
var saleInvShowBase = @json(url('/sale/orders'));
function saleOpenInvOptions(id) {
    document.getElementById('saleInvPay').href = saleInvPayBase + '?invoice_id=' + encodeURIComponent(id);
    document.getElementById('saleInvPrint').href = '/sale/customers/' + {{ (int) $customer->id }} + '/invoices/' + id + '/print';
    document.getElementById('saleInvShare').onclick = function () {
        var url = window.location.origin + '/sale/customers/' + {{ (int) $customer->id }} + '/invoices/' + id + '/print';
        if (navigator.share) navigator.share({ url: url });
        else { prompt('Share link', url); }
        saleCloseSheet('saleInvOptSheet');
    };
    saleOpenSheet('saleInvOptSheet');
}
</script>
@endpush
