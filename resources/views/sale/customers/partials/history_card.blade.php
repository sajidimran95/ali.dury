<a class="sale-inv-card" href="{{ $row['show_url'] ?? route('sale.orders.show', $row['id']) }}">
    <div class="sale-inv-card__row">
        <div>
            <h3>{{ $row['kind'] }}</h3>
            <div class="meta">Amount: ${{ number_format((float) $row['amount'], 2) }}</div>
            @if($row['kind'] === 'Invoice')
                <div class="meta">Balance: ${{ number_format((float) $row['balance'], 2) }}</div>
            @endif
        </div>
        <div class="text-right">
            <div class="font-extrabold">#: {{ $row['invoice_no'] }} <span style="color:#e53935">›</span></div>
            <div class="meta">{{ $row['date'] }}</div>
            <div class="mt-2"><span class="{{ ($row['status'] ?? '') === 'Paid' ? 'sale-status-paid' : 'sale-status-sent' }}">{{ $row['status'] ?? 'Sent' }}</span></div>
        </div>
    </div>
</a>
