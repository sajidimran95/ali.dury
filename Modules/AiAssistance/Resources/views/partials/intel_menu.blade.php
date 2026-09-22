@php
    $intelHas = \Illuminate\Support\Facades\Route::has('ai.intelligence.index');
@endphp
@if($intelHas)
<p class="text-muted" style="margin:0 0 10px;font-size:13px;">Open a report. Each link loads the full list (forecast, match, cheaper suppliers, …).</p>
<p style="display:flex;flex-wrap:wrap;gap:8px;">
    @foreach([
        'forecast' => 'Forecast / draft PO',
        'collections' => 'Collections risk',
        'match' => '3-way match',
        'slow' => 'Slow / dead stock',
        'credit' => 'Credit risk',
        'cash' => 'Cash flow',
        'cross' => 'Cross-sell',
        'churn' => 'Churn',
        'vendors' => 'Vendor score',
        'prices' => 'Cheaper suppliers',
        'compliance' => 'Compliance',
        'anomalies' => 'Anomalies',
        'digest' => 'Digest',
    ] as $sec => $label)
        <a class="ai-chip" href="{{ route('ai.intelligence.index', ['section' => $sec]) }}" style="text-decoration:none;">{{ $label }}</a>
    @endforeach
    @if(\Illuminate\Support\Facades\Route::has('ai.intelligence.supplier-prices'))
        <a class="ai-chip" href="{{ route('ai.intelligence.supplier-prices') }}" style="text-decoration:none;">Supplier price table</a>
    @endif
</p>
@endif
