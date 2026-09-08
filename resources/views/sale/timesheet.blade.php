@extends('sale.layout')
@section('title', 'Timesheet')
@section('header', 'Timesheet')
@section('content')
@php
    $entries = $entries ?? collect();
@endphp

<div class="sale-ts">
    <div class="sale-ts__card" id="saleTsWidget">
        <div class="sale-ts__state" id="saleTsState">Not clocked in</div>
        <div class="sale-ts__meta" id="saleTsMeta"></div>
        <div class="sale-ts__timers">
            <div>
                <span>Worked</span>
                <strong id="saleTsWorked">00:00:00</strong>
            </div>
            <div>
                <span>Break</span>
                <strong id="saleTsBreak">00:00:00</strong>
            </div>
        </div>
        <div class="sale-ts__actions">
            <button type="button" class="sale-btn" id="saleTsIn">Clock In</button>
            <button type="button" class="sale-btn-ghost hidden" id="saleTsBreakStart">Start Break</button>
            <button type="button" class="sale-btn-ghost hidden" id="saleTsBreakEnd">End Break</button>
            <button type="button" class="sale-btn hidden" id="saleTsOut" style="background:#0f172a">Clock Out</button>
        </div>
        <p class="sale-ts__hint" id="saleTsMsg"></p>
    </div>

    <div class="sale-sec-title mt-4 mb-2">Recent</div>
    @forelse($entries as $row)
        <div class="sale-ts__row">
            <div>
                <div class="font-extrabold text-sm">{{ optional($row->clock_in_at)->timezone(session('business.time_zone', config('app.timezone')))->format('D, M j') }}</div>
                <div class="text-xs text-slate-500 mt-0.5">
                    {{ optional($row->clock_in_at)->timezone(session('business.time_zone', config('app.timezone')))->format('g:i A') }}
                    –
                    {{ $row->clock_out_at ? optional($row->clock_out_at)->timezone(session('business.time_zone', config('app.timezone')))->format('g:i A') : 'open' }}
                </div>
            </div>
            <div class="text-right">
                <div class="text-sm font-extrabold">{{ \App\EmployeeAttendance::formatDuration($row->workedSeconds()) }}</div>
                <div class="text-[11px] text-slate-400">{{ ucfirst(str_replace('_', ' ', $row->status)) }}</div>
            </div>
        </div>
    @empty
        <p class="text-sm text-slate-400 text-center py-6">No timesheet entries yet. Clock in to start.</p>
    @endforelse
</div>
@endsection

