@php
	$os_pack_factor = !empty($pack_unit['factor']) ? (float) $pack_unit['factor'] : 0;
	$os_pack_name = $pack_unit['name'] ?? '';
@endphp
<div class="row">
	<div class="col-sm-12">
		@if($os_pack_factor > 0)
			<p class="help-block"><i class="fa fa-info-circle"></i> 1 {{ $os_pack_name }} = {{ @format_quantity($os_pack_factor) }} {{ $product->unit->short_name }} — @lang('lang_v1.os_pack_help', ['pack' => $os_pack_name, 'unit' => $product->unit->short_name])</p>
		@endif
		@forelse($locations as $key => $value)
		<div class="box box-solid">
			<div class="box-header">
	            <h3 class="box-title">@lang('sale.location'): {{$value}}</h3>
	        </div>
			<div class="box-body">
				<div class="row tw-overflow-scroll">
					<div class="col-sm-12">
						<table class="table table-condensed table-bordered text-center table-responsive table-striped add_opening_stock_table" data-pack-factor="{{ $os_pack_factor }}">
								<thead>
								<tr class="bg-green">
									<th>@lang( 'product.product_name' )</th>
									<th>@lang( 'lang_v1.opening_stock_qty' )</th>
									<th>@if($os_pack_factor > 0) @lang('lang_v1.purchase_price_per') {{ $product->unit->short_name }} / {{ $os_pack_name }} @else @lang( 'purchase.unit_cost_before_tax' ) @endif</th>
									@if($enable_expiry == 1 && $product->enable_stock == 1)
										<th>Exp. Date</th>
									@endif
									@if($enable_lot == 1)
										<th>@lang( 'lang_v1.lot_number' )</th>
									@endif
									<th>@lang( 'purchase.subtotal_before_tax' )</th>
									<th>@lang( 'lang_v1.date' )</th>
									<th>@lang( 'brand.note' )</th>
									<th>&nbsp;</th>
								</tr>
								</thead>
								<tbody>
@php
	$subtotal = 0;
@endphp
@foreach($product->variations as $variation)
	@if(empty($purchases[$key][$variation->id]))
		@php
			$purchases[$key][$variation->id][] = ['quantity' => 0, 
			'purchase_price' => $variation->default_purchase_price,
			'purchase_line_id' => null,
			'lot_number' => null,
			'transaction_date' => null,
			'purchase_line_note' => null,
			'secondary_unit_quantity' => 0
			]
		@endphp
	@endif

@foreach($purchases[$key][$variation->id] as $sub_key => $var)
	@php

	$purchase_line_id = $var['purchase_line_id'];

	$qty = $var['quantity'];

	$purcahse_price = $var['purchase_price'];

	$row_total = $qty * $purcahse_price;

	$subtotal += $row_total;
	$lot_number = $var['lot_number'];
	$transaction_date = $var['transaction_date'];
	$purchase_line_note = $var['purchase_line_note'];
	@endphp

<tr>
	<td>
		<strong>{{ $product->name }}</strong> @if( $product->type == 'variable' ) (<b>{{ $variation->product_variation->name }}</b> : {{ $variation->name }}) @endif
		@if($loop->index == 0)
			@php
				$os_current = (float) optional($variation->variation_location_details->firstWhere('location_id', $key))->qty_available;
			@endphp
			<div class="os-current-stock">
				@lang('lang_v1.current_stock'):
				<b>{{ @format_quantity($os_current) }} {{ $product->unit->short_name }}</b>
				@if($os_pack_factor > 0)
					<span class="text-muted">({{ @format_quantity($os_current / $os_pack_factor) }} {{ $os_pack_name }})</span>
				@endif
			</div>
		@endif

		@if(!empty($purchase_line_id))
			{!! Form::hidden('stocks[' . $key . '][' . $variation->id . '][' . $sub_key . '][purchase_line_id]', $purchase_line_id); !!}
		@endif
	</td>
	<td>
		@if($os_pack_factor > 0)
		<div class="input-group" style="margin-bottom:4px;">
		  <input type="text" class="form-control input-sm input_number os_pack_qty" value="{{ @format_quantity($qty / $os_pack_factor) }}" autocomplete="off">
		  <span class="input-group-addon">{{ $os_pack_name }}</span>
		</div>
		@endif
		<div class="input-group">
		  {!! Form::text('stocks[' . $key . '][' . $variation->id . '][' . $sub_key . '][quantity]', @format_quantity($qty) , ['class' => 'form-control input-sm input_number purchase_quantity input_quantity', 'required']); !!}
		  <span class="input-group-addon">
		    {{ $product->unit->short_name }}
		  </span>
		</div>
		@if(!empty($product->second_unit))
			<br>
            <span>
            @lang('lang_v1.quantity_in_second_unit', ['unit' => $product->second_unit->short_name])*:</span><br>
            {!! Form::text('stocks[' . $key . '][' . $variation->id . '][' . $sub_key . '][secondary_unit_quantity]', @format_quantity($var['secondary_unit_quantity']) , ['class' => 'form-control input-sm input_number input_quantity', 'required']); !!}
		@endif
	</td>
<td>
	<div class="input-group">
		{!! Form::text('stocks[' . $key . '][' . $variation->id . '][' . $sub_key . '][purchase_price]', @num_format($purcahse_price) , ['class' => 'form-control input-sm input_number unit_price', 'required']); !!}
		@if($os_pack_factor > 0)<span class="input-group-addon">/ {{ $product->unit->short_name }}</span>@endif
	</div>
	@if($os_pack_factor > 0)
	<div class="input-group" style="margin-top:4px;">
		<input type="text" class="form-control input-sm input_number os_pack_price" value="{{ @num_format($purcahse_price * $os_pack_factor) }}" autocomplete="off">
		<span class="input-group-addon">/ {{ $os_pack_name }}</span>
	</div>
	@endif
