<?php

namespace Modules\AiAssistance\Http\Controllers;

use App\Utils\TransactionUtil;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Modules\AiAssistance\Services\InvoiceExtractionService;

class InvoiceExtractionController extends Controller
{
    public function __construct(protected InvoiceExtractionService $service)
    {
    }

    /**
     * Read an uploaded vendor invoice image and return extracted line items for preview.
     * Does not write anything to the business database.
     */
    public function extract(Request $request, TransactionUtil $transactionUtil)
    {
        if (! auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            // Kept in sync with config('constants.document_size_limit') (5,000,000 bytes) so the
            // file also successfully stores as the purchase's attached document further below.
            'invoice_file' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:4800',
        ]);

        $business_id = (int) $request->session()->get('user.business_id');
        $is_pdf = $request->file('invoice_file')->getMimeType() === 'application/pdf';

        $result = $this->service->extract($business_id, $request->file('invoice_file'));

        if (empty($result['success'])) {
            return response()->json([
                'success' => false,
                'msg' => $result['msg'] ?? __('aiassistance::lang.chat_error'),
            ]);
        }

        try {
            // PDFs go through the 'document' mime allowlist (which already includes
            // application/pdf); images are verified as image/* via the 'image' branch.
            $stored_name = $transactionUtil->uploadFile($request, 'invoice_file', 'documents', $is_pdf ? 'document' : 'image');
        } catch (\Throwable $e) {
            $stored_name = null;
        }

        $token = (string) Str::uuid();
        Cache::put('ai_invoice_'.$token, [
            'business_id' => $business_id,
            'data' => $result['data'],
            'image' => $stored_name,
            'is_pdf' => $is_pdf,
        ], now()->addMinutes(30));

        return response()->json([
            'success' => true,
            'token' => $token,
            'data' => $result['data'],
            'image_url' => $stored_name ? asset('/uploads/documents/'.$stored_name) : null,
            'is_pdf' => $is_pdf,
        ]);
    }

    /**
     * Fetch a previously extracted invoice by token (used by the chat "review" deep link).
     */
    public function result(Request $request, string $token)
    {
        if (! auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = (int) $request->session()->get('user.business_id');
        $payload = Cache::get('ai_invoice_'.$token);

        if (empty($payload) || (int) ($payload['business_id'] ?? 0) !== $business_id) {
            return response()->json(['success' => false, 'msg' => __('messages.something_went_wrong')], 404);
        }

        return response()->json([
            'success' => true,
            'token' => $token,
            'data' => $payload['data'],
            'image_url' => ! empty($payload['image']) ? asset('/uploads/documents/'.$payload['image']) : null,
            'is_pdf' => ! empty($payload['is_pdf']),
        ]);
    }
}
