<!-- default value -->
@php
    $go_back_url = action([\App\Http\Controllers\SellPosController::class, 'index']);
    $transaction_sub_type = '';
    $view_suspended_sell_url = action([\App\Http\Controllers\SellController::class, 'index']) . '?suspended=1';
    $pos_redirect_url = action([\App\Http\Controllers\SellPosController::class, 'create']);
@endphp

@if (!empty($pos_module_data))
    @foreach ($pos_module_data as $key => $value)
        @php
            if (!empty($value['go_back_url'])) {
                $go_back_url = $value['go_back_url'];
            }

            if (!empty($value['transaction_sub_type'])) {
                $transaction_sub_type = $value['transaction_sub_type'];
                $view_suspended_sell_url .= '&transaction_sub_type=' . $transaction_sub_type;
                $pos_redirect_url .= '?sub_type=' . $transaction_sub_type;
            }
        @endphp
    @endforeach
@endif
<input type="hidden" name="transaction_sub_type" id="transaction_sub_type" value="{{ $transaction_sub_type }}">
@inject('request', 'Illuminate\Http\Request')
<div class="col-md-12 no-print pos-header zoho-topbar">
    <input type="hidden" id="pos_redirect_url" value="{{ $pos_redirect_url }}">

    <div class="pos-ls-topbar">
        <div class="pos-ls-topbar-left">
            <p class="pos-brand-mark">{{ session('business.name') ?? config('app.name') }}</p>
            <div class="pos-ls-location tw-hidden md:tw-flex">
                @if (empty($transaction->location_id))
                    @if (count($business_locations) > 1)
                        {!! Form::select(
                            'select_location_id',
                            $business_locations,
                            $default_location->id ?? null,
                            ['class' => 'form-control input-sm', 'id' => 'select_location_id', 'required', 'autofocus'],
                            $bl_attributes,
                        ) !!}
                    @else
                        <span class="pos-ls-location-name">{{ $default_location->name ?? '' }}</span>
                    @endif
                @else
                    <span class="pos-ls-location-name">{{ $transaction->location->name }}</span>
                @endif
            </div>
            <span class="curr_datetime pos-ls-datetime tw-hidden md:tw-inline">{{ @format_datetime('now') }}</span>
            <button type="button" class="pos-ls-icon-btn tw-hidden md:tw-inline-flex pos-ls-help-btn"
                data-toggle="popover" data-placement="bottom" data-trigger="hover" data-html="true"
                data-content="@include('sale_pos.partials.keyboard_shortcuts_details')"
                title="Help">
                <i class="fas fa-keyboard"></i>
            </button>
        </div>

        <div class="pos-ls-topbar-center tw-hidden lg:tw-flex">
            <button type="button" class="pos-ls-global-search" id="pos_ls_focus_product_search" title="Search products">
                <i class="fas fa-search"></i>
                <span>Search</span>
                <kbd>Ctrl+/</kbd>
            </button>
        </div>

        {{-- Previous toolbar buttons — same IDs / workflow, dark layout --}}
        <div class="pos-ls-topbar-right" id="pos_header_more_options">
            <a href="{{ $go_back_url }}" title="{{ __('lang_v1.go_back') }}" class="pos-tb-btn">
                <i class="fas fa-chevron-left" style="color:#009EE4;"></i>
            </a>
            <span class="pos-tb-divider"></span>

            <button type="button" class="pos-tb-btn popover-default" id="return_sale"
                title="@lang('lang_v1.sell_return')" data-toggle="popover" data-trigger="click"
                data-content='<div class="m-8"><input type="text" class="form-control" placeholder="@lang('sale.invoice_no')" id="send_for_sell_return_invoice_no"></div><div class="w-100 text-center"><button type="button" class="tw-dw-btn tw-dw-btn-error tw-text-white tw-dw-btn-sm" id="send_for_sell_return">@lang('lang_v1.send')</button></div>'
                data-html="true" data-placement="bottom">
                <i class="fas fa-undo" style="color:#EF4B53;"></i>
            </button>

            <button type="button" id="view_suspended_sales" title="{{ __('lang_v1.view_suspended_sales') }}"
                class="pos-tb-btn btn-modal"
                data-container=".view_modal" data-href="{{ $view_suspended_sell_url }}">
                <i class="fas fa-pause" style="color:#A5ADBB;"></i>
            </button>

            <div class="pos-ls-drafts-slot">
                @include('sale_pos.partials.pos_drafts_panel')
            </div>

            @if (!isset($pos_settings['hide_recent_trans']) || $pos_settings['hide_recent_trans'] == 0)
                <button type="button" class="pos-tb-btn"
                    data-toggle="modal" data-target="#recent_transactions_modal" id="recent-transactions"
                    title="{{ __('lang_v1.recent_transactions') }}">
                    <i class="fas fa-history"></i>
                </button>
            @endif

            <span class="pos-tb-divider"></span>

            @can('view_cash_register')
                <button type="button" id="register_details" title="{{ __('cash_register.register_details') }}"
                    class="pos-tb-btn btn-modal"
                    data-container=".register_details_modal"
                    data-href="{{ action([\App\Http\Controllers\CashRegisterController::class, 'getRegisterDetails']) }}">
                    <i class="fas fa-briefcase" style="color:#00935F;"></i>
                </button>
            @endcan

            @can('close_cash_register')
                <button type="button" id="close_register" title="{{ __('cash_register.close_register') }}"
                    class="pos-tb-btn btn-modal"
                    data-container=".close_register_modal"
                    data-href="{{ action([\App\Http\Controllers\CashRegisterController::class, 'getCloseRegister']) }}">
                    <i class="fas fa-times-circle" style="color:#EF4B53;"></i>
                </button>
            @endcan

            @if (!empty($pos_settings['inline_service_staff']))
                <span class="pos-tb-divider"></span>
                <button type="button" id="show_service_staff_availability"
                    title="{{ __('lang_v1.service_staff_availability') }}"
                    class="pos-tb-btn"
                    data-container=".view_modal"
                    data-href="{{ action([\App\Http\Controllers\SellPosController::class, 'showServiceStaffAvailibility']) }}">
                    <i class="fas fa-users" style="color:#646EE4;"></i>
                </button>
            @endif

            @if (!empty($pos_settings['inline_service_staff']) || in_array('tables', $enabled_modules) || in_array('service_staff', $enabled_modules))
                <button type="button" class="pos-tb-btn popover-default" id="service_staff_replacement"
                    title="{{ __('restaurant.service_staff_replacement') }}"
                    data-toggle="popover" data-trigger="click"
                    data-content='<div class="m-8"><input type="text" class="form-control" placeholder="@lang('sale.invoice_no')" id="send_for_sell_service_staff_invoice_no"></div><div class="w-100 text-center"><button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-error" id="send_for_sercice_staff_replacement">@lang('lang_v1.send')</button></div>'
                    data-html="true" data-placement="bottom">
                    <i class="fas fa-user-plus" style="color:#646EE4;"></i>
                </button>
            @endif

            <span class="pos-tb-divider"></span>

            <button title="@lang('lang_v1.calculator')" id="btnCalculator" type="button"
                class="pos-tb-btn popover-default"
                data-toggle="popover" data-trigger="click"
                data-content='@include('layouts.partials.calculator')' data-html="true"
                data-placement="bottom">
                <i class="fas fa-calculator" style="color:#00935F;"></i>
            </button>

            <button type="button" title="{{ __('lang_v1.full_screen') }}" class="pos-tb-btn" id="full_screen">
                <i class="fas fa-expand" style="color:#646EE4;"></i>
            </button>

            @if (!empty($pos_settings['customer_display_screen']))
                <a href="{{ route('pos_display') }}" id="customer_display_screen"
                    onclick="window.open(this.href, 'customer_display', 'width='+screen.width+',height='+screen.height+',top=0,left=0'); return false;"
                    title="{{ __('lang_v1.customer_display_screen') }}" class="pos-tb-btn">
                    <i class="fas fa-desktop" style="color:#646EE4;"></i>
                </a>
            @endif

            @if (Module::has('Repair') && $transaction_sub_type != 'repair')
                <span class="pos-tb-divider"></span>
                @include('repair::layouts.partials.pos_header')
            @endif

            @if (in_array('pos_sale', $enabled_modules) && !empty($transaction_sub_type))
                @can('sell.create')
                    <a href="{{ action([\App\Http\Controllers\SellPosController::class, 'create']) }}"
                        title="@lang('sale.pos_sale')" class="pos-tb-btn-wide">
                        <i class="fas fa-th" style="color:#00935F;"></i>
                        <span>@lang('sale.pos_sale')</span>
                    </a>
                @endcan
            @endif

            @can('expense.add')
                <button type="button" title="{{ __('expense.add_expense') }}"
                    class="pos-tb-btn-wide" id="add_expense">
                    <i class="fas fa-minus-circle"></i>
                    <span>@lang('expense.add_expense')</span>
                </button>
            @endcan
        </div>
    </div>
</div>

<script>
(function () {
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('#pos_ls_focus_product_search');
        if (!btn) return;
        e.preventDefault();
        var input = document.getElementById('search_product');
        if (input) {
            input.focus();
            input.select && input.select();
        }
    });
    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key === '/') {
            e.preventDefault();
            var input = document.getElementById('search_product');
            if (input) input.focus();
        }
    });
})();
</script>

<div class="modal fade" id="service_staff_modal" tabindex="-1" role="dialog"
    aria-labelledby="gridSystemModalLabel">
</div>
