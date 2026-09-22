<?php

namespace Modules\AiAssistance\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\AiAssistance\Entities\AiAssistanceSetting;

class InvoiceExtractionService
{
    protected const SYSTEM_PROMPT = <<<'PROMPT'
You are reading a photo, scan, or PDF of a vendor purchase invoice / bill / purchase order for a retail or wholesale POS system.
Extract the data and return ONLY a single valid JSON object (no markdown, no commentary) with this exact shape:

{
  "supplier_name": string|null,
  "ref_no": string|null,
  "invoice_date": string|null,
  "currency": string|null,
  "lines": [
    { "name": string, "sku": string|null, "quantity": number, "unit_price": number }
  ],
  "subtotal": number|null,
  "tax_amount": number|null,
  "total": number|null
}

Rules:
- "ref_no" is the vendor PO number or invoice number printed on the document (prefer PO # when both appear).
- "invoice_date" must be formatted as YYYY-MM-DD if you can determine it, else null.
- "lines" must contain one entry per distinct product/item row on the invoice. If the file has multiple pages, include line items from every page. Do not drop rows.
- "quantity" and "unit_price" must be plain numbers only (no currency symbols, no thousands separators).
- "unit_price" is the per-unit cost on that row (not the line total). If only line total and quantity are printed, compute unit_price = line_total / quantity.
- If a SKU / item code / product id / barcode is printed next to a line, put it in "sku", else null.
- Do not invent data that is not visibly on the document. Use null when unsure.
- Return ONLY the JSON object, nothing else.
PROMPT;

    /**
     * Read a vendor invoice (image or PDF) with OpenAI and return normalized line-item data.
     * Never writes to the database — purely an extraction/preview step.
     */
    public function extract(int $business_id, UploadedFile $file): array
    {
        $settings = AiAssistanceSetting::forBusiness($business_id);
        $api_key = $settings->resolveApiKey();

        if (empty($api_key)) {
            return [
                'success' => false,
                'msg' => __('aiassistance::lang.no_api_key'),
            ];
        }

        // response_format=json_object / structured output requires a vision-capable model;
        // fall back if the business has configured an older non-vision alias.
        $model = $settings->resolveModel();
        if (! preg_match('/gpt-4o|gpt-4\.1|gpt-5|omni/i', $model)) {
            $model = 'gpt-4o-mini';
        }

        $mime = $file->getMimeType() ?: 'image/jpeg';

        try {
            if ($mime === 'application/pdf') {
                return $this->extractFromPdf($api_key, $model, $file);
            }

            return $this->extractFromImage($api_key, $model, $file, $mime);
        } catch (\Throwable $e) {
            Log::error('InvoiceExtractionService: '.$e->getMessage());

            return ['success' => false, 'msg' => $e->getMessage()];
        }
    }

    /**
     * Images go through Chat Completions vision — a single inline base64 data URI.
     */
    protected function extractFromImage(string $api_key, string $model, UploadedFile $file, string $mime): array
    {
        $encoded = base64_encode((string) file_get_contents($file->getRealPath()));
        $data_uri = 'data:'.$mime.';base64,'.$encoded;

        $response = Http::withToken($api_key)
            ->timeout(90)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'temperature' => 0,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
                    [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => 'Extract the invoice data as instructed.'],
                            ['type' => 'image_url', 'image_url' => ['url' => $data_uri]],
                        ],
                    ],
                ],
            ]);

        if (! $response->successful()) {
            return ['success' => false, 'msg' => $response->json('error.message') ?: $response->body()];
        }

        $content = data_get($response->json(), 'choices.0.message.content');
        if (empty($content)) {
            return ['success' => false, 'msg' => __('aiassistance::lang.chat_error')];
        }

        $parsed = $this->parseJsonLoosely((string) $content);
        if (! is_array($parsed)) {
            return ['success' => false, 'msg' => 'JapsAI could not read structured data from this image. Try a clearer photo.'];
        }

        return ['success' => true, 'data' => $this->normalize($parsed)];
    }

    /**
     * PDFs are sent to OpenAI's Files + Responses API. This box only has the GD PHP
     * extension (no Imagick), so we can't rasterize a PDF locally — instead we hand the
     * original PDF to OpenAI directly, which reads it natively (no local conversion).
     */
    protected function extractFromPdf(string $api_key, string $model, UploadedFile $file): array
    {
        // OpenAI rejects uppercase extensions (e.g. .PDF) — always send lowercase .pdf.
        $uploadName = 'invoice.pdf';
        $original = (string) ($file->getClientOriginalName() ?: '');
        if ($original !== '') {
            $base = pathinfo($original, PATHINFO_FILENAME) ?: 'invoice';
            $uploadName = preg_replace('/[^A-Za-z0-9_\-]+/', '_', $base).'.pdf';
        }

        $upload = Http::withToken($api_key)
            ->timeout(60)
            ->attach('file', (string) file_get_contents($file->getRealPath()), $uploadName)
            ->post('https://api.openai.com/v1/files', [
                'purpose' => 'user_data',
            ]);

        if (! $upload->successful()) {
            return ['success' => false, 'msg' => $upload->json('error.message') ?: $upload->body()];
        }

        $file_id = $upload->json('id');
        if (empty($file_id)) {
            return ['success' => false, 'msg' => 'Could not upload PDF to OpenAI.'];
        }

        try {
            $response = Http::withToken($api_key)
                ->timeout(90)
                ->post('https://api.openai.com/v1/responses', [
                    'model' => $model,
                    'temperature' => 0,
                    'input' => [
                        [
                            'role' => 'system',
                            'content' => [
                                ['type' => 'input_text', 'text' => self::SYSTEM_PROMPT],
                            ],
                        ],
                        [
                            'role' => 'user',
                            'content' => [
                                ['type' => 'input_text', 'text' => 'Extract the invoice data as instructed.'],
                                ['type' => 'input_file', 'file_id' => $file_id],
                            ],
                        ],
                    ],
                ]);
        } finally {
            // Best-effort cleanup — don't let a failed delete affect the extraction result.
            try {
                Http::withToken($api_key)->delete('https://api.openai.com/v1/files/'.$file_id);
            } catch (\Throwable $e) {
                // ignore
            }
        }

        if (! $response->successful()) {
            return ['success' => false, 'msg' => $response->json('error.message') ?: $response->body()];
        }

        $content = $this->extractResponsesText((array) $response->json());
        if (empty($content)) {
            return ['success' => false, 'msg' => __('aiassistance::lang.chat_error')];
        }

        $parsed = $this->parseJsonLoosely($content);
        if (! is_array($parsed)) {
            return ['success' => false, 'msg' => 'JapsAI could not read structured data from this PDF.'];
        }

        return ['success' => true, 'data' => $this->normalize($parsed)];
    }

    /**
     * Pull the assistant's text out of a Responses API payload. Walks the documented
     * output[].content[] shape; falls back defensively in case of minor API differences.
     */
    protected function extractResponsesText(array $json): ?string
    {
        if (! empty($json['output_text'])) {
            return (string) $json['output_text'];
        }

        foreach ((array) ($json['output'] ?? []) as $item) {
            if (($item['type'] ?? null) !== 'message') {
                continue;
            }
            foreach ((array) ($item['content'] ?? []) as $part) {
                if (in_array($part['type'] ?? null, ['output_text', 'text'], true) && ! empty($part['text'])) {
                    return (string) $part['text'];
                }
            }
        }

        return null;
    }

    /**
     * Parse JSON, falling back to extracting the first {...} block if the model
     * wrapped it in stray text (Responses API isn't given strict json_object mode here).
     */
    protected function parseJsonLoosely(string $content): ?array
    {
        $parsed = json_decode($content, true);
        if (is_array($parsed)) {
            return $parsed;
        }

        if (preg_match('/\{.*\}/s', $content, $m)) {
            $parsed = json_decode($m[0], true);
            if (is_array($parsed)) {
                return $parsed;
            }
        }

        return null;
    }

    protected function normalize(array $parsed): array
    {
        $lines = [];
        foreach ((array) ($parsed['lines'] ?? []) as $line) {
            if (empty($line['name'])) {
                continue;
            }
            $lines[] = [
                'name' => (string) $line['name'],
                'sku' => ! empty($line['sku']) ? (string) $line['sku'] : null,
                'quantity' => is_numeric($line['quantity'] ?? null) ? (float) $line['quantity'] : 1.0,
                'unit_price' => is_numeric($line['unit_price'] ?? null) ? (float) $line['unit_price'] : 0.0,
            ];
        }

        return [
            'supplier_name' => ! empty($parsed['supplier_name']) ? (string) $parsed['supplier_name'] : null,
            'ref_no' => ! empty($parsed['ref_no']) ? (string) $parsed['ref_no'] : null,
            'invoice_date' => ! empty($parsed['invoice_date']) ? (string) $parsed['invoice_date'] : null,
            'currency' => ! empty($parsed['currency']) ? (string) $parsed['currency'] : null,
            'lines' => $lines,
            'subtotal' => is_numeric($parsed['subtotal'] ?? null) ? (float) $parsed['subtotal'] : null,
            'tax_amount' => is_numeric($parsed['tax_amount'] ?? null) ? (float) $parsed['tax_amount'] : null,
            'total' => is_numeric($parsed['total'] ?? null) ? (float) $parsed['total'] : null,
        ];
    }

    /**
     * Preview match only — no DB writes. Used so the user can confirm before
     * auto-adding missing products/supplier and creating/updating the PO.
     */
    public function previewForConfirm(int $business_id, array $data): array
    {
        $matched = [];
        $missing = [];

        foreach ((array) ($data['lines'] ?? []) as $idx => $line) {
            $hit = $this->matchProductLine($business_id, $line);
            $row = [
                'index' => (int) $idx,
                'name' => (string) ($line['name'] ?? 'unknown'),
                'sku' => ! empty($line['sku']) ? (string) $line['sku'] : null,
                'quantity' => max(0.01, (float) ($line['quantity'] ?? 1)),
                'unit_price' => max(0, (float) ($line['unit_price'] ?? 0)),
            ];
            if ($hit) {
                $matched[] = array_merge($row, [
                    'product_id' => (int) $hit['product_id'],
                    'variation_id' => (int) $hit['variation_id'],
                ]);
            } else {
                $missing[] = $row;
            }
        }

        $supplierName = trim((string) ($data['supplier_name'] ?? ''));
        $supplierId = $supplierName !== '' ? $this->matchSupplier($business_id, $supplierName) : null;
        $supplier = [
            'name' => $supplierName !== '' ? $supplierName : null,
            'found' => (bool) $supplierId,
            'contact_id' => $supplierId,
            'will_create' => $supplierName !== '' && ! $supplierId,
        ];

        $existing = $this->findExistingPurchaseOrder($business_id, $data['ref_no'] ?? null);
        $existingPo = null;
        if ($existing) {
            $existingPo = [
                'id' => $existing->id,
                'ref_no' => $existing->ref_no,
                'status' => $existing->status ?: 'ordered',
                'edit_url' => action([\App\Http\Controllers\PurchaseOrderController::class, 'edit'], [$existing->id]),
            ];
        }

        $parts = [];
        $parts[] = count($matched).' item(s) OK in product list';
        if (count($missing) > 0) {
            $parts[] = count($missing).' item(s) not found — will auto-add on confirm';
        }
        if ($supplier['will_create']) {
            $parts[] = 'supplier "'.$supplierName.'" not found — will auto-add on confirm';
        } elseif ($supplier['found']) {
            $parts[] = 'supplier OK';
        }
        if ($existingPo) {
            $parts[] = 'existing PO '.$existingPo['ref_no'].' will be updated';
        } else {
            $parts[] = 'new draft PO will be created';
        }

        return [
            'needs_confirm' => true,
            'matched' => $matched,
            'matched_count' => count($matched),
            'missing' => $missing,
            'missing_count' => count($missing),
            'supplier' => $supplier,
            'existing_po' => $existingPo,
            'ref_no' => $data['ref_no'] ?? null,
            'total' => $data['total'] ?? null,
            'msg' => implode('. ', $parts).'. Confirm to continue.',
        ];
    }

    /**
     * After user confirms: create missing products + supplier, then create/update PO.
     */
    public function confirmAndCreatePurchaseOrder(int $business_id, int $user_id, array $data): array
    {
        $createdProducts = [];
        $createdSupplier = null;

        foreach ((array) ($data['lines'] ?? []) as $line) {
            if ($this->matchProductLine($business_id, $line)) {
                continue;
            }
            try {
                $created = $this->createProductFromInvoiceLine($business_id, $user_id, $line);
                if ($created) {
                    $createdProducts[] = $created;
                }
            } catch (\Throwable $e) {
                Log::warning('InvoiceExtractionService create product: '.$e->getMessage());
            }
        }

        $supplierName = trim((string) ($data['supplier_name'] ?? ''));
        if ($supplierName !== '' && ! $this->matchSupplier($business_id, $supplierName)) {
            try {
                $createdSupplier = $this->createSupplierFromInvoice($business_id, $user_id, $supplierName);
            } catch (\Throwable $e) {
                Log::warning('InvoiceExtractionService create supplier: '.$e->getMessage());
            }
        }

        $result = $this->createDraftPurchaseOrderFromExtract($business_id, $user_id, $data);
        $result['created_products'] = $createdProducts;
        $result['created_products_count'] = count($createdProducts);
        $result['created_supplier'] = $createdSupplier;
        $result['confirmed'] = true;

        return $result;
    }

    /**
     * Create a simple single product from an invoice line (enable stock, invoice cost).
     */
    protected function createProductFromInvoiceLine(int $business_id, int $user_id, array $line): ?array
    {
        $name = trim((string) ($line['name'] ?? ''));
        if ($name === '') {
            return null;
        }

        $unitId = \App\Unit::where('business_id', $business_id)->value('id');
        if (! $unitId) {
            throw new \RuntimeException('No unit defined — cannot auto-add product.');
        }

        $sku = trim((string) ($line['sku'] ?? ''));
        $price = max(0, (float) ($line['unit_price'] ?? 0));
        $util = app(\App\Utils\ProductUtil::class);

        $product = \App\Product::create([
            'name' => $name,
            'business_id' => $business_id,
            'created_by' => $user_id,
            'type' => 'single',
            'unit_id' => $unitId,
            'barcode_type' => 'C128',
            'tax_type' => 'exclusive',
            'enable_stock' => 1,
            'sku' => $sku !== '' ? $sku : ' ',
        ]);

        if (trim((string) $product->sku) === '') {
            $product->sku = $util->generateProductSku($product->id);
            $product->save();
        }

        $locationIds = \App\BusinessLocation::where('business_id', $business_id)->pluck('id')->all();
        if ($locationIds !== []) {
            $product->product_locations()->sync($locationIds);
        }

        $util->createSingleProductVariation(
            $product->id,
            $product->sku,
            $price,
            $price,
            0,
            $price,
            $price
        );

        $variation = \App\Variation::where('product_id', $product->id)->whereNull('deleted_at')->first();

        return [
            'name' => $product->name,
            'sku' => $product->sku,
            'product_id' => $product->id,
            'variation_id' => $variation ? (int) $variation->id : null,
            'unit_price' => $price,
        ];
    }

    /**
     * Create a supplier contact from the invoice supplier name.
     */
    protected function createSupplierFromInvoice(int $business_id, int $user_id, string $name): ?array
    {
        $util = app(\App\Utils\ContactUtil::class);
        $out = $util->createNewContact([
            'business_id' => $business_id,
            'type' => 'supplier',
            'name' => $name,
            'supplier_business_name' => $name,
            'mobile' => '0000000000',
            'created_by' => $user_id,
            'contact_status' => 'active',
        ]);

        $contact = $out['data'] ?? null;
        if (! $contact) {
            return null;
        }

        return [
            'id' => (int) $contact->id,
            'name' => $contact->supplier_business_name ?: $contact->name,
        ];
    }

    /**
     * Prefer matching an existing PO by invoice ref. Only create a new draft when none exists.
     * Draft only — not sent to the vendor. Lines always keep invoice unit prices.
     */
    public function createDraftPurchaseOrderFromExtract(int $business_id, int $user_id, array $data): array
    {
        $tolerance = 2.0;
        try {
            $tolerance = (float) (AiAssistanceSetting::forBusiness($business_id)->match_tolerance_pct ?: 2);
        } catch (\Throwable $e) {
            $tolerance = 2.0;
        }

        $existing = $this->findExistingPurchaseOrder($business_id, $data['ref_no'] ?? null);
        if ($existing) {
            return $this->matchInvoiceAgainstPurchaseOrder($business_id, $existing, $data, $tolerance);
        }

        return $this->createNewDraftPurchaseOrder($business_id, $user_id, $data, $tolerance);
    }

    /**
     * Find PO by vendor ref (exact, AI-prefixed, or contains the digits).
     */
    protected function findExistingPurchaseOrder(int $business_id, ?string $ref): ?\App\Transaction
    {
        $ref = trim((string) $ref);
        if ($ref === '') {
            return null;
        }

        $clean = preg_replace('/[^A-Za-z0-9\-_]/', '', $ref);
        $candidates = array_values(array_unique(array_filter([
            $ref,
            $clean,
            'AI-'.$clean,
            ltrim($clean, '0'),
        ])));

        $txn = \App\Transaction::where('business_id', $business_id)
            ->where('type', 'purchase_order')
            ->whereIn('ref_no', $candidates)
            ->orderByDesc('id')
            ->first();
        if ($txn) {
            return $txn;
        }

        // Numeric vendor refs are often stored without prefixes (e.g. 1068471).
        if (preg_match('/\d{4,}/', $clean, $m)) {
            $needle = $m[0];

            return \App\Transaction::where('business_id', $business_id)
                ->where('type', 'purchase_order')
                ->where(function ($q) use ($needle) {
                    $q->where('ref_no', $needle)
                        ->orWhere('ref_no', 'like', '%'.$needle.'%');
                })
                ->orderByDesc('id')
                ->first();
        }

        return null;
    }

    /**
     * Match invoice to existing PO: write invoice unit prices onto PO lines,
     * sync catalog PO cost, and collect admin alert rows.
     */
    protected function matchInvoiceAgainstPurchaseOrder(
        int $business_id,
        \App\Transaction $po,
        array $data,
        float $tolerance
    ): array {
        $po->load(['purchase_lines']);
        $poByVariation = [];
        foreach ($po->purchase_lines as $pl) {
            $poByVariation[(int) $pl->variation_id] = $pl;
        }

        $within = [];
        $exceptions = [];
        $unmatched = [];
        $updated = [];
        $matched = 0;
        $total = 0.0;

        \Illuminate\Support\Facades\DB::transaction(function () use (
            $business_id, $po, $data, $tolerance, &$poByVariation, &$within, &$exceptions,
            &$unmatched, &$updated, &$matched, &$total
        ) {
            foreach ((array) ($data['lines'] ?? []) as $line) {
                $hit = $this->matchProductLine($business_id, $line);
                $label = (string) ($line['sku'] ?? $line['name'] ?? 'unknown');
                $invQty = max(0.01, (float) ($line['quantity'] ?? 1));
                $invPrice = max(0, (float) ($line['unit_price'] ?? 0));

                if (! $hit) {
                    $unmatched[] = $label;

                    continue;
                }

                $matched++;
                $vid = (int) $hit['variation_id'];
                $poLine = $poByVariation[$vid] ?? null;
                if (! $poLine) {
                    // Product exists (or was just auto-added) but not on this PO — append a line.
                    if ($invPrice <= 0 && $invQty <= 0) {
                        $exceptions[] = [
                            'label' => $label,
                            'product_id' => (int) $hit['product_id'],
                            'msg' => 'on invoice but not on PO '.$po->ref_no.' (skipped — no price)',
                            'invoice_price' => $invPrice,
                            'po_price' => null,
                            'invoice_qty' => $invQty,
                            'po_qty' => null,
                        ];

                        continue;
                    }

                    $poLine = new \App\PurchaseLine();
                    $poLine->transaction_id = $po->id;
                    $poLine->product_id = (int) $hit['product_id'];
                    $poLine->variation_id = $vid;
                    $poLine->quantity = $invQty;
                    $poLine->pp_without_discount = $invPrice;
                    $poLine->purchase_price = $invPrice;
                    $poLine->purchase_price_inc_tax = $invPrice;
                    $poLine->item_tax = 0;
                    $poLine->discount_percent = 0;
                    $poLine->save();
                    $poByVariation[$vid] = $poLine;

                    $updated[] = [
                        'product_id' => (int) $hit['product_id'],
                        'label' => $label,
                        'old_cost' => null,
                        'new_cost' => $invPrice,
                        'old_qty' => null,
                        'new_qty' => $invQty,
                    ];
                    $within[] = [
                        'label' => $label,
                        'product_id' => (int) $hit['product_id'],
                        'invoice_price' => $invPrice,
                        'po_price' => $invPrice,
                        'invoice_qty' => $invQty,
                        'po_qty' => $invQty,
                        'recv_qty' => 0,
                        'price_var_pct' => 0,
                        'qty_var_pct' => 0,
                        'msg' => 'added to PO '.$po->ref_no.' from invoice (new line).',
                    ];

                    continue;
                }

                $poQtyBefore = (float) $poLine->quantity;
                $poPriceBefore = (float) $poLine->purchase_price;
                $recvQty = (float) ($poLine->po_quantity_purchased ?? 0);

                // Invoice wins: set PO line unit cost (and qty) from the invoice.
                if ($invPrice > 0) {
                    $poLine->pp_without_discount = $invPrice;
                    $poLine->purchase_price = $invPrice;
                    $poLine->purchase_price_inc_tax = $invPrice;
                    $poLine->item_tax = 0;
                }
                $poLine->quantity = $invQty;
                $poLine->save();

                $newPrice = (float) $poLine->purchase_price;
                $priceVarBefore = $poPriceBefore > 0
                    ? abs($invPrice - $poPriceBefore) / $poPriceBefore * 100
                    : ($invPrice > 0 ? 100 : 0);

                $row = [
                    'label' => $label,
                    'product_id' => (int) $hit['product_id'],
                    'invoice_price' => $invPrice,
                    'po_price' => $newPrice,
                    'po_price_before' => $poPriceBefore,
                    'invoice_qty' => $invQty,
                    'po_qty' => $invQty,
                    'recv_qty' => $recvQty,
                    'price_var_pct' => round($priceVarBefore, 2),
                    'qty_var_pct' => 0,
                ];

                if (round($poPriceBefore, 4) !== round($newPrice, 4) || round($poQtyBefore, 4) !== round($invQty, 4)) {
                    $updated[] = [
                        'product_id' => (int) $hit['product_id'],
                        'label' => $label,
                        'old_cost' => $poPriceBefore,
                        'new_cost' => $newPrice,
                        'old_qty' => $poQtyBefore,
                        'new_qty' => $invQty,
                    ];
                    $row['msg'] = 'updated PO line to invoice: cost '
                        .$poPriceBefore.' → '.$newPrice
                        .', qty '.$poQtyBefore.' → '.$invQty
                        .' (received '.$recvQty.').';
                    if ($priceVarBefore > $tolerance) {
                        $exceptions[] = $row;
                    } else {
                        $within[] = $row;
                    }
                } elseif ($priceVarBefore <= $tolerance) {
                    $row['msg'] = 'within '.$tolerance.'% of PO '.$po->ref_no
                        .' (ordered '.$invQty.', received '.$recvQty.'). Cost already matched invoice.';
                    $within[] = $row;
                } else {
                    $row['msg'] = 'price exception vs prior PO cost '.$poPriceBefore
                        .' (invoice '.$invPrice.'). Line set to invoice amount.';
                    $exceptions[] = $row;
                }
            }

            // Recalc PO totals from current lines (including untouched ones).
            $po->load('purchase_lines');
            foreach ($po->purchase_lines as $pl) {
                $total += (float) $pl->quantity * (float) $pl->purchase_price;
            }
            $po->total_before_tax = $total;
            $po->final_total = $total;
            $notes = trim((string) $po->additional_notes);
            $stamp = 'JapsAI '.now()->format('Y-m-d H:i').': PO lines updated from uploaded invoice (costs + qty). Catalog PO cost synced where changed.';
            $po->additional_notes = $notes === '' ? $stamp : ($notes."\n".$stamp);
            $po->save();
        });

        $costChanges = session('japs_po_cost_changes', []);
        $statusLabel = $po->status ?: 'ordered';

        return [
            'created' => false,
            'matched_existing' => true,
            'po_updated' => count($updated) > 0,
            'updated_count' => count($updated),
            'updated' => array_slice($updated, 0, 40),
            'cost_changes' => $costChanges,
            'po_id' => $po->id,
            'ref_no' => $po->ref_no,
            'status' => $statusLabel,
            'matched' => $matched,
            'unmatched' => $unmatched,
            'within' => array_slice($within, 0, 40),
            'within_count' => count($within),
            'exceptions' => array_slice($exceptions, 0, 40),
            'exception_count' => count($exceptions),
            'tolerance_pct' => $tolerance,
            'edit_url' => action([\App\Http\Controllers\PurchaseOrderController::class, 'edit'], [$po->id]),
            'msg' => 'PO '.$po->ref_no.' updated from invoice ('.count($updated).' line cost/qty change(s)). '
                .'Catalog PO cost synced where different. '
                .count($within).' within '.$tolerance.'%, '.count($exceptions).' exception(s)'
                .(count($unmatched) ? ', '.count($unmatched).' unmatched' : '')
                .'. Admin alert queued. Not sent to vendor.',
        ];
    }

    /**
     * Create a new draft PO using invoice unit prices only (never catalog fallback).
     */
    protected function createNewDraftPurchaseOrder(
        int $business_id,
        int $user_id,
        array $data,
        float $tolerance
    ): array {
        $matched = [];
        $unmatched = [];
        $missing_price = [];

        foreach ((array) ($data['lines'] ?? []) as $line) {
            $hit = $this->matchProductLine($business_id, $line);
            $label = (string) ($line['name'] ?? ($line['sku'] ?? 'unknown'));
            if ($hit) {
                $unitPrice = max(0, (float) ($line['unit_price'] ?? 0));
                if ($unitPrice <= 0) {
                    $missing_price[] = $label;
                }
                $matched[] = array_merge($hit, [
                    'quantity' => max(0.01, (float) ($line['quantity'] ?? 1)),
                    'unit_price' => $unitPrice,
                    'source_name' => $label,
                ]);
            } else {
                $unmatched[] = $label;
            }
        }

        if ($matched === []) {
            return [
                'created' => false,
                'msg' => 'No catalog match for any line — open review to map products.'
                    .(count($unmatched) ? ' Unmatched: '.implode(', ', array_slice($unmatched, 0, 10)) : ''),
                'matched' => 0,
                'unmatched' => $unmatched,
            ];
        }

        $contactId = $this->matchSupplier($business_id, $data['supplier_name'] ?? null);
        if (! $contactId) {
            $contactId = \App\Contact::where('business_id', $business_id)
                ->whereIn('type', ['supplier', 'both'])
                ->value('id');
        }
        if (! $contactId) {
            return [
                'created' => false,
                'msg' => 'No supplier on file to attach the draft PO.',
                'matched' => count($matched),
                'unmatched' => $unmatched,
            ];
        }

        $locationId = \App\BusinessLocation::where('business_id', $business_id)->value('id');
        if (! $locationId) {
            return [
                'created' => false,
                'msg' => 'No business location found.',
                'matched' => count($matched),
                'unmatched' => $unmatched,
            ];
        }

        $total = 0;
        $purchases = [];
        foreach ($matched as $line) {
            // Always use invoice unit price — never substitute catalog default_purchase_price.
            $price = round((float) $line['unit_price'], 4);
            $qty = (float) $line['quantity'];
            $total += $qty * $price;
            $priceStr = number_format($price, 4, '.', '');
            $purchases[] = [
                'product_id' => $line['product_id'],
                'variation_id' => $line['variation_id'],
                'quantity' => number_format($qty, 4, '.', ''),
                'pp_without_discount' => $priceStr,
                'discount_percent' => 0,
                'purchase_price' => $priceStr,
                'purchase_price_inc_tax' => $priceStr,
                'item_tax' => 0,
                'purchase_line_tax_id' => null,
            ];
        }

        $cleanRef = ! empty($data['ref_no'])
            ? preg_replace('/[^A-Za-z0-9\-_]/', '', (string) $data['ref_no'])
            : '';
        // Keep AI- prefix so we never overwrite / collide with a real vendor PO number.
        $ref = $cleanRef !== ''
            ? 'AI-'.$cleanRef
            : 'AI-PO-'.\Carbon\Carbon::now()->format('Ymd-His');

        $txnDate = \Carbon\Carbon::now()->toDateTimeString();
        if (! empty($data['invoice_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $data['invoice_date'])) {
            $txnDate = $data['invoice_date'].' '.\Carbon\Carbon::now()->format('H:i:s');
        }

        $transaction = \Illuminate\Support\Facades\DB::transaction(function () use (
            $business_id, $user_id, $contactId, $locationId, $total, $purchases, $ref, $txnDate, $unmatched, $missing_price
        ) {
            $notes = 'JapsAI draft PO from uploaded invoice. Prices copied from invoice. '
                .'Status ordered = draft for review — not sent to vendor. Receiving still updates stock when you post a purchase.';
            if ($unmatched !== []) {
                $notes .= ' Unmatched lines skipped: '.implode(', ', array_slice($unmatched, 0, 20));
            }
            if ($missing_price !== []) {
                $notes .= ' Lines with missing invoice price (saved as 0): '.implode(', ', array_slice($missing_price, 0, 20));
            }

            $transaction = \App\Transaction::create([
                'business_id' => $business_id,
                'location_id' => $locationId,
                'type' => 'purchase_order',
                'status' => 'ordered',
                'contact_id' => $contactId,
                'transaction_date' => $txnDate,
                'total_before_tax' => $total,
                'final_total' => $total,
                'discount_type' => 'fixed',
                'discount_amount' => 0,
                'tax_amount' => 0,
                'exchange_rate' => 1,
                'created_by' => $user_id,
                'ref_no' => $ref,
                'additional_notes' => $notes,
            ]);

            $util = app(\App\Utils\ProductUtil::class);
            // Object + empty thousand sep so num_uf keeps invoice decimals intact.
            $currency = (object) ['thousand_separator' => '', 'decimal_separator' => '.', 'symbol' => ''];
            $util->createOrUpdatePurchaseLines($transaction, $purchases, $currency, 0);

            return $transaction->fresh();
        });

        return [
            'created' => true,
            'matched_existing' => false,
            'po_updated' => true,
            'po_id' => $transaction->id,
            'ref_no' => $transaction->ref_no,
            'status' => 'ordered',
            'matched' => count($matched),
            'unmatched' => $unmatched,
            'missing_price' => $missing_price,
            'cost_changes' => session('japs_po_cost_changes', []),
            'tolerance_pct' => $tolerance,
            'edit_url' => action([\App\Http\Controllers\PurchaseOrderController::class, 'edit'], [$transaction->id]),
            'msg' => 'Draft PO '.$transaction->ref_no.' created (status: ordered = new/review, not sent). '
                .count($matched).' line(s) with invoice prices'
                .(count($unmatched) ? '; '.count($unmatched).' unmatched skipped: '.implode(', ', array_slice($unmatched, 0, 5)) : '')
                .(count($missing_price) ? '; '.count($missing_price).' missing invoice price' : '')
                .'. Catalog PO cost updated where different — admin alert queued.',
        ];
    }

    protected function matchProductLine(int $business_id, array $line): ?array
    {
        $sku = trim((string) ($line['sku'] ?? ''));
        $name = trim((string) ($line['name'] ?? ''));
        $select = ['p.id as product_id', 'v.id as variation_id', 'v.default_purchase_price'];

        if ($sku !== '') {
            $row = \Illuminate\Support\Facades\DB::table('variations as v')
                ->join('products as p', 'p.id', '=', 'v.product_id')
                ->where('p.business_id', $business_id)
                ->whereNull('v.deleted_at')
                ->where(function ($q) use ($sku) {
                    $q->where('v.sub_sku', $sku)
                        ->orWhere('p.sku', $sku)
                        ->orWhere('v.sub_sku', ltrim($sku, '0'))
                        ->orWhere('p.sku', ltrim($sku, '0'));
                })
                ->select($select)
                ->first();
            if ($row) {
                return (array) $row;
            }

            // Many wholesalers print internal product id as the "SKU".
            if (ctype_digit($sku)) {
                $row = \Illuminate\Support\Facades\DB::table('variations as v')
                    ->join('products as p', 'p.id', '=', 'v.product_id')
                    ->where('p.business_id', $business_id)
                    ->whereNull('v.deleted_at')
                    ->where(function ($q) use ($sku) {
                        $q->where('p.id', (int) $sku)->orWhere('v.id', (int) $sku);
                    })
                    ->select($select)
                    ->first();
                if ($row) {
                    return (array) $row;
                }
            }
        }

        if ($name !== '') {
            $row = \Illuminate\Support\Facades\DB::table('variations as v')
                ->join('products as p', 'p.id', '=', 'v.product_id')
                ->where('p.business_id', $business_id)
                ->whereNull('v.deleted_at')
                ->where('p.name', $name)
                ->select($select)
                ->first();
            if ($row) {
                return (array) $row;
            }

            $row = \Illuminate\Support\Facades\DB::table('variations as v')
                ->join('products as p', 'p.id', '=', 'v.product_id')
                ->where('p.business_id', $business_id)
                ->whereNull('v.deleted_at')
                ->where('p.name', 'like', '%'.$name.'%')
                ->select($select)
                ->limit(2)
                ->get();
            if ($row->count() === 1) {
                return (array) $row->first();
            }
        }

        return null;
    }

    protected function matchSupplier(int $business_id, ?string $name): ?int
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }

        $id = \App\Contact::where('business_id', $business_id)
            ->whereIn('type', ['supplier', 'both'])
            ->where(function ($q) use ($name) {
                $q->where('name', $name)
                    ->orWhere('supplier_business_name', $name);
            })
            ->value('id');
        if ($id) {
            return (int) $id;
        }

        $id = \App\Contact::where('business_id', $business_id)
            ->whereIn('type', ['supplier', 'both'])
            ->where(function ($q) use ($name) {
                $q->where('name', 'like', '%'.$name.'%')
                    ->orWhere('supplier_business_name', 'like', '%'.$name.'%');
            })
            ->value('id');

        return $id ? (int) $id : null;
    }
}
