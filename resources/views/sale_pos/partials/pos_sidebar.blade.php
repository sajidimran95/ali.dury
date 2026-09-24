<div class="pos-sidebar-root">
@if(empty($drawer_id_suffix))
{{-- Product Scan / Search + quick-add — visible above categories --}}
@include('sale_pos.partials.pos_product_search_input', [
    'search_disabled' => empty($default_location),
    'search_autofocus' => !empty($default_location),
])
@endif

{{-- Category tiles (top) — same classes so existing pos.js filtering works --}}
@if (!empty($categories))
@php
    $qkEnabled = !empty($posQuickKeysEnabled);
    $qkLayout = $posQuickLayout ?? null;
    // When Quick Keys is on: show only selected category tabs. Blank layout = All only.
    $displayCats = $qkEnabled ? [] : $categories;
    if ($qkEnabled && $qkLayout && !empty($qkLayout->keys)) {
        $byId = [];
        foreach ($categories as $c) {
            $byId[(int) $c['id']] = $c;
        }
        $ordered = [];
        $seen = [];
        $keyRows = $qkLayout->keys;
        if (is_string($keyRows)) {
            $keyRows = json_decode($keyRows, true) ?: [];
        }
        if (!is_array($keyRows)) {
            $keyRows = [];
        }
        usort($keyRows, function ($a, $b) {
            return ((int) ($a['slot'] ?? 0)) <=> ((int) ($b['slot'] ?? 0));
        });
        foreach ($keyRows as $key) {
            $type = $key['type'] ?? (!empty($key['variation_id']) ? 'product' : 'category');
            if ($type === 'manual' || $type !== 'category') {
                continue;
            }
            $cid = (int) ($key['category_id'] ?? 0);
            if ($cid && isset($byId[$cid]) && empty($seen[$cid]) && count($ordered) < 8) {
                $row = $byId[$cid];
                $row['_qk_label'] = $key['label'] ?: $row['name'];
                $row['_qk_color'] = $key['color'] ?? null;
                $ordered[] = $row;
                $seen[$cid] = true;
            }
        }
        $displayCats = $ordered;
    }
@endphp
<div id="product_category_div" class="pos-inline-categories">
    <div class="pos-cat-selected" style="display: none;">
        <div class="pos-cat-selected-bar">
            <span class="pos-cat-selected-name"></span>
            <button type="button" class="pos-cat-selected-close" aria-label="Close">&times;</button>
        </div>
        <div class="pos-cat-selected-subcats" style="display: none;"></div>
    </div>
    <div class="pos-category-scroll" aria-label="@lang('category.categories')">
    <div class="row pos-card-grid pos-card-grid-categories" data-label-all="@lang('messages.all')" style="margin: 0;">
        @foreach ($displayCats as $index => $category)
            <div class="col-md-2 col-xs-4 tw-mb-2 tw-cursor-pointer main-category-div main-category no-print"
                data-value="{{ $category['id'] }}" data-name="{{ $category['_qk_label'] ?? $category['name'] }}" data-parent="0"
                style="padding-left: 4px; padding-right: 4px;">
                <div class="pos-card cat pos-cat-tile {{ !empty($category['_qk_color']) ? 'pos-cat-tile--custom' : (($index % 5 === 1 || $index % 5 === 3) ? 'pos-cat-tile--accent' : '') }}"
                    @if(!empty($category['_qk_color'])) style="--qk-color: {{ $category['_qk_color'] }};" @endif>
                    <h4 class="pos-card-name">{{ $category['_qk_label'] ?? $category['name'] }}</h4>
                    @if (!empty($category['sub_categories']))
                        <span class="pos-card-subcat-dot"></span>
                    @endif
                </div>
            </div>
        @endforeach
        @foreach ($categories as $category)
            @if (!empty($category['sub_categories']))
                <div class="all-sub-category" data-category-id="{{ $category['id'] }}" style="display: none">
                    @foreach ($category['sub_categories'] as $sc)
                        @if ($sc['parent_id'] != 0)
                            <div class="col-md-2 col-xs-4 tw-mb-2 tw-cursor-pointer product_category no-print"
                                data-value="{{ $sc['id'] }}" data-name="{{ $sc['name'] }}"
                                style="padding-left: 4px; padding-right: 4px;">
                                <div class="pos-card cat pos-cat-tile">
                                    <h4 class="pos-card-name">{{ $sc['name'] }}</h4>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        @endforeach
    </div>
    </div>
    {{-- Keep drawer hooks for JS clear/overlay (hidden) --}}
    <input id="my-drawer-4{{ $drawer_id_suffix ?? '' }}" type="checkbox" class="tw-dw-drawer-toggle" style="display:none !important;">
    <label for="my-drawer-4{{ $drawer_id_suffix ?? '' }}" class="overlay-category" style="display:none;"></label>
    <span class="pos-active-filter-badge pos-filter-badge-cat" style="display:none;">
        <span class="pos-active-filter-name"></span>
        <button type="button" class="pos-filter-chip-clear" data-clear="category" aria-label="Clear"></button>
    </span>
