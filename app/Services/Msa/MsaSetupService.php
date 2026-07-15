<?php

namespace App\Services\Msa;

use App\Business;
use App\Category;
use App\Contact;
use App\CustomerGroup;
use App\Product;
use App\SellingPriceGroup;
use App\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MsaSetupService
{
    /**
     * Create/restore Tobacco MSA categories, labels, retail/wholesale groups, and defaults.
     *
     * @return int Tobacco parent category id
     */
    public function setupForBusiness(Business $business): int
    {
        $parentId = $this->ensureCategories((int) $business->id);
        $priceGroups = $this->ensurePriceGroups((int) $business->id);
        $customerGroups = $this->ensureCustomerGroups((int) $business->id, $priceGroups);
        $this->ensureCustomLabels($business);
        $this->ensureCommonSettings($business, $parentId, $priceGroups, $customerGroups);
        $this->ensureWalkInCustomer((int) $business->id, $customerGroups['retailer']);
        $this->ensureTobaccoProductDefaults((int) $business->id, $parentId);

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
            'dropdown_options' => "200\n5\n10\n20\n25\n40\n60\n100\n250",
        ];
        $labels['product_cf_details'][2] = [
            'type' => 'dropdown',
            'dropdown_options' => "003231\n003211\n003251\n003252\n003292\n003221\n003241\n003213\n003212\n003214\n003215\n003227",
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
     * Fill blank MSA fields on tobacco products (e.g. Lux → Cigarettes / 200 / 003231).
     */
    public function ensureTobaccoProductDefaults(int $businessId, int $tobaccoCategoryId): void
    {
        $cigaretteId = Category::where('business_id', $businessId)
            ->where('parent_id', $tobaccoCategoryId)
            ->where('name', 'Cigarettes')
            ->value('id');

        if (! $cigaretteId) {
            return;
        }

        $subIds = Category::where('business_id', $businessId)
            ->where('parent_id', $tobaccoCategoryId)
            ->pluck('id')
            ->push($tobaccoCategoryId);

        $products = Product::where('business_id', $businessId)
            ->where(function ($q) use ($subIds) {
                $q->whereIn('category_id', $subIds)
                    ->orWhereIn('sub_category_id', $subIds);
            })
            ->get();

        foreach ($products as $product) {
            $dirty = false;

            if (empty($product->sub_category_id) && (int) $product->category_id === $tobaccoCategoryId) {
                $product->sub_category_id = $cigaretteId;
                $dirty = true;
            }

            if ($product->product_custom_field1 === null || $product->product_custom_field1 === '') {
                $product->product_custom_field1 = '200';
                $dirty = true;
            }

            if ($product->product_custom_field2 === null || $product->product_custom_field2 === '') {
                $product->product_custom_field2 = '003231';
                $dirty = true;
            }

            if ($dirty) {
                $product->save();
            }
        }
    }
}
