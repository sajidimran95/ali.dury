<?php

namespace App\Services\Delivery;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Nearest-neighbor + 2-opt from POS/warehouse origin (haversine).
 * Stop 1 = nearest to POS, then 2, 3… along the route.
 */
class RouteOptimizationService
{
    /**
     * @param  array{lat:float,lng:float}  $origin
     * @param  array<int, array{lat:float,lng:float}>  $stops keyed by transaction id
     * @return array{order:list<int>,distances:array<int,int>,durations:array<int,int>,total_distance:int,total_duration:int}
     */
    public function optimize(array $origin, array $stops): array
    {
        if ($stops === []) {
            return ['order' => [], 'distances' => [], 'durations' => [], 'total_distance' => 0, 'total_duration' => 0];
        }

        $ids = array_keys($stops);
        // From POS: pick nearest unpaid stop as #1, then nearest remaining, etc.
        $order = $this->nearestNeighbor($origin, $stops, $ids);
        $order = $this->twoOpt($origin, $stops, $order);

        $distances = [];
        $durations = [];
        $totalDist = 0;
        $totalDur = 0;
        $prev = $origin;
        foreach ($order as $id) {
            $cur = $stops[$id];
            $m = (int) round($this->haversineMeters($prev['lat'], $prev['lng'], $cur['lat'], $cur['lng']));
            $sec = (int) max(60, round($m / 8.33)); // ~30 km/h
            $distances[$id] = $m;
            $durations[$id] = $sec;
            $totalDist += $m;
            $totalDur += $sec;
            $prev = $cur;
        }

        return [
            'order' => array_values($order),
            'distances' => $distances,
            'durations' => $durations,
            'total_distance' => $totalDist,
            'total_duration' => $totalDur,
        ];
    }

    /**
     * @return array{lat:float,lng:float}|null
     */
    public function geocode(string $query): ?array
    {
        $query = trim(preg_replace('/\s+/', ' ', $query) ?? '');
        if ($query === '') {
            return null;
        }

        $cacheKey = 'delivery_geo_'.md5(mb_strtolower($query));
        $cached = Cache::get($cacheKey);
        if (is_array($cached) && isset($cached['lat'], $cached['lng'])) {
            return $cached;
        }

        $hit = $this->geocodeNominatim($query) ?? $this->geocodePhoton($query);
        if ($hit) {
            Cache::put($cacheKey, $hit, now()->addDays(30));

            return $hit;
        }

        Log::warning('Delivery geocode failed.', ['query' => $query]);

        return null;
    }

    private function nearestNeighbor(array $origin, array $stops, array $ids): array
    {
        $remaining = array_values($ids);
        $path = [];
        $cur = $origin;
        while ($remaining !== []) {
            $best = null;
            $bestD = PHP_FLOAT_MAX;
            foreach ($remaining as $id) {
                $d = $this->haversineMeters($cur['lat'], $cur['lng'], $stops[$id]['lat'], $stops[$id]['lng']);
                if ($d < $bestD) {
                    $bestD = $d;
                    $best = $id;
                }
            }
            $path[] = $best;
            $cur = $stops[$best];
            $remaining = array_values(array_filter($remaining, fn ($i) => $i !== $best));
        }

        return $path;
    }

    private function twoOpt(array $origin, array $stops, array $order): array
    {
        $n = count($order);
        if ($n < 4) {
            return $order;
        }
        $improved = true;
        $guard = 0;
        while ($improved && $guard < 50) {
            $improved = false;
            $guard++;
            for ($i = 0; $i < $n - 2; $i++) {
                for ($k = $i + 1; $k < $n - 1; $k++) {
                    $a = $i === 0 ? $origin : $stops[$order[$i - 1]];
                    $b = $stops[$order[$i]];
                    $c = $stops[$order[$k]];
                    $d = $stops[$order[$k + 1]];
                    $before = $this->haversineMeters($a['lat'], $a['lng'], $b['lat'], $b['lng'])
                        + $this->haversineMeters($c['lat'], $c['lng'], $d['lat'], $d['lng']);
                    $after = $this->haversineMeters($a['lat'], $a['lng'], $c['lat'], $c['lng'])
                        + $this->haversineMeters($b['lat'], $b['lng'], $d['lat'], $d['lng']);
                    if ($after + 1 < $before) {
                        $slice = array_reverse(array_slice($order, $i, $k - $i + 1));
                        array_splice($order, $i, $k - $i + 1, $slice);
                        $improved = true;
                    }
                }
            }
        }

        return $order;
    }

    private function haversineMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371000;
        $p1 = deg2rad($lat1);
        $p2 = deg2rad($lat2);
        $dp = deg2rad($lat2 - $lat1);
        $dl = deg2rad($lng2 - $lng1);
        $a = sin($dp / 2) ** 2 + cos($p1) * cos($p2) * sin($dl / 2) ** 2;

        return 2 * $r * asin(min(1, sqrt($a)));
    }

    private function geocodeNominatim(string $query): ?array
    {
        try {
            $ua = 'JapsPOS-Delivery/1.0 ('.rtrim((string) config('app.url'), '/').')';
            $response = Http::timeout(12)
                ->withUserAgent($ua)
                ->withHeaders(['Accept' => 'application/json', 'Referer' => (string) config('app.url')])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $query,
                    'format' => 'json',
                    'limit' => 1,
                ]);
            if (! $response->successful()) {
                return null;
            }
            $first = $response->json('0');
            if (is_array($first) && isset($first['lat'], $first['lon'])) {
                return ['lat' => (float) $first['lat'], 'lng' => (float) $first['lon']];
            }
        } catch (\Throwable $e) {
            Log::notice('Nominatim geocode failed.', ['error' => $e->getMessage()]);
        }

        return null;
    }

    private function geocodePhoton(string $query): ?array
    {
        try {
            $response = Http::timeout(12)
                ->withUserAgent('JapsPOS-Delivery/1.0')
                ->acceptJson()
                ->get('https://photon.komoot.io/api/', [
                    'q' => $query,
                    'limit' => 1,
                    'lang' => 'en',
                ]);
            $coords = $response->json('features.0.geometry.coordinates');
            if (is_array($coords) && isset($coords[0], $coords[1])) {
                return ['lat' => (float) $coords[1], 'lng' => (float) $coords[0]];
            }
        } catch (\Throwable $e) {
            Log::notice('Photon geocode failed.', ['error' => $e->getMessage()]);
        }

        return null;
    }
}