</td>

@if($enable_expiry == 1 && $product->enable_stock == 1)
	<td>
		{!! Form::text('stocks[' . $key . '][' . $variation->id . '][' . $sub_key . '][exp_date]', !empty($var['exp_date']) ? @format_date($var['exp_date']) : null , ['class' => 'form-control input-sm os_exp_date', 'readonly']); !!}
	</td>
@endif

@if($enable_lot == 1)
	<td>
		{!! Form::text('stocks[' . $key . '][' . $variation->id . '][' . $sub_key . '][lot_number]', $lot_number , ['class' => 'form-control input-sm']); !!}
	</td>
@endif
	<td>
		<span class="row_subtotal_before_tax">{{@num_format($row_total)}}</span>
	</td>
	<td>
		<div class="input-group date">
		{!! Form::text('stocks[' . $key . '][' . $variation->id . '][' . $sub_key . '][transaction_date]', $transaction_date , ['class' => 'form-control input-sm os_date', 'readonly', 'placeholder' => __('lang_v1.date')]); !!}
		</div>
	</td>
	<td>
		{!! Form::textarea('stocks[' . $key . '][' . $variation->id . '][' . $sub_key . '][purchase_line_note]', $purchase_line_note , ['class' => 'form-control input-sm', 'rows' => 2, 'placeholder' => __('brand.note')]); !!}
	</td>
	<td>
		@if($loop->index == 0)
			<button type="button" class="btn btn-primary btn-sm add_stock_row" title="@lang('lang_v1.add_stock_batch_help')" data-sub-key="{{ count($purchases[$key][$variation->id])}}" 
				data-row-html='<tr>
					<td>
						{{ $product->name }} @if( $product->type == "variable" ) (<b>{{ $variation->product_variation->name }}</b> : {{ $variation->name }}) @endif
					</td>
					<td>
					@if($os_pack_factor > 0)
					<div class="input-group" style="margin-bottom:4px;">
						<input type="text" class="form-control input-sm input_number os_pack_qty" value="0" autocomplete="off">
						<span class="input-group-addon">{{ $os_pack_name }}</span>
					</div>
					@endif
					<div class="input-group">
	              		<input class="form-control input-sm input_number purchase_quantity" required="" name="stocks[{{$key}}][{{$variation->id}}][__subkey__][quantity]" type="text" value="0">
			              <span class="input-group-addon">
			                {{ $product->unit->short_name }}
			              </span>
	        			</div>
					</td>
	<td>
		<input class="form-control input-sm input_number unit_price" required="" name="stocks[{{$key}}][{{$variation->id}}][__subkey__][purchase_price]" type="text" value="{{@num_format($purcahse_price)}}">
		@if($os_pack_factor > 0)
		<div class="input-group" style="margin-top:4px;">
			<input type="text" class="form-control input-sm input_number os_pack_price" value="{{ @num_format($purcahse_price * $os_pack_factor) }}" autocomplete="off">
			<span class="input-group-addon">/ {{ $os_pack_name }}</span>
		</div>
		@endif
	</td>

	@if($enable_expiry == 1 && $product->enable_stock == 1)
	<td>
		<input class="form-control input-sm os_exp_date" required="" name="stocks[{{$key}}][{{$variation->id}}][__subkey__][exp_date]" type="text" readonly>
	</td>
	@endif

	@if($enable_lot == 1)
	<td>
		<input class="form-control input-sm" name="stocks[{{$key}}][{{$variation->id}}][__subkey__][lot_number]" type="text">
	</td>
	@endif
	<td>
		<span class="row_subtotal_before_tax">
			0.00
		</span>
	</td>
	<td>
		<div class="input-group date">
			<input class="form-control input-sm os_date" name="stocks[{{$key}}][{{$variation->id}}][__subkey__][transaction_date]" type="text" readonly>
		</div>
	</td>
	<td>
		<textarea rows="2" class="form-control input-sm" name="stocks[{{$key}}][{{$variation->id}}][__subkey__][purchase_line_note]"></textarea>
	</td>
	<td><button type="button" class="btn btn-default btn-sm text-danger remove_stock_row" title="{{ __('messages.delete') }}"><i class="fa fa-trash"></i></button></td></tr>'
	><i class="fa fa-plus"></i> @lang('lang_v1.add_stock_batch')</button>
	@else
		&nbsp;
	@endif
			</td>
			</tr>
		@endforeach
	@endforeach
								</tbody>
								<tfoot>
								<tr>
									<td colspan="{{ 3 + (($enable_expiry == 1 && $product->enable_stock == 1) ? 1 : 0) + ($enable_lot == 1 ? 1 : 0) }}"></td>
									<td><strong>@lang( 'lang_v1.total_amount_exc_tax' ): </strong> <span id="total_subtotal">{{@num_format($subtotal)}}</span>
									<input type="hidden" id="total_subtotal_hidden" value=0>
									</td>
								</tr>
								</tfoot>
						</table>
						
					</div>
				</div>
			</div>
		</div> <!--box end-->
		@empty
    		<h3>@lang( 'lang_v1.product_not_assigned_to_any_location' )</h3>
		@endforelse
	</div>
</div>
<style>
	.add_opening_stock_table > tbody > tr > td { vertical-align: middle; }
	.add_opening_stock_table .os_date { min-width: 150px; }
	.add_opening_stock_table .input-group { min-width: 150px; }
	.add_opening_stock_table textarea { min-width: 140px; resize: vertical; }
	.add_opening_stock_table .os-current-stock { margin-top: 6px; font-size: 12px; color: #334155; white-space: nowrap; }
	.add_opening_stock_table .add_stock_row { white-space: nowrap; }
</style>