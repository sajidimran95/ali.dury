@extends('layouts.app')
@section('title', __('product.variations'))

@section('css')
    <link rel="stylesheet" href="{{ asset('css/product-list-metrics.css?v=' . ($asset_v ?? '') . '&plm=3') }}">
@endsection

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header product-list-header">
        <div class="product-list-header__row">
            <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black product-list-header__title">
                @lang('product.variations')
                <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">@lang('lang_v1.manage_product_variations')</small>
            </h1>
            <div class="product-list-header__actions">
                <a class="product-list-header__btn product-list-header__btn--primary btn-modal"
                    data-href="{{ action([\App\Http\Controllers\VariationTemplateController::class, 'create']) }}"
                    data-container=".variation_modal"
                    href="javascript:void(0);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 5l0 14" />
                        <path d="M5 12l14 0" />
                    </svg>
                    @lang('messages.add')
                </a>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        @if (!empty($variation_stats))
            @include('variation.partials.list_metrics')
        @endif
        @component('components.widget', ['class' => 'box-primary', 'title' => __('lang_v1.all_variations')])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="variation_table">
                    <thead>
                        <tr>
                            <th>@lang('product.variations')</th>
                            <th>@lang('lang_v1.values')</th>
                            <th class="not-export">@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent

        <div class="modal fade variation_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

    </section>
    <!-- /.content -->

@endsection
