@foreach($featured_products as $variation)
	@php
		$enable_stock = !empty($variation->product) ? $variation->product->enable_stock : 0;
		$tile_name = $variation->product->name;
		if ($variation->product->type == 'variable') {
			$tile_name .= ' - ' . $variation->name;
		}
		$tile_image = null;
		if (count($variation->media) > 0) {
			$tile_image = $variation->media->first()->display_url;
		} elseif (!empty($variation->product->image_url)) {
			$tile_image = $variation->product->image_url;
		}
	@endphp
	<div class="col-md-3 col-xs-4 no-print !tw-px-[3px]">
		<div class="product_box tw-w-full tw-mb-1 tw-text-center tw-cursor-pointer tw-font-semibold tw-rounded-lg tw-p-1 @if(empty($tile_image)) product_box--no-image @endif @if($enable_stock && $variation->qty_available <= 0) product_out_of_stock @endif" data-toggle="tooltip" data-placement="bottom" data-variation_id="{{$variation->id}}" title="{{$variation->full_name}}">

		@if($enable_stock)
			<span class="pos-tile-stock">{{ @num_format($variation->qty_available) }}</span>
		@endif

		@if(!empty($tile_image))
			<div class="image-container tw-mx-auto tw-w-full"
				style="background-image: url({{ $tile_image }});">
			</div>
		@endif

		<div class="text_div">
			<small class="text text-muted">{{$tile_name}}</small>
			<span class="product_price">@format_currency($variation->sell_price_inc_tax)</span>
		</div>

		</div>
	</div>
@endforeach
