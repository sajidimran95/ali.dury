<?php

namespace App\Http\Controllers\Api\Mobile;

use App\DeliveryRoute;
use App\DeliveryRouteOrder;
use App\Http\Controllers\Api\Mobile\Concerns\MobileApiResponses;
use App\Http\Controllers\Controller;
use App\Services\Delivery\DeliveryRouteService;
use Illuminate\Http\Request;

/**
 * Native Delivery (driver) app — JSON version of DeliveryApp\DeliveryAppController.
 * Same service calls, same ownership rules (driver only sees own routes/stops).
 */
class DeliveryApiController extends Controller
{
    use MobileApiResponses;

    public function __construct(private DeliveryRouteService $routes) {}

    /** Dashboard (PWA: /delivery). */
    public function home(Request $request)
    {
        $user = $request->user('delivery');
        $date = now()->toDateString();
        $route = $this->todayRoute($request);
        $deliveredToday = $route ? $route->stops->where('status', 'delivered')->count() : 0;
        $failedToday = $route ? $route->stops->where('status', 'failed')->count() : 0;
        $left = $route ? $route->remainingCount() : 0;
        $suggested = $route?->currentStop();
        $total = (int) ($route?->total_orders ?? 0);

        return $this->ok([
            'date' => $date,
            'driver' => [
                'id' => $user->id,
                'name' => $user->user_full_name ?: $user->username,
                'first_name' => explode(' ', $user->user_full_name ?: $user->username)[0],
            ],
            'stats' => [
                'today_stops' => $total,
                'pending' => $left,
                'delivered_today' => $deliveredToday,
                'failed_today' => $failedToday,
                'all_time_delivered' => $this->routes->driverFinishedCount($user),
                'progress_percent' => $total > 0 ? (int) round(($deliveredToday / $total) * 100) : 0,
            ],
            'route' => $route ? $this->routeSummary($route) : null,
            'next_stop' => $suggested ? $this->stopPayload($suggested) : null,
        ]);
    }

    /** Today's route with all stops + map points (PWA: /delivery/route?stop=). */
    public function route(Request $request)
    {
        $date = now()->toDateString();
        $route = $this->routeFromStopQuery($request) ?? $this->todayRoute($request);
        if (! $route) {
            return $this->ok([
                'date' => $date,
                'route' => null,
                'active_stop' => null,
                'suggested_stop_id' => null,
                'stops' => [],
                'map' => null,
            ], 'No route assigned for '.$date.'.');
        }

        $active = $this->activeStop($route, (int) $request->query('stop', 0));
        $suggested = $route->currentStop();
        $stops = $route->stops->sortBy('stop_no')->values();

        return $this->ok([
            'date' => $date,
            'route' => $this->routeSummary($route),
            'active_stop' => $active ? $this->stopPayload($active) : null,
            'suggested_stop_id' => $suggested?->id,
            'stops' => $stops->map(fn ($s) => $this->stopPayload($s))->values(),
            'map' => [
                'origin' => [
                    'name' => $route->start_name,
                    'address' => $route->start_address,
                    'lat' => $route->start_latitude,
                    'lng' => $route->start_longitude,
                ],
                'points' => $stops->map(fn ($s) => [
                    'id' => $s->id,
                    'n' => (int) $s->stop_no,
                    'lat' => $s->latitude ? (float) $s->latitude : null,
                    'lng' => $s->longitude ? (float) $s->longitude : null,
                    'label' => '#'.($s->order?->invoice_no ?? $s->transaction_id),
                    'name' => $s->ship_to_name,
                    'status' => $s->status,
                ])->values(),
            ],
        ]);
    }

    /** One stop (must belong to this driver). */
    public function showStop(Request $request, $stop)
    {
        $row = $this->ownedStop($request, (int) $stop);
        $row->load(['route', 'order.contact']);

        return $this->ok($this->stopPayload($row, true));
    }

    /** Open (not delivered / skipped) stops in a date range (PWA: /delivery/assigned). */
    public function assigned(Request $request)
    {
        $user = $request->user('delivery');
        [$from, $to] = $this->dateRange($request, now()->toDateString(), now()->toDateString());
        $stops = $this->routes->driverStopsInRange($user, $from, $to, 'open');

        return $this->ok([
            'from' => $from,
            'to' => $to,
            'count' => $stops->count(),
            'stops' => $stops->map(fn ($s) => $this->stopPayload($s))->values(),
        ]);
    }

