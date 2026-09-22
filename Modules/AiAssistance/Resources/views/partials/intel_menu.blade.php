@php
    $intelHas = \Illuminate\Support\Facades\Route::has('ai.intelligence.index');
    $embedUrl = $intelHas && \Illuminate\Support\Facades\Route::has('ai.intelligence.embed')
        ? route('ai.intelligence.embed')
        : ($intelHas ? route('ai.intelligence.index') : '');
@endphp
@if($intelHas && $embedUrl)
<p class="text-muted" style="margin:0 0 10px;font-size:13px;">Click a chip — the report opens below (same as chat).</p>
<div class="ai-chips" id="ai-intel-chips">
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
        'prices-table' => 'Supplier price table',
    ] as $sec => $label)
        <button type="button"
                class="japs-intel-chip"
                data-intel-url="{{ $embedUrl }}?embed=1&amp;section={{ urlencode($sec) }}">{{ $label }}</button>
    @endforeach
</div>
<div id="ai-intel-report" class="ai-intel-report">
    <p class="text-muted" style="margin:8px 0 0;">No report yet. Click a chip above.</p>
</div>
@endif
