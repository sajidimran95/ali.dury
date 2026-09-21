<?php

namespace App\Services;

use App\Product;
use Carbon\Carbon;

class SkuInactivityService
{
    public const INACTIVITY_DAYS = 180;

    /**
     * Record a POS scan and return whether the cashier should be prompted
     * to retain or archive the SKU.
     *
     * @return array{prompt: bool, product_id: int|null, sku: string|null, days: int|null, product_name: string|null}
     */
    public function recordScanAndCheckPrompt(?int $productId): array
    {
        $empty = [
            'prompt' => false,
            'product_id' => null,
            'sku' => null,
            'days' => null,
            'product_name' => null,
        ];

        if (empty($productId)) {
            return $empty;
        }

        $product = Product::find($productId);
        if (empty($product) || (int) $product->is_inactive === 1) {
            return $empty;
        }

        $now = Carbon::now();
        $lastScanned = $product->last_scanned_at ? Carbon::parse($product->last_scanned_at) : null;

        // Never scanned: use created_at as activity baseline when available.
        $baseline = $lastScanned ?: ($product->created_at ? Carbon::parse($product->created_at) : null);
        $days = $baseline ? $baseline->diffInDays($now) : self::INACTIVITY_DAYS;
        $shouldPrompt = $baseline ? $days >= self::INACTIVITY_DAYS : true;

        $product->last_scanned_at = $now;
        $product->save();

        if (! $shouldPrompt) {
            return $empty;
        }

        return [
            'prompt' => true,
            'product_id' => (int) $product->id,
            'sku' => $product->sku,
            'days' => (int) $days,
            'product_name' => $product->name,
        ];
    }

    /**
     * Retain SKU: keep active and reset inactivity clock (another 180 days).
     */
    public function retain(int $productId, int $businessId): Product
    {
        $product = Product::where('business_id', $businessId)->findOrFail($productId);
        $product->is_inactive = 0;
        $product->last_scanned_at = Carbon::now();
        $product->save();

        return $product;
    }

    /**
     * Archive SKU: mark inactive so it leaves normal POS selling.
     */
    public function archive(int $productId, int $businessId): Product
    {
        $product = Product::where('business_id', $businessId)->findOrFail($productId);
        $product->is_inactive = 1;
        $product->not_for_selling = 1;
        $product->save();

        return $product;
    }
}
