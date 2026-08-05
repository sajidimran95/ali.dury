@extends('layouts.app')
@section('title', __('stock_adjustment.stock_adjustments'))

@section('css')
    <link rel="stylesheet" href="{{ asset('css/product-list-metrics.css?v=' . ($asset_v ?? '') . '&plm=4') }}">
@endsection

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header product-list-header">
    <div class="product-list-header__row">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black product-list-header__title">
            @lang('stock_adjustment.stock_adjustments')
        </h1>
        <div class="product-list-header__actions">
            @if(auth()->user()->can('stock_adjustment.create'))
                <a class="product-list-header__btn product-list-header__btn--primary"
                    href="{{ action([\App\Http\Controllers\StockAdjustmentController::class, 'create']) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 5l0 14" />
                        <path d="M5 12l14 0" />
                    </svg>
                    @lang('messages.add')
                </a>
            @endif
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
    @if (!empty($adjustment_stats))
        @include('stock_adjustment.partials.list_metrics')
    @endif

    @component('components.widget', ['class' => 'box-primary', 'title' => __('stock_adjustment.all_stock_adjustments')])
        <div class="table-responsive">
            <table class="table table-bordered table-striped ajax_view" id="stock_adjustment_table">
                <thead>
                    <tr>
                        <th class="not-export">@lang('messages.action')</th>
                        <th>@lang('messages.date')</th>
                        <th>@lang('purchase.ref_no')</th>
                        <th>@lang('business.location')</th>
                        <th>@lang('stock_adjustment.adjustment_type')</th>
                        <th>@lang('stock_adjustment.total_amount')</th>
                        <th>@lang('stock_adjustment.total_amount_recovered')</th>
                        <th>@lang('stock_adjustment.reason_for_stock_adjustment')</th>
                        <th>@lang('lang_v1.added_by')</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent

</section>
<!-- /.content -->
@stop
@section('javascript')
	<script src="{{ asset('js/stock_adjustment.js?v=' . $asset_v) }}"></script>
@endsection

@cannot('view_purchase_price')
    <style>
        .show_price_with_permission {
            display: none !important;
        }
    </style>
@endcannot
