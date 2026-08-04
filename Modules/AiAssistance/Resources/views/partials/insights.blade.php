@php
    $sales = $insights['sales'];
    $inventory = $insights['inventory'];
    $invoices = $insights['invoices'];
    $symbol = session('currency')['symbol'] ?? '';
@endphp

<div class="ai-cards">
    <div class="ai-card">
        <div class="ai-card__label">
            <span>@lang('aiassistance::lang.sales')</span>
            <i class="fa fa-line-chart"></i>
        </div>
        <div class="ai-card__value">
            {{ $symbol }}{{ number_format($sales['today']['total'] ?? $sales['total'], 2) }}
            <small style="display:block;font-size:12px;font-weight:500;opacity:.75;margin-top:4px;">
                @lang('aiassistance::lang.today_sales')
                · @lang('aiassistance::lang.across_orders', ['count' => $sales['today']['order_count'] ?? $sales['order_count']])
            </small>
            <small style="display:block;font-size:12px;font-weight:500;opacity:.65;margin-top:2px;">
                @lang('aiassistance::lang.last_30_days'): {{ $symbol }}{{ number_format($sales['total'], 2) }}
                (@lang('aiassistance::lang.across_orders', ['count' => $sales['order_count']]))
            </small>
        </div>
        <ul>
            <li>@lang('aiassistance::lang.avg_order_value') {{ $symbol }}{{ number_format($sales['aov'], 0) }}</li>
            <li>{{ $sales['leads'] }} @lang('aiassistance::lang.active_leads')</li>
            <li>{{ $sales['customers'] }} @lang('aiassistance::lang.customers_on_file')</li>
        </ul>
    </div>

    <div class="ai-card">
        <div class="ai-card__label">
            <span>@lang('aiassistance::lang.inventory')</span>
            <i class="fa fa-cubes"></i>
        </div>
        <div class="ai-card__value">
            {{ $inventory['attention_count'] }} of {{ $inventory['product_count'] }}
            @lang('aiassistance::lang.products_need_attention')
        </div>
        <ul>
            <li>{{ $inventory['out_of_stock_count'] }} @lang('aiassistance::lang.out_of_stock')</li>
            <li>{{ $inventory['low_stock_count'] }} @lang('aiassistance::lang.at_or_below_reorder')</li>
            @if(!empty($inventory['lowest']))
                <li>
                    @lang('aiassistance::lang.lowest'):
                    @foreach(array_slice($inventory['lowest'], 0, 3) as $item)
                        {{ $item['name'] }}
                        ({{ number_format($item['qty'], 0) }}/{{ number_format($item['alert'], 0) }})@if(!$loop->last), @endif
                    @endforeach
                </li>
            @endif
        </ul>
    </div>

    <div class="ai-card">
        <div class="ai-card__label">
            <span>@lang('aiassistance::lang.invoices')</span>
            <i class="fa fa-file-text-o"></i>
        </div>
        <div class="ai-card__value">
            {{ $invoices['overdue'] }} @lang('aiassistance::lang.overdue')
            · {{ $symbol }}{{ number_format($invoices['outstanding'], 0) }} @lang('aiassistance::lang.outstanding')
        </div>
        <ul>
            <li>{{ $invoices['open'] }} @lang('aiassistance::lang.open_invoices')</li>
            <li>{{ $invoices['overdue'] }} @lang('aiassistance::lang.past_due')</li>
            <li>{{ $symbol }}{{ number_format($invoices['outstanding'], 0) }} @lang('aiassistance::lang.cash_awaiting')</li>
        </ul>
    </div>
</div>

<div class="ai-actions-head">
    <i class="fa fa-exclamation-triangle text-warning"></i>
    @lang('aiassistance::lang.suggested_actions')
    <span class="count">{{ count($insights['actions']) }}</span>
</div>

@forelse($insights['actions'] as $action)
    <a class="ai-action" href="{{ $action['url'] }}">
        <span class="ai-action__dot {{ $action['priority'] }}"></span>
        <div style="flex:1;">
            <div class="ai-action__title">
                {{ $action['title'] }}
                <span class="ai-badge {{ $action['priority'] }}">@lang('aiassistance::lang.'.$action['priority'])</span>
            </div>
            <div class="ai-action__desc">{{ $action['description'] }}</div>
        </div>
    </a>
@empty
    <p class="text-muted">@lang('aiassistance::lang.no_actions')</p>
@endforelse
