<?php

namespace Modules\AiAssistance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Mail;
use Modules\AiAssistance\Entities\AiAssistanceSetting;
use Modules\AiAssistance\Services\ChatMemoryService;
use Modules\AiAssistance\Services\IntelligenceService;

class IntelligenceController extends Controller
{
    public function __construct(protected IntelligenceService $intel, protected ChatMemoryService $memory)
    {
    }

    protected function gate(): int
    {
        if (! auth()->user()->can('ai.access') && ! auth()->user()->can('ai.chat')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = (int) request()->session()->get('user.business_id');
        $settings = AiAssistanceSetting::forBusiness($business_id);
        if (! $settings->is_enabled) {
            abort(403, 'AI Assistant is disabled.');
        }

        return $business_id;
    }

    public function supplierPrices()
    {
        if (! auth()->user()->can('ai.access') && ! auth()->user()->can('ai.chat') && ! auth()->user()->can('purchase_n_sell_report.view')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = (int) request()->session()->get('user.business_id');
        $symbol = session('currency')['symbol'] ?? '';
        try {
            $rows = $this->intel->cheaperSupplierPrices($business_id);
        } catch (\Throwable $e) {
            $rows = [];
            if (request()->boolean('embed')) {
                return response('<p class="text-danger">'.e($e->getMessage()).'</p>', 200);
            }
        }

        if (request()->boolean('embed')) {
            $body = view('aiassistance::intelligence.supplier_prices_embed', compact('rows', 'symbol'))->render();

            return response()
                ->view('aiassistance::intelligence.embed_shell', ['embedBody' => $body])
                ->header('X-Frame-Options', 'SAMEORIGIN')
                ->header('Content-Security-Policy', "frame-ancestors 'self'");
        }

        return view('aiassistance::intelligence.supplier_prices', compact('rows', 'symbol'));
    }

    public function index()
    {
        $section = (string) request()->get('section', 'overview');
        $symbol = session('currency')['symbol'] ?? '';
        $chat_history = [];

        try {
            $business_id = $this->gate();
            $user_id = (int) request()->session()->get('user.id');
            $chat_history = $this->memory->load($business_id, $user_id, 'intelligence');

        if (request()->boolean('embed')) {
            $data = $this->intel->sectionData($business_id, $section);
            $body = view('aiassistance::intelligence.report', compact('data', 'section', 'chat_history', 'symbol'))->render();

            return response()
                ->view('aiassistance::intelligence.embed_shell', ['embedBody' => $body])
                ->header('X-Frame-Options', 'SAMEORIGIN')
                ->header('Content-Security-Policy', "frame-ancestors 'self'");
        }

            $data = $this->intel->snapshot($business_id);
        } catch (\Throwable $e) {
            if (request()->boolean('embed')) {
                return response(
                    view('aiassistance::intelligence.embed_shell', [
                        'embedBody' => '<p class="text-danger">Could not load this report: '.e($e->getMessage()).'</p>',
                    ])->render(),
                    200
                )->header('X-Frame-Options', 'SAMEORIGIN');
            }
            throw $e;
        }

        return view('aiassistance::intelligence.index', compact('data', 'section', 'chat_history', 'symbol'));
    }

    public function embed()
    {
        try {
            $business_id = $this->gate();
            $section = (string) request()->get('section', 'forecast');
            $symbol = session('currency')['symbol'] ?? '';
            if ($section === 'prices-table') {
                $rows = $this->intel->cheaperSupplierPrices($business_id);

                return response()
                    ->view('aiassistance::intelligence.supplier_prices_embed', compact('rows', 'symbol'))
                    ->header('Content-Type', 'text/html; charset=UTF-8');
            }
            $data = $this->intel->sectionData($business_id, $section);
            $chat_history = [];

            return response()
                ->view('aiassistance::intelligence.report', compact('data', 'section', 'chat_history', 'symbol'))
                ->header('Content-Type', 'text/html; charset=UTF-8');
        } catch (\Throwable $e) {
            return response(
                '<p class="text-danger">Could not load this report: '.e($e->getMessage()).'</p>',
                200
            )->header('Content-Type', 'text/html; charset=UTF-8');
        }
    }

    public function export(Request $request)
    {
        $business_id = $this->gate();
        $section = (string) $request->get('section', 'forecast');

        if ($section === 'prices-table') {
            $rows = $this->intel->cheaperSupplierPrices($business_id);
        } else {
            $data = $this->intel->sectionData($business_id, $section);
            $rows = match ($section) {
                'forecast' => $data['forecast']['ready'] ?? [],
                'collections' => $data['collections'] ?? [],
                'match' => $data['match']['exceptions'] ?? [],
                'slow' => $data['slow_movers'] ?? [],
                'credit' => $data['credit'] ?? [],
                'cash' => collect($data['cash_flow']['buckets'] ?? [])
                    ->map(fn ($v, $k) => ['days' => $k, 'expected_cash_in' => $v])->values()->all(),
                'cross' => $data['cross_sell'] ?? [],
                'churn' => $data['churn'] ?? [],
                'vendors' => $data['vendors'] ?? [],
                'prices' => $data['supplier_prices'] ?? [],
                'compliance' => array_merge(
                    array_map(fn ($r) => ['status' => 'expiring'] + (array) $r, $data['regulated']['licenses']['expiring'] ?? []),
                    array_map(fn ($r) => ['status' => 'missing'] + (is_array($r) ? $r : ['item' => $r]), $data['regulated']['licenses']['missing'] ?? [])
                ),
                'anomalies' => $data['anomalies'] ?? [],
                'digest' => array_map(
                    fn ($line) => ['digest' => $line],
                    array_values(array_filter(preg_split('/\r?\n/', (string) ($data['digest'] ?? ''))))
                ),
                default => [],
            };
        }

        $rows = array_values(array_filter((array) $rows, 'is_array'));
        $headers = [];
        foreach ($rows as $row) {
            foreach ($row as $key => $value) {
                if (! is_array($value) && ! is_object($value) && ! in_array($key, $headers, true)) {
                    $headers[] = $key;
                }
            }
        }

        $filename = 'japsai-'.preg_replace('/[^a-z0-9\-]/', '', strtolower($section)).'-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($rows, $headers) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            if ($headers === []) {
                fputcsv($out, ['No rows for this report'], ',', '"', '\\');
            } else {
                fputcsv($out, array_map(fn ($h) => ucfirst(str_replace('_', ' ', (string) $h)), $headers), ',', '"', '\\');
                foreach ($rows as $row) {
                    fputcsv($out, array_map(function ($h) use ($row) {
                        $v = $row[$h] ?? '';

                        return is_float($v) ? round($v, 2) : $v;
                    }, $headers), ',', '"', '\\');
                }
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function draftPo(Request $request)
    {
        $business_id = $this->gate();
        if (! auth()->user()->can('purchase_order.create') && ! auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        $ids = array_map('intval', (array) $request->input('variation_ids', []));
        if ($ids === []) {
            return back()->with('status', ['success' => 0, 'msg' => 'Select at least one SKU.']);
        }

        try {
            $po = $this->intel->createDraftPurchaseOrder(
                $business_id,
                (int) $request->session()->get('user.id'),
                $ids
            );
        } catch (\Throwable $e) {
            return back()->with('status', ['success' => 0, 'msg' => $e->getMessage()]);
        }

        return redirect()
            ->action([\App\Http\Controllers\PurchaseOrderController::class, 'edit'], [$po->id])
            ->with('status', [
                'success' => 1,
                'msg' => 'Draft PO '.$po->ref_no.' created for review. Not sent to the vendor.',
            ]);
    }

    public function chat(Request $request)
    {
        $business_id = $this->gate();
        $user_id = (int) $request->session()->get('user.id');
        $request->validate(['message' => 'required|string|max:4000']);
        $history = $this->memory->load($business_id, $user_id, 'intelligence');
        $message = trim($request->input('message'));
        $reply = $this->intel->ask($business_id, $message, $history);
        $this->memory->append($business_id, $user_id, 'intelligence', [
            ['role' => 'user', 'content' => $message],
            ['role' => 'assistant', 'content' => $reply],
        ]);

        return response()->json(['success' => true, 'reply' => $reply]);
    }

    public function sendDigest(Request $request)
    {
        $business_id = $this->gate();
        $email = trim((string) $request->input('email'));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return back()->with('status', ['success' => 0, 'msg' => 'Enter a valid email. Digest is never sent without this step.']);
        }
        $data = $this->intel->snapshot($business_id);
        Mail::raw($data['digest'], function ($m) use ($email) {
            $m->to($email)->subject('JapsAI daily digest (draft summary)');
        });

        return back()->with('status', ['success' => 1, 'msg' => 'Digest emailed to '.$email.'.']);
    }

    public function reviewMatch(Request $request)
    {
        $business_id = $this->gate();
        $line = (int) $request->input('po_line_id');
        $this->intel->markMatchReviewed($business_id, $line);

        return back()->with('status', ['success' => 1, 'msg' => 'Exception marked reviewed. Invoice was not auto-paid.']);
    }

    public function posHints(Request $request)
    {
        $business_id = $this->gate();
        $contact = (int) $request->get('contact_id');
        if ($contact < 1) {
            return response()->json(['credit' => ['flag' => false], 'reorder' => []]);
        }

        return response()->json($this->intel->posHints($business_id, $contact));
    }

    public function addons(Request $request)
    {
        $business_id = $this->gate();
        $vid = (int) $request->get('variation_id');
        if ($vid < 1) {
            return response()->json(['addons' => []]);
        }

        return response()->json(['addons' => $this->intel->addonsForVariation($business_id, $vid)]);
    }
}
