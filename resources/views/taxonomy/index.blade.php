@extends('layouts.app')
@php
    $heading = !empty($module_category_data['heading']) ? $module_category_data['heading'] : __('category.categories');
    $navbar = !empty($module_category_data['navbar']) ? $module_category_data['navbar'] : null;
@endphp
@section('title', $heading)

@section('css')
    <link rel="stylesheet" href="{{ asset('css/product-list-metrics.css?v=' . ($asset_v ?? '') . '&plm=4') }}">
@endsection

@section('content')
    @if (!empty($navbar))
        @include($navbar)
    @endif
    @php
        $can_add = true;
        if (request()->get('type') == 'product' && !auth()->user()->can('category.create')) {
            $can_add = false;
        }
    @endphp
    <!-- Content Header (Page header) -->
    <section class="content-header product-list-header">
        <div class="product-list-header__row">
            <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black product-list-header__title">
                {{ $heading }}
                <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">
                    {{ $module_category_data['sub_heading'] ?? __('category.manage_your_categories') }}
                </small>
                @if (isset($module_category_data['heading_tooltip']))
                    @show_tooltip($module_category_data['heading_tooltip'])
                @endif
            </h1>
            <div class="product-list-header__actions">
                @if ($can_add)
                    <a class="product-list-header__btn product-list-header__btn--primary btn-modal"
                        data-href="{{ action([\App\Http\Controllers\TaxonomyController::class, 'create']) }}?type={{ request()->get('type') }}"
                        data-container=".category_modal"
                        href="javascript:void(0);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 5l0 14" />
                            <path d="M5 12l14 0" />
                        </svg>
                        @lang('messages.add')
                    </a>
                @endif
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        @if (!empty($taxonomy_stats))
            @include('taxonomy.partials.list_metrics')
        @endif
        @php
            $cat_code_enabled =
                isset($module_category_data['enable_taxonomy_code']) && !$module_category_data['enable_taxonomy_code']
                    ? false
                    : true;
        @endphp
        <input type="hidden" id="category_type" value="{{ request()->get('type') }}">
        @component('components.widget', ['class' => 'box-solid'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="category_table">
                    <thead>
                        <tr>
                            <th>
                                @if (!empty($module_category_data['taxonomy_label']))
                                    {{ $module_category_data['taxonomy_label'] }}
                                @else
                                    @lang('category.category')
                                @endif
                            </th>
                            @if ($cat_code_enabled)
                                <th>{{ $module_category_data['taxonomy_code_label'] ?? __('category.code') }}</th>
                            @endif
                            <th>@lang('lang_v1.description')</th>
                            <th class="not-export">@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent

        <div class="modal fade category_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

    </section>
    <!-- /.content -->
@stop
@section('javascript')
    @includeIf('taxonomy.taxonomies_js')
@endsection
