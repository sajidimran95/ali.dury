<?php

namespace App\Services\Msa;

use App\Business;
use App\BusinessLocation;
use App\Category;
use App\Contact;
use App\Product;
use App\Transaction;
use App\VariationLocationDetails;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * MULTICAT™ All Tobacco (TOB) fixed-width MSA report exporter.
 * Layout follows requirements/msa report sample files (HID → BID → SID/PUR → TOT).
 */
class MsaTobReportExporter
{
    protected Business $business;

    protected BusinessLocation $location;

    protected array $options;

    protected Carbon $weekEnd;

    protected Carbon $weekStart;

    protected string $distributorId;

    protected string $stateTax;

    /** @var array<int, array> */
    protected array $bids = [];

    /** @var array<int, array> keyed by contact_id */
    protected array $sids = [];

    /** @var array<int, array> */
    protected array $purs = [];

    protected float $sumQtyShipped = 0;

    protected float $sumInventory = 0;

    protected float $sumSaleableReturns = 0;

    protected float $sumUnsaleableReturns = 0;

    protected float $sumWholesalerReturns = 0;

    public function __construct(Business $business, BusinessLocation $location, array $options = [])
    {
        $this->business = $business;
        $this->location = $location;
        $this->options = $options;

        $this->weekEnd = Carbon::parse($options['week_ending'])->startOfDay();
        // MSA weeks end Saturday; Sunday–Saturday range
        $this->weekStart = $this->weekEnd->copy()->subDays(6)->startOfDay();

        $settings = $business->common_settings ?? [];
        $this->distributorId = $this->digits(
            $options['distributor_id'] ?? ($settings['msa_distributor_id'] ?? ''),
            8
        );
        $this->stateTax = strtoupper(substr(
            $options['state_tax']
                ?? ($settings['msa_state_tax'] ?? ($location->state ?? 'TN')),
            0,
            2
        ));
    }

    public function export(): string
    {
        $this->buildBids();
        $this->buildShipments();

        $lines = [];
        $lines[] = $this->buildHid();
        foreach ($this->bids as $bid) {
            $lines[] = $this->formatBid($bid);
        }
        foreach ($this->sids as $contactId => $sid) {
            $lines[] = $this->formatSid($sid);
            foreach ($this->purs[$contactId] ?? [] as $pur) {
                $lines[] = $this->formatPur($sid, $pur);
            }
        }
        $lines[] = $this->buildTot();

        // One record per line, CRLF only — never blank lines (MSA rejects double-spaced files).
        $lines = array_values(array_filter(array_map(function ($line) {
            return rtrim(str_replace(["\r", "\n"], '', (string) $line));
        }, $lines), fn ($line) => $line !== ''));

        return implode("\r\n", $lines)."\r\n";
    }

    public function suggestedFilename(): string
    {
        $name = preg_replace('/[^A-Za-z0-9]+/', '_', strtoupper($this->location->name ?: $this->business->name));
        $name = trim($name, '_');

        return "{$name}_TOB_{$this->distributorId}_" . $this->weekEnd->format('Ymd') . '.txt';
    }

