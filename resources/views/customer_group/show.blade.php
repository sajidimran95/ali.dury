@extends('layouts.app')
@section('title', $customer_group->name)

@section('css')
    <link rel="stylesheet" href="{{ asset('css/japs-customer-group.css?v=' . ($asset_v ?? '') . '&cg=12') }}">
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
        </div>
        <div class="cg-stats">
            <span><strong id="cg_customers_count">{{ $customers_count }}</strong> @lang('lang_v1.customers')</span>
            <span><strong id="cg_products_count">{{ $priced_count }}</strong> @lang('lang_v1.group_product_prices')</span>
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
            <p class="cg-help">@lang('lang_v1.group_price_scope_help')</p>

            @can('customer.update')
                <div class="cg-apply-bar">
                    <div class="cg-apply-pct">
                        <label for="cg_discount_amount">@lang('lang_v1.calculation_percentage')</label>
                        <input type="text" class="form-control input_number" id="cg_discount_amount" value="{{ @num_format($customer_group->amount) }}">
                        <span class="text-muted">%</span>
                    </div>
                    <div class="cg-scope-row">
                        <label class="cg-scope-opt is-active">
                            <input type="radio" name="cg_scope" value="all_inventory" checked>
                            @lang('lang_v1.scope_all_inventory')
                        </label>
                        <label class="cg-scope-opt">
                            <input type="radio" name="cg_scope" value="category">
                            @lang('lang_v1.scope_category')
                        </label>
                        <label class="cg-scope-opt">
                            <input type="radio" name="cg_scope" value="product">
                            @lang('lang_v1.scope_product')
                        </label>
                    </div>
                    <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white" id="cg_apply_btn">@lang('lang_v1.apply_group_percent')</button>
                </div>

                <div class="cg-scope-panel hide" data-scope-panel="category">
                    <div class="cg-filter-row">
                        <div class="form-group">
                            {!! Form::label('cg_category_id', __('category.category') . ':') !!}
                            {!! Form::select('cg_category_id', $categories, null, ['class' => 'form-control select2', 'id' => 'cg_category_id', 'style' => 'width:100%']) !!}
                        </div>
                        <div class="form-group" id="cg_sub_category_wrap" style="display:none;">
                            {!! Form::label('cg_sub_category_id', __('product.sub_category') . ':') !!}
                            <select class="form-control select2" id="cg_sub_category_id" style="width:100%">
                                <option value="">@lang('lang_v1.all')</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="cg-scope-panel hide" data-scope-panel="product">
                    <p class="cg-help">@lang('lang_v1.scope_product_pick_help')</p>
                    <div class="form-group">
                        <label for="cg_product_search">@lang('lang_v1.select_items_search')</label>
                        <select id="cg_product_search" class="form-control" multiple style="width:100%"></select>
                    </div>
                </div>
            @endcan

            <hr style="margin:18px 0;border-color:#e5e7eb;">
            <div class="cg-list-head">
                <div>
                    <h3>@lang('lang_v1.group_priced_products')</h3>
                    <div class="cg-list-filter" id="cg_list_filter">
                        <label class="cg-list-filter-opt is-active">
                            <input type="radio" name="cg_list_mode" value="tagged" checked>
                            @lang('lang_v1.show_already_tagged')
                        </label>
                        <label class="cg-list-filter-opt">
                            <input type="radio" name="cg_list_mode" value="all">
                            @lang('lang_v1.show_all_products')
                        </label>
                    </div>
                </div>
                @can('customer.update')
                    <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white" id="cg_save_prices">@lang('lang_v1.save_group_prices')</button>
                @endcan
            </div>

            @can('customer.update')
                <div class="cg-bulk-bar" id="cg_bulk_bar">
                    <div class="cg-bulk-count">
                        <i class="fa fa-check-square-o"></i>
                        <strong><span id="cg_bulk_selected_count">0</span></strong>
                        @lang('lang_v1.products_selected')
                    </div>
                    <p class="cg-bulk-help">@lang('lang_v1.group_bulk_pricing_help')</p>
                    <div class="cg-bulk-controls">
                        <div class="form-group">
                            <label for="cg_bulk_update_type">@lang('lang_v1.update_type')</label>
                            <select class="form-control" id="cg_bulk_update_type">
                                <option value="set">@lang('lang_v1.set_same_price')</option>
                                <option value="percentage">@lang('lang_v1.percentage')</option>
                                <option value="flat">@lang('lang_v1.flat_amount')</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="cg_bulk_update_value">@lang('lang_v1.value')</label>
                            <input type="number" step="0.01" class="form-control" id="cg_bulk_update_value" placeholder="e.g. 20">
                            <span class="help-block"><small id="cg_bulk_value_help">@lang('lang_v1.set_price_help_text')</small></span>
                        </div>
                        <div class="form-group cg-bulk-apply-wrap">
                            <label>&nbsp;</label>
                            <button type="button" class="tw-dw-btn tw-dw-btn-success tw-text-white btn-block" id="cg_bulk_apply">
                                <i class="fa fa-check"></i> @lang('lang_v1.apply_update')
                            </button>
                        </div>
                    </div>
                </div>
            @endcan
            <table class="table table-bordered" id="cg_products_table" style="width:100%">
                <thead>
                    <tr>
                        <th class="cg-pick-col" style="width:44px;text-align:center;">
                            <input type="checkbox" id="cg_select_all_products" title="@lang('lang_v1.select_all')">
                        </th>
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
    applyScopeUrl: @json(url('customer-group/'.$customer_group->id.'/products/apply-scope')),
    bulkUpdateUrl: @json(url('customer-group/'.$customer_group->id.'/products/bulk-update')),
    discountUrl: @json(url('customer-group/'.$customer_group->id.'/discount')),
    productSearchUrl: @json(url('customer-group/'.$customer_group->id.'/products/search')),
    productOverrideUrl: @json(url('customer-group/'.$customer_group->id.'/products/override')),
    customersSearchUrl: @json(url('customer-group/'.$customer_group->id.'/customers/search')),
    subCategoriesUrl: @json(action([\App\Http\Controllers\ProductController::class, 'getSubCategories'])),
    applyAllConfirm: @json(__('lang_v1.apply_all_products_confirm')),
    noPrices: @json(__('lang_v1.no_group_prices_to_save')),
    selectCustomers: @json(__('lang_v1.select_customers_to_tag')),
    selectCategory: @json(__('lang_v1.select_category_to_apply')),
    selectProducts: @json(__('lang_v1.select_products_to_apply')),
    pleaseSelectProducts: @json(__('lang_v1.please_select_products')),
    pleaseEnterValue: @json(__('lang_v1.please_enter_valid_value')),
    setPriceHelp: @json(__('lang_v1.set_price_help_text')),
    percentHelp: @json(__('lang_v1.percentage_help_text')),
    flatHelp: @json(__('lang_v1.flat_amount_help_text')),
    initialScope: @json('all_inventory'),
    scopeAll: @json(__('lang_v1.scope_all_inventory')),
    scopeCategory: @json(__('lang_v1.scope_category')),
    scopeProduct: @json(__('lang_v1.scope_product')),
    btnAll: @json(__('lang_v1.apply_group_percent')),
    btnCategory: @json(__('lang_v1.apply_to_category')),
    btnProducts: @json(__('lang_v1.apply_to_products'))
};
</script>
<script src="{{ asset('js/japs-customer-group.js?v=' . ($asset_v ?? '') . '&cg=18') }}"></script>
@endsection
