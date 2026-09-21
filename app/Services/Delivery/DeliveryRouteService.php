<?php

namespace App\Services\Delivery;

use App\BusinessLocation;
use App\DeliveryRoute;
use App\DeliveryRouteOrder;
use App\Transaction;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeliveryRouteService
{
    public function __construct(private RouteOptimizationService $optimizer) {}

    /**
     * @param  list<int>  $transactionIds
     */
    public function assignOrders(User $actor, array $transactionIds, int $driverId, string $date): int
    {
        $this->assertManage($actor);
        $businessId = (int) $actor->business_id;
        $driver = $this->deliveryDriver($businessId, $driverId);
        $ids = array_values(array_unique(array_map('intval', $transactionIds)));
        if ($ids === []) {
            throw ValidationException::withMessages(['orders' => 'Select at least one order.']);
        }

        $orders = Transaction::query()
            ->where('business_id', $businessId)
            ->where('type', 'sell')
            ->whereIn('status', ['final'])
            ->whereIn('id', $ids)
            ->get();

        if ($orders->count() !== count($ids)) {
            throw ValidationException::withMessages(['orders' => 'One or more sales were not found.']);
        }

        return DB::transaction(function () use ($orders, $driver, $date) {
            foreach ($orders as $order) {
                $order->delivery_person = $driver->id;
                $order->delivery_date = $date;
                if (! in_array($order->delivery_status, ['delivered', 'failed'], true)) {
                    $order->delivery_status = 'assigned';
                }
                $order->save();
            }

            return $orders->count();
        });
    }

    /**
     * Assign invoices to a driver and immediately put them on that driver's route
     * for the date (admin Today's routes + delivery app).
     */
    public function assignOrdersToTodayRoute(User $actor, array $transactionIds, int $driverId, string $date, ?int $locationId = null): DeliveryRoute
    {
        $this->assignOrders($actor, $transactionIds, $driverId, $date);

        return $this->generateRoute($actor, $driverId, $date, $locationId);
    }

    public function generateRoute(User $actor, int $driverId, string $date, ?int $locationId = null): DeliveryRoute
    {
        $this->assertManage($actor);
        $businessId = (int) $actor->business_id;
        $driver = $this->deliveryDriver($businessId, $driverId);
        $origin = $this->originFromBusiness($businessId, $locationId);

        $orders = Transaction::query()
            ->with('contact')
            ->where('business_id', $businessId)
            ->where('type', 'sell')
            ->where('status', 'final')
            ->where('delivery_person', $driver->id)
            ->whereDate('delivery_date', $date)
            ->orderByDesc('id')
            ->get();

        if ($orders->isEmpty()) {
            throw ValidationException::withMessages(['orders' => 'No assigned invoices for this driver and date.']);
        }

        // Geocode missing addresses so we can order nearest from POS → stop 1, 2, 3…
        foreach ($orders as $i => $order) {
            if ($i > 0) {
                usleep(150000);
            }
            $this->ensureOrderCoordinates($order);
        }

        // Any invoice still without coords gets a near-POS pin so map line S→1→2→3 always draws
        $nearIndex = 0;
        foreach ($orders as $order) {
            if ($order->shipping_latitude && $order->shipping_longitude) {
                continue;
            }
            $near = $this->offsetFromOrigin($origin, $nearIndex++);
            $order->shipping_latitude = $near['lat'];
            $order->shipping_longitude = $near['lng'];
        }

        return DB::transaction(function () use ($businessId, $driver, $date, $origin, $orders, $locationId) {
            $route = DeliveryRoute::query()
                ->with('stops')
                ->where('business_id', $businessId)
                ->where('delivery_user_id', $driver->id)
                ->whereDate('route_date', $date)
                ->whereIn('status', [
                    DeliveryRoute::STATUS_PLANNED,
                    DeliveryRoute::STATUS_STARTED,
                    DeliveryRoute::STATUS_COMPLETED,
                ])
                ->latest('id')
                ->first();

            if (! $route) {
                $route = new DeliveryRoute;
                $route->status = DeliveryRoute::STATUS_PLANNED;
            }

            $route->fill([
                'business_id' => $businessId,
                'delivery_user_id' => $driver->id,
                'business_location_id' => $locationId ?: $route->business_location_id,
                'route_date' => $date,
                'start_name' => $origin['name'] ?? 'Warehouse',
                'start_address' => $origin['address'] ?? null,
                'start_latitude' => $origin['lat'],
                'start_longitude' => $origin['lng'],
            ]);
            $route->save();

            $existingByTx = $route->stops->keyBy(fn ($s) => (int) $s->transaction_id);
            $newIds = [];
            foreach ($orders as $order) {
                $txId = (int) $order->id;
                if ($existingByTx->has($txId)) {
                    continue;
                }
                if (in_array($order->delivery_status, ['delivered', 'failed'], true) && ! $existingByTx->has($txId)) {
                    continue;
                }
                $contact = $order->contact;
                $stop = DeliveryRouteOrder::query()->create([
                    'delivery_route_id' => $route->id,
                    'transaction_id' => $order->id,
                    'stop_no' => 9000 + $txId,
                    'status' => DeliveryRouteOrder::STATUS_PENDING,
                    'ship_to_name' => $contact->supplier_business_name ?: $contact->name ?? $order->delivered_to,
                    'ship_to_phone' => $contact->mobile ?? null,
                    'ship_to_address' => $order->shipping_address,
                    'ship_to_city' => $contact->city ?? null,
                    'ship_to_state' => $contact->state ?? null,
                    'ship_to_zip' => $contact->zip_code ?? null,
                    'latitude' => $order->shipping_latitude,
                    'longitude' => $order->shipping_longitude,
                ]);
                $existingByTx[$txId] = $stop;
                $newIds[] = $txId;
            }

            $route->load('stops');

            $oldStops = $route->stops
                ->reject(fn ($s) => in_array((int) $s->transaction_id, $newIds, true))
                ->sortBy('stop_no')
                ->values();
            $newOrderIds = $orders->whereIn('id', $newIds)->sortBy('id')->pluck('id')->map(fn ($id) => (int) $id)->all();

            $lastPt = $origin;
            if ($oldStops->isNotEmpty()) {
                $last = $oldStops->last();
                if ($last->latitude && $last->longitude) {
                    $lastPt = ['lat' => (float) $last->latitude, 'lng' => (float) $last->longitude];
                }
            }

            $newStopsForOpt = [];
            foreach ($newOrderIds as $txId) {
                $stop = $existingByTx->get($txId) ?? $route->stops->firstWhere('transaction_id', $txId);
                if (! $stop || ! $stop->latitude || ! $stop->longitude) {
                    continue;
                }
                $newStopsForOpt[$txId] = [
                    'lat' => (float) $stop->latitude,
                    'lng' => (float) $stop->longitude,
                ];
            }
            $newOptimized = $newStopsForOpt === []
                ? ['order' => $newOrderIds, 'distances' => [], 'durations' => []]
                : $this->optimizer->optimize($lastPt, $newStopsForOpt);

            $oldIds = $oldStops->pluck('transaction_id')->map(fn ($id) => (int) $id)->all();
            $newSeq = array_values(array_unique(array_map('intval', $newOptimized['order'] ?: $newOrderIds)));
            $sequence = array_values(array_unique([...$oldIds, ...$newSeq]));

            $oldOptimized = ['distances' => [], 'durations' => []];
            if ($oldStops->isNotEmpty()) {
                $oldPts = [];
                foreach ($oldStops as $stop) {
                    if ($stop->latitude && $stop->longitude) {
                        $oldPts[(int) $stop->transaction_id] = [
                            'lat' => (float) $stop->latitude,
                            'lng' => (float) $stop->longitude,
                        ];
                    }
                }
                if ($oldPts !== []) {
                    $oldOptimized = $this->optimizer->optimize($origin, $oldPts);
                }
            }
            $optimized = [
                'distances' => ($oldOptimized['distances'] ?? []) + ($newOptimized['distances'] ?? []),
                'durations' => ($oldOptimized['durations'] ?? []) + ($newOptimized['durations'] ?? []),
            ];

            // Avoid unique collisions while re-numbering
            foreach ($route->stops as $stop) {
                $stop->stop_no = 8000 + (int) $stop->id;
                $stop->save();
            }

            $stopNo = 1;
            $totalDist = 0;
            $totalDur = 0;
            foreach ($sequence as $txId) {
                $stop = $route->stops->firstWhere('transaction_id', $txId);
                if (! $stop) {
                    continue;
                }
                $dist = (int) ($optimized['distances'][$txId] ?? 0);
                $dur = (int) ($optimized['durations'][$txId] ?? 0);
                $totalDist += $dist;
                $totalDur += $dur;
                $stop->stop_no = $stopNo;
                $stop->distance_from_previous = $dist;
                $stop->estimated_duration_from_previous = $dur;
                $stop->save();
                $stopNo++;
            }

            $route->total_orders = $stopNo - 1;
            $route->total_distance = $totalDist;
            $route->estimated_duration = $totalDur;
            if ($newIds !== [] && $route->status === DeliveryRoute::STATUS_COMPLETED) {
                $route->status = DeliveryRoute::STATUS_STARTED;
                $route->completed_at = null;
            }
            $route->save();

            return $route->fresh(['stops.transaction.contact', 'driver']);
        });
    }

    public function assignAndRoute(User $actor, array $transactionIds, int $driverId, string $date, ?int $locationId = null): DeliveryRoute
    {
        return $this->assignOrdersToTodayRoute($actor, $transactionIds, $driverId, $date, $locationId);
    }

    public function startRoute(User $user, DeliveryRoute $route): void
    {
        $this->assertCanUpdate($user, $route);
        if ($route->status === DeliveryRoute::STATUS_CANCELLED) {
            throw ValidationException::withMessages(['route' => 'This route was cancelled.']);
        }
        if ($route->status === DeliveryRoute::STATUS_COMPLETED) {
            return;
        }
        if ($route->status !== DeliveryRoute::STATUS_STARTED) {
            $route->status = DeliveryRoute::STATUS_STARTED;
            $route->started_at = now();
            $route->save();
        }
    }

    public function markArrived(User $user, DeliveryRouteOrder $stop): void
    {
        $stop->loadMissing('route');
        $this->assertCanUpdate($user, $stop->route);
        $this->assertStopCanAct($stop);
        $this->startRoute($user, $stop->route);
        $stop->status = DeliveryRouteOrder::STATUS_ARRIVED;
        $stop->arrived_at ??= now();
        $stop->save();
        $this->syncTxStatus($stop, 'arrived');
    }

    public function markDelivered(User $user, DeliveryRouteOrder $stop, ?string $notes = null): void
    {
        DB::transaction(function () use ($user, $stop, $notes) {
            $stop->loadMissing('route.stops');
            $this->assertCanUpdate($user, $stop->route);
            if ($stop->status === DeliveryRouteOrder::STATUS_DELIVERED) {
                return;
            }
            if (! $stop->canMarkDelivered()) {
                throw ValidationException::withMessages(['stop' => 'This stop cannot be marked delivered.']);
            }
            if ($stop->route->status === DeliveryRoute::STATUS_COMPLETED) {
                $stop->route->status = DeliveryRoute::STATUS_STARTED;
                $stop->route->completed_at = null;
                $stop->route->save();
            }
            $this->startRoute($user, $stop->route);
            $stop->status = DeliveryRouteOrder::STATUS_DELIVERED;
            $stop->fail_reason = null;
            $stop->delivered_at = now();
            if ($notes !== null && trim($notes) !== '') {
                $stop->delivery_notes = $notes;
            }
            $stop->save();
            $this->syncTxStatus($stop, 'delivered');
            $this->advanceRoute($stop->route->fresh());
        });
    }

    public function markFailed(User $user, DeliveryRouteOrder $stop, string $reason, ?string $notes = null): void
    {
        if (! isset(DeliveryRouteOrder::FAIL_REASONS[$reason])) {
            throw ValidationException::withMessages(['fail_reason' => 'Select a failure reason.']);
        }
        DB::transaction(function () use ($user, $stop, $reason, $notes) {
            $stop->loadMissing('route.stops');
            $this->assertCanUpdate($user, $stop->route);
            $this->assertStopCanAct($stop);
            $this->startRoute($user, $stop->route);
            $stop->status = DeliveryRouteOrder::STATUS_FAILED;
            $stop->fail_reason = $reason;
            $stop->delivery_notes = $notes;
            $stop->delivered_at = now();
            $stop->save();
            $this->syncTxStatus($stop, 'failed');
            $this->advanceRoute($stop->route->fresh());
        });
    }

    public function saveNotes(User $user, DeliveryRouteOrder $stop, ?string $notes): void
    {
        $stop->loadMissing('route');
        $this->assertCanUpdate($user, $stop->route);
        $stop->delivery_notes = $notes;
        $stop->save();
    }

    public function driverRouteForDate(User $driver, string $date): ?DeliveryRoute
    {
        return DeliveryRoute::query()
            ->with(['stops.order', 'driver'])
            ->where('business_id', $driver->business_id)
            ->where('delivery_user_id', $driver->id)
            ->whereDate('route_date', $date)
            ->whereIn('status', [DeliveryRoute::STATUS_PLANNED, DeliveryRoute::STATUS_STARTED])
            ->latest('id')
            ->first();
    }

    /**
     * @param  'open'|'all'  $mode
     * @return \Illuminate\Support\Collection<int, DeliveryRouteOrder>
     */
    public function driverStopsInRange(User $driver, string $from, string $to, string $mode = 'all')
    {
        $q = DeliveryRouteOrder::query()
            ->with(['route', 'order.contact'])
            ->whereHas('route', function ($r) use ($driver, $from, $to) {
                $r->where('business_id', $driver->business_id)
                    ->where('delivery_user_id', $driver->id)
                    ->whereDate('route_date', '>=', $from)
                    ->whereDate('route_date', '<=', $to);
            })
            ->orderBy('delivery_route_id')
            ->orderBy('stop_no');

        if ($mode === 'open') {
            $q->whereNotIn('status', [
                DeliveryRouteOrder::STATUS_DELIVERED,
                DeliveryRouteOrder::STATUS_SKIPPED,
            ]);
        }

        return $q->get();
    }

    /**
     * @return \Illuminate\Support\Collection<int, DeliveryRouteOrder>
     */
    public function driverFinishedStops(User $driver, ?string $date = null)
    {
        $q = DeliveryRouteOrder::query()
            ->with(['route', 'order'])
            ->whereIn('status', [DeliveryRouteOrder::STATUS_DELIVERED, DeliveryRouteOrder::STATUS_FAILED])
            ->whereHas('route', function ($r) use ($driver, $date) {
                $r->where('business_id', $driver->business_id)
                    ->where('delivery_user_id', $driver->id);
                if ($date) {
                    $r->whereDate('route_date', $date);
                }
            })
            ->orderByDesc('delivered_at')
            ->limit(100);

        return $q->get();
    }

    public function driverFinishedCount(User $driver): int
    {
        return DeliveryRouteOrder::query()
            ->where('status', DeliveryRouteOrder::STATUS_DELIVERED)
            ->whereHas('route', function ($r) use ($driver) {
                $r->where('business_id', $driver->business_id)
                    ->where('delivery_user_id', $driver->id);
            })
            ->count();
    }

    private function advanceRoute(DeliveryRoute $route): void
    {
        $route->loadMissing('stops');
        $open = $route->stops->filter(fn (DeliveryRouteOrder $s) => ! $s->isFinished());
        if ($open->isEmpty()) {
            $route->status = DeliveryRoute::STATUS_COMPLETED;
            $route->completed_at = now();
            $route->save();
        }
    }

    private function syncTxStatus(DeliveryRouteOrder $stop, string $status): void
    {
        $tx = Transaction::find($stop->transaction_id);
        if (! $tx) {
            return;
        }
        $tx->delivery_status = $status;
        if ($status === 'delivered') {
            app(\App\Services\CreditControlService::class)
                ->assertCanAdvanceShippingStatus($tx, 'delivered');
            $tx->shipping_status = 'delivered';
        }
        $tx->save();
    }

    private function assertStopCanAct(DeliveryRouteOrder $stop): void
    {
        if (! $stop->canAct()) {
            throw ValidationException::withMessages(['stop' => 'This stop is already finished.']);
        }
    }

    private function assertManage(User $actor): void
    {
        if (! $actor->can('delivery.manage') && ! $this->isAdmin($actor)) {
            abort(403, 'Unauthorized action.');
        }
    }

    private function assertCanUpdate(User $user, DeliveryRoute $route): void
    {
        if ((int) $route->business_id !== (int) $user->business_id) {
            abort(403);
        }
        if ($this->isAdmin($user) || $user->can('delivery.manage')) {
            return;
        }
        if ((int) $route->delivery_user_id === (int) $user->id && ($user->can('delivery.driver.edit') || $user->can('delivery.driver.view'))) {
            return;
        }
        abort(403);
    }

    private function isAdmin(User $user): bool
    {
        return $user->hasRole('Admin#'.$user->business_id) || $user->can('superadmin');
    }

    private function deliveryDriver(int $businessId, int $driverId): User
    {
        $driver = User::where('business_id', $businessId)
            ->whereHas('roles', function ($r) use ($businessId) {
                $r->where('name', 'Delivery#'.$businessId);
            })
            ->find($driverId);

        if (! $driver) {
            throw ValidationException::withMessages(['driver' => 'Selected user is not a delivery driver.']);
        }

        return $driver;
    }

    /**
     * @return array{lat:float,lng:float,name:?string,address:?string}
     */
    private function originFromBusiness(int $businessId, ?int $locationId): array
    {
        $q = BusinessLocation::where('business_id', $businessId);
        $loc = null;
        if ($locationId) {
            $loc = (clone $q)->find($locationId);
        }
        if (! $loc) {
            $loc = (clone $q)->where('is_active', 1)->first() ?: $q->first();
        }

        $addrParts = $loc ? array_filter([
            $loc->landmark ?? null,
            $loc->city ?? null,
            $loc->state ?? null,
            $loc->zip_code ?? null,
            $loc->country ?: 'USA',
        ]) : [];
        $addr = $addrParts ? implode(', ', $addrParts) : null;

        $lat = ($loc && is_numeric($loc->latitude) && (float) $loc->latitude != 0.0) ? (float) $loc->latitude : null;
        $lng = ($loc && is_numeric($loc->longitude) && (float) $loc->longitude != 0.0) ? (float) $loc->longitude : null;

        if (($lat === null || $lng === null) && $addr) {
            // Prefer "City, ST ZIP, USA" for stable geocode
            $geoQuery = trim(implode(', ', array_filter([
                $loc->city ?? null,
                trim(($loc->state ?? '').' '.($loc->zip_code ?? '')),
                $loc->country ?: 'USA',
            ])));
            $geo = $this->optimizer->geocode($geoQuery !== '' ? $geoQuery : $addr)
                ?? $this->optimizer->geocode($addr);
            if ($geo) {
                $lat = $geo['lat'];
                $lng = $geo['lng'];
                if ($loc) {
                    $loc->latitude = $lat;
                    $loc->longitude = $lng;
                    $loc->save();
                }
            }
        }

        if ($lat === null || $lng === null) {
            $lat = 40.7127281;
            $lng = -74.0060152; // NYC fallback (not Detroit)
        }

        return [
            'lat' => $lat,
            'lng' => $lng,
            'name' => $loc->name ?? 'POS / Warehouse',
            'address' => $addr,
        ];
    }

    private function ensureOrderCoordinates(Transaction $order): void
    {
        if ($order->shipping_latitude && $order->shipping_longitude) {
            return;
        }

        $order->loadMissing('contact');
        $contact = $order->contact;
        $query = trim(collect([
            is_string($order->shipping_address) && trim($order->shipping_address) !== '' ? $order->shipping_address : null,
            $contact->shipping_address ?? null,
            $contact->address_line_1 ?? null,
            $contact->address_line_2 ?? null,
            $contact->city ?? null,
            $contact->state ?? null,
            $contact->zip_code ?? null,
            $contact->land_mark ?? null,
        ])->filter(fn ($v) => is_string($v) && trim($v) !== '')->implode(', '));

        if ($query === '') {
            return;
        }

        $geo = $this->optimizer->geocode($query);
        if (! $geo) {
            return;
        }

        $order->shipping_latitude = $geo['lat'];
        $order->shipping_longitude = $geo['lng'];
        $order->save();
    }

    /**
     * Place a stop near POS when the invoice has no shipping address (walk-in etc).
     *
     * @return array{lat:float,lng:float}
     */
    private function offsetFromOrigin(array $origin, int $index): array
    {
        $bearing = deg2rad(30 + ($index * 40));
        $meters = 500 + ($index * 450);
        $dLat = ($meters * cos($bearing)) / 111320;
        $cosLat = cos(deg2rad($origin['lat']));
        $dLng = $cosLat != 0.0 ? ($meters * sin($bearing)) / (111320 * $cosLat) : 0.0;

        return [
            'lat' => $origin['lat'] + $dLat,
            'lng' => $origin['lng'] + $dLng,
        ];
    }

    /**
     * Backfill missing start/stop coordinates on an existing route so the map line appears.
     */
    public function repairRouteMap(DeliveryRoute $route): DeliveryRoute
    {
        $origin = [
            'lat' => (float) ($route->start_latitude ?: 0),
            'lng' => (float) ($route->start_longitude ?: 0),
            'name' => $route->start_name,
            'address' => $route->start_address,
        ];

        // Re-resolve POS if start still looks like old Detroit fallback or empty
        $needsOrigin = ! $route->start_latitude || ! $route->start_longitude
            || (abs((float) $route->start_latitude - 42.3314) < 0.001 && abs((float) $route->start_longitude - (-83.0458)) < 0.001);

        if ($needsOrigin) {
            $fixed = $this->originFromBusiness((int) $route->business_id, $route->business_location_id);
            $route->start_latitude = $fixed['lat'];
            $route->start_longitude = $fixed['lng'];
            if (! $route->start_address && $fixed['address']) {
                $route->start_address = $fixed['address'];
            }
            $route->save();
            $origin = $fixed;
        }

        $route->load(['stops.transaction.contact']);
        $changed = false;
        $nearIndex = 0;
        $stopsForOpt = [];

        foreach ($route->stops->sortBy('stop_no') as $stop) {
            $tx = $stop->transaction;
            if ($tx) {
                $this->ensureOrderCoordinates($tx);
            }

            $lat = $stop->latitude ?: $tx?->shipping_latitude;
            $lng = $stop->longitude ?: $tx?->shipping_longitude;

            if ((! $lat || ! $lng) && $stop->ship_to_address) {
                $query = trim(collect([
                    $stop->ship_to_address,
                    $stop->ship_to_city,
                    $stop->ship_to_state,
                    $stop->ship_to_zip,
                ])->filter()->implode(', '));
                $geo = $query !== '' ? $this->optimizer->geocode($query) : null;
                if ($geo) {
                    $lat = $geo['lat'];
                    $lng = $geo['lng'];
                }
            }

            if (! $lat || ! $lng) {
                $near = $this->offsetFromOrigin($origin, $nearIndex++);
                $lat = $near['lat'];
                $lng = $near['lng'];
            }

            if ((float) $stop->latitude !== (float) $lat || (float) $stop->longitude !== (float) $lng) {
                $stop->latitude = $lat;
                $stop->longitude = $lng;
                $stop->save();
                $changed = true;
            }

            $stopsForOpt[(int) $stop->id] = ['lat' => (float) $lat, 'lng' => (float) $lng];
        }

        if ($stopsForOpt !== []) {
            // Re-number stop_no nearest from POS and redraw distances
            $byTx = [];
            foreach ($route->stops as $stop) {
                $byTx[(int) $stop->id] = $stop;
            }
            $opt = $this->optimizer->optimize($origin, $stopsForOpt);
            $stopNo = 1;
            $totalDist = 0;
            $totalDur = 0;
            foreach ($opt['order'] as $stopId) {
                $stop = $byTx[$stopId] ?? null;
                if (! $stop) {
                    continue;
                }
                $stop->stop_no = $stopNo++;
                $stop->distance_from_previous = (int) ($opt['distances'][$stopId] ?? 0);
                $stop->estimated_duration_from_previous = (int) ($opt['durations'][$stopId] ?? 0);
                $totalDist += $stop->distance_from_previous;
                $totalDur += $stop->estimated_duration_from_previous;
                $stop->save();
                $changed = true;
            }
            $route->total_distance = $totalDist;
            $route->estimated_duration = $totalDur;
            $route->total_orders = $stopNo - 1;
            $route->save();
        }

        return $route->fresh(['driver', 'stops.order.contact', 'stops.transaction.contact']);
    }

    public static function assignableDrivers(int $businessId)
    {
        return User::where('business_id', $businessId)
            ->where('status', 'active')
            ->where('allow_login', 1)
            ->whereHas('roles', function ($r) use ($businessId) {
                $r->where('name', 'Delivery#'.$businessId);
            })
            ->orderBy('first_name')
            ->get();
    }
}
