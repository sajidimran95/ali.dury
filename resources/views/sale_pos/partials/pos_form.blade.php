<div class="pos-cart-actions no-print">
	<button type="button" class="pos-cart-action-btn" id="view_suspended_sales_link" data-mirror="#view_suspended_sales" aria-expanded="false">
		Retrieve sale
	</button>
	@if (empty($pos_settings['disable_suspend']))
		<button type="button" class="pos-cart-action-btn pos-express-finalize" data-pay_method="suspend" title="@lang('lang_v1.tooltip_suspend')">
			Park sale
		</button>
	@endif
	<div class="dropdown pos-more-actions-dropdown">
		<button type="button" class="pos-cart-action-btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" id="pos_more_actions_btn">
			More actions... <i class="fas fa-chevron-down" style="font-size:10px;margin-left:2px;"></i>
		</button>
		<ul class="dropdown-menu pos-more-menu dropdown-menu-right" aria-labelledby="pos_more_actions_btn">
			@if (!Gate::check('disable_quotation') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
				<li>
					<a href="javascript:void(0);" class="pos-more-item pos-nav-trigger" data-trigger="#pos-quotation">
						Create a quote
					</a>
				</li>
			@endif
			@if (empty($pos_settings['disable_draft']) || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
				@if (!Gate::check('disable_draft') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
					<li>
						<a href="javascript:void(0);" class="pos-more-item" id="pos_more_save_draft">
							@lang('sale.draft')
						</a>
					</li>
				@endif
			@endif
			<li>
				<a href="javascript:void(0);" class="pos-more-item" id="pos_mark_all_fulfillment">
					Mark all fulfillment
				</a>
			</li>
			<li role="separator" class="divider pos-more-divider"></li>
			@if (!Gate::check('disable_suspend_sale') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
				@if (empty($pos_settings['disable_suspend']))
					<li>
						<a href="javascript:void(0);" class="pos-more-item pos-nav-trigger" data-trigger="#pos-suspend-btn">
							@lang('lang_v1.suspend')
						</a>
					</li>
				@endif
			@endif
			<li role="separator" class="divider pos-more-divider"></li>
			<li>
				<a href="javascript:void(0);" class="pos-more-item pos-more-item--danger pos-nav-trigger" data-trigger=".js-pos-cancel">
					@lang('sale.cancel')
				</a>
			</li>
		</ul>
	</div>
	<div id="pos_retrieve_panel" class="pos-retrieve-panel" aria-hidden="true"></div>
</div>

<div class="pos-cart-panel">
<div class="row pos-cart-customer">
	<div class="col-md-12">
		<div class="form-group pos-customer-bar">
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fa fa-user"></i>
				</span>
				<input type="hidden" id="default_customer_id" 
				value="{{ $walk_in_customer['id'] ?? ''}}" >
				<input type="hidden" id="default_customer_name" 
				value="{{ $walk_in_customer['name'] ?? ''}}" >
				<input type="hidden" id="default_customer_balance" 
				value="{{ $walk_in_customer['balance'] ?? ''}}" >
				<input type="hidden" id="default_customer_address" 
				value="{{ $walk_in_customer['shipping_address'] ?? ''}}" >
				@if(!empty($walk_in_customer['price_calculation_type']) && $walk_in_customer['price_calculation_type'] == 'selling_price_group')
					<input type="hidden" id="default_selling_price_group" 
				value="{{ $walk_in_customer['selling_price_group_id'] ?? ''}}" >
				@endif
				{!! Form::select('contact_id', 
					[], null, ['class' => 'form-control mousetrap', 'id' => 'customer_id', 'placeholder' => 'Add a customer', 'required']); !!}
				<span class="input-group-btn">
					<button type="button" class="btn btn-default bg-white btn-flat add_new_customer" data-name=""  @if(!auth()->user()->can('customer.create')) disabled @endif><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
					@can('sell.payments')
					<button type="button" id="pos-receive-customer-payment" class="btn btn-default bg-white btn-flat" title="@lang('lang_v1.receive_payment')"><i class="fas fa-hand-holding-usd text-primary fa-lg"></i></button>
					@endcan
				</span>
			</div>
			{{-- In-cart customer picker (Lightspeed) — not a floating Select2 popup --}}
			<div id="pos_customer_picker" class="pos-customer-picker" aria-hidden="true">
				<div class="pos-customer-picker-search">
					<input type="text" id="pos_customer_picker_input" class="pos-customer-picker-input" placeholder="Search customers" autocomplete="off">
				</div>
				<div id="pos_customer_picker_list" class="pos-customer-picker-list"></div>
				<div class="pos-customer-picker-footer">
					<button type="button" class="btn btn-link add_new_customer pos-cust-add-btn" id="pos_customer_picker_add" data-name="">
						<i class="fa fa-plus" aria-hidden="true"></i>
						<span class="pos-cust-add-label">+ Add as a new customer</span>
					</button>
				</div>
			</div>
			<small class="text-danger hide contact_due_text"><strong>@lang('account.customer_due'):</strong> <span></span></small>
		</div>
	</div>
	@if(!empty($pos_settings['hide_product_suggestion']))
	<div class="col-md-8">
		<div class="form-group">
			<div class="input-group">
				<div class="input-group-btn">
					<button type="button" class="btn btn-default bg-white btn-flat" data-toggle="modal" data-target="#configure_search_modal" title="{{__('lang_v1.configure_product_search')}}"><i class="fas fa-search-plus"></i></button>
				</div>
				{!! Form::text('search_product', null, ['class' => 'form-control', 'id' => 'search_product', 'placeholder' => __('lang_v1.search_product_placeholder'),
				'disabled' => is_null($default_location)? true : false,
				'autofocus' => is_null($default_location)? false : true,
				]); !!}
				<span class="input-group-btn">
					@if(isset($pos_settings['enable_weighing_scale']) && $pos_settings['enable_weighing_scale'] == 1)
						<button type="button" class="btn btn-default bg-white btn-flat" id="weighing_scale_btn" data-toggle="modal" data-target="#weighing_scale_modal"
						title="@lang('lang_v1.weighing_scale')"><i class="fa fa-digital-tachograph text-primary fa-lg"></i></button>
					@endif
					<button type="button" class="btn btn-default bg-white btn-flat pos_add_quick_product" data-href="{{action([\App\Http\Controllers\ProductController::class, 'quickAdd'])}}" data-container=".quick_add_product_modal"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
				</span>
			</div>
		</div>
	</div>
	@endif
</div>
<script>
(function () {
	function closeRetrievePanel() {
		var panel = document.getElementById('pos_retrieve_panel');
		var link = document.getElementById('view_suspended_sales_link');
		if (panel) {
			panel.classList.remove('is-open');
			panel.setAttribute('aria-hidden', 'true');
		}
		if (link) {
			link.classList.remove('is-active');
			link.setAttribute('aria-expanded', 'false');
		}
	}

	function openSuspendedSalesModal() {
		var mirror = document.getElementById('view_suspended_sales');
		var href = mirror ? mirror.getAttribute('data-href') : null;
		var panel = document.getElementById('pos_retrieve_panel');
		var link = document.getElementById('view_suspended_sales_link');
		if (!href || typeof jQuery === 'undefined') {
			if (mirror) mirror.click();
			return;
		}
		if (panel && panel.classList.contains('is-open')) {
			closeRetrievePanel();
			return;
		}
		if (panel && panel.getAttribute('data-loading') === '1') return;
		if (panel) panel.setAttribute('data-loading', '1');
		jQuery.ajax({
			url: href,
			dataType: 'html',
			success: function (result) {
				if (panel) {
					// jQuery.html runs scripts; raw innerHTML does not
					jQuery(panel).html(result);
					panel.classList.add('is-open');
					panel.setAttribute('aria-hidden', 'false');
					positionRetrievePanel();
					if (link) {
						link.classList.add('is-active');
						link.setAttribute('aria-expanded', 'true');
					}
				} else {
					var $container = jQuery((mirror && mirror.getAttribute('data-container')) || '.view_modal').first();
					$container.html(result).modal('show');
				}
			},
			error: function () {
				if (typeof toastr !== 'undefined') {
					toastr.error('Unable to load suspended sales');
				}
			},
			complete: function () {
				if (panel) panel.setAttribute('data-loading', '0');
			}
		});
	}

	function openRetrievedSale(rowOrHref) {
		var row = null;
		var href = null;
		if (rowOrHref && rowOrHref.nodeType === 1) {
			row = rowOrHref;
			href = row.getAttribute('data-href');
		} else {
			href = rowOrHref;
		}

		// Prefer in-cart load on create POS (no edit redirect)
		if (row && document.getElementById('add_pos_sell_form') && typeof pos_load_retrieved_sale_into_cart === 'function') {
			pos_load_retrieved_sale_into_cart(row);
			return;
		}

		if (!href && row) {
			href = row.getAttribute('data-href');
		}
		if (!href) return;
		closeRetrievePanel();
		if (typeof toastr !== 'undefined') {
			toastr.info('Loading parked sale into cart...');
		}
		window.location.href = href;
	}

	window.posOpenRetrievedSaleRow = function (event, row) {
		if (!row) return;
		if (event && event.target && event.target.closest && event.target.closest('a')) {
			return;
		}
		if (event) {
			event.preventDefault();
			event.stopPropagation();
		}
		openRetrievedSale(row);
	};

	document.addEventListener('click', function (e) {
		var link = e.target.closest('#view_suspended_sales_link');
		if (link) {
			e.preventDefault();
			e.stopPropagation();
			openSuspendedSalesModal();
			return;
		}

		// Delete uses app.js $(document).on('click', '.delete-sale') — do not stopPropagation
		if (e.target.closest('#pos_retrieve_panel a.pos-retrieve-delete, #pos_retrieve_panel a.delete-sale')) {
			return;
		}

		// Retrieve sale row → add items into current cart
		var retrieveOpen = e.target.closest('#pos_retrieve_panel a.pos-retrieve-open');
		if (retrieveOpen) {
			e.preventDefault();
			e.stopPropagation();
			var rowFromOpen = retrieveOpen.closest('.pos-retrieve-row');
			openRetrievedSale(rowFromOpen || retrieveOpen.getAttribute('href'));
			return;
		}
		var retrieveRow = e.target.closest('#pos_retrieve_panel .pos-retrieve-row.is-clickable');
		if (retrieveRow && !e.target.closest('a')) {
			e.preventDefault();
			e.stopPropagation();
			openRetrievedSale(retrieveRow);
			return;
		}

		var panel = document.getElementById('pos_retrieve_panel');
		if (panel && panel.classList.contains('is-open') && !e.target.closest('#pos_retrieve_panel') && !e.target.closest('#view_suspended_sales_link')) {
			// Keep panel open while SweetAlert delete confirm is showing
			if (e.target.closest('.swal-modal, .swal-overlay')) {
				return;
			}
			closeRetrievePanel();
		}
	});

	function positionRetrievePanel() {
		var panel = document.getElementById('pos_retrieve_panel');
		var link = document.getElementById('view_suspended_sales_link');
		if (!panel || !link) return;
		var rect = link.getBoundingClientRect();
		var width = Math.min(560, window.innerWidth - 24);
		var left = Math.min(Math.max(12, rect.right - width), window.innerWidth - width - 12);
		var top = Math.min(rect.bottom + 6, window.innerHeight - 80);
		panel.style.top = top + 'px';
		panel.style.left = left + 'px';
		panel.style.right = 'auto';
		panel.style.width = width + 'px';
		panel.style.maxHeight = Math.min(window.innerHeight - top - 16, 560) + 'px';
	}

	window.posReloadRetrievePanel = function () {
		var panel = document.getElementById('pos_retrieve_panel');
		var mirror = document.getElementById('view_suspended_sales');
		var href = mirror ? mirror.getAttribute('data-href') : null;
		if (!panel || !href || typeof jQuery === 'undefined') return;
		jQuery.ajax({
			url: href,
			dataType: 'html',
			success: function (result) {
				jQuery(panel).html(result);
				if (panel.classList.contains('is-open')) {
					positionRetrievePanel();
				}
			}
		});
	};

	function closeMoreMenu(el) {
		if (typeof jQuery !== 'undefined') {
			jQuery(el).closest('.dropdown').removeClass('open');
		}
	}

	// More-actions: trigger existing POS buttons
	document.addEventListener('click', function (e) {
		var item = e.target.closest('.pos-more-item.pos-nav-trigger');
		if (!item) return;
		e.preventDefault();
		var sel = item.getAttribute('data-trigger');
		if (!sel) return;
		var el = document.querySelector(sel);
		if (el) {
			// jQuery trigger works even when footer control is visually hidden
			if (typeof jQuery !== 'undefined') {
				jQuery(el).trigger('click');
			} else {
				el.click();
			}
		}
		closeMoreMenu(item);
	});

	// Save draft — previous system behavior (same as #pos-draft)
	document.addEventListener('click', function (e) {
		var btn = e.target.closest('#pos_more_save_draft');
		if (!btn) return;
		e.preventDefault();
		closeMoreMenu(btn);
		if (typeof jQuery !== 'undefined' && jQuery('#pos-draft').length) {
			jQuery('#pos-draft').trigger('click');
		}
	});

	// Mark all fulfillment → kitchen order / shipping note
	document.addEventListener('click', function (e) {
		var btn = e.target.closest('#pos_mark_all_fulfillment');
		if (!btn) return;
		e.preventDefault();
		var kitchen = document.getElementById('is_kitchen_order');
		if (kitchen) {
			kitchen.checked = true;
			if (typeof jQuery !== 'undefined') {
				jQuery(kitchen).iCheck('check');
				jQuery(kitchen).trigger('change');
			} else {
				kitchen.dispatchEvent(new Event('change', { bubbles: true }));
			}
			if (typeof toastr !== 'undefined') {
				toastr.success('All items marked for fulfillment');
			}
		} else {
			var ship = document.querySelector('[data-target="#posShippingModal"]');
			if (ship) ship.click();
			else if (typeof toastr !== 'undefined') {
				toastr.info('Open shipping / note to set fulfillment');
			}
		}
		closeMoreMenu(btn);
	});

	// Assign all sale items → service staff on all lines / staff modal
	document.addEventListener('click', function (e) {
		var btn = e.target.closest('#pos_assign_all_sale_items');
		if (!btn) return;
		e.preventDefault();

		var staffSelects = document.querySelectorAll('select.order_line_service_staff, select#res_waiter_id, select#service_staff_id');
		if (staffSelects.length) {
			var first = staffSelects[0];
			var val = first.value;
			if (!val && first.options.length > 1) {
				// focus first select so user can pick, then apply to all
				if (typeof jQuery !== 'undefined' && jQuery(first).data('select2')) {
					jQuery(first).select2('open');
				} else {
					first.focus();
				}
				if (typeof toastr !== 'undefined') {
					toastr.info('Select staff — it will apply to all sale items');
				}
				var applyAll = function () {
					var chosen = first.value;
					if (!chosen) return;
					staffSelects.forEach(function (sel) {
						sel.value = chosen;
						if (typeof jQuery !== 'undefined') {
							jQuery(sel).trigger('change');
						}
					});
					first.removeEventListener('change', applyAll);
					if (typeof toastr !== 'undefined') {
						toastr.success('Staff assigned to all sale items');
					}
				};
				first.addEventListener('change', applyAll);
			} else if (val) {
				staffSelects.forEach(function (sel) {
					sel.value = val;
					if (typeof jQuery !== 'undefined') {
						jQuery(sel).trigger('change');
					}
				});
				if (typeof toastr !== 'undefined') {
					toastr.success('Staff assigned to all sale items');
				}
			}
		} else {
			var staffBtn = document.getElementById('service_staff_replacement') || document.getElementById('show_service_staff_availability');
			if (staffBtn) {
				staffBtn.click();
			} else if (typeof toastr !== 'undefined') {
				toastr.info('Enable service staff in POS settings to assign items');
			}
		}
		closeMoreMenu(btn);
	});
})();
</script>

<div class="row pos-cart-meta">
	@if(!empty($pos_settings['show_invoice_layout']))
	<div class="col-md-4">
		<div class="form-group">
		{!! Form::select('invoice_layout_id', 
					$invoice_layouts, $default_location->invoice_layout_id, ['class' => 'form-control select2', 'placeholder' => __('lang_v1.select_invoice_layout'), 'id' => 'invoice_layout_id']); !!}
		</div>
	</div>
	@endif
	<input type="hidden" name="pay_term_number" id="pay_term_number" value="{{$walk_in_customer['pay_term_number'] ?? ''}}">
	<input type="hidden" name="pay_term_type" id="pay_term_type" value="{{$walk_in_customer['pay_term_type'] ?? ''}}">
	
	@if(!empty($commission_agent))
		@php
			$is_commission_agent_required = !empty($pos_settings['is_commission_agent_required']);
		@endphp
		<div class="col-md-4 pos-cart-meta-extra">
			<div class="form-group">
			{!! Form::select('commission_agent', 
						$commission_agent, null, ['class' => 'form-control select2', 'placeholder' => __('lang_v1.commission_agent'), 'id' => 'commission_agent', 'required' => $is_commission_agent_required]); !!}
			</div>
		</div>
	@endif
	@if(!empty($pos_settings['enable_transaction_date']))
		<div class="col-md-6 col-sm-6 pos-cart-meta-date">
			<div class="form-group">
				{!! Form::label('transaction_date', __('sale.sale_date') . ':*', ['class' => 'pos-cart-meta-label']) !!}
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fa fa-calendar"></i>
					</span>
					{!! Form::text('transaction_date', $default_datetime, ['class' => 'form-control', 'readonly', 'required', 'id' => 'transaction_date']); !!}
				</div>
			</div>
		</div>
	@endif
	@if(config('constants.enable_sell_in_diff_currency') == true)
		<div class="col-md-4 col-sm-6 pos-cart-meta-extra">
			<div class="form-group">
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fas fa-exchange-alt"></i>
					</span>
					{!! Form::text('exchange_rate', config('constants.currency_exchange_rate'), ['class' => 'form-control input-sm input_number', 'placeholder' => __('lang_v1.currency_exchange_rate'), 'id' => 'exchange_rate']); !!}
				</div>
			</div>
		</div>
	@endif
	@if(!empty($price_groups) && count($price_groups) > 1)
		<div class="col-md-6 col-sm-6 pos-cart-meta-price">
			<div class="form-group">
				{!! Form::label('price_group', __('lang_v1.selling_price_group') . ':', ['class' => 'pos-cart-meta-label']) !!}
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fas fa-money-bill-alt"></i>
					</span>
					@php
						reset($price_groups);
						$selected_price_group = !empty($default_price_group_id) && array_key_exists($default_price_group_id, $price_groups) ? $default_price_group_id : null;
					@endphp
					{!! Form::hidden('hidden_price_group', key($price_groups), ['id' => 'hidden_price_group']) !!}
					{!! Form::select('price_group', $price_groups, $selected_price_group, ['class' => 'form-control select2', 'id' => 'price_group']); !!}
				</div>
			</div>
		</div>
	@else
		@php
			reset($price_groups);
		@endphp
		{!! Form::hidden('price_group', key($price_groups), ['id' => 'price_group']) !!}
	@endif
	@if(!empty($default_price_group_id))
		{!! Form::hidden('default_price_group', $default_price_group_id, ['id' => 'default_price_group']) !!}
	@endif

	@if(in_array('types_of_service', $enabled_modules) && !empty($types_of_service))
		<div class="col-md-4 col-sm-6 pos-cart-meta-extra">
			<div class="form-group">
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fa fa-external-link-square-alt text-primary service_modal_btn"></i>
					</span>
					{!! Form::select('types_of_service_id', $types_of_service, null, ['class' => 'form-control', 'id' => 'types_of_service_id', 'style' => 'width: 100%;', 'placeholder' => __('lang_v1.select_types_of_service')]); !!}

					{!! Form::hidden('types_of_service_price_group', null, ['id' => 'types_of_service_price_group']) !!}

					<span class="input-group-addon">
						@show_tooltip(__('lang_v1.types_of_service_help'))
					</span> 
				</div>
				<small><p class="help-block hide" id="price_group_text">@lang('lang_v1.price_group'): <span></span></p></small>
			</div>
		</div>
		<div class="modal fade types_of_service_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
	@endif

	@if(!empty($pos_settings['show_invoice_scheme']))
		@php
			$invoice_scheme_id = $default_invoice_schemes->id;
			if(!empty($default_location->invoice_scheme_id)) {
				$invoice_scheme_id = $default_location->invoice_scheme_id;
			}
		@endphp
		<div class="col-md-4 col-sm-6 pos-cart-meta-extra">
			<div class="form-group">
				{!! Form::select('invoice_scheme_id', $invoice_schemes, $invoice_scheme_id, 
					['class' => 'form-control', 'placeholder' => __('lang_v1.select_invoice_scheme'), 
					'id' => 'invoice_scheme_id']); !!}
			</div>
		</div>
	@endif
	@if(in_array('subscription', $enabled_modules))
		<div class="col-md-4 col-sm-6 pos-cart-meta-extra">
			<label>
              {!! Form::checkbox('is_recurring', 1, false, ['class' => 'input-icheck', 'id' => 'is_recurring']); !!} @lang('lang_v1.subscribe')?
            </label><button type="button" data-toggle="modal" data-target="#recurringInvoiceModal" class="btn btn-link"><i class="fa fa-external-link-square-alt"></i></button>@show_tooltip(__('lang_v1.recurring_invoice_help'))
		</div>
	@endif
	
	<!-- Call restaurant module if defined -->
    @if(in_array('tables' ,$enabled_modules) || in_array('service_staff' ,$enabled_modules))
    	<span id="restaurant_module_span">
      		<div class="col-md-3"></div>
    	</span>
    @endif

	@if(in_array('kitchen' ,$enabled_modules))
		<div class="col-md-3 pos-cart-meta-extra">
			<div class="form-group">
				<div class="checkbox">
				<label>
						{!! Form::checkbox('is_kitchen_order', 1, false, ['class' => 'input-icheck status', 'id' => 'is_kitchen_order']); !!} {{ __('lang_v1.kitchen_order') }}
				</label>
				@show_tooltip(__('lang_v1.kitchen_order_tooltip'))
				</div>
			</div>
		</div>
    @endif
    
</div>
<!-- include module fields -->
@if(!empty($pos_module_data))
    @foreach($pos_module_data as $key => $value)
        @if(!empty($value['view_path']))
            @includeIf($value['view_path'], ['view_data' => $value['view_data']])
        @endif
    @endforeach
@endif
<div class="row pos-cart-lines" style="margin:0;">
	<div class="col-sm-12 pos_product_div" style="padding:4px 0 0 0;">
		<input type="hidden" name="sell_price_tax" id="sell_price_tax" value="{{$business_details->sell_price_tax}}">

		<!-- Keeps count of product rows -->
		<input type="hidden" id="product_row_count" 
			value="0">
		@php
			$hide_tax = '';
			if( session()->get('business.enable_inline_tax') == 0){
				$hide_tax = 'hide';
			}
		@endphp
		<table class="table table-condensed table-responsive tw-border-0 tw-table-fixed" id="pos_table">
			<thead>
				<tr>
					<th class="text-center pos-th-product tw-sticky tw-top-0 tw-z-10 !tw-bg-[#f8fafc] !tw-text-[#94a3b8] !tw-border-b !tw-border-[#e2e8f0] !tw-border-t-0 !tw-border-l-0 !tw-border-r-0 !tw-px-2 !tw-py-1 !tw-text-[11px] tw-font-bold tw-uppercase tw-tracking-[0.4px] !tw-leading-[1.2] tw-whitespace-nowrap tw-overflow-hidden !tw-align-middle tw-w-[30%]">
						@lang('sale.product') @show_tooltip(__('lang_v1.tooltip_sell_product_column'))
					</th>
					<th class="text-center pos-th-qty tw-sticky tw-top-0 tw-z-10 !tw-bg-[#f8fafc] !tw-text-[#94a3b8] !tw-border-b !tw-border-[#e2e8f0] !tw-border-t-0 !tw-border-l-0 !tw-border-r-0 !tw-px-2 !tw-py-1 !tw-text-[11px] tw-font-bold tw-uppercase tw-tracking-[0.4px] !tw-leading-[1.2] tw-whitespace-nowrap tw-overflow-hidden !tw-align-middle tw-w-[19%]">
						@lang('sale.qty')
					</th>
					@if(!empty($pos_settings['inline_service_staff']))
						<th class="text-center pos-th-staff tw-sticky tw-top-0 tw-z-10 !tw-bg-[#f8fafc] !tw-text-[#94a3b8] !tw-border-b !tw-border-[#e2e8f0] !tw-border-t-0 !tw-border-l-0 !tw-border-r-0 !tw-px-2 !tw-py-1 !tw-text-[11px] tw-font-bold tw-uppercase tw-tracking-[0.4px] !tw-leading-[1.2] tw-whitespace-nowrap tw-overflow-hidden !tw-align-middle">
							@lang('restaurant.service_staff')
						</th>
					@endif
					<th class="text-center pos-th-price tw-sticky tw-top-0 tw-z-10 !tw-bg-[#f8fafc] !tw-text-[#94a3b8] !tw-border-b !tw-border-[#e2e8f0] !tw-border-t-0 !tw-border-l-0 !tw-border-r-0 !tw-px-2 !tw-py-1 !tw-text-[11px] tw-font-bold tw-uppercase tw-tracking-[0.4px] !tw-leading-[1.2] tw-whitespace-nowrap tw-overflow-hidden !tw-align-middle tw-w-auto tw-min-w-[13%] {{$hide_tax}}">
						@lang('sale.price_inc_tax')
					</th>
					<th class="text-center pos-th-subtotal tw-sticky tw-top-0 tw-z-10 !tw-bg-[#f8fafc] !tw-text-[#94a3b8] !tw-border-b !tw-border-[#e2e8f0] !tw-border-t-0 !tw-border-l-0 !tw-border-r-0 !tw-px-2 !tw-py-1 !tw-text-[11px] tw-font-bold tw-uppercase tw-tracking-[0.4px] !tw-leading-[1.2] tw-whitespace-nowrap tw-overflow-hidden !tw-align-middle tw-w-auto tw-min-w-[14%]">
						@lang('sale.subtotal')
					</th>
					<th class="pos-th-action tw-sticky tw-top-0 tw-z-10 !tw-bg-[#f8fafc] !tw-border-b !tw-border-[#e2e8f0] !tw-border-t-0 !tw-border-l-0 !tw-border-r-0 !tw-py-1 !tw-pr-2 !tw-pl-0 !tw-text-[11px] tw-font-bold tw-uppercase tw-tracking-[0.4px] !tw-leading-[1.2] tw-whitespace-nowrap tw-overflow-hidden !tw-align-middle tw-w-[52px] !tw-text-center"></th>
				</tr>
			</thead>
			<tbody>
				<tr class="pos-empty-state-row">
					<td colspan="100" class="!tw-border-0 !tw-p-0 pos-empty-td">
						<div class="pos-empty-cart tw-flex tw-flex-col tw-items-center tw-justify-center tw-text-center tw-py-10 md:tw-py-14 tw-px-6 tw-gap-3">
							<div class="pos-empty-cart-icon tw-w-16 tw-h-16 tw-rounded-full tw-flex tw-items-center tw-justify-center">
								<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17h-11v-14h-2"/><path d="M6 5l14 1l-1 7h-13"/></svg>
							</div>
							<div class="pos-empty-cart-title">Your cart is empty</div>
							<div class="pos-empty-cart-sub">Scan a barcode, tap a product tile, or type to search.</div>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
		<style>
			#pos_table:not(.pos-has-rows) thead { display: none !important; }
			#pos_table.pos-has-rows .pos-empty-state-row { display: none !important; }
			#add_pos_sell_form:not(.pos-has-rows) .pos_form_totals .pos_totals_left,
			#edit_pos_sell_form:not(.pos-has-rows) .pos_form_totals .pos_totals_left { display: none !important; }
			#add_pos_sell_form:not(.pos-has-rows) .pos_form_totals,
			#edit_pos_sell_form:not(.pos-has-rows) .pos_form_totals {
				display: flex !important;
				flex-direction: column !important;
			}
		</style>
	</div>
</div>