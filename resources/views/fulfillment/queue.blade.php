@extends('layouts.app')
@section('title', __('fulfillment.'.$stage))

@section('css')
    <link rel="stylesheet" href="{{ asset('css/japs-fulfillment.css?v=' . $asset_v) }}">
@endsection

@section('content')
<div class="japs-dashboard-page">
<div class="ff-page">
    <p class="ff-crumb">{{ __('fulfillment.breadcrumb_orders') }} / {{ __('fulfillment.breadcrumb_fulfillment') }} / {{ __('fulfillment.'.$stage) }}</p>
    <div class="ff-queue-head">
        <h1>{{ __('fulfillment.'.$stage) }}</h1>
        <div class="ff-source-filter">
            <span>{{ __('fulfillment.source') }}</span>
            <select id="ff_source" class="form-control">
                <option value="">{{ __('fulfillment.all_sources') }}</option>
                <option value="ecommerce" @selected(($source ?? '') === 'ecommerce')>{{ __('lang_v1.menu_online_orders') }}</option>
                <option value="sale_app" @selected(($source ?? '') === 'sale_app')>{{ __('lang_v1.menu_field_sales_orders') }}</option>
                <option value="customer_app" @selected(($source ?? '') === 'customer_app')>{{ __('home.source_customer_app') }}</option>
            </select>
            @if(($stage ?? '') === 'shipment')
                <span style="margin-left:10px;">{{ __('fulfillment.status') }}</span>
                <select id="ff_shipment_filter" class="form-control">
                    <option value="" @selected(($shipment_filter ?? '') === '')>{{ __('fulfillment.all_shipments') }}</option>
                    <option value="ready" @selected(($shipment_filter ?? '') === 'ready')>{{ __('fulfillment.ready_to_ship') }}</option>
                    <option value="shipped" @selected(($shipment_filter ?? '') === 'shipped')>{{ __('fulfillment.shipped') }}</option>
                </select>
            @endif
        </div>
    </div>
    <div class="ff-card">
        <table class="table" id="ff_queue_table" style="width:100%">
            <thead>
                <tr>
                    <th>@lang('sale.invoice_no')</th>
                    <th>{{ __('fulfillment.source') }}</th>
                    @if(($stage ?? '') === 'shipment')
                        <th>{{ __('fulfillment.status') }}</th>
                    @endif
                    <th>{{ __('fulfillment.customer') }}</th>
                    <th>@lang('sale.total')</th>
                    <th>@lang('messages.date')</th>
                    <th></th>
                </tr>
            </thead>
        </table>
    </div>
</div>
</div>
@endsection

@section('javascript')
<script>
$(function () {
    var isShipment = @json(($stage ?? '') === 'shipment');
    var columns = [
        { data: 'invoice_no', name: 'invoice_no' },
        { data: 'source_badge', name: 'order_source', orderable: false }
    ];
    if (isShipment) {
        columns.push({ data: 'stage_badge', name: 'fulfillment_stage', orderable: false });
    }
    columns = columns.concat([
        { data: 'customer', name: 'contacts.name', orderable: false },
        { data: 'final_total', name: 'final_total' },
        { data: 'transaction_date', name: 'transaction_date' },
        { data: 'action', orderable: false, searchable: false }
    ]);
    var dateCol = isShipment ? 5 : 4;

    var table = $('#ff_queue_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.location.pathname,
            data: function (d) {
                d.source = $('#ff_source').val();
                if (isShipment) {
                    d.shipment_filter = $('#ff_shipment_filter').val();
                }
            }
        },
        columns: columns,
        order: [[dateCol, 'desc']],
        fnDrawCallback: function () {
            if (typeof __currency_convert_recursively === 'function') {
                __currency_convert_recursively($('#ff_queue_table'));
            }
        }
    });
    $('#ff_source, #ff_shipment_filter').on('change', function () {
        table.ajax.reload();
    });
});
</script>
@endsection
