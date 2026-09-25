<div class="input-group-btn sell-search-mode" role="group" aria-label="{{ __('lang_v1.product_entry_mode') }}">
    <button type="button" class="btn btn-default btn-flat pos-search-mode-btn is-active" data-pos-search-mode="scan" title="{{ __('lang_v1.pos_scan_mode_help') }}">
        <i class="fas fa-barcode" aria-hidden="true"></i> @lang('lang_v1.scan')
    </button>
    <button type="button" class="btn btn-default btn-flat pos-search-mode-btn" data-pos-search-mode="search" title="{{ __('lang_v1.pos_search_mode_help') }}">
        <i class="fas fa-search" aria-hidden="true"></i> @lang('lang_v1.search')
    </button>
    <button type="button" class="btn btn-default bg-white btn-flat" data-toggle="modal" data-target="#configure_search_modal" title="{{__('lang_v1.configure_product_search')}}"><i class="fas fa-search-plus"></i></button>
</div>
<style>
    .input-group .sell-search-mode .btn.pos-search-mode-btn { background: #fff !important; color: #334155 !important; font-weight: 600; }
    .input-group .sell-search-mode .btn.pos-search-mode-btn.is-active,
    .input-group .sell-search-mode .btn.pos-search-mode-btn.is-active:hover,
    .input-group .sell-search-mode .btn.pos-search-mode-btn.is-active:focus {
        background: #4f46e5 !important; border-color: #4f46e5 !important; color: #fff !important;
    }
</style>
<script>
    (function () {
        // Highlight the saved mode (default Scan) even before pos.js runs
        var mode = 'scan';
        try { mode = localStorage.getItem('pos_product_search_mode') === 'search' ? 'search' : 'scan'; } catch (e) {}
        var btns = document.currentScript.parentNode.querySelectorAll('.sell-search-mode .pos-search-mode-btn');
        btns.forEach(function (b) { b.classList.toggle('is-active', b.getAttribute('data-pos-search-mode') === mode); });
    })();
</script>
