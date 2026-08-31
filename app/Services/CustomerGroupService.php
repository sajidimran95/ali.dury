<?php

namespace App\Services;

use App\CustomerGroup;
use App\SellingPriceGroup;
use App\Variation;
use App\VariationGroupPrice;
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

    public function applyPercentToGroup(CustomerGroup $customerGroup, float $percent, bool $updateAllProductPrices = true): int
    {
        $customerGroup->price_calculation_type = 'percentage';
        $customerGroup->amount = $percent;
        $customerGroup->inventory_slug = 'all_inventory';
        $customerGroup->save();

        if (! $updateAllProductPrices && ! empty($customerGroup->selling_price_group_id)) {
            VariationGroupPrice::where('price_group_id', $customerGroup->selling_price_group_id)->delete();
        }

        $updated = 0;
        if ($updateAllProductPrices) {
            $priceGroup = $this->ensurePriceGroup($customerGroup);
            $updated = $this->writeAllProductPricesFromPercent(
                (int) $customerGroup->business_id,
                (int) $priceGroup->id,
                $percent
            );
        }

        $updated = 0;
        if ($updateAllProductPrices) {
            $priceGroup = $this->ensurePriceGroup($customerGroup);
            $updated = $this->writeAllProductPricesFromPercent(
                (int) $customerGroup->business_id,
                (int) $priceGroup->id,
                $percent
            );
        }

        return $updated;
    }

    public function writeAllProductPricesFromPercent(int $businessId, int $priceGroupId, float $percent): int
    {
        $updated = 0;
        $now = now();
        $factor = 1 + ($percent / 100);

        Variation::query()
            ->select(['variations.id', 'variations.sell_price_inc_tax'])
            ->whereHas('product', function ($q) use ($businessId) {
                $q->where('business_id', $businessId);
            })
            ->orderBy('variations.id')
            ->chunkById(500, function ($variations) use ($priceGroupId, $factor, $now, &$updated) {
                $ids = $variations->pluck('id')->all();
                $existing = VariationGroupPrice::where('price_group_id', $priceGroupId)
                    ->whereIn('variation_id', $ids)
                    ->pluck('id', 'variation_id');

                $insert = [];
                foreach ($variations as $variation) {
                    $price = round(((float) ($variation->sell_price_inc_tax ?? 0)) * $factor, 4);
                    if (isset($existing[$variation->id])) {
                        VariationGroupPrice::where('id', $existing[$variation->id])
                            ->update(['price_inc_tax' => $price, 'price_type' => 'fixed', 'updated_at' => $now]);
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
            });

        return $updated;
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
            if ($existing) {
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
