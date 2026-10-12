<?php

namespace Modules\Ecommerce\Support;

class Storefront
{
    public static function photo(string $id): string
    {
        if (ctype_digit($id) || is_numeric($id)) {
            return StaticCatalog::img((int) $id);
        }
        $rel = 'uploads/img/greencloud/'.$id.'.jpg';

        return file_exists(public_path($rel)) ? asset($rel) : StaticCatalog::img(17962160);
    }

    public static function heroPhotos(): array
    {
        return ['17962160', '4110409', '1637114', '33698249', '29474381', '16997535', '3756766', '7404897'];
    }

    public static function hub(?string $slug): ?array
    {
        $hubs = [
            'vape-nicotine' => [
                'kicker' => 'Vape',
                'title' => 'Vape & Nicotine',
                'lede' => 'Disposables, pods, nic salts, and devices for licensed smoke shops. 21+ wholesale only.',
                'image' => StaticCatalog::img(17962160),
            ],
            'devices' => [
                'kicker' => 'Hardware',
                'title' => 'Vape Devices & Pods',
                'lede' => 'Pod kits, box mods, mesh coils, and charging docks — case packed for retailers.',
                'image' => StaticCatalog::img(11698682),
            ],
            'accessories' => [
                'kicker' => 'Smoke shop',
                'title' => 'Papers, Flame & Trays',
                'lede' => 'Rolling papers, wraps, cones, grinders, lighters, and trays for the smoke counter.',
                'image' => StaticCatalog::img(29474381),
            ],
            'lifestyle' => [
                'kicker' => 'Cigar',
                'title' => 'Cigar Lounge',
                'lede' => 'Robustos, Churchills, maduros, cigarillos, and humidors.',
                'image' => StaticCatalog::img(1637114),
            ],
            'thc-delta' => [
                'kicker' => 'Hookah',
                'title' => 'Hookah & Shisha',
                'lede' => 'Stems, clay bowls, hoses, and coconut charcoal cubes.',
                'image' => StaticCatalog::img(33698249),
            ],
        ];

        return $hubs[$slug] ?? null;
    }
}
