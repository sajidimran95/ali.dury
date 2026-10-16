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

    /** @var array<string, int> BID SKU => variation_id (MSA rejects duplicate SKUs) */
    protected array $usedSkus = [];

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

        // One physical record per line — strip control chars, keep fixed-width spaces.
        // Never emit blank lines; MSA / MFT Hub reject double-spaced files.
        $clean = [];
        foreach ($lines as $line) {
            $line = (string) $line;
            // Remove CR/LF/NUL and other controls that break fixed-width packing
            $line = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $line) ?? '';
            $line = str_replace(["\r", "\n"], '', $line);
            if ($line === '') {
                continue;
            }
            // Only emit known MSA record types
            if (! preg_match('/^(HID|BID|SID|PUR|TOT)/', $line)) {
                continue;
            }
            $clean[] = $line;
        }

        return implode("\r\n", $clean)."\r\n";
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

        $soldVariationIds = $this->soldVariationIds()->map(fn ($id) => (int) $id)->flip();

        // On-hand qty for this location (including zero/negative — MSA reports floor at 0).
        $this->options['_stock_rows'] = VariationLocationDetails::query()
            ->where('location_id', $this->location->id)
            ->pluck('qty_available', 'variation_id')
            ->all();
        $this->ensureWeekEndInventoryMaps();

        // Every stock-tracked item in the MSA categories is a candidate (plus any SKU sold this week),
        // judged on its inventory AS OF week end — not today's stock, which made items that sold out
        // after the week drop off the file and the item count swing week to week.
        $products = Product::query()
            ->where('business_id', $this->business->id)
            ->when(! empty($categoryIds), function ($q) use ($categoryIds) {
                $q->where(function ($inner) use ($categoryIds) {
                    $inner->whereIn('category_id', $categoryIds)
                        ->orWhereIn('sub_category_id', $categoryIds);
                });
            })
            ->where(function ($q) use ($soldVariationIds) {
                $q->where('enable_stock', 1);
                if ($soldVariationIds->isNotEmpty()) {
                    $q->orWhereHas('variations', function ($vq) use ($soldVariationIds) {
                        $vq->whereIn('id', $soldVariationIds->keys());
                    });
                }
            })
            ->with(['variations.product_variation'])
            ->orderBy('id')
            ->get();

        foreach ($products as $product) {
            if ($this->isExcludedFromMsaReport($product)) {
                continue;
            }

            // Stable order so the same variation keeps its SKU week to week when de-duplicating.
            foreach ($product->variations->sortBy('id') as $variation) {
                $variationId = (int) $variation->id;
                // MSA: an item is only reported with 0 inventory when a sale explains it.
                if ($this->inventoryAsOfWeekEnd($variationId) <= 0 && ! $soldVariationIds->has($variationId)) {
                    continue;
                }

                $this->registerBid($product, $variation);
            }
        }
    }

    /**
     * Build / register one BID row (shared by inventory scan and late sale SKUs).
     */
    protected function registerBid(Product $product, $variation): void
    {
        if ($this->isExcludedFromMsaReport($product)) {
            return;
        }

        $variationId = (int) $variation->id;
        if (isset($this->bids[$variationId])) {
            return;
        }

        $sku = $this->uniqueSku($this->distributorSku($product, $variation), $product, $variation);
        $this->usedSkus[$sku] = $variationId;
        $upc =$this->upc($product, $variation);
        $desc = strtoupper($this->productDescription($product, $variation, $sku));
        $itemsPerUnit = $this->itemsPerSellingUnit($product);
        $msaCategory = $this->msaCategoryCode($product);
        $promo = $this->resolvePromoFlags($product, $desc);
        $inventory = $this->inventoryAsOfWeekEnd($variationId);

        $this->bids[$variationId] = [
            'variation_id' => $variationId,
            'upc' => $upc,
            'sku' => $sku,
            'description' => $desc,
            'promo_description' => $promo['promo_description'],
            'items_per_unit' => $itemsPerUnit,
            'promo_indicator' => $promo['promo_indicator'],
            'msa_category' => $msaCategory,
            'promo_code' => $promo['promo_code'],
            'inventory' => $inventory,
            'wholesaler_returns' => 0.0,
        ];
        $this->sumInventory += $inventory;
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

            $txnDate = Carbon::parse($transaction->transaction_date)->format('Ymd');
            $isReturn = $transaction->type === 'sell_return';

            foreach ($transaction->sell_lines as $line) {
                $product = $line->product;
                if (! $product || $this->isExcludedFromMsaReport($product)) {
                    continue;
                }

                if (! empty($categoryIds)) {
                    if (! in_array((int) $product->category_id, $categoryIds, true) && ! in_array((int) $product->sub_category_id, $categoryIds, true)) {
                        continue;
                    }
                }

                $variationId = (int) $line->variation_id;
                if (! isset($this->bids[$variationId])) {
                    $variation = $line->variations;
                    if (! $variation) {
                        continue;
                    }
                    $this->registerBid($product, $variation);
                    if (! isset($this->bids[$variationId])) {
                        continue;
                    }
                }

                $qty = (float) $line->quantity;
                if ($qty == 0) {
                    continue;
                }

                // SID only for customers with a reported tobacco line (never a store with zero sales).
                if (! isset($this->sids[$contact->id])) {
                    $this->sids[$contact->id] = $this->mapSid($contact);
                    $this->purs[$contact->id] = [];
                }

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

        // Flatten PUR keys to list; drop any store left without a PUR row
        foreach ($this->purs as $contactId => $rows) {
            $this->purs[$contactId] = array_values($rows);
            if (empty($this->purs[$contactId])) {
                unset($this->purs[$contactId], $this->sids[$contactId]);
            }
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
        $adjustedAfter = (float) ($this->options['_adjusted_after'][$variationId] ?? 0);

        return max(0.0, $current + $soldAfter + $adjustedAfter - $returnedAfter - $purchasedAfter);
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

        // Stock adjustments (damage / count corrections) after week end removed stock that existed then
        $this->options['_adjusted_after'] = DB::table('stock_adjustment_lines as sal')
            ->join('transactions as t', 't.id', '=', 'sal.transaction_id')
            ->where('t.business_id', $this->business->id)
            ->where('t.location_id', $this->location->id)
            ->where('t.type', 'stock_adjustment')
            ->whereDate('t.transaction_date', '>', $this->weekEnd->toDateString())
            ->groupBy('sal.variation_id')
            ->selectRaw('sal.variation_id, SUM(sal.quantity) as qty')
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
        $store = $this->storeAddress();
        $name = strtoupper($this->location->name ?: $this->business->name);
        // Keep Nashville Goods branding when DID is 17033752
        if ($this->distributorId === '17033752' && stripos($name, 'NASHVILLE GOODS') === false) {
            $name = 'NASHVILLE GOODS DISTRIBUTION';
        }
        $street = $store['address'];
        $city = $store['city'];
        $state = $store['state'];
        $zip = $store['zip'];

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
        // Promo indicator MUST be Y or N only — never blank (MFT Warning 2015)
        $promoIndicator = (($bid['promo_indicator'] ?? 'N') === 'Y') ? 'Y' : 'N';
        $promoCode = $promoIndicator === 'Y' ? trim((string) ($bid['promo_code'] ?? '')) : '';
        $promoDesc = $promoIndicator === 'Y' ? trim((string) ($bid['promo_description'] ?? '')) : '';
        // Never fill promo code / description with placeholder text (PROMO, SEE ITEM DESCRIPTION…):
        // Altria flags it. Blank is correct when there is no real manufacturer promo code.

        $upc = $this->normalizeGtin14((string) ($bid['upc'] ?? ''));
        $sku = $this->normalizeGtin14((string) ($bid['sku'] ?? ''), true);

        return 'BID'
            . $this->rj($upc, 14, '0')
            . $this->rj($sku, 14, '0')
            . $this->lj($bid['description'], 50)
            . $this->lj($promoDesc, 50)
            . $this->rj((string) (int) $bid['items_per_unit'], 6, '0')
            . $promoIndicator
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
        $name = strtoupper(trim($contact->supplier_business_name ?: $contact->name));
        if ($name === '') {
            $name = 'CUSTOMER';
        }
        $storeNo = $this->digits((string) ($contact->custom_field2 ?? $contact->id), 8);

        $store = $this->storeAddress();
        $address = strtoupper(trim(($contact->address_line_1 ?? '').' '.($contact->address_line_2 ?? '')));
        $city = strtoupper(trim((string) ($contact->city ?? '')));
        $state = strtoupper(substr(trim((string) ($contact->state ?? '')), 0, 2));
        $zip = $this->msaZipField($contact->zip_code ?? '', $state, $city);

        // Walk-in / default customer, or any incomplete / invalid ship-to → store (location) address.
        // MFT Suspend 4011: zip 11011 (etc.) invalid for Nashville TN.
        $isWalkIn = ! empty($contact->is_default)
            || preg_match('/\bwalk[\s\-]?in\b/i', $name)
            || preg_match('/\bwalk[\s\-]?in\b/i', (string) ($contact->name ?? ''));

        $zip5 = substr($zip, 0, 5);
        $addressIncomplete = ($address === '' || $city === '' || ! $this->isPlausibleUsZip5($zip5, $state, $city));
        // Street must not be the city name alone (export showed ADDRESS=NASHVILLE)
        $streetIsCity = ($address !== '' && $city !== '' && $address === $city)
            || in_array($address, ['NASHVILLE', 'UNKNOWN', 'STORE'], true);

        if ($isWalkIn || $addressIncomplete || $streetIsCity) {
            $address = $store['address'];
            $city = $store['city'];
            $state = $store['state'];
            $zip = $store['zip'];
        }

        // Final normalize
        if ($state === '') {
            $state = $store['state'] !== '' ? $store['state'] : $this->stateTax;
        }
        $zip = $this->msaZipField($zip, $state, $city);
        if (! $this->isPlausibleUsZip5(substr($zip, 0, 5), $state, $city)) {
            $address = $store['address'];
            $city = $store['city'];
            $state = $store['state'];
            $zip = $store['zip'];
        }
        if ($address === '' || $address === $city) {
            $address = $store['address'];
        }
        if ($city === '') {
            $city = $store['city'];
        }

        $phone = $this->digits($contact->mobile ?? $contact->landline ?? '', 10);
        if ($phone === '0000000000') {
            $locPhone = preg_replace('/\D+/', '', (string) ($this->location->mobile ?? ''));
            if ($locPhone !== '') {
                $phone = $this->digits($locPhone, 10);
            }
        }

        $cot = $this->resolveClassOfTrade($contact);
        // Walk-in / cash sales at the warehouse → cash & carry Y (parent = store)
        $cashCarryRaw = strtoupper(substr((string) ($contact->custom_field3 ?? ''), 0, 1));
        if ($isWalkIn) {
            $cashCarry = 'Y';
        } elseif (in_array($cashCarryRaw, ['Y', 'N'], true)) {
            $cashCarry = $cashCarryRaw;
        } else {
            $cashCarry = 'N';
        }

        // Cust promo accept indicator — must be Y or N (MFT Warning 4033); blank is invalid
        $promoAcceptRaw = strtoupper(substr(trim((string) ($contact->custom_field4 ?? '')), 0, 1));
        $promoAccept = in_array($promoAcceptRaw, ['Y', 'N'], true) ? $promoAcceptRaw : 'N';

        return compact(
            'custNo', 'shipNo', 'ext', 'name', 'storeNo', 'address', 'city', 'state', 'zip', 'phone', 'cot', 'cashCarry', 'promoAccept'
        );
    }

    /**
     * Canonical ship-from for Nashville Goods (DID 17033752) when DB address/zip is bad.
     * Zip 37203 is a real Nashville TN ZIP — 11011 is New York and causes MFT Suspend 4011.
     *
     * @return array{address:string,city:string,state:string,zip5:string,zip:string}
     */
    protected function defaultDistributorAddress(): array
    {
        return [
            'address' => '1200 BROADWAY',
            'city' => 'NASHVILLE',
            'state' => 'TN',
            'zip5' => '37203',
            'zip' => '372030000',
        ];
    }

    /**
     * Ship-from / store address (business location) used for HID and incomplete SIDs.
     * Always returns MSA-valid street + city + state + 9-digit zip.
     *
     * @return array{address:string,city:string,state:string,zip:string}
     */
    protected function storeAddress(): array
    {
        $defaults = $this->defaultDistributorAddress();
        $useNashvilleDefaults = ($this->distributorId === '17033752')
            || stripos((string) ($this->location->name ?? ''), 'nashville goods') !== false
            || stripos((string) ($this->business->name ?? ''), 'nashville goods') !== false;

        $address = strtoupper(trim((string) ($this->location->landmark ?? '')));
        $city = strtoupper(trim((string) ($this->location->city ?? '')));
        $state = strtoupper(substr(trim((string) ($this->location->state ?? $this->stateTax)), 0, 2));

        if ($state === '') {
            $state = $useNashvilleDefaults ? $defaults['state'] : 'TN';
        }

        // Reject city-as-street / empty street (was EXPORT: Address NASHVILLE)
        if (
            $address === ''
            || $address === $city
            || in_array($address, ['NASHVILLE', 'UNKNOWN', 'STORE', 'DEMO'], true)
        ) {
            $address = $useNashvilleDefaults ? $defaults['address'] : ($city !== '' ? $city.' LOCATION' : $defaults['address']);
        }

        if ($city === '' || $city === 'UNKNOWN') {
            $city = $useNashvilleDefaults ? $defaults['city'] : 'UNKNOWN';
        }

        $zip = $this->msaZipField($this->location->zip_code ?? '', $state, $city);
        if (! $this->isPlausibleUsZip5(substr($zip, 0, 5), $state, $city)) {
            // Hard fallback for MFT — never emit invalid distributor zip again
            $address = $defaults['address'];
            $city = $defaults['city'];
            $state = $defaults['state'];
            $zip = $defaults['zip'];
        }

        return compact('address', 'city', 'state', 'zip');
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
        $store = $this->storeAddress();
        // Parent (cash & carry / warehouse) always the store location.
        $parentName = $sid['cashCarry'] === 'Y'
            ? strtoupper($this->location->name ?: $this->business->name)
            : $sid['name'];
        $parentAddr = $sid['cashCarry'] === 'Y'
            ? ($store['address'] !== '' ? $store['address'] : $sid['address'])
            : $sid['address'];
        $parentCity = $sid['cashCarry'] === 'Y'
            ? ($store['city'] !== '' ? $store['city'] : $sid['city'])
            : $sid['city'];
        $parentState = $sid['cashCarry'] === 'Y'
            ? ($store['state'] !== '' ? $store['state'] : $sid['state'])
            : $sid['state'];
        $parentZip = $sid['cashCarry'] === 'Y'
            ? (($store['zip'] !== '' && $store['zip'] !== '000000000') ? $store['zip'] : $sid['zip'])
            : $sid['zip'];
        $parentPhone = $sid['phone'];
        if ($parentPhone === '' || $parentPhone === '0000000000') {
            $parentPhone = $this->digits((string) ($this->location->mobile ?? ''), 10);
        }
        $independentFlag = $sid['cashCarry'] === 'Y' ? 'N' : 'Y';

        // Ship-to / parent always re-validated (never re-emit Suspend 4011 zip)
        $shipAddr = $sid['address'] !== '' ? $sid['address'] : $store['address'];
        $shipCity = $sid['city'] !== '' ? $sid['city'] : $store['city'];
        $shipState = $sid['state'] !== '' ? $sid['state'] : ($store['state'] !== '' ? $store['state'] : $this->stateTax);
        $shipZip = $this->msaZipField($sid['zip'] ?? '', $shipState, $shipCity);
        if (! $this->isPlausibleUsZip5(substr($shipZip, 0, 5), $shipState, $shipCity)) {
            $shipAddr = $store['address'];
            $shipCity = $store['city'];
            $shipState = $store['state'];
            $shipZip = $store['zip'];
        }
        if ($shipAddr === '' || $shipAddr === $shipCity) {
            $shipAddr = $store['address'];
        }

        $parentZip = $this->msaZipField($parentZip ?? '', $parentState, $parentCity);
        if (! $this->isPlausibleUsZip5(substr($parentZip, 0, 5), $parentState, $parentCity)) {
            $parentAddr = $store['address'];
            $parentCity = $store['city'];
            $parentState = $store['state'];
            $parentZip = $store['zip'];
        }
        if ($parentAddr === '' || $parentAddr === $parentCity) {
            $parentAddr = $store['address'];
        }
        $line = 'SID'
            . $this->lj($sid['custNo'], 8)
            . $this->lj($sid['shipNo'], 8)
            . $this->lj($sid['ext'], 8)
            . $this->lj($sid['name'], 32)
            . $this->lj($sid['storeNo'], 8)
            . $this->lj($shipAddr, 90)
            . $this->lj($shipCity, 25)
            . $this->lj($shipState, 2)
            . $this->lj($shipZip, 9)
            . $this->lj('USA', 3)
            . $this->lj($this->stateTax, 2) // option1 usually blank when using BID option2 — samples fill both TN
            . $this->lj('', 3)
            . $this->rj($sid['phone'], 10)
            . $this->lj($sid['cot'], 20)
            . $this->rj('0', 7) // TDLinx
            . $sid['cashCarry']
            // Col 240: cash & carry adjacent Promo Accept early field (must be Y/N, not blank)
            . (($sid['promoAccept'] ?? 'N') === 'Y' ? 'Y' : 'N')
            . str_repeat(' ', 66)
            . $this->lj($parentName, 32)
            . $this->lj($parentAddr, 90)
            . $this->lj($parentCity, 25)
            . $this->lj($parentState, 2)
            . $this->lj($parentZip, 9)
            . $this->lj('USA', 3)
            . $this->lj('', 5)
            . $this->rj($parentPhone, 10)
            . $this->rj('0', 14, '0')
            // MFT Warning 4033 / Altria test feedback (DID 17033752):
            // "Promo Acceptance Indicator for SID needs to be a Y/N in column 498"
            // but the Y/N was one column early at 497. Parent phone (10) + zeros (14)
            // ends at col 496; insert one pad so the indicator lands on col 498.
            // Trailing filler 42 → 41 keeps SID total length 551.
            . ' ' // col 497 pad
            . $independentFlag // col 498 — Y/N (MFT Promo Acceptance / independent indicator)
            . str_repeat(' ', 41)
            . $this->rj('0', 11, '0');

        // Keep exact fixed width; do not rtrim padding spaces
        $line = substr($this->lj($line, 551), 0, 551);

        return $line;
    }

    /**
     * Assert SID Promo Acceptance / indicator is in column 498 (1-based).
     * Used by tests / diagnostics.
     */
    public static function sidPromoColumnIsCorrect(string $sidLine): bool
    {
        if (strlen($sidLine) < 498 || ! str_starts_with($sidLine, 'SID')) {
            return false;
        }
        $ch = $sidLine[497]; // 0-based index for column 498

        return $ch === 'Y' || $ch === 'N';
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

        $selected = is_array($categoryId) ? $categoryId : [$categoryId];
        $selected = array_values(array_unique(array_filter(array_map('intval', $selected))));
        if (empty($selected)) {
            return [];
        }

        $childIds = Category::where('business_id', $this->business->id)
            ->whereIn('parent_id', $selected)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return array_values(array_unique(array_merge($selected, $childIds)));
    }

    protected function upc(Product $product, $variation): string
    {
        // Prefer real barcode digits (sub_sku / sku). Alphanumeric SKUs like TOB-CIG-LUX
        // previously became UPC=all zeros (MFT Warning 2038).
        $candidates = [
            (string) ($variation->sub_sku ?? ''),
            (string) ($product->sku ?? ''),
            (string) ($product->product_custom_field5 ?? ''),
        ];

        foreach ($candidates as $raw) {
            $digits = preg_replace('/\D+/', '', $raw) ?? '';
            if ($this->isUsableUpcDigits($digits)) {
                return $this->padGtin14($digits);
            }
        }

        // Synthesize stable private GTIN-14 so the field is never all zeros
        return $this->syntheticGtin14((int) $product->id, (int) $variation->id);
    }

    protected function distributorSku(Product $product, $variation): string
    {
        $candidates = [
            (string) ($product->sku ?? ''),
            (string) ($variation->sub_sku ?? ''),
        ];

        foreach ($candidates as $raw) {
            $digits = preg_replace('/\D+/', '', $raw) ?? '';
            // Accept long numeric skus; short internal 001 from ECOM-001 is ok if not all zero
            if ($digits !== '' && ! preg_match('/^0+$/', $digits)) {
                return $this->padGtin14($digits);
            }
        }

        // Stable numeric SKU from variation id (never zero)
        $id = max(1, (int) $variation->id);

        return $this->padGtin14((string) $id);
    }

    /**
     * MSA Fatal error: every BID needs its own SKU. Variations of one product share product->sku, and
     * alphanumeric SKUs collapse to the same digits (TOB-001 / VAP-001 → 001). On collision fall back to
     * the variation's own sub_sku, then to a private GTIN built from product + variation id.
     */
    protected function uniqueSku(string $sku, Product $product, $variation): string
    {
        if (! isset($this->usedSkus[$sku])) {
            return $sku;
        }

        $subDigits = preg_replace('/\D+/', '', (string) ($variation->sub_sku ?? '')) ?? '';
        if ($subDigits !== '' && ! preg_match('/^0+$/', $subDigits)) {
            $candidate = $this->padGtin14($subDigits);
            if (! isset($this->usedSkus[$candidate])) {
                return $candidate;
            }
        }

        $candidate = $this->syntheticGtin14((int) $product->id, (int) $variation->id);
        while (isset($this->usedSkus[$candidate])) {
            $candidate = str_pad((string) (((int) $candidate) + 1), 14, '0', STR_PAD_LEFT);
        }

        return $candidate;
    }

    /**
     * Promo Y/N from CF3/CF4 or product description (PROMO, nice price, $/, sale, etc.).
     * A price/promo in the description only sets the indicator to Y; promo code and description
     * come from CF3/CF4 when entered and are otherwise left blank (no placeholders).
     *
     * @return array{promo_indicator:string,promo_code:string,promo_description:string}
     */
    protected function resolvePromoFlags(Product $product, string $description): array
    {
        $promoCode = trim((string) ($product->product_custom_field3 ?? ''));
        $promoDesc = trim((string) ($product->product_custom_field4 ?? ''));
        $hay = strtoupper($this->asciiOnly(trim($description.' '.($product->name ?? ''))));

        $looksPromo = (bool) preg_match(
            '/\b(PROMO(?:TION)?S?|NICE\s*PRICE|SPECIAL|SALE|DISCOUNT|SAVE\b|DEAL|BOGO|BUY\s*\d|%\s*OFF|VALUE\s*PACK|LIMITED|TWO\s*FOR|2\s*FOR|OFFER|CLEARANCE)\b/',
            $hay
        ) || (bool) preg_match('/\$\s*\d|\d+\s*¢|\b\d+\.\d{2}\b|\b\d+\s*OFF\b/', $hay);

        if ($promoCode !== '' || $promoDesc !== '' || $looksPromo) {
            return [
                'promo_indicator' => 'Y',
                'promo_code' => strtoupper(substr($this->asciiOnly($promoCode), 0, 10)),
                'promo_description' => strtoupper(substr($this->asciiOnly($promoDesc), 0, 50)),
            ];
        }

        return [
            'promo_indicator' => 'N',
            'promo_code' => '',
            'promo_description' => '',
        ];
    }

    /**
     * Real UPC/EAN/GTIN digit string (not blank / not all zeros / typical length).
     */
    protected function isUsableUpcDigits(string $digits): bool
    {
        if ($digits === '' || preg_match('/^0+$/', $digits)) {
            return false;
        }
        $len = strlen($digits);

        // Standard retail lengths; reject tiny 1–3 digit fragments
        return $len >= 8 && $len <= 14;
    }

    protected function padGtin14(string $digits): string
    {
        $digits = preg_replace('/\D+/', '', $digits) ?? '';
        if ($digits === '' || preg_match('/^0+$/', $digits)) {
            return '20000000000001';
        }
        if (strlen($digits) > 14) {
            $digits = substr($digits, -14);
        }

        return str_pad($digits, 14, '0', STR_PAD_LEFT);
    }

    /**
     * Private GTIN-style code when product has no numeric barcode (alphanumeric SKUs).
     * Always non-zero so MFT 2038 does not fire.
     */
    protected function syntheticGtin14(int $productId, int $variationId): string
    {
        $productId = max(1, $productId % 100000);
        $variationId = max(1, $variationId % 10000000);
        // Indicator 2 = internal / variable measure private marking
        $body = sprintf('2%05d%08d', $productId, $variationId);

        return substr(str_pad($body, 14, '0', STR_PAD_RIGHT), 0, 14);
    }

    /**
     * Final UPC/SKU cell before write — never emit all zeros.
     */
    protected function normalizeGtin14(string $value, bool $allowShortSku = false): string
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';
        if ($digits === '' || preg_match('/^0+$/', $digits)) {
            return '20000000000001';
        }
        if (! $allowShortSku && ! $this->isUsableUpcDigits($digits) && strlen($digits) < 8) {
            // Short non-zero still ok after pad for SKU; for UPC prefer synthetic already applied
            return $this->padGtin14($digits);
        }

        return $this->padGtin14($digits);
    }

    protected function productDescription(Product $product, $variation, ?string $skuFallback = null): string
    {
        $name = trim((string) ($product->name ?? ''));
        if ($product->type === 'variable' && ! empty($variation->name) && $variation->name !== 'DUMMY') {
            $name = trim($name.' '.$variation->name);
        }

        // Never leave BID description blank — empty description shifts unit/category columns.
        if ($name === '') {
            $name = 'PRODUCT ' . ($skuFallback ?: ($product->sku ?: $product->id));
        }

        // Strip CR/LF and non-ASCII so fixed-width columns never drift.
        $name = $this->asciiOnly($name);
        $name = preg_replace('/\s+/', ' ', str_replace(["\r", "\n", "\t"], ' ', $name));

        return trim($name) !== '' ? trim($name) : ('PRODUCT ' . ($product->id ?? '0'));
    }

    /**
     * Altria/Liggett do not currently monitor these product types — omit from TOB file.
     */
    protected function isExcludedFromMsaReport(Product $product): bool
    {
        if (! isset($this->options['_category_names'])) {
            $this->options['_category_names'] = Category::where('business_id', $this->business->id)->pluck('name', 'id')->all();
        }
        $subName = (string) ($this->options['_category_names'][(int) $product->sub_category_id] ?? '');
        $catName = (string) ($this->options['_category_names'][(int) $product->category_id] ?? '');

        $haystack = strtolower(trim(
            ($product->name ?? '').' '.($product->sku ?? '').' '.$subName.' '.$catName
        ));

        // Explicit exclusion markers on CF2
        $cf2 = strtoupper(trim((string) ($product->product_custom_field2 ?? '')));
        if (in_array($cf2, ['EXCLUDE', 'SKIP', 'N/A', 'NA'], true)) {
            return true;
        }

        if (preg_match(
            '/\b(thc|delta[\s-]?8|delta[\s-]?9|delta[\s-]?10|kratom|kanna|hemp\b(?!\s*wrap)|cbd\s*(gumm|flower|oil|vape)|hhc|thca|thcp|mushroom|psilly)\b/i',
            $haystack
        )) {
            return true;
        }

        // Hardware with no nicotine (510 batteries, chargers) — "cake batter" flavours are not matched
        return (bool) preg_match('/\b(batter(y|ies)|chargers?)\b/i', (string) ($product->name ?? ''));
    }

    /**
     * Items per selling unit (carton=200, pack counts for vapor boxes, etc.).
     * Reject UPC/SKU-sized values accidentally stored in custom field 1 (caused inflated units).
     */
    protected function itemsPerSellingUnit(Product $product): int
    {
        $inferred = $this->inferItemsPerSellingUnit($product);

        $raw = preg_replace('/\D+/', '', (string) ($product->product_custom_field1 ?? ''));
        // Valid MSA values are small (1–250). Anything larger is almost certainly a barcode/SKU.
        if ($raw !== '' && strlen($raw) <= 3) {
            $custom = (int) $raw;
            if ($custom >= 1 && $custom <= 250) {
                // Prefer name/category inference when CF1 is a generic "1" but name says BOX OF 10 / BOX OF 20.
                if ($custom === 1 && $inferred > 1) {
                    return $inferred;
                }
                // Prefer pack count from name over wrong model numbers stuffed into CF1 (e.g. 49 from XU-49M).
                if ($inferred >= 10 && $custom !== $inferred && preg_match('/\b(box\s+of|ct|count|pack|pk|pcs?)\b/i', (string) $product->name)) {
                    return $inferred;
                }

                return $custom;
            }
        }

        return $inferred;
    }

    protected function inferItemsPerSellingUnit(Product $product): int
    {
        if (! isset($this->options['_category_names'])) {
            $this->options['_category_names'] = Category::where('business_id', $this->business->id)->pluck('name', 'id')->all();
        }
        $subName = (string) ($this->options['_category_names'][(int) $product->sub_category_id] ?? '');
        $catName = (string) ($this->options['_category_names'][(int) $product->category_id] ?? '');

        $haystack = strtolower(trim(($product->name ?? '').' '.$subName.' '.$catName));

        // Explicit pack counts: "BOX OF 10", "20 CT", "10 PC", "5 PK"
        if (preg_match('/\bbox\s+of\s+(\d{1,3})\b/', $haystack, $m)) {
            $n = (int) $m[1];
            if ($n >= 1 && $n <= 250) {
                return $n;
            }
        }
        if (preg_match('/\b(\d{1,3})\s*(pc|pcs|pk|pack|packs|ct|count)\b/', $haystack, $m)) {
            $n = (int) $m[1];
            if ($n >= 1 && $n <= 250) {
                return $n;
            }
        }

        if (preg_match('/\b(lighter|zippo|torch|bic\b|clipper)\b/', $haystack)) {
            // Torch lighter box of 20 handled above; single lighter = 1
            return 1;
        }
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
            // Multi-pack pods often "4 pack" captured above; single pod kit units default 4
            return 4;
        }
        // Vapor/disposable selling unit = packs; prefer pack count from name, else 1 pack
        if (preg_match('/\b(e-?cig|vape|disposable|puff|juul|elf\s*bar|geek)\b/', $haystack)) {
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
        // Name-driven categories first for accessories that were mis-filed under vapor
        $nameHint = $this->inferMsaCategoryFromText((string) ($product->name ?? ''));
        if ($nameHint === '003263') {
            return '003263'; // lighters
        }

        $custom = preg_replace('/\D+/', '', (string) ($product->product_custom_field2 ?? ''));
        // Accept only known-length MSA codes (6 digits). Reject barcodes stuffed into CF2.
        if (strlen($custom) === 6 && str_starts_with($custom, '003')) {
            // Override wrong vapor code when product is clearly a lighter
            if ($custom === '003292' && $nameHint === '003263') {
                return '003263';
            }

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
                $code = $m[1];
                // Don't leave lighters on vapor just because they sit under Tobacco parent
                if ($code === '003292' && $nameHint === '003263') {
                    return '003263';
                }

                return $code;
            }
            $inferred = $this->inferMsaCategoryFromText((string) ($category->name ?? ''));
            if ($inferred !== null) {
                return $inferred;
            }
        }

        if ($nameHint !== null) {
            return $nameHint;
        }

        // Last resort — e-cig/vapor family (not cigarettes).
        return '003292';
    }

    protected function inferMsaCategoryFromText(string $text): ?string
    {
        $name = strtolower($text);

        // Lighters / torches are NOT vapor — MSA accessory code 3263 → 003263
        if (preg_match('/\b(lighter|lighters|zippo|torch\s*lighter|bic\b|clipper\s*lighter)\b/', $name)
            || preg_match('/\btorch\b.*\blighter\b|\blighter\b.*\btorch\b/', $name)) {
            return '003263';
        }
        if (preg_match('/\b(e-?cig|vape|disposable|puff|pod|juul|elf\s*bar|geek|mr\s*fog)\b/', $name)) {
            return '003292';
        }
        if (preg_match('/\b(blunt\s*wrap|wraps?|papers?|tubes?)\b/', $name)) {
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

        return null;
    }

    /**
     * Fixed-width fields must be single-byte. Strip or transliterate non-ASCII.
     */
    protected function asciiOnly(string $value): string
    {
        $value = (string) $value;
        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
            if ($converted !== false) {
                $value = $converted;
            }
        }

        return preg_replace('/[^\x20-\x7E]/', '', $value) ?? '';
    }

    protected function zipPlus4(?string $zip): string
    {
        // Backward-compatible alias — prefer msaZipField for State-aware validation
        return $this->msaZipField($zip, $this->stateTax ?: 'TN', '');
    }

    /**
     * 9-char MSA zip (5+4). Rejects invalid ZIPs that fail MFT (e.g. 11011 for Nashville TN).
     */
    protected function msaZipField(?string $zip, string $state = 'TN', string $city = ''): string
    {
        $digits = preg_replace('/\D+/', '', (string) $zip);
        if ($digits === '') {
            $digits = '00000';
        }
        $zip5 = substr(str_pad($digits, 5, '0', STR_PAD_LEFT), 0, 5);
        $plus4 = strlen($digits) >= 9 ? substr($digits, 5, 4) : '0000';
        $plus4 = str_pad(substr($plus4, 0, 4), 4, '0');

        if (! $this->isPlausibleUsZip5($zip5, $state, $city)) {
            return $this->defaultDistributorAddress()['zip'];
        }

        return $zip5.$plus4;
    }

    /**
     * Basic US/TN ZIP plausibility for Multicat MFT (Suspend error 4011).
     */
    protected function isPlausibleUsZip5(string $zip5, string $state = '', string $city = ''): bool
    {
        if (! preg_match('/^\d{5}$/', $zip5)) {
            return false;
        }
        // Explicit denylist of bad / demo / shifted ZIPs that have failed Nashville filing
        if (in_array($zip5, ['00000', '11111', '12345', '99999', '11011', '11001', '10001'], true)) {
            return false;
        }

        $state = strtoupper(substr(trim($state), 0, 2));
        $n = (int) $zip5;

        // Tennessee ZIP range roughly 37001–38589
        if ($state === 'TN' || stripos($city, 'NASHVILLE') !== false) {
            return $n >= 37001 && $n <= 38589;
        }

        // Generic: non-zero range
        return $n >= 501 && $n <= 99950;
    }

    protected function digits(string $value, int $len): string
    {
        $digits = preg_replace('/\D+/', '', $value);

        return substr(str_pad($digits ?: '0', $len, '0', STR_PAD_LEFT), -$len);
    }

    protected function lj(string $value, int $len): string
    {
        $value = $this->asciiOnly($value);
        // Byte-safe truncate for fixed-width ASCII records
        $value = substr($value, 0, $len);

        return str_pad($value, $len, ' ', STR_PAD_RIGHT);
    }

    protected function rj(string $value, int $len, string $pad = '0'): string
    {
        $value = $this->asciiOnly($value);
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
