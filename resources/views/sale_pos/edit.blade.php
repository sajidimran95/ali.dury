@extends('layouts.app')

@section('title', __('sale.pos_sale'))

@section('content')
<section class="content no-print">
	<input type="hidden" id="amount_rounding_method" value="{{$pos_settings['amount_rounding_method'] ?? ''}}">
	<input type="hidden" id="pos_defer_product_list" value="0">
	@if(!empty($pos_settings['allow_overselling']))
		<input type="hidden" id="is_overselling_allowed">
	@endif
	@if(session('business.enable_rp') == 1)
        <input type="hidden" id="reward_point_enabled">
    @endif
	@php
		$is_discount_enabled = $pos_settings['disable_discount'] != 1 ? true : false;
		$is_rp_enabled = session('business.enable_rp') == 1 ? true : false;
	@endphp
	<div class="pos-sale-shell @if(strtolower((string) ($business_details->pos_screen_layout ?? 'ltr')) === 'rtl') pos-layout-rtl @else pos-layout-ltr @endif">
		@include('sale_pos.partials.pos_nav_sidebar')
		<div class="pos-sale-main">
	{!! Form::open(['url' => action([\App\Http\Controllers\SellPosController::class, 'update'], [$transaction->id]), 'method' => 'post', 'id' => 'edit_pos_sell_form', 'class' => count($sell_details ?? []) > 0 ? 'pos-has-rows' : '' ]) !!}
	{{ method_field('PUT') }}
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
							{!! Form::hidden('location_id', $transaction->location_id, ['id' => 'location_id', 'data-receipt_printer_type' => !empty($location_printer_type) ? $location_printer_type : 'browser', 'data-default_payment_accounts' => $transaction->location->default_payment_accounts]); !!}
							{!! Form::hidden('sub_type', isset($sub_type) ? $sub_type : null) !!}
							<input type="hidden" name="pos_print_copies" id="pos_print_copies" value="{{ (int) ($business_details->print_per_sale_copies ?? 1) }}">
							<input type="hidden" name="pos_print_printer" id="pos_print_printer" value="{{ $business_details->print_per_sale_printer_id ?? '' }}">
							<input type="hidden" id="pos_print_settings_url" value="{{ url('/sells/pos/print-per-sale-settings') }}">
							<input type="hidden" id="pos_screen_settings_url" value="{{ url('/sells/pos/screen-settings') }}">
							<input type="hidden" id="pos_screen_layout" value="{{ strtolower((string) ($business_details->pos_screen_layout ?? 'ltr')) === 'rtl' ? 'rtl' : 'ltr' }}">
							<input type="hidden" id="item_addition_method" value="1">
							@include('sale_pos.partials.pos_cart_actions')
							<div class="pos-cart-panel">
							@include('sale_pos.partials.pos_form_edit')

							@include('sale_pos.partials.pos_form_totals', ['edit' => true])
							</div>{{-- /.pos-cart-panel --}}

							@include('sale_pos.partials.payment_modal')

							@if(empty($pos_settings['disable_suspend']))
								@include('sale_pos.partials.suspend_note_modal')
							@endif

							@if(empty($pos_settings['disable_recurring_invoice']))
								@include('sale_pos.partials.recurring_invoice_modal')
							@endif
						</div>
						@if(!empty($only_payment))
							<div class="overlay"></div>
						@endif
					</div>
				</div>
			</div>
		</div>
	</div>
	@include('sale_pos.partials.pos_form_actions', ['edit' => true])
	{!! Form::close() !!}
		</div>{{-- /.pos-sale-main --}}
	</div>{{-- /.pos-sale-shell --}}
</section>

<!-- This will be printed -->
<section class="invoice print_section" id="receipt_section">
</section>
<div class="modal fade contact_modal" id="pos_contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
	@include('contact.create', ['quick_add' => true])
</div>
@if(empty($pos_settings['hide_product_suggestion']) && isMobile())
	@include('sale_pos.partials.mobile_product_suggestions')
@endif
<!-- /.content -->
<div class="modal fade register_details_modal" tabindex="-1" role="dialog" 
	aria-labelledby="gridSystemModalLabel">
</div>
<div class="modal fade close_register_modal" tabindex="-1" role="dialog" 
	aria-labelledby="gridSystemModalLabel">
</div>
<!-- quick product modal -->
<div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>

@include('sale_pos.partials.configure_search_modal')

@include('sale_pos.partials.recent_transactions_modal')

@include('sale_pos.partials.print_per_sale_modal')

@include('sale_pos.partials.pos_screen_setting_modal')

@include('sale_pos.partials.pos_quick_keys_modal')

@include('sale_pos.partials.customer_purchase_history_modal')

@include('sale_pos.partials.weighing_scale_modal')

<div class="modal fade" id="pos_pay_contact_due_modal" tabindex="-1" role="dialog">
</div>

@stop

@section('javascript')
	<script src="{{ asset('js/pos.js?v=' . $asset_v . '&ls=162') }}"></script>
	<script src="{{ asset('js/printer.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/opening_stock.js?v=' . $asset_v . '&pack=2') }}"></script>
	@include('sale_pos.partials.keyboard_shortcuts')

	<!-- Call restaurant module if defined -->
    @if(in_array('tables' ,$enabled_modules) || in_array('modifiers' ,$enabled_modules) || in_array('service_staff' ,$enabled_modules))
    	<script src="{{ asset('js/restaurant.js?v=' . $asset_v) }}"></script>
    @endif

    <!-- include module js -->
    @if(!empty($pos_module_data))
	    @foreach($pos_module_data as $key => $value)
            @if(!empty($value['module_js_path']))
                @includeIf($value['module_js_path'], ['view_data' => $value['view_data']])
            @endif
	    @endforeach
	@endif

	@include('sale_pos.partials.pos_layout_script', ['form_id' => 'edit_pos_sell_form'])
	@include('sale_pos.partials.pos_cart_actions_script')
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
		});
	})();
	</script>

@endsection

@section('css')
	<style type="text/css">
		/*CSS to print receipts*/
		.print_section{
		    display: none;
		}
		@media print{
		    .print_section{
		        display: block !important;
		    }
		}
		@page {
		    size: 3.1in auto;/* width height */
		    height: auto !important;
		    margin-top: 0mm;
		    margin-bottom: 0mm;
		}
		.overlay {
			background: rgba(255,255,255,0) !important;
			cursor: not-allowed;
		}
	</style>
	<!-- include module css -->
    @if(!empty($pos_module_data))
        @foreach($pos_module_data as $key => $value)
            @if(!empty($value['module_css_path']))
                @includeIf($value['module_css_path'])
            @endif
        @endforeach
    @endif
@endsection