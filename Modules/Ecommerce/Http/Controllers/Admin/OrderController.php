<?php

namespace Modules\Ecommerce\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Transaction;
use App\Utils\TransactionUtil;
use App\Utils\Util;
use Illuminate\Http\Request;
use Modules\Ecommerce\Entities\EcomOrderMeta;
use Yajra\DataTables\Facades\DataTables;

class OrderController extends Controller
{
    protected $transactionUtil;

    protected $commonUtil;

    public function __construct(TransactionUtil $transactionUtil, Util $commonUtil)
    {
        $this->transactionUtil = $transactionUtil;
        $this->commonUtil = $commonUtil;
    }

    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        if (request()->ajax()) {
            $orders = Transaction::where('transactions.business_id', $business_id)
                ->ecommerceOrders()
                ->leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->leftJoin('ecom_order_meta', 'transactions.id', '=', 'ecom_order_meta.transaction_id')
                ->select(
                    'transactions.id',
                    'transactions.invoice_no',
                    'contacts.name as customer_name',
                    'transactions.final_total',
                    'transactions.payment_status',
                    'ecom_order_meta.fulfillment_status',
                    'transactions.transaction_date'
                );

            return DataTables::of($orders)
                ->editColumn('invoice_no', function ($row) {
                    $url = route('ecommerce.admin.orders.show', $row->id);

                    return '<a href="'.$url.'">'.e($row->invoice_no).'</a>';
                })
                ->editColumn('customer_name', function ($row) {
                    return $row->customer_name ?: '-';
                })
                ->editColumn('final_total', function ($row) {
                    return '<span class="display_currency" data-currency_symbol="true" data-orig-value="'.$row->final_total.'">'.$row->final_total.'</span>';
                })
                ->editColumn('payment_status', function ($row) {
                    return ucfirst($row->payment_status ?? '-');
                })
                ->editColumn('fulfillment_status', function ($row) {
                    return $row->fulfillment_status ? ucfirst($row->fulfillment_status) : '-';
                })
                ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
                ->rawColumns(['invoice_no', 'final_total'])
                ->make(true);
        }

        $business_id = (int) request()->session()->get('user.business_id');
        $order_stats = $this->getEcommerceOrderListStats($business_id);

        return view('ecommerce::admin.orders.index', compact('order_stats'));
    }

    /**
     * KPI stats for ecommerce orders list cards.
     */
    protected function getEcommerceOrderListStats(int $businessId): array
    {
        $base = Transaction::where('business_id', $businessId)
            ->ecommerceOrders();

        $total = (int) (clone $base)->count();
        $today = (int) (clone $base)->whereDate('transaction_date', \Carbon\Carbon::today())->count();
        $thisMonth = (int) (clone $base)
            ->whereBetween('transaction_date', [
                \Carbon\Carbon::now()->startOfMonth()->toDateTimeString(),
                \Carbon\Carbon::now()->endOfMonth()->toDateTimeString(),
            ])
            ->count();

        $orderIds = (clone $base)->pluck('id');
        $metaCounts = $orderIds->isEmpty()
            ? collect()
            : EcomOrderMeta::whereIn('transaction_id', $orderIds)
                ->selectRaw('fulfillment_status, COUNT(*) as c')
                ->groupBy('fulfillment_status')
                ->pluck('c', 'fulfillment_status');

        $pending = (int) ($metaCounts['pending'] ?? 0) + (int) ($metaCounts['confirmed'] ?? 0);
        $processing = (int) ($metaCounts['processing'] ?? 0) + (int) ($metaCounts['packed'] ?? 0);
        $shipped = (int) ($metaCounts['shipped'] ?? 0);
        $completed = (int) ($metaCounts['delivered'] ?? 0);

        $salesTotal = (float) (clone $base)->sum('final_total');

        return [
            'total' => $total,
            'today' => $today,
            'this_month' => $thisMonth,
            'pending' => $pending,
            'processing' => $processing,
            'shipped' => $shipped,
            'completed' => $completed,
            'sales_total' => $salesTotal,
        ];
    }

    public function show($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $order = Transaction::where('business_id', $business_id)
            ->where('source', 'ecommerce')
            ->with([
                'contact',
                'payment_lines',
                'sell_lines',
                'sell_lines.product',
                'sell_lines.product.unit',
                'sell_lines.variations',
                'sell_lines.variations.product_variation',
            ])
            ->findOrFail($id);
        $meta = EcomOrderMeta::where('transaction_id', $order->id)->first();
        $payment_types = $this->commonUtil->payment_types(null, true, $business_id);
        $shipping_statuses = $this->commonUtil->shipping_statuses();

        return view('ecommerce::admin.orders.show', compact('order', 'meta', 'payment_types', 'shipping_statuses'));
    }

    public function downloadPdf($id)
    {
        if (! auth()->user()->can('print_invoice')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $order = Transaction::where('business_id', $business_id)
            ->where('source', 'ecommerce')
            ->findOrFail($id);

        $receipt_contents = $this->transactionUtil->getPdfContentsForGivenTransaction($business_id, $order->id);
        $receipt_details = $receipt_contents['receipt_details'];
        $location_details = $receipt_contents['location_details'];
        $is_email_attachment = false;

        $blade_file = ! empty($receipt_details->is_export) ? 'download_export_pdf' : 'download_pdf';

        $body = view('sale_pos.receipts.'.$blade_file)
            ->with(compact('receipt_details', 'location_details', 'is_email_attachment'))
            ->render();

        $mpdf = new \Mpdf\Mpdf([
            'tempDir' => public_path('uploads/temp'),
            'mode' => 'utf-8',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'autoVietnamese' => true,
            'autoArabic' => true,
            'margin_top' => 8,
            'margin_bottom' => 8,
            'format' => 'A4',
        ]);

        $mpdf->useSubstitutions = true;
        $mpdf->SetWatermarkText($receipt_details->business_name, 0.1);
        $mpdf->showWatermarkText = true;
        $mpdf->SetTitle('INVOICE-'.$receipt_details->invoice_no.'.pdf');
        $mpdf->WriteHTML($body);
        $mpdf->Output('INVOICE-'.$receipt_details->invoice_no.'.pdf', 'I');
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
