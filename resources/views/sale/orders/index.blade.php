@extends('sale.layout')
@section('title', 'Reports')
@section('header', 'Reports')
@section('header_right')
<button type="button" class="sale-rpt-more-btn" id="saleRptMoreBtn" aria-label="More">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><circle cx="5" cy="12" r="1.7"/><circle cx="12" cy="12" r="1.7"/><circle cx="19" cy="12" r="1.7"/></svg>
</button>
@endsection
@section('content')
@php
    $rs = $reportStats ?? [];
    $g = $goals ?? [];
    $period = $period ?? 'monthly';
    $monthKey = $monthKey ?? now()->format('Y-m');
    $periodLabel = $periodLabel ?? now()->format('M Y');
    $goalPct = function ($cur, $tgt) {
        $tgt = (float) $tgt;
        if ($tgt <= 0) {
            return 0;
        }

        return (int) min(100, round(((float) $cur / $tgt) * 100));
    };
    $section = $section ?? '';
    $visitCustomers = $visitCustomers ?? collect();
    $rptBase = array_filter([
        'period' => $period,
        'month' => $period === 'monthly' ? $monthKey : null,
        'section' => $section ?: null,
        'q' => $q ?? null,
        'status' => $status ?? null,
    ]);
    $rptLink = function ($sec) use ($period, $monthKey) {
        return route('sale.orders', array_filter([
            'period' => $period,
            'month' => $period === 'monthly' ? $monthKey : null,
            'section' => $sec,
        ]));
    };
    $listTitle = $section === 'invoices' ? 'Invoices' : ($section === 'payments' ? 'Payments' : ($section === 'visits' ? 'Visits' : ($section === 'orders' ? 'Orders' : '')));
    $periodName = $period === 'daily' ? 'Daily Statistic' : ($period === 'weekly' ? 'Weekly Statistic' : 'Monthly Statistic');
