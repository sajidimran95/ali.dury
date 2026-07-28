@extends('layouts.app')
@section('title', __('lang_v1.inventory_dashboard'))

@section('content')
<section class="content inv-dash">
    <div class="inv-head">
        <div>
            <h1 class="inv-title">@lang('lang_v1.inventory_dashboard')</h1>
            <p class="inv-sub">
                @lang('lang_v1.inventory_dashboard_subtitle', [
                    'shown' => $stats['stock_tracked'],
                    'total' => $stats['total_products'],
                ])
            </p>
        </div>
        <div class="inv-actions">
            {!! Form::open(['method' => 'get', 'class' => 'inv-location-form']) !!}
                {!! Form::select('location_id', $business_locations, $location_id, [
                    'class' => 'form-control select2 inv-select',
                    'placeholder' => __('lang_v1.all_locations'),
                    'onchange' => 'this.form.submit()',
                ]) !!}
            {!! Form::close() !!}

            @can('product.create')
                <a href="{{ action([\App\Http\Controllers\ProductController::class, 'create']) }}"
                   class="inv-btn inv-btn-primary">
                    <span aria-hidden="true">+</span> @lang('product.add_product')
                </a>
            @endcan
        </div>
    </div>

    <div class="inv-kpi-grid">
        <a href="{{ action([\App\Http\Controllers\ProductController::class, 'index']) }}" class="inv-kpi inv-kpi--blue">
            <div class="inv-kpi__body">
                <div class="inv-kpi__label">@lang('lang_v1.total_products')</div>
                <div class="inv-kpi__value">{{ @num_format($stats['total_products']) }}</div>
                <div class="inv-kpi__hint">{{ @num_format($stats['stock_tracked']) }} @lang('lang_v1.stock_tracked')</div>
            </div>
            <span class="inv-kpi__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.27 6.96L12 12.01l8.73-5.05"/><path d="M12 22.08V12"/></svg>
            </span>
        </a>

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

        <a href="{{ action([\App\Http\Controllers\ReportController::class, 'getStockReport']) }}" class="inv-kpi inv-kpi--amber">
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
        </a>

        @can('category.view')
        <a href="{{ action([\App\Http\Controllers\TaxonomyController::class, 'index']) }}?type=product" class="inv-kpi inv-kpi--violet">
        @else
        <div class="inv-kpi inv-kpi--violet">
        @endcan
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
        @can('category.view')
        </a>
        @else
        </div>
        @endcan
    </div>

    <div class="inv-panels">
        <div class="inv-panel">
            <div class="inv-panel__header">
                <h3 class="inv-panel__title">@lang('lang_v1.low_stock')</h3>
                <span class="inv-badge {{ $stats['low_stock'] > 0 ? 'inv-badge--warn' : '' }}">{{ $stats['low_stock'] }}</span>
            </div>
            <div class="inv-panel__body">
                @forelse($low_stock_products as $row)
                    <div class="inv-row">
                        <div class="inv-row__main">
                            <div class="inv-row__name">{{ $row->product }}</div>
                            <div class="inv-row__meta">{{ $row->sku ?? '' }} @if(!empty($row->location_name)) · {{ $row->location_name }} @endif</div>
                        </div>
                        <div class="inv-row__side">
                            <div class="inv-row__value inv-row__value--warn">
                                {{ @num_format($row->stock) }} {{ $row->unit ?? '' }}
                            </div>
                            <div class="inv-row__meta">@lang('product.alert_quantity'): {{ @num_format($row->alert_quantity) }}</div>
                        </div>
                    </div>
                @empty
                    <div class="inv-empty">@lang('lang_v1.no_low_stock_products')</div>
                @endforelse
            </div>
        </div>

        <div class="inv-panel">
            <div class="inv-panel__header">
                <h3 class="inv-panel__title">@lang('lang_v1.highest_stock')</h3>
            </div>
            <div class="inv-panel__body">
                @forelse($top_stock_products as $row)
                    <div class="inv-row">
                        <div class="inv-row__main">
                            <div class="inv-row__name">{{ $row->product }}</div>
                            <div class="inv-row__meta">{{ $row->sku }}</div>
                        </div>
                        <div class="inv-row__side">
                            <div class="inv-row__value">
                                {{ @num_format($row->stock) }} {{ $row->unit ?? '' }}
                            </div>
                            <div class="inv-row__meta">
                                <span class="display_currency" data-currency_symbol="true" data-orig-value="{{ $row->stock_value }}">{{ $row->stock_value }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="inv-empty">@lang('lang_v1.no_products_to_display')</div>
                @endforelse
            </div>
        </div>

        <div class="inv-panel">
            <div class="inv-panel__header">
                <h3 class="inv-panel__title">@lang('lang_v1.by_category')</h3>
            </div>
            <div class="inv-panel__body">
                @forelse($category_breakdown as $row)
                    <div class="inv-row">
                        <div class="inv-row__main">
                            <div class="inv-row__name">{{ $row->category_name }}</div>
                            <div class="inv-row__meta">{{ @num_format($row->product_count) }} @lang('sale.products')</div>
                        </div>
                        <div class="inv-row__side">
                            <div class="inv-row__value">{{ @num_format($row->stock) }}</div>
                            <div class="inv-row__meta">
                                <span class="display_currency" data-currency_symbol="true" data-orig-value="{{ $row->stock_value }}">{{ $row->stock_value }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="inv-empty">@lang('lang_v1.no_products_to_display')</div>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection

@section('javascript')
<script type="text/javascript">
    $(document).ready(function () {
        __currency_convert_recursively($('section.inv-dash'));
    });
</script>
@endsection
