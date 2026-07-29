@extends('layouts.app')
@section('title', __('lang_v1.inventory_dashboard'))

@section('content')
@php
    $catMax = 0;
    foreach ($category_breakdown as $catRow) {
        $catMax = max($catMax, (float) ($catRow->stock_value ?? 0), (float) ($catRow->stock ?? 0));
    }
    $stockHealth = max(0, 100 - min(100, (int) $stats['low_stock'] * 4));
@endphp
<section class="content inv-dash">
    {{-- Hero pulse --}}
    <div class="inv-pulse">
        <div class="inv-pulse__glow" aria-hidden="true"></div>
        <div class="inv-pulse__top">
            <div class="inv-pulse__intro">
                <p class="inv-pulse__eyebrow">@lang('lang_v1.inventory_dashboard')</p>
                <h1 class="inv-pulse__title">{{ __('home.welcome_message', ['name' => Session::get('user.first_name')]) }}</h1>
                <p class="inv-pulse__sub">
                    {{ Session::get('business.name') }} ·
                    @lang('lang_v1.inventory_dashboard_subtitle', [
                        'shown' => $stats['stock_tracked'],
                        'total' => $stats['total_products'],
                    ])
                </p>
            </div>
            <div class="inv-pulse__actions">
                {!! Form::open(['method' => 'get', 'class' => 'inv-location-form']) !!}
                    {!! Form::select('location_id', $business_locations, $location_id, [
                        'class' => 'form-control select2 inv-select',
                        'placeholder' => __('lang_v1.all_locations'),
                        'onchange' => 'this.form.submit()',
                    ]) !!}
                {!! Form::close() !!}

                <a href="{{ action([\App\Http\Controllers\ReportController::class, 'getStockReport']) }}"
                   class="inv-btn inv-btn-ghost">
                    @lang('report.stock_report')
                </a>

                @can('product.create')
                    <a href="{{ action([\App\Http\Controllers\ProductController::class, 'create']) }}"
                       class="inv-btn inv-btn-primary">
                        <span aria-hidden="true">+</span> @lang('product.add_product')
                    </a>
                @endcan
            </div>
        </div>

        <div class="inv-pulse__spotlight">
            <div class="inv-spotlight">
                <div class="inv-spotlight__label">@lang('lang_v1.inventory_value')</div>
                <div class="inv-spotlight__value">
                    <span class="display_currency" data-currency_symbol="true" data-orig-value="{{ $stats['inventory_value'] }}">{{ $stats['inventory_value'] }}</span>
                </div>
                <div class="inv-spotlight__meta">
                    @lang('lang_v1.at_selling_price')
                    <strong>
                        <span class="display_currency" data-currency_symbol="true" data-orig-value="{{ $stats['sell_value'] }}">{{ $stats['sell_value'] }}</span>
                    </strong>
                </div>
            </div>

            <div class="inv-health">
                <div class="inv-health__head">
                    <span>@lang('lang_v1.low_stock')</span>
                    <strong class="{{ $stats['low_stock'] > 0 ? 'is-warn' : '' }}">{{ @num_format($stats['low_stock']) }}</strong>
                </div>
                <div class="inv-health__track" aria-hidden="true">
                    <span class="inv-health__fill {{ $stats['low_stock'] > 0 ? 'is-warn' : 'is-ok' }}" style="width: {{ $stockHealth }}%"></span>
                </div>
                <div class="inv-health__hint">
                    {{ @num_format($stats['out_of_stock']) }} @lang('lang_v1.out_of_stock')
                </div>
            </div>
        </div>
    </div>

    {{-- Quick metrics --}}
    <div class="inv-metric-row">
        <a href="{{ action([\App\Http\Controllers\ProductController::class, 'index']) }}" class="inv-metric inv-metric--blue">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.27 6.96L12 12.01l8.73-5.05"/><path d="M12 22.08V12"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.total_products')</div>
                <div class="inv-metric__value">{{ @num_format($stats['total_products']) }}</div>
                <div class="inv-metric__hint">{{ @num_format($stats['stock_tracked']) }} @lang('lang_v1.stock_tracked')</div>
            </div>
        </a>

        <a href="{{ action([\App\Http\Controllers\ReportController::class, 'getStockReport']) }}" class="inv-metric inv-metric--amber">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('lang_v1.low_stock')</div>
                <div class="inv-metric__value {{ $stats['low_stock'] > 0 ? 'is-warn' : '' }}">{{ @num_format($stats['low_stock']) }}</div>
                <div class="inv-metric__hint">{{ @num_format($stats['out_of_stock']) }} @lang('lang_v1.out_of_stock')</div>
            </div>
        </a>

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
                <div class="inv-metric__value">{{ @num_format($stats['categories']) }}</div>
                <div class="inv-metric__hint">{{ @num_format($stats['brands']) }} @lang('brand.brands') · {{ @num_format($stats['units']) }} @lang('unit.units')</div>
            </div>
        @can('category.view')
        </a>
        @else
        </div>
        @endcan
    </div>

    {{-- Insight bento --}}
    <div class="inv-bento">
        <div class="inv-panel inv-panel--alert">
            <div class="inv-panel__header">
                <div class="inv-panel__heading">
                    <span class="inv-panel__chip inv-panel__chip--warn">@lang('lang_v1.low_stock')</span>
                    <h3 class="inv-panel__title">@lang('lang_v1.low_stock')</h3>
                </div>
                <span class="inv-badge {{ $stats['low_stock'] > 0 ? 'inv-badge--warn' : '' }}">{{ $stats['low_stock'] }}</span>
            </div>
            <div class="inv-panel__body">
                @forelse($low_stock_products as $row)
                    @php
                        $alert = max(1, (float) ($row->alert_quantity ?? 1));
                        $stock = max(0, (float) ($row->stock ?? 0));
                        $pct = min(100, round(($stock / $alert) * 100));
                    @endphp
                    <div class="inv-row">
                        <div class="inv-row__rank inv-row__rank--warn" aria-hidden="true">{{ $loop->iteration }}</div>
                        <div class="inv-row__main">
                            <div class="inv-row__name">{{ $row->product }}</div>
                            <div class="inv-row__meta">{{ $row->sku ?? '' }} @if(!empty($row->location_name)) · {{ $row->location_name }} @endif</div>
                            <div class="inv-bar" aria-hidden="true"><span style="width: {{ $pct }}%"></span></div>
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
            <div class="inv-panel__footer">
                <a href="{{ action([\App\Http\Controllers\ReportController::class, 'getStockReport']) }}" class="inv-panel__link">
                    @lang('report.stock_report')
                    <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <div class="inv-panel">
            <div class="inv-panel__header">
                <div class="inv-panel__heading">
                    <span class="inv-panel__chip inv-panel__chip--teal">@lang('lang_v1.highest_stock')</span>
                    <h3 class="inv-panel__title">@lang('lang_v1.highest_stock')</h3>
                </div>
            </div>
            <div class="inv-panel__body">
                @forelse($top_stock_products as $row)
                    <div class="inv-row">
                        <div class="inv-row__rank inv-row__rank--teal" aria-hidden="true">{{ $loop->iteration }}</div>
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
            <div class="inv-panel__footer">
                <a href="{{ action([\App\Http\Controllers\ProductController::class, 'index']) }}" class="inv-panel__link">
                    @lang('sale.products')
                    <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <div class="inv-panel inv-panel--cats">
            <div class="inv-panel__header">
                <div class="inv-panel__heading">
                    <span class="inv-panel__chip inv-panel__chip--slate">@lang('lang_v1.by_category')</span>
                    <h3 class="inv-panel__title">@lang('lang_v1.by_category')</h3>
                </div>
            </div>
            <div class="inv-panel__body">
                @forelse($category_breakdown as $row)
                    @php
                        $basis = max((float) ($row->stock_value ?? 0), (float) ($row->stock ?? 0));
                        $bar = $catMax > 0 ? min(100, round(($basis / $catMax) * 100)) : 0;
                    @endphp
                    <div class="inv-cat">
                        <div class="inv-cat__top">
                            <div class="inv-cat__name">{{ $row->category_name }}</div>
                            <div class="inv-cat__value">
                                <span class="display_currency" data-currency_symbol="true" data-orig-value="{{ $row->stock_value }}">{{ $row->stock_value }}</span>
                            </div>
                        </div>
                        <div class="inv-bar inv-bar--teal" aria-hidden="true"><span style="width: {{ $bar }}%"></span></div>
                        <div class="inv-cat__meta">
                            {{ @num_format($row->product_count) }} @lang('sale.products')
                            · {{ @num_format($row->stock) }}
                        </div>
                    </div>
                @empty
                    <div class="inv-empty">@lang('lang_v1.no_products_to_display')</div>
                @endforelse
            </div>
            @can('category.view')
            <div class="inv-panel__footer">
                <a href="{{ action([\App\Http\Controllers\TaxonomyController::class, 'index']) }}?type=product" class="inv-panel__link">
                    @lang('category.categories')
                    <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                </a>
            </div>
            @endcan
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