@endphp
<div class="sale-rpt">
    <div class="sale-rpt__row">
        <button type="button" class="sale-rpt__dd" id="salePeriodOpen">{{ $periodName }} <span class="sale-rpt__caret"></span></button>
        <button type="button" class="sale-rpt__dd" id="saleMonthOpen">{{ $periodLabel }} <span class="sale-rpt__caret"></span></button>
    </div>
    <div class="sale-goals">
        <h2>Sales Goals</h2>
        <div class="sale-goals__grid">
            <a class="sale-goal" href="{{ $rptLink('orders') }}">
                <div class="sale-goal__arc" style="--p: {{ $goalPct($g['orders_current'] ?? 0, $g['orders_target'] ?? 0) }}"><b>{{ $goalPct($g['orders_current'] ?? 0, $g['orders_target'] ?? 0) }}%</b></div>
                <div class="sale-goal__name">Orders</div>
                <div class="sale-goal__cur" style="color:#7c3aed">Current: ${{ number_format((float) ($g['orders_current'] ?? 0), 2) }}</div>
                <div class="sale-goal__tgt">Target: ${{ number_format((float) ($g['orders_target'] ?? 0), 2) }}</div>
            </a>
            <a class="sale-goal" href="{{ $rptLink('invoices') }}">
                <div class="sale-goal__arc" style="--p: {{ $goalPct($g['invoices_current'] ?? 0, $g['invoices_target'] ?? 0) }}"><b>{{ $goalPct($g['invoices_current'] ?? 0, $g['invoices_target'] ?? 0) }}%</b></div>
                <div class="sale-goal__name">Invoices</div>
                <div class="sale-goal__cur" style="color:#ea580c">Current: ${{ number_format((float) ($g['invoices_current'] ?? 0), 2) }}</div>
                <div class="sale-goal__tgt">Target: ${{ number_format((float) ($g['invoices_target'] ?? 0), 2) }}</div>
            </a>
            <a class="sale-goal" href="{{ $rptLink('payments') }}">
                <div class="sale-goal__arc" style="--p: {{ $goalPct($g['payments_current'] ?? 0, $g['payments_target'] ?? 0) }}"><b>{{ $goalPct($g['payments_current'] ?? 0, $g['payments_target'] ?? 0) }}%</b></div>
                <div class="sale-goal__name">Payments</div>
                <div class="sale-goal__cur" style="color:#e53935">Current: ${{ number_format((float) ($g['payments_current'] ?? 0), 2) }}</div>
                <div class="sale-goal__tgt">Target: ${{ number_format((float) ($g['payments_target'] ?? 0), 2) }}</div>
            </a>
            <a class="sale-goal" href="{{ $rptLink('visits') }}">
                <div class="sale-goal__arc" style="--p: {{ $goalPct($g['visits_current'] ?? 0, $g['visits_target'] ?? 0) }}"><b>{{ $goalPct($g['visits_current'] ?? 0, $g['visits_target'] ?? 0) }}%</b></div>
                <div class="sale-goal__name">Visits</div>
                <div class="sale-goal__cur" style="color:#0284c7">Current: {{ (int) ($g['visits_current'] ?? 0) }}</div>
                <div class="sale-goal__tgt">Target: {{ (int) ($g['visits_target'] ?? 0) }}</div>
            </a>
        </div>
    </div>

    @php $t = $tx ?? []; @endphp
    <div class="sale-tx">
        <h2>Transactions</h2>
        <div class="sale-tx__grid">
            <a class="sale-tx__tile is-quotes" href="{{ $rptLink('quotes') }}">
                <div class="sale-tx__top"><b>{{ (int) ($t['quotes'] ?? 0) }} Quotes</b><span class="sale-rpt__caret"></span></div>
                <div class="sale-tx__amt">${{ number_format((float) ($t['quotes_total'] ?? 0), 2) }}</div>
            </a>
            <a class="sale-tx__tile is-orders" href="{{ $rptLink('orders') }}">
                <div class="sale-tx__top"><b>{{ (int) ($t['orders'] ?? 0) }} Orders</b><span class="sale-rpt__caret"></span></div>
                <div class="sale-tx__amt">${{ number_format((float) ($t['orders_total'] ?? 0), 2) }}</div>
            </a>
            <a class="sale-tx__tile is-invoices" href="{{ $rptLink('invoices') }}">
                <div class="sale-tx__top"><b>{{ (int) ($t['invoices'] ?? 0) }} Invoices</b><span class="sale-rpt__caret"></span></div>
                <div class="sale-tx__amt">${{ number_format((float) ($t['invoices_total'] ?? 0), 2) }}</div>
            </a>
            <a class="sale-tx__tile is-credit" href="{{ $rptLink('credit') }}">
                <div class="sale-tx__top"><b>{{ (int) ($t['credit'] ?? 0) }} Credit</b><span class="sale-rpt__caret"></span></div>
                <div class="sale-tx__amt">${{ number_format((float) ($t['credit_total'] ?? 0), 2) }}</div>
            </a>
            <a class="sale-tx__tile is-pay sale-tx__tile--wide" href="{{ $rptLink('payments') }}">
                <div class="sale-tx__top"><b>{{ (int) ($t['payments'] ?? 0) }} Payments</b><span class="sale-rpt__caret"></span></div>
                <div class="sale-tx__amt">${{ number_format((float) ($t['payments_total'] ?? 0), 2) }}</div>
            </a>
        </div>
    </div>
</div>

