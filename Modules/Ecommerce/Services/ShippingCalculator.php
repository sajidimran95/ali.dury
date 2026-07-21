<?php

namespace Modules\Ecommerce\Services;

use Modules\Ecommerce\Entities\EcomShippingRule;

class ShippingCalculator
{
    public function calculate(int $business_id, float $subtotal, ?string $state = null, float $weight = 0): float
    {
        $rules = EcomShippingRule::where('business_id', $business_id)
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get();

        if ($rules->isEmpty()) {
            return 0;
        }

        foreach ($rules as $rule) {
            if ($rule->type === 'free') {
                if (is_null($rule->min_order_amount) || $subtotal >= $rule->min_order_amount) {
                    return 0;
                }
            }

            if ($rule->type === 'flat') {
                if (is_null($rule->min_order_amount) || $subtotal >= $rule->min_order_amount) {
                    return (float) $rule->amount;
                }
            }

            if ($rule->type === 'weight') {
                if (is_null($rule->max_weight) || $weight <= $rule->max_weight) {
                    return (float) $rule->amount;
                }
            }

            if ($rule->type === 'state' && $state) {
                $states = $rule->states ?? [];
                if (in_array(strtoupper($state), array_map('strtoupper', $states), true)) {
                    return (float) $rule->amount;
                }
            }
        }

        return (float) ($rules->first()->amount ?? 0);
    }
}
