@php
    $stats = $product_stats ?? [];
    $totalProducts = (float) ($stats['total_products'] ?? 0);
    $stockTracked = (float) ($stats['stock_tracked'] ?? 0);
    $invValue = (float) ($stats['inventory_value'] ?? 0);
    $sellValue = (float) ($stats['sell_value'] ?? 0);
    $lowStock = (float) ($stats['low_stock'] ?? 0);
    $outOfStock = (float) ($stats['out_of_stock'] ?? 0);
    $categories = (float) ($stats['categories'] ?? 0);
    $brands = (float) ($stats['brands'] ?? 0);
    $typeSingle = (int) ($stats['type_single'] ?? 0);
    $typeVariable = (int) ($stats['type_variable'] ?? 0);
    $typeCombo = (int) ($stats['type_combo'] ?? 0);
    $inactive = (int) ($stats['inactive'] ?? 0);
@endphp
<div class="product-list-dash inv-dash" id="product_list_metrics">
    <div class="inv-metric-row product-list-metrics inv-saas-kpis">
        <div class="inv-metric inv-metric--blue">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.27 6.96L12 12.01l8.73-5.05"/><path d="M12 22.08V12"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.total_products')</div>
                <div class="inv-metric__value">{{ @num_format($totalProducts) }}</div>
                <div class="inv-metric__hint">
                    {{ @num_format($stockTracked) }} @lang('lang_v1.stock_tracked')
                    @if($inactive > 0)
                        · {{ @num_format($inactive) }} @lang('lang_v1.inactive')
                    @endif
                </div>
            </div>
        </div>

        <div class="inv-metric inv-metric--violet">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.inventory_value')</div>
                <div class="inv-metric__value">
                    <span class="display_currency" data-currency_symbol="true" data-orig-value="{{ $invValue }}">{{ $invValue }}</span>
                </div>
                <div class="inv-metric__hint">
                    @lang('lang_v1.at_selling_price')
                    <strong>
                        <span class="display_currency" data-currency_symbol="true" data-orig-value="{{ $sellValue }}">{{ $sellValue }}</span>
                    </strong>
                </div>
            </div>
        </div>

        @can('stock_report.view')
        <a href="{{ action([\App\Http\Controllers\ReportController::class, 'getStockReport']) }}" class="inv-metric inv-metric--amber">
        @else
        <div class="inv-metric inv-metric--amber">
        @endcan
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.low_stock')</div>
                <div class="inv-metric__value {{ $lowStock > 0 ? 'is-warn' : '' }}">{{ @num_format($lowStock) }}</div>
                <div class="inv-metric__hint">{{ @num_format($outOfStock) }} @lang('lang_v1.out_of_stock')</div>
            </div>
        @can('stock_report.view')
        </a>
        @else
        </div>
        @endcan

        @can('category.view')
        <a href="{{ action([\App\Http\Controllers\TaxonomyController::class, 'index']) }}?type=product" class="inv-metric inv-metric--teal">
        @else
        <div class="inv-metric inv-metric--teal">
        @endcan
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('category.categories')</div>
                <div class="inv-metric__value">{{ @num_format($categories) }}</div>
                <div class="inv-metric__hint">{{ @num_format($brands) }} @lang('brand.brands')</div>
            </div>
        @can('category.view')
        </a>
        @else
        </div>
        @endcan

        <div class="inv-metric inv-metric--slate">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('product.product_type')</div>
                <div class="inv-metric__value inv-metric__value--sm">
                    {{ @num_format($typeSingle + $typeVariable + $typeCombo) }}
                </div>
                <div class="inv-metric__hint">
                    {{ @num_format($typeSingle) }} @lang('lang_v1.single')
                    · {{ @num_format($typeVariable) }} @lang('lang_v1.variable')
                    · {{ @num_format($typeCombo) }} @lang('lang_v1.combo')
                </div>
            </div>
        </div>
    </div>
</div>
