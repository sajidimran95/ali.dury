@forelse($products as $product)
	@php
		$tile_image = null;
		if (count($product->media) > 0) {
			$tile_image = $product->media->first()->display_url;
		} elseif (!empty($product->product_image)) {
			$tile_image = asset('/uploads/img/' . rawurlencode($product->product_image));
		}
		$is_out_of_stock = !empty($product->enable_stock) && (float) ($product->qty_available ?? 0) <= 0;
		$unit_label = $product->unit ?? 'Pc(s)';
	@endphp
	<div class="col-md-3 col-xs-4 no-print !tw-px-[3px]">
		<div class="product_box tw-w-full tw-mb-1 tw-text-center tw-font-semibold tw-rounded-lg tw-p-1 @if($is_out_of_stock) product_out_of_stock tw-cursor-not-allowed @else tw-cursor-pointer @endif" data-variation_id="{{$product->id}}" title="{{$product->name}} @if($product->type == 'variable')- {{$product->variation}} @endif {{ '(' . $product->sub_sku . ')'}}@if($is_out_of_stock) — {{ __('lang_v1.item_out_of_stock') }}@endif">

		<div class="image-container tw-mx-auto tw-w-full @if(empty($tile_image)) image-container--placeholder @endif"
			@if(!empty($tile_image)) style="background-image: url({{ $tile_image }});" @endif>
		</div>

		<div class="text_div">
			@if(!empty($product->sub_sku))
				<span class="product_sku">({{ $product->sub_sku }})</span>
			@endif
			@if($product->enable_stock)
				<span class="product_stock">{{ (int) round((float) ($product->qty_available ?? 0)) }} {{ $unit_label }} in stock</span>
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
