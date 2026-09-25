@php
    $ph = $packaging_helper ?? [];
    $ph_enabled = !empty($ph['enabled']);
    $ph_name = $ph['name'] ?? 'Box';
    $ph_factor = $ph['factor'] ?? '';
    $ph_unit_id = $ph['unit_id'] ?? '';
    $common_pack_names = ['Box', 'Case', 'Carton', 'Dozen', 'Pack', 'Bundle'];
@endphp
<div class="col-sm-12 product-packaging-uom" id="product_packaging_uom">
    <div class="box box-solid" style="border: 1px solid #d2d6de; margin-bottom: 15px;">
        <div class="box-body" style="padding: 12px 15px;">
            <div class="checkbox" style="margin-top: 0;">
                <label>
                    {!! Form::checkbox('enable_packaging_unit', 1, $ph_enabled, ['class' => 'input-icheck', 'id' => 'enable_packaging_unit']); !!}
                    <strong>@lang('lang_v1.also_sell_packaging_unit')</strong>
                </label>
                @show_tooltip(__('lang_v1.also_sell_packaging_unit_help'))
            </div>

            <div id="packaging_uom_fields" class="@if(!$ph_enabled) hide @endif">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            {!! Form::label('packaging_unit_name', __('lang_v1.packaging_unit') . ':') !!}
                            <select name="packaging_unit_name" id="packaging_unit_name" class="form-control">
                                @foreach($common_pack_names as $n)
                                    <option value="{{ $n }}" @if($ph_name === $n) selected @endif>{{ $n }}</option>
                                @endforeach
                                @if($ph_name && !in_array($ph_name, $common_pack_names, true))
                                    <option value="{{ $ph_name }}" selected>{{ $ph_name }}</option>
                                @endif
                                <option value="__custom__">@lang('lang_v1.custom_packaging_unit')</option>
                            </select>
                            {!! Form::text('packaging_unit_custom_name', null, [
                                'class' => 'form-control hide',
                                'id' => 'packaging_unit_custom_name',
                                'placeholder' => __('lang_v1.custom_packaging_unit_placeholder'),
                                'style' => 'margin-top:8px;',
                            ]); !!}
                            <input type="hidden" name="packaging_unit_id" id="packaging_unit_id" value="{{ $ph_unit_id }}">
                            <input type="hidden" name="packaging_prev_unit_id" value="{{ $ph_unit_id }}">
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            {!! Form::label('packaging_conversion_factor', __('lang_v1.conversion_factor') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">1 <span class="js-pack-unit-label">{{ $ph_name ?: 'Box' }}</span> =</span>
                                {!! Form::text('packaging_conversion_factor', $ph_factor !== '' ? @num_format($ph_factor) : null, [
                                    'class' => 'form-control input_number',
                                    'id' => 'packaging_conversion_factor',
                                    'placeholder' => '10',
                                ]); !!}
                                <span class="input-group-addon"><span class="js-base-unit-label">Pcs</span></span>
                            </div>
                            <p class="help-block" style="margin-bottom:0;">@lang('lang_v1.conversion_factor_example')</p>
                        </div>
                    </div>
                </div>

                <p class="help-block text-muted" style="margin:0;">
                    <i class="fa fa-info-circle"></i> @lang('lang_v1.uom_price_in_table_help')
                </p>
            </div>
        </div>
    </div>
</div>