    /** All stops in a date range grouped per day (PWA: /delivery/all). */
    public function all(Request $request)
    {
        $user = $request->user('delivery');
        [$from, $to] = $this->dateRange($request, now()->subDays(7)->toDateString(), now()->toDateString());
        $stops = $this->routes->driverStopsInRange($user, $from, $to, 'all');
        $days = $stops
            ->groupBy(fn ($s) => optional($s->route?->route_date)->toDateString() ?: 'unknown')
            ->map(fn ($group, $day) => [
                'date' => $day,
                'stops' => $group->sortBy('stop_no')->values()->map(fn ($s) => $this->stopPayload($s))->values(),
            ])
            ->values();

        return $this->ok([
            'from' => $from,
            'to' => $to,
            'total' => $stops->count(),
            'open_count' => $stops->filter(fn ($s) => $s->canAct())->count(),
            'delivered_count' => $stops->where('status', 'delivered')->count(),
            'days' => $days,
        ]);
    }

    /** Delivered / failed stops (PWA: /delivery/delivered?date= | ?all=1). Max 100. */
    public function history(Request $request)
    {
        $user = $request->user('delivery');
        $all = $request->boolean('all');
        $date = $all ? null : (string) $request->query('date', now()->toDateString());
        if ($date !== null && $date !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = now()->toDateString();
        }
        $stops = $this->routes->driverFinishedStops($user, $all ? null : $date);

        return $this->ok([
            'date' => $date,
            'all' => $all,
            'delivered_count' => $stops->where('status', 'delivered')->count(),
            'failed_count' => $stops->where('status', 'failed')->count(),
            'stops' => $stops->map(fn ($s) => $this->stopPayload($s))->values(),
        ]);
    }

    /** Failure reasons for the "Could not deliver" form. */
    public function failReasons()
    {
        return $this->ok(collect(DeliveryRouteOrder::FAIL_REASONS)
            ->map(fn ($label, $key) => ['key' => $key, 'label' => $label])
            ->values());
    }

    public function start(Request $request)
    {
        $route = $this->todayRoute($request);
        if (! $route) {
            return $this->fail('No route assigned for today.', 404);
        }
        $this->routes->startRoute($request->user('delivery'), $route);

        return $this->ok($this->routeSummary($route->fresh(['stops.order'])), 'Route started.');
    }

