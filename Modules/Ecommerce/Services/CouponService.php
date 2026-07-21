<?php

namespace Modules\Ecommerce\Services;

use Modules\Ecommerce\Entities\EcomCoupon;

class CouponService
{
    public function findValid(int $business_id, string $code, float $subtotal): EcomCoupon
    {
        $coupon = EcomCoupon::where('business_id', $business_id)
            ->where('code', $code)
            ->where('is_active', 1)
            ->first();

        if (! $coupon) {
            throw new \RuntimeException(__('ecommerce::lang.invalid_coupon'));
        }

        if ($coupon->starts_at && now()->lt($coupon->starts_at)) {
            throw new \RuntimeException(__('ecommerce::lang.invalid_coupon'));
        }
        if ($coupon->expires_at && now()->gt($coupon->expires_at)) {
            throw new \RuntimeException(__('ecommerce::lang.coupon_expired'));
        }
        if (! is_null($coupon->usage_limit) && $coupon->used_count >= $coupon->usage_limit) {
            throw new \RuntimeException(__('ecommerce::lang.coupon_limit_reached'));
        }
        if (! is_null($coupon->min_order_amount) && $subtotal < $coupon->min_order_amount) {
            throw new \RuntimeException(__('ecommerce::lang.coupon_min_order', ['amount' => $coupon->min_order_amount]));
        }

        return $coupon;
    }

    public function discountAmount(EcomCoupon $coupon, float $subtotal): float
    {
        if ($coupon->type === 'percentage') {
            return round($subtotal * ((float) $coupon->value) / 100, 2);
        }

        return min((float) $coupon->value, $subtotal);
    }
}
