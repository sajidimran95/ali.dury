@extends('layouts.app')
@section('title', __('lang_v1.sell_payment_report'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">{{ __('lang_v1.sell_payment_report')}}</h1>
</section>

<!-- Main content -->
<section class="content no-print">
    <div class="row">
        <div class="col-md-12">
            @include('report.partials.catalog_style_filters', [
                'csfFormId' => 'sell_payment_report_form',
                'csfDateId' => 'spr_date_filter',
                'csfTitle' => __('lang_v1.sell_payment_report'),
                'csfHasSort' => false,
                'csfTableSelector' => '#sell_payment_report_table',
                'csfColumns' => ['', __('purchase.ref_no'), __('lang_v1.paid_on'), __('sale.amount'), __('contact.customer'), __('lang_v1.contact_id'), __('lang_v1.customer_group'), __('lang_v1.payment_method'), __('sale.sale'), __('messages.action')],
                'csfFilters' => [
                    'date' => ['label' => 'Date', 'type' => 'date', 'all' => 'Custom'],
                    'name' => [
                        'label' => 'Name',
                        'all' => 'All customers/jobs',
                        'html' => Form::select('customer_id', $customers, null, ['class' => 'form-control select2', 'id' => 'customer_id', 'style' => 'width:100%', 'placeholder' => 'All customers/jobs']),
                    ],
                    'group' => [
                        'label' => 'Customer Group',
                        'all' => 'All customer groups',
                        'html' => Form::select('customer_group_filter', $customer_groups, null, ['class' => 'form-control select2', 'id' => 'customer_group_filter', 'style' => 'width:100%']),
                    ],
                    'location' => [
                        'label' => 'Business Location',
                        'all' => 'All',
                        'html' => Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'id' => 'location_id', 'style' => 'width:100%', 'placeholder' => __('messages.all')]),
                    ],
                    'payment' => [
                        'label' => 'Payment Method',
                        'all' => 'All',
                        'html' => Form::select('payment_types', $payment_types, null, ['class' => 'form-control select2', 'id' => 'payment_types', 'style' => 'width:100%', 'placeholder' => __('messages.all')]),
                    ],
                ],
            ])
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary'])
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" 
                    id="sell_payment_report_table">
                        <thead>
                            <tr>
                                <th>&nbsp;</th>
                                <th>@lang('purchase.ref_no')</th>
                                <th>@lang('lang_v1.paid_on')</th>
                                <th>@lang('sale.amount')</th>
                                <th>@lang('contact.customer')</th>
                                <th>@lang('lang_v1.contact_id')</th>
                                <th>@lang('lang_v1.customer_group')</th>
                                <th>@lang('lang_v1.payment_method')</th>
                                <th>@lang('sale.sale')</th>
                                <th class="not-export">@lang('messages.action')</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr class="bg-gray font-17 footer-total text-center">
                                <td colspan="4"><strong>@lang('sale.total'):</strong></td>
                                <td><span class="display_currency" id="footer_total_amount" data-currency_symbol ="true"></span></td>
                                <td colspan="4"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endcomponent
        </div>
    </div>
</section>
<!-- /.content -->
<div class="modal fade view_register" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

@endsection

@section('javascript')
    <script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/catalog_style_filters.js?v=' . $asset_v) }}"></script>
@endsection