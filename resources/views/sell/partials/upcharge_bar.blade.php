@php
    $upcharge_value = (float) ($upcharge_percent ?? 0);
@endphp
<div class="sell-upcharge-bar no-print" id="sell_upcharge_bar"
    style="display:flex; flex-wrap:wrap; align-items:center; gap:8px; margin:0 0 10px; padding:8px 10px; background:#f8fafc; border:1px dashed #cbd5e1; border-radius:8px;">
    <input type="hidden" name="upcharge_percent" id="upcharge_percent" value="{{ $upcharge_value > 0 ? $upcharge_value : 0 }}">
    <strong style="font-size:13px;"><i class="fa fa-arrow-up text-primary"></i> @lang('lang_v1.upcharge_title')</strong>
    <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary sell-upcharge-quick" data-percent="5">+5%</button>
    <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary sell-upcharge-quick" data-percent="10">+10%</button>
    <div class="input-group input-group-sm" style="width:130px;">
        <input type="number" step="0.01" min="0" class="form-control" id="upcharge_custom_percent" placeholder="%">
        <span class="input-group-btn">
            <button type="button" class="btn btn-primary btn-flat" id="upcharge_apply_custom">@lang('lang_v1.upcharge_apply')</button>
        </span>
    </div>
    <span id="upcharge_active_badge" class="label label-warning" style="font-size:12px; @if($upcharge_value <= 0) display:none; @endif">
        @lang('lang_v1.upcharge_active', ['percent' => '<span id="upcharge_active_value">'.($upcharge_value + 0).'</span>'])
    </span>
    <button type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-error" id="upcharge_remove" @if($upcharge_value <= 0) style="display:none;" @endif>
        <i class="fa fa-times"></i> @lang('lang_v1.upcharge_remove')
    </button>
    <small class="text-muted" style="flex-basis:100%;">@lang('lang_v1.upcharge_help')</small>
</div>
