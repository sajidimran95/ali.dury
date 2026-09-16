@extends('delivery_app.layout')
@section('title', 'Route')
@section('nav_active', 'route')
@section('content')
<div class="top">
    <div>
        <h1>Today’s route</h1>
        <div class="sub">Tap any stop — navigate opens Google Maps</div>
    </div>
</div>
<div class="wrap">
    @if ($errors->any())
        <div class="err">{{ $errors->first() }}</div>
    @endif
    @if(session('status'))
        @php $st = session('status'); @endphp
        <div class="ok">{{ is_array($st) ? ($st['msg'] ?? '') : $st }}</div>
    @endif

    @if (! $route)
        <div class="card">
            <p>No route assigned for {{ $date }}.</p>
        </div>
    @else
        <div class="stats four">
            <div><strong>Total</strong><span>{{ $route->total_orders }}</span></div>
            <div><strong>Done</strong><span>{{ $delivered }}</span></div>
            <div><strong>Left</strong><span>{{ $route->remainingCount() }}</span></div>
            <div><strong>Open</strong><span>{{ $active?->stop_no ?: '—' }}</span></div>
        </div>

        @if ($route->status === 'planned')
            <form method="POST" action="{{ route('delivery.app.start') }}" style="margin-bottom:.75rem">
                @csrf
                <button class="btn btn-p" type="submit" style="width:100%">Start route</button>
            </form>
        @endif

        <div id="dlv-app-map" class="map"></div>

        @if ($active)
            @include('delivery_app.partials.stop-card', ['active' => $active, 'suggested' => $suggested])
        @endif

        <div class="card">
            <div class="muted">Stops</div>
            @foreach ($route->stops as $stop)
                <a class="stop {{ $active && $active->id === $stop->id ? 'on-row' : '' }}" href="{{ route('delivery.app.route', ['stop' => $stop->id]) }}#dlv-stop-card">
                    <div class="num {{ $active && $active->id === $stop->id ? 'on' : '' }}">{{ $stop->stop_no }}</div>
                    <div>
                        <strong>Invoice #{{ $stop->order?->invoice_no }}</strong>
                        <div class="muted">{{ $stop->ship_to_name }} · {{ $stop->statusLabel() }}</div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection

@section('scripts')
@if ($route)
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
  const origin = @json(['lat' => $route->start_latitude, 'lng' => $route->start_longitude, 'name' => $route->start_name]);
  const stops = (@json($mapStops) || []).slice().sort(function (a, b) {
    return (Number(a.n) || 0) - (Number(b.n) || 0);
  });
  const home = @json(route('delivery.app.route'));
  const el = document.getElementById('dlv-app-map');
  if (!el || !window.L) return;
  const pin = (label, start, status) => {
    let cls = 'is-pending';
    if (start) cls = 'is-start';
    else if (status === 'delivered') cls = 'is-delivered';
    else if (status === 'failed') cls = 'is-failed';
    else if (status === 'en_route' || status === 'arrived') cls = 'is-en_route';
    return L.divIcon({
      className: '',
      html: '<span class="dlv-map-num '+cls+'">'+label+'</span>',
      iconSize: [36, 36], iconAnchor: [18, 18]
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
  const map = L.map(el);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OSM' }).addTo(map);
  const linePts = [];
  if (origin.lat && origin.lng) {
    linePts.push([origin.lat, origin.lng]);
    L.marker([origin.lat, origin.lng], { icon: pin('S', true, 'start') }).addTo(map).bindPopup('POS / Start');
  }
  stops.forEach((s) => {
    if (!s.lat || !s.lng) return;
    const p = [s.lat, s.lng];
    if (linePts.length) {
      drawSeg(map, linePts[linePts.length - 1], p, s.status);
    }
    linePts.push(p);
    const m = L.marker([s.lat, s.lng], { icon: pin(String(s.n), false, s.status) }).addTo(map)
      .bindPopup('Stop '+s.n+' '+(s.label||'')+' · '+(s.status||'pending'));
    m.on('click', function () {
      window.location.href = home + '?stop=' + s.id + '#dlv-stop-card';
    });
  });
  setTimeout(() => {
    map.invalidateSize();
    if (linePts.length) map.fitBounds(linePts, { padding: [28, 28] });
    else map.setView([42.3314, -83.0458], 7);
  }, 250);
})();
</script>
@endif
@endsection