@push('head')
<style>
    .sale-ts__card {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 18px 16px;
        box-shadow: 0 1px 2px rgba(15,23,42,.04);
    }
    .sale-ts__state { font-size: 20px; font-weight: 800; color: #0f172a; }
    .sale-ts__meta { font-size: 12px; font-weight: 600; color: #64748b; min-height: 18px; margin-top: 4px; }
    .sale-ts__timers { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 16px 0; }
    .sale-ts__timers div { background: #f8fafc; border-radius: 12px; padding: 12px; }
    .sale-ts__timers span { display: block; font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; }
    .sale-ts__timers strong { display: block; font-size: 22px; font-weight: 800; margin-top: 4px; font-variant-numeric: tabular-nums; }
    .sale-ts__actions { display: flex; flex-wrap: wrap; gap: 8px; }
    .sale-ts__actions .sale-btn, .sale-ts__actions .sale-btn-ghost { flex: 1; min-width: 120px; justify-content: center; }
    .sale-ts__hint { min-height: 18px; margin-top: 10px; font-size: 12px; font-weight: 700; color: #e53935; }
    .sale-ts__row {
        display: flex; justify-content: space-between; align-items: center; gap: 12px;
        background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px; margin-bottom: 8px;
    }
    .sale-ts__card.is-working .sale-ts__state { color: #43a047; }
    .sale-ts__card.is-break .sale-ts__state { color: #f59e0b; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const urls = {
        status: @json(route('sale.timesheet.status')),
        in: @json(route('sale.timesheet.clock_in')),
        breakStart: @json(route('sale.timesheet.start_break')),
        breakEnd: @json(route('sale.timesheet.end_break')),
        out: @json(route('sale.timesheet.clock_out')),
    };
    const el = {
        widget: document.getElementById('saleTsWidget'),
        state: document.getElementById('saleTsState'),
        meta: document.getElementById('saleTsMeta'),
        worked: document.getElementById('saleTsWorked'),
        brk: document.getElementById('saleTsBreak'),
        msg: document.getElementById('saleTsMsg'),
        in: document.getElementById('saleTsIn'),
        bStart: document.getElementById('saleTsBreakStart'),
        bEnd: document.getElementById('saleTsBreakEnd'),
        out: document.getElementById('saleTsOut'),
    };

    let attState = 'clocked_out';
    let clockInAt = null;
    let baseWorked = 0;
    let baseBreak = 0;
    let loadedAt = Date.now();
    let lastGeo = null;
    let busy = false;

    function pad(n) { return n < 10 ? '0' + n : '' + n; }
    function formatSecs(total) {
        total = Math.max(0, Math.floor(total || 0));
        return pad(Math.floor(total / 3600)) + ':' + pad(Math.floor((total % 3600) / 60)) + ':' + pad(total % 60);
    }
    function hide(node, on) { node.classList.toggle('hidden', on); }

    function applyStatus(st) {
        if (!st) return;
        attState = st.state || 'clocked_out';
        clockInAt = st.clock_in_at ? new Date(st.clock_in_at) : null;
        baseWorked = parseInt(st.worked_seconds, 10) || 0;
        baseBreak = parseInt(st.break_seconds, 10) || 0;
        loadedAt = Date.now();
        el.widget.classList.remove('is-working', 'is-break');
        hide(el.in, true); hide(el.bStart, true); hide(el.bEnd, true); hide(el.out, true);
        if (attState === 'clocked_in') {
            el.widget.classList.add('is-working');
            el.state.textContent = 'Working';
            el.meta.textContent = clockInAt ? ('Clocked in at ' + clockInAt.toLocaleTimeString()) : '';
            hide(el.bStart, false); hide(el.out, false);
        } else if (attState === 'on_break') {
            el.widget.classList.add('is-break');
            el.state.textContent = 'On Break';
            el.meta.textContent = 'Break in progress';
            hide(el.bEnd, false); hide(el.out, false);
        } else {
            el.state.textContent = 'Not clocked in';
            el.meta.textContent = '';
            hide(el.in, false);
        }
        tick();
    }

    function tick() {
        const now = Date.now();
        const elapsed = Math.max(0, Math.floor((now - loadedAt) / 1000));
        let worked = baseWorked, brk = baseBreak;
        if (attState === 'clocked_in') {
            worked = clockInAt && !isNaN(clockInAt.getTime())
                ? Math.max(0, Math.floor((now - clockInAt.getTime()) / 1000) - baseBreak)
                : baseWorked + elapsed;
        } else if (attState === 'on_break') {
            brk = baseBreak + elapsed;
        }
        el.worked.textContent = formatSecs(worked);
        el.brk.textContent = formatSecs(brk);
    }

    function geoPayload() {
        return lastGeo ? { latitude: lastGeo.latitude, longitude: lastGeo.longitude } : {};
    }
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (pos) {
            lastGeo = { latitude: pos.coords.latitude, longitude: pos.coords.longitude };
        }, function () {}, { enableHighAccuracy: false, timeout: 2000, maximumAge: 300000 });
    }

    async function loadStatus() {
        const res = await fetch(urls.status, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
        applyStatus(await res.json());
    }

    async function post(url) {
        if (busy) return;
        busy = true;
        el.msg.textContent = '';
        try {
            const body = new URLSearchParams(geoPayload());
            body.set('_token', csrf);
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': csrf
                },
                body
            });
            const data = await res.json();
            if (data.status) applyStatus(data.status);
            el.msg.textContent = data.msg || '';
            el.msg.style.color = data.success ? '#43a047' : '#e53935';
            if (data.success && (url === urls.out)) {
                setTimeout(function () { window.location.reload(); }, 600);
            }
        } catch (e) {
            el.msg.textContent = 'Could not update timesheet';
        }
        busy = false;
    }

    el.in.addEventListener('click', function () { post(urls.in); });
    el.bStart.addEventListener('click', function () { post(urls.breakStart); });
    el.bEnd.addEventListener('click', function () { post(urls.breakEnd); });
    el.out.addEventListener('click', function () { post(urls.out); });
    setInterval(tick, 1000);
    loadStatus();
})();
</script>
@endpush
