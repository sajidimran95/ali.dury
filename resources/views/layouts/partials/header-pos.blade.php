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
        </div>

        <div class="pos-ls-topbar-center">
            <button type="button" class="pos-ls-global-search" id="pos_ls_focus_product_search" title="Search products">
                <i class="fas fa-search"></i>
                <span>Search</span>
                <kbd>Ctrl+/</kbd>
            </button>
        </div>

        <div class="pos-ls-topbar-right">
            @if (empty($pos_settings['hide_product_suggestion']))
                <button type="button" title="{{ __('lang_v1.view_products') }}"
                    class="pos-ls-icon-btn tw-block md:tw-hidden btn-modal"
                    data-toggle="modal" data-target="#mobile_product_suggestion_modal">
                    <i class="fas fa-shopping-bag"></i>
                </button>
            @endif

            <button type="button" class="pos-ls-text-btn tw-hidden md:tw-inline-flex" data-toggle="popover"
                data-placement="bottom" data-trigger="hover" data-html="true"
                data-content="@include('sale_pos.partials.keyboard_shortcuts_details')"
                title="Help">
                Help
            </button>

            <div class="dropdown pos-ls-tools-dropdown">
                <button type="button" class="pos-ls-icon-btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Tools">
                    <i class="fas fa-bell"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-right pos-ls-tools-menu" id="pos_header_more_options">
                    <li class="pos-ls-tools-mobile-only">
                        <a href="{{ $go_back_url }}">{{ __('lang_v1.go_back') }}</a>
                    </li>
                    <li>
                        <a href="javascript:void(0);" id="return_sale" class="popover-default"
                            title="@lang('lang_v1.sell_return')" data-toggle="popover" data-trigger="click"
                            data-content='<div class="m-8"><input type="text" class="form-control" placeholder="@lang('sale.invoice_no')" id="send_for_sell_return_invoice_no"></div><div class="w-100 text-center"><button type="button" class="tw-dw-btn tw-dw-btn-error tw-text-white tw-dw-btn-sm" id="send_for_sell_return">@lang('lang_v1.send')</button></div>'
                            data-html="true" data-placement="bottom">@lang('lang_v1.sell_return')</a>
                    </li>
                    <li>
                        <a href="javascript:void(0);" id="view_suspended_sales" class="btn-modal"
                            data-container=".view_modal" data-href="{{ $view_suspended_sell_url }}">{{ __('lang_v1.view_suspended_sales') }}</a>
                    </li>
                    @if (!isset($pos_settings['hide_recent_trans']) || $pos_settings['hide_recent_trans'] == 0)
                        <li>
                            <a href="javascript:void(0);" data-toggle="modal" data-target="#recent_transactions_modal" id="recent-transactions">{{ __('lang_v1.recent_transactions') }}</a>
                        </li>
                    @endif

                    @can('view_cash_register')
                        <li>
                            <a href="javascript:void(0);" id="register_details" class="btn-modal"
                                data-container=".register_details_modal"
                                data-href="{{ action([\App\Http\Controllers\CashRegisterController::class, 'getRegisterDetails']) }}">{{ __('cash_register.register_details') }}</a>
                        </li>
                    @endcan
                    @can('close_cash_register')
                        <li>
                            <a href="javascript:void(0);" id="close_register" class="btn-modal"
                                data-container=".close_register_modal"
                                data-href="{{ action([\App\Http\Controllers\CashRegisterController::class, 'getCloseRegister']) }}">{{ __('cash_register.close_register') }}</a>
                        </li>
                    @endcan

                    @if (!empty($pos_settings['inline_service_staff']))
                        <li>
                            <a href="javascript:void(0);" id="show_service_staff_availability"
                                data-container=".view_modal"
                                data-href="{{ action([\App\Http\Controllers\SellPosController::class, 'showServiceStaffAvailibility']) }}">{{ __('lang_v1.service_staff_availability') }}</a>
                        </li>
                    @endif

                    @if (
                        !empty($pos_settings['inline_service_staff']) ||
                            (in_array('tables', $enabled_modules) || in_array('service_staff', $enabled_modules)))
                        <li>
                            <a href="javascript:void(0);" id="service_staff_replacement" class="popover-default"
                                data-toggle="popover" data-trigger="click"
                                data-content='<div class="m-8"><input type="text" class="form-control" placeholder="@lang('sale.invoice_no')" id="send_for_sell_service_staff_invoice_no"></div><div class="w-100 text-center"><button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-error" id="send_for_sercice_staff_replacement">@lang('lang_v1.send')</button></div>'
                                data-html="true" data-placement="bottom">{{ __('restaurant.service_staff_replacement') }}</a>
                        </li>
                    @endif

                    <li role="separator" class="divider"></li>
                    <li>
                        <a href="javascript:void(0);" id="btnCalculator" class="popover-default"
                            data-toggle="popover" data-trigger="click" data-content='@include('layouts.partials.calculator')' data-html="true"
                            data-placement="bottom">@lang('lang_v1.calculator')</a>
                    </li>
                    <li class="tw-hidden">
                        <a href="javascript:void(0);" id="full_screen">{{ __('lang_v1.full_screen') }}</a>
                    </li>
                    @if (!empty($pos_settings['customer_display_screen']))
                        <li>
                            <a href="{{ route('pos_display') }}" id="customer_display_screen"
                                onclick="window.open(this.href, 'customer_display', 'width='+screen.width+',height='+screen.height+',top=0,left=0'); return false;">{{ __('lang_v1.customer_display_screen') }}</a>
                        </li>
                    @endif

                    @if (in_array('pos_sale', $enabled_modules) && !empty($transaction_sub_type))
                        @can('sell.create')
                            <li>
                                <a href="{{ action([\App\Http\Controllers\SellPosController::class, 'create']) }}">@lang('sale.pos_sale')</a>
                            </li>
                        @endcan
                    @endif
                    @can('expense.add')
                        <li>
                            <a href="javascript:void(0);" id="add_expense">@lang('expense.add_expense')</a>
                        </li>
                    @endcan
                </ul>
            </div>

            {{-- Drafts stay as sibling so existing panel JS keeps working --}}
            <div class="pos-ls-drafts-slot">
                @include('sale_pos.partials.pos_drafts_panel')
            </div>

            <span class="pos-ls-username">{{ auth()->user()->first_name ?? auth()->user()->username ?? 'POS' }}</span>

            <a href="{{ $go_back_url }}" class="pos-ls-icon-btn tw-hidden md:tw-inline-flex" title="{{ __('lang_v1.go_back') }}">
                <i class="fas fa-sign-out-alt"></i>
            </a>

            <button type="button" class="pos-ls-icon-btn tw-inline-flex md:tw-hidden"
                onclick="document.getElementById('pos_header_more_options').parentElement.classList.toggle('open')">
                <i class="fas fa-bars"></i>
            </button>
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
