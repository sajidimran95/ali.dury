@php
	$pos_unit_price = !empty($product->unit_price_before_discount) ? $product->unit_price_before_discount : $product->default_sell_price;
	$currency_symbol = session('currency.symbol', '$');
@endphp
<div class="modal-dialog" role="document">
	<div class="modal-content pos-line-details">
		<div class="modal-header pos-line-details-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<div class="pos-line-details-tabs" role="tablist">
				<span class="pos-line-details-tab is-active">Details</span>
			</div>
			<h4 class="modal-title hide" id="myModalLabel">{{ $product->product_name }} - {{$product->sub_sku}}</h4>
		</div>
		<div class="modal-body">
			<div class="pos-line-details-grid">
				<div class="pos-line-details-field">
					<label for="pos_line_qty_field_{{$row_count}}">@lang('sale.qty')</label>
					<input type="text"
						id="pos_line_qty_field_{{$row_count}}"
						class="form-control input_number pos-line-edit-qty mousetrap"
						value="{{@format_quantity($product->quantity_ordered)}}"
						autocomplete="off">
				</div>
				<div class="pos-line-details-field @if(!auth()->user()->can('edit_product_price_from_sale_screen')) hide @endif">
					<label for="pos_unit_price_field_{{$row_count}}">@lang('sale.unit_price')</label>
					<div class="pos-line-details-affix">
						<span class="pos-line-details-prefix">{{ $currency_symbol }}</span>
						<input type="text"
							id="pos_unit_price_field_{{$row_count}}"
							name="products[{{$row_count}}][unit_price]"
							class="form-control pos_unit_price input_number mousetrap"
							value="{{@num_format($pos_unit_price)}}"
							@if(!empty($pos_settings['enable_msp'])) data-rule-min-value="{{$pos_unit_price}}" data-msg-min-value="{{__('lang_v1.minimum_selling_price_error_msg', ['price' => @num_format($pos_unit_price)])}}" @endif>
					</div>
				</div>
				@if(!auth()->user()->can('edit_product_price_from_sale_screen'))
				<div class="pos-line-details-field">
					<label>@lang('sale.unit_price')</label>
					<div class="pos-line-details-readonly">{{ $currency_symbol }} {{@num_format($pos_unit_price)}}</div>
				</div>
				@endif
				<div class="pos-line-details-field @if(!$edit_discount) hide @endif">
					<label for="pos_line_discount_field_{{$row_count}}">@lang('sale.discount')</label>
					<select name="products[{{$row_count}}][line_discount_type]" class="form-control row_discount_type pos-line-discount-type-select">
						<option value="percentage" @if($discount_type != 'fixed') selected @endif>{{ __('lang_v1.percentage') }}</option>
						<option value="fixed" @if($discount_type == 'fixed') selected @endif>{{ __('lang_v1.fixed') }}</option>
					</select>
					<div class="pos-line-details-affix">
						<input type="text"
							id="pos_line_discount_field_{{$row_count}}"
							name="products[{{$row_count}}][line_discount_amount]"
							class="form-control input_number row_discount_amount"
							value="{{@num_format($discount_amount)}}">
						<span class="pos-line-details-suffix pos-line-discount-suffix">{{ $discount_type == 'fixed' ? $currency_symbol : '%' }}</span>
					</div>
				</div>
			</div>

			@if(!empty($discount))
				<p class="help-block pos-line-details-help">{!! __('lang_v1.applied_discount_text', ['discount_name' => $discount->name, 'starts_at' => $discount->formated_starts_at, 'ends_at' => $discount->formated_ends_at]) !!}</p>
			@endif

			<div class="pos-line-details-field pos-line-details-note {{$hide_tax}}">
				<label>@lang('sale.tax')</label>
				{!! Form::hidden("products[$row_count][item_tax]", @num_format($item_tax), ['class' => 'item_tax']); !!}
				{!! Form::select("products[$row_count][tax_id]", $tax_dropdown['tax_rates'], $tax_id, ['placeholder' => 'Select', 'class' => 'form-control tax_id'], $tax_dropdown['attributes']); !!}
			</div>

			@if(!empty($warranties))
				<div class="pos-line-details-field pos-line-details-note">
					<label>@lang('lang_v1.warranty')</label>
					{!! Form::select("products[$row_count][warranty_id]", $warranties, $warranty_id, ['placeholder' => __('messages.please_select'), 'class' => 'form-control']); !!}
				</div>
			@endif

			<div class="pos-line-details-field pos-line-details-note">
				<label for="pos_line_note_field_{{$row_count}}">Note</label>
				<textarea id="pos_line_note_field_{{$row_count}}"
					class="form-control"
					name="products[{{$row_count}}][sell_line_note]"
					rows="2"
					placeholder="Enter a note">{{$sell_line_note}}</textarea>
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">@lang('messages.close')</button>
			<button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white pos-row-edit-price-update">@lang('messages.update')</button>
		</div>
	</div>
</div>
