@extends('layouts.app')
@section('title', __('expense.expense_categories'))

@section('css')
    <link rel="stylesheet" href="{{ asset('css/product-list-metrics.css?v=' . ($asset_v ?? '') . '&plm=8') }}">
@endsection

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header product-list-header">
    <div class="product-list-header__row">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black product-list-header__title">
            @lang('expense.expense_categories')
            <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">@lang('expense.manage_your_expense_categories')</small>
        </h1>
        <div class="product-list-header__actions">
            @if(auth()->user()->can('expense.add') || auth()->user()->can('expense.edit'))
                <a class="product-list-header__btn product-list-header__btn--primary btn-modal"
                    data-href="{{ action([\App\Http\Controllers\ExpenseCategoryController::class, 'create']) }}"
                    data-container=".expense_category_modal"
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
    @if (!empty($expense_category_stats))
        @include('expense_category.partials.list_metrics')
    @endif
    @component('components.widget', ['class' => 'box-primary', 'title' => __('expense.all_your_expense_categories')])
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="expense_category_table">
                <thead>
                    <tr>
                        <th>@lang('expense.category_name')</th>
                        <th>@lang('expense.category_code')</th>
                        <th class="not-export">@lang('messages.action')</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent

    <div class="modal fade expense_category_modal" tabindex="-1" role="dialog"
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection
