@php
    $rows = $product_barcodes ?? collect();
    if ($rows instanceof \Illuminate\Support\Collection) {
        $rows = $rows->values();
    } elseif (is_array($rows)) {
        $rows = collect($rows);
    } else {
        $rows = collect();
    }
@endphp
<div class="col-sm-12" id="product_additional_barcodes">
    <div class="box box-solid" style="border: 1px solid #d2d6de; margin-bottom: 15px;">
        <div class="box-body" style="padding: 12px 15px;">
            <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:12px; margin-bottom:10px;">
                <div>
                    <strong style="font-size:14px;">@lang('lang_v1.additional_barcodes')</strong>
                    @show_tooltip(__('lang_v1.additional_barcodes_help'))
                    <p class="help-block" style="margin:4px 0 0;">@lang('lang_v1.additional_barcodes_help_short')</p>
                </div>
                <button type="button" class="tw-dw-btn tw-dw-btn-sm tw-dw-btn-outline tw-dw-btn-primary" id="btn_add_product_barcode">
                    <i class="fa fa-plus"></i> @lang('lang_v1.add_barcode')
                </button>
            </div>

            <div id="product_barcodes_list">
                @forelse($rows as $i => $row)
                    @include('product.partials.additional_barcode_row', [
                        'index' => $i,
                        'row' => $row,
                    ])
                @empty
                @endforelse
            </div>

            <div id="product_barcodes_empty" class="text-muted @if($rows->count()) hide @endif" style="padding:8px 0;">
                <i class="fa fa-barcode"></i> @lang('lang_v1.no_additional_barcodes_yet')
            </div>
        </div>
    </div>
</div>

<template id="product_barcode_row_template">
    @include('product.partials.additional_barcode_row', [
        'index' => '__INDEX__',
        'row' => null,
    ])
</template>

{{-- Fallback markup source for older browsers / jQuery --}}
<div id="product_barcode_row_template_html" class="hide" aria-hidden="true">
    @include('product.partials.additional_barcode_row', [
        'index' => '__INDEX__',
        'row' => null,
    ])
</div>