    protected function buildBids(): void
    {
        $categoryId = $this->options['category_id'] ?? null;
        $categoryIds = $this->expandCategoryIds($categoryId);

        $variationIds = $this->soldVariationIds();

        // Load all on-hand qty for this location (including zero/negative — MSA reports floor at 0).
        $stockRows = VariationLocationDetails::query()
            ->where('location_id', $this->location->id)
            ->pluck('qty_available', 'variation_id');

        $this->options['_stock_rows'] = $stockRows->all();
        $this->ensureWeekEndInventoryMaps();

        $positiveStockIds = $stockRows->filter(fn ($qty) => (float) $qty > 0)->keys();

        $allVariationIds = collect($variationIds)
            ->merge($positiveStockIds)
            ->unique()
            ->values();

        if ($allVariationIds->isEmpty() && empty($categoryIds)) {
            // Still allow export of empty skeleton with no BIDs when no activity
            $this->bids = [];

            return;
        }

        // Include stock-tracked products and any sold tobacco SKUs (even if enable_stock was off in QB).
        $products = Product::query()
            ->where('business_id', $this->business->id)
            ->when(! empty($categoryIds), function ($q) use ($categoryIds) {
                $q->where(function ($inner) use ($categoryIds) {
                    $inner->whereIn('category_id', $categoryIds)
                        ->orWhereIn('sub_category_id', $categoryIds);
                });
            })
            ->where(function ($q) use ($allVariationIds) {
                $q->where('enable_stock', 1);
                if ($allVariationIds->isNotEmpty()) {
                    $q->orWhereHas('variations', function ($vq) use ($allVariationIds) {
                        $vq->whereIn('id', $allVariationIds);
                    });
                }
            })
            ->with(['variations' => function ($q) use ($allVariationIds) {
                if ($allVariationIds->isNotEmpty()) {
                    $q->whereIn('id', $allVariationIds);
                }
            }, 'variations.product_variation'])
            ->get();

        foreach ($products as $product) {
            foreach ($product->variations as $variation) {
                if ($allVariationIds->isNotEmpty() && ! $allVariationIds->contains($variation->id)) {
                    continue;
                }

                $sku = $this->distributorSku($product, $variation);
                $upc = $this->upc($product, $variation);
                $desc = strtoupper($this->productDescription($product, $variation));
                $itemsPerUnit = $this->itemsPerSellingUnit($product);
                $msaCategory = $this->msaCategoryCode($product);
                $promoCode = trim((string) ($product->product_custom_field3 ?? ''));
                $promoDesc = trim((string) ($product->product_custom_field4 ?? ''));
                $isPromo = ($promoCode !== '' || $promoDesc !== '') ? 'Y' : 'N';
                // MSA inventory (003) = on-hand as of week ending (not "today").
                $inventory = $this->inventoryAsOfWeekEnd((int) $variation->id);

                $this->bids[$variation->id] = [
                    'variation_id' => $variation->id,
                    'upc' => $upc,
                    'sku' => $sku,
                    'description' => $desc,
                    'promo_description' => strtoupper($promoDesc),
                    'items_per_unit' => $itemsPerUnit,
                    'promo_indicator' => $isPromo,
                    'msa_category' => $msaCategory,
                    'promo_code' => $promoCode,
                    'inventory' => $inventory,
                    'wholesaler_returns' => 0.0,
                ];
                $this->sumInventory += $inventory;
            }
        }
    }

