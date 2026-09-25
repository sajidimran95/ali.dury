@if(!session('business.enable_price_tax')) 
  @php
    $default = 0;
    $class = 'hide';
  @endphp
@else
  @php
    $default = null;
    $class = '';
  @endphp
@endif
@php
    $common_settings = session()->get('business.common_settings');
@endphp

<div class="col-sm-12"><br>
@php
    $variation = $product_deatails->variations->first();
@endphp
@if($variation)
    @php
        $is_image_required = !empty($common_settings['is_product_image_required']) && count($variation->media) == 0;
        $action = !empty($action) ? $action : '';
    @endphp
    <div class="pp-price-grid add-product-price-table {{ $class }}">
        <div class="pp-card pp-card--purchase">
            <input type="hidden" name="single_variation_id" value="{{ $variation->id }}">
            <div class="pp-card__head">
                <span class="pp-card__icon"><i class="fa fa-shopping-cart"></i></span>
                <div>
                    <div class="pp-card__title">@lang('product.default_purchase_price')</div>
                    <div class="pp-card__sub">What you pay the supplier</div>
                </div>
            </div>

            <div class="pp-pack js-pack-price-wrap hide">
                <div class="pp-pack__top">
                    <label class="pp-pack__label" for="uom_purchase_pack">@lang('lang_v1.purchase_price_per') <span class="js-pack-unit-label">Box</span></label>
                    <span class="pp-pack__badge">1 <span class="js-pack-unit-label">Box</span> = <span class="js-pack-factor-label">—</span> <span class="js-base-unit-label">Pcs</span></span>
                </div>
                <input type="text" id="uom_purchase_pack" class="form-control input_number" placeholder="0.00" autocomplete="off">
            </div>

            <div class="pp-base">
                <label for="single_dpp">@lang('lang_v1.purchase_price_per') <span class="js-base-unit-label">Pcs</span>:*</label>
                {!! Form::text('single_dpp', @num_format($variation->default_purchase_price), ['class' => 'form-control input-sm dpp input_number', 'id' => 'single_dpp', 'placeholder' => '0.00', 'required']); !!}
            </div>

            <div class="hide">
                {!! Form::text('single_dpp_inc_tax', @num_format($variation->dpp_inc_tax), ['class' => 'form-control input-sm dpp_inc_tax input_number', 'placeholder' => __('product.inc_of_tax'), 'required']); !!}
            </div>
        </div>

        <div class="pp-margin">
            <span class="pp-margin__flow">Cost &rarr; Sell</span>
            <div class="pp-margin__ring" title="{{ __('tooltip.profit_percent') }}">
                <small>Margin</small>
                <div class="pp-margin__value">
                    {!! Form::text('profit_percent', @num_format($variation->profit_percent), ['class' => 'form-control input-sm input_number', 'id' => 'profit_percent', 'required']); !!}
                    <span class="pp-margin__pct">%</span>
                </div>
            </div>
            <div class="pp-margin__profit">
                <span>Profit / <span class="js-base-unit-label">Pcs</span></span>
                <strong id="pp_profit_base">0.00</strong>
            </div>
            <div class="pp-margin__profit pp-profit-pack-row hide">
                <span>Profit / <span class="js-pack-unit-label">Box</span></span>
                <strong id="pp_profit_pack">0.00</strong>
            </div>
        </div>

        <div class="pp-card pp-card--sell">
            <div class="pp-card__head">
                <span class="pp-card__icon"><i class="fa fa-tag"></i></span>
                <div>
                    <div class="pp-card__title">@lang('product.default_selling_price')</div>
                    <div class="pp-card__sub">What the customer pays</div>
                </div>
            </div>

            <div class="pp-pack js-pack-price-wrap hide">
                <div class="pp-pack__top">
                    <label class="pp-pack__label" for="uom_sell_pack">@lang('lang_v1.sell_price_per') <span class="js-pack-unit-label">Box</span></label>
                    <span class="pp-pack__badge">1 <span class="js-pack-unit-label">Box</span> = <span class="js-pack-factor-label">—</span> <span class="js-base-unit-label">Pcs</span></span>
                </div>
                <input type="text" id="uom_sell_pack" class="form-control input_number" placeholder="0.00" autocomplete="off">
            </div>

            <div class="pp-base">
                <label for="single_dsp">@lang('lang_v1.sell_price_per') <span class="js-base-unit-label">Pcs</span> <small class="text-muted">(<span class="dsp_label"></span>)</small></label>
                {!! Form::text('single_dsp', @num_format($variation->default_sell_price), ['class' => 'form-control input-sm dsp input_number', 'placeholder' => __('product.exc_of_tax'), 'id' => 'single_dsp', 'required']); !!}
                {!! Form::text('single_dsp_inc_tax', @num_format($variation->sell_price_inc_tax), ['class' => 'form-control input-sm hide input_number', 'placeholder' => __('product.inc_of_tax'), 'id' => 'single_dsp_inc_tax', 'required']); !!}
            </div>
        </div>

        <div class="pp-card pp-card--image">
            <div class="pp-card__head">
                <span class="pp-card__icon"><i class="fa fa-image"></i></span>
                <div>
                    <div class="pp-card__title">@lang('lang_v1.product_image')</div>
                    <div class="pp-card__sub">Shown on POS and invoices</div>
                </div>
            </div>
            @if($action !== 'duplicate')
                @foreach($variation->media as $media)
                    <div class="img-thumbnail">
                        <span class="badge bg-red delete-media" data-href="{{ action([\App\Http\Controllers\ProductController::class, 'deleteMedia'], ['media_id' => $media->id])}}"><i class="fas fa-times"></i></span>
                        {!! $media->thumbnail() !!}
                    </div>
                @endforeach
            @endif
            <div class="form-group">
                {!! Form::file('variation_images[]', ['class' => 'variation_images', 'accept' => 'image/*', 'multiple', 'required' => $is_image_required]); !!}
                <small><p class="help-block">@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)]) <br> @lang('lang_v1.aspect_ratio_should_be_1_1')</p></small>
            </div>
        </div>
    </div>
@endif
</div>
@include('product.partials.pack_price_row_style')