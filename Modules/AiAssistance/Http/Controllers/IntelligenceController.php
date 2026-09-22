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
        $rows = $this->intel->cheaperSupplierPrices($business_id);
        $symbol = session('currency')['symbol'] ?? '';

        return view('aiassistance::intelligence.supplier_prices', compact('rows', 'symbol'));
    }

    public function index()
    {
        $business_id = $this->gate();
        $data = $this->intel->snapshot($business_id);
        $section = request()->get('section', 'overview');
        $user_id = (int) request()->session()->get('user.id');
        $chat_history = $this->memory->load($business_id, $user_id, 'intelligence');
        $symbol = session('currency')['symbol'] ?? '';

        return view('aiassistance::intelligence.index', compact('data', 'section', 'chat_history', 'symbol'));
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
