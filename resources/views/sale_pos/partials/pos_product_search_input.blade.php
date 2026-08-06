@php
    $searchDisabled = isset($search_disabled) ? (bool) $search_disabled : empty($default_location);
    $searchAutofocus = isset($search_autofocus) ? (bool) $search_autofocus : !empty($default_location);
@endphp
<div class="pos-product-search-wrap">
    <div class="input-group pos-product-search-group">
        <div class="pos-search-mode-toggle" role="group" aria-label="{{ __('lang_v1.product_entry_mode') }}">
            <button type="button"
                class="pos-search-mode-btn is-active"
                data-pos-search-mode="scan"
                title="{{ __('lang_v1.pos_scan_mode_help') }}">
                <i class="fas fa-barcode" aria-hidden="true"></i>
                <span>@lang('lang_v1.scan')</span>
            </button>
            <button type="button"
                class="pos-search-mode-btn"
                data-pos-search-mode="search"
                title="{{ __('lang_v1.pos_search_mode_help') }}">
                <i class="fas fa-search" aria-hidden="true"></i>
                <span>@lang('lang_v1.search')</span>
            </button>
        </div>
        {!! Form::text('search_product', null, [
            'class' => 'form-control',
            'id' => 'search_product',
            'placeholder' => __('lang_v1.enter_sku_placeholder'),
            'disabled' => $searchDisabled,
            'autofocus' => $searchAutofocus,
            'autocomplete' => 'off',
            'aria-label' => __('lang_v1.search_product'),
            'data-placeholder-scan' => __('lang_v1.enter_sku_placeholder'),
            'data-placeholder-search' => __('lang_v1.search_product_placeholder'),
        ]) !!}
        <span class="input-group-btn">
            <button type="button" class="btn btn-default bg-white btn-flat" data-toggle="modal" data-target="#configure_search_modal" title="{{__('lang_v1.configure_product_search')}}"><i class="fas fa-search-plus"></i></button>
            @if(isset($pos_settings['enable_weighing_scale']) && $pos_settings['enable_weighing_scale'] == 1)
                <button type="button" class="btn btn-default bg-white btn-flat" id="weighing_scale_btn" data-toggle="modal" data-target="#weighing_scale_modal"
                title="@lang('lang_v1.weighing_scale')"><i class="fa fa-digital-tachograph text-primary fa-lg"></i></button>
            @endif
            <button type="button" class="btn btn-default bg-white btn-flat pos_add_quick_product" data-href="{{action([\App\Http\Controllers\ProductController::class, 'quickAdd'])}}" data-container=".quick_add_product_modal" title="@lang('lang_v1.add_new_product')"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
        </span>
    </div>
</div>
