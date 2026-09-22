@php
    $s = $symbol ?? ($s ?? '');
    $sec = $section ?? ($sec ?? 'overview');
    $data = $data ?? [];
@endphp
<p><strong>{{ strtoupper($sec) }}</strong>
    @if(!empty($data['as_of']))
        <span class="text-muted"> · as of {{ $data['as_of'] }}</span>
    @endif
</p>
@if(!empty($data['error']))
    <p class="text-danger">{{ $data['error'] }}</p>
@endif
@if(session('status.msg'))
    <div class="alert alert-{{ session('status.success') ? 'success' : 'danger' }}">{{ session('status.msg') }}</div>
@endif

@if($sec === 'overview')
    <h4>Suggested actions</h4>
    @forelse($data['actions'] ?? [] as $a)
        <p><span class="label label-{{ $a['priority'] === 'high' ? 'danger' : ($a['priority'] === 'medium' ? 'warning' : 'default') }}">{{ $a['priority'] }}</span>
            <a href="{{ $a['url'] }}">{{ $a['title'] }}</a> — {{ $a['description'] }}</p>
    @empty
        <p>No suggested actions right now.</p>
    @endforelse
    <p>Cash-in next weeks: <strong>{{ $s }}{{ number_format($data['cash_flow']['total'] ?? 0, 2) }}</strong>
        @foreach($data['cash_flow']['buckets'] ?? [] as $b => $v)
            · {{ $b }}d {{ $s }}{{ number_format($v, 0) }}
        @endforeach
    </p>
@endif

@if($sec === 'forecast')
    <p>{{ $data['forecast']['ready_count'] ?? 0 }} SKUs ready for a draft PO · {{ $data['forecast']['insufficient_count'] ?? 0 }} SKUs skipped (not enough sales history — not guessed).</p>
    @if(!empty($data['forecast']['ready']))
    <form method="POST" action="{{ route('ai.intelligence.draft-po') }}">
        @csrf
        <button class="btn btn-primary btn-sm" type="submit">Create draft PO from selected</button>
        <table class="table table-condensed table-striped" style="margin-top:12px;">
            <thead><tr><th></th><th>Product</th><th>On hand</th><th>Qty</th><th>Target</th><th>Why</th></tr></thead>
            <tbody>
            @foreach($data['forecast']['ready'] as $row)
                <tr>
                    <td><input type="checkbox" name="variation_ids[]" value="{{ $row['variation_id'] }}" checked></td>
                    <td>{{ $row['name'] }} <small>{{ $row['sku'] }}</small></td>
                    <td>{{ number_format($row['qty'], 2) }}</td>
                    <td>{{ $row['suggested_qty'] }}</td>
                    <td>{{ $row['target_date'] }}</td>
                    <td><small>{{ $row['basis'] }}</small></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </form>
    @endif
    @if(!empty($data['forecast']['insufficient_sample']))
        <h5>Insufficient data (sample)</h5>
        <table class="table table-condensed">
            <thead><tr><th>Product</th><th>Why skipped</th></tr></thead>
            <tbody>
            @foreach($data['forecast']['insufficient_sample'] as $row)
                <tr><td>{{ $row['name'] }}</td><td>{{ $row['basis'] }}</td></tr>
            @endforeach
            </tbody>
        </table>
    @endif
@endif

@if($sec === 'collections')
    <table class="table table-condensed">
        <thead><tr><th>Score</th><th>Customer</th><th>Invoice</th><th>Due</th><th>Age</th><th>Draft script</th></tr></thead>
        <tbody>
        @forelse($data['collections'] ?? [] as $row)
            <tr>
                <td>{{ $row['risk_score'] }}</td>
                <td>{{ $row['customer'] }}</td>
                <td>{{ $row['invoice_no'] }}</td>
                <td>{{ $s }}{{ number_format($row['remaining'], 2) }}</td>
                <td>{{ $row['days_overdue'] }}d</td>
                <td>
                    <small>{{ $row['script'] }}</small><br>
                    @if(!empty($row['email']))
                        <a href="mailto:{{ $row['email'] }}?subject={{ urlencode('Invoice '.$row['invoice_no']) }}&body={{ urlencode($row['script']) }}">Open email draft</a>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6">No collection-risk invoices right now.</td></tr>
        @endforelse
        </tbody>
    </table>
@endif

