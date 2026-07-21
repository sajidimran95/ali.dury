<?php

namespace Modules\Ecommerce\Services;

use App\Product;
use App\VariationLocationDetails;
use Modules\Ecommerce\Entities\EcommerceSetting;

class InventoryGuard
{
    public function availableQty(int $product_id, int $variation_id, int $location_id): float
    {
        $product = Product::find($product_id);
        if (! $product || ! $product->enable_stock) {
            return 999999;
        }

        $row = VariationLocationDetails::where('product_id', $product_id)
            ->where('variation_id', $variation_id)
            ->where('location_id', $location_id)
            ->first();

        return $row ? (float) $row->qty_available : 0.0;
    }

    public function assertAvailable(int $product_id, int $variation_id, int $location_id, float $qty): void
    {
        $available = $this->availableQty($product_id, $variation_id, $location_id);
        if ($qty > $available) {
            throw new \RuntimeException(__('ecommerce::lang.insufficient_stock'));
        }
    }

    public function locationId(int $business_id): ?int
    {
        $settings = EcommerceSetting::forBusiness($business_id);

        return $settings?->location_id;
    }
}
