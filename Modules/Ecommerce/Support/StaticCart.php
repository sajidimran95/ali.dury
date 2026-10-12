<?php

namespace Modules\Ecommerce\Support;

use Illuminate\Support\Collection;

class StaticCart
{
    public const KEY = 'ecom_static_cart';

    public static function lines(): array
    {
        return session(self::KEY, []);
    }

    public static function add(int $productId, float $qty = 1): void
    {
        $lines = self::lines();
        $lines[$productId] = ($lines[$productId] ?? 0) + $qty;
        session([self::KEY => $lines]);
    }

    public static function setQty(int $productId, float $qty): void
    {
        $lines = self::lines();
        if ($qty <= 0) {
            unset($lines[$productId]);
        } else {
            $lines[$productId] = $qty;
        }
        session([self::KEY => $lines]);
    }

    public static function remove(int $productId): void
    {
        $lines = self::lines();
        unset($lines[$productId]);
        session([self::KEY => $lines]);
    }

    public static function clear(): void
    {
        session()->forget(self::KEY);
    }

    public static function items(): Collection
    {
        $out = collect();
        foreach (self::lines() as $id => $qty) {
            $product = StaticCatalog::find((int) $id);
            if (! $product) {
                continue;
            }
            $out->push((object) [
                'id' => (int) $id,
                'product_id' => (int) $id,
                'quantity' => (float) $qty,
                'unit_price' => (float) $product->ecom_price,
                'product' => $product,
            ]);
        }

        return $out;
    }

    public static function cart(): object
    {
        $items = self::items();

        return (object) [
            'items' => $items,
            'coupon_code' => session('ecom_static_coupon'),
        ];
    }

    public static function totals(): array
    {
        $items = self::items();
        $count = (int) $items->sum('quantity');
        $subtotal = (float) $items->sum(fn ($i) => $i->quantity * $i->unit_price);

        return [
            'count' => $count,
            'subtotal' => $subtotal,
            'shipping' => $subtotal >= 500 ? 0 : ($count ? 12.0 : 0),
            'tax' => round($subtotal * 0.05, 2),
            'discount' => 0,
            'total' => $subtotal + ($subtotal >= 500 ? 0 : ($count ? 12.0 : 0)) + round($subtotal * 0.05, 2),
        ];
    }
}