@if($sec === 'match')
    <p>Tolerance {{ $data['match']['tolerance_pct'] ?? 0 }}%. Auto-approved (no queue) {{ $data['match']['auto_ok_count'] ?? 0 }}. Open exceptions {{ $data['match']['exception_count'] ?? 0 }}.</p>
    <table class="table table-condensed">
        <thead><tr><th>PO</th><th>Supplier</th><th>PO qty</th><th>Recv</th><th>Qty var%</th><th>Cost var%</th><th></th></tr></thead>
        <tbody>
        @forelse($data['match']['exceptions'] ?? [] as $row)
            <tr class="danger">
                <td>{{ $row['po_ref'] }}</td>
                <td>{{ $row['supplier'] }}</td>
                <td>{{ $row['po_qty'] }}</td>
                <td>{{ $row['recv_qty'] }}</td>
                <td>{{ $row['qty_var_pct'] }}</td>
                <td>{{ $row['cost_var_pct'] }}</td>
                <td>
                    @if(!empty($row['purchase_id']))
                        <a href="{{ action([\App\Http\Controllers\PurchaseController::class, 'edit'], [$row['purchase_id']]) }}">Invoice</a>
                    @endif
                    @if(!empty($row['po_line_id']))
                    <form method="POST" action="{{ route('ai.intelligence.match-review') }}" style="display:inline;">
                        @csrf
                        <input type="hidden" name="po_line_id" value="{{ $row['po_line_id'] }}">
                        <button class="btn btn-xs btn-success" type="submit">Mark reviewed</button>
                    </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="7">No 3-way match exceptions (or none outside tolerance).</td></tr>
        @endforelse
        </tbody>
    </table>
@endif

@if($sec === 'slow')
    <table class="table table-condensed">
        <thead><tr><th>Band</th><th>Product</th><th>Days</th><th>Qty</th><th>Value</th><th>Why</th></tr></thead>
        <tbody>
        @foreach($data['slow_movers'] ?? [] as $row)
            <tr>
                <td>{{ $row['band'] }}</td>
                <td>{{ $row['name'] }}</td>
                <td>{{ $row['days_since_sale'] }}</td>
                <td>{{ number_format($row['qty'], 2) }}</td>
                <td>{{ $s }}{{ number_format($row['stock_value'], 0) }}</td>
                <td><small>{{ $row['basis'] }} — suggest clearance (draft)</small></td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

@if($sec === 'credit')
    <p>Warn before extra credit. Shown here; POS checkout screens were not modified.</p>
    <table class="table table-condensed">
        <thead><tr><th>Customer</th><th>DSO now</th><th>DSO prev</th><th>On-time now</th><th>On-time prev</th></tr></thead>
        <tbody>
        @foreach($data['credit'] ?? [] as $row)
            <tr>
                <td>{{ $row['customer'] }}</td>
                <td>{{ $row['dso_now'] }}</td>
                <td>{{ $row['dso_prev'] }}</td>
                <td>{{ $row['on_time_now'] }}%</td>
                <td>{{ $row['on_time_prev'] }}%</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

@if($sec === 'cash')
    <p>{{ $data['cash_flow']['basis'] ?? '' }}</p>
    <ul>
        @foreach($data['cash_flow']['buckets'] ?? [] as $b => $v)
            <li>{{ $b }} days: {{ $s }}{{ number_format($v, 2) }}</li>
        @endforeach
    </ul>
    <p>Total projected: {{ $s }}{{ number_format($data['cash_flow']['total'] ?? 0, 2) }}</p>
@endif

@if($sec === 'cross')
    <p>Add-on pairs for order entry. POS UI not changed — use these as the data source.</p>
    <ul>
        @forelse($data['cross_sell'] ?? [] as $row)
            <li>Who buy <strong>{{ $row['buy'] }}</strong> also buy <strong>{{ $row['also'] }}</strong> ({{ $row['together'] }} tickets). {{ $row['basis'] }}</li>
        @empty
            <li class="text-muted">No pairs yet. Needs the same 2 products on at least 3 final tickets in the last 120 days.</li>
        @endforelse
    </ul>
@endif