    protected function buildShipments(): void
    {
        $categoryId = $this->options['category_id'] ?? null;
        $categoryIds = $this->expandCategoryIds($categoryId);

        $sells = Transaction::query()
            ->where('business_id', $this->business->id)
            ->where('location_id', $this->location->id)
            ->whereIn('type', ['sell', 'sell_return'])
            ->where('status', 'final')
            ->whereDate('transaction_date', '>=', $this->weekStart->toDateString())
            ->whereDate('transaction_date', '<=', $this->weekEnd->toDateString())
            ->with(['contact', 'sell_lines.product', 'sell_lines.variations'])
            ->orderBy('contact_id')
            ->orderBy('transaction_date')
            ->get();

        foreach ($sells as $transaction) {
            $contact = $transaction->contact;
            if (! $contact) {
                continue;
            }

            if (! isset($this->sids[$contact->id])) {
                $this->sids[$contact->id] = $this->mapSid($contact);
                $this->purs[$contact->id] = [];
            }

            $txnDate = Carbon::parse($transaction->transaction_date)->format('Ymd');
            $isReturn = $transaction->type === 'sell_return';

            foreach ($transaction->sell_lines as $line) {
                if (! empty($categoryIds)) {
                    $product = $line->product;
                    if (! $product || (! in_array((int) $product->category_id, $categoryIds, true) && ! in_array((int) $product->sub_category_id, $categoryIds, true))) {
                        continue;
                    }
                }

                $variationId = $line->variation_id;
                if (! isset($this->bids[$variationId])) {
                    // Ensure BID exists for sold SKU even if stock is zero / not preloaded
                    $product = $line->product;
                    $variation = $line->variations;
                    if (! $product || ! $variation) {
                        continue;
                    }
                    $inventory = $this->inventoryAsOfWeekEnd((int) $variationId);
                    $this->bids[$variationId] = [
                        'variation_id' => $variationId,
                        'upc' => $this->upc($product, $variation),
                        'sku' => $this->distributorSku($product, $variation),
                        'description' => strtoupper($this->productDescription($product, $variation)),
                        'promo_description' => strtoupper(trim((string) ($product->product_custom_field4 ?? ''))),
                        'items_per_unit' => $this->itemsPerSellingUnit($product),
                        'promo_indicator' => trim((string) ($product->product_custom_field3 ?? '')) !== '' ? 'Y' : 'N',
                        'msa_category' => $this->msaCategoryCode($product),
                        'promo_code' => trim((string) ($product->product_custom_field3 ?? '')),
                        'inventory' => $inventory,
                        'wholesaler_returns' => 0.0,
                    ];
                    $this->sumInventory += $inventory;
                }

                $qty = (float) $line->quantity;
                $sku = $this->bids[$variationId]['sku'];
                $key = $txnDate . '|' . $sku;

                if (! isset($this->purs[$contact->id][$key])) {
                    $this->purs[$contact->id][$key] = [
                        'sku' => $sku,
                        'date' => $txnDate,
                        'qty_shipped' => 0.0,
                        'dollars' => 0.0,
                        'saleable_returns' => 0.0,
                        'unsaleable_returns' => 0.0,
                    ];
                }

                if ($isReturn) {
                    // Unsaleable returns (005) — negative in sample style
                    $this->purs[$contact->id][$key]['unsaleable_returns'] += $qty;
                    $this->sumUnsaleableReturns += $qty;
                } else {
                    $this->purs[$contact->id][$key]['qty_shipped'] += $qty;
                    $this->sumQtyShipped += $qty;
                    $lineDollars = (float) ($line->unit_price_inc_tax ?? $line->unit_price ?? 0) * $qty;
                    $this->purs[$contact->id][$key]['dollars'] += $lineDollars;
                }
            }
        }

        // Flatten PUR keys to list
        foreach ($this->purs as $contactId => $rows) {
            $this->purs[$contactId] = array_values($rows);
        }
    }

    /**
     * Ending inventory for the MSA week:
     * current on-hand + qty sold after week_ending - qty purchased after week_ending.
     * Floored at 0 (MSA inventory measure is not negative).
     */
    protected function inventoryAsOfWeekEnd(int $variationId): float
    {
        $this->ensureWeekEndInventoryMaps();

        $current = (float) ($this->options['_stock_rows'][$variationId] ?? 0);
        $soldAfter = (float) ($this->options['_sold_after'][$variationId] ?? 0);
        $returnedAfter = (float) ($this->options['_returned_after'][$variationId] ?? 0);
        $purchasedAfter = (float) ($this->options['_purchased_after'][$variationId] ?? 0);

        return max(0.0, $current + $soldAfter - $returnedAfter - $purchasedAfter);
    }

