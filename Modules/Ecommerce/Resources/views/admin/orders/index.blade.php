@extends('layouts.app')
@section('title', __('ecommerce::lang.orders'))

@section('css')
    <link rel="stylesheet" href="{{ asset('css/product-list-metrics.css?v=' . ($asset_v ?? '') . '&plm=4') }}">
@endsection

@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('ecommerce::lang.orders')</h1>
</section>

<section class="content">
    @if (!empty($order_stats))
        @include('ecommerce::admin.orders.partials.list_metrics')
    @endif

    @component('components.widget', ['class' => 'box-primary', 'title' => __('ecommerce::lang.orders')])
        <table class="table table-bordered table-striped ajax_view" id="ecom_orders_table">
            <thead>
                <tr>
                    <th>@lang('sale.invoice_no')</th>
                    <th>@lang('sale.customer_name')</th>
                    <th>@lang('sale.total_amount')</th>
                    <th>@lang('sale.payment_status')</th>
                    <th>@lang('ecommerce::lang.fulfillment')</th>
                    <th>@lang('messages.date')</th>
                    <th>@lang('sale.invoice_no')</th>
                </tr>
            </thead>
        </table>
    @endcomponent
</section>
@endsection

@section('javascript')
<script type="text/javascript">
    $(document).ready(function () {
        var ecom_orders_table = $('#ecom_orders_table').DataTable({
            processing: true,
            serverSide: true,
            fixedHeader: false,
            aaSorting: [[5, 'desc']],
            ajax: "{{ route('ecommerce.admin.orders') }}",
            columns: [
                { data: 'invoice_no', name: 'transactions.invoice_no' },
                { data: 'customer_name', name: 'contacts.name' },
                { data: 'final_total', name: 'transactions.final_total' },
                { data: 'payment_status', name: 'transactions.payment_status' },
                { data: 'fulfillment_status', name: 'ecom_order_meta.fulfillment_status' },
                { data: 'transaction_date', name: 'transactions.transaction_date' },
                { data: 'converted_invoice', name: 'converted_invoice', orderable: false, searchable: false }
            ],
            fnDrawCallback: function () {
                __currency_convert_recursively($('#ecom_orders_table'));
            }
        });
        if (typeof __currency_convert_recursively === 'function') {
            __currency_convert_recursively($('#ecom_order_list_metrics'));
        }
    });
</script>
@endsection
