<?php

namespace Modules\AiAssistance\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\AiAssistance\Entities\AiAssistanceSetting;

class InvoiceExtractionService
{
    protected const SYSTEM_PROMPT = <<<'PROMPT'
You are reading a photo, scan, or PDF of a vendor purchase invoice / bill for a retail or wholesale POS system.
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
- "invoice_date" must be formatted as YYYY-MM-DD if you can determine it, else null.
- "lines" must contain one entry per distinct product/item row on the invoice. If the file has multiple pages, include line items from every page.
- "quantity" and "unit_price" must be plain numbers only (no currency symbols, no thousands separators).
- If unit_price is not printed but line total and quantity are, compute unit_price = line_total / quantity.
- If a SKU / item code / barcode is printed next to a line, put it in "sku", else null.
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
        $upload = Http::withToken($api_key)
            ->timeout(60)
            ->attach('file', (string) file_get_contents($file->getRealPath()), $file->getClientOriginalName() ?: 'invoice.pdf')
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
}
