@extends('layouts.app')
@section('title', $customer_group->name)

@section('css')
    <link rel="stylesheet" href="{{ asset('css/japs-customer-group.css?v=' . ($asset_v ?? '') . '&cg=3') }}">
@endsection

@section('content')
<div class="japs-dashboard-page cg-page">
    <p class="cg-crumb">
        <a href="{{ action([\App\Http\Controllers\CustomerGroupController::class, 'index']) }}">@lang('lang_v1.customer_groups')</a>
        / {{ $customer_group->name }}
    </p>

    <div class="cg-title-row">
        <div>
            <h1>{{ $customer_group->name }}</h1>
            <p class="cg-sub">@lang('lang_v1.tag_customers_help')</p>
            @can('customer.update')
                <form class="cg-discount-row" id="cg_discount_form">
                    <label for="cg_discount_amount">@lang('lang_v1.calculation_percentage')</label>
                    <input type="text" class="form-control input_number" id="cg_discount_amount" name="amount" value="{{ @num_format($customer_group->amount) }}" style="max-width:120px">
                    <span class="text-muted">%</span>
                    <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white">@lang('lang_v1.apply_all_products')</button>
                </form>
                <p class="cg-help">@lang('lang_v1.group_discount_help')</p>
            @endcan
        </div>
        <div class="cg-stats">
            <span>{{ $customers_count }} @lang('lang_v1.customers')</span>
            <span>{{ $priced_count }} @lang('lang_v1.group_product_prices')</span>
        </div>
    </div>

    <div class="cg-tabs">
        <button type="button" class="tw-dw-btn tw-dw-btn-sm cg-tab is-active" data-tab="customers">@lang('lang_v1.tag_customers')</button>
        <button type="button" class="tw-dw-btn tw-dw-btn-sm tw-dw-btn-outline cg-tab" data-tab="prices">@lang('lang_v1.group_product_prices')</button>
    </div>

    <section class="cg-panel" data-panel="customers">
        <div class="cg-card">
            <h2>@lang('lang_v1.tag_customers')</h2>
            @can('customer.update')
                <div class="cg-tag-row">
                    <select id="cg_customer_search" class="form-control" multiple style="width:100%"></select>
                    <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white" id="cg_tag_customers">@lang('lang_v1.tag_customers')</button>
                </div>
            @endcan
            <table class="table table-bordered" id="cg_members_table" style="width:100%">
                <thead>
                    <tr>
                        <th>@lang('contact.customer')</th>
                        <th>@lang('business.mobile')</th>
                        <th>@lang('messages.action')</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>

    <section class="cg-panel hide" data-panel="prices">
        <div class="cg-card">
            <h2>@lang('lang_v1.group_product_prices')</h2>
            <p class="cg-help">@lang('lang_v1.group_product_prices_help')</p>
            @can('customer.update')
                <div class="cg-price-actions">
                    <button type="button" class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-primary" id="cg_apply_all">@lang('lang_v1.apply_all_products')</button>
                    <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white" id="cg_save_prices">@lang('lang_v1.save_group_prices')</button>
                </div>
            @endcan
            <table class="table table-bordered" id="cg_products_table" style="width:100%">
                <thead>
                    <tr>
                        <th>@lang('sale.product')</th>
                        <th>SKU</th>
                        <th>@lang('lang_v1.default_selling_price')</th>
                        <th>@lang('lang_v1.group_price')</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>
</div>
@endsection

@section('javascript')
<script>
window.cgGroup = {
    membersUrl: @json(url('customer-group/'.$customer_group->id.'/members')),
    productsUrl: @json(url('customer-group/'.$customer_group->id.'/products')),
    applyAllUrl: @json(url('customer-group/'.$customer_group->id.'/products/apply-all')),
    discountUrl: @json(url('customer-group/'.$customer_group->id.'/discount')),
    customersSearchUrl: @json(action([\App\Http\Controllers\ContactController::class, 'getCustomers'])),
    applyAllConfirm: @json(__('lang_v1.apply_all_products_confirm')),
    noPrices: @json(__('lang_v1.no_group_prices_to_save'))
};
</script>
<script src="{{ asset('js/japs-customer-group.js?v=' . ($asset_v ?? '') . '&cg=3') }}"></script>
@endsection
