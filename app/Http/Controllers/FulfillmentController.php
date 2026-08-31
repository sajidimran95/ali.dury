<?php

namespace App\Http\Controllers;

use App\Services\FulfillmentService;
use App\Utils\BusinessUtil;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class FulfillmentController extends Controller
{
    public function __construct(
        protected FulfillmentService $fulfillment,
        protected BusinessUtil $businessUtil
    ) {}

    protected function authorizeFulfillment(): void
    {
        $is_admin = $this->businessUtil->is_admin(auth()->user());
        if (! $is_admin && ! auth()->user()->hasAnyPermission([
            'access_shipping', 'access_own_shipping', 'access_commission_agent_shipping',
        ])) {
            abort(403, 'Unauthorized action.');
        }
    }

    public function picking(Request $request)
    {
        return $this->queue($request, 'picking');
    }

    public function packing(Request $request)
    {
        return $this->queue($request, 'packing');
    }

    public function shipment(Request $request)
    {
        return $this->queue($request, 'shipment');
    }

    protected function queue(Request $request, string $stage)
    {
        $this->authorizeFulfillment();
        $business_id = (int) $request->session()->get('user.business_id');
        $permitted = auth()->user()->permitted_locations();
        $source = $request->get('source');

        if ($request->ajax()) {
            $query = $this->fulfillment->forStage(
                $this->fulfillment->pipelineQuery($business_id, $permitted),
                $stage
            );
            $this->fulfillment->applySourceFilter($query, $source);
            $query->leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->select('transactions.*', 'contacts.name as customer_name')
                ->orderByDesc('transactions.transaction_date');

            return Datatables::of($query)
                ->addColumn('source_badge', function ($row) {
                    $b = $row->fulfillmentSourceBadge();

                    return '<span class="ff-source-badge '.$b['class'].'">'.e($b['short']).'</span>';
                })
                ->addColumn('customer', function ($row) {
                    return e($row->customer_name ?? '');
                })
                ->editColumn('invoice_no', function ($row) {
                    $url = action([\App\Http\Controllers\FulfillmentController::class, 'show'], [$row->id]);

                    return '<a href="'.e($url).'">'.e($row->invoice_no).'</a>';
                })
                ->editColumn('final_total', function ($row) {
                    return '<span class="display_currency" data-currency_symbol="true">'.$row->final_total.'</span>';
                })
                ->editColumn('transaction_date', function ($row) {
                    return \Carbon::parse($row->transaction_date)->format('M j, Y g:i A');
                })
                ->addColumn('action', function ($row) {
                    $url = action([\App\Http\Controllers\FulfillmentController::class, 'show'], [$row->id]);

                    return '<a class="btn btn-sm btn-primary" href="'.e($url).'">'.e(__('fulfillment.open')).'</a>';
                })
                ->rawColumns(['source_badge', 'invoice_no', 'final_total', 'action'])
                ->make(true);
        }

        return view('fulfillment.queue', compact('stage', 'source'));
    }

    public function show($id)
    {
        $this->authorizeFulfillment();
        $order = $this->findOrder($id);
        $this->fulfillment->ensurePipelineFields($order);
        $order->refresh()->load([
            'contact',
            'payment_lines',
            'sell_lines' => function ($q) {
                $q->whereNull('parent_sell_line_id');
            },
            'sell_lines.product',
            'sell_lines.variations',
            'sell_lines.variations.product_variation',
        ]);

        $stage = $order->fulfillment_stage ?: 'picking';
        $badge = $order->fulfillmentSourceBadge();
        $meta = $order->fulfillment_meta ?? [];
        $pick = $this->fulfillment->pickedCount($order);
        $checklist = $meta['pack_checklist'] ?? [];
        $package = $meta['package'] ?? [];
        $ship = $meta['shipment'] ?? [];
        $timeline = array_reverse($meta['timeline'] ?? []);

        return view('fulfillment.show', compact(
            'order', 'stage', 'badge', 'meta', 'pick', 'checklist', 'package', 'ship', 'timeline'
        ));
    }

    public function startPicking($id)
    {
        $this->authorizeFulfillment();
        $order = $this->findOrder($id);
        $this->fulfillment->ensurePipelineFields($order);
        $this->fulfillment->appendTimeline($order, 'start_picking', 'Start picking');
        $order->save();

        return redirect()->action([self::class, 'show'], [$id]);
    }

    public function pick(Request $request, $id)
    {
        $this->authorizeFulfillment();
        $order = $this->findOrder($id);
        $order->load('sell_lines');
        try {
            $this->fulfillment->savePicks(
                $order,
                $request->input('qty_picked', []),
                (bool) $request->input('advance')
            );
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('status', ['success' => 0, 'msg' => $e->getMessage()]);
        }

        return redirect()->action([self::class, 'show'], [$id])->with('status', [
            'success' => 1,
            'msg' => __('lang_v1.updated_success'),
        ]);
    }

    public function pack(Request $request, $id)
    {
        $this->authorizeFulfillment();
        $order = $this->findOrder($id);
        $checklist = [
            'items_verified' => (bool) $request->input('items_verified'),
            'packaging_selected' => (bool) $request->input('packaging_selected'),
            'packing_slip' => (bool) $request->input('packing_slip'),
            'package_sealed' => (bool) $request->input('package_sealed'),
        ];
        $package = $request->only(['package_type', 'length', 'width', 'height', 'weight']);
        try {
            $this->fulfillment->savePacking($order, $checklist, $package, (bool) $request->input('advance'));
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('status', ['success' => 0, 'msg' => $e->getMessage()]);
        }

        return redirect()->action([self::class, 'show'], [$id])->with('status', [
            'success' => 1,
            'msg' => __('lang_v1.updated_success'),
        ]);
    }

    public function ship(Request $request, $id)
    {
        $this->authorizeFulfillment();
        $order = $this->findOrder($id);
        $ship = [
            'carrier' => $request->input('carrier'),
            'service' => $request->input('service'),
            'tracking' => $request->input('tracking'),
            'label_attached' => (bool) $request->input('label_attached'),
            'address_verified' => (bool) $request->input('address_verified'),
            'package_sealed' => (bool) $request->input('package_sealed'),
        ];
        $this->fulfillment->saveShipment($order, $ship, (bool) $request->input('confirm'));

        return redirect()->action([self::class, 'show'], [$id])->with('status', [
            'success' => 1,
            'msg' => __('lang_v1.updated_success'),
        ]);
    }

    protected function findOrder($id)
    {
        $business_id = (int) request()->session()->get('user.business_id');
        $permitted = auth()->user()->permitted_locations();

        return $this->fulfillment->pipelineQuery($business_id, $permitted)->findOrFail($id);
    }
}
