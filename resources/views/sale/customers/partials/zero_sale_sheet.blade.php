@php $zeroSaleReasons = $zeroSaleReasons ?? collect(); @endphp
<div id="saleZeroSaleSheet" class="sale-sheet" hidden>
    <div class="sale-sheet__panel">
        <div class="sale-sheet__head is-center">
            <div class="sale-sheet__title">Zero Sale</div>
            <button type="button" class="sale-sheet__close" onclick="saleCloseSheet('saleZeroSaleSheet')">×</button>
        </div>
        <p class="text-center text-sm text-slate-500 px-4 -mt-1 mb-1" id="saleZeroSaleWho">{{ $custName ?? '' }}</p>
        <form method="POST" id="saleZeroSaleForm" action="{{ isset($customer) ? route('sale.customers.zero_sale', $customer->id) : '#' }}" class="sale-sheet__body" style="padding:12px 16px 8px">
            @csrf
            <label class="text-xs font-bold text-slate-500 mb-1.5 block">Reason</label>
            <select name="reason_id" id="saleZeroSaleReason" class="sale-input mb-3" required>
                <option value="">Select reason</option>
                @foreach($zeroSaleReasons as $reason)
                    <option value="{{ $reason->id }}">{{ $reason->name }}</option>
                @endforeach
            </select>
            <label class="text-xs font-bold text-slate-500 mb-1.5 block">Comment</label>
            <textarea name="comment" id="saleZeroSaleComment" class="sale-input" rows="4" placeholder="Comment"></textarea>
            <button type="submit" class="sale-pay-btn" style="margin-top:12px">SAVE</button>
        </form>
        <div class="sale-sheet__foot">
            <button type="button" class="sale-more-cancel" onclick="saleCloseSheet('saleZeroSaleSheet')">CANCEL</button>
        </div>
    </div>
</div>
