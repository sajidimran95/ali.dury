@php
    $barcode = is_object($row) ? ($row->barcode ?? '') : ($row['barcode'] ?? '');
    $type = is_object($row) ? ($row->type ?? 'alternate') : ($row['type'] ?? 'alternate');
    $label = is_object($row) ? ($row->label ?? '') : ($row['label'] ?? '');
    $unit_id = is_object($row) ? ($row->unit_id ?? '') : ($row['unit_id'] ?? '');
    $id = is_object($row) ? ($row->id ?? '') : ($row['id'] ?? '');
    $is_pack = $type === 'pack';
@endphp
<div class="product-barcode-row" data-index="{{ $index }}" style="display:flex; flex-wrap:wrap; gap:8px; align-items:flex-end; margin-bottom:10px; padding:10px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px;">
    @if($id !== '' && $id !== null)
        <input type="hidden" name="product_barcodes[{{ $index }}][id]" value="{{ $id }}">
    @endif

    <div style="flex:1 1 160px; min-width:140px;">
        <label class="control-label" style="font-size:12px; margin-bottom:4px;">@lang('lang_v1.barcode_or_sku')</label>
        <input type="text" name="product_barcodes[{{ $index }}][barcode]" value="{{ $barcode }}" class="form-control product-barcode-input" placeholder="@lang('lang_v1.barcode_or_sku_placeholder')" autocomplete="off">
    </div>

    <input type="hidden" name="product_barcodes[{{ $index }}][type]" class="product-barcode-type" value="{{ $is_pack ? 'pack' : 'alternate' }}">
    <input type="hidden" name="product_barcodes[{{ $index }}][unit_id]" class="product-barcode-unit-id" value="{{ $unit_id }}">

    <div style="flex:1 1 140px; min-width:120px;">
        <label class="control-label" style="font-size:12px; margin-bottom:4px;">@lang('lang_v1.barcode_label_optional')</label>
        <input type="text" name="product_barcodes[{{ $index }}][label]" value="{{ $label }}" class="form-control" placeholder="@lang('lang_v1.barcode_label_placeholder')" autocomplete="off">
    </div>

    <div style="flex:0 0 auto; padding-bottom:2px;">
        <button type="button" class="tw-dw-btn tw-dw-btn-sm tw-dw-btn-outline tw-dw-btn-error btn-remove-product-barcode" title="@lang('messages.remove')">
            <i class="fa fa-trash"></i>
        </button>
    </div>
</div>
