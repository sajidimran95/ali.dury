@php $zeroSaleReasons = $zeroSaleReasons ?? collect(); @endphp
<button type="button" class="sale-act-row" id="{{ $zeroSaleBtnId ?? 'saleCustZeroSale' }}">
    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"/><path d="M8 12h8"/></svg>
    Zero Sale
</button>
