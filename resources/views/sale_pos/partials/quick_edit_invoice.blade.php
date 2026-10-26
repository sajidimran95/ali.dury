@extends('layouts.app')

@section('title', 'Quick edit')

@section('content')
@php
    $backUrl = !empty($transaction) && $transaction->type === 'sales_order'
        ? action([\App\Http\Controllers\SalesOrderController::class, 'index'])
        : action([\App\Http\Controllers\SellController::class, 'index']);
@endphp
<section class="content qe-page">
    <div class="qe-page-bar no-print">
        <h1>Quick edit
            @if(!empty($transaction))
                — {{ $transaction->invoice_no }}
            @endif
        </h1>
        <a href="{{ $backUrl }}" class="btn btn-default">Back</a>
    </div>

    @if(!empty($blocked))
        <div class="alert alert-warning">{{ $blocked }}</div>
    @else
        <form id="qe_invoice_form" method="POST" action="{{ route('pos.quick-edit.save', [$transaction->id]) }}"
            data-tax-percent="{{ $order_tax_percent }}"
            data-extra="{{ $extra_charges }}"
            data-location-id="{{ $transaction->location_id }}"
            data-paid="{{ $paid_amount }}"
            data-symbol="$"
            data-next="{{ count($receipt_details->lines ?? []) }}">
            @csrf
            <div class="qe-sheet-wrap">
                @include('sale_pos.receipts.us_letter', [
                    'receipt_details' => $receipt_details,
                    'quick_edit' => $quick_edit,
                ])
            </div>
            <div class="qe-page-actions no-print">
                <a href="{{ $backUrl }}" class="btn btn-default">Back</a>
                <button type="submit" class="btn btn-primary" id="qe_save">@lang('messages.save')</button>
            </div>
        </form>
    @endif
</section>
@endsection

@section('css')
<style>
    .qe-page { max-width: 860px; margin: 0 auto; overflow: visible; }
    .qe-page-bar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
    .qe-page-bar h1 { margin: 0; font-size: 22px; }
    .qe-sheet-wrap {
        background: #e6e6e6;
        padding: 12px;
        max-height: calc(100vh - 150px);
        overflow-x: hidden;
        overflow-y: auto;
    }
    .qe-page-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-top: 12px;
        position: sticky;
        bottom: 0;
        z-index: 4;
        padding: 8px 0;
        background: #f3f5f9;
    }
    .qe-page .qb-wrap.qe-on {
        display: flex !important;
        flex-direction: column;
        width: 100% !important;
        max-width: 100% !important;
        min-height: 11in !important;
        padding: 0.4in 0.35in 0.6in !important;
        overflow: visible;
        box-sizing: border-box;
    }
    .qe-page .qb-wrap.qe-on .qb-items-sheet {
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        min-height: 2.4in;
    }
    .qe-page .qb-wrap.qe-on > *,
    .qe-page .qb-wrap.qe-on .qb-items-sheet,
    .qe-page .qb-wrap.qe-on .qb-parties,
    .qe-page .qb-wrap.qe-on .qb-box {
        min-width: 0 !important;
        max-width: 100% !important;
    }
    .qe-page .qb-wrap.qe-on table {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        table-layout: fixed !important;
    }
    .qe-page .qb-wrap.qe-on th,
    .qe-page .qb-wrap.qe-on td {
        min-width: 0 !important;
        box-sizing: border-box;
    }
    .qe-page .qb-wrap.qe-on .qb-meta th,
    .qe-page .qb-wrap.qe-on .qb-meta td {
        height: auto !important;
        white-space: normal !important;
        overflow: visible;
        font-size: 11px !important;
        line-height: 1.25;
        padding: 4px 6px !important;
        text-align: center;
    }
    .qe-page .qb-wrap.qe-on .qb-items {
        font-size: 11px;
    }
    .qe-page .qb-wrap.qe-on .qb-items th,
    .qe-page .qb-wrap.qe-on .qb-items td {
        padding: 3px 6px !important;
        font-size: 11px !important;
    }
    .qe-page .qb-wrap.qe-on td.c { text-align: center; vertical-align: middle; }
    .qe-page .qb-wrap.qe-on .qe-stepper {
        display: inline-flex !important;
        align-items: stretch;
        justify-content: center;
        width: auto !important;
        max-width: 100%;
        height: 26px;
        gap: 0;
        border: 1px solid #111;
        background: #fff;
        overflow: hidden;
        vertical-align: middle;
    }
    .qe-page .qb-wrap.qe-on .qe-qty {
        width: 32px !important;
        max-width: 40px !important;
        min-width: 28px !important;
        height: 24px !important;
        border: 0 !important;
        border-left: 1px solid #111 !important;
        border-right: 1px solid #111 !important;
        padding: 0 !important;
        margin: 0 !important;
        text-align: center !important;
        font-size: 12px !important;
        line-height: 24px !important;
        background: #fff !important;
        box-shadow: none !important;
    }
    .qe-page .qb-wrap.qe-on .qe-stepper button {
        width: 24px !important;
        min-width: 24px !important;
        height: 24px !important;
        flex: 0 0 24px;
        border: 0 !important;
        background: #f4f4f4 !important;
        color: #111 !important;
        font-size: 16px !important;
        line-height: 22px !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .qe-page .qb-wrap.qe-on .qe-stepper button:hover { background: #e7e7e7 !important; }
    .qe-page .qb-wrap.qe-on .qe-remove {
        width: 18px !important;
        min-width: 18px !important;
        height: 18px !important;
        flex: 0 0 18px;
    }
    .qe-page .qb-wrap.qe-on .qb-items-fill {
        grid-template-columns: 18% 16% 36% 15% 15%;
        border-bottom: 1px solid #000;
    }
    .qe-page .qb-wrap.qe-on .qb-items-totals td {
        border-top: 1px solid #000 !important;
        border-bottom: 1px solid #000 !important;
    }
    .qe-page .qb-wrap.qe-on .qe-note { margin-top: 14px; }
    .qe-page .qb-wrap.qe-on .qe-unit,
    .qe-page .qb-wrap.qe-on #qe_shipping,
    .qe-page .qb-wrap.qe-on #qe_order_discount_amount,
    .qe-page .qb-wrap.qe-on #qe_order_discount_type {
        width: 100% !important;
        max-width: 100% !important;
        min-width: 0 !important;
        box-sizing: border-box !important;
    }
    .qe-page .qb-wrap.qe-on .qe-discount {
        display: flex;
        width: 100%;
        max-width: 100%;
        gap: 3px;
    }
    .qe-page .qb-wrap.qe-on .qe-dtype,
    .qe-page .qb-wrap.qe-on .qe-damount {
        width: auto !important;
        min-width: 0 !important;
        max-width: none !important;
        flex: 1 1 0;
        box-sizing: border-box !important;
    }
    .qe-page .qb-wrap.qe-on .qb-items-fill {
        width: 100%;
        max-width: 100%;
    }
</style>
@endsection
