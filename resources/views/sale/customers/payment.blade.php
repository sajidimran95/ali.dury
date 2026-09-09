@extends('sale.layout')
@section('title', 'Payment')
@section('header', 'Payment')
@section('header_left')
<a href="{{ route('sale.customers.show', $customer->id) }}" class="h-9 w-9 flex items-center justify-center text-inherit no-underline" aria-label="Back">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 18l-6-6 6-6"/></svg>
</a>
@endsection
@section('header_right')
<span class="h-9 w-9"></span>
@endsection
@section('content')
@php $printBase = route('sale.customers.invoice.print', [$customer->id, 0]); @endphp
<div class="sale-seg">
    <button type="button" class="{{ $tabPay === 'amount' ? 'is-on' : '' }}" id="salePayTabAmt">Amount</button>
    <button type="button" class="{{ $tabPay === 'payments' ? 'is-on' : '' }}" id="salePayTabPay">Payments</button>
</div>

<div id="salePayAmountView" class="{{ $tabPay === 'amount' ? '' : 'hidden' }}">
    @forelse($openInvoices as $row)
        <label class="sale-pay-check">
            <input type="checkbox" class="sale-pay-inv" value="{{ $row['id'] }}" data-bal="{{ $row['balance'] }}" data-print="{{ route('sale.customers.invoice.print', [$customer->id, $row['id']]) }}" {{ ($preselect && (int) $preselect === (int) $row['id']) || ! $preselect ? 'checked' : '' }}>
            <dl class="sale-pay-dl">
                <dt>Transaction #:</dt><dd>{{ $row['invoice_no'] }}</dd>
                <dt>Due:</dt><dd>{{ $row['due_date'] }}</dd>
                <dt>Amount:</dt><dd>${{ number_format((float) $row['amount'], 2) }}</dd>
                <dt>Balance:</dt><dd>${{ number_format((float) $row['balance'], 2) }}</dd>
            </dl>
        </label>
    @empty
        <div class="sale-card sale-empty text-sm text-slate-500">No open invoices to pay.</div>
    @endforelse
    <button type="button" class="sale-pay-btn" id="saleGoPayments">PAYMENT</button>
</div>