    /** Driver GPS ping while today's route is in progress (admin route map shows it live). */
    public function location(Request $request)
    {
        $data = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
        ]);
        $route = $this->todayRoute($request);
        if (! $route || $route->status !== DeliveryRoute::STATUS_STARTED) {
            return $this->ok(['tracking' => false], 'No route in progress.');
        }

        $route->forceFill([
            'driver_latitude' => $data['lat'],
            'driver_longitude' => $data['lng'],
            'driver_accuracy' => isset($data['accuracy']) ? (int) round($data['accuracy']) : null,
            'driver_location_at' => now(),
        ])->save();

        return $this->ok(['tracking' => true], 'Location updated.');
    }

    public function arrived(Request $request, $stop)
    {
        $row = $this->ownedStop($request, (int) $stop);
        $this->routes->markArrived($request->user('delivery'), $row);

        return $this->ok($this->stopPayload($row->fresh(['route', 'order.contact'])), 'Marked as arrived.');
    }

    public function delivered(Request $request, $stop)
    {
        $request->validate(['notes' => 'nullable|string|max:5000']);
        $row = $this->ownedStop($request, (int) $stop);
        $notes = trim((string) $request->input('notes', ''));
        $this->routes->markDelivered($request->user('delivery'), $row, $notes !== '' ? $notes : null);

        return $this->ok($this->stopPayload($row->fresh(['route', 'order.contact'])), 'Marked as delivered.');
    }

    public function failed(Request $request, $stop)
    {
        $request->validate([
            'fail_reason' => 'required|string|in:'.implode(',', array_keys(DeliveryRouteOrder::FAIL_REASONS)),
            'notes' => 'nullable|string|max:5000',
        ]);
        $row = $this->ownedStop($request, (int) $stop);
        $this->routes->markFailed(
            $request->user('delivery'),
            $row,
            (string) $request->input('fail_reason', ''),
            $request->input('notes')
        );

        return $this->ok($this->stopPayload($row->fresh(['route', 'order.contact'])), 'Marked as failed.');
    }

    public function notes(Request $request, $stop)
    {
        $request->validate(['notes' => 'nullable|string|max:5000']);
        $row = $this->ownedStop($request, (int) $stop);
        $this->routes->saveNotes($request->user('delivery'), $row, $request->input('notes'));

        return $this->ok($this->stopPayload($row->fresh(['route', 'order.contact'])), 'Note saved.');
    }

    // ---------------------------------------------------------------- helpers

    protected function routeSummary(DeliveryRoute $route): array
    {
        $route->loadMissing('stops');

        return [
            'id' => $route->id,
            'route_date' => optional($route->route_date)->toDateString(),
            'status' => $route->status,
            'status_label' => $route->statusLabel(),
            'total_orders' => (int) $route->total_orders,
            'delivered' => $route->stops->where('status', 'delivered')->count(),
            'failed' => $route->stops->where('status', 'failed')->count(),
            'remaining' => $route->remainingCount(),
            'total_distance' => (int) $route->total_distance,
            'estimated_duration' => (int) $route->estimated_duration,
            'started_at' => optional($route->started_at)->toIso8601String(),
            'completed_at' => optional($route->completed_at)->toIso8601String(),
            'can_start' => $route->status === DeliveryRoute::STATUS_PLANNED,
            'start' => [
                'name' => $route->start_name,
                'address' => $route->start_address,
                'lat' => $route->start_latitude,
                'lng' => $route->start_longitude,
            ],
        ];
    }

    protected function stopPayload(DeliveryRouteOrder $s, bool $withOrder = false): array
    {
        $order = $s->relationLoaded('order') ? $s->order : null;
        $route = $s->relationLoaded('route') ? $s->route : null;

        $payload = [
            'id' => $s->id,
            'route_id' => $s->delivery_route_id,
            'route_date' => $route ? optional($route->route_date)->toDateString() : null,
            'stop_no' => (int) $s->stop_no,
            'status' => $s->status,
            'status_label' => $s->statusLabel(),
            'transaction_id' => $s->transaction_id,
            'invoice_no' => $order?->invoice_no,
            'invoice_total' => $order ? (float) $order->final_total : null,
            'payment_status' => $order?->payment_status,
            'ship_to_name' => $s->ship_to_name,
            'ship_to_phone' => $s->ship_to_phone,
            'ship_to_address' => $s->ship_to_address,
            'ship_to_city' => $s->ship_to_city,
            'ship_to_state' => $s->ship_to_state,
            'ship_to_zip' => $s->ship_to_zip,
            'formatted_address' => $s->formattedAddress(),
            'latitude' => $s->latitude,
            'longitude' => $s->longitude,
            'navigate_url' => $s->navigateUrl(),
            'distance_from_previous' => (int) $s->distance_from_previous,
            'estimated_duration_from_previous' => (int) $s->estimated_duration_from_previous,
            'arrived_at' => optional($s->arrived_at)->toIso8601String(),
            'delivered_at' => optional($s->delivered_at)->toIso8601String(),
            'fail_reason' => $s->fail_reason,
            'fail_reason_label' => $s->fail_reason ? (DeliveryRouteOrder::FAIL_REASONS[$s->fail_reason] ?? $s->fail_reason) : null,
            'delivery_notes' => $s->delivery_notes,
            'can_act' => $s->canAct(),
            'can_mark_arrived' => $s->canAct() && $s->status !== DeliveryRouteOrder::STATUS_ARRIVED,
            'can_mark_delivered' => $s->canMarkDelivered(),
            'can_mark_failed' => $s->canAct(),
        ];

        if ($withOrder && $order) {
            $payload['order'] = [
                'id' => $order->id,
                'invoice_no' => $order->invoice_no,
                'transaction_date' => $this->dt($order->transaction_date),
                'final_total' => (float) $order->final_total,
                'payment_status' => $order->payment_status,
                'shipping_status' => $order->shipping_status,
                'shipping_details' => $order->shipping_details,
                'shipping_address' => $order->shipping_address,
                'additional_notes' => $order->additional_notes,
                'customer' => $order->contact ? [
                    'id' => $order->contact->id,
                    'name' => $order->contact->name,
                    'business_name' => $order->contact->supplier_business_name,
                    'mobile' => $order->contact->mobile,
                ] : null,
            ];
        }

        return $payload;
    }

    protected function routeFromStopQuery(Request $request): ?DeliveryRoute
    {
        $stopId = (int) $request->query('stop', 0);
        if (! $stopId) {
            return null;
        }
        $stop = DeliveryRouteOrder::query()->with(['route.stops.order', 'route.driver'])->find($stopId);
        if (! $stop?->route || (int) $stop->route->delivery_user_id !== (int) $request->user('delivery')->id) {
            return null;
        }

        return $stop->route;
    }

    /** @return array{0: string, 1: string} */
    protected function dateRange(Request $request, string $defaultFrom, string $defaultTo): array
    {
        $from = (string) $request->query('from', $defaultFrom);
        $to = (string) $request->query('to', $defaultTo);
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
            $from = $defaultFrom;
        }
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $to = $defaultTo;
        }
        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
    }

    protected function todayRoute(Request $request): ?DeliveryRoute
    {
        return $this->routes->driverRouteForDate($request->user('delivery'), now()->toDateString());
    }

    protected function activeStop(?DeliveryRoute $route, int $openId): ?DeliveryRouteOrder
    {
        if (! $route) {
            return null;
        }
        if ($openId) {
            $picked = $route->stops->firstWhere('id', $openId);
            if ($picked) {
                return $picked;
            }
        }

        return $route->currentStop();
    }

    protected function ownedStop(Request $request, int $id): DeliveryRouteOrder
    {
        $user = $request->user('delivery');

        return DeliveryRouteOrder::whereHas('route', function ($q) use ($user) {
            $q->where('business_id', $user->business_id)->where('delivery_user_id', $user->id);
        })->findOrFail($id);
    }
}
