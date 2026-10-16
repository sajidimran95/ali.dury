<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\DeliveryRoute;
use App\Services\Delivery\DeliveryRouteService;
use App\Transaction;
use App\Utils\BusinessUtil;
use Illuminate\Http\Request;

class DeliveryManagementController extends Controller
{
    public function __construct(
        private DeliveryRouteService $routes,
        private BusinessUtil $businessUtil
    ) {}

    private function authorizeManage(): void
    {
        $user = auth()->user();
        $isAdmin = $this->businessUtil->is_admin($user) || $user->can('superadmin');
        if (! $isAdmin && ! $user->can('delivery.manage')) {
            abort(403, 'Unauthorized action.');
        }
    }

    public function assign(Request $request)
    {
        $this->authorizeManage();
        $businessId = (int) session('user.business_id');
        $date = $request->get('date', now()->toDateString());
        $filter = $request->get('filter', 'unassigned'); // unassigned|assigned|all

        $q = Transaction::with(['contact', 'delivery_person_user'])
            ->where('business_id', $businessId)
            ->where('type', 'sell')
            ->where('status', 'final')
            ->whereDate('transaction_date', '>=', now()->subDays(60)->toDateString())
            ->orderByDesc('id');

        if ($filter === 'unassigned') {
            $q->where(function ($w) {
                $w->whereNull('delivery_person')->orWhere('delivery_person', 0);
            })->where(function ($w) {
                $w->whereNull('delivery_status')->orWhereNotIn('delivery_status', ['delivered', 'failed']);
            });
        } elseif ($filter === 'assigned') {
            $q->whereNotNull('delivery_person')->where('delivery_person', '>', 0);
        }

        if ($request->filled('q')) {
            $term = $request->get('q');
            $q->where(function ($w) use ($term) {
                $w->where('invoice_no', 'like', "%{$term}%")
                    ->orWhereHas('contact', function ($c) use ($term) {
                        $c->where('name', 'like', "%{$term}%")
                            ->orWhere('supplier_business_name', 'like', "%{$term}%")
                            ->orWhere('mobile', 'like', "%{$term}%");
                    });
            });
        }

        $orders = $q->paginate(40)->appends($request->query());
        $drivers = DeliveryRouteService::assignableDrivers($businessId);
        $locations = BusinessLocation::where('business_id', $businessId)->get();

        return view('delivery_management.assign', compact('orders', 'drivers', 'locations', 'date', 'filter'));
    }

