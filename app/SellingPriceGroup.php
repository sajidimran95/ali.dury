<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SellingPriceGroup extends Model
{
    use SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    public function scopeActive($query)
    {
        return $query->where('selling_price_groups.is_active', 1);
    }

    /**
     * Return list of selling price groups
     *
     * @param  int  $business_id
     * @return array
     */
    public static function forDropdown($business_id, $with_default = true)
    {
        $price_groups = SellingPriceGroup::where('business_id', $business_id)
                                    ->active()
                                    ->get();

        $dropdown = [];

        if ($with_default && auth()->user()->can('access_default_selling_price')) {
            $dropdown[0] = __('lang_v1.default_selling_price');
        }

        foreach ($price_groups as $price_group) {
            if (auth()->user()->can('selling_price_group.'.$price_group->id)) {
                $dropdown[$price_group->id] = $price_group->name;
            }
        }

        return $dropdown;
    }

    /**
     * Counts total number of selling price groups
     *
     * @param  int  $business_id
     * @return array
     */
    public static function countSellingPriceGroups($business_id)
    {
        $count = SellingPriceGroup::where('business_id', $business_id)
                                ->active()
                                ->count();

        return $count;
    }

    /**
     * Seed every inventory variation into a price group using each item's
     * default sell price (shared inventory — only prices differ per group).
     */
    public static function seedAllInventoryPrices(int $business_id, int $price_group_id): int
    {
        $seeded = 0;
        $now = now();

        Variation::query()
            ->select(['variations.id', 'variations.sell_price_inc_tax'])
            ->whereHas('product', function ($q) use ($business_id) {
                $q->where('business_id', $business_id);
            })
            ->orderBy('variations.id')
            ->chunkById(500, function ($variations) use ($price_group_id, $now, &$seeded, $business_id) {
                $ids = $variations->pluck('id')->all();
                $existing = VariationGroupPrice::where('price_group_id', $price_group_id)
                    ->whereIn('variation_id', $ids)
                    ->pluck('variation_id')
                    ->all();
                $existingLookup = array_flip($existing);
                $factor = static::groupPriceFactor($business_id, $price_group_id);

                $batch = [];
                foreach ($variations as $variation) {
                    if (isset($existingLookup[$variation->id])) {
                        continue;
                    }
                    $batch[] = [
                        'variation_id' => $variation->id,
                        'price_group_id' => $price_group_id,
                        'price_inc_tax' => round(((float) ($variation->sell_price_inc_tax ?? 0)) * $factor, 4),
                        'price_type' => 'fixed',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if (! empty($batch)) {
                    VariationGroupPrice::insert($batch);
                    $seeded += count($batch);
                }
            });

        return $seeded;
    }

    /**
     * Ensure a single variation has a price row in every active price group.
     */
    public static function ensureVariationInAllGroups(int $business_id, $variation): void
    {
        if (! is_object($variation)) {
            $variation = Variation::find($variation);
        }
        if (empty($variation)) {
            return;
        }

        $group_ids = static::where('business_id', $business_id)
            ->active()
            ->pluck('id');

        if ($group_ids->isEmpty()) {
            return;
        }

        $base = (float) ($variation->sell_price_inc_tax ?? 0);

        foreach ($group_ids as $group_id) {
            $factor = static::groupPriceFactor($business_id, (int) $group_id);
            VariationGroupPrice::firstOrCreate(
                [
                    'variation_id' => $variation->id,
                    'price_group_id' => $group_id,
                ],
                [
                    'price_inc_tax' => round($base * $factor, 4),
                    'price_type' => 'fixed',
                ]
            );
        }
    }

    /**
     * 1 + (customer group calculation % / 100). Missing % means default selling price.
     */
    public static function groupPriceFactor(int $business_id, int $price_group_id): float
    {
        $percent = CustomerGroup::where('business_id', $business_id)
            ->where('selling_price_group_id', $price_group_id)
            ->where('price_calculation_type', 'percentage')
            ->value('amount');

        if ($percent === null || $percent === '') {
            return 1.0;
        }

        return 1 + ((float) $percent / 100);
    }
}
