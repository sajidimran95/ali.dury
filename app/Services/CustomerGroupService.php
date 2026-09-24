<?php

namespace App\Services;

use App\CustomerGroup;
use App\SellingPriceGroup;
use App\Variation;
use App\VariationGroupPrice;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CustomerGroupService
{
    /**
     * Rename legacy Retail / Wholesale labels to Group A / Group B.
     */
    public function renameLegacyLabels(int $businessId): void
    {
        $this->renameRecord(SellingPriceGroup::class, $businessId, ['Retail', 'Retailer'], 'Group A');
        $this->renameRecord(SellingPriceGroup::class, $businessId, ['Wholesale'], 'Group B');
        $this->renameRecord(CustomerGroup::class, $businessId, ['Retailer', 'Retail'], 'Group A');
        $this->renameRecord(CustomerGroup::class, $businessId, ['Wholesale'], 'Group B');
    }

    /**
     * Group A = -10% on all products, Group B = -5% on all products.
     */
    public function ensureDefaultGroupRates(int $businessId): void
    {
        $this->applyPercentToNamedGroup($businessId, 'Group A', -10);
        $this->applyPercentToNamedGroup($businessId, 'Group B', -5);
    }

    public function applyPercentToGroup(CustomerGroup $customerGroup, float $percent, bool $updateAllProductPrices = false): int
    {
        $customerGroup->price_calculation_type = 'percentage';
        $customerGroup->amount = $percent;
        if (empty($customerGroup->inventory_slug)) {
            $customerGroup->inventory_slug = 'all_inventory';
        }
        $customerGroup->save();

        if (! $updateAllProductPrices) {
            return 0;
        }

        $priceGroup = $this->ensurePriceGroup($customerGroup);

        return $this->writeProductPricesFromPercent(
            (int) $customerGroup->business_id,
            (int) $priceGroup->id,
            $percent
        );
    }

    /**
     * Write group prices from % for all products, or filtered by category / subcategory / variation ids.
     */
    public function writeProductPricesFromPercent(
        int $businessId,
        int $priceGroupId,
        float $percent,
        ?int $categoryId = null,
        ?int $subCategoryId = null,
        array $variationIds = []
    ): int {
        $updated = 0;
        $now = now();
        $factor = 1 + ($percent / 100);

        $query = Variation::query()
            ->select(['variations.id', 'variations.sell_price_inc_tax'])
            ->whereHas('product', function ($q) use ($businessId, $categoryId, $subCategoryId) {
                $q->where('business_id', $businessId)
                    ->where('is_inactive', 0)
                    ->where('not_for_selling', 0)
                    ->whereIn('type', ['single', 'variable']);
                if ($categoryId) {
                    $q->where('category_id', $categoryId);
                }
                if ($subCategoryId) {
                    $q->where('sub_category_id', $subCategoryId);
                }
            });

        if (! empty($variationIds)) {
            $query->whereIn('variations.id', $variationIds);
        }

        $query->orderBy('variations.id')
            ->chunkById(500, function ($variations) use ($priceGroupId, $factor, $now, &$updated) {
                $ids = $variations->pluck('id')->all();
                $existing = VariationGroupPrice::where('price_group_id', $priceGroupId)
                    ->whereIn('variation_id', $ids)
                    ->pluck('id', 'variation_id');

                $insert = [];
                $updateIds = [];
                $priceById = [];
                foreach ($variations as $variation) {
                    $price = round(((float) ($variation->sell_price_inc_tax ?? 0)) * $factor, 4);
                    if (isset($existing[$variation->id])) {
                        $rowId = (int) $existing[$variation->id];
                        $updateIds[] = $rowId;
                        $priceById[$rowId] = $price;
                    } else {
                        $insert[] = [
                            'variation_id' => $variation->id,
                            'price_group_id' => $priceGroupId,
                            'price_inc_tax' => $price,
                            'price_type' => 'fixed',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                    $updated++;
                }
                if (! empty($insert)) {
                    VariationGroupPrice::insert($insert);
                }
                if (! empty($updateIds)) {
                    $cases = '';
                    $bindings = [];
                    foreach ($priceById as $rowId => $price) {
                        $cases .= ' WHEN ? THEN ?';
                        $bindings[] = $rowId;
                        $bindings[] = $price;
                    }
                    $idPlaceholders = implode(',', array_fill(0, count($updateIds), '?'));
                    $bindings[] = $now;
                    $bindings = array_merge($bindings, $updateIds);
                    DB::update(
                        "UPDATE variation_group_prices SET price_inc_tax = CASE id{$cases} END, price_type = 'fixed', updated_at = ? WHERE id IN ({$idPlaceholders})",
                        $bindings
                    );
                }
            });

        return $updated;
    }

    public function writeAllProductPricesFromPercent(int $businessId, int $priceGroupId, float $percent): int
    {
        return $this->writeProductPricesFromPercent($businessId, $priceGroupId, $percent);
    }

    /**
     * Ensure the customer group has a linked selling price group (same name).
     */
    public function ensurePriceGroup(CustomerGroup $customerGroup): SellingPriceGroup
    {
        $businessId = (int) $customerGroup->business_id;

        if (! empty($customerGroup->selling_price_group_id)) {
            $existing = SellingPriceGroup::withTrashed()
                ->where('business_id', $businessId)
                ->find($customerGroup->selling_price_group_id);
            // Only reuse if it belongs to this customer group (same name).
            if ($existing && $existing->name === $customerGroup->name) {
                if ($existing->trashed()) {
                    $existing->restore();
                } elseif ((int) $existing->is_active !== 1) {
                    $existing->is_active = 1;
                    $existing->save();
                }

                return $existing;
            }
        }

        $priceGroup = SellingPriceGroup::withTrashed()
            ->where('business_id', $businessId)
            ->where('name', $customerGroup->name)
            ->first();

        if ($priceGroup && $priceGroup->trashed()) {
            $priceGroup->restore();
        }

        if (! $priceGroup) {
            $priceGroup = SellingPriceGroup::create([
                'name' => $customerGroup->name,
                'description' => $customerGroup->name.' prices',
                'business_id' => $businessId,
                'is_active' => 1,
            ]);
        } else {
            $priceGroup->is_active = 1;
            $priceGroup->save();
        }

        $this->grantPriceGroupPermission($businessId, (int) $priceGroup->id);

        $customerGroup->selling_price_group_id = $priceGroup->id;
        $customerGroup->save();

        return $priceGroup;
    }

    protected function grantPriceGroupPermission(int $businessId, int $priceGroupId): void
    {
        $permission = Permission::firstOrCreate([
            'name' => 'selling_price_group.'.$priceGroupId,
            'guard_name' => 'web',
        ]);

        $adminRole = Role::where('name', 'Admin#'.$businessId)->where('guard_name', 'web')->first();
        if ($adminRole && ! $adminRole->hasPermissionTo($permission)) {
            $adminRole->givePermissionTo($permission);
        }
    }

    protected function renameRecord(string $modelClass, int $businessId, array $fromNames, string $toName): void
    {
        $target = $modelClass::where('business_id', $businessId)->where('name', $toName)->first();
        if ($target) {
            return;
        }

        $legacy = $modelClass::where('business_id', $businessId)
            ->whereIn('name', $fromNames)
            ->first();

        if ($legacy) {
            $legacy->name = $toName;
            $legacy->save();
        }
    }

    protected function applyPercentToNamedGroup(int $businessId, string $name, float $percent): void
    {
        $group = CustomerGroup::where('business_id', $businessId)->where('name', $name)->first();
        if (! $group) {
            return;
        }

        $already = $group->price_calculation_type === 'percentage' && (float) $group->amount == $percent;
        $hasPrices = ! empty($group->selling_price_group_id)
            && VariationGroupPrice::where('price_group_id', $group->selling_price_group_id)->exists();

        if ($already && $hasPrices) {
            return;
        }

        $this->applyPercentToGroup($group, $percent, true);
    }
}
