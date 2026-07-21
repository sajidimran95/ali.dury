<?php

namespace Modules\Ecommerce\Services;

use Modules\Ecommerce\Entities\EcomTaxRule;

class TaxCalculator
{
    public function calculate(int $business_id, float $subtotal, ?string $state = null, ?string $city = null): float
    {
        $rate = 0.0;

        $rules = EcomTaxRule::where('business_id', $business_id)->where('is_active', 1)->get();

        foreach ($rules as $rule) {
            if ($rule->level === 'state' && $state && strcasecmp($rule->state ?? '', $state) === 0) {
                $rate += (float) $rule->rate;
            }
            if ($rule->level === 'city' && $city && strcasecmp($rule->city ?? '', $city) === 0) {
                $rate += (float) $rule->rate;
            }
            if ($rule->level === 'county' && $state && strcasecmp($rule->state ?? '', $state) === 0) {
                $rate += (float) $rule->rate;
            }
        }

        return round($subtotal * $rate / 100, 2);
    }
}
