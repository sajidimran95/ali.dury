<?php

namespace App\Http\Controllers\Api\Mobile\Concerns;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * JSON envelope shared by the native-app API:
 *   success: { "success": true,  "message": string|null, "data": mixed, "meta"?: {...} }
 *   error:   { "success": false, "message": string,      "errors": object|null }
 */
trait MobileApiResponses
{
    protected function ok($data = null, ?string $message = null, int $status = 200, array $extra = []): JsonResponse
    {
        return response()->json(array_merge([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $extra), $status);
    }

    protected function fail(string $message, int $status = 422, $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }

    protected function paginated(LengthAwarePaginator $page, callable $map, array $extraData = [], ?string $message = null): JsonResponse
    {
        $items = collect($page->items())->map($map)->values();

        return $this->ok(array_merge($extraData, ['items' => $items]), $message, 200, [
            'meta' => $this->pageMeta($page),
        ]);
    }

    protected function pageMeta(LengthAwarePaginator $page): array
    {
        return [
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
            'per_page' => $page->perPage(),
            'total' => $page->total(),
            'from' => $page->firstItem(),
            'to' => $page->lastItem(),
            'has_more' => $page->hasMorePages(),
        ];
    }

    /**
     * Data passed to the Blade view by a PWA controller action.
     */
    protected function viewData($response): array
    {
        if ($response instanceof View) {
            return $response->getData();
        }
        if ($response instanceof Response && $response->getOriginalContent() instanceof View) {
            return $response->getOriginalContent()->getData();
        }

        return [];
    }

    protected function isView($response, ?string $name = null): bool
    {
        $view = $response instanceof View ? $response
            : (($response instanceof Response && $response->getOriginalContent() instanceof View) ? $response->getOriginalContent() : null);

        return $view !== null && ($name === null || $view->name() === $name);
    }

    /**
     * Outcome of a PWA action that answered with redirect()->with('status', [...]).
     *
     * @return array{success: bool, message: string|null, errors: array|null}
     */
    protected function redirectOutcome(Request $request, $response): array
    {
        $status = $request->session()->get('status');
        $errorBag = $request->session()->get('errors');
        $errors = $errorBag && method_exists($errorBag, 'getBag') ? $errorBag->getBag('default')->toArray() : null;
        $flashError = $request->session()->get('error');
        $flashSuccess = $request->session()->get('success');

        if (is_array($status)) {
            return [
                'success' => ! empty($status['success']),
                'message' => $status['msg'] ?? null,
                'errors' => $errors ?: null,
            ];
        }
        if (! empty($errors)) {
            return ['success' => false, 'message' => collect($errors)->flatten()->first(), 'errors' => $errors];
        }
        if (! empty($flashError)) {
            return ['success' => false, 'message' => (string) $flashError, 'errors' => null];
        }

        return [
            'success' => $response instanceof RedirectResponse || $response instanceof JsonResponse,
            'message' => $flashSuccess ? (string) $flashSuccess : null,
            'errors' => null,
        ];
    }

    protected function dt($value, string $format = 'Y-m-d H:i:s'): ?string
    {
        if (empty($value)) {
            return null;
        }
        try {
            return Carbon::parse($value)->format($format);
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function num($value): float
    {
        return round((float) ($value ?? 0), 4);
    }

    /**
     * Standalone HTML page for an invoice/receipt (shown in a native WebView or printed).
     */
    protected function invoiceDocument(string $receiptHtml, string $title): string
    {
        $title = e($title);

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
<title>{$title}</title>
<style>
  * { box-sizing: border-box; }
  body { margin: 0; padding: 12px; background: #fff; color: #0b1220; font-family: system-ui, -apple-system, sans-serif; }
  .inv-wrap { max-width: 900px; margin: 0 auto; }
  img { max-width: 100%; height: auto; }
  table { max-width: 100%; }
  @media print { body { padding: 0; } }
</style>
</head>
<body><div class="inv-wrap">{$receiptHtml}</div></body>
</html>
HTML;
    }

    /**
     * Return invoice as text/html (default), JSON {html} (?format=json) or PDF (?format=pdf).
     */
    protected function invoiceResponse(Request $request, string $receiptHtml, string $title)
    {
        $doc = $this->invoiceDocument($receiptHtml, $title);
        $format = strtolower((string) $request->query('format', 'html'));
        $fileName = preg_replace('/[^A-Za-z0-9_\-]+/', '_', $title ?: 'invoice').'.pdf';

        if ($format === 'json') {
            return $this->ok(['title' => $title, 'html' => $doc]);
        }

        if ($format === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($doc)->setPaper('letter');

            return $pdf->download($fileName);
        }

        return response($doc, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
