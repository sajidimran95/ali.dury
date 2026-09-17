<?php

namespace Modules\Ecommerce\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Product;
use App\Variation;
use Illuminate\Http\Request;
use Modules\Ecommerce\Services\CartService;

class QuickOrderController extends Controller
{
    public function index()
    {
        return view('ecommerce::front.quick_order.index');
    }

    public function submit(Request $request, CartService $cartService)
    {
        $business_id = $request->attributes->get('ecommerce_business_id');
        $lines = $this->parseLines($request);

        if (empty($lines)) {
            return back()->with('error', 'Add at least one SKU and quantity.');
        }

        $added = 0;
        $errors = [];

        foreach ($lines as $i => $line) {
            $sku = $line['sku'];
            $qty = $line['qty'];
            if ($qty <= 0) {
                $errors[] = "Row ".($i + 1).": quantity must be greater than 0.";
                continue;
            }

            $product = Product::storefrontQuery((int) $business_id)
                ->where(function ($q) use ($sku) {
                    $q->where('sku', $sku)->orWhere('sku', strtoupper($sku));
                })
                ->with('variations')
                ->first();

            $variation = null;
            if ($product) {
                $variation = $product->variations->first();
            } else {
                $variation = Variation::where('sub_sku', $sku)
                    ->orWhere('sub_sku', strtoupper($sku))
                    ->whereHas('product', function ($q) use ($business_id) {
                        $q->where('business_id', $business_id)->active();
                    })
                    ->with('product')
                    ->first();
                $product = $variation?->product;
            }

            if (! $product || ! $variation) {
                $errors[] = "SKU \"{$sku}\" not found.";
                continue;
            }

            try {
                $cartService->add($business_id, $product->id, $variation->id, $qty);
                $added++;
            } catch (\Throwable $e) {
                $errors[] = "SKU \"{$sku}\": ".$e->getMessage();
            }
        }

        if ($added === 0) {
            return back()->withInput()->with('error', implode(' ', $errors) ?: 'No items added.');
        }

        $msg = "Added {$added} line(s) to cart.";
        if ($errors) {
            $msg .= ' Some issues: '.implode(' ', $errors);
        }

        return redirect()->route('ecommerce.cart')->with('success', $msg);
    }

    protected function parseLines(Request $request): array
    {
        $lines = [];

        $rows = $request->input('rows', []);
        if (is_array($rows)) {
            foreach ($rows as $row) {
                $sku = trim((string) ($row['sku'] ?? ''));
                $qty = (float) ($row['qty'] ?? 0);
                if ($sku !== '') {
                    $lines[] = ['sku' => $sku, 'qty' => $qty];
                }
            }
        }

        $bulk = trim((string) $request->input('bulk', ''));
        if ($bulk !== '') {
            foreach (preg_split('/\r\n|\r|\n/', $bulk) as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                if (preg_match('/^(.+?)[\s,;|\t]+([\d.]+)$/', $line, $m)) {
                    $lines[] = ['sku' => trim($m[1]), 'qty' => (float) $m[2]];
                } elseif (preg_match('/^(.+)$/', $line, $m)) {
                    $lines[] = ['sku' => trim($m[1]), 'qty' => 1];
                }
            }
        }

        return $lines;
    }
}
