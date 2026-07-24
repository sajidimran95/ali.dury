@forelse($products as $product)
	@php
		$tile_name = $product->name;
		if ($product->type == 'variable') {
			$tile_name .= ' - ' . $product->variation;
		}
		$tile_image = null;
		if (count($product->media) > 0) {
			$tile_image = $product->media->first()->display_url;
		} elseif (!empty($product->product_image)) {
			$tile_image = asset('/uploads/img/' . rawurlencode($product->product_image));
		}
	@endphp
	<div class="col-md-3 col-xs-4 no-print !tw-px-[3px]">
		<div class="product_box tw-w-full tw-mb-1 tw-text-center tw-cursor-pointer tw-font-semibold tw-rounded-lg tw-p-1 @if(empty($tile_image)) product_box--no-image @endif @if($product->enable_stock && $product->qty_available <= 0) product_out_of_stock @endif" data-variation_id="{{$product->id}}" title="{{$product->name}} @if($product->type == 'variable')- {{$product->variation}} @endif {{ '(' . $product->sub_sku . ')'}}">

		@if(!empty($tile_image))
			<div class="image-container tw-mx-auto tw-w-full"
				style="background-image: url({{ $tile_image }});">
			</div>
		@endif

		<div class="text_div">
			<small class="text text-muted">{{$tile_name}}</small>
			@if($product->enable_stock)
				<span class="product_stock">{{ @num_format($product->qty_available) }}</span>
			@endif
		</div>

		</div>
	</div>
@empty
	<input type="hidden" id="no_products_found">
	<div class="col-md-12">
		<h4 class="text-center">
			@lang('lang_v1.no_products_to_display')
		</h4>
	</div>
@endforelse
