@foreach($featured_products as $variation)
	@php
		$enable_stock = !empty($variation->product) ? $variation->product->enable_stock : 0;
		$tile_image = null;
		if (count($variation->media) > 0) {
			$tile_image = $variation->media->first()->display_url;
		} elseif (!empty($variation->product->image_url)) {
			$tile_image = $variation->product->image_url;
		}
		$qty_available = (float) ($variation->qty_available ?? 0);
		$is_out_of_stock = !empty($enable_stock) && $qty_available <= 0;
		$unit_label = optional(optional($variation->product)->unit)->short_name ?? 'Pc(s)';
	@endphp
	<div class="col-md-3 col-xs-4 no-print !tw-px-[3px]">
		<div class="product_box tw-w-full tw-mb-1 tw-text-center tw-font-semibold tw-rounded-lg tw-p-1 @if($is_out_of_stock) product_out_of_stock tw-cursor-not-allowed @else tw-cursor-pointer @endif"
			data-toggle="tooltip"
			data-placement="bottom"
			data-variation_id="{{$variation->id}}"
			data-enable-stock="{{ !empty($enable_stock) ? 1 : 0 }}"
			data-base-qty="{{ $qty_available }}"
			data-unit="{{ $unit_label }}"
			title="{{$variation->full_name}}@if($is_out_of_stock) — {{ __('lang_v1.item_out_of_stock') }}@endif">

		<div class="image-container tw-mx-auto tw-w-full @if(empty($tile_image)) image-container--placeholder @endif">
			@if(!empty($tile_image))
				<img class="product_tile_img" src="{{ $tile_image }}" alt="" loading="lazy"
					onerror="this.onerror=null;var p=this.parentElement;this.remove();if(p){p.classList.add('image-container--placeholder');}">
			@endif
		</div>

		<div class="text_div">
			@if(!empty($variation->sub_sku))
				<span class="product_sku">({{ $variation->sub_sku }})</span>
			@endif
			@if($enable_stock)
				<span class="product_stock" data-stock-label="{{ $unit_label }}">{{ (int) round($qty_available) }} {{ $unit_label }} in stock</span>
			@endif
		</div>

		</div>
	</div>
@endforeach