@if($sec === 'churn')
    <table class="table table-condensed">
        <thead><tr><th>Customer</th><th>Orders now/prev</th><th>Volume now/prev</th></tr></thead>
        <tbody>
        @foreach($data['churn'] ?? [] as $row)
            <tr>
                <td>{{ $row['customer'] }}</td>
                <td>{{ $row['orders_now'] }} / {{ $row['orders_prev'] }}</td>
                <td>{{ $s }}{{ number_format($row['volume_now'], 0) }} / {{ $s }}{{ number_format($row['volume_prev'], 0) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

@if($sec === 'vendors')
    <table class="table table-condensed">
        <thead><tr><th>Vendor</th><th>Fill %</th><th>On-time %</th><th>Price var %</th><th>POs</th><th>Score</th></tr></thead>
        <tbody>
        @foreach($data['vendors'] ?? [] as $row)
            <tr>
                <td>{{ $row['vendor'] }}</td>
                <td>{{ $row['fill_rate'] }}</td>
                <td>{{ $row['on_time_pct'] ?? '—' }}</td>
                <td>{{ $row['price_variance_pct'] ?? '—' }}</td>
                <td>{{ $row['po_count'] }}</td>
                <td>{{ $row['score'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

@if($sec === 'prices')
    <p>Which supplier is cheaper per SKU — last 12 months average unit cost. Advice only.</p>
    <table class="table table-condensed table-striped">
        <thead>
            <tr>
                <th>Product</th>
                <th>Cheapest supplier</th>
                <th>Their avg cost</th>
                <th>Highest supplier</th>
                <th>Their avg</th>
                <th>Save %</th>
                <th>All suppliers</th>
            </tr>
        </thead>
        <tbody>
        @forelse($data['supplier_prices'] ?? [] as $row)
            <tr>
                <td>{{ $row['product'] }} <small>{{ $row['sku'] }}</small></td>
                <td><strong>{{ $row['cheapest_supplier'] }}</strong></td>
                <td>{{ $s }}{{ number_format($row['cheapest_avg'], 2) }}</td>
                <td>{{ $row['costliest_supplier'] }}</td>
                <td>{{ $s }}{{ number_format($row['costliest_avg'], 2) }}</td>
                <td>{{ $row['save_pct'] }}%</td>
                <td>
                    <small>
                        @foreach($row['suppliers'] as $sup)
                            {{ $sup['supplier'] }} {{ $s }}{{ number_format($sup['avg_cost'], 2) }}@if(!$loop->last); @endif
                        @endforeach
                    </small>
                </td>
            </tr>
        @empty
            <tr><td colspan="7">Need at least two suppliers with different costs on the same SKU.</td></tr>
        @endforelse
        </tbody>
    </table>
@endif

@if($sec === 'compliance')
    <p>Regulated SKUs (category tags): {{ $data['regulated']['regulated_sku_count'] ?? 0 }}</p>
    @if(!empty($data['regulated']['licenses']['tracked']))
        <h5>Licenses expiring in 30 days</h5>
        <ul>
            @forelse($data['regulated']['licenses']['expiring'] ?? [] as $row)
                <li>{{ $row['customer'] }} — {{ $row['type'] }} expires {{ $row['expires_at'] }}</li>
            @empty
                <li>None in the next 30 days.</li>
            @endforelse
        </ul>
        <h5>Missing license (bought regulated SKUs)</h5>
        <ul>
            @forelse($data['regulated']['licenses']['missing'] ?? [] as $row)
                <li>{{ $row['customer'] }}</li>
            @empty
                <li>None.</li>
            @endforelse
        </ul>
    @else
        <p>{{ $data['regulated']['licenses']['missing_note'] ?? '' }}</p>
    @endif
@endif

@if($sec === 'anomalies')
    <table class="table table-condensed">
        <thead><tr><th>Invoice</th><th>Amount</th><th>Discount %</th><th>Reason</th></tr></thead>
        <tbody>
        @foreach($data['anomalies'] ?? [] as $row)
            <tr>
                <td>{{ $row['invoice_no'] }}</td>
                <td>{{ $s }}{{ number_format($row['amount'], 2) }}</td>
                <td>{{ $row['discount_pct'] }}</td>
                <td>{{ $row['reason'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

@if($sec === 'digest')
    <p>{{ $data['digest'] ?? '' }}</p>
    <form method="POST" action="{{ route('ai.intelligence.digest') }}" class="form-inline">
        @csrf
        <input type="email" name="email" class="form-control" placeholder="email@company.com" required>
        <button class="btn btn-default" type="submit">Email this digest (opt-in)</button>
    </form>
@endif
