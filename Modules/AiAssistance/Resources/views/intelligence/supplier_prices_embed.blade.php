<p class="help-block">Average purchase / PO unit cost per SKU. Shown only when two or more suppliers sold you the same item. Advice only.
    @if(\Illuminate\Support\Facades\Route::has('ai.intelligence.export'))
        <a href="{{ route('ai.intelligence.export', ['section' => 'prices-table']) }}"
           class="btn btn-default btn-xs" style="margin-left:8px;border-radius:999px;">
            <i class="fa fa-download"></i> Export CSV
        </a>
    @endif
</p>
<table class="table table-bordered table-striped table-condensed">
    <thead>
        <tr>
            <th>Product</th>
            <th>SKU</th>
            <th>Cheapest supplier</th>
            <th>Avg cost</th>
            <th>Highest supplier</th>
            <th>Avg cost</th>
            <th>Save %</th>
            <th>All suppliers (avg)</th>
        </tr>
    </thead>
    <tbody>
    @forelse($rows as $row)
        <tr>
            <td>{{ $row['product'] }}</td>
            <td>{{ $row['sku'] }}</td>
            <td><strong>{{ $row['cheapest_supplier'] }}</strong></td>
            <td>{{ $symbol }}{{ number_format($row['cheapest_avg'], 2) }}</td>
            <td>{{ $row['costliest_supplier'] }}</td>
            <td>{{ $symbol }}{{ number_format($row['costliest_avg'], 2) }}</td>
            <td>{{ $row['save_pct'] }}%</td>
            <td>
                @foreach($row['suppliers'] as $sup)
                    {{ $sup['supplier'] }}: {{ $symbol }}{{ number_format($sup['avg_cost'], 2) }}@if(!$loop->last); @endif
                @endforeach
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="8">No SKUs with two or more suppliers and different prices in the last 12 months.</td>
        </tr>
    @endforelse
    </tbody>
</table>
