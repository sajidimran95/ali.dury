@extends('layouts.app')
@section('title', __('delivery.route_detail'))

@section('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
.dlv-admin-page { margin: 0 -15px; }
.dlv-summary {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(7.5rem, 1fr));
    gap: 0.45rem;
    padding: 0.65rem 1rem;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}
.dlv-summary div { background: #fff; border: 1px solid #e2e8f0; padding: 0.4rem 0.55rem; border-radius: 4px; }
.dlv-summary strong { display: block; font-size: 11px; color: #64748b; text-transform: uppercase; }
.dlv-summary span { font-size: 13px; font-weight: 600; color: #0f172a; }
.dlv-route-split { display: flex; min-height: 32rem; border-top: 1px solid #e2e8f0; }
.dlv-route-stops { flex: 0 0 28rem; max-width: 28rem; overflow: auto; min-height: 32rem; background: #fff; border-right: 1px solid #e2e8f0; }
.dlv-route-map-wrap { flex: 1 1 auto; min-width: 0; min-height: 32rem; position: relative; background: #d5dee8; }
.dlv-map { position: absolute; inset: 0; z-index: 1; }
.dlv-timeline { list-style: none; padding: 0.5rem 0.75rem 1.25rem; margin: 0; }
.dlv-timeline li { display: flex; gap: 0.65rem; padding: 0.7rem 0.55rem; border-bottom: 1px solid #eef2f6; }
.dlv-timeline li p { margin: 0.15rem 0 0; font-size: 13px; color: #334155; }
.dlv-timeline li strong { font-size: 14px; color: #0f172a; }
.dlv-stop-badge {
    flex-shrink: 0; width: 1.85rem; height: 1.85rem; border-radius: 999px;
    background: #eab308; color: #422006; display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 800;
}
.dlv-stop-badge.is-start { background: #0f766e; color: #fff; font-size: 9px; }
.dlv-stop-badge.is-pending { background: #eab308; color: #422006; }
.dlv-stop-badge.is-delivered { background: #16a34a; color: #fff; }
.dlv-stop-badge.is-failed { background: #dc2626; color: #fff; }
.dlv-stop-badge.is-en_route,
.dlv-stop-badge.is-arrived { background: #f59e0b; color: #fff; }
.dlv-pill { display: inline-block; font-size: 12px; font-weight: 700; padding: 0.1rem 0.45rem; border-radius: 999px; background: #e2e8f0; color: #475569; margin: 0.25rem 0.35rem 0.25rem 0; }
.dlv-pill.is-delivered { background: #d1fae5; color: #065f46; }
.dlv-pill.is-en_route { background: #fef3c7; color: #92400e; }
.dlv-pill.is-failed { background: #fee2e2; color: #991b1b; }
.dlv-pill.is-pending { background: #e2e8f0; color: #475569; }
.dlv-muted { color: #64748b; font-size: 12px; }
.dlv-map-num {
    display: flex; width: 36px; height: 36px; align-items: center; justify-content: center;
    border-radius: 999px; background: #ffd000; color: #1a1400; font-size: 14px; font-weight: 900;
    border: 3px solid #fff; box-shadow: 0 0 0 3px rgba(255,208,0,.55), 0 4px 12px rgba(0,0,0,.45);
}
.dlv-map-num.is-start { background: #0ea5e9; color: #fff; font-size: 12px; box-shadow: 0 0 0 3px rgba(14,165,233,.5), 0 4px 12px rgba(0,0,0,.45); }
.dlv-map-num.is-pending { background: #ffd000; color: #1a1400; box-shadow: 0 0 0 4px rgba(255,208,0,.65), 0 4px 14px rgba(0,0,0,.5); }
.dlv-map-num.is-delivered { background: #22c55e; color: #fff; box-shadow: 0 0 0 4px rgba(34,197,94,.7), 0 4px 14px rgba(0,0,0,.5); }
.dlv-map-num.is-failed { background: #ef4444; color: #fff; box-shadow: 0 0 0 4px rgba(239,68,68,.7), 0 4px 14px rgba(0,0,0,.5); }
.dlv-map-num.is-en_route,
.dlv-map-num.is-arrived { background: #f97316; color: #fff; box-shadow: 0 0 0 4px rgba(249,115,22,.65), 0 4px 14px rgba(0,0,0,.5); }
.dlv-map-legend {
    position: absolute; z-index: 500; left: 10px; bottom: 10px;
    background: rgba(15,23,42,.88); color: #fff; border-radius: 10px; padding: 8px 10px;
    font-size: 12px; font-weight: 700; display: flex; gap: 10px; align-items: center;
}
.dlv-map-legend i { display:inline-block; width:10px; height:10px; border-radius:999px; margin-right:4px; vertical-align:middle; }
.dlv-map-legend .lg-p { background:#ffd000; }
.dlv-map-legend .lg-d { background:#22c55e; }
.dlv-map-legend .lg-f { background:#ef4444; }
.dlv-filter-bar {
    display: flex;
    flex-wrap: nowrap;
    align-items: center;
    gap: 8px;
    padding: 10px 15px;
    background: #fff;
    border-bottom: 1px solid #e2e8f0;
}
.dlv-filter-bar form { display: flex; align-items: center; gap: 8px; margin: 0; }
.dlv-filter-bar .form-control {
    height: 34px;
    min-width: 12rem;
    margin: 0;
    display: inline-block;
    vertical-align: middle;
}
.dlv-filter-bar .btn {
    height: 34px;
    line-height: 32px;
    padding: 0 14px;
    margin: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    vertical-align: middle;
    white-space: nowrap;
}
.dlv-filter-bar .dlv-stop-count {
    color: #64748b;
    font-size: 13px;
    line-height: 34px;
    white-space: nowrap;
    margin: 0 4px 0 0;
}
.dlv-filter-bar .dlv-filter-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-left: auto;
}
.dlv-progress { height: 8px; background: #e2e8f0; border-radius: 999px; overflow: hidden; min-width: 8rem; }
.dlv-progress span { display: block; height: 100%; background: #0f766e; border-radius: 999px; }
@media (max-width: 991px) {
    .dlv-route-split { flex-direction: column; }
    .dlv-route-stops { flex: none; max-width: none; min-height: 18rem; border-right: 0; border-bottom: 1px solid #e2e8f0; }
    .dlv-route-map-wrap { min-height: 22rem; }
}
.dlv-driver-pin { display:flex; width:40px; height:40px; align-items:center; justify-content:center; border-radius:999px; background:#2563eb; color:#fff; font-size:20px; border:3px solid #fff; box-shadow:0 0 0 6px rgba(37,99,235,.35), 0 4px 14px rgba(0,0,0,.45); }
.dlv-driver-pin.is-stale { background:#94a3b8; box-shadow:0 0 0 6px rgba(148,163,184,.35), 0 4px 14px rgba(0,0,0,.35); }
.dlv-map-legend .lg-driver { background:#2563eb; }
</style>
@endsection

@section('content')
<section class="content-header">
    <h1>{{ optional($route->driver)->user_full_name }} — {{ __('delivery.route_detail') }}
        <small>{{ @format_date($route->route_date) }}</small>
    </h1>
</section>
<section class="content dlv-admin-page">
    @if(session('status'))
        @php $st = session('status'); @endphp
        <div class="alert {{ !empty($st['success']) ? 'alert-success' : 'alert-danger' }}" style="margin:0 15px 10px;">{{ $st['msg'] ?? '' }}</div>
    @endif

    <div class="box box-solid" style="margin-bottom:0;">
        <div class="dlv-filter-bar">
            <form method="GET">
                <select name="listFilter" class="form-control" onchange="this.form.submit()">
                    <option value="today" @selected($listFilter==='today')>Today / Remaining</option>
                    <option value="pending" @selected($listFilter==='pending')>Pending</option>
                    <option value="all" @selected($listFilter==='all')>All stops</option>
                </select>
            </form>
            <span class="dlv-stop-count">{{ $stops->count() }} stop{{ $stops->count() === 1 ? '' : 's' }}</span>
            <div class="dlv-filter-actions">
                <a href="{{ route('deliveries.routes', ['date' => $route->route_date->format('Y-m-d')]) }}" class="btn btn-default">{{ __('messages.go_back') }}</a>
                <a href="{{ route('deliveries.assign', ['date' => $route->route_date->format('Y-m-d')]) }}" class="btn btn-primary">{{ __('delivery.assign_orders') }}</a>
            </div>
        </div>

        <div class="dlv-summary">
            <div><strong>Date</strong><span>{{ $route->route_date?->format('F j, Y') }}</span></div>
            <div><strong>Start</strong><span>{{ $route->start_name }}</span></div>
            <div><strong>Total orders</strong><span>{{ $route->total_orders }}</span></div>
            <div><strong>Delivered</strong><span>{{ $delivered }}</span></div>
            <div><strong>Remaining</strong><span>{{ $remaining }}</span></div>
            <div><strong>Distance</strong><span>{{ $miles }} mi</span></div>
            <div><strong>Est. time</strong><span>{{ $minutes }} min</span></div>
            <div><strong>Status</strong><span id="dlv-route-status">{{ $route->statusLabel() }}</span></div>
            <div><strong>Driver location</strong><span id="dlv-driver-seen">{{ $driverLocation['ago'] ? 'Updated '.$driverLocation['ago'] : 'Not shared yet' }}</span></div>
        </div>

        <div class="dlv-route-split">
            <div class="dlv-route-stops">
                <ol class="dlv-timeline">
                    <li>
                        <div class="dlv-stop-badge is-start">S</div>
                        <div>
                            <strong>{{ $route->start_name }}</strong>
                            <p>{{ $route->start_address }}</p>
                        </div>
                    </li>
                    @forelse ($stops as $stop)
                        @php
                            $pill = match ($stop->status) {
                                'delivered' => 'delivered',
                                'failed' => 'failed',
                                'en_route', 'arrived' => 'en_route',
                                default => 'pending',
                            };
                        @endphp
                        <li>
                            <div class="dlv-stop-badge is-{{ $pill }}">{{ $stop->stop_no }}</div>
                            <div>
                                <strong>Invoice #{{ $stop->order?->invoice_no ?? $stop->transaction?->invoice_no }}</strong>
                                <p>{{ $stop->ship_to_name }}</p>
                                <p class="dlv-muted">{!! nl2br(e($stop->formattedAddress())) !!}</p>
                                <span class="dlv-pill is-{{ $pill }}">{{ $stop->statusLabel() }}</span>
                                @if ($stop->delivery_notes)
                                    <p class="dlv-muted">Note: {{ $stop->delivery_notes }}</p>
                                @endif
                                <a class="btn btn-xs btn-primary" href="{{ $stop->navigateUrl() }}" target="_blank" rel="noopener">{{ __('delivery.navigate') }}</a>
                            </div>
                        </li>
                    @empty
                        <li>
                            <div>
                                <p class="dlv-muted">
                                    @if ($listFilter === 'pending')
                                        No pending stops on this route.
                                    @elseif ($listFilter === 'today')
                                        No remaining stops for today.
                                    @else
                                        No stops on this route.
                                    @endif
                                </p>
                            </div>
                        </li>
                    @endforelse
                </ol>
            </div>
            <div class="dlv-route-map-wrap">
                <div id="dlv-admin-map" class="dlv-map"></div>
                <div class="dlv-map-legend">
                    <span><i class="lg-p"></i> Pending</span>
                    <span><i class="lg-d"></i> Delivered</span>
                    <span><i class="lg-f"></i> Failed</span>
                    <span><i class="lg-driver"></i> Driver</span>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('javascript')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
  const origin = @json(['lat' => $route->start_latitude, 'lng' => $route->start_longitude, 'name' => $route->start_name]);
  // Always draw Start → 1 → 2 → 3… by stop number
  const stops = (@json($mapStops) || []).slice().sort(function (a, b) {
    return (Number(a.n) || 0) - (Number(b.n) || 0);
  });
  const pin = (label, start, status) => {
    let cls = 'is-pending';
    if (start) cls = 'is-start';
    else if (status === 'delivered') cls = 'is-delivered';
    else if (status === 'failed') cls = 'is-failed';
    else if (status === 'en_route' || status === 'arrived') cls = 'is-en_route';
    return L.divIcon({
      className: '',
      html: '<span class="dlv-map-num ' + cls + '">' + label + '</span>',
      iconSize: [36, 36],
      iconAnchor: [18, 18],
      popupAnchor: [0, -18],
    });
  };
  const lineColor = (status) => {
    if (status === 'delivered') return '#22c55e';
    if (status === 'failed') return '#ef4444';
    if (status === 'en_route' || status === 'arrived') return '#f97316';
    return '#ffd000';
  };
  const drawSeg = (map, a, b, status) => {
    L.polyline([a, b], { color: '#0f172a', weight: 12, opacity: 0.55, lineJoin: 'round', lineCap: 'round' }).addTo(map);
    L.polyline([a, b], { color: lineColor(status), weight: 8, opacity: 1, lineJoin: 'round', lineCap: 'round' }).addTo(map);
  };
  const el = document.getElementById('dlv-admin-map');
  if (!window.L || !el) return;
  const map = L.map(el, { scrollWheelZoom: true });
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap'
  }).addTo(map);

  const linePts = [];
  if (origin.lat && origin.lng) {
    linePts.push([origin.lat, origin.lng]);
    L.marker([origin.lat, origin.lng], { icon: pin('S', true, 'start') })
      .addTo(map)
      .bindPopup('Start: ' + (origin.name || 'Warehouse'));
  }

  stops.forEach(function (s) {
    if (!s.lat || !s.lng) return;
    const p = [s.lat, s.lng];
    if (linePts.length) {
      drawSeg(map, linePts[linePts.length - 1], p, s.status);
    }
    linePts.push(p);
    L.marker(p, { icon: pin(String(s.n), false, s.status) })
      .addTo(map)
      .bindPopup('Stop ' + s.n + ' · Invoice ' + (s.label || '') + (s.name ? '<br>' + s.name : '') + '<br>' + (s.status || 'pending'));
  });

  let driver = @json($driverLocation);
  let driverMarker = null;
  const seenEl = document.getElementById('dlv-driver-seen');
  const statusEl = document.getElementById('dlv-route-status');
  const driverIcon = (stale) => L.divIcon({
    className: '',
    html: '<span class="dlv-driver-pin' + (stale ? ' is-stale' : '') + '">&#128666;</span>',
    iconSize: [40, 40],
    iconAnchor: [20, 20],
    popupAnchor: [0, -20],
  });
  const showDriver = () => {
    if (seenEl) seenEl.textContent = driver.ago ? 'Updated ' + driver.ago : 'Not shared yet';
    if (statusEl && driver.status_label) statusEl.textContent = driver.status_label;
    if (!driver.lat || !driver.lng) return;
    const p = [driver.lat, driver.lng];
    const label = 'Driver · ' + (driver.ago ? 'updated ' + driver.ago : '') + (driver.accuracy ? '<br>± ' + driver.accuracy + ' m' : '');
    if (!driverMarker) {
      driverMarker = L.marker(p, { icon: driverIcon(driver.stale), zIndexOffset: 1000 }).addTo(map).bindPopup(label);
    } else {
      driverMarker.setLatLng(p).setIcon(driverIcon(driver.stale)).setPopupContent(label);
    }
  };

  const fit = () => {
    map.invalidateSize();
    const pts = linePts.slice();
    if (driver.lat && driver.lng) pts.push([driver.lat, driver.lng]);
    if (pts.length) map.fitBounds(pts, { padding: [36, 36] });
    else map.setView([42.3314, -83.0458], 7);
  };
  showDriver();
  requestAnimationFrame(function () { fit(); setTimeout(fit, 200); setTimeout(fit, 800); });

  const locationUrl = @json(route('deliveries.routes.driver_location', ['id' => $route->id]));
  setInterval(function () {
    if (document.hidden) return;
    fetch(locationUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (d) { if (d) { driver = d; showDriver(); } })
      .catch(function () {});
  }, 20000);
})();
</script>
@endsection
