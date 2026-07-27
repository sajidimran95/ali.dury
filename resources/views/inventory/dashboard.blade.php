@extends('layouts.app')
@section('title', __('lang_v1.inventory_dashboard'))

@section('content')
<section class="content-header">
    <div class="tw-flex tw-flex-col md:tw-flex-row md:tw-items-end md:tw-justify-between tw-gap-4">
        <div>
            <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black tw-m-0">
                @lang('lang_v1.inventory_dashboard')
            </h1>
            <p class="tw-text-sm md:tw-text-base tw-text-gray-600 tw-mt-1 tw-mb-0">
                @lang('lang_v1.inventory_dashboard_subtitle', [
                    'shown' => $stats['stock_tracked'],
                    'total' => $stats['total_products'],
                ])
            </p>
        </div>
        <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-2">
            {!! Form::open(['method' => 'get', 'class' => 'tw-flex tw-items-center tw-gap-2 tw-m-0']) !!}
                {!! Form::select('location_id', $business_locations, $location_id, [
                    'class' => 'form-control select2',
                    'style' => 'min-width:180px',
                    'placeholder' => __('lang_v1.all_locations'),
                    'onchange' => 'this.form.submit()',
                ]) !!}
            {!! Form::close() !!}

            @can('product.create')
                <a href="{{ action([\App\Http\Controllers\ProductController::class, 'create']) }}"
                   class="tw-inline-flex tw-items-center tw-gap-1.5 tw-rounded-lg tw-bg-blue-600 hover:tw-bg-blue-700 tw-text-white tw-text-sm tw-font-semibold tw-px-4 tw-py-2.5 tw-no-underline tw-shadow-sm">
                    <span aria-hidden="true">+</span> @lang('product.add_product')
                </a>
            @endcan
        </div>
    </div>
</section>

<section class="content">
    {{-- KPI cards --}}
    <div class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 xl:tw-grid-cols-4 tw-gap-4 tw-mb-6">
        <div class="tw-rounded-xl tw-bg-white tw-border tw-border-gray-200 tw-shadow-sm tw-p-5">
            <div class="tw-text-sm tw-font-medium tw-text-gray-500">@lang('lang_v1.total_products')</div>
            <div class="tw-mt-2 tw-text-3xl tw-font-bold tw-text-gray-900">{{ @num_format($stats['total_products']) }}</div>
            <div class="tw-mt-1 tw-text-xs tw-text-gray-400">{{ @num_format($stats['stock_tracked']) }} @lang('lang_v1.stock_tracked')</div>
        </div>

        <div class="tw-rounded-xl tw-bg-white tw-border tw-border-gray-200 tw-shadow-sm tw-p-5">
            <div class="tw-text-sm tw-font-medium tw-text-gray-500">@lang('lang_v1.inventory_value')</div>
            <div class="tw-mt-2 tw-text-3xl tw-font-bold tw-text-gray-900">
                <span class="display_currency" data-currency_symbol="true" data-orig-value="{{ $stats['inventory_value'] }}">{{ $stats['inventory_value'] }}</span>
            </div>
            <div class="tw-mt-1 tw-text-xs tw-text-gray-400">
                @lang('lang_v1.at_selling_price'):
                <span class="display_currency" data-currency_symbol="true" data-orig-value="{{ $stats['sell_value'] }}">{{ $stats['sell_value'] }}</span>
            </div>
        </div>

        <div class="tw-rounded-xl tw-bg-white tw-border tw-border-gray-200 tw-shadow-sm tw-p-5">
            <div class="tw-text-sm tw-font-medium tw-text-gray-500">@lang('lang_v1.low_stock')</div>
            <div class="tw-mt-2 tw-text-3xl tw-font-bold {{ $stats['low_stock'] > 0 ? 'tw-text-amber-600' : 'tw-text-gray-900' }}">
                {{ @num_format($stats['low_stock']) }}
            </div>
            <div class="tw-mt-1 tw-text-xs tw-text-gray-400">
                {{ @num_format($stats['out_of_stock']) }} @lang('lang_v1.out_of_stock')
            </div>
        </div>

        <div class="tw-rounded-xl tw-bg-white tw-border tw-border-gray-200 tw-shadow-sm tw-p-5">
            <div class="tw-text-sm tw-font-medium tw-text-gray-500">@lang('category.categories')</div>
            <div class="tw-mt-2 tw-text-3xl tw-font-bold tw-text-gray-900">{{ @num_format($stats['categories']) }}</div>
            <div class="tw-mt-1 tw-text-xs tw-text-gray-400">
                {{ @num_format($stats['brands']) }} @lang('brand.brands') · {{ @num_format($stats['units']) }} @lang('unit.units')
            </div>
        </div>
    </div>

    {{-- Quick links --}}
    <div class="tw-flex tw-flex-wrap tw-gap-2 tw-mb-6">
        <a href="{{ action([\App\Http\Controllers\ProductController::class, 'index']) }}" class="tw-inline-flex tw-items-center tw-rounded-lg tw-border tw-border-gray-200 tw-bg-white tw-px-3 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-no-underline">
            @lang('lang_v1.list_products')
        </a>
        @can('category.view')
        <a href="{{ action([\App\Http\Controllers\TaxonomyController::class, 'index']) }}?type=product" class="tw-inline-flex tw-items-center tw-rounded-lg tw-border tw-border-gray-200 tw-bg-white tw-px-3 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-no-underline">
            @lang('category.categories')
        </a>
        @endcan
        @can('product.create')
        <a href="{{ action([\App\Http\Controllers\SellingPriceGroupController::class, 'index']) }}" class="tw-inline-flex tw-items-center tw-rounded-lg tw-border tw-border-gray-200 tw-bg-white tw-px-3 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-no-underline">
            @lang('lang_v1.selling_price_group')
        </a>
        <a href="{{ action([\App\Http\Controllers\ImportProductsController::class, 'index']) }}" class="tw-inline-flex tw-items-center tw-rounded-lg tw-border tw-border-gray-200 tw-bg-white tw-px-3 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-no-underline">
            @lang('product.import_products')
        </a>
        @endcan
        @if(in_array('stock_adjustment', session('business.enabled_modules', [])) && (auth()->user()->can('stock_adjustment.view') || auth()->user()->can('view_own_stock_adjustment')))
        <a href="{{ action([\App\Http\Controllers\StockAdjustmentController::class, 'index']) }}" class="tw-inline-flex tw-items-center tw-rounded-lg tw-border tw-border-gray-200 tw-bg-white tw-px-3 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-no-underline">
            @lang('stock_adjustment.list')
        </a>
        @endif
        @can('stock_report.view')
        <a href="{{ action([\App\Http\Controllers\ReportController::class, 'getStockReport']) }}" class="tw-inline-flex tw-items-center tw-rounded-lg tw-border tw-border-gray-200 tw-bg-white tw-px-3 tw-py-2 tw-text-sm tw-font-medium tw-text-gray-700 hover:tw-bg-gray-50 tw-no-underline">
            @lang('report.stock_report')
        </a>
        @endcan
    </div>

    <div class="tw-grid tw-grid-cols-1 xl:tw-grid-cols-3 tw-gap-4">
        {{-- Low stock --}}
        <div class="tw-rounded-xl tw-bg-white tw-border tw-border-gray-200 tw-shadow-sm tw-overflow-hidden xl:tw-col-span-1">
            <div class="tw-flex tw-items-center tw-justify-between tw-px-5 tw-py-4 tw-border-b tw-border-gray-100">
                <h3 class="tw-text-base tw-font-semibold tw-text-gray-900 tw-m-0">@lang('lang_v1.low_stock')</h3>
                <span class="tw-text-xs tw-font-semibold tw-rounded-full tw-px-2 tw-py-0.5 {{ $stats['low_stock'] > 0 ? 'tw-bg-amber-100 tw-text-amber-700' : 'tw-bg-gray-100 tw-text-gray-600' }}">
                    {{ $stats['low_stock'] }}
                </span>
            </div>
            <div class="tw-divide-y tw-divide-gray-100">
                @forelse($low_stock_products as $row)
                    <div class="tw-px-5 tw-py-3 tw-flex tw-items-start tw-justify-between tw-gap-3">
                        <div class="tw-min-w-0">
                            <div class="tw-text-sm tw-font-medium tw-text-gray-900 tw-truncate">{{ $row->product }}</div>
                            <div class="tw-text-xs tw-text-gray-500">{{ $row->sku ?? '' }} @if(!empty($row->location_name)) · {{ $row->location_name }} @endif</div>
                        </div>
                        <div class="tw-text-right tw-shrink-0">
                            <div class="tw-text-sm tw-font-semibold tw-text-amber-600">
                                {{ @num_format($row->stock) }} {{ $row->unit ?? '' }}
                            </div>
                            <div class="tw-text-xs tw-text-gray-400">@lang('product.alert_quantity'): {{ @num_format($row->alert_quantity) }}</div>
                        </div>
                    </div>
                @empty
                    <div class="tw-px-5 tw-py-8 tw-text-sm tw-text-gray-500 tw-text-center">@lang('lang_v1.no_low_stock_products')</div>
                @endforelse
            </div>
        </div>

        {{-- Top stock --}}
        <div class="tw-rounded-xl tw-bg-white tw-border tw-border-gray-200 tw-shadow-sm tw-overflow-hidden xl:tw-col-span-1">
            <div class="tw-flex tw-items-center tw-justify-between tw-px-5 tw-py-4 tw-border-b tw-border-gray-100">
                <h3 class="tw-text-base tw-font-semibold tw-text-gray-900 tw-m-0">@lang('lang_v1.highest_stock')</h3>
            </div>
            <div class="tw-divide-y tw-divide-gray-100">
                @forelse($top_stock_products as $row)
                    <div class="tw-px-5 tw-py-3 tw-flex tw-items-start tw-justify-between tw-gap-3">
                        <div class="tw-min-w-0">
                            <div class="tw-text-sm tw-font-medium tw-text-gray-900 tw-truncate">{{ $row->product }}</div>
                            <div class="tw-text-xs tw-text-gray-500">{{ $row->sku }}</div>
                        </div>
                        <div class="tw-text-right tw-shrink-0">
                            <div class="tw-text-sm tw-font-semibold tw-text-gray-900">
                                {{ @num_format($row->stock) }} {{ $row->unit ?? '' }}
                            </div>
                            <div class="tw-text-xs tw-text-gray-400">
                                <span class="display_currency" data-currency_symbol="true" data-orig-value="{{ $row->stock_value }}">{{ $row->stock_value }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="tw-px-5 tw-py-8 tw-text-sm tw-text-gray-500 tw-text-center">@lang('lang_v1.no_products_to_display')</div>
                @endforelse
            </div>
        </div>

        {{-- Categories --}}
        <div class="tw-rounded-xl tw-bg-white tw-border tw-border-gray-200 tw-shadow-sm tw-overflow-hidden xl:tw-col-span-1">
            <div class="tw-flex tw-items-center tw-justify-between tw-px-5 tw-py-4 tw-border-b tw-border-gray-100">
                <h3 class="tw-text-base tw-font-semibold tw-text-gray-900 tw-m-0">@lang('lang_v1.by_category')</h3>
            </div>
            <div class="tw-divide-y tw-divide-gray-100">
                @forelse($category_breakdown as $row)
                    <div class="tw-px-5 tw-py-3 tw-flex tw-items-start tw-justify-between tw-gap-3">
                        <div class="tw-min-w-0">
                            <div class="tw-text-sm tw-font-medium tw-text-gray-900 tw-truncate">{{ $row->category_name }}</div>
                            <div class="tw-text-xs tw-text-gray-500">{{ @num_format($row->product_count) }} @lang('sale.products')</div>
                        </div>
                        <div class="tw-text-right tw-shrink-0">
                            <div class="tw-text-sm tw-font-semibold tw-text-gray-900">{{ @num_format($row->stock) }}</div>
                            <div class="tw-text-xs tw-text-gray-400">
                                <span class="display_currency" data-currency_symbol="true" data-orig-value="{{ $row->stock_value }}">{{ $row->stock_value }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="tw-px-5 tw-py-8 tw-text-sm tw-text-gray-500 tw-text-center">@lang('lang_v1.no_products_to_display')</div>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection

@section('javascript')
<script type="text/javascript">
    $(document).ready(function () {
        __currency_convert_recursively($('section.content'));
    });
</script>
@endsection
