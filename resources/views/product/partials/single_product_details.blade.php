<br>
@php
	$base_label = $product->unit->short_name ?? 'Pcs';
	$pack_factor = !empty($pack_unit) ? (float) $pack_unit['factor'] : 0;
	$pack_label = !empty($pack_unit) ? $pack_unit['name'] : null;
	$show_sell_exc = !empty($product->tax);
@endphp
<div class="row">
	<div class="col-md-12">
		<div class="table-responsive">
			<table class="table bg-gray">
				<tr class="bg-green">
					@can('view_purchase_price')
						<th>@lang('lang_v1.purchase_price_per') {{ $base_label }}</th>
						@if($pack_factor > 0)
							<th>@lang('lang_v1.purchase_price_per') {{ $pack_label }}</th>
						@endif
					@endcan
					@can('access_default_selling_price')
						@can('view_purchase_price')
				        	<th>@lang('product.profit_percent')</th>
				        @endcan
				        @if($show_sell_exc)
				        	<th>@lang('lang_v1.sell_price_per') {{ $base_label }} (@lang('product.exc_of_tax'))</th>
				        @endif
				        <th>@lang('lang_v1.sell_price_per') {{ $base_label }} (@lang('product.inc_of_tax'))</th>
				        @if($pack_factor > 0)
				        	<th>@lang('lang_v1.sell_price_per') {{ $pack_label }}</th>
				        @endif
				    @endcan
				    @if(!empty($allowed_group_prices))
			        	<th>@lang('lang_v1.group_prices')</th>
			        @endif
			        <th>@lang('lang_v1.variation_images')</th>
				</tr>
				@foreach($product->variations as $variation)
				@php
					$purchase_base = (float) $variation->default_purchase_price > 0 ? $variation->default_purchase_price : $variation->dpp_inc_tax;
					$sell_pack_base = $product->tax_type === 'exclusive' ? $variation->default_sell_price : $variation->sell_price_inc_tax;
				@endphp
				<tr>
					@can('view_purchase_price')
					<td>
						<span class="display_currency" data-currency_symbol="true">{{ $purchase_base }}</span>
					</td>
					@if($pack_factor > 0)
					<td>
						<span class="display_currency" data-currency_symbol="true">{{ $purchase_base * $pack_factor }}</span>
					</td>
					@endif
					@endcan
					@can('access_default_selling_price')
						@can('view_purchase_price')
						<td>
							{{ @num_format($variation->profit_percent) }}
						</td>
						@endcan
						@if($show_sell_exc)
						<td>
							<span class="display_currency" data-currency_symbol="true">{{ $variation->default_sell_price }}</span>
						</td>
						@endif
						<td>
							<span class="display_currency" data-currency_symbol="true">{{ $variation->sell_price_inc_tax }}</span>
						</td>
						@if($pack_factor > 0)
						<td>
							<span class="display_currency" data-currency_symbol="true">{{ $sell_pack_base * $pack_factor }}</span>
						</td>
						@endif
					@endcan
					@if(!empty($allowed_group_prices))
			        	<td class="td-full-width">
			        		@foreach($allowed_group_prices as $key => $value)
			        			<strong>{{$value}}</strong> - @if(!empty($group_price_details[$variation->id][$key]))
			        				<span class="display_currency" data-currency_symbol="true">{{ $group_price_details[$variation->id][$key]['calculated_price'] }}</span>
			        			@else
			        				0
			        			@endif
			        			<br>
			        		@endforeach
			        	</td>
			        @endif
			        <td>
			        	@foreach($variation->media as $media)
			        		{!! $media->thumbnail([60, 60], 'img-thumbnail') !!}
			        	@endforeach
			        </td>
				</tr>
				@endforeach
			</table>
		</div>
	</div>
</div>