</div>
@endif

{{-- Compact brand + featured (between categories and products) --}}
<div class="pos-filter-tools tw-flex tw-items-center tw-gap-2 tw-flex-wrap" style="margin: 4px 0 10px;">
    @if (!empty($brands))
        <div id="product_brand_div" class="tw-flex-1 tw-min-w-[120px]">
            <div class="tw-dw-drawer tw-dw-drawer-end">
                <input id="my-drawer-brand{{ $drawer_id_suffix ?? '' }}" type="checkbox" class="tw-dw-drawer-toggle" style="display:none !important;">
                <div class="tw-dw-drawer-content">
                    <label for="my-drawer-brand{{ $drawer_id_suffix ?? '' }}"
                        class="tw-dw-btn tw-dw-btn-sm tw-w-full tw-h-8 tw-min-h-[2rem] tw-rounded tw-text-xs tw-font-semibold tw-normal-case">
                        @lang('brand.brands')
                        <span class="pos-active-filter-badge pos-filter-badge-brand tw-dw-badge tw-dw-badge-sm" style="display:none;">
                            <span class="pos-active-filter-name tw-truncate"></span>
                            <button type="button" class="pos-filter-chip-clear" data-clear="brand" aria-label="Clear"></button>
                        </span>
                        <span class="pos-count-badge tw-dw-badge tw-dw-badge-sm">{{ count($brands) }}</span>
                    </label>
                </div>
                <div class="tw-dw-drawer-side" style="z-index: 4000">
                    <label for="my-drawer-brand{{ $drawer_id_suffix ?? '' }}" aria-label="close sidebar" class="tw-dw-drawer-overlay overlay-brand"></label>
                    <div class="tw-dw-menu pos-drawer-panel pos-drawer-brand tw-min-h-full tw-bg-white">
                        <div class="tw-flex tw-items-start tw-justify-between tw-gap-3 tw-px-6 tw-pt-[22px] tw-pb-4 tw-border-b tw-border-slate-100">
                            <h3 class="tw-text-xl tw-font-bold tw-m-0">@lang('brand.brands')</h3>
                            <button type="button" class="close-side-bar-brand tw-inline-flex tw-items-center tw-justify-center tw-w-9 tw-h-9 tw-p-0 tw-leading-none tw-rounded-[10px] tw-bg-slate-100 tw-border-0" aria-label="Close">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                            </button>
                        </div>
                        <div class="pos-drawer-body tw-px-6 tw-pt-[18px] tw-pb-6 tw-overflow-y-auto tw-flex-1">
                            <div class="row pos-card-grid pos-card-grid-brands" style="margin: 0;">
                                @foreach ($brands as $key => $brand)
                                    <div class="col-md-4 col-xs-6 tw-mb-3 tw-cursor-pointer product_brand no-print"
                                        data-value="{{ $key }}" data-name="{{ $brand }}"
                                        style="padding-left: 4px; padding-right: 4px;">
                                        <div class="pos-card brand">
                                            <h4 class="pos-card-name">{{ $brand }}</h4>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="col-md-6 hide" id="product_service_div">
        {!! Form::select(
            'is_enabled_stock',
            ['' => __('messages.all'), 'product' => __('sale.product'), 'service' => __('lang_v1.service')],
            null,
            ['id' => 'is_enabled_stock', 'class' => 'select2', 'name' => null, 'style' => 'width:100% !important'],
        ) !!}
    </div>
</div>

{{-- Products (bottom, under categories) --}}
<div class="row pos-products-section" style="margin: 0;">
    <input type="hidden" id="suggestion_page" value="1">
    <div class="col-md-12" style="padding: 0;">
        <div id="product_list_body" class="eq-height-row tw-overflow-y-auto tw-overflow-x-hidden">
            <div id="product_list_items" class="tw-w-full"></div>
            <div class="col-md-12 text-center" id="suggestion_page_loader" style="display: none;">
                <i class="fa fa-spinner fa-spin fa-2x"></i>
            </div>
            <div class="pos-product-scroll-spacer" aria-hidden="true"></div>
        </div>
    </div>
</div>
</div>
