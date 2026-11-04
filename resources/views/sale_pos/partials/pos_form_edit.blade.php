<div class="row pos-cart-customer">
	<div class="col-md-12">
		<div class="form-group pos-customer-bar">
			<div class="input-group">
				<span class="input-group-addon">
					<i class="fa fa-user"></i>
				</span>
				<input type="hidden" id="default_customer_id" 
				value="{{ $transaction->contact->id }}" >
				<input type="hidden" id="default_customer_name" 
				value="{{ trim($transaction->contact->supplier_business_name ?: $transaction->contact->name) }}" >
				<input type="hidden" id="default_customer_balance" 
				value="{{$transaction->contact->balance}}" >
				<input type="hidden" id="default_customer_address" 
				value="{{ $transaction->contact->shipping_address ?? '' }}" >
				@if(!empty($transaction->contact->price_calculation_type) && $transaction->contact->price_calculation_type == 'selling_price_group')
					<input type="hidden" id="default_selling_price_group" 
				value="{{ $transaction->contact->selling_price_group_id ?? '' }}" >
				@endif
				{!! Form::select('contact_id', 
					[], null, ['class' => 'form-control mousetrap', 'id' => 'customer_id', 'placeholder' => 'Add a customer', 'required']); !!}
				<span class="input-group-btn">
					<button type="button" id="pos_customer_reset_walkin" class="btn btn-default bg-white btn-flat hide" title="Reset to Walk-In Customer"><i class="fas fa-undo text-primary fa-lg"></i></button>
					<button type="button" class="btn btn-default bg-white btn-flat add_new_customer" data-name=""  @if(!auth()->user()->can('customer.create')) disabled @endif><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
					<button type="button" id="pos-customer-purchase-history" class="btn btn-default bg-white btn-flat" title="@lang('lang_v1.customer_purchase_history')"><i class="fas fa-history text-primary fa-lg"></i></button>
					@can('sell.payments')
					<button type="button" id="pos-receive-customer-payment" class="btn btn-default bg-white btn-flat" title="@lang('lang_v1.receive_payment')"><i class="fas fa-hand-holding-usd text-primary fa-lg"></i></button>
					@endcan
				</span>
			</div>
			<div id="pos_customer_picker" class="pos-customer-picker" aria-hidden="true">
				<div class="pos-customer-picker-search">
					<input type="text" id="pos_customer_picker_input" class="pos-customer-picker-input" placeholder="Search name, phone, address, city, ZIP" autocomplete="off">
				</div>
				<div id="pos_customer_picker_list" class="pos-customer-picker-list"></div>
				<div class="pos-customer-picker-footer">
					<button type="button" class="btn btn-link add_new_customer pos-cust-add-btn" id="pos_customer_picker_add" data-name="">
						<i class="fa fa-plus" aria-hidden="true"></i>
						<span class="pos-cust-add-label">Add as a new customer</span>
					</button>
				</div>
			</div>
			<small class="text-danger @if(empty($customer_due)) hide @endif contact_due_text"><strong>@lang('account.customer_due'):</strong> <span>{{$customer_due ?? ''}}</span></small>
			@if(auth()->check() && strcasecmp((string) (auth()->user()->username ?? ''), 'japspos2') === 0)
			{{-- japspos2 only: manual name printed as Bill To on the invoice (customer details are not printed) --}}
			<div class="pos-manual-bill-name" style="margin-top:6px;">
				<input type="text" name="invoice_bill_name" id="invoice_bill_name" class="form-control" maxlength="191" autocomplete="off" placeholder="Bill To name (prints on invoice)" value="{{ $transaction->invoice_bill_name ?? '' }}">
			</div>
			@endif
		</div>
	</div>
	@if(!empty($pos_settings['hide_product_suggestion']))
	<div class="col-md-12">
		<div class="form-group">
			@include('sale_pos.partials.pos_product_search_input', [
				'search_disabled' => false,
				'search_autofocus' => true,
			])
		</div>
	</div>
	@endif
