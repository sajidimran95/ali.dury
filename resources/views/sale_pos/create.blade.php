@extends('layouts.app')

@section('title', __('sale.pos_sale'))

@section('content')
    <section class="content no-print">
        <input type="hidden" id="amount_rounding_method" value="{{ $pos_settings['amount_rounding_method'] ?? '' }}">
        <input type="hidden" id="pos_defer_product_list" value="0">
        @if (!empty($pos_settings['allow_overselling']))
            <input type="hidden" id="is_overselling_allowed">
        @endif
        @if (session('business.enable_rp') == 1)
            <input type="hidden" id="reward_point_enabled">
        @endif
        @php
            $is_discount_enabled = $pos_settings['disable_discount'] != 1 ? true : false;
            $is_rp_enabled = session('business.enable_rp') == 1 ? true : false;
        @endphp
        <div class="pos-sale-shell">
            @include('sale_pos.partials.pos_nav_sidebar')
            <div class="pos-sale-main">
        {!! Form::open([
            'url' => action([\App\Http\Controllers\SellPosController::class, 'store']),
            'method' => 'post',
            'id' => 'add_pos_sell_form',
        ]) !!}
        <div class="row" style="margin:0;">
            <div class="col-md-12" style="padding:0;">
                <div class="row tw-flex lg:tw-flex-row md:tw-flex-col sm:tw-flex-col tw-flex-col tw-items-stretch pos-checkout-row" style="gap: 0; margin: 0; padding: 0;">
                    @if (empty($pos_settings['hide_product_suggestion']))
                        <div class="tw-w-full lg:tw-w-[55%] tw-order-2 lg:tw-order-1" style="padding:0;" id="pos_sidebar_wrap">
                            @include('sale_pos.partials.pos_sidebar')
                        </div>
                    @endif
                    <div class="tw-w-full @if(empty($pos_settings['hide_product_suggestion'])) lg:tw-w-[45%] tw-order-1 lg:tw-order-2 @else lg:tw-w-[100%] @endif" style="padding:0;" id="pos_checkout_wrap">

                        <div class="pos-checkout-inner" style="padding:0;height:100%;">
                                <div class="box-body pb-0">
                                    {!! Form::hidden('location_id', $default_location->id ?? null, [
                                        'id' => 'location_id',
                                        'data-receipt_printer_type' => !empty($default_location->receipt_printer_type)
                                            ? $default_location->receipt_printer_type
                                            : 'browser',
                                        'data-default_payment_accounts' => $default_location->default_payment_accounts ?? '',
                                    ]) !!}
                                    <!-- sub_type -->
                                    {!! Form::hidden('sub_type', isset($sub_type) ? $sub_type : null) !!}
                                    <input type="hidden" name="pos_print_copies" id="pos_print_copies" value="{{ (int) ($business_details->print_per_sale_copies ?? 1) }}">
                                    <input type="hidden" name="pos_print_printer" id="pos_print_printer" value="{{ $business_details->print_per_sale_printer_id ?? '' }}">
                                    <input type="hidden" id="pos_print_settings_url" value="{{ url('/sells/pos/print-per-sale-settings') }}">
                                    <input type="hidden" id="item_addition_method"
                                        value="{{ $business_details->item_addition_method }}">
                                    @include('sale_pos.partials.pos_form')

                                    @include('sale_pos.partials.pos_form_totals')
                                    </div>{{-- /.pos-cart-panel --}}

                                    @include('sale_pos.partials.payment_modal')

                                    @if (empty($pos_settings['disable_suspend']))
                                        @include('sale_pos.partials.suspend_note_modal')
                                    @endif

                                    @if (empty($pos_settings['disable_recurring_invoice']))
                                        @include('sale_pos.partials.recurring_invoice_modal')
                                    @endif
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('sale_pos.partials.pos_form_actions')
        {!! Form::close() !!}
            </div>{{-- /.pos-sale-main --}}
        </div>{{-- /.pos-sale-shell --}}
    </section>

    <!-- This will be printed -->
    <section class="invoice print_section" id="receipt_section">
    </section>
    <div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        @include('contact.create', ['quick_add' => true])
    </div>
    @if (empty($pos_settings['hide_product_suggestion']) && isMobile())
        @include('sale_pos.partials.mobile_product_suggestions')
    @endif
    <!-- /.content -->
    <div class="modal fade register_details_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade close_register_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <!-- quick product modal -->
    <div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>

    <div class="modal fade" id="expense_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade" id="pos_pay_contact_due_modal" tabindex="-1" role="dialog">
    </div>

    @include('sale_pos.partials.configure_search_modal')

    @include('sale_pos.partials.recent_transactions_modal')

    @include('sale_pos.partials.print_per_sale_modal')

    @include('sale_pos.partials.pos_catalog_visibility_modal')

    @include('sale_pos.partials.customer_purchase_history_modal')

    @include('sale_pos.partials.weighing_scale_modal')

