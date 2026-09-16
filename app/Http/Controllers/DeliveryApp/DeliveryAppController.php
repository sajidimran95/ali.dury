<?php

namespace App\Http\Controllers\DeliveryApp;

use App\DeliveryRoute;
use App\DeliveryRouteOrder;
use App\Http\Controllers\Controller;
use App\Services\Delivery\DeliveryRouteService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DeliveryAppController extends Controller
{
    public function __construct(private DeliveryRouteService $routes) {}

    public function home(Request $request)
    {
        $user = $request->user('delivery');
        $date = now()->toDateString();
        $route = $this->todayRoute($request);
        $deliveredToday = $route ? $route->stops->where('status', 'delivered')->count() : 0;
        $failedToday = $route ? $route->stops->where('status', 'failed')->count() : 0;
        $left = $route ? $route->remainingCount() : 0;
        $suggested = $route?->currentStop();
        $allTimeDelivered = $this->routes->driverFinishedCount($user);
        $assignedCount = $route?->total_orders ?? 0;

        return view('delivery_app.home', compact(
            'user', 'route', 'date', 'deliveredToday', 'failedToday', 'left', 'suggested', 'allTimeDelivered', 'assignedCount'
        ));
    }

    public function route(Request $request)
    {
        $date = now()->toDateString();
        $route = $this->routeFromStopQuery($request) ?? $this->todayRoute($request);
        $active = $this->activeStop($route, (int) $request->query('stop', 0));
        $suggested = $route?->currentStop();
        $delivered = $route ? $route->stops->where('status', 'delivered')->count() : 0;
        $mapStops = $route ? $route->stops->sortBy('stop_no')->values()->map(fn ($s) => [
            'id' => $s->id,
            'n' => (int) $s->stop_no,
            'lat' => $s->latitude ? (float) $s->latitude : null,
            'lng' => $s->longitude ? (float) $s->longitude : null,
            'label' => '#'.($s->order?->invoice_no ?? $s->transaction_id),
            'name' => $s->ship_to_name,
            'status' => $s->status,
        ])->all() : [];

        return view('delivery_app.route', compact('route', 'active', 'suggested', 'delivered', 'mapStops', 'date'));
    }

    public function assigned(Request $request)
    {
        $user = $request->user('delivery');
        [$from, $to] = $this->dateRange($request, now()->toDateString(), now()->toDateString());
        $stops = $this->routes->driverStopsInRange($user, $from, $to, 'open');

        return view('delivery_app.assigned', compact('stops', 'from', 'to'));
    }

    public function all(Request $request)
    {
        $user = $request->user('delivery');
        [$from, $to] = $this->dateRange($request, now()->subDays(7)->toDateString(), now()->toDateString());
        $stops = $this->routes->driverStopsInRange($user, $from, $to, 'all');
        $openCount = $stops->filter(fn ($s) => $s->canAct())->count();
        $deliveredCount = $stops->where('status', 'delivered')->count();
        $days = $stops
            ->groupBy(fn ($s) => optional($s->route?->route_date)->toDateString() ?: 'unknown')
            ->map(fn ($group) => $group->sortBy('stop_no')->values());

        return view('delivery_app.all', compact('stops', 'days', 'from', 'to', 'openCount', 'deliveredCount'));
    }

    public function history(Request $request)
    {
        $user = $request->user('delivery');
        $all = $request->boolean('all');
        $date = $all ? null : (string) $request->query('date', now()->toDateString());
        if ($date !== null && $date !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = now()->toDateString();
        }
        $stops = $this->routes->driverFinishedStops($user, $all ? null : $date);
        $deliveredCount = $stops->where('status', 'delivered')->count();
        $failedCount = $stops->where('status', 'failed')->count();

        return view('delivery_app.history', compact('stops', 'date', 'all', 'deliveredCount', 'failedCount'));
    }

    public function start(Request $request)
    {
        $route = $this->todayRoute($request);
        abort_unless($route, 404);
        $this->routes->startRoute($request->user('delivery'), $route);

        return $this->backToStop($request);
    }

    public function arrived(Request $request, $stop)
    {
        $row = $this->ownedStop($request, (int) $stop);
        $this->routes->markArrived($request->user('delivery'), $row);

        return $this->backToStop($request, $row->id);
    }

    public function delivered(Request $request, $stop)
    {
        $row = $this->ownedStop($request, (int) $stop);
        $notes = trim((string) $request->input('notes', ''));
        $this->routes->markDelivered($request->user('delivery'), $row, $notes !== '' ? $notes : null);

        return $this->backToStop($request, $row->id);
    }

    public function failed(Request $request, $stop)
    {
        $row = $this->ownedStop($request, (int) $stop);
        try {
            $this->routes->markFailed(
                $request->user('delivery'),
                $row,
                (string) $request->input('fail_reason', ''),
                $request->input('notes')
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return $this->backToStop($request, $row->id);
    }

    public function notes(Request $request, $stop)
    {
        $row = $this->ownedStop($request, (int) $stop);
        $this->routes->saveNotes($request->user('delivery'), $row, $request->input('notes'));

        return $this->backToStop($request, $row->id)->with('status', ['success' => 1, 'msg' => 'Note saved.']);
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

    protected function backToStop(Request $request, ?int $stopId = null)
    {
        $id = $stopId ?: (int) $request->input('stop', $request->query('stop', 0));

        return redirect()->route('delivery.app.route', array_filter(['stop' => $id ?: null]));
    }

    protected function ownedStop(Request $request, int $id): DeliveryRouteOrder
    {
        $user = $request->user('delivery');

        return DeliveryRouteOrder::whereHas('route', function ($q) use ($user) {
            $q->where('business_id', $user->business_id)->where('delivery_user_id', $user->id);
        })->findOrFail($id);
    }
}
