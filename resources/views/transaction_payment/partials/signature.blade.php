@php
    $sigFile = $payment->signature ?? null;
    if (empty($sigFile) && ! empty($payment->parent_id)) {
        $sigFile = optional(\App\TransactionPayment::find($payment->parent_id))->signature;
    }
    $sigUrl = ! empty($sigFile) ? asset('/uploads/signatures/'.$sigFile) : null;
@endphp
@if($sigUrl)
    <a href="{{ $sigUrl }}" target="_blank" rel="noopener" class="sale-pay-sig-link">
        <img src="{{ $sigUrl }}" alt="Signature" style="max-height:48px;max-width:120px;border:1px solid #e5e7eb;background:#fff;padding:2px;border-radius:4px">
    </a>
@else
    <span class="text-muted">—</span>
@endif
