@extends('layouts.app')
@section('title', __('unit.units'))

@section('css')
    <link rel="stylesheet" href="{{ asset('css/product-list-metrics.css?v=' . ($asset_v ?? '') . '&plm=4') }}">
@endsection

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header product-list-header">
        <div class="product-list-header__row">
            <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black product-list-header__title">
                @lang('unit.units')
                <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">@lang('unit.manage_your_units')</small>
            </h1>
            <div class="product-list-header__actions">
                @can('unit.create')
                    <a class="product-list-header__btn product-list-header__btn--primary btn-modal"
                        data-href="{{ action([\App\Http\Controllers\UnitController::class, 'create']) }}"
                        data-container=".unit_modal"
                        href="javascript:void(0);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 5l0 14" />
                            <path d="M5 12l14 0" />
                        </svg>
                        @lang('messages.add')
                    </a>
                @endcan
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        @if (!empty($unit_stats))
            @include('unit.partials.list_metrics')
        @endif
        @component('components.widget', ['class' => 'box-primary', 'title' => __('unit.all_your_units')])
            @can('unit.view')
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="unit_table">
                        <thead>
                            <tr>
                                <th>@lang('unit.name')</th>
                                <th>@lang('unit.short_name')</th>
                                <th>@lang('unit.allow_decimal') @show_tooltip(__('tooltip.unit_allow_decimal'))</th>
                                <th class="not-export">@lang('messages.action')</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endcan
        @endcomponent

        <div class="modal fade unit_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

    </section>
    <!-- /.content -->

@endsection