    public function assignStore(Request $request)
    {
        $this->authorizeManage();
        $data = $request->validate([
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'integer',
            'driver_id' => 'required|integer',
            'date' => 'required|date',
            'location_id' => 'nullable|integer',
            'generate' => 'nullable|boolean',
        ]);

        try {
            $route = $this->routes->assignOrdersToTodayRoute(
                auth()->user(),
                $data['order_ids'],
                (int) $data['driver_id'],
                $data['date'],
                $data['location_id'] ?? null
            );

            $msg = $request->boolean('generate')
                ? 'Assigned and added to today\'s route ('.$route->total_orders.' stops).'
                : 'Assigned '.$route->stops->count().' invoice(s) to today\'s route.';

            return redirect()
                ->route('deliveries.routes.show', ['id' => $route->id, 'listFilter' => 'all'])
                ->with('status', ['success' => 1, 'msg' => $msg]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            return back()->with('status', ['success' => 0, 'msg' => $e->getMessage()]);
        }
    }

    public function routes(Request $request)
    {
        $this->authorizeManage();
        $businessId = (int) session('user.business_id');
        $listFilter = $request->get('listFilter', 'today');
        if (! in_array($listFilter, ['today', 'pending', 'range'], true)) {
            $listFilter = 'today';
        }

        $date = $request->get('date', now()->toDateString());
        $date_from = $request->get('date_from', $date);
        $date_to = $request->get('date_to', $date_from);

        $q = DeliveryRoute::with(['driver', 'stops'])
            ->where('business_id', $businessId);

        if ($listFilter === 'pending') {
            $q->whereIn('status', [DeliveryRoute::STATUS_PLANNED, DeliveryRoute::STATUS_STARTED]);
        } elseif ($listFilter === 'range') {
            $q->whereDate('route_date', '>=', $date_from)
                ->whereDate('route_date', '<=', $date_to);
        } else {
            $q->whereDate('route_date', $date);
        }

        $routes = $q->orderByDesc('route_date')->orderByDesc('id')->get();

        $orders = $routes->sum(fn ($r) => (int) $r->total_orders);
        $delivered = $routes->sum(fn ($r) => $r->stops->where('status', 'delivered')->count());
        $failed = $routes->sum(fn ($r) => $r->stops->where('status', 'failed')->count());
        $remaining = $routes->sum(fn ($r) => $r->remainingCount());
        $miles = round($routes->sum('total_distance') / 1609.34, 1);
        $totals = compact('orders', 'delivered', 'failed', 'remaining', 'miles');

        $drivers = DeliveryRouteService::assignableDrivers($businessId);

        return view('delivery_management.routes', compact(
            'routes', 'drivers', 'date', 'date_from', 'date_to', 'listFilter', 'totals'
        ));
    }

    public function generate(Request $request)
    {
        $this->authorizeManage();
        $data = $request->validate([
            'driver_id' => 'required|integer',
            'date' => 'required|date',
            'location_id' => 'nullable|integer',
        ]);

        try {
            $route = $this->routes->generateRoute(
                auth()->user(),
                (int) $data['driver_id'],
                $data['date'],
                $data['location_id'] ?? null
            );

            return redirect()
                ->route('deliveries.routes.show', ['id' => $route->id, 'listFilter' => 'all'])
                ->with('status', ['success' => 1, 'msg' => 'Route generated with '.$route->total_orders.' stops.']);
        } catch (\Throwable $e) {
            return back()->with('status', ['success' => 0, 'msg' => $e->getMessage()]);
        }
    }

    public function show(Request $request, $id)
    {
        $this->authorizeManage();
        $businessId = (int) session('user.business_id');
        $listFilter = $request->get('listFilter', 'all');
        if (! in_array($listFilter, ['today', 'pending', 'all'], true)) {
            $listFilter = 'all';
        }

        $route = DeliveryRoute::with(['driver', 'stops.order.contact', 'stops.transaction.contact'])
            ->where('business_id', $businessId)
            ->findOrFail($id);

        // If map pins/line missing (no lat/lng), repair from POS + stop addresses
        $needsMap = ! $route->start_latitude || ! $route->start_longitude
            || $route->stops->contains(fn ($s) => ! $s->latitude || ! $s->longitude);
        if ($needsMap) {
            $route = $this->routes->repairRouteMap($route);
        }

        $allStops = $route->stops->sortBy('stop_no')->values();
        $stops = $allStops->filter(function ($stop) use ($listFilter) {
            if ($listFilter === 'all') {
                return true;
            }

            return ! $stop->isFinished();
        })->values();

        $delivered = $allStops->where('status', 'delivered')->count();
        $remaining = $route->remainingCount();
        $miles = round($route->total_distance / 1609.34, 1);
        $minutes = (int) round($route->estimated_duration / 60);

        // Map always uses full stop sequence 1 → 2 → 3… (line order by stop_no)
        $mapStops = $allStops->map(fn ($s) => [
            'n' => (int) $s->stop_no,
            'lat' => $s->latitude ? (float) $s->latitude : null,
            'lng' => $s->longitude ? (float) $s->longitude : null,
            'label' => '#'.($s->order?->invoice_no ?? $s->transaction?->invoice_no ?? $s->transaction_id),
            'name' => $s->ship_to_name,
            'status' => $s->status,
        ])->values()->all();

        $driverLocation = $this->driverLocationPayload($route);

        return view('delivery_management.show', compact(
            'route', 'stops', 'listFilter', 'delivered', 'remaining', 'miles', 'minutes', 'mapStops', 'driverLocation'
        ));
    }

    /** Live driver position for the route map (polled by the route detail page). */
    public function driverLocation($id)
    {
        $this->authorizeManage();
        $route = DeliveryRoute::query()
            ->where('business_id', (int) session('user.business_id'))
            ->findOrFail($id);

        return response()->json($this->driverLocationPayload($route));
    }

    private function driverLocationPayload(DeliveryRoute $route): array
    {
        $at = $route->driver_location_at;

        return [
            'lat' => $route->driver_latitude,
            'lng' => $route->driver_longitude,
            'accuracy' => $route->driver_accuracy,
            'at' => $at?->toIso8601String(),
            'ago' => $at?->diffForHumans(),
            'stale' => ! $at || $at->lt(now()->subMinutes(5)),
            'status' => $route->status,
            'status_label' => $route->statusLabel(),
        ];
    }
}
