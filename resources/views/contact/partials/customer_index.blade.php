@php
    $stats = $customer_stats ?? [
        'total' => 0,
        'balance_due' => 0,
        'wholesale' => 0,
        'retail' => 0,
        'wholesale_label' => __('lang_v1.wholesale_accounts'),
        'retail_label' => __('lang_v1.retail_accounts'),
    ];
@endphp

<style>
    .customers-page .content-header { padding-bottom: 0; }
    .customers-page .cust-stat-card {
        position: relative;
        overflow: hidden;
        background: #fff;
        border: 1px solid #e7eaf0;
        border-radius: 18px;
        padding: 22px 20px 18px;
        box-shadow: 0 2px 10px rgba(15, 20, 37, 0.045);
        min-height: 112px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .customers-page .cust-stat-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        border-radius: 18px 18px 0 0;
        background: #4f8fce;
    }
    .customers-page .tw-grid > .cust-stat-card:nth-child(1)::before { background: #4f8fce; }
    .customers-page .tw-grid > .cust-stat-card:nth-child(2)::before { background: #d5aa35; }
    .customers-page .tw-grid > .cust-stat-card:nth-child(3)::before { background: #4db39e; }
    .customers-page .tw-grid > .cust-stat-card:nth-child(4)::before { background: #7c72c9; }
    .customers-page .cust-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 22px rgba(15, 20, 37, 0.07);
    }
    .customers-page .cust-stat-label {
        font-size: 14px;
        color: #7a8495;
        font-weight: 500;
        margin-bottom: 10px;
    }
    .customers-page .cust-stat-value {
        font-size: 26px;
        font-weight: 700;
        color: #151b2b;
        line-height: 1.15;
        letter-spacing: -0.03em;
    }
    .customers-page .cust-stat-value.is-due { color: #b78616; }
    .customers-page .cust-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        margin: 18px 0 14px;
    }
    .customers-page .cust-search {
        flex: 1 1 280px;
        position: relative;
    }
    .customers-page .cust-search input {
        width: 100%;
        height: 42px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0 14px 0 40px;
        background: #fff;
        box-shadow: none;
    }
    .customers-page .cust-search i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }
    .customers-page .cust-btn {
        height: 42px;
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: 10px;
        padding: 0 16px;
        font-weight: 600;
        color: #334155;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .customers-page .cust-btn:hover { background: #f8fafc; }
    /* Header lives outside .customers-page — keep button styles unscoped */
    a.cust-add-btn,
    .cust-add-btn {
        height: 42px;
        border: none !important;
        border-radius: 10px !important;
        padding: 0 18px !important;
        font-weight: 700 !important;
        font-size: 14px !important;
        line-height: 42px !important;
        color: #fff !important;
        background: #2563eb !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        gap: 6px;
        text-decoration: none !important;
        box-shadow: 0 1px 2px rgba(37, 99, 235, 0.25);
        white-space: nowrap;
        cursor: pointer;
    }
    a.cust-add-btn:hover,
    a.cust-add-btn:focus,
    .cust-add-btn:hover,
    .cust-add-btn:focus {
        background: #1d4ed8 !important;
        color: #fff !important;
        text-decoration: none !important;
    }
    .customers-page .cust-filters-panel {
        display: none;
        background: #fff;
        border: 1px solid #e8ecf1;
        border-radius: 14px;
        padding: 16px 16px 4px;
        margin-bottom: 14px;
    }
    .customers-page .cust-filters-panel.is-open { display: block; }
    .customers-page .cust-table-wrap {
        background: #fff;
        border: 1px solid #e8ecf1;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }
    .customers-page .cust-table-wrap .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .customers-page #contact_table {
        margin: 0 !important;
        width: 100% !important;
        min-width: 1400px;
        border: none !important;
    }
    .customers-page #contact_table thead th {
        background: #fff !important;
        border-bottom: 1px solid #eef2f7 !important;
        border-top: none !important;
        color: #64748b !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        text-transform: none;
        padding: 14px 16px !important;
        white-space: nowrap;
    }
    .customers-page #contact_table tbody td {
        border-top: 1px solid #f1f5f9 !important;
        padding: 14px 16px !important;
        vertical-align: middle !important;
        color: #0f172a;
        font-size: 14px;
    }
    .customers-page #contact_table tbody tr {
        cursor: pointer;
        transition: background 0.12s ease;
    }
    .customers-page #contact_table tbody tr:hover { background: #f8fafc !important; }
    .customers-page #contact_table tbody tr td:first-child,
    .customers-page #contact_table tbody tr .btn-group {
        cursor: default;
    }
    .customers-page .dataTables_wrapper .dataTables_filter { display: none; }
    .customers-page .dataTables_wrapper .dataTables_length,
    .customers-page .dataTables_wrapper .dataTables_info,
    .customers-page .dataTables_wrapper .dataTables_paginate {
        padding: 12px 16px;
    }
    .customers-page .cust-group-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.2;
        white-space: nowrap;
    }
    .customers-page .cust-group-wholesale { background: #ecfeff; color: #0e7490; }
    .customers-page .cust-group-retail { background: #eff6ff; color: #1d4ed8; }
    .customers-page .cust-group-default { background: #f1f5f9; color: #334155; }
    .customers-page .cust-group-none { background: #fff1f2; color: #be123c; }
    .customers-page .cust-due-owes { color: #b45309 !important; font-weight: 700; }
    .customers-page .cust-due-icon { margin-left: 4px; font-size: 12px; }
    .customers-page .cust-hint {
        padding: 0 16px 14px;
        font-size: 12px;
        color: #94a3b8;
    }
    .customers-page .cust-count {
        color: #64748b;
        font-size: 14px;
        margin-top: 4px;
    }
    .customers-page #contact_table tbody tr { cursor: pointer; }
</style>

<section class="content-header">
    <div class="tw-flex tw-flex-col md:tw-flex-row md:tw-items-start md:tw-justify-between tw-gap-4">
        <div>
            <h1 class="tw-text-2xl md:tw-text-3xl tw-font-bold tw-text-slate-900 tw-m-0">@lang('lang_v1.customers')</h1>
            <p class="cust-count tw-mb-0" id="customer_list_count">
                <span id="customer_shown_count">0</span> @lang('lang_v1.shown_of')
                <span id="customer_total_count">{{ (int) $stats['total'] }}</span> @lang('lang_v1.total_lowercase')
            </p>
        </div>
        @if (auth()->user()->can('customer.create') || auth()->user()->can('customer.view_own'))
            <a class="cust-add-btn btn-modal"
               data-href="{{ action([\App\Http\Controllers\ContactController::class, 'create'], ['type' => 'customer']) }}"
               data-container="#global_contact_modal">
                <span aria-hidden="true">+</span> @lang('lang_v1.add_customer')
            </a>
        @endif
    </div>
</section>

<section class="content customers-page">
    <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 xl:tw-grid-cols-4 tw-gap-3 tw-mb-1">
        <div class="cust-stat-card">
            <div class="cust-stat-label">@lang('lang_v1.total_customers')</div>
            <div class="cust-stat-value">{{ @num_format($stats['total']) }}</div>
        </div>
        <div class="cust-stat-card">
            <div class="cust-stat-label">@lang('lang_v1.total_balance_due')</div>
            <div class="cust-stat-value is-due">
                <span class="display_currency" data-currency_symbol="true" data-orig-value="{{ $stats['balance_due'] }}">{{ $stats['balance_due'] }}</span>
            </div>
        </div>
        <div class="cust-stat-card">
            <div class="cust-stat-label">{{ $stats['wholesale_label'] }}</div>
            <div class="cust-stat-value">{{ @num_format($stats['wholesale']) }}</div>
        </div>
        <div class="cust-stat-card">
            <div class="cust-stat-label">{{ $stats['retail_label'] }}</div>
            <div class="cust-stat-value">{{ @num_format($stats['retail']) }}</div>
        </div>
    </div>

    <div class="cust-toolbar">
        <div class="cust-search">
            <i class="fas fa-search" aria-hidden="true"></i>
            <input type="search" id="customer_quick_search" class="form-control" placeholder="@lang('lang_v1.search_customer_placeholder')" autocomplete="off">
        </div>
        <button type="button" class="cust-btn" id="customer_filters_toggle">
            <i class="fas fa-sliders-h" aria-hidden="true"></i> @lang('report.filters')
        </button>
        <button type="button" class="cust-btn" id="customer_export_btn">
            <i class="fas fa-download" aria-hidden="true"></i> @lang('lang_v1.export')
        </button>
        <button type="button" class="cust-btn" id="customer_toggle_columns" title="@lang('lang_v1.show_all_columns')">
            <i class="fas fa-columns" aria-hidden="true"></i> @lang('lang_v1.all_columns')
        </button>
    </div>

    <div class="cust-filters-panel" id="customer_filters_panel">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>
                        {!! Form::checkbox('has_sell_due', 1, false, ['class' => 'input-icheck', 'id' => 'has_sell_due']) !!}
                        <strong>@lang('lang_v1.sell_due')</strong>
                    </label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>
                        {!! Form::checkbox('has_sell_return', 1, false, ['class' => 'input-icheck', 'id' => 'has_sell_return']) !!}
                        <strong>@lang('lang_v1.sell_return')</strong>
                    </label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>
                        {!! Form::checkbox('has_advance_balance', 1, false, ['class' => 'input-icheck', 'id' => 'has_advance_balance']) !!}
                        <strong>@lang('lang_v1.advance_balance')</strong>
                    </label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>
                        {!! Form::checkbox('has_opening_balance', 1, false, ['class' => 'input-icheck', 'id' => 'has_opening_balance']) !!}
                        <strong>@lang('lang_v1.opening_balance')</strong>
                    </label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="has_no_sell_from">@lang('lang_v1.has_no_sell_from'):</label>
                    {!! Form::select(
                        'has_no_sell_from',
                        [
                            'one_month' => __('lang_v1.one_month'),
                            'three_months' => __('lang_v1.three_months'),
                            'six_months' => __('lang_v1.six_months'),
                            'one_year' => __('lang_v1.one_year'),
                        ],
                        null,
                        ['class' => 'form-control', 'id' => 'has_no_sell_from', 'placeholder' => __('messages.please_select')],
                    ) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="cg_filter">@lang('lang_v1.customer_group'):</label>
                    {!! Form::select('cg_filter', $customer_groups, null, ['class' => 'form-control', 'id' => 'cg_filter']) !!}
                </div>
            </div>
            @if (config('constants.enable_contact_assign') === true)
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('assigned_to', __('lang_v1.assigned_to') . ':') !!}
                        {!! Form::select('assigned_to', $users, null, ['class' => 'form-control select2', 'style' => 'width:100%']) !!}
                    </div>
                </div>
            @endif
            <div class="col-md-3">
                <div class="form-group">
                    <label for="status_filter">@lang('sale.status'):</label>
                    {!! Form::select(
                        'status_filter',
                        ['active' => __('business.is_active'), 'inactive' => __('lang_v1.inactive')],
                        null,
                        ['class' => 'form-control', 'id' => 'status_filter', 'placeholder' => __('lang_v1.none')],
                    ) !!}
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" value="customer" id="contact_type">

    @if (auth()->user()->can('customer.view') || auth()->user()->can('customer.view_own'))
        <div class="cust-table-wrap">
            <div class="table-responsive">
                <table class="table table-striped" id="contact_table">
                    <thead>
                        <tr>
                            <th class="not-export">@lang('messages.action')</th>
                            <th>@lang('lang_v1.contact_id')</th>
                            <th>@lang('business.business_name')</th>
                            <th>@lang('contact.name')</th>
                            <th>@lang('business.email')</th>
                            <th>@lang('contact.tax_no')</th>
                            <th>@lang('lang_v1.credit_limit')</th>
                            <th>@lang('contact.pay_term')</th>
                            <th>@lang('account.opening_balance')</th>
                            <th>@lang('lang_v1.advance_balance')</th>
                            <th>@lang('lang_v1.added_on')</th>
                            @if ($reward_enabled)
                                <th id="rp_col">{{ session('business.rp_name') }}</th>
                            @endif
                            <th>@lang('lang_v1.customer_group')</th>
                            <th>@lang('business.address')</th>
                            <th>@lang('contact.mobile')</th>
                            <th>@lang('contact.total_sale_due')</th>
                            <th>@lang('lang_v1.total_sell_return_due')</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr class="bg-gray font-17 text-center footer-total">
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td @if ($reward_enabled) colspan="9" @else colspan="8" @endif>
                                <strong>@lang('sale.total'):</strong>
                            </td>
                            <td class="footer_contact_due"></td>
                            <td class="footer_contact_return_due"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="cust-hint">@lang('lang_v1.customer_row_click_hint')</div>
        </div>
    @endif

    <div class="modal fade pay_contact_due_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
</section>