<div id="saleRptMenu" class="sale-sheet sale-sheet--rpt" hidden>
    <div class="sale-sheet__panel">
        <div class="sale-sheet__head is-center">
            <div class="sale-sheet__title">Reports</div>
            <button type="button" class="sale-sheet__close" id="saleRptMenuClose" aria-label="Close">×</button>
        </div>
        <div class="sale-sheet__body !pt-1">
            <a class="sale-act-row" href="{{ route('sale.orders', ['section' => 'day', 'period' => 'daily', 'day' => now()->format('Y-m-d')]) }}">
                <svg viewBox="0 0 24 24"><path d="M4 14a8 8 0 1 1 8 6"/><path d="M4 14h6v6"/></svg>
                Start/End of day report
            </a>
            <a class="sale-act-row" href="{{ route('sale.products') }}">
                <svg viewBox="0 0 24 24"><path d="M3 10h18M5 10V8l7-4 7 4v2M6 10v8h12v-8"/></svg>
                Inventory Report
            </a>
        </div>
        <div class="sale-sheet__foot">
            <button type="button" class="sale-more-cancel" id="saleRptMenuCancel">CANCEL</button>
        </div>
    </div>
</div>
<div id="salePeriodSheet" class="sale-sheet sale-sheet--rpt" hidden>
    <div class="sale-sheet__panel">
        <div class="sale-sheet__head">
            <div class="sale-sheet__title">Select date period</div>
            <button type="button" class="sale-sheet__close" id="salePeriodClose" aria-label="Close">×</button>
        </div>
        <div class="sale-sheet__body text-center">
            <a class="sale-period-opt {{ $period === 'daily' ? 'is-on' : '' }}" href="{{ route('sale.orders', array_filter(['period' => 'daily'])) }}">Daily</a>
            <a class="sale-period-opt {{ $period === 'weekly' ? 'is-on' : '' }}" href="{{ route('sale.orders', array_filter(['period' => 'weekly'])) }}">Weekly</a>
            <a class="sale-period-opt {{ $period === 'monthly' ? 'is-on' : '' }}" href="{{ route('sale.orders', array_filter(['period' => 'monthly', 'month' => $monthKey])) }}">Monthly</a>
        </div>
        <div class="sale-sheet__foot">
            <button type="button" class="sale-more-cancel" id="salePeriodCancel">CANCEL</button>
        </div>
    </div>
