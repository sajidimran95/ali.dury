@extends('layouts.app')
@section('title', __( 'sale.list_pos'))

@section('css')
    <link rel="stylesheet" href="{{ asset('css/product-list-metrics.css?v=' . ($asset_v ?? '') . '&plm=7') }}">
@endsection

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header product-list-header no-print">
    <div class="product-list-header__row">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black product-list-header__title">
            @lang('sale.pos_sale')
        </h1>
        <div class="product-list-header__actions">
            @can('sell.create')
                <a class="product-list-header__btn product-list-header__btn--primary"
                    href="{{ action([\App\Http\Controllers\SellPosController::class, 'create']) }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 5l0 14" />
                        <path d="M5 12l14 0" />
                    </svg>
                    @lang('messages.add')
                </a>
            @endcan
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content no-print">
    @if (!empty($pos_stats))
        @include('sale_pos.partials.list_metrics')
    @endif
    @component('components.filters', ['title' => __('report.filters')])
        @include('sell.partials.sell_list_filters')
    @endcomponent

    @component('components.widget', ['class' => 'box-primary', 'title' => __( 'sale.list_pos')])
        @can('sell.view')
            <input type="hidden" name="is_direct_sale" id="is_direct_sale" value="0">
            @include('sale_pos.partials.sales_table')
        @endcan
    @endcomponent
</section>
<!-- /.content -->
<div class="modal fade payment_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

<div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

<div class="modal fade register_details_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>
<div class="modal fade close_register_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

<!-- This will be printed -->
<section class="invoice print_section" id="receipt_section">
</section> 


@stop

@section('javascript')
@include('sale_pos.partials.sale_table_javascript')
<script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
<script type="text/javascript">
$(function () {
    if (typeof __currency_convert_recursively === 'function') {
        __currency_convert_recursively($('#pos_list_metrics'));
    }
});
</script>
@endsection