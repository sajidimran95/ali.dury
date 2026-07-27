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

<section class="content inv-dash">
<style>
.inv-dash .inv-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
@media (max-width: 1199px) {
    .inv-dash .inv-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 575px) {
    .inv-dash .inv-kpi-grid { grid-template-columns: 1fr; }
}
.inv-dash .inv-kpi {
    --kpi-accent: #4f8fce;
    --kpi-soft: #eef6fd;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;
    min-height: 112px;
    background: #fff;
    border: 1px solid #e7eaf0;
    border-radius: 18px;
    padding: 22px 20px 18px;
    box-shadow: 0 2px 10px rgba(15, 20, 37, 0.045);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.inv-dash .inv-kpi::before {
    content: "";
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    border-radius: 18px 18px 0 0;
    background: var(--kpi-accent);
}
.inv-dash .inv-kpi:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(15, 20, 37, 0.07);
}
.inv-dash .inv-kpi--blue { --kpi-accent: #4f8fce; --kpi-soft: #eef6fd; }
.inv-dash .inv-kpi--teal { --kpi-accent: #4db39e; --kpi-soft: #ecf8f4; }
.inv-dash .inv-kpi--amber { --kpi-accent: #d5aa35; --kpi-soft: #fff8e6; }
.inv-dash .inv-kpi--violet { --kpi-accent: #7c72c9; --kpi-soft: #f5f0fc; }
.inv-dash .inv-kpi__body { flex: 1; min-width: 0; }
.inv-dash .inv-kpi__label {
    margin: 0 0 10px;
    font-size: 14px;
    font-weight: 500;
    color: #7a8495;
}
.inv-dash .inv-kpi__value {
    margin: 0;
    font-size: 26px;
    font-weight: 700;
    color: #151b2b;
    line-height: 1.15;
    letter-spacing: -0.03em;
}
.inv-dash .inv-kpi__value.is-warn { color: #b78616; }
.inv-dash .inv-kpi__value.is-money { color: #2d927b; }
.inv-dash .inv-kpi__hint {
    margin-top: 8px;
    font-size: 12px;
    color: #94a3b8;
}
.inv-dash .inv-kpi__icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: var(--kpi-soft);
    color: var(--kpi-accent);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.inv-dash .inv-kpi__icon svg { width: 22px; height: 22px; display: block; }
</style>

    {{-- KPI cards --}}
    <div class="inv-kpi-grid">
        <div class="inv-kpi inv-kpi--blue">
            <div class="inv-kpi__body">
                <div class="inv-kpi__label">@lang('lang_v1.total_products')</div>
                <div class="inv-kpi__value">{{ @num_format($stats['total_products']) }}</div>
                <div class="inv-kpi__hint">{{ @num_format($stats['stock_tracked']) }} @lang('lang_v1.stock_tracked')</div>
            </div>
            <span class="inv-kpi__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.27 6.96L12 12.01l8.73-5.05"/><path d="M12 22.08V12"/></svg>
            </span>
        </div>

        <div class="inv-kpi inv-kpi--teal">
            <div class="inv-kpi__body">
                <div class="inv-kpi__label">@lang('lang_v1.inventory_value')</div>
                <div class="inv-kpi__value is-money">
                    <span class="display_currency" data-currency_symbol="true" data-orig-value="{{ $stats['inventory_value'] }}">{{ $stats['inventory_value'] }}</span>
                </div>
                <div class="inv-kpi__hint">
                    @lang('lang_v1.at_selling_price'):
                    <span class="display_currency" data-currency_symbol="true" data-orig-value="{{ $stats['sell_value'] }}">{{ $stats['sell_value'] }}</span>
                </div>
            </div>
            <span class="inv-kpi__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>
            </span>
        </div>

        <div class="inv-kpi inv-kpi--amber">
            <div class="inv-kpi__body">
                <div class="inv-kpi__label">@lang('lang_v1.low_stock')</div>
                <div class="inv-kpi__value {{ $stats['low_stock'] > 0 ? 'is-warn' : '' }}">
                    {{ @num_format($stats['low_stock']) }}
                </div>
                <div class="inv-kpi__hint">
                    {{ @num_format($stats['out_of_stock']) }} @lang('lang_v1.out_of_stock')
                </div>
            </div>
            <span class="inv-kpi__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </span>
        </div>

        <div class="inv-kpi inv-kpi--violet">
            <div class="inv-kpi__body">
                <div class="inv-kpi__label">@lang('category.categories')</div>
                <div class="inv-kpi__value">{{ @num_format($stats['categories']) }}</div>
                <div class="inv-kpi__hint">
                    {{ @num_format($stats['brands']) }} @lang('brand.brands') · {{ @num_format($stats['units']) }} @lang('unit.units')
                </div>
            </div>
            <span class="inv-kpi__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
            </span>
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
