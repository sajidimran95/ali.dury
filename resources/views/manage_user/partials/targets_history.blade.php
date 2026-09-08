@php
    $dailyT = $user->sales_target_daily ?? null;
    $weeklyT = $user->sales_target_weekly ?? null;
    $monthlyT = $user->sales_target_monthly ?? $user->sales_target ?? null;
    $chartDays = $salesStats['days'] ?? [];
@endphp
<div class="ls-two">
    <section class="ls-sec ls-sec--stack">
        <h2>Targets</h2>
        <div class="ls-target-row">
            <div><span>Today</span><strong>@format_currency($salesStats['today'] ?? 0)</strong></div>
            <em>@if($dailyT === null || $dailyT === '') No target set @else @format_currency($dailyT) @endif</em>
        </div>
        <div class="ls-target-row">
            <div><span>This week</span><strong>@format_currency($salesStats['week'] ?? 0)</strong></div>
            <em>@if($weeklyT === null || $weeklyT === '') No target set @else @format_currency($weeklyT) @endif</em>
        </div>
        <div class="ls-target-row">
            <div><span>This month</span><strong>@format_currency($salesStats['month'] ?? 0)</strong></div>
            <em>@if($monthlyT === null || $monthlyT === '') No target set @else @format_currency($monthlyT) @endif</em>
        </div>
    </section>
    <section class="ls-sec ls-sec--stack">
        <div class="ls-hist-head">
            <h2>History</h2>
            <div class="ls-hist-tabs">
                <button type="button" class="is-on" data-hist="7">7 days</button>
                <button type="button" data-hist="14">14 days</button>
                <button type="button" data-hist="30">30 days</button>
            </div>
        </div>
        <div class="ls-hist-body">
            <div class="ls-hist-chart">
                <svg id="lsHistChart" viewBox="0 0 320 140" preserveAspectRatio="none"></svg>
            </div>
            <div class="ls-hist-stats">
                <div><span>Total sales</span><strong id="lsHistTotal">@format_currency($salesStats['total_7'] ?? 0)</strong></div>
                <div><span>Average day total</span><strong id="lsHistAvg">@format_currency($salesStats['avg_day_7'] ?? 0)</strong></div>
                <div><span>Average sale value</span><strong id="lsHistAvgSale">@format_currency($salesStats['avg_day_7'] ?? 0)</strong></div>
            </div>
        </div>
    </section>
</div>
