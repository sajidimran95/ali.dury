<?php

namespace Modules\Ecommerce\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Transaction;
use Illuminate\Http\Request;
use Modules\Ecommerce\Entities\EcomOrderMeta;

class OrderController extends Controller
{
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');
        $orders = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->where('source', 'ecommerce')
            ->with(['contact'])
            ->orderByDesc('id')
            ->paginate(25);

        $metas = EcomOrderMeta::whereIn('transaction_id', $orders->pluck('id'))->get()->keyBy('transaction_id');

        return view('ecommerce::admin.orders.index', compact('orders', 'metas'));
    }

    public function show($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $order = Transaction::where('business_id', $business_id)
            ->where('source', 'ecommerce')
            ->with(['contact', 'sell_lines', 'sell_lines.product', 'sell_lines.variations'])
            ->findOrFail($id);
        $meta = EcomOrderMeta::where('transaction_id', $order->id)->first();

        return view('ecommerce::admin.orders.show', compact('order', 'meta'));
    }

    public function updateStatus(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');
        $order = Transaction::where('business_id', $business_id)->where('source', 'ecommerce')->findOrFail($id);
        $meta = EcomOrderMeta::firstOrCreate(
            ['transaction_id' => $order->id],
            [
                'business_id' => $business_id,
                'tracking_token' => \Illuminate\Support\Str::random(40),
                'fulfillment_status' => 'pending',
                'timeline' => [],
            ]
        );

        $status = $request->input('fulfillment_status');
        $allowed = ['pending', 'confirmed', 'processing', 'packed', 'shipped', 'delivered', 'cancelled', 'returned'];
        if (! in_array($status, $allowed, true)) {
            abort(422);
        }

        $timeline = $meta->timeline ?? [];
        $timeline[] = [
            'status' => $status,
            'label' => ucfirst($status),
            'at' => now()->toDateTimeString(),
            'note' => $request->input('note'),
        ];
        $meta->fulfillment_status = $status;
        $meta->timeline = $timeline;
        $meta->save();

        $shipping_map = [
            'packed' => 'packed',
            'shipped' => 'shipped',
            'delivered' => 'delivered',
            'cancelled' => 'cancelled',
        ];
        if (isset($shipping_map[$status])) {
            $order->shipping_status = $shipping_map[$status];
            $order->save();
        }

        return redirect()->back()->with('status', ['success' => 1, 'msg' => __('ecommerce::lang.status_updated')]);
    }
}
