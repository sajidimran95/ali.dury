<?php

namespace App\Services\Msa;

use App\Business;
use App\Category;
use App\Contact;
use App\CustomerGroup;
use App\Product;
use App\SellingPriceGroup;
use App\TaxRate;
use App\Unit;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\ProductUtil;
use App\Utils\TaxUtil;
use App\VariationGroupPrice;
use App\VariationLocationDetails;
use Carbon\Carbon;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MsaSetupService
{
    /**
     * Create/restore Tobacco MSA categories, labels, retail/wholesale groups, and defaults.
     *
     * @param  bool  $withDemoData  When true, also seed sample products, stock, customers, and sales.
     * @return int Tobacco parent category id
     */
    public function setupForBusiness(Business $business, bool $withDemoData = true): int
    {
        $parentId = $this->ensureCategories((int) $business->id);
        $priceGroups = $this->ensurePriceGroups((int) $business->id);
        $customerGroups = $this->ensureCustomerGroups((int) $business->id, $priceGroups);
        $taxRates = $this->ensureTaxRates($business);
        $this->ensureCustomLabels($business);
        $this->ensureCommonSettings($business, $parentId, $priceGroups, $customerGroups);
        $this->ensureWalkInCustomer((int) $business->id, $customerGroups['retailer']);

        if ($withDemoData) {
            $this->ensureTobaccoProductDefaults((int) $business->id, $parentId, $taxRates['default_id'] ?? null);
            $this->ensureDemoSalesData($business, $parentId, $priceGroups, $customerGroups, $taxRates['default_id'] ?? null);
        }

        // Always keep Retail/Wholesale price tiers in sync for tobacco products by subcategory.
        $this->ensurePriceTiersByCategory((int) $business->id, $parentId, $priceGroups);

        return $parentId;
    }

    public function ensureCategories(int $businessId): int
    {
        $parent = Category::withTrashed()
            ->where('business_id', $businessId)
            ->where('category_type', 'product')
            ->where('name', 'Tobacco')
            ->where('parent_id', 0)
            ->first();

        if ($parent && $parent->trashed()) {
            $parent->restore();
        }

        if (! $parent) {
            $parent = Category::create([
                'name' => 'Tobacco',
                'business_id' => $businessId,
                'short_code' => 'TOB',
                'parent_id' => 0,
                'created_by' => User::where('business_id', $businessId)->value('id') ?: 1,
                'category_type' => 'product',
            ]);
        }

        $subs = [
            ['name' => 'Cigarettes', 'short_code' => 'CIG', 'msa' => '003231'],
            ['name' => 'Moist Spit Tobacco', 'short_code' => 'MST', 'msa' => '003211'],
            ['name' => 'Cigars', 'short_code' => 'CGR', 'msa' => '003251'],
            ['name' => 'Little Filtered Cigars', 'short_code' => 'LFC', 'msa' => '003252'],
            ['name' => 'E-Cigarettes', 'short_code' => 'ECG', 'msa' => '003292'],
            ['name' => 'RYO Tobacco', 'short_code' => 'RYO', 'msa' => '003221'],
            ['name' => 'Pipe Tobacco', 'short_code' => 'PIP', 'msa' => '003241'],
            // Lighters belong to MSA accessory category 3263 (padded 003263) — not vapor 003292
            ['name' => 'Lighters', 'short_code' => 'LGT', 'msa' => '003263'],
        ];

        foreach ($subs as $sub) {
            $existing = Category::withTrashed()
                ->where('business_id', $businessId)
                ->where('category_type', 'product')
                ->where('parent_id', $parent->id)
                ->where('name', $sub['name'])
                ->first();

            if ($existing && $existing->trashed()) {
                $existing->restore();
            }

            if (! $existing) {
                Category::create([
                    'name' => $sub['name'],
                    'business_id' => $businessId,
                    'short_code' => $sub['short_code'],
                    'parent_id' => $parent->id,
                    'created_by' => User::where('business_id', $businessId)->value('id') ?: 1,
                    'category_type' => 'product',
                    'description' => 'MSA category code '.$sub['msa'],
                ]);
            } elseif (empty($existing->description)) {
                $existing->description = 'MSA category code '.$sub['msa'];
                $existing->save();
            }
        }

        return (int) $parent->id;
    }

    /**
     * @return array{retail: SellingPriceGroup, wholesale: SellingPriceGroup}
     */
    public function ensurePriceGroups(int $businessId): array
    {
        $retail = $this->firstOrCreatePriceGroup($businessId, 'Retail');
        $wholesale = $this->firstOrCreatePriceGroup($businessId, 'Wholesale');

        return [
            'retail' => $retail,
            'wholesale' => $wholesale,
        ];
    }

    protected function firstOrCreatePriceGroup(int $businessId, string $name): SellingPriceGroup
    {
        $group = SellingPriceGroup::withTrashed()
            ->where('business_id', $businessId)
            ->where('name', $name)
            ->first();

        if ($group && $group->trashed()) {
            $group->restore();
        }

        if (! $group) {
            $group = SellingPriceGroup::create([
                'name' => $name,
                'description' => $name.' selling prices for tobacco / MSA',
                'business_id' => $businessId,
                'is_active' => 1,
            ]);
        } else {
            $group->is_active = 1;
            $group->save();
        }

        $permission = Permission::firstOrCreate([
            'name' => 'selling_price_group.'.$group->id,
            'guard_name' => 'web',
        ]);

        $adminRole = Role::where('name', 'Admin#'.$businessId)->where('guard_name', 'web')->first();
        if ($adminRole && ! $adminRole->hasPermissionTo($permission)) {
            $adminRole->givePermissionTo($permission);
        }

        return $group;
    }

    /**
     * @param  array{retail: SellingPriceGroup, wholesale: SellingPriceGroup}  $priceGroups
     * @return array{retailer: CustomerGroup, wholesale: CustomerGroup}
     */
    public function ensureCustomerGroups(int $businessId, array $priceGroups): array
    {
        $createdBy = User::where('business_id', $businessId)->value('id') ?: 1;

        $retailer = $this->firstOrCreateCustomerGroup(
            $businessId,
            'Retailer',
            (int) $priceGroups['retail']->id,
            $createdBy
        );

        $wholesale = $this->firstOrCreateCustomerGroup(
            $businessId,
            'Wholesale',
            (int) $priceGroups['wholesale']->id,
            $createdBy
        );

        return [
            'retailer' => $retailer,
            'wholesale' => $wholesale,
        ];
    }

    protected function firstOrCreateCustomerGroup(
        int $businessId,
        string $name,
        int $sellingPriceGroupId,
        int $createdBy
    ): CustomerGroup {
        $group = CustomerGroup::where('business_id', $businessId)
            ->where('name', $name)
            ->first();

        if (! $group) {
            $group = CustomerGroup::create([
                'name' => $name,
                'business_id' => $businessId,
                'amount' => 0,
                'price_calculation_type' => 'selling_price_group',
                'selling_price_group_id' => $sellingPriceGroupId,
                'created_by' => $createdBy,
            ]);
        } else {
            $group->price_calculation_type = 'selling_price_group';
            $group->selling_price_group_id = $sellingPriceGroupId;
            $group->save();
        }

        return $group;
    }

    public function ensureCustomLabels(Business $business): void
    {
        $labels = $business->custom_labels;
        if (is_string($labels)) {
            $labels = json_decode($labels, true) ?: [];
        }
        if (! is_array($labels)) {
            $labels = [];
        }

        $labels['product'] = $labels['product'] ?? [];
        $labels['contact'] = $labels['contact'] ?? [];
        $labels['product_cf_details'] = $labels['product_cf_details'] ?? [];
        $labels['contact_cf_details'] = $labels['contact_cf_details'] ?? [];

        $productLabels = [
            1 => 'MSA Items Per Selling Unit',
            2 => 'MSA Category Code',
            3 => 'MSA Promo Code',
            4 => 'MSA Promo Description',
        ];
        foreach ($productLabels as $i => $label) {
            if (empty($labels['product']['custom_field_'.$i])) {
                $labels['product']['custom_field_'.$i] = $label;
            }
        }

        $labels['product_cf_details'][1] = [
            'type' => 'dropdown',
            'dropdown_options' => "1\n4\n5\n10\n20\n25\n40\n60\n100\n200\n250",
        ];
        $labels['product_cf_details'][2] = [
            'type' => 'dropdown',
            'dropdown_options' => "003231\n003211\n003251\n003252\n003292\n003221\n003241\n003261\n003263\n003213\n003212\n003214\n003215\n003227",
        ];
        $labels['product_cf_details'][3] = [
            'type' => 'text',
            'dropdown_options' => null,
        ];
        $labels['product_cf_details'][4] = [
            'type' => 'text',
            'dropdown_options' => null,
        ];

        if (empty($labels['contact']['custom_field_1'])) {
            $labels['contact']['custom_field_1'] = 'MSA Class Of Trade';
        }
        if (empty($labels['contact']['custom_field_2'])) {
            $labels['contact']['custom_field_2'] = 'MSA Store #';
        }
        if (empty($labels['contact']['custom_field_3'])) {
            $labels['contact']['custom_field_3'] = 'MSA Cash And Carry (Y/N)';
        }

        $labels['contact_cf_details'][1] = [
            'type' => 'dropdown',
            'dropdown_options' => "RETAIL\nDISTRIBUTOR\nOTHER",
        ];
        $labels['contact_cf_details'][2] = [
            'type' => 'text',
            'dropdown_options' => null,
        ];
        $labels['contact_cf_details'][3] = [
            'type' => 'dropdown',
            'dropdown_options' => "Y\nN",
        ];

        $business->custom_labels = $labels;
        $business->save();
    }

    /**
     * @param  array{retail: SellingPriceGroup, wholesale: SellingPriceGroup}  $priceGroups
     * @param  array{retailer: CustomerGroup, wholesale: CustomerGroup}  $customerGroups
     */
    public function ensureCommonSettings(
        Business $business,
        int $tobaccoCategoryId,
        array $priceGroups,
        array $customerGroups
    ): void {
        $common = $business->common_settings ?? [];
        if (! is_array($common)) {
            $common = [];
        }

        $common['msa_tobacco_category_id'] = $tobaccoCategoryId;
        $common['msa_retail_price_group_id'] = (int) $priceGroups['retail']->id;
        $common['msa_wholesale_price_group_id'] = (int) $priceGroups['wholesale']->id;
        $common['msa_retail_customer_group_id'] = (int) $customerGroups['retailer']->id;
        $common['msa_wholesale_customer_group_id'] = (int) $customerGroups['wholesale']->id;

        if (empty($common['msa_state_tax'])) {
            $common['msa_state_tax'] = 'TN';
        }
        // Nashville Goods sample distributor id used with Altria MFT
        if (empty($common['msa_distributor_id'])) {
            $common['msa_distributor_id'] = '17033752';
        }

        $admin = User::where('business_id', $business->id)->orderBy('id')->first();
        if ($admin) {
            if (empty($common['msa_contact_first']) && ! empty($admin->first_name)) {
                $common['msa_contact_first'] = $admin->first_name;
            }
            if (empty($common['msa_contact_last']) && ! empty($admin->surname)) {
                $common['msa_contact_last'] = $admin->surname;
            }
            if (empty($common['msa_contact_email']) && ! empty($admin->email)) {
                $common['msa_contact_email'] = $admin->email;
            }
        }

        $business->common_settings = $common;
        $business->save();

        // Align location 1 branding for Nashville Goods HID when still a generic name
        $location = \App\BusinessLocation::where('business_id', $business->id)->orderBy('id')->first();
        if ($location) {
            $dirtyLoc = false;
            $generic = empty($location->name)
                || stripos($location->name, 'awesome') !== false
                || stripos($location->name, 'demo') !== false
                || stripos($location->name, 'japs pos') !== false
                || strcasecmp(trim($location->name), trim((string) $business->name)) === 0;
            // Sample MSA filing uses Nashville Goods Distribution + distributor 17033752
            if ($generic || ($common['msa_distributor_id'] ?? '') === '17033752') {
                if (stripos((string) $location->name, 'nashville goods') === false) {
                    $location->name = 'Nashville Goods Distribution';
                    $dirtyLoc = true;
                }
            }
            if (empty($location->city) || stripos((string) $location->city, 'nashville') === false) {
                $location->city = 'Nashville';
                $dirtyLoc = true;
            }
            if (empty($location->state) || strlen(trim((string) $location->state)) < 2) {
                $location->state = 'TN';
                $dirtyLoc = true;
            }
            if (empty($location->zip_code)) {
                $location->zip_code = '37203';
                $dirtyLoc = true;
            }
            if (empty($location->landmark) || stripos((string) $location->landmark, 'nashville') === false) {
                $location->landmark = '1200 Broadway';
                $dirtyLoc = true;
            }
            if ($dirtyLoc) {
                $location->save();
            }
        }
    }

    /**
     * Create the tax rates Japstech POS ships with (demo defaults) plus TN sales tax.
     *
     * @return array{default_id: int|null, rates: array<string, TaxRate>}
     */
    public function ensureTaxRates(Business $business): array
    {
        $businessId = (int) $business->id;
        $createdBy = User::where('business_id', $businessId)->value('id') ?: 1;

        $vat = $this->firstOrCreateTaxRate($businessId, 'VAT@10%', 10, $createdBy, false, false);
        $cgst = $this->firstOrCreateTaxRate($businessId, 'CGST@10%', 10, $createdBy, false, true);
        $sgst = $this->firstOrCreateTaxRate($businessId, 'SGST@8%', 8, $createdBy, false, true);
        $igst = $this->firstOrCreateTaxRate($businessId, 'IGST@18%', 18, $createdBy, false, false);
        $salesTax = $this->firstOrCreateTaxRate($businessId, 'Sales Tax@9.75%', 9.75, $createdBy, false, false);
        $zeroTax = $this->firstOrCreateTaxRate($businessId, 'Zero Tax@0%', 0, $createdBy, false, false);

        $gst = $this->firstOrCreateTaxRate($businessId, 'GST@18%', 18, $createdBy, true, false);
        $gst->sub_taxes()->sync([$cgst->id, $sgst->id]);
        app(TaxUtil::class)->updateGroupTaxAmount($gst->id);
        $gst->refresh();

        // Enable tax on pricing / POS; default to Sales Tax for TN tobacco ops.
        // Keep lot/expiry off so POS cart does not show "Lot & Expiry" for simple tobacco stock.
        $business->enable_price_tax = 1;
        $business->enable_inline_tax = 1;
        $business->enable_lot_number = 0;
        $business->enable_product_expiry = 0;
        if (empty($business->sell_price_tax)) {
            $business->sell_price_tax = 'includes';
        }
        if (empty($business->tax_label_1)) {
            $business->tax_label_1 = 'Tax ID';
        }
        $business->default_sales_tax = $salesTax->id;
        $business->save();

        $common = $business->common_settings ?? [];
        if (! is_array($common)) {
            $common = [];
        }
        $common['msa_default_tax_id'] = $salesTax->id;
        $business->common_settings = $common;
        $business->save();

        return [
            'default_id' => (int) $salesTax->id,
            'rates' => [
                'vat' => $vat,
                'cgst' => $cgst,
                'sgst' => $sgst,
                'igst' => $igst,
                'gst' => $gst,
                'sales_tax' => $salesTax,
                'zero' => $zeroTax,
            ],
        ];
    }

    protected function firstOrCreateTaxRate(
        int $businessId,
        string $name,
        float $amount,
        int $createdBy,
        bool $isTaxGroup = false,
        bool $forTaxGroupOnly = false
    ): TaxRate {
        $tax = TaxRate::withTrashed()
            ->where('business_id', $businessId)
            ->where('name', $name)
            ->first();

        if ($tax && $tax->trashed()) {
            $tax->restore();
        }

        if (! $tax) {
            $tax = TaxRate::create([
                'name' => $name,
                'business_id' => $businessId,
                'amount' => $amount,
                'is_tax_group' => $isTaxGroup ? 1 : 0,
                'for_tax_group' => $forTaxGroupOnly ? 1 : 0,
                'created_by' => $createdBy,
            ]);
        } else {
            $tax->amount = $amount;
            $tax->is_tax_group = $isTaxGroup ? 1 : 0;
            $tax->for_tax_group = $forTaxGroupOnly ? 1 : 0;
            $tax->save();
        }

        return $tax;
    }

    public function ensureWalkInCustomer(int $businessId, CustomerGroup $retailerGroup): void
    {
        $walkIn = Contact::where('business_id', $businessId)
            ->where('is_default', 1)
            ->whereIn('type', ['customer', 'both'])
            ->first();

        if (! $walkIn) {
            return;
        }

        $walkIn->customer_group_id = $retailerGroup->id;
        if (empty($walkIn->custom_field1)) {
            $walkIn->custom_field1 = 'RETAIL';
        }
        if (empty($walkIn->custom_field3)) {
            $walkIn->custom_field3 = 'Y';
        }
        $walkIn->save();
    }

    /**
     * Apply Retail / Wholesale selling-price-group tiers for tobacco products by subcategory.
     * Retail = current sell_price_inc_tax; Wholesale = category-specific % of retail.
     *
     * @param  array{retail: SellingPriceGroup, wholesale: SellingPriceGroup}  $priceGroups
     */
    public function ensurePriceTiersByCategory(int $businessId, int $tobaccoCategoryId, array $priceGroups): void
    {
        $subs = Category::where('business_id', $businessId)
            ->where('parent_id', $tobaccoCategoryId)
            ->get()
            ->keyBy(fn ($c) => strtolower($c->name));

        // Wholesale as fraction of retail sell price, by MSA tobacco subcategory.
        $wholesaleFactors = [
            'cigarettes' => 0.90,
            'moist spit tobacco' => 0.87,
            'cigars' => 0.85,
            'little filtered cigars' => 0.85,
            'e-cigarettes' => 0.83,
            'ryo tobacco' => 0.83,
            'pipe tobacco' => 0.82,
        ];

        $subIds = $subs->pluck('id')->push($tobaccoCategoryId);

        $products = Product::with('variations')
            ->where('business_id', $businessId)
            ->where(function ($q) use ($subIds) {
                $q->whereIn('category_id', $subIds)
                    ->orWhereIn('sub_category_id', $subIds);
            })
            ->get();

        foreach ($products as $product) {
            $subName = '';
            if (! empty($product->sub_category_id)) {
                $match = $subs->firstWhere('id', (int) $product->sub_category_id);
                $subName = strtolower((string) ($match->name ?? ''));
            }

            $factor = $wholesaleFactors[$subName] ?? 0.85;

            foreach ($product->variations as $variation) {
                $retail = (float) ($variation->sell_price_inc_tax ?: $variation->default_sell_price ?: 0);
                if ($retail <= 0) {
                    continue;
                }
                $wholesale = round($retail * $factor, 4);

                VariationGroupPrice::updateOrCreate(
                    [
                        'variation_id' => $variation->id,
                        'price_group_id' => $priceGroups['retail']->id,
                    ],
                    [
                        'price_inc_tax' => $retail,
                        'price_type' => 'fixed',
                    ]
                );

                VariationGroupPrice::updateOrCreate(
                    [
                        'variation_id' => $variation->id,
                        'price_group_id' => $priceGroups['wholesale']->id,
                    ],
                    [
                        'price_inc_tax' => $wholesale,
                        'price_type' => 'fixed',
                    ]
                );
            }
        }
    }

    /**
     * Fill blank MSA fields on tobacco products using subcategory defaults
     * (never force every SKU into Cigarettes / 200 / 003231).
     */
    public function ensureTobaccoProductDefaults(int $businessId, int $tobaccoCategoryId, ?int $defaultTaxId = null): void
    {
        $subs = Category::where('business_id', $businessId)
            ->where('parent_id', $tobaccoCategoryId)
            ->get()
            ->keyBy(fn ($c) => strtolower((string) $c->name));

        $defaultsBySub = [
            'cigarettes' => ['items' => '200', 'code' => '003231'],
            'moist spit tobacco' => ['items' => '5', 'code' => '003211'],
            'cigars' => ['items' => '20', 'code' => '003251'],
            'little filtered cigars' => ['items' => '20', 'code' => '003252'],
            'e-cigarettes' => ['items' => '1', 'code' => '003292'],
            'ryo tobacco' => ['items' => '1', 'code' => '003221'],
            'pipe tobacco' => ['items' => '1', 'code' => '003241'],
            'lighters' => ['items' => '1', 'code' => '003263'],
        ];

        $subIds = $subs->pluck('id')->push($tobaccoCategoryId);

        $products = Product::with('variations')
            ->where('business_id', $businessId)
            ->where(function ($q) use ($subIds) {
                $q->whereIn('category_id', $subIds)
                    ->orWhereIn('sub_category_id', $subIds);
            })
            ->get();

        foreach ($products as $product) {
            $dirty = false;

            $subName = '';
            if (! empty($product->sub_category_id)) {
                $match = $subs->firstWhere('id', (int) $product->sub_category_id);
                $subName = strtolower((string) ($match->name ?? ''));
            }

            // Only fill blanks — never overwrite existing MSA fields / subcategory.
            $defaults = $defaultsBySub[$subName] ?? null;

            $cf1 = trim((string) ($product->product_custom_field1 ?? ''));
            $cf1Digits = preg_replace('/\D+/', '', $cf1);
            // Repair barcode/SKU values wrongly stored as "items per unit"
            if ($cf1 === '' || strlen($cf1Digits) > 3 || (int) $cf1Digits > 250) {
                $product->product_custom_field1 = $defaults['items'] ?? '1';
                $dirty = true;
            }

            $cf2 = preg_replace('/\D+/', '', (string) ($product->product_custom_field2 ?? ''));
            if ($cf2 === '' || strlen($cf2) !== 6 || ! str_starts_with($cf2, '003')) {
                $product->product_custom_field2 = $defaults['code'] ?? '003292';
                $dirty = true;
            }

            if (empty($product->tax) && ! empty($defaultTaxId)) {
                $product->tax = $defaultTaxId;
                if (empty($product->tax_type)) {
                    $product->tax_type = 'inclusive';
                }
                $dirty = true;

                $taxAmount = (float) TaxRate::where('id', $defaultTaxId)->value('amount');
                if ($product->tax_type === 'inclusive' && $taxAmount > 0) {
                    foreach ($product->variations as $variation) {
                        $inc = (float) $variation->sell_price_inc_tax;
                        if ($inc > 0) {
                            $variation->default_sell_price = round($inc / (1 + ($taxAmount / 100)), 4);
                            $variation->save();
                        }
                    }
                }
            }

            if ($dirty) {
                $product->save();
            }
        }

        // Name-based repairs for real catalog imported under wrong category / units
        $this->repairCatalogMsaFields($businessId, $tobaccoCategoryId, $subs);
    }

    /**
     * Reclassify lighters, pack counts (BOX OF N), and strip bad MSA units from barcode CF1.
     *
     * @param  \Illuminate\Support\Collection<string, Category>  $subs
     */
    public function repairCatalogMsaFields(int $businessId, int $tobaccoCategoryId, $subs): void
    {
        $lighterSub = $subs->get('lighters') ?? $subs->first(fn ($c) => strtolower((string) $c->name) === 'lighters');
        $ecigSub = $subs->get('e-cigarettes') ?? $subs->first(fn ($c) => strtolower((string) $c->name) === 'e-cigarettes');

        $products = Product::where('business_id', $businessId)->get();
        foreach ($products as $product) {
            $name = strtolower((string) $product->name);
            $dirty = false;

            // Empty product names break BID alignment — ensure something printable
            if (trim((string) $product->name) === '') {
                $product->name = 'PRODUCT ' . ($product->sku ?: $product->id);
                $dirty = true;
            }

            // Lighters → category 003263 + Lighters subcategory
            if (preg_match('/\b(lighter|lighters|zippo|torch\s*lighter)\b/i', (string) $product->name)
                || (preg_match('/\btorch\b/i', (string) $product->name) && preg_match('/\b(lighter|xu-?\d)/i', (string) $product->name))) {
                if ($lighterSub) {
                    $product->category_id = $tobaccoCategoryId;
                    $product->sub_category_id = $lighterSub->id;
                    $product->product_custom_field2 = '003263';
                    $dirty = true;
                }
            }

            // Pack counts from product name for vapor / accessories
            if (preg_match('/\bbox\s+of\s+(\d{1,3})\b/i', (string) $product->name, $m)
                || preg_match('/\b(\d{1,3})\s*(pc|pcs|pk|pack|packs|ct|count)\b/i', (string) $product->name, $m)) {
                $n = (int) $m[1];
                if ($n >= 1 && $n <= 250) {
                    $product->product_custom_field1 = (string) $n;
                    $dirty = true;
                }
            }

            // Torches that are "BOX OF 20"
            if (preg_match('/\btorch\b/i', (string) $product->name) && preg_match('/\bbox\s+of\s+20\b/i', (string) $product->name)) {
                $product->product_custom_field1 = '20';
                if ($lighterSub) {
                    $product->sub_category_id = $lighterSub->id;
                    $product->product_custom_field2 = '003263';
                }
                $dirty = true;
            }

            // Clear THC etc. CF2 mistagged as cigarette/vapor — mark exclude via CF2 EXCLUDE optional
            if (preg_match('/\b(thc|delta[\s-]?8|delta[\s-]?9|kratom|kanna)\b/i', (string) $product->name)) {
                // Leave products in catalog but exporter skips them; blank mistyped cigarette code
                if (trim((string) $product->product_custom_field2) === '003231') {
                    $product->product_custom_field2 = 'EXCLUDE';
                    $dirty = true;
                }
            }

            // Repair barcode-length "items per unit"
            $cf1 = preg_replace('/\D+/', '', (string) ($product->product_custom_field1 ?? ''));
            if ($cf1 !== '' && (strlen($cf1) > 3 || (int) $cf1 > 250)) {
                $product->product_custom_field1 = '1';
                $dirty = true;
            }

            if ($dirty) {
                $product->save();
            }
        }
    }

    /**
     * Seed sample tobacco SKUs, retailer/wholesale customers, and opening stock (idempotent).
     *
     * @param  array{retail: SellingPriceGroup, wholesale: SellingPriceGroup}  $priceGroups
     * @param  array{retailer: CustomerGroup, wholesale: CustomerGroup}  $customerGroups
     */
    public function ensureDemoSalesData(
        Business $business,
        int $tobaccoCategoryId,
        array $priceGroups,
        array $customerGroups,
        ?int $defaultTaxId = null
    ): void {
        $businessId = (int) $business->id;
        $userId = (int) (User::where('business_id', $businessId)->value('id') ?: 1);
        $unitId = (int) (Unit::where('business_id', $businessId)->value('id') ?: 1);
        $locationId = 1;
        $taxAmount = $defaultTaxId
            ? (float) TaxRate::where('id', $defaultTaxId)->value('amount')
            : 0.0;

        $subs = Category::where('business_id', $businessId)
            ->where('parent_id', $tobaccoCategoryId)
            ->get()
            ->keyBy('name');

        $skuDefs = [
            [
                'name' => 'Lux Cigarettes Carton',
                'sku' => 'TOB-CIG-LUX',
                'sub' => 'Cigarettes',
                'msa_items' => '200',
                'msa_code' => '003231',
                'purchase' => 20,
                'retail' => 25,
                'wholesale' => 22.5,
                'stock' => 100,
            ],
            [
                'name' => 'Marlboro Red Carton',
                'sku' => 'TOB-CIG-MRB',
                'sub' => 'Cigarettes',
                'msa_items' => '200',
                'msa_code' => '003231',
                'purchase' => 55,
                'retail' => 72,
                'wholesale' => 64,
                'stock' => 80,
            ],
            [
                'name' => 'Copenhagen Long Cut',
                'sku' => 'TOB-MST-COP',
                'sub' => 'Moist Spit Tobacco',
                'msa_items' => '5',
                'msa_code' => '003211',
                'purchase' => 28,
                'retail' => 38,
                'wholesale' => 33,
                'stock' => 60,
            ],
            [
                'name' => 'Swisher Sweets Cigarillos',
                'sku' => 'TOB-CGR-SWS',
                'sub' => 'Cigars',
                'msa_items' => '20',
                'msa_code' => '003251',
                'purchase' => 18,
                'retail' => 26,
                'wholesale' => 22,
                'stock' => 50,
            ],
            [
                'name' => 'Cheyenne Little Filtered Cigars',
                'sku' => 'TOB-LFC-CHE',
                'sub' => 'Little Filtered Cigars',
                'msa_items' => '20',
                'msa_code' => '003252',
                'purchase' => 14,
                'retail' => 20,
                'wholesale' => 17,
                'stock' => 50,
            ],
            [
                'name' => 'Juul Menthol Pods',
                'sku' => 'TOB-ECG-JUL',
                'sub' => 'E-Cigarettes',
                'msa_items' => '4',
                'msa_code' => '003292',
                'purchase' => 12,
                'retail' => 18,
                'wholesale' => 15,
                'stock' => 40,
            ],
            [
                'name' => 'Bugler RYO Tobacco',
                'sku' => 'TOB-RYO-BUG',
                'sub' => 'RYO Tobacco',
                'msa_items' => '6',
                'msa_code' => '003221',
                'purchase' => 8,
                'retail' => 12,
                'wholesale' => 10,
                'stock' => 40,
            ],
            [
                'name' => 'Captain Black Pipe Tobacco',
                'sku' => 'TOB-PIP-CBK',
                'sub' => 'Pipe Tobacco',
                'msa_items' => '1',
                'msa_code' => '003241',
                'purchase' => 7,
                'retail' => 11,
                'wholesale' => 9,
                'stock' => 30,
            ],
            [
                'name' => 'Newport Menthol Carton',
                'sku' => 'TOB-CIG-NPT',
                'sub' => 'Cigarettes',
                'msa_items' => '200',
                'msa_code' => '003231',
                'purchase' => 58,
                'retail' => 75,
                'wholesale' => 66,
                'stock' => 70,
            ],
            [
                'name' => 'Camel Blue Carton',
                'sku' => 'TOB-CIG-CAM',
                'sub' => 'Cigarettes',
                'msa_items' => '200',
                'msa_code' => '003231',
                'purchase' => 54,
                'retail' => 70,
                'wholesale' => 62,
                'stock' => 70,
            ],
            [
                'name' => 'Skoal Long Cut Classic',
                'sku' => 'TOB-MST-SKL',
                'sub' => 'Moist Spit Tobacco',
                'msa_items' => '5',
                'msa_code' => '003211',
                'purchase' => 27,
                'retail' => 36,
                'wholesale' => 31,
                'stock' => 55,
            ],
            [
                'name' => 'Backwoods Honey Berry Cigars',
                'sku' => 'TOB-CGR-BWH',
                'sub' => 'Cigars',
                'msa_items' => '20',
                'msa_code' => '003251',
                'purchase' => 22,
                'retail' => 32,
                'wholesale' => 27,
                'stock' => 45,
            ],
            [
                'name' => 'Esco Bars 6ml Disposable Vape Box of 10 - Blue Ras',
                'sku' => 'TOB-ECG-ESC10',
                'sub' => 'E-Cigarettes',
                'msa_items' => '10',
                'msa_code' => '003292',
                'purchase' => 45,
                'retail' => 65,
                'wholesale' => 55,
                'stock' => 80,
            ],
            [
                'name' => 'Xuper XU-49M Torch Lighter Box of 20 CT',
                'sku' => 'TOB-LGT-XU49',
                'sub' => 'Lighters',
                'msa_items' => '20',
                'msa_code' => '003263',
                'purchase' => 16,
                'retail' => 28,
                'wholesale' => 22,
                'stock' => 40,
            ],
            [
                'name' => 'Zippo Lighter Design Buck Wear',
                'sku' => 'TOB-LGT-ZBW',
                'sub' => 'Lighters',
                'msa_items' => '1',
                'msa_code' => '003263',
                'purchase' => 12,
                'retail' => 22,
                'wholesale' => 18,
                'stock' => 35,
            ],
            [
                'name' => 'Zippo Lighter Design Eagle',
                'sku' => 'TOB-LGT-ZEG',
                'sub' => 'Lighters',
                'msa_items' => '1',
                'msa_code' => '003263',
                'purchase' => 12,
                'retail' => 22,
                'wholesale' => 18,
                'stock' => 35,
            ],
        ];

        $productUtil = new ProductUtil();

        // Rename legacy Lux → Lux Cigarettes Carton SKU if still named Lux
        $lux = Product::where('business_id', $businessId)->where('name', 'Lux')->first();
        if ($lux) {
            $lux->name = 'Lux Cigarettes Carton';
            $lux->sku = 'TOB-CIG-LUX';
            $lux->save();
            $variation = $lux->variations()->first();
            if ($variation) {
                $variation->sub_sku = 'TOB-CIG-LUX';
                $variation->save();
            }
        }

        foreach ($skuDefs as $def) {
            $sub = $subs->get($def['sub']);
            if (! $sub) {
                continue;
            }

            $product = Product::where('business_id', $businessId)
                ->where(function ($q) use ($def) {
                    $q->where('sku', $def['sku'])->orWhere('name', $def['name']);
                })
                ->first();

            $sellInc = (float) $def['retail'];
            $sellExc = $taxAmount > 0 ? round($sellInc / (1 + ($taxAmount / 100)), 4) : $sellInc;
            $purchase = (float) $def['purchase'];
            $dppInc = $taxAmount > 0 ? round($purchase * (1 + ($taxAmount / 100)), 4) : $purchase;
            $profit = $purchase > 0 ? round((($sellExc - $purchase) / $purchase) * 100, 4) : 25;

            if (! $product) {
                $product = Product::create([
                    'name' => $def['name'],
                    'business_id' => $businessId,
                    'type' => 'single',
                    'unit_id' => $unitId,
                    'tax' => $defaultTaxId,
                    'tax_type' => 'inclusive',
                    'barcode_type' => 'C128',
                    'sku' => $def['sku'],
                    'category_id' => $tobaccoCategoryId,
                    'sub_category_id' => $sub->id,
                    'enable_stock' => 1,
                    'alert_quantity' => 10,
                    'created_by' => $userId,
                    'product_custom_field1' => $def['msa_items'],
                    'product_custom_field2' => $def['msa_code'],
                ]);
                $productUtil->createSingleProductVariation(
                    $product,
                    $def['sku'],
                    $purchase,
                    $dppInc,
                    $profit,
                    $sellExc,
                    $sellInc
                );
            } else {
                $product->category_id = $tobaccoCategoryId;
                $product->sub_category_id = $sub->id;
                $product->tax = $defaultTaxId ?: $product->tax;
                $product->tax_type = $product->tax_type ?: 'inclusive';
                $product->product_custom_field1 = $product->product_custom_field1 ?: $def['msa_items'];
                $product->product_custom_field2 = $product->product_custom_field2 ?: $def['msa_code'];
                $product->sku = $def['sku'];
                $product->name = $def['name'];
                $product->enable_stock = 1;
                $product->save();
            }

            $product->product_locations()->syncWithoutDetaching([$locationId]);
            $product->load('variations');
            $variation = $product->variations->first();
            if (! $variation) {
                continue;
            }

            VariationGroupPrice::updateOrCreate(
                ['variation_id' => $variation->id, 'price_group_id' => $priceGroups['retail']->id],
                ['price_inc_tax' => $def['retail']]
            );
            VariationGroupPrice::updateOrCreate(
                ['variation_id' => $variation->id, 'price_group_id' => $priceGroups['wholesale']->id],
                ['price_inc_tax' => $def['wholesale']]
            );

            $existingQty = (float) VariationLocationDetails::where('variation_id', $variation->id)
                ->where('location_id', $locationId)
                ->value('qty_available');

            if ($existingQty <= 0 && $def['stock'] > 0) {
                $this->addOpeningStockWithoutAuth(
                    $businessId,
                    $product->load('variations', 'product_tax'),
                    $locationId,
                    (float) $def['stock'],
                    $purchase,
                    $userId
                );
            }
        }

        $this->ensureDemoCustomers($business, $customerGroups, $userId);
        $this->ensureDemoWeekSales($business, $locationId, $userId, $tobaccoCategoryId);
    }

    /**
     * Create a full week of final POS sells to Nashville retailers so MSA SID/PUR export is non-empty.
     * Idempotent via invoice_no prefix MSA-DEMO-{weekEnd}-
     */
    public function ensureDemoWeekSales(
        Business $business,
        int $locationId,
        int $userId,
        int $tobaccoCategoryId
    ): int {
        $businessId = (int) $business->id;
        $weekEnd = Carbon::now()->isSaturday()
            ? Carbon::now()->startOfDay()
            : Carbon::now()->previous(Carbon::SATURDAY)->startOfDay();
        $weekStart = $weekEnd->copy()->subDays(6)->startOfDay();
        $weekTag = $weekEnd->format('Ymd');
        $invoicePrefix = 'MSA-DEMO-'.$weekTag.'-';

        $existing = \App\Transaction::where('business_id', $businessId)
            ->where('type', 'sell')
            ->where('invoice_no', 'like', $invoicePrefix.'%')
            ->count();
        if ($existing > 0) {
            return $existing;
        }

        $customerNames = [
            'Nashville Corner Mart',
            'East Nashville Tobacco',
            'Midtown Market',
            'MR Wholesale Distributor',
        ];
        $customers = Contact::where('business_id', $businessId)
            ->where(function ($q) use ($customerNames) {
                $q->whereIn('supplier_business_name', $customerNames)
                    ->orWhereIn('name', $customerNames);
            })
            ->get();
        if ($customers->isEmpty()) {
            return 0;
        }

        $subIds = Category::where('business_id', $businessId)
            ->where(function ($q) use ($tobaccoCategoryId) {
                $q->where('id', $tobaccoCategoryId)->orWhere('parent_id', $tobaccoCategoryId);
            })
            ->pluck('id')
            ->all();

        $products = Product::with('variations')
            ->where('business_id', $businessId)
            ->where(function ($q) use ($subIds) {
                $q->whereIn('category_id', $subIds)->orWhereIn('sub_category_id', $subIds);
            })
            ->where('enable_stock', 1)
            ->where(function ($q) {
                // Keep monitored categories only in demo sales
                $q->where('name', 'not like', '%THC%')
                    ->where('name', 'not like', '%Delta%')
                    ->where('name', 'not like', '%Kratom%')
                    ->where('name', 'not like', '%Kanna%')
                    ->where('name', 'not like', '%Hemp%');
            })
            ->get()
            ->filter(fn ($p) => $p->variations->isNotEmpty())
            ->values();

        if ($products->isEmpty()) {
            return 0;
        }

        $created = 0;
        $seq = 1;
        $dayIndex = 0;

        foreach ($customers as $customer) {
            // 2 sale days per customer within the MSA week
            for ($d = 0; $d < 2; $d++) {
                $saleDate = $weekStart->copy()->addDays(min(6, $dayIndex % 7))->setTime(10 + $d, 15, 0);
                $dayIndex++;

                // Pick 3–5 SKUs per invoice
                $lines = $products->shuffle()->take(min(5, max(3, $products->count())))->values();
                $total = 0;
                $lineRows = [];
                foreach ($lines as $product) {
                    $variation = $product->variations->first();
                    $qty = in_array(strtolower((string) ($product->product_custom_field2 ?? '')), ['003231'], true)
                        ? rand(2, 8)
                        : rand(1, 12);
                    $price = (float) ($variation->sell_price_inc_tax ?: $variation->default_sell_price ?: 10);
                    $lineTotal = round($price * $qty, 4);
                    $total += $lineTotal;
                    $lineRows[] = compact('product', 'variation', 'qty', 'price', 'lineTotal');
                }

                $invoiceNo = $invoicePrefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
                $seq++;

                $transaction = \App\Transaction::create([
                    'business_id' => $businessId,
                    'location_id' => $locationId,
                    'type' => 'sell',
                    'status' => 'final',
                    'payment_status' => 'paid',
                    'contact_id' => $customer->id,
                    'invoice_no' => $invoiceNo,
                    'transaction_date' => $saleDate->toDateTimeString(),
                    'total_before_tax' => $total,
                    'final_total' => $total,
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                    'created_by' => $userId,
                ]);

                foreach ($lineRows as $row) {
                    \App\TransactionSellLine::create([
                        'transaction_id' => $transaction->id,
                        'product_id' => $row['product']->id,
                        'variation_id' => $row['variation']->id,
                        'quantity' => $row['qty'],
                        'unit_price' => $row['price'],
                        'unit_price_inc_tax' => $row['price'],
                        'unit_price_before_discount' => $row['price'],
                        'item_tax' => 0,
                        'line_discount_type' => 'fixed',
                        'line_discount_amount' => 0,
                    ]);

                    // Reduce stock without hard-failing if map missing
                    try {
                        $vld = VariationLocationDetails::firstOrCreate(
                            [
                                'product_id' => $row['product']->id,
                                'product_variation_id' => $row['variation']->product_variation_id
                                    ?? \App\ProductVariation::where('product_id', $row['product']->id)->value('id'),
                                'variation_id' => $row['variation']->id,
                                'location_id' => $locationId,
                            ],
                            ['qty_available' => 100]
                        );
                        $vld->qty_available = max(0, (float) $vld->qty_available - (float) $row['qty']);
                        $vld->save();
                    } catch (\Throwable $e) {
                        // ignore stock map issues for demo seed
                    }
                }

                // Cash payment row so payment_status stays paid
                try {
                    \App\TransactionPayment::create([
                        'transaction_id' => $transaction->id,
                        'business_id' => $businessId,
                        'amount' => $total,
                        'method' => 'cash',
                        'paid_on' => $saleDate->toDateTimeString(),
                        'created_by' => $userId,
                        'payment_for' => $customer->id,
                    ]);
                } catch (\Throwable $e) {
                    // payment table shape may vary — sale still counts for MSA PUR
                }

                $created++;
            }
        }

        return $created;
    }

    /**
     * Opening stock helper that works from CLI (no auth session).
     */
    protected function addOpeningStockWithoutAuth(
        int $businessId,
        Product $product,
        int $locationId,
        float $qty,
        float $purchasePrice,
        int $userId
    ): void {
        $productUtil = new ProductUtil();
        $taxPercent = ! empty($product->product_tax->amount) ? (float) $product->product_tax->amount : 0;
        $taxId = ! empty($product->product_tax->id) ? $product->product_tax->id : null;
        $itemTax = $productUtil->calc_percentage($purchasePrice, $taxPercent);
        $purchasePriceIncTax = $purchasePrice + $itemTax;
        $variationId = $product->variations->first()->id;
        $purchaseTotal = $purchasePriceIncTax * $qty;

        $purchaseLine = new \App\PurchaseLine();
        $purchaseLine->product_id = $product->id;
        $purchaseLine->variation_id = $variationId;
        $purchaseLine->item_tax = $itemTax;
        $purchaseLine->tax_id = $taxId;
        $purchaseLine->quantity = $qty;
        $purchaseLine->pp_without_discount = $purchasePrice;
        $purchaseLine->purchase_price = $purchasePrice;
        $purchaseLine->purchase_price_inc_tax = $purchasePriceIncTax;
        $purchaseLine->lot_number = null;

        $transaction = \App\Transaction::create([
            'type' => 'opening_stock',
            'opening_stock_product_id' => $product->id,
            'status' => 'received',
            'business_id' => $businessId,
            'transaction_date' => Carbon::now()->toDateTimeString(),
            'total_before_tax' => $purchaseTotal,
            'location_id' => $locationId,
            'final_total' => $purchaseTotal,
            'payment_status' => 'paid',
            'created_by' => $userId,
        ]);
        $transaction->purchase_lines()->save($purchaseLine);

        $productUtil->updateProductQuantity($locationId, $product->id, $variationId, $qty, 0, null, false);
    }

    /**
     * @param  array{retailer: CustomerGroup, wholesale: CustomerGroup}  $customerGroups
     */
    protected function ensureDemoCustomers(Business $business, array $customerGroups, int $userId): void
    {
        $businessId = (int) $business->id;
        $businessUtil = new BusinessUtil();

        $customers = [
            [
                'supplier_business_name' => 'Nashville Corner Mart',
                'name' => 'Nashville Corner Mart',
                'mobile' => '6151110001',
                'address_line_1' => '501 Broadway',
                'city' => 'Nashville',
                'state' => 'TN',
                'zip_code' => '37203',
                'country' => 'USA',
                'group' => 'retailer',
                'cot' => 'RETAIL',
                'cash_carry' => 'N',
                'store_no' => '1',
            ],
            [
                'supplier_business_name' => 'East Nashville Tobacco',
                'name' => 'East Nashville Tobacco',
                'mobile' => '6151110002',
                'address_line_1' => '1220 Gallatin Ave',
                'city' => 'Nashville',
                'state' => 'TN',
                'zip_code' => '37206',
                'country' => 'USA',
                'group' => 'retailer',
                'cot' => 'RETAIL',
                'cash_carry' => 'N',
                'store_no' => '2',
            ],
            [
                'supplier_business_name' => 'Midtown Market',
                'name' => 'Midtown Market',
                'mobile' => '6151110003',
                'address_line_1' => '2100 West End Ave',
                'city' => 'Nashville',
                'state' => 'TN',
                'zip_code' => '37203',
                'country' => 'USA',
                'group' => 'retailer',
                'cot' => 'RETAIL',
                'cash_carry' => 'N',
                'store_no' => '3',
            ],
            [
                'supplier_business_name' => 'MR Wholesale Distributor',
                'name' => 'MR Wholesale Distributor',
                'mobile' => '6151110007',
                'address_line_1' => '300 Industrial Blvd',
                'city' => 'Nashville',
                'state' => 'TN',
                'zip_code' => '37209',
                'country' => 'USA',
                'group' => 'wholesale',
                'cot' => 'DISTRIBUTOR',
                'cash_carry' => 'N',
                'store_no' => '7',
            ],
        ];

        foreach ($customers as $row) {
            $existing = Contact::where('business_id', $businessId)
                ->where(function ($q) use ($row) {
                    $q->where('supplier_business_name', $row['supplier_business_name'])
                        ->orWhere('name', $row['name']);
                })
                ->first();

            $groupId = $row['group'] === 'wholesale'
                ? $customerGroups['wholesale']->id
                : $customerGroups['retailer']->id;

            if (! $existing) {
                $refCount = $businessUtil->setAndGetReferenceCount('contacts', $businessId);
                $contactId = $businessUtil->generateReferenceNumber('contacts', $refCount, $businessId);

                Contact::create([
                    'business_id' => $businessId,
                    'type' => 'customer',
                    'contact_type' => 'business',
                    'supplier_business_name' => $row['supplier_business_name'],
                    'name' => $row['name'],
                    'mobile' => $row['mobile'],
                    'address_line_1' => $row['address_line_1'],
                    'city' => $row['city'],
                    'state' => $row['state'],
                    'zip_code' => $row['zip_code'],
                    'country' => $row['country'],
                    'customer_group_id' => $groupId,
                    'custom_field1' => $row['cot'],
                    'custom_field2' => $row['store_no'],
                    'custom_field3' => $row['cash_carry'],
                    'created_by' => $userId,
                    'contact_id' => $contactId,
                    'credit_limit' => null,
                ]);
            } else {
                $existing->customer_group_id = $groupId;
                $existing->custom_field1 = $existing->custom_field1 ?: $row['cot'];
                $existing->custom_field2 = $existing->custom_field2 ?: $row['store_no'];
                $existing->custom_field3 = $existing->custom_field3 ?: $row['cash_carry'];
                $existing->supplier_business_name = $existing->supplier_business_name ?: $row['supplier_business_name'];
                $existing->address_line_1 = $existing->address_line_1 ?: $row['address_line_1'];
                $existing->city = $existing->city ?: $row['city'];
                $existing->state = $existing->state ?: $row['state'];
                $existing->zip_code = $existing->zip_code ?: $row['zip_code'];
                $existing->country = $existing->country ?: $row['country'];
                $existing->mobile = $existing->mobile ?: $row['mobile'];
                $existing->save();
            }
        }

        // Align any loose named customer into Retailer if still unset
        Contact::where('business_id', $businessId)
            ->where('is_default', 0)
            ->whereIn('type', ['customer', 'both'])
            ->whereNull('customer_group_id')
            ->update([
                'customer_group_id' => $customerGroups['retailer']->id,
                'custom_field1' => 'RETAIL',
                'custom_field3' => 'N',
            ]);
    }
}
