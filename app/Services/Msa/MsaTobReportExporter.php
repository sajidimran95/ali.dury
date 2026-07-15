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

        return implode("\r\n", $lines) . "\r\n";
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

        $stockRows = VariationLocationDetails::query()
            ->where('location_id', $this->location->id)
            ->where('qty_available', '>', 0)
            ->pluck('qty_available', 'variation_id');

        $allVariationIds = collect($variationIds)
            ->merge($stockRows->keys())
            ->unique()
            ->values();

        if ($allVariationIds->isEmpty() && empty($categoryIds)) {
            // Still allow export of empty skeleton with no BIDs when no activity
            $this->bids = [];

            return;
        }

        $products = Product::query()
            ->where('business_id', $this->business->id)
            ->where('enable_stock', 1)
            ->when(! empty($categoryIds), function ($q) use ($categoryIds) {
                $q->where(function ($inner) use ($categoryIds) {
                    $inner->whereIn('category_id', $categoryIds)
                        ->orWhereIn('sub_category_id', $categoryIds);
                });
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
                $inventory = (float) ($stockRows[$variation->id] ?? 0);

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
                        'inventory' => 0.0,
                        'wholesaler_returns' => 0.0,
                    ];
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
                }
            }
        }

        // Flatten PUR keys to list
        foreach ($this->purs as $contactId => $rows) {
            $this->purs[$contactId] = array_values($rows);
        }
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
        $cot = strtoupper(trim((string) ($contact->custom_field1 ?? 'RETAIL')));
        if ($cot === '') {
            $cot = 'RETAIL';
        }
        $cashCarry = strtoupper(substr((string) ($contact->custom_field3 ?? 'N'), 0, 1));
        if (! in_array($cashCarry, ['Y', 'N'], true)) {
            $cashCarry = 'N';
        }

        return compact(
            'custNo', 'shipNo', 'ext', 'name', 'storeNo', 'address', 'city', 'state', 'zip', 'phone', 'cot', 'cashCarry'
        );
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
            . $this->measureValue($pur['dollars'], 11)
            . '004'
            . $this->measureValue(-1 * abs($pur['saleable_returns']), 11, true)
            . '005'
            . $this->measureValue(-1 * abs($pur['unsaleable_returns']), 11, true);
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
            $name .= ' ' . $variation->name;
        }

        return $name;
    }

    protected function itemsPerSellingUnit(Product $product): int
    {
        $custom = (int) preg_replace('/\D+/', '', (string) ($product->product_custom_field1 ?? ''));
        if ($custom > 0) {
            return $custom;
        }

        return 200;
    }

    protected function msaCategoryCode(Product $product): string
    {
        $custom = preg_replace('/\D+/', '', (string) ($product->product_custom_field2 ?? ''));
        if (strlen($custom) >= 4) {
            return substr(str_pad($custom, 6, '0', STR_PAD_LEFT), -6);
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
            if (preg_match('/\b(\d{6})\b/', (string) $category->description, $m)) {
                return $m[1];
            }
            $name = strtolower($category->name ?? '');
            if (str_contains($name, 'e-cig') || str_contains($name, 'vape')) {
                return '003292';
            }
            if (str_contains($name, 'little') && str_contains($name, 'cigar')) {
                return '003252';
            }
            if (str_contains($name, 'cigar')) {
                return '003251';
            }
            if (str_contains($name, 'moist') || str_contains($name, 'spit') || str_contains($name, 'snuff') || str_contains($name, 'chew') || str_contains($name, 'snus')) {
                return '003211';
            }
            if (str_contains($name, 'ryo')) {
                return '003221';
            }
            if (str_contains($name, 'pipe')) {
                return '003241';
            }
            if (str_contains($name, 'cigarette')) {
                return '003231';
            }
        }

        return '003231';
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