</div>
<div class="row pos-cart-meta">
	@if(!empty($pos_settings['show_invoice_layout']))
	<div class="col-md-4 pos-cart-meta-extra">
		<div class="form-group">
		{!! Form::select('invoice_layout_id', 
					$invoice_layouts, $transaction->location->invoice_layout_id, ['class' => 'form-control select2', 'placeholder' => __('lang_v1.select_invoice_layout'), 'id' => 'invoice_layout_id']); !!}
		</div>
	</div>
	@endif
	<input type="hidden" name="pay_term_number" id="pay_term_number" value="{{$transaction->pay_term_number}}">
	<input type="hidden" name="pay_term_type" id="pay_term_type" value="{{$transaction->pay_term_type}}">
	
	@if(!empty($commission_agent))
		@php
			$is_commission_agent_required = !empty($pos_settings['is_commission_agent_required']);
		@endphp
		<div class="col-sm-4 pos-cart-meta-extra">
			<div class="form-group">
			{!! Form::select('commission_agent', 
						$commission_agent, $transaction->commission_agent, ['class' => 'form-control select2', 'placeholder' => __('lang_v1.commission_agent'), 'id' => 'commission_agent', 'required' => $is_commission_agent_required]); !!}
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
					{!! Form::text('transaction_date', @format_date($transaction->transaction_date), ['class' => 'form-control', 'readonly', 'required', 'id' => 'transaction_date']); !!}
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
					{!! Form::text('exchange_rate', @num_format($transaction->exchange_rate), ['class' => 'form-control input-sm input_number', 'placeholder' => __('lang_v1.currency_exchange_rate'), 'id' => 'exchange_rate']); !!}
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
						$selected_price_group = !empty($transaction->selling_price_group_id) && array_key_exists($transaction->selling_price_group_id, $price_groups)
							? $transaction->selling_price_group_id
							: ((!empty($default_price_group_id) && array_key_exists($default_price_group_id, $price_groups)) ? $default_price_group_id : null);
					@endphp
					{!! Form::hidden('hidden_price_group', key($price_groups), ['id' => 'hidden_price_group']) !!}
					{!! Form::select('price_group', $price_groups, $selected_price_group, ['class' => 'form-control select2', 'id' => 'price_group']); !!}
				</div>
			</div>
		</div>
	@elseif(!empty($transaction->selling_price_group_id))
		<div class="col-md-6 col-sm-6 pos-cart-meta-price">
			<div class="form-group">
				{!! Form::label('price_group_text', __('lang_v1.selling_price_group') . ':', ['class' => 'pos-cart-meta-label']) !!}
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fas fa-money-bill-alt"></i>
					</span>
					{!! Form::hidden('price_group', $transaction->selling_price_group_id, ['id' => 'price_group']) !!}
					{!! Form::text('price_group_text', optional($transaction->price_group)->name, ['class' => 'form-control', 'readonly']); !!}
				</div>
			</div>
		</div>
	@else
		@if(!empty($price_groups))
			@php reset($price_groups); @endphp
			{!! Form::hidden('price_group', key($price_groups), ['id' => 'price_group']) !!}
		@elseif(!empty($transaction->selling_price_group_id))
			{!! Form::hidden('price_group', $transaction->selling_price_group_id, ['id' => 'price_group']) !!}
		@endif
	@endif

	@if(in_array('types_of_service', $enabled_modules) && !empty($transaction->types_of_service))
		<div class="col-md-4 col-sm-6">
			<div class="form-group">
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fas fa-external-link-square-alt text-primary service_modal_btn"></i>
					</span>
					{!! Form::text('types_of_service_text', $transaction->types_of_service->name, ['class' => 'form-control', 'readonly']); !!}

					{!! Form::hidden('types_of_service_id', $transaction->types_of_service_id, ['id' => 'types_of_service_id']) !!}
					<span class="input-group-addon">
						@show_tooltip(__('lang_v1.types_of_service_help'))
					</span> 
				</div>
				<small><p class="help-block @if(empty($transaction->selling_price_group_id)) hide @endif" id="price_group_text">@lang('lang_v1.price_group'): <span>@if(!empty($transaction->selling_price_group_id)){{$transaction->price_group->name}}@endif</span></p></small>
			</div>
		</div>
		<div class="modal fade types_of_service_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
			@if(!empty($transaction->types_of_service))
				@include('types_of_service.pos_form_modal', ['types_of_service' => $transaction->types_of_service])
			@endif
		</div>
	@endif
	@if($transaction->status == 'draft' && !empty($pos_settings['show_invoice_scheme']))
		<div class="col-sm-3">
			<div class="form-group">
				{!! Form::select('invoice_scheme_id', $invoice_schemes, $default_invoice_schemes->id, ['class' => 'form-control', 'placeholder' => __('lang_v1.select_invoice_scheme')]); !!}
			</div>
		</div>
	@endif
	<!-- Call restaurant module if defined -->
    @if(in_array('tables' ,$enabled_modules) || in_array('service_staff' ,$enabled_modules))
    	<span id="restaurant_module_span" 
    		data-transaction_id="{{$transaction->id}}">
      		<div class="col-md-3"></div>
    	</span>
    @endif
	@if(in_array('kitchen' ,$enabled_modules))
		<div class="col-md-3">
			<div class="form-group">
				<div class="checkbox">
				<label>
						{!! Form::checkbox('is_kitchen_order', 1, $transaction->is_kitchen_order, ['class' => 'input-icheck status', 'id' => 'is_kitchen_order']); !!} {{ __('lang_v1.kitchen_order') }}
				</label>
				</div>
			</div>
		</div>
    @endif
    @if(in_array('subscription', $enabled_modules))
		<div class="col-md-4 col-sm-6">
			<label>
              {!! Form::checkbox('is_recurring', 1, $transaction->is_recurring, ['class' => 'input-icheck', 'id' => 'is_recurring']); !!} @lang('lang_v1.subscribe')?
            </label><button type="button" data-toggle="modal" data-target="#recurringInvoiceModal" class="btn btn-link"><i class="fa fa-external-link-square-alt"></i></button>@show_tooltip(__('lang_v1.recurring_invoice_help'))
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
			value="{{count($sell_details)}}">
		@php
			$hide_tax = '';
			if( session()->get('business.enable_inline_tax') == 0 || !empty($pos_settings['disable_line_tax'])){
				$hide_tax = 'hide';
			}
		@endphp
		<table class="table table-condensed table-responsive tw-border-0 tw-table-fixed @if(count($sell_details) > 0) pos-has-rows @endif" id="pos_table">
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
					<th class="text-center pos-th-price tw-sticky tw-top-0 tw-z-10 !tw-bg-[#f8fafc] !tw-text-[#94a3b8] !tw-border-b !tw-border-[#e2e8f0] !tw-border-t-0 !tw-border-l-0 !tw-border-r-0 !tw-px-2 !tw-py-1 !tw-text-[11px] tw-font-bold tw-uppercase tw-tracking-[0.4px] !tw-leading-[1.2] tw-whitespace-nowrap tw-overflow-hidden !tw-align-middle tw-w-auto tw-min-w-[13%]">
						&nbsp;
					</th>
					<th class="text-center pos-th-subtotal tw-sticky tw-top-0 tw-z-10 !tw-bg-[#f8fafc] !tw-text-[#94a3b8] !tw-border-b !tw-border-[#e2e8f0] !tw-border-t-0 !tw-border-l-0 !tw-border-r-0 !tw-px-2 !tw-py-1 !tw-text-[11px] tw-font-bold tw-uppercase tw-tracking-[0.4px] !tw-leading-[1.2] tw-whitespace-nowrap tw-overflow-hidden !tw-align-middle tw-w-auto tw-min-w-[14%]">
						@lang('sale.subtotal')
					</th>
					<th class="pos-th-action tw-sticky tw-top-0 tw-z-10 !tw-bg-[#f8fafc] !tw-border-b !tw-border-[#e2e8f0] !tw-border-t-0 !tw-border-l-0 !tw-border-r-0 !tw-py-1 !tw-pr-2 !tw-pl-0 !tw-text-[11px] tw-font-bold tw-uppercase tw-tracking-[0.4px] !tw-leading-[1.2] tw-whitespace-nowrap tw-overflow-hidden !tw-align-middle tw-w-[52px] !tw-text-center"></th>
				</tr>
			</thead>
			<tbody class="pos-cart-empty-body">
				@if(count($sell_details) === 0)
				<tr class="pos-empty-state-row">
					<td colspan="100" class="!tw-border-0 !tw-p-0">
						<div class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-text-center tw-py-10 md:tw-py-14 tw-px-6 tw-gap-3">
							<div class="tw-w-16 tw-h-16 tw-rounded-full tw-bg-slate-100 tw-flex tw-items-center tw-justify-center tw-text-slate-400">
								<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/><path d="M17 17h-11v-14h-2"/><path d="M6 5l14 1l-1 7h-13"/></svg>
							</div>
							<div class="tw-text-[15px] tw-font-semibold tw-text-slate-600">Your cart is empty</div>
							<div class="tw-text-[13px] tw-text-slate-400 tw-leading-relaxed tw-max-w-sm">Scan a barcode, tap a product tile, or type to search.</div>
						</div>
					</td>
				</tr>
				@endif
			</tbody>
			@foreach($sell_details as $sell_line)
				@include('sale_pos.product_row', 
					['product' => $sell_line, 
					'row_count' => $loop->index, 
					'tax_dropdown' => $taxes, 
					'sub_units' => !empty($sell_line->unit_details) ? $sell_line->unit_details : [],
					'action' => 'edit'
				])
			@endforeach
		</table>
		<style>
			#pos_table:not(.pos-has-rows) thead { display: none !important; }
			#pos_table.pos-has-rows .pos-empty-state-row { display: none !important; }
			#add_pos_sell_form:not(.pos-has-rows) .pos_form_totals .pos_totals_left,
			#edit_pos_sell_form:not(.pos-has-rows) .pos_form_totals .pos_totals_left,
			#add_pos_sell_form:not(.pos-has-rows) .pos_form_totals .pos_totals_right,
			#edit_pos_sell_form:not(.pos-has-rows) .pos_form_totals .pos_totals_right { display: none !important; }
			#add_pos_sell_form:not(.pos-has-rows) .pos_form_totals,
			#edit_pos_sell_form:not(.pos-has-rows) .pos_form_totals {
				display: flex !important;
				flex-direction: column !important;
			}
		</style>
	</div>
</div>