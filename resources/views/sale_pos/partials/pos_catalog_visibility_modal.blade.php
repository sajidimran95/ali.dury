@php
    $pos_settings = $pos_settings ?? [];
    $visible_cats = $pos_settings['pos_visible_category_ids'] ?? [];
    $visible_brands = $pos_settings['pos_visible_brand_ids'] ?? [];
    if (! is_array($visible_cats)) {
        $visible_cats = [];
    }
    if (! is_array($visible_brands)) {
        $visible_brands = [];
    }
    $visible_cats = array_map('intval', $visible_cats);
    $visible_brands = array_map('intval', $visible_brands);
    $show_all_categories = empty($visible_cats);
    $show_all_brands = empty($visible_brands);

    $all_cats = $pos_all_categories ?? $categories ?? [];
    $all_brands = $pos_all_brands ?? $brands ?? [];
    if ($all_cats === false) {
        $all_cats = [];
    }
    if ($all_brands === false) {
        $all_brands = [];
    }
@endphp
<div class="modal fade no-print" id="pos_catalog_visibility_modal" tabindex="-1" role="dialog"
    aria-labelledby="posCatalogVisibilityTitle">
    <div class="modal-dialog" role="document" style="max-width: 520px;">
        <div class="modal-content pos-catalog-modal">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="posCatalogVisibilityTitle">
                    <i class="fas fa-layer-group text-primary"></i>
                    @lang('lang_v1.pos_catalog_visibility')
                </h4>
                <p class="text-muted" style="margin: 6px 0 0; font-size: 12px;">
                    @lang('lang_v1.pos_catalog_visibility_help')
                </p>
            </div>
            <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
                <input type="hidden" id="pos_catalog_visibility_url" value="{{ url('/sells/pos/catalog-visibility') }}">

                <div class="pos-catalog-section">
                    <div class="pos-catalog-section__head">
                        <strong>@lang('category.categories')</strong>
                        <label class="pos-catalog-all">
                            <input type="checkbox" id="pos_cat_show_all" class="input-icheck" @if($show_all_categories) checked @endif>
                            @lang('lang_v1.show_all')
                        </label>
                    </div>
                    <div id="pos_cat_list" class="pos-catalog-checklist @if($show_all_categories) is-disabled @endif">
                        @forelse($all_cats as $cat)
                            @php
                                $cid = (int) ($cat['id'] ?? 0);
                                $checked = $show_all_categories || in_array($cid, $visible_cats, true);
                            @endphp
                            <label class="pos-catalog-item">
                                <input type="checkbox" class="pos-cat-item input-icheck" value="{{ $cid }}"
                                    @if($checked) checked @endif @if($show_all_categories) disabled @endif>
                                <span>{{ $cat['name'] ?? '' }}</span>
                            </label>
                        @empty
                            <p class="text-muted">@lang('lang_v1.no_data')</p>
                        @endforelse
                    </div>
                </div>

                <hr style="margin: 16px 0;">

                <div class="pos-catalog-section">
                    <div class="pos-catalog-section__head">
                        <strong>@lang('brand.brands')</strong>
                        <label class="pos-catalog-all">
                            <input type="checkbox" id="pos_brand_show_all" class="input-icheck" @if($show_all_brands) checked @endif>
                            @lang('lang_v1.show_all')
                        </label>
                    </div>
                    <div id="pos_brand_list" class="pos-catalog-checklist @if($show_all_brands) is-disabled @endif">
                        @php
                            $brand_rows = [];
                            if ($all_brands instanceof \Illuminate\Support\Collection) {
                                $brand_rows = $all_brands->all();
                            } elseif (is_array($all_brands)) {
                                $brand_rows = $all_brands;
                            }
                        @endphp
                        @forelse($brand_rows as $bid => $bname)
                            @if ((string) $bid === 'all' || $bid === '' || $bid === null)
                                @continue
                            @endif
                            @php
                                $bidInt = (int) $bid;
                                $bChecked = $show_all_brands || in_array($bidInt, $visible_brands, true);
                            @endphp
                            <label class="pos-catalog-item">
                                <input type="checkbox" class="pos-brand-item input-icheck" value="{{ $bidInt }}"
                                    @if($bChecked) checked @endif @if($show_all_brands) disabled @endif>
                                <span>{{ $bname }}</span>
                            </label>
                        @empty
                            <p class="text-muted">@lang('lang_v1.no_data')</p>
                        @endforelse
                    </div>
                </div>

                <p class="pos-catalog-status text-muted" id="pos_catalog_status" style="margin-top: 12px; min-height: 18px;"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                <button type="button" class="btn btn-primary" id="pos_catalog_save_btn">
                    <i class="fa fa-save"></i> @lang('messages.save')
                </button>
            </div>
        </div>
    </div>
