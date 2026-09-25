@if(!session('business.enable_price_tax'))
  @php
    $default = 0;
    $class = 'hide';
  @endphp
@else
  @php
    $default = 0;
    $class = '';
  @endphp
@endif

{{-- Carried over from JapsAI invoice extraction (quick-add flow), if present. --}}
@php
  $dpp_default = ! empty($purchase_price) ? $purchase_price : $default;
@endphp

<div class="table-responsive">
    <table class="table table-bordered add-product-price-table table-condensed {{$class}}">
        <tr>
          <th>@lang('product.default_purchase_price')</th>
          <th>@lang('product.profit_percent') @show_tooltip(__('tooltip.profit_percent'))</th>
          <th>@lang('product.default_selling_price')</th>
          @if(empty($quick_add))
            <th>@lang('lang_v1.product_image')</th>
          @endif
        </tr>
        <tr>
          <td>
            <div class="col-sm-12">
              <label for="single_dpp">@lang('lang_v1.purchase_price_per') <span class="js-base-unit-label">Pcs</span>:*</label>

              {!! Form::text('single_dpp', $dpp_default !== null ? $dpp_default : 0, ['class' => 'form-control input-sm dpp input_number', 'id' => 'single_dpp', 'placeholder' => '0.00', 'required']); !!}
            </div>

            <div class="col-sm-6 hide">
              {!! Form::label('single_dpp_inc_tax', trans('product.inc_of_tax') . ':*') !!}

              {!! Form::text('single_dpp_inc_tax', $dpp_default !== null ? $dpp_default : 0, ['class' => 'form-control input-sm dpp_inc_tax input_number', 'placeholder' => __('product.inc_of_tax'), 'required']); !!}
            </div>

            <div class="clearfix"></div>
            <div class="col-sm-12 js-pack-price-wrap pack-price-row hide">
              <div class="input-group input-group-sm">
                <span class="input-group-addon">@lang('lang_v1.purchase_price_per') <span class="js-pack-unit-label">Box</span></span>
                <input type="text" id="uom_purchase_pack" class="form-control input_number" placeholder="0.00" autocomplete="off">
              </div>
            </div>
          </td>

          <td>
            <br/>
            {!! Form::text('profit_percent', @num_format($profit_percent), ['class' => 'form-control input-sm input_number', 'id' => 'profit_percent', 'required']); !!}
          </td>

          <td>
            <label>@lang('lang_v1.sell_price_per') <span class="js-base-unit-label">Pcs</span> <small class="text-muted">(<span class="dsp_label">@lang('product.exc_of_tax')</span>)</small></label>
            {!! Form::text('single_dsp', $default !== null ? $default : 0, ['class' => 'form-control input-sm dsp input_number', 'placeholder' => __('product.exc_of_tax'), 'id' => 'single_dsp', 'required']); !!}

            {!! Form::text('single_dsp_inc_tax', $default !== null ? $default : 0, ['class' => 'form-control input-sm hide input_number', 'placeholder' => __('product.inc_of_tax'), 'id' => 'single_dsp_inc_tax', 'required']); !!}

            <div class="js-pack-price-wrap pack-price-row hide">
              <div class="input-group input-group-sm">
                <span class="input-group-addon">@lang('lang_v1.sell_price_per') <span class="js-pack-unit-label">Box</span></span>
                <input type="text" id="uom_sell_pack" class="form-control input_number" placeholder="0.00" autocomplete="off">
              </div>
            </div>
          </td>
          @if(empty($quick_add))
          <td>
              <div class="form-group">
                {!! Form::label('variation_images', __('lang_v1.product_image') . ':') !!}
                {!! Form::file('variation_images[]', ['class' => 'variation_images', 
                    'accept' => 'image/*', 'multiple']); !!}
                <small><p class="help-block">@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)]) <br> @lang('lang_v1.aspect_ratio_should_be_1_1')</p></small>
              </div>
          </td>
          @endif
        </tr>
    </table>
</div>
@include('product.partials.pack_price_row_style')