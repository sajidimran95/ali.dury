@php
    $os_locations = $locations ?? $business_locations ?? [];
    $os_enable_expiry = !empty($enable_expiry);
    $os_enable_lot = !empty($enable_lot);
    $os_required = !empty($opening_stock_required);
@endphp
@can('product.opening_stock')
<div class="row" id="product_opening_stock_div">
    <div class="col-sm-12">
        <div class="box box-solid" style="border: 1px solid #d2d6de; margin-bottom: 10px;">
            <div class="box-header with-border" style="padding: 8px 15px;">
                <h4 class="box-title" style="font-size: 15px; margin: 0;">
                    <i class="fa fa-database"></i> @lang('lang_v1.add_opening_stock')
                </h4>
            </div>
            <div class="box-body" style="padding: 10px 15px;">
                <p class="help-block text-muted" style="margin-top:0;">
                    @lang('lang_v1.inline_opening_stock_help')
                </p>
                <div class="table-responsive">
                    <table class="table table-condensed table-th-green" id="product_opening_stock_table">
                        <thead>
                            <tr>
                                <th>@lang('sale.location')</th>
                                <th class="js-os-pack-col hide">@lang('lang_v1.quantity') <small class="text-muted">(<span class="js-pack-unit-label">Box</span>)</small></th>
                                <th>@lang('lang_v1.quantity') <small class="text-muted">(<span class="js-base-unit-label">Pcs</span>)</small></th>
                                <th>@lang('lang_v1.purchase_price_per') <span class="js-base-unit-label">Pcs</span></th>
                                @if($os_enable_expiry)
                                    <th>@lang('lang_v1.expiry_date')</th>
                                @endif
                                @if($os_enable_lot)
                                    <th>@lang('lang_v1.lot_number')</th>
                                @endif
                                <th>@lang('purchase.subtotal_before_tax')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($os_locations as $key => $value)
                                <tr>
                                    <td>{{ $value }}</td>
                                    <td class="js-os-pack-col hide">
                                        <input type="text" class="form-control input-sm input_number os_pack_qty" placeholder="0" autocomplete="off">
                                    </td>
                                    <td>
                                        {!! Form::text('opening_stock[' . $key . '][quantity]', 0, [
                                            'class' => 'form-control input-sm input_number purchase_quantity',
                                            'required' => $os_required,
                                            'placeholder' => '0',
                                        ]); !!}
                                    </td>
                                    <td>
                                        {!! Form::text('opening_stock[' . $key . '][purchase_price]', null, [
                                            'class' => 'form-control input-sm input_number unit_price os_purchase_price',
                                            'required' => $os_required,
                                            'placeholder' => '0.00',
                                        ]); !!}
                                    </td>
                                    @if($os_enable_expiry)
                                        <td>
                                            {!! Form::text('opening_stock[' . $key . '][exp_date]', null, [
                                                'class' => 'form-control input-sm os_exp_date',
                                                'readonly',
                                                'placeholder' => __('lang_v1.expiry_date'),
                                            ]); !!}
                                        </td>
                                    @endif
                                    @if($os_enable_lot)
                                        <td>
                                            {!! Form::text('opening_stock[' . $key . '][lot_number]', null, [
                                                'class' => 'form-control input-sm',
                                                'placeholder' => __('lang_v1.lot_number'),
                                            ]); !!}
                                        </td>
                                    @endif
                                    <td>
                                        <span class="row_subtotal_before_tax">0</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-muted">@lang('lang_v1.no_location_available')</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endcan