<form method="POST" action="{{ route('sale.customers.payment.store', $customer->id) }}" id="salePayPaymentsView" class="{{ $tabPay === 'payments' ? '' : 'hidden' }}">
    @csrf
    <input type="hidden" name="paid_on" value="{{ now()->toDateTimeString() }}">
    <input type="hidden" name="pay_mode" id="salePayMode" value="pay">
    <input type="hidden" name="note" id="salePayNoteField" value="">
    <input type="hidden" name="signature" id="salePaySigField" value="">
    <div class="sale-pay-sum">
        <div class="sale-pay-sum-row"><span class="red">Total Amount</span><b id="salePaySumAmt">$0.00</b></div>
        <div class="sale-pay-sum-row"><span class="red">Total Payment</span><b id="salePaySumPay">$0.00</b></div>
        <div class="sale-pay-sum-row"><span>Balance</span><span class="text-slate-500" id="salePaySumBal">$0.00</span></div>
    </div>
    <div class="sale-pay-modes">
        <button type="button" class="sale-pay-chip is-on" data-mode="pay">PAY</button>
        <button type="button" class="sale-pay-chip" data-mode="credit">CREDIT</button>
    </div>
    <div class="grid grid-cols-2 gap-2 mb-2">
        <input type="text" name="amount" id="salePayAmount" class="sale-input" inputmode="decimal" value="0.00">
        <select name="method" class="sale-input">
            @foreach($paymentTypes as $key => $label)
                <option value="{{ $key }}" {{ $key === 'cash' ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <input type="text" id="salePayRef" class="sale-input mb-3" maxlength="21" placeholder="Ref no. 21 characters max.">
    <button type="submit" class="sale-pay-btn" style="border-radius:999px;font-size:16px">SEND</button>
</form>

<div class="sale-pay-tools">
    <button type="button" id="salePayCommentsBtn">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
        Comments
    </button>
    <button type="button" id="salePaySigBtn">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19c2-6 8-10 16-10"/></svg>
        Signature
    </button>
    <button type="button" id="salePayMoreBtn">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><circle cx="5" cy="12" r="1.6"/><circle cx="12" cy="12" r="1.6"/><circle cx="19" cy="12" r="1.6"/></svg>
        More
    </button>
</div>

<div id="salePayCommentSheet" class="sale-sheet" hidden>
    <div class="sale-sheet__panel">
        <div class="sale-sheet__head is-center">
            <div class="sale-sheet__title">Comments</div>
            <button type="button" class="sale-sheet__close" onclick="saleCloseSheet('salePayCommentSheet')">×</button>
        </div>
        <div class="sale-sheet__body" style="padding:12px 16px">
            <textarea id="salePayCommentText" class="sale-input" rows="5" placeholder="Add a comment"></textarea>
        </div>
        <div class="sale-sheet__foot">
            <button type="button" class="sale-pay-btn" style="margin:0" id="salePayCommentSave">SAVE</button>
        </div>
    </div>
</div>
<div id="salePaySigSheet" class="sale-sheet" hidden>
    <div class="sale-sheet__panel">
        <div class="sale-sheet__head is-center">
            <div class="sale-sheet__title">Signature</div>
            <button type="button" class="sale-sheet__close" onclick="saleCloseSheet('salePaySigSheet')">×</button>
        </div>
        <div class="sale-sheet__body" style="padding:12px 16px">
            <canvas id="saleSigCanvas" class="sale-sig-pad"></canvas>
        </div>
        <div class="sale-sheet__foot" style="display:flex;gap:8px">
            <button type="button" class="sale-more-cancel" id="saleSigClear">CLEAR</button>
            <button type="button" class="sale-pay-btn" style="margin:0" id="saleSigSave">SAVE</button>
        </div>
    </div>
</div>
<div id="salePayMoreSheet" class="sale-sheet" hidden>
    <div class="sale-sheet__panel">
        <div class="sale-sheet__head is-center">
            <div class="sale-sheet__title">Options</div>
            <button type="button" class="sale-sheet__close" onclick="saleCloseSheet('salePayMoreSheet')">×</button>
        </div>
        <div class="sale-sheet__body !pt-1">
            <button type="button" class="sale-act-row" id="salePayPrintBtn">
                <svg viewBox="0 0 24 24"><path d="M6 9V3h12v6"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v7H6z"/></svg>
                Print
            </button>
            <a class="sale-act-row" href="{{ route('sale.customers.show', [$customer->id, 'tab' => 'details']) }}">
                <svg viewBox="0 0 24 24"><path d="M8 6h8M8 10h8M8 14h5"/><path d="M5 4h14v16H5z"/></svg>
                Details
            </a>
        </div>
        <div class="sale-sheet__foot">
            <button type="button" class="sale-more-cancel" onclick="saleCloseSheet('salePayMoreSheet')">CANCEL</button>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
(function () {
    function money(n) { return '$' + (Number(n) || 0).toFixed(2); }
    function selectedBoxes() { return Array.from(document.querySelectorAll('.sale-pay-inv:checked')); }
    function selectedTotal() {
        return selectedBoxes().reduce(function (t, el) { return t + (parseFloat(el.getAttribute('data-bal') || '0') || 0); }, 0);
    }
    function sync() {
        var t = selectedTotal();
        var sumAmt = document.getElementById('salePaySumAmt');
        var sumPay = document.getElementById('salePaySumPay');
        var sumBal = document.getElementById('salePaySumBal');
        var inp = document.getElementById('salePayAmount');
        if (sumAmt) sumAmt.textContent = money(t);
        var pay = parseFloat((inp && inp.value) || t) || 0;
        if (sumPay) sumPay.textContent = money(pay);
        if (sumBal) sumBal.textContent = money(t - pay);
        if (inp && !inp.dataset.touched) inp.value = t.toFixed(2);
    }
    document.querySelectorAll('.sale-pay-inv').forEach(function (el) { el.addEventListener('change', sync); });
    var inp = document.getElementById('salePayAmount');
    if (inp) inp.addEventListener('input', function () { inp.dataset.touched = '1'; sync(); });
    document.getElementById('salePayTabAmt').addEventListener('click', function () {
        document.getElementById('salePayAmountView').classList.remove('hidden');
        document.getElementById('salePayPaymentsView').classList.add('hidden');
        this.classList.add('is-on');
        document.getElementById('salePayTabPay').classList.remove('is-on');
    });
    function goPay() {
        document.getElementById('salePayAmountView').classList.add('hidden');
        document.getElementById('salePayPaymentsView').classList.remove('hidden');
        document.getElementById('salePayTabPay').classList.add('is-on');
        document.getElementById('salePayTabAmt').classList.remove('is-on');
        sync();
    }
    document.getElementById('salePayTabPay').addEventListener('click', goPay);
    document.getElementById('saleGoPayments').addEventListener('click', goPay);
    document.querySelectorAll('.sale-pay-chip').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.sale-pay-chip').forEach(function (b) { b.classList.remove('is-on'); });
            btn.classList.add('is-on');
            document.getElementById('salePayMode').value = btn.getAttribute('data-mode');
        });
    });
    document.getElementById('salePayPaymentsView').addEventListener('submit', function () {
        var ref = document.getElementById('salePayRef').value || '';
        var comment = document.getElementById('salePayCommentText').value || '';
        document.getElementById('salePayNoteField').value = [ref, comment].filter(Boolean).join(' | ');
    });
    document.getElementById('salePayCommentsBtn').addEventListener('click', function () { saleOpenSheet('salePayCommentSheet'); });
    document.getElementById('salePayCommentSave').addEventListener('click', function () { saleCloseSheet('salePayCommentSheet'); });
    document.getElementById('salePayMoreBtn').addEventListener('click', function () { saleOpenSheet('salePayMoreSheet'); });
    document.getElementById('salePayPrintBtn').addEventListener('click', function () {
        var boxes = selectedBoxes();
        if (!boxes.length) boxes = Array.from(document.querySelectorAll('.sale-pay-inv'));
        if (!boxes.length) { alert('No invoice to print'); return; }
        boxes.forEach(function (el) {
            var url = el.getAttribute('data-print');
            if (url) window.open(url, '_blank');
        });
        saleCloseSheet('salePayMoreSheet');
    });

    var canvas = document.getElementById('saleSigCanvas');
    var drawing = false;
    function pos(e) {
        var r = canvas.getBoundingClientRect();
        var t = e.touches ? e.touches[0] : e;
        return { x: t.clientX - r.left, y: t.clientY - r.top };
    }
    function sizeCanvas() {
        var r = canvas.getBoundingClientRect();
        canvas.width = r.width * 2;
        canvas.height = r.height * 2;
        var ctx = canvas.getContext('2d');
        ctx.scale(2, 2);
        ctx.strokeStyle = '#111';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
    }
    document.getElementById('salePaySigBtn').addEventListener('click', function () {
        saleOpenSheet('salePaySigSheet');
        setTimeout(sizeCanvas, 50);
    });
    canvas.addEventListener('pointerdown', function (e) { drawing = true; var p = pos(e); canvas.getContext('2d').beginPath(); canvas.getContext('2d').moveTo(p.x, p.y); });
    canvas.addEventListener('pointermove', function (e) { if (!drawing) return; var p = pos(e); canvas.getContext('2d').lineTo(p.x, p.y); canvas.getContext('2d').stroke(); });
    canvas.addEventListener('pointerup', function () { drawing = false; });
    document.getElementById('saleSigClear').addEventListener('click', function () { canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height); });
    document.getElementById('saleSigSave').addEventListener('click', function () {
        document.getElementById('salePaySigField').value = canvas.toDataURL('image/png');
        saleCloseSheet('salePaySigSheet');
    });
    sync();
})();
</script>
@endpush
