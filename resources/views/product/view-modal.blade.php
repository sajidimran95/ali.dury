<div class="modal-dialog modal-xl" role="document">
	<div class="modal-content">
		<div class="modal-header">
		    <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		      <h4 class="modal-title" id="modalTitle">{{$product->name}}</h4>
	    </div>
	    <div class="modal-body">
      		<div class="row">
      			<div class="col-sm-9">
	      			<div class="col-sm-4 invoice-col">
	      				<b>@lang('product.sku'):</b>
						{{$product->sku }}<br>
						<b>@lang('lang_v1.all_barcodes'):</b>
						@php
							$all_codes = collect([$product->sku])
								->merge($product->variations->pluck('sub_sku'))
								->merge(optional($product->barcodes)->pluck('barcode') ?? collect())
								->map(fn ($c) => trim((string) $c))
								->filter()
								->unique(fn ($c) => mb_strtolower($c))
								->values();
						@endphp
						@if($all_codes->isNotEmpty())
							<span class="d-inline-block" style="display:block; margin-top:2px;">
								@foreach($all_codes as $code)
									<code style="display:inline-block; margin:0 4px 4px 0; padding:2px 6px; background:#f4f4f4; border:1px solid #ddd;">{{ $code }}</code>
								@endforeach
							</span>
						@else
							--
						@endif
						<br>
						@if(optional($product->barcodes)->isNotEmpty())
							<b>@lang('lang_v1.additional_barcodes'):</b>
							@foreach($product->barcodes as $bc)
								<br>
								<code>{{ $bc->barcode }}</code>
								@if($bc->type === 'pack') <span class="label label-default">@lang('lang_v1.barcode_type_pack')</span> @endif
								@if($bc->label) <small class="text-muted">({{ $bc->label }})</small> @endif
							@endforeach
							<br>
						@endif
						<b>@lang('product.brand'): </b>
						{{$product->brand->name ?? '--' }}<br>
						<b>@lang('purchase.supplier'): </b>
						@if(!empty($product->supplier))
							@can('supplier.view')
								<a href="{{ action([\App\Http\Controllers\ContactController::class, 'show'], [$product->supplier->id]) }}" target="_blank">{{ $product->supplier->name ?: $product->supplier->supplier_business_name }}</a>
							@else
								{{ $product->supplier->name ?: $product->supplier->supplier_business_name }}
							@endif
						@else
							--
						@endif
						<br>
						@if(!empty($purchased_from) && count($purchased_from))
							<b>@lang('lang_v1.purchased_from'): </b>
							@foreach($purchased_from as $pf)
								<span class="label bg-gray" style="display:inline-block;margin:1px 2px 1px 0;" title="{{ __('lang_v1.last_purchase') }}: {{ @format_date($pf->last_purchase) }}">{{ $pf->name }} ({{ $pf->purchase_count }})</span>
							@endforeach
							<br>
						@endif
						<b>@lang('product.unit'): </b>
						{{$product->unit->short_name ?? '--' }}<br>
						@if(!empty($pack_unit))
							<b>@lang('lang_v1.packaging_unit'): </b>
							1 {{ $pack_unit['name'] }} = {{ @format_quantity($pack_unit['factor']) }} {{ $product->unit->short_name ?? '' }}<br>
						@endif
						<b>@lang('product.barcode_type'): </b>
						{{$product->barcode_type ?? '--' }}
						@php 
    						$custom_labels = json_decode(session('business.custom_labels'), true);
						@endphp

                        @for($i = 1; $i <= 20; $i++)
                            @php
                                $db_field = 'product_custom_field' . $i;
                                $label = 'custom_field_' .$i;
                            @endphp

                            @if(!empty($product->$db_field))
                                <br/>
                                <b>{{ $custom_labels['product'][$label] ?? '' }}: </b>
                                {{$product->$db_field }}
                            @endif
                        @endfor
						
						<br>
						<strong>@lang('lang_v1.available_in_locations'):</strong>
						@if(count($product->product_locations) > 0)
							{{implode(', ', $product->product_locations->pluck('name')->toArray())}}
						@else
							@lang('lang_v1.none')
						@endif
						@if(!empty($product->media->first())) <br>
							<strong>@lang('lang_v1.product_brochure'):</strong>
							<a href="{{$product->media->first()->display_url}}" download="{{$product->media->first()->display_name}}">
								<span class="label label-info">
									<i class="fas fa-download"></i>
									{{$product->media->first()->display_name}}
								</span>
							</a>
						@endif
	      			</div>

	      			<div class="col-sm-4 invoice-col">
						<b>@lang('product.category'): </b>
						{{$product->category->name ?? '--' }}<br>
						<b>@lang('product.sub_category'): </b>
						{{$product->sub_category->name ?? '--' }}<br>	
						
						<b>@lang('product.manage_stock'): </b>
						@if($product->enable_stock)
							@lang('messages.yes')
						@else
							@lang('messages.no')
						@endif
						<br>
						@if($product->enable_stock)
							<b>@lang('product.alert_quantity'): </b>
							{{$product->alert_quantity ?? '--' }}
						@endif

						@if(!empty($product->warranty))
							<br>
							<b>@lang('lang_v1.warranty'): </b>
							{{$product->warranty->display_name }}
						@endif
	      			</div>
					
	      			<div class="col-sm-4 invoice-col">
	      				<b>@lang('product.expires_in'): </b>
	      				@php
	  						$expiry_array = ['months'=>__('product.months'), 'days'=>__('product.days'), '' =>__('product.not_applicable') ];
	  					@endphp
	      				@if(!empty($product->expiry_period) && !empty($product->expiry_period_type))
							{{$product->expiry_period}} {{$expiry_array[$product->expiry_period_type]}}
						@else
							{{$expiry_array['']}}
	      				@endif
	      				<br>
						@if($product->weight)
							<b>@lang('lang_v1.weight'): </b>
							{{$product->weight }}<br>
						@endif
						<b>@lang('product.applicable_tax'): </b>
						{{$product->product_tax->name ?? __('lang_v1.none') }}<br>
						@php
							$tax_type = ['inclusive' => __('product.inclusive'), 'exclusive' => __('product.exclusive')];
						@endphp
						<b>@lang('product.selling_price_tax_type'): </b>
						{{$tax_type[$product->tax_type]  }}<br>
						<b>@lang('product.product_type'): </b>
						@lang('lang_v1.' . $product->type)
						
	      			</div>
	      			<div class="clearfix"></div>
	      			<br>
      				<div class="col-sm-12">
      					{!! $product->product_description !!}
      				</div>
	      		</div>
      			<div class="col-sm-3 col-md-3 invoice-col">
      				<div class="thumbnail">
      					<img src="{{$product->image_url}}" alt="Product image">
      				</div>
      			</div>
      		</div>
      		@if($rack_details->count())
      		@if(session('business.enable_racks') || session('business.enable_row') || session('business.enable_position'))
      			<div class="row">
      				<div class="col-md-12">
      					<h4>@lang('lang_v1.rack_details'):</h4>
      				</div>
      				<div class="col-md-12">
      					<div class="table-responsive">
      					<table class="table table-condensed bg-gray">
      						<tr class="bg-green">
      							<th>@lang('business.location')</th>
      							@if(session('business.enable_racks'))
      								<th>@lang('lang_v1.rack')</th>
      							@endif
      							@if(session('business.enable_row'))
      								<th>@lang('lang_v1.row')</th>
      							@endif
      							@if(session('business.enable_position'))
      								<th>@lang('lang_v1.position')</th>
      							@endif
      							</tr>
      						@foreach($rack_details as $rd)
      							<tr>
	      							<td>{{$rd->name}}</td>
	      							@if(session('business.enable_racks'))
	      								<td>{{$rd->rack}}</td>
	      							@endif
	      							@if(session('business.enable_row'))
	      								<td>{{$rd->row}}</td>
	      							@endif
	      							@if(session('business.enable_position'))
	      								<td>{{$rd->position}}</td>
	      							@endif
      							</tr>
      						@endforeach
      					</table>
      					</div>
      				</div>
      			</div>
      		@endif
      		@endif
      		@if($product->type == 'single')
      			@include('product.partials.single_product_details')
      		@elseif($product->type == 'variable')
      			@include('product.partials.variable_product_details')
      		@elseif($product->type == 'combo')
      			@include('product.partials.combo_product_details')
      		@endif
      		@if($product->enable_stock == 1)
	      		<div class="row">
	      			<div class="col-md-12">
	      				<strong>@lang('lang_v1.product_stock_details')</strong>
	      			</div>
	      			<div class="col-md-12" id="view_product_stock_details" data-product_id="{{$product->id}}">
	      			</div>
	      		</div>
      		@endif
      	</div>
      	<div class="modal-footer">
      		<a href="{{ action([\App\Http\Controllers\LabelsController::class, 'show']) }}?product_id={{ $product->id }}"
      			class="tw-dw-btn tw-dw-btn-info tw-text-white no-print"
      			title="{{ __('lang_v1.label_help') }}">
      			<i class="fa fa-barcode"></i> @lang('barcode.print_labels')
      		</a>
      		@can('purchase.create')
      		<a href="{{ action([\App\Http\Controllers\PurchaseController::class, 'create'], array_filter(['product_id' => $product->id, 'supplier_id' => $product->supplier_id])) }}"
      			class="tw-dw-btn tw-dw-btn-success tw-text-white no-print">
      			<i class="fas fa-cart-plus"></i> @lang('purchase.add_purchase')
      		</a>
      		@endcan
      		<button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white no-print" 
	        aria-label="Print" 
	          onclick="$(this).closest('div.modal').printThis();">
	        <i class="fa fa-print"></i> @lang( 'messages.print' )
	      </button>
	      	<button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white no-print" data-dismiss="modal">@lang( 'messages.close' )</button>
	    </div>
	</div>
</div>
