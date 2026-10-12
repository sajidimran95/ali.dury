<?php

namespace Modules\Ecommerce\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ecommerce\Support\StaticCatalog;
use Modules\Ecommerce\Support\StaticCart;

class QuickOrderController extends Controller
{
    public function index()
    {
        $cart_totals = StaticCart::totals();
        $exampleSku = optional(StaticCatalog::all()->first())->sku ?: 'EDEMO-01';

        return view('ecommerce::front.quick_order.index', compact('cart_totals', 'exampleSku'));
    }

    public function suggest(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $items = StaticCatalog::search($q, 12)->map(fn ($p) => [
            'sku' => $p->sku,
            'name' => $p->name,
            'product_id' => $p->id,
            'variation_id' => $p->variations->first()->id,
        ])->values();

        return response()->json(['items' => $items]);
    }

    public function submit(Request $request)
    {
        $lines = $this->parseLines($request);
        if (empty($lines)) {
            return back()->withInput()->with('error', 'Add at least one SKU and quantity.');
        }

        $added = 0;
        $errors = [];
        foreach ($lines as $i => $line) {
            if ($line['qty'] <= 0) {
                $errors[] = 'Row '.($i + 1).': quantity must be greater than 0.';
                continue;
            }
            $hit = StaticCatalog::find($line['sku'])
                ?: StaticCatalog::search($line['sku'], 2)->first();
            if (! $hit || (StaticCatalog::search($line['sku'], 2)->count() > 1 && ! StaticCatalog::find($line['sku']))) {
                $exact = StaticCatalog::all()->first(fn ($p) => strcasecmp($p->sku, $line['sku']) === 0);
                $hit = $exact ?: (StaticCatalog::search($line['sku'], 1)->count() === 1 ? StaticCatalog::search($line['sku'], 1)->first() : null);
            }
            if (! $hit) {
                $errors[] = 'SKU "'.$line['sku'].'" not found.';
                continue;
            }
            StaticCart::add((int) $hit->id, (float) $line['qty']);
            $added++;
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
        $merged = [];
        $push = function (string $sku, float $qty) use (&$merged) {
            $key = strtolower(preg_replace('/[\s\-]+/', '', trim($sku)) ?? '');
            if ($key === '') {
                return;
            }
            if (! isset($merged[$key])) {
                $merged[$key] = ['sku' => trim($sku), 'qty' => 0.0];
            }
            $merged[$key]['qty'] += $qty;
        };

        foreach ((array) $request->input('rows', []) as $row) {
            $sku = trim((string) ($row['sku'] ?? ''));
            if ($sku !== '') {
                $push($sku, (float) ($row['qty'] ?? 0));
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
                    $push(trim($m[1]), (float) $m[2]);
                } else {
                    $push($line, 1);
                }
            }
        }

        return array_values($merged);
    }
}
