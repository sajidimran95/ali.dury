<?php

namespace Modules\Ecommerce\Support;

class Storefront
{
    public static function photo(string $id): string
    {
        $rel = 'uploads/img/greencloud/'.$id.'.jpg';

        return file_exists(public_path($rel)) ? asset($rel) : asset('img/default.png');
    }

    public static function heroPhotos(): array
    {
        return [
            'photo-1558618666-fcd25c85cd64',
            'photo-1519671482749-fd09be7ccebf',
            'photo-1511689660979-10d2b1aada49',
            'photo-1461532257246-777de18cd58b',
            'photo-1556742049-0cfed4f6a45d',
        ];
    }

    public static function hub(?string $slug): ?array
    {
        $hubs = [
            'vape-nicotine' => [
                'kicker' => 'Nicotine',
                'title' => 'Vape & Nicotine',
                'lede' => 'Disposables, pods, salts, and devices for licensed Wisconsin retailers. 21+ and PACT Act shipping rules apply.',
                'image' => self::photo('photo-1558618666-fcd25c85cd64'),
            ],
            'devices' => [
                'kicker' => 'Hardware',
                'title' => 'Smoking Devices & Supplies',
                'lede' => 'Disposable vapes, dry herb vaporizers, dab rigs, and pod systems — in-stock for licensed retailers.',
                'image' => self::photo('photo-1518770660439-4636190af475'),
            ],
            'accessories' => [
                'kicker' => 'Tools',
                'title' => 'Smoking Accessories & Tools',
                'lede' => 'Papers, glass, grinders, lighters, trays, wraps, and dab tools for smoke shops.',
                'image' => self::photo('photo-1563906267088-b029e7101114'),
            ],
            'lifestyle' => [
                'kicker' => 'Specialty',
                'title' => 'Lifestyle & Specialty',
                'lede' => 'Hemp wellness, CBD, artistic glass, crystals, and specialty goods.',
                'image' => self::photo('photo-1518531933037-91b2f5f229cc'),
            ],
            'thc-delta' => [
                'kicker' => 'Hemp',
                'title' => 'THC / Delta Products',
                'lede' => 'Farm Bill hemp SKUs with batch COAs. Restricted states apply — verify before you order.',
                'image' => self::photo('photo-1511689660979-10d2b1aada49'),
            ],
        ];

        return $hubs[$slug] ?? null;
    }
}