    protected function ensureWeekEndInventoryMaps(): void
    {
        if (isset($this->options['_inventory_maps_ready'])) {
            return;
        }

        if (! isset($this->options['_stock_rows'])) {
            $this->options['_stock_rows'] = VariationLocationDetails::query()
                ->where('location_id', $this->location->id)
                ->pluck('qty_available', 'variation_id')
                ->all();
        }

        $this->options['_sold_after'] = DB::table('transaction_sell_lines as tsl')
            ->join('transactions as t', 't.id', '=', 'tsl.transaction_id')
            ->where('t.business_id', $this->business->id)
            ->where('t.location_id', $this->location->id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->whereDate('t.transaction_date', '>', $this->weekEnd->toDateString())
            ->groupBy('tsl.variation_id')
            ->selectRaw('tsl.variation_id, SUM(tsl.quantity) as qty')
            ->pluck('qty', 'variation_id')
            ->all();

        $this->options['_returned_after'] = DB::table('transaction_sell_lines as tsl')
            ->join('transactions as t', 't.id', '=', 'tsl.transaction_id')
            ->where('t.business_id', $this->business->id)
            ->where('t.location_id', $this->location->id)
            ->where('t.type', 'sell_return')
            ->where('t.status', 'final')
            ->whereDate('t.transaction_date', '>', $this->weekEnd->toDateString())
            ->groupBy('tsl.variation_id')
            ->selectRaw('tsl.variation_id, SUM(tsl.quantity) as qty')
            ->pluck('qty', 'variation_id')
            ->all();

        $this->options['_purchased_after'] = DB::table('purchase_lines as pl')
            ->join('transactions as t', 't.id', '=', 'pl.transaction_id')
            ->where('t.business_id', $this->business->id)
            ->where('t.location_id', $this->location->id)
            ->whereIn('t.type', ['purchase', 'opening_stock'])
            ->where('t.status', 'received')
            ->whereDate('t.transaction_date', '>', $this->weekEnd->toDateString())
            ->groupBy('pl.variation_id')
            ->selectRaw('pl.variation_id, SUM(pl.quantity) as qty')
            ->pluck('qty', 'variation_id')
            ->all();

        $this->options['_inventory_maps_ready'] = true;
    }

    protected function soldVariationIds(): Collection
    {
        return DB::table('transaction_sell_lines as tsl')
            ->join('transactions as t', 't.id', '=', 'tsl.transaction_id')
            ->where('t.business_id', $this->business->id)
            ->where('t.location_id', $this->location->id)
            ->whereIn('t.type', ['sell', 'sell_return'])
            ->where('t.status', 'final')
            ->whereDate('t.transaction_date', '>=', $this->weekStart->toDateString())
            ->whereDate('t.transaction_date', '<=', $this->weekEnd->toDateString())
            ->distinct()
            ->pluck('tsl.variation_id');
    }

    protected function buildHid(): string
    {
        $settings = $this->business->common_settings ?? [];
        $user = auth()->user();
        $last = strtoupper($this->options['contact_last']
            ?? ($settings['msa_contact_last'] ?? ($user->surname ?? 'CONTACT')));
        $first = strtoupper($this->options['contact_first']
            ?? ($settings['msa_contact_first'] ?? ($user->first_name ?? 'ADMIN')));
        $phone = $this->digits(
            $this->options['contact_phone']
                ?? ($settings['msa_contact_phone'] ?? ($this->location->mobile ?? '')),
            10
        );
        $email = strtolower($this->options['contact_email']
            ?? ($settings['msa_contact_email'] ?? ($user->email ?? '')));

        $testLive = ! empty($this->options['is_test']) ? 'T' : ' ';
        $name = strtoupper($this->location->name ?: $this->business->name);
        $street = strtoupper($this->location->landmark ?? '');
        $city = strtoupper($this->location->city ?? '');
        $state = strtoupper(substr($this->location->state ?? $this->stateTax, 0, 2));
        $zip = $this->zipPlus4($this->location->zip_code ?? '');

        // Sample uses 2 BID measures (003+006) and 4 PUR measures (001,002,004,005)
        $bidMeasures = '0002';
        $sidMeasures = '0000';
        $purMeasures = '0004';

        return 'HID'
            . $this->distributorId
            . $this->lj('TOB', 4)
            . $testLive
            . 'W'
            . $this->weekEnd->format('Ymd')
            . $this->lj($name, 32)
            . $this->lj($street, 90)
            . $this->lj($city, 25)
            . $this->lj($state, 2)
            . $this->lj($zip, 9)
            . $this->lj('USA', 3)
            . $this->lj($last, 20)
            . $this->lj($first, 20)
            . $this->rj('0', 5) // dialing code reserved
            . $this->rj($phone, 10)
            . $this->rj('0', 5)
            . $this->rj('0', 10) // fax
            . $this->lj($email, 60)
            . $bidMeasures
            . $sidMeasures
            . $purMeasures
            . Carbon::now()->format('Ymd')
            . '0'; // inventory resubmission flag
    }

    protected function formatBid(array $bid): string
    {
        $promoCode = $bid['promo_indicator'] === 'Y' ? $bid['promo_code'] : '';
        $promoDesc = $bid['promo_indicator'] === 'Y' ? $bid['promo_description'] : '';

        return 'BID'
            . $this->rj($bid['upc'], 14, '0')
            . $this->rj($bid['sku'], 14, '0')
            . $this->lj($bid['description'], 50)
            . $this->lj($promoDesc, 50)
            . $this->rj((string) (int) $bid['items_per_unit'], 6, '0')
            . $bid['promo_indicator']
            . $this->lj('', 6) // NACS
            . $this->rj($bid['msa_category'], 6, '0')
            . $this->lj('', 10) // project future
            . $this->rj('0', 6) // unit size
            . $this->lj('', 10) // unit size desc
            . ' ' // shipper flag blank
            . $this->lj($promoCode, 10)
            . $this->lj('', 14) // mfr product id
            . $this->lj('', 2)
            . $this->lj('', 4)
            . $this->lj($this->stateTax, 2)
            . $this->rj('0', 16, '0') // alt upc 1
            . $this->rj('0', 16, '0') // alt upc 2
            . $this->lj('', 2)
            . $this->lj('', 4)
            . '003'
            . $this->measureValue($bid['inventory'], 11)
            . '006'
            . $this->measureValue($bid['wholesaler_returns'], 11);
    }

    protected function mapSid(Contact $contact): array
    {
        $custNo = $this->digits($contact->contact_id ?: (string) $contact->id, 8);
        $shipNo = $this->digits((string) $contact->id, 8);
        $ext = $this->digits((string) $contact->id, 8);
        $name = strtoupper($contact->supplier_business_name ?: $contact->name);
        $storeNo = $this->digits((string) ($contact->custom_field2 ?? $contact->id), 8);
        $address = strtoupper(trim(($contact->address_line_1 ?? '') . ' ' . ($contact->address_line_2 ?? '')));
        $city = strtoupper($contact->city ?? '');
        $state = strtoupper(substr($contact->state ?? $this->stateTax, 0, 2));
        $zip = $this->zipPlus4($contact->zip_code ?? '');
        $phone = $this->digits($contact->mobile ?? $contact->landline ?? '', 10);
        $cot = $this->resolveClassOfTrade($contact);
        $cashCarry = strtoupper(substr((string) ($contact->custom_field3 ?? 'N'), 0, 1));
        if (! in_array($cashCarry, ['Y', 'N'], true)) {
            $cashCarry = 'N';
        }

        return compact(
            'custNo', 'shipNo', 'ext', 'name', 'storeNo', 'address', 'city', 'state', 'zip', 'phone', 'cot', 'cashCarry'
        );
    }

    /**
     * Normalize MSA Class of Trade (RETAIL / DISTRIBUTOR / OTHER).
     */
    protected function resolveClassOfTrade(Contact $contact): string
    {
        $cot = strtoupper(trim((string) ($contact->custom_field1 ?? '')));

        $aliases = [
            'WHOLESALE' => 'DISTRIBUTOR',
            'WHOLESALER' => 'DISTRIBUTOR',
            'DISTRIBUTOR' => 'DISTRIBUTOR',
            'RETAIL' => 'RETAIL',
            'RETAILER' => 'RETAIL',
            'OTHER' => 'OTHER',
        ];

        if ($cot !== '' && isset($aliases[$cot])) {
            return $aliases[$cot];
        }

        if ($cot !== '') {
            return $cot;
        }

        $groupName = '';
        if (! empty($contact->customer_group_id)) {
            $groupName = strtoupper(trim((string) (
                \App\CustomerGroup::where('id', $contact->customer_group_id)->value('name') ?? ''
            )));
        }

        if (in_array($groupName, ['WHOLESALE', 'WHOLESALER', 'DISTRIBUTOR'], true)) {
            return 'DISTRIBUTOR';
        }

        if (in_array($groupName, ['RETAIL', 'RETAILER'], true)) {
            return 'RETAIL';
        }

        return 'RETAIL';
    }

    protected function formatSid(array $sid): string
    {
        $parentName = $sid['cashCarry'] === 'Y'
            ? strtoupper($this->location->name ?: $this->business->name)
            : $sid['name'];
        $parentAddr = $sid['cashCarry'] === 'Y'
            ? strtoupper($this->location->landmark ?? '')
            : $sid['address'];
        $parentCity = $sid['cashCarry'] === 'Y'
            ? strtoupper($this->location->city ?? '')
            : $sid['city'];
        $parentState = $sid['cashCarry'] === 'Y'
            ? strtoupper(substr($this->location->state ?? $this->stateTax, 0, 2))
            : $sid['state'];
        $parentZip = $sid['cashCarry'] === 'Y'
            ? $this->zipPlus4($this->location->zip_code ?? '')
            : $sid['zip'];
        $parentPhone = $sid['phone'];
        $independentFlag = $sid['cashCarry'] === 'Y' ? 'N' : 'Y';

        $line = 'SID'
            . $this->lj($sid['custNo'], 8)
            . $this->lj($sid['shipNo'], 8)
            . $this->lj($sid['ext'], 8)
            . $this->lj($sid['name'], 32)
            . $this->lj($sid['storeNo'], 8)
            . $this->lj($sid['address'], 90)
            . $this->lj($sid['city'], 25)
            . $this->lj($sid['state'], 2)
            . $this->lj($sid['zip'], 9)
            . $this->lj('USA', 3)
            . $this->lj($this->stateTax, 2) // option1 usually blank when using BID option2 — samples fill both TN
            . $this->lj('', 3)
            . $this->rj($sid['phone'], 10)
            . $this->lj($sid['cot'], 20)
            . $this->rj('0', 7) // TDLinx
            . $sid['cashCarry']
            . str_repeat(' ', 67)
            . $this->lj($parentName, 32)
            . $this->lj($parentAddr, 90)
            . $this->lj($parentCity, 25)
            . $this->lj($parentState, 2)
            . $this->lj($parentZip, 9)
            . $this->lj('USA', 3)
            . $this->lj('', 5)
            . $this->rj($parentPhone, 10)
            . $this->rj('0', 14, '0')
            . $independentFlag
            . str_repeat(' ', 42)
            . $this->rj('0', 11, '0');

        return substr($this->lj($line, 551), 0, 551);
    }

    protected function formatPur(array $sid, array $pur): string
    {
        // Altria STARS: keep FIXED measure slots even when dollars are zero.
        // Do NOT shift 004/005 into the 002 column. Order must be:
        //   001 qty shipped | 002 dollars (zeros OK) | 004 saleable | 005 unsaleable
        // Floor returns (006) belong on BID, not PUR.
        return 'PUR'
            . $this->lj($sid['custNo'], 8)
            . $this->lj($sid['shipNo'], 8)
            . $this->lj($sid['ext'], 8)
            . $this->rj($pur['sku'], 14, '0')
            . $this->lj('', 33)
            . $pur['date']
            . $this->lj('', 20)
            . '001'
            . $this->measureValue($pur['qty_shipped'], 11)
            . '002'
            . $this->measureValue($pur['dollars'] ?? 0, 11)
            . '004'
            . $this->measureValue(-1 * abs($pur['saleable_returns'] ?? 0), 11, true)
            . '005'
            . $this->measureValue(-1 * abs($pur['unsaleable_returns'] ?? 0), 11, true);
    }

    protected function buildTot(): string
    {
        $bidCount = count($this->bids);
        $sidCount = count($this->sids);
        $purCount = 0;
        foreach ($this->purs as $rows) {
            $purCount += count($rows);
        }

        return 'TOT'
            . $this->distributorId
            . $this->weekEnd->format('Ymd')
            . $this->rj((string) $bidCount, 9, '0')
            . $this->rj((string) $sidCount, 9, '0')
            . $this->rj((string) $purCount, 9, '0')
            . $this->lj('', 40)
            . '001'
            . $this->measureValue($this->sumQtyShipped, 15)
            . $this->lj('', 18) // optional dollars blank in sample
            . '003'
            . $this->measureValue($this->sumInventory, 15)
            . '006'
            . $this->measureValue($this->sumWholesalerReturns, 15)
            . '004'
            . $this->measureValue(-1 * abs($this->sumSaleableReturns), 15, true)
            . '005'
            . $this->measureValue(-1 * abs($this->sumUnsaleableReturns), 15, true);
    }

    protected function expandCategoryIds($categoryId): array
    {
        if (empty($categoryId)) {
            return [];
        }

        $ids = [(int) $categoryId];
        $childIds = Category::where('business_id', $this->business->id)
            ->where('parent_id', $categoryId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return array_values(array_unique(array_merge($ids, $childIds)));
    }

    protected function upc(Product $product, $variation): string
    {
        $raw = preg_replace('/\D+/', '', (string) ($variation->sub_sku ?: $product->sku));

        return substr(str_pad($raw ?: '0', 14, '0', STR_PAD_LEFT), -14);
    }

    protected function distributorSku(Product $product, $variation): string
    {
        $raw = preg_replace('/\D+/', '', (string) ($product->sku ?: $variation->sub_sku ?: $variation->id));

        return substr(str_pad($raw ?: (string) $variation->id, 14, '0', STR_PAD_LEFT), -14);
    }

    protected function productDescription(Product $product, $variation): string
    {
        $name = $product->name;
        if ($product->type === 'variable' && ! empty($variation->name) && $variation->name !== 'DUMMY') {
            $name .= ' '.$variation->name;
        }

        // Strip CR/LF — embedded newlines create blank lines MSA cannot process.
        return preg_replace('/\s+/', ' ', str_replace(["\r", "\n", "\t"], ' ', $name));
    }

    /**
     * Items per selling unit (carton=200, can/roll=5, pack, each=1).
     * Reject UPC/SKU-sized values accidentally stored in custom field 1 (caused inflated units).
     */
    protected function itemsPerSellingUnit(Product $product): int
    {
        $raw = preg_replace('/\D+/', '', (string) ($product->product_custom_field1 ?? ''));
        // Valid MSA values are small (1–250). Anything larger is almost certainly a barcode/SKU.
        if ($raw !== '' && strlen($raw) <= 3) {
            $custom = (int) $raw;
            if ($custom >= 1 && $custom <= 250) {
                return $custom;
            }
        }

        return $this->inferItemsPerSellingUnit($product);
    }

    protected function inferItemsPerSellingUnit(Product $product): int
    {
        $subName = '';
        $catName = '';
        if (! empty($product->sub_category_id)) {
            $subName = (string) (Category::where('id', $product->sub_category_id)->value('name') ?? '');
        }
        if (! empty($product->category_id)) {
            $catName = (string) (Category::where('id', $product->category_id)->value('name') ?? '');
        }

        $haystack = strtolower(trim(($product->name ?? '').' '.$subName.' '.$catName));

        if (preg_match('/\bcarton\b/', $haystack) || preg_match('/\bcigarettes?\b/', $haystack)) {
            return 200;
        }
        if (preg_match('/\b(can|roll|cans)\b/', $haystack) && preg_match('/\b(moist|snuff|cope|skoal|copenhagen)\b/', $haystack)) {
            return 5;
        }
        if (preg_match('/\b(cigarillo|little\s+cigar|filtered\s+cigar)\b/', $haystack)) {
            return 20;
        }
        if (preg_match('/\b(pod|pods|refill)\b/', $haystack)) {
            return 4;
        }
        if (preg_match('/\b(e-?cig|vape|disposable|puff|thc|hemp|delta|gumm|flower|preroll|pre-roll|wrap)\b/', $haystack)) {
            return 1;
        }
        if (preg_match('/\bcigar\b/', $haystack)) {
            return 20;
        }
        if (preg_match('/\b(ryo|pipe)\b/', $haystack)) {
            return 1;
        }

        // Safe default for unknown non-cigarette SKUs: each (not 200 cartons).
        return 1;
    }

    protected function msaCategoryCode(Product $product): string
    {
        $custom = preg_replace('/\D+/', '', (string) ($product->product_custom_field2 ?? ''));
        // Accept only known-length MSA codes (6 digits). Reject barcodes stuffed into CF2.
        if (strlen($custom) === 6 && str_starts_with($custom, '003')) {
            return $custom;
        }

        // Prefer subcategory, then parent category description "MSA category code ######"
        foreach ([$product->sub_category_id, $product->category_id] as $catId) {
            if (empty($catId)) {
                continue;
            }
            $category = Category::find($catId);
            if (! $category) {
                continue;
            }
            if (preg_match('/\b(003\d{3})\b/', (string) $category->description, $m)) {
                return $m[1];
            }
            $inferred = $this->inferMsaCategoryFromText((string) ($category->name ?? ''));
            if ($inferred !== null) {
                return $inferred;
            }
        }

        $fromName = $this->inferMsaCategoryFromText((string) ($product->name ?? ''));
        if ($fromName !== null) {
            return $fromName;
        }

        // Last resort — only for clearly tobacco-named items; otherwise e-cig/other (not cigarettes).
        return '003292';
    }

    protected function inferMsaCategoryFromText(string $text): ?string
    {
        $name = strtolower($text);

        if (preg_match('/\b(e-?cig|vape|disposable|puff|pod|juul|elf\s*bar|geek|mr\s*fog)\b/', $name)) {
            return '003292';
        }
        if (preg_match('/\b(hemp\s*wrap|blunt\s*wrap|wraps?|papers?|tubes?)\b/', $name)) {
            return '003261'; // wraps / papers family
        }
        if (preg_match('/\b(little|filtered)\b.*\bcigar|\bcigarillo\b/', $name)) {
            return '003252';
        }
        if (preg_match('/\bcigar\b/', $name)) {
            return '003251';
        }
        if (preg_match('/\b(moist|spit|snuff|chew|snus|copenhagen|skoal)\b/', $name)) {
            return '003211';
        }
        if (preg_match('/\b(ryo|roll[\s-]?your[\s-]?own|bugler)\b/', $name)) {
            return '003221';
        }
        if (preg_match('/\bpipe\s*tobacco\b/', $name)) {
            return '003241';
        }
        if (preg_match('/\bcigarette|marlboro|newport|camel|pall\s*mall|carton\b/', $name)) {
            return '003231';
        }
        // Hemp/THC/flower/gummies are not cigarettes — map to e-cig/other rather than 003231.
        if (preg_match('/\b(thc|delta|hemp|gumm|flower|preroll|pre-roll|caviar|resin|wax|mushroom|psilly)\b/', $name)) {
            return '003292';
        }

        return null;
    }

    protected function zipPlus4(?string $zip): string
    {
        $digits = preg_replace('/\D+/', '', (string) $zip);

        return str_pad(substr($digits, 0, 9), 9, '0');
    }

    protected function digits(string $value, int $len): string
    {
        $digits = preg_replace('/\D+/', '', $value);

        return substr(str_pad($digits ?: '0', $len, '0', STR_PAD_LEFT), -$len);
    }

    protected function lj(string $value, int $len): string
    {
        $value = substr($value, 0, $len);

        return str_pad($value, $len, ' ', STR_PAD_RIGHT);
    }

    protected function rj(string $value, int $len, string $pad = '0'): string
    {
        $value = substr($value, 0, $len);

        return str_pad($value, $len, $pad, STR_PAD_LEFT);
    }

    /**
     * Format measure value like samples: zero-filled; negatives as -0000000002.
     */
    protected function measureValue(float $value, int $width, bool $allowNegative = false): string
    {
        $int = (int) round($value);
        if ($allowNegative && $int < 0) {
            $body = str_pad((string) abs($int), $width - 1, '0', STR_PAD_LEFT);

            return '-' . $body;
        }

        return str_pad((string) abs($int), $width, '0', STR_PAD_LEFT);
    }
}