</div>
<style>
    .pos-catalog-section__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
        gap: 12px;
    }
    .pos-catalog-all {
        font-weight: normal;
        margin: 0;
        font-size: 13px;
        white-space: nowrap;
    }
    .pos-catalog-checklist {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px 12px;
    }
    .pos-catalog-checklist.is-disabled {
        opacity: 0.55;
    }
    .pos-catalog-item {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        font-weight: normal;
        font-size: 13px;
        padding: 6px 8px;
        border-radius: 6px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
    }
    .pos-catalog-item span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    @media (max-width: 480px) {
        .pos-catalog-checklist { grid-template-columns: 1fr; }
    }
</style>
<script>
(function () {
    if (window.__posCatalogVisibilityBound) return;
    window.__posCatalogVisibilityBound = true;

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function setListDisabled(listId, disabled) {
        var list = document.getElementById(listId);
        if (!list) return;
        list.classList.toggle('is-disabled', !!disabled);
        list.querySelectorAll('input[type="checkbox"]').forEach(function (cb) {
            cb.disabled = !!disabled;
            if (disabled) cb.checked = true;
        });
    }

    function bindToggle(allId, listId) {
        var allCb = document.getElementById(allId);
        if (!allCb) return;
        allCb.addEventListener('change', function () {
            setListDisabled(listId, allCb.checked);
        });
    }

    function collectIds(selector) {
        var ids = [];
        document.querySelectorAll(selector + ':checked').forEach(function (el) {
            var v = parseInt(el.value, 10);
            if (!isNaN(v) && v > 0) ids.push(v);
        });
        return ids;
    }

    function setStatus(msg, isError) {
        var el = document.getElementById('pos_catalog_status');
        if (!el) return;
        el.textContent = msg || '';
        el.className = 'pos-catalog-status ' + (isError ? 'text-danger' : 'text-success');
    }

    document.addEventListener('DOMContentLoaded', function () {
        bindToggle('pos_cat_show_all', 'pos_cat_list');
        bindToggle('pos_brand_show_all', 'pos_brand_list');

        var saveBtn = document.getElementById('pos_catalog_save_btn');
        if (!saveBtn) return;

        saveBtn.addEventListener('click', function () {
            var urlEl = document.getElementById('pos_catalog_visibility_url');
            var url = urlEl ? urlEl.value : '';
            if (!url) return;

            var showAllCat = !!(document.getElementById('pos_cat_show_all') && document.getElementById('pos_cat_show_all').checked);
            var showAllBrand = !!(document.getElementById('pos_brand_show_all') && document.getElementById('pos_brand_show_all').checked);
            var catIds = showAllCat ? [] : collectIds('.pos-cat-item');
            var brandIds = showAllBrand ? [] : collectIds('.pos-brand-item');

            // No categories selected → treat as show all
            if (!showAllCat && catIds.length === 0) {
                showAllCat = true;
            }
            if (!showAllBrand && brandIds.length === 0) {
                showAllBrand = true;
            }

            saveBtn.disabled = true;
            setStatus('Saving…', false);

            var body = new FormData();
            body.append('_token', csrfToken());
            body.append('show_all_categories', showAllCat ? '1' : '0');
            body.append('show_all_brands', showAllBrand ? '1' : '0');
            catIds.forEach(function (id) { body.append('category_ids[]', id); });
            brandIds.forEach(function (id) { body.append('brand_ids[]', id); });

            fetch(url, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: body,
                credentials: 'same-origin'
            })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    saveBtn.disabled = false;
                    if (res && res.success) {
                        setStatus(res.msg || 'Saved', false);
                        if (typeof toastr !== 'undefined') {
                            toastr.success(res.msg || 'Saved');
                        }
                        // Reload so category/brand tiles refresh
                        setTimeout(function () { window.location.reload(); }, 400);
                    } else {
                        setStatus((res && res.msg) || 'Failed', true);
                    }
                })
                .catch(function () {
                    saveBtn.disabled = false;
                    setStatus('Failed', true);
                });
        });
    });
})();
</script>
