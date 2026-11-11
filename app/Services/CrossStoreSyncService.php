<?php

namespace App\Services;

use App\Brands;
use App\Category;
use App\CrossStoreStockLog;
use App\Product;
use App\ProductVariation;
use App\SellingPriceGroup;
use App\Unit;
use App\User;
use App\Variation;
use App\VariationLocationDetails;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CrossStoreSyncService
{
    /** Prevent echo loops when applying an inbound sync. */
    protected static bool $receiving = false;

    public static function isReceiving(): bool
    {
        return self::$receiving;
    }

    public function enabled(): bool
    {
        return (bool) config('cross_store_sync.enabled')
            && config('cross_store_sync.peer_url')
            && config('cross_store_sync.token');
    }

    public function pushProductCreated(int $productId): void
    {
        if (! $this->enabled() || self::$receiving) {
            return;
        }

        $product = Product::with(['unit', 'brand', 'category', 'variations.product_variation'])
            ->find($productId);

        if (! $product || empty(trim((string) $product->sku))) {
            return;
        }

        $payload = [
            'sku' => $product->sku,
            'name' => $product->name,
            'type' => $product->type,
            'barcode_type' => $product->barcode_type,
            'enable_stock' => (int) $product->enable_stock,
            'not_for_selling' => (int) $product->not_for_selling,
            'tax_type' => $product->tax_type,
            'weight' => $product->weight,
            'product_description' => $product->product_description,
            'alert_quantity' => $product->alert_quantity,
            'unit' => $product->unit ? [
                'actual_name' => $product->unit->actual_name,
                'short_name' => $product->unit->short_name,
                'allow_decimal' => (int) $product->unit->allow_decimal,
            ] : null,
            'brand_name' => $product->brand->name ?? null,
            'category_name' => $product->category->name ?? null,
            'variations' => $product->variations->map(function (Variation $variation) {
                return [
                    'sub_sku' => $variation->sub_sku,
                    'name' => $variation->name,
                    'product_variation_name' => optional($variation->product_variation)->name ?: 'DUMMY',
                    'is_dummy' => (int) (optional($variation->product_variation)->is_dummy ?? 1),
                    'default_purchase_price' => $variation->default_purchase_price,
                    'dpp_inc_tax' => $variation->dpp_inc_tax,
                    'profit_percent' => $variation->profit_percent,
                    'default_sell_price' => $variation->default_sell_price,
                    'sell_price_inc_tax' => $variation->sell_price_inc_tax,
                ];
            })->values()->all(),
        ];

        $this->postToPeer('/api/cross-store/product', $payload);
    }

    public function pushStockDelta(int $productId, int $variationId, float $qtyDelta): void
    {
        if (! $this->enabled() || self::$receiving || $qtyDelta == 0.0) {
            return;
        }

        $product = Product::withTrashed()->find($productId);
        $variation = Variation::withTrashed()->find($variationId);

        if (! $product || ! $variation || empty(trim((string) $product->sku))) {
            return;
        }

        if ((int) $product->enable_stock !== 1) {
            return;
        }

        $this->postToPeer('/api/cross-store/stock', [
            'sku' => $product->sku,
            'sub_sku' => $variation->sub_sku,
            'qty_delta' => $qtyDelta,
            'source_site' => (string) config('cross_store_sync.site_name', config('app.name')),
        ]);
    }

    public function receiveProduct(array $payload): array
    {
        self::$receiving = true;

        try {
            $businessId = (int) config('cross_store_sync.business_id');
            $locationId = (int) config('cross_store_sync.location_id');
            $sku = trim((string) ($payload['sku'] ?? ''));

            if ($sku === '') {
                return ['success' => false, 'message' => 'SKU is required.'];
            }

            $existing = Product::where('business_id', $businessId)
                ->where('sku', $sku)
                ->first();

            if ($existing) {
                $existing->product_locations()->syncWithoutDetaching([$locationId]);

                return [
                    'success' => true,
                    'message' => 'Product already exists; sale price left unchanged.',
                    'product_id' => $existing->id,
                ];
            }

            DB::beginTransaction();

            $unitId = $this->resolveUnitId($businessId, $payload['unit'] ?? null);
            $brandId = $this->resolveBrandId($businessId, $payload['brand_name'] ?? null);
            $categoryId = $this->resolveCategoryId($businessId, $payload['category_name'] ?? null);
            $createdBy = $this->resolveCreatedBy($businessId);

            $product = Product::create([
                'name' => $payload['name'] ?? $sku,
                'business_id' => $businessId,
                'type' => $payload['type'] ?? 'single',
                'unit_id' => $unitId,
                'brand_id' => $brandId,
                'category_id' => $categoryId,
                'tax_type' => $payload['tax_type'] ?? 'exclusive',
                'barcode_type' => $payload['barcode_type'] ?? 'C128',
                'sku' => $sku,
                'enable_stock' => (int) ($payload['enable_stock'] ?? 1),
                'not_for_selling' => (int) ($payload['not_for_selling'] ?? 0),
                'alert_quantity' => $payload['alert_quantity'] ?? null,
                'weight' => $payload['weight'] ?? null,
                'product_description' => $payload['product_description'] ?? null,
                'created_by' => $createdBy,
            ]);

            $product->product_locations()->sync([$locationId]);

            $variations = $payload['variations'] ?? [];
            if (empty($variations)) {
                $variations = [[
                    'sub_sku' => $sku,
                    'name' => 'DUMMY',
                    'product_variation_name' => 'DUMMY',
                    'is_dummy' => 1,
                    'default_purchase_price' => 0,
                    'dpp_inc_tax' => 0,
                    'profit_percent' => 0,
                    'default_sell_price' => 0,
                    'sell_price_inc_tax' => 0,
                ]];
            }

            $grouped = [];
            foreach ($variations as $row) {
                $pvName = $row['product_variation_name'] ?? 'DUMMY';
                $grouped[$pvName][] = $row;
            }

            foreach ($grouped as $pvName => $rows) {
                $isDummy = (int) ($rows[0]['is_dummy'] ?? ($pvName === 'DUMMY' ? 1 : 0));
                $productVariation = ProductVariation::create([
                    'name' => $pvName,
                    'product_id' => $product->id,
                    'is_dummy' => $isDummy,
                ]);

                foreach ($rows as $row) {
                    $variation = $productVariation->variations()->create([
                        'name' => $row['name'] ?? $pvName,
                        'product_id' => $product->id,
                        'sub_sku' => $row['sub_sku'] ?? $sku,
                        'default_purchase_price' => $row['default_purchase_price'] ?? 0,
                        'dpp_inc_tax' => $row['dpp_inc_tax'] ?? 0,
                        'profit_percent' => $row['profit_percent'] ?? 0,
                        'default_sell_price' => $row['default_sell_price'] ?? 0,
                        'sell_price_inc_tax' => $row['sell_price_inc_tax'] ?? 0,
                    ]);

                    if (class_exists(SellingPriceGroup::class) && method_exists(SellingPriceGroup::class, 'ensureVariationInAllGroups')) {
                        SellingPriceGroup::ensureVariationInAllGroups($businessId, $variation);
                    }
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Product created on peer.',
                'product_id' => $product->id,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Cross-store receiveProduct failed: '.$e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        } finally {
            self::$receiving = false;
        }
    }

    public function receiveStock(array $payload): array
    {
        self::$receiving = true;

        try {
            $businessId = (int) config('cross_store_sync.business_id');
            $locationId = (int) config('cross_store_sync.location_id');
            $sku = trim((string) ($payload['sku'] ?? ''));
            $subSku = trim((string) ($payload['sub_sku'] ?? ''));
            $qtyDelta = (float) ($payload['qty_delta'] ?? 0);

            if ($sku === '' || $subSku === '' || $qtyDelta == 0.0) {
                return ['success' => false, 'message' => 'sku, sub_sku and non-zero qty_delta are required.'];
            }

            $product = Product::where('business_id', $businessId)
                ->where('sku', $sku)
                ->first();

            if (! $product) {
                return ['success' => false, 'message' => 'Product not found for SKU '.$sku];
            }

            $variation = Variation::where('product_id', $product->id)
                ->where('sub_sku', $subSku)
                ->first();

            if (! $variation) {
                return ['success' => false, 'message' => 'Variation not found for sub_sku '.$subSku];
            }

            if ((int) $product->enable_stock !== 1) {
                return ['success' => true, 'message' => 'Stock disabled; ignored.'];
            }

            $details = VariationLocationDetails::where('variation_id', $variation->id)
                ->where('product_id', $product->id)
                ->where('location_id', $locationId)
                ->first();

            if (empty($details)) {
                $details = VariationLocationDetails::create([
                    'product_id' => $product->id,
                    'product_variation_id' => $variation->product_variation_id,
                    'variation_id' => $variation->id,
                    'location_id' => $locationId,
                    'qty_available' => 0,
                ]);
            }

            $details->qty_available = (float) $details->qty_available + $qtyDelta;
            $details->save();

            $this->logStockChange([
                'business_id' => $businessId,
                'product_id' => $product->id,
                'variation_id' => $variation->id,
                'sku' => $sku,
                'sub_sku' => $subSku,
                'qty_delta' => $qtyDelta,
                'qty_after' => (float) $details->qty_available,
                'source_site' => trim((string) ($payload['source_site'] ?? 'Peer store')) ?: 'Peer store',
                'event' => 'delta',
            ]);

            return [
                'success' => true,
                'message' => 'Stock updated.',
                'qty_available' => $details->qty_available,
            ];
        } catch (\Throwable $e) {
            Log::error('Cross-store receiveStock failed: '.$e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        } finally {
            self::$receiving = false;
        }
    }

    /**
     * Local stock map keyed by "sku|sub_sku".
     */
    public function localStockMap(): array
    {
        $businessId = (int) config('cross_store_sync.business_id');
        $locationId = (int) config('cross_store_sync.location_id');

        $rows = DB::table('products as p')
            ->join('variations as v', function ($join) {
                $join->on('v.product_id', '=', 'p.id')->whereNull('v.deleted_at');
            })
            ->leftJoin('variation_location_details as vld', function ($join) use ($locationId) {
                $join->on('vld.variation_id', '=', 'v.id')
                    ->where('vld.location_id', '=', $locationId);
            })
            ->where('p.business_id', $businessId)
            ->whereNull('p.deleted_at')
            ->where('p.enable_stock', 1)
            ->whereRaw("TRIM(p.sku) <> ''")
            ->select([
                'p.sku',
                'v.sub_sku',
                DB::raw('COALESCE(vld.qty_available, 0) as qty'),
            ])
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $map[$row->sku.'|'.$row->sub_sku] = [
                'sku' => $row->sku,
                'sub_sku' => $row->sub_sku,
                'qty' => round((float) $row->qty, 4),
            ];
        }

        return $map;
    }

    public function receiveStockAbsolute(array $payload): array
    {
        self::$receiving = true;

        try {
            $businessId = (int) config('cross_store_sync.business_id');
            $locationId = (int) config('cross_store_sync.location_id');
            $sku = trim((string) ($payload['sku'] ?? ''));
            $subSku = trim((string) ($payload['sub_sku'] ?? ''));

            if ($sku === '' || $subSku === '' || ! array_key_exists('qty', $payload)) {
                return ['success' => false, 'message' => 'sku, sub_sku and qty are required.'];
            }

            $qty = round((float) $payload['qty'], 4);

            $product = Product::where('business_id', $businessId)->where('sku', $sku)->first();
            if (! $product) {
                return ['success' => false, 'message' => 'Product not found for SKU '.$sku];
            }

            $variation = Variation::where('product_id', $product->id)->where('sub_sku', $subSku)->first();
            if (! $variation) {
                return ['success' => false, 'message' => 'Variation not found for sub_sku '.$subSku];
            }

            $details = VariationLocationDetails::firstOrCreate(
                [
                    'variation_id' => $variation->id,
                    'product_id' => $product->id,
                    'location_id' => $locationId,
                ],
                [
                    'product_variation_id' => $variation->product_variation_id,
                    'qty_available' => 0,
                ]
            );

            $before = (float) $details->qty_available;
            $delta = round($qty - $before, 4);
            $details->qty_available = $qty;
            $details->save();

            if ($delta != 0.0) {
                $this->logStockChange([
                    'business_id' => $businessId,
                    'product_id' => $product->id,
                    'variation_id' => $variation->id,
                    'sku' => $sku,
                    'sub_sku' => $subSku,
                    'qty_delta' => $delta,
                    'qty_after' => $qty,
                    'source_site' => trim((string) ($payload['source_site'] ?? 'Peer store')) ?: 'Peer store',
                    'event' => 'absolute',
                ]);
            }

            return ['success' => true, 'message' => 'Absolute stock set.', 'qty_available' => $qty];
        } catch (\Throwable $e) {
            Log::error('Cross-store receiveStockAbsolute failed: '.$e->getMessage());

            return ['success' => false, 'message' => $e->getMessage()];
        } finally {
            self::$receiving = false;
        }
    }

    /**
     * Rows for product stock history UI (where stock change came from).
     */
    public function historyRowsForVariation(int $variationId): array
    {
        $logs = CrossStoreStockLog::where('variation_id', $variationId)
            ->orderBy('created_at', 'desc')
            ->limit(500)
            ->get();

        $rows = [];
        foreach ($logs as $log) {
            $rows[] = [
                'date' => $log->created_at?->toDateTimeString(),
                'transaction_id' => null,
                'contact_name' => $log->source_site,
                'supplier_business_name' => null,
                'quantity_change' => (float) $log->qty_delta,
                'stock' => $log->qty_after,
                'type' => 'cross_store_sync',
                'type_label' => 'Stock sync (from '.$log->source_site.')',
                'ref_no' => $log->event === 'absolute' ? 'match' : 'sync',
                'stock_in_second_unit' => 0,
            ];
        }

        return $rows;
    }

    protected function logStockChange(array $data): void
    {
        try {
            CrossStoreStockLog::create($data);
        } catch (\Throwable $e) {
            Log::warning('Cross-store stock log failed: '.$e->getMessage());
        }
    }

    public function receiveStockAbsoluteBatch(array $items): array
    {
        $fixed = 0;
        $failed = 0;
        foreach ($items as $item) {
            $result = $this->receiveStockAbsolute(is_array($item) ? $item : []);
            if ($result['success'] ?? false) {
                $fixed++;
            } else {
                $failed++;
            }
        }

        return [
            'success' => $failed === 0,
            'message' => "Absolute batch done. fixed={$fixed}, failed={$failed}",
            'fixed' => $fixed,
            'failed' => $failed,
        ];
    }

    /**
     * Lightweight match status for navbar (cached briefly).
     */
    public function stockMatchStatus(bool $fresh = false): array
    {
        if (! $this->enabled()) {
            return [
                'success' => false,
                'enabled' => false,
                'matched' => false,
                'mismatches' => 0,
                'common' => 0,
                'message' => 'Sync disabled',
            ];
        }

        $cacheKey = 'cross_store_stock_match_status';
        if (! $fresh && cache()->has($cacheKey)) {
            return cache()->get($cacheKey);
        }

        $local = $this->localStockMap();
        $peerResponse = $this->requestPeer('GET', '/api/cross-store/stock-map');
        if (! $peerResponse || ! ($peerResponse['success'] ?? false)) {
            $status = [
                'success' => false,
                'enabled' => true,
                'matched' => false,
                'mismatches' => 0,
                'common' => 0,
                'message' => 'Peer unreachable',
            ];
            cache()->put($cacheKey, $status, now()->addSeconds(30));

            return $status;
        }

        $peer = $peerResponse['stocks'] ?? [];
        $common = 0;
        $mismatches = 0;
        foreach ($local as $key => $row) {
            if (! isset($peer[$key])) {
                continue;
            }
            $common++;
            if (abs($row['qty'] - (float) $peer[$key]) > 0.0001) {
                $mismatches++;
            }
        }

        $status = [
            'success' => true,
            'enabled' => true,
            'matched' => $mismatches === 0,
            'mismatches' => $mismatches,
            'common' => $common,
            'message' => $mismatches === 0
                ? 'All stock matched'
                : $mismatches.' stock mismatch',
        ];
        cache()->put($cacheKey, $status, now()->addSeconds(45));

        return $status;
    }

    /**
     * Match mismatched stock with peer.
     * Master pushes its qty to peer. Child pulls master qty onto itself.
     */
    public function reconcileStock(): array
    {
        if (! $this->enabled()) {
            return ['success' => false, 'message' => 'Cross-store sync is disabled.', 'fixed' => 0];
        }

        if (! config('cross_store_sync.reconcile_enabled', true)) {
            return ['success' => false, 'message' => 'Reconcile is disabled.', 'fixed' => 0];
        }

        $local = $this->localStockMap();
        $peerResponse = $this->requestPeer('GET', '/api/cross-store/stock-map');
        if (! $peerResponse || ! ($peerResponse['success'] ?? false)) {
            return [
                'success' => false,
                'message' => 'Could not fetch peer stock map.',
                'fixed' => 0,
                'peer' => $peerResponse,
            ];
        }

        $peer = $peerResponse['stocks'] ?? [];
        $isMaster = (bool) config('cross_store_sync.is_master');
        $mismatches = [];

        if ($isMaster) {
            foreach ($local as $key => $row) {
                if (! isset($peer[$key])) {
                    continue;
                }
                if (abs($row['qty'] - (float) $peer[$key]) > 0.0001) {
                    $mismatches[] = [
                        'sku' => $row['sku'],
                        'sub_sku' => $row['sub_sku'],
                        'qty' => $row['qty'],
                    ];
                }
            }

            if (empty($mismatches)) {
                cache()->forget('cross_store_stock_match_status');

                return ['success' => true, 'message' => 'Already matched.', 'fixed' => 0];
            }

            $result = $this->requestPeer('POST', '/api/cross-store/stock-absolute-batch', [
                'source_site' => (string) config('cross_store_sync.site_name', config('app.name')),
                'items' => $mismatches,
            ]);
            cache()->forget('cross_store_stock_match_status');

            return [
                'success' => (bool) ($result['success'] ?? false),
                'message' => $result['message'] ?? 'Reconcile pushed to peer.',
                'fixed' => count($mismatches),
                'direction' => 'master_to_peer',
            ];
        }

        // Child: adopt master (peer) qty for mismatches
        $peerSite = (string) config('cross_store_sync.peer_site_name', 'Peer store');
        foreach ($peer as $key => $peerQty) {
            if (! isset($local[$key])) {
                continue;
            }
            if (abs($local[$key]['qty'] - (float) $peerQty) > 0.0001) {
                [$sku, $subSku] = explode('|', $key, 2);
                $mismatches[] = [
                    'sku' => $sku,
                    'sub_sku' => $subSku,
                    'qty' => round((float) $peerQty, 4),
                    'source_site' => $peerSite,
                ];
            }
        }

        if (empty($mismatches)) {
            cache()->forget('cross_store_stock_match_status');

            return ['success' => true, 'message' => 'Already matched.', 'fixed' => 0];
        }

        $result = $this->receiveStockAbsoluteBatch($mismatches);
        cache()->forget('cross_store_stock_match_status');

        return [
            'success' => (bool) ($result['success'] ?? false),
            'message' => $result['message'] ?? 'Reconcile pulled from master.',
            'fixed' => (int) ($result['fixed'] ?? count($mismatches)),
            'direction' => 'peer_to_local',
        ];
    }

    protected function requestPeer(string $method, string $path, array $payload = []): ?array
    {
        try {
            $url = config('cross_store_sync.peer_url').$path;
            $pending = Http::timeout(max(30, (int) config('cross_store_sync.timeout', 8)))
                ->withHeaders([
                    'X-Cross-Store-Token' => config('cross_store_sync.token'),
                    'Accept' => 'application/json',
                ])
                ->asJson();

            if (config('app.env') !== 'production') {
                $pending = $pending->withoutVerifying();
            }

            $response = strtoupper($method) === 'GET'
                ? $pending->get($url)
                : $pending->post($url, $payload);

            return $response->json();
        } catch (\Throwable $e) {
            Log::warning('Cross-store peer request failed: '.$e->getMessage(), [
                'path' => $path,
            ]);

            return null;
        }
    }

    protected function postToPeer(string $path, array $payload): void
    {
        $response = $this->requestPeer('POST', $path, $payload);
        if ($response === null) {
            return;
        }
        if (! ($response['success'] ?? true)) {
            Log::warning('Cross-store sync peer error', [
                'path' => $path,
                'body' => $response,
            ]);
        }
    }

    protected function resolveUnitId(int $businessId, ?array $unit): int
    {
        $actual = trim((string) ($unit['actual_name'] ?? 'Pieces'));
        $short = trim((string) ($unit['short_name'] ?? 'Pc'));

        $existing = Unit::where('business_id', $businessId)
            ->whereRaw('LOWER(actual_name) = ?', [strtolower($actual)])
            ->first();

        if ($existing) {
            return (int) $existing->id;
        }

        $created = Unit::create([
            'business_id' => $businessId,
            'actual_name' => $actual !== '' ? $actual : 'Pieces',
            'short_name' => $short !== '' ? $short : 'Pc',
            'allow_decimal' => (int) ($unit['allow_decimal'] ?? 0),
            'created_by' => $this->resolveCreatedBy($businessId),
        ]);

        return (int) $created->id;
    }

    protected function resolveBrandId(int $businessId, ?string $name): ?int
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }

        $existing = Brands::where('business_id', $businessId)
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->first();

        if ($existing) {
            return (int) $existing->id;
        }

        $created = Brands::create([
            'business_id' => $businessId,
            'name' => $name,
            'created_by' => $this->resolveCreatedBy($businessId),
        ]);

        return (int) $created->id;
    }

    protected function resolveCategoryId(int $businessId, ?string $name): ?int
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }

        $existing = Category::where('business_id', $businessId)
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->where(function ($q) {
                $q->whereNull('category_type')->orWhere('category_type', 'product');
            })
            ->first();

        if ($existing) {
            return (int) $existing->id;
        }

        $created = Category::create([
            'name' => $name,
            'business_id' => $businessId,
            'short_code' => null,
            'parent_id' => 0,
            'created_by' => $this->resolveCreatedBy($businessId),
            'category_type' => 'product',
        ]);

        return (int) $created->id;
    }

    protected function resolveCreatedBy(int $businessId): int
    {
        $userId = User::where('business_id', $businessId)->value('id');

        return (int) ($userId ?: 1);
    }
}
