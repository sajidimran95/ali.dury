@forelse($products as $product)
	@php
		$default_tile = asset('/img/default.png');
		$tile_image = $default_tile;
		if (count($product->media) > 0) {
			$tile_image = $product->media->first()->display_url ?: $default_tile;
		} elseif (!empty($product->product_image)) {
			$img_path = public_path('uploads/' . config('constants.product_img_path') . '/' . $product->product_image);
			$tile_image = is_file($img_path)
				? asset('/uploads/' . config('constants.product_img_path') . '/' . rawurlencode($product->product_image))
				: $default_tile;
		}
		$qty_available = (float) ($product->qty_available ?? 0);
		$is_out_of_stock = !empty($product->enable_stock) && $qty_available <= 0;
		$unit_label = $product->unit ?? 'Pc(s)';
	@endphp
	<div class="col-md-3 col-xs-4 no-print !tw-px-[3px]">
		<div class="product_box tw-w-full tw-mb-1 tw-text-center tw-font-semibold tw-rounded-lg tw-p-1 @if($is_out_of_stock) product_out_of_stock tw-cursor-not-allowed @else tw-cursor-pointer @endif"
			data-variation_id="{{$product->id}}"
			data-enable-stock="{{ !empty($product->enable_stock) ? 1 : 0 }}"
			data-base-qty="{{ $qty_available }}"
			data-unit="{{ $unit_label }}"
			title="{{$product->name}} @if($product->type == 'variable')- {{$product->variation}} @endif {{ '(' . $product->sub_sku . ')'}}@if($is_out_of_stock) — {{ __('lang_v1.item_out_of_stock') }}@endif">

		<div class="image-container tw-mx-auto tw-w-full">
			<img class="product_tile_img" src="{{ $tile_image }}" alt="" loading="lazy"
				onerror="this.onerror=null;this.src='{{ $default_tile }}';">
		</div>

		<div class="text_div">
			@php
				$tile_name = $product->name;
				if ($product->type == 'variable' && !empty($product->variation)) {
					$tile_name .= ' - ' . $product->variation;
				}
			@endphp
			<span class="product_name">{{ $tile_name }}</span>
			@if(!empty($product->sub_sku))
				<span class="product_sku">({{ $product->sub_sku }})</span>
			@endif
			@if($product->enable_stock)
				<span class="product_stock" data-stock-label="{{ $unit_label }}">{{ (int) round($qty_available) }} {{ $unit_label }} in stock</span>
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
