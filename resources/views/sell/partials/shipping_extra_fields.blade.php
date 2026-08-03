{{-- Shared shipping extra fields: method, date, tracking, boxes --}}
@php
    $ship_methods = $shipping_methods ?? (\App\ShippingMethod::forDropdown(
        (int) (session('user.business_id') ?? 0),
        false,
        true
    ));
    $prefix = $field_prefix ?? '';
    $ship_method_name = $prefix ? $prefix.'shipping_method' : 'shipping_method';
    $ship_date_name = $prefix ? $prefix.'shipping_date' : 'shipping_date';
    $ship_track_name = $prefix ? $prefix.'shipping_tracking_no' : 'shipping_tracking_no';
    $ship_boxes_name = $prefix ? $prefix.'no_of_boxes' : 'no_of_boxes';
    $tx = $transaction ?? null;
    $val_method = old('shipping_method', $tx->shipping_method ?? null);
    $val_date = null;
    if (!empty($tx->shipping_date)) {
        try {
            $val_date = \Carbon\Carbon::parse($tx->shipping_date)->format(session('business.date_format', 'm/d/Y'));
        } catch (\Throwable $e) {
            $val_date = $tx->shipping_date;
        }
    }
    $val_date = old('shipping_date', $val_date);
    $val_track = old('shipping_tracking_no', $tx->shipping_tracking_no ?? null);
    $val_boxes = old('no_of_boxes', $tx->no_of_boxes ?? null);
@endphp
<div class="col-md-{{ $col ?? 3 }}">
    <div class="form-group">
        {!! Form::label($ship_method_name, __('lang_v1.shipping_method') . ':') !!}
        {!! Form::select($ship_method_name, $ship_methods, $val_method, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('messages.please_select'), 'id' => $ship_method_name]); !!}
    </div>
</div>
<div class="col-md-{{ $col ?? 3 }}">
    <div class="form-group">
        {!! Form::label($ship_date_name, __('lang_v1.shipping_date') . ':') !!}
        <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
            {!! Form::text($ship_date_name, $val_date, ['class' => 'form-control shipping-date-picker', 'placeholder' => __('lang_v1.shipping_date'), 'id' => $ship_date_name, 'readonly']); !!}
        </div>
    </div>
</div>
<div class="col-md-{{ $col ?? 3 }}">
    <div class="form-group">
        {!! Form::label($ship_track_name, __('lang_v1.shipping_tracking_no') . ':') !!}
        {!! Form::text($ship_track_name, $val_track, ['class' => 'form-control', 'placeholder' => __('lang_v1.shipping_tracking_no'), 'id' => $ship_track_name]); !!}
    </div>
</div>
<div class="col-md-{{ $col ?? 3 }}">
    <div class="form-group">
        {!! Form::label($ship_boxes_name, __('lang_v1.no_of_boxes') . ':') !!}
        {!! Form::number($ship_boxes_name, $val_boxes, ['class' => 'form-control', 'placeholder' => __('lang_v1.no_of_boxes'), 'id' => $ship_boxes_name, 'min' => 0, 'step' => 1]); !!}
    </div>
</div>