@stop
@section('css')
    <!-- include module css -->
    @if (!empty($pos_module_data))
        @foreach ($pos_module_data as $key => $value)
            @if (!empty($value['module_css_path']))
                @includeIf($value['module_css_path'])
            @endif
        @endforeach
    @endif
@stop
@section('javascript')
    <script src="{{ asset('js/pos.js?v=' . $asset_v . '&ls=110') }}"></script>
    <script src="{{ asset('js/printer.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>
    @include('sale_pos.partials.keyboard_shortcuts')

    <!-- Call restaurant module if defined -->
    @if (in_array('tables', $enabled_modules) ||
            in_array('modifiers', $enabled_modules) ||
            in_array('service_staff', $enabled_modules))
        <script src="{{ asset('js/restaurant.js?v=' . $asset_v) }}"></script>
    @endif
    <!-- include module js -->
    @if (!empty($pos_module_data))
        @foreach ($pos_module_data as $key => $value)
            @if (!empty($value['module_js_path']))
                @includeIf($value['module_js_path'], ['view_data' => $value['view_data']])
            @endif
        @endforeach
    @endif
    @include('sale_pos.partials.pos_layout_script', ['form_id' => 'add_pos_sell_form'])
    <script>
    (function () {
        function syncPayCta() {
            var src = document.getElementById('total_payable');
            var targets = document.querySelectorAll('.pos_pay_cta_total');
            if (!src || !targets.length) return;
            targets.forEach(function (el) {
                el.textContent = src.textContent;
            });
        }
        $(document).ready(function () {
            syncPayCta();
            var src = document.getElementById('total_payable');
            if (src && window.MutationObserver) {
                new MutationObserver(syncPayCta).observe(src, { childList: true, characterData: true, subtree: true });
            }
            $(document).on('change keyup', '#final_total_input', syncPayCta);
            setInterval(syncPayCta, 800);
            $(document).on('click', '#pos_table tbody tr.product_row', function () {
                $('#pos_table tbody tr.product_row').removeClass('is-selected');
                $(this).addClass('is-selected');
            });

            // Category grid uses 6 cols (col-md-2) — fix subcategory strip row math
            $(document).off('click.posCatFix', '.main-category').on('click.posCatFix', '.main-category', function () {
                setTimeout(function () {
                    var $grid = $('.pos-card-grid-categories');
                    var $strip = $grid.find('.pos-subcat-strip');
                    if (!$strip.length) return;
                    var $cols = $grid.children('[class*="col-"]').not('.pos-subcat-strip');
                    var $active = $grid.find('.pos-card.cat.is-active').closest('[class*="col-"]');
                    if (!$active.length) return;
                    var idx = $cols.index($active);
                    var perRow = window.innerWidth <= 575 ? 2 : (window.innerWidth <= 1100 ? 4 : (window.innerWidth <= 1400 ? 5 : 6));
                    var $lastInRow = $cols.eq(Math.min(Math.ceil((idx + 1) / perRow) * perRow - 1, $cols.length - 1));
                    $strip.insertAfter($lastInRow);
                }, 0);
            });
        });
    })();
    </script>
@endsection