</div>
<div id="saleMonthSheet" class="sale-sheet sale-sheet--rpt" hidden>
    <div class="sale-sheet__panel">
        <button type="button" class="sale-month-done" id="saleMonthDone">Done</button>
        <form method="GET" action="{{ route('sale.orders') }}" id="saleMonthForm">
            <input type="hidden" name="period" id="saleWheelPeriod" value="{{ $period }}">
            <input type="hidden" name="month" id="saleWheelMonth" value="{{ $monthKey }}">
            <input type="hidden" name="day" id="saleWheelDay" value="{{ $dayKey ?? now()->format('Y-m-d') }}">
        </form>
        <div class="sale-wheel" id="saleWheel">
            <div class="sale-wheel__hl" aria-hidden="true"></div>
            <div class="sale-wheel__col" id="saleWheelM" data-kind="month">
                @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $i => $mn)
                    <div class="sale-wheel__item" data-val="{{ str_pad($i+1, 2, '0', STR_PAD_LEFT) }}">{{ $mn }}</div>
                @endforeach
            </div>
            <div class="sale-wheel__col" id="saleWheelD" data-kind="day">
                @for($d = 1; $d <= 31; $d++)
                    <div class="sale-wheel__item" data-val="{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}">{{ $d }}</div>
                @endfor
            </div>
            <div class="sale-wheel__col" id="saleWheelY" data-kind="year">
                @for($y = now()->year - 2; $y <= now()->year + 4; $y++)
                    <div class="sale-wheel__item" data-val="{{ $y }}">{{ $y }}</div>
                @endfor
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    function bindSheet(openId, sheetId, closes) {
        const sheet = document.getElementById(sheetId);
        const open = document.getElementById(openId);
        if (!sheet || !open) return;
        open.addEventListener('click', () => { sheet.hidden = false; });
        (closes || []).forEach((id) => {
            document.getElementById(id)?.addEventListener('click', () => { sheet.hidden = true; });
        });
        sheet.addEventListener('click', (e) => { if (e.target === sheet) sheet.hidden = true; });
    }
    bindSheet('saleRptMoreBtn', 'saleRptMenu', ['saleRptMenuClose', 'saleRptMenuCancel']);
    bindSheet('salePeriodOpen', 'salePeriodSheet', ['salePeriodClose', 'salePeriodCancel']);
    bindSheet('saleMonthOpen', 'saleMonthSheet', []);
    function wheelValue(col) {
        if (!col) return '';
        const items = [...col.querySelectorAll('.sale-wheel__item')];
        const mid = col.getBoundingClientRect().top + col.clientHeight / 2;
        let best = items[0], bestDist = 9999;
        items.forEach((it) => {
            const r = it.getBoundingClientRect();
            const d = Math.abs((r.top + r.height / 2) - mid);
            if (d < bestDist) { bestDist = d; best = it; }
        });
        return best ? best.getAttribute('data-val') : '';
    }
    function scrollWheelTo(col, val) {
        if (!col || !val) return;
        const item = col.querySelector('.sale-wheel__item[data-val="' + val + '"]');
        if (!item) return;
        const top = item.offsetTop - (col.clientHeight / 2) + (item.offsetHeight / 2);
        col.scrollTop = Math.max(0, top);
    }
    document.getElementById('saleMonthOpen')?.addEventListener('click', () => {
        setTimeout(() => {
            const mk = @json($monthKey);
            const dk = @json($dayKey ?? now()->format('Y-m-d'));
            scrollWheelTo(document.getElementById('saleWheelM'), mk.slice(5, 7));
            scrollWheelTo(document.getElementById('saleWheelY'), mk.slice(0, 4));
            scrollWheelTo(document.getElementById('saleWheelD'), dk.slice(8, 10));
        }, 50);
    });
    document.getElementById('saleMonthDone')?.addEventListener('click', () => {
        const form = document.getElementById('saleMonthForm');
        const m = wheelValue(document.getElementById('saleWheelM'));
        const y = wheelValue(document.getElementById('saleWheelY'));
        const d = wheelValue(document.getElementById('saleWheelD')) || '01';
        if (!form || !m || !y) return;
        const period = @json($period);
        document.getElementById('saleWheelPeriod').value = period;
        document.getElementById('saleWheelMonth').value = y + '-' + m;
        const last = new Date(Number(y), Number(m), 0).getDate();
        const day = String(Math.min(Number(d), last)).padStart(2, '0');
        document.getElementById('saleWheelDay').value = y + '-' + m + '-' + day;
        form.submit();
    });
})();
(function () {
    const form = document.getElementById('saleOrderSearchForm');
    const input = document.getElementById('saleOrderSearch');
    if (!form || !input) return;

    let timer = null;
    let abort = null;

    function urlFromForm() {
        const data = new FormData(form);
        const params = new URLSearchParams();
        data.forEach(function (value, key) {
            if (String(value).trim() !== '') {
                params.set(key, String(value).trim());
            }
        });
        const qs = params.toString();
        return form.action + (qs ? ('?' + qs) : '');
    }

    function liveSearch() {
        const url = urlFromForm();
        if (abort) abort.abort();
        abort = new AbortController();
        history.replaceState({}, '', url);
        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
            signal: abort.signal
        }).then(function (res) { return res.text(); }).then(function (html) {
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const nextResults = doc.getElementById('saleOrderResults');
            const nextCount = doc.getElementById('saleOrderCount');
            const nextChips = doc.getElementById('saleOrderChips');
            const results = document.getElementById('saleOrderResults');
            const count = document.getElementById('saleOrderCount');
            const chips = document.getElementById('saleOrderChips');
            if (results && nextResults) results.innerHTML = nextResults.innerHTML;
            if (count && nextCount) count.innerHTML = nextCount.innerHTML;
            if (chips && nextChips) chips.innerHTML = nextChips.innerHTML;
        }).catch(function (err) {
            if (err.name !== 'AbortError') {
                window.location.href = url;
            }
        });
    }

    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(liveSearch, 250);
    });
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearTimeout(timer);
        liveSearch();
    });
})();
</script>
@endpush
