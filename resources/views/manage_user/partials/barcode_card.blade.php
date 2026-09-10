@php
    $barcodeCode = $barcodeCode ?? '';
    $showBarcode = $showBarcode ?? ($barcodeCode !== '');
    $barcodeEditable = $barcodeEditable ?? true;
    $barcodeAlways = !empty($barcodeAlways);
@endphp
<div class="ls-barcode-card" id="ls_user_barcode_wrap" data-code="{{ $barcodeCode }}" data-always="{{ $barcodeAlways ? '1' : '0' }}" @if(!$showBarcode) style="display:none;" @endif>
    <div class="ls-barcode-card__body">
        <div class="ls-barcode-kicker">Your quick switch ID</div>
        <div class="ls-barcode-id" id="ls_barcode_id">{{ $barcodeCode }}</div>
        <div class="ls-barcode-img-wrap">
            <img
                src="{{ $barcodeCode !== '' ? url('/users/barcode-image').'?text='.urlencode($barcodeCode) : '' }}"
                alt="{{ $barcodeCode }}"
                class="ls-barcode-img"
                id="ls_barcode_img"
            >
        </div>
    </div>
    <div class="ls-barcode-foot">
        <span>Print and attach this barcode to your staff ID</span>
        <div class="ls-barcode-foot__btns">
            @if($barcodeEditable)
                <button type="button" class="ls-barcode-print" id="lsPrintBarcode">Print barcode</button>
                <button type="button" class="ls-barcode-copy" id="lsCopyId">Copy ID</button>
            @else
                <a class="ls-barcode-print" href="{{ url('/users/barcode-image') }}?text={{ urlencode($barcodeCode) }}" target="_blank" rel="noopener">Print barcode</a>
                <button type="button" class="ls-barcode-copy" id="lsCopyId">Copy ID</button>
            @endif
        </div>
    </div>
</div>
