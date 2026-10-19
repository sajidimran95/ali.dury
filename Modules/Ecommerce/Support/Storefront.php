<?php

namespace Modules\Ecommerce\Support;

use App\Category;
use Illuminate\Support\Collection;

class Storefront
{
    public static function gsTopCategoryNames(): array
    {
        return [
            'Candy', 'General Merchandise', 'Health & Beauty', 'King Palm', 'Novelty', 'Ooze',
            'Papers & Cones', 'Smoking Accessories', 'Snacks & Drinks', 'Tobacco', 'Vape Devices',
        ];
    }

    public static function gsNavCategoryNames(): array
    {
        return [
            'Candy', 'Snacks & Drinks', 'Tobacco', 'Papers & Cones',
            'General Merchandise', 'Health & Beauty', 'Vape Devices',
            'Smoking Accessories', 'Novelty',
        ];
    }

    public static function gsCategoryIds(int $businessId): array
    {
        $tops = Category::query()
            ->where('business_id', $businessId)
            ->whereIn('name', self::gsTopCategoryNames())
            ->pluck('id');

        return Category::query()
            ->where('business_id', $businessId)
            ->where(function ($q) use ($tops) {
                $q->whereIn('id', $tops)->orWhereIn('parent_id', $tops);
            })
            ->pluck('id')
            ->unique()
            ->values()
            ->all();
    }

    public static function isGsCategory(?Category $category): bool
    {
        if (! $category) {
            return false;
        }
        $names = self::gsTopCategoryNames();
        if (in_array($category->name, $names, true)) {
            return true;
        }
        if ($category->parent_id) {
            $parentName = Category::query()->where('id', $category->parent_id)->value('name');

            return in_array($parentName, $names, true);
        }

        return false;
    }

    public static function placeholder(): string
    {
        return asset('/img/default.png');
    }

    public static function photo(string $id): string
    {
        $rel = 'uploads/img/greencloud/'.$id.'.jpg';

        return file_exists(public_path($rel)) ? asset($rel) : self::placeholder();
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

    public static function videos(): Collection
    {
        $mediaFile = storage_path('app/ecom_gs_media.json');
        $media = is_file($mediaFile) ? (json_decode(file_get_contents($mediaFile), true) ?: []) : [];
        $slides = array_values(array_filter(array_merge(
            $media['sliders'] ?? [],
            $media['banners'] ?? [],
            $media['tetra'] ?? []
        )));
        if ($slides) {
            return collect($slides)->unique()->take(5)->values()->map(function ($url, $i) {
                $isVideo = (bool) preg_match('/\.(mp4|webm|ogg)(\?|$)/i', (string) $url);

                return (object) [
                    'title' => 'G&S Wholesale '.($i + 1),
                    'image' => $url,
                    'poster' => $url,
                    'is_video' => $isVideo,
                ];
            });
        }

        return collect([
            (object) [
                'title' => 'Wholesale Vape Cloud',
                'image' => asset('ecom/hero/vape.mp4'),
                'poster' => self::placeholder(),
                'is_video' => true,
            ],
            (object) [
                'title' => 'Cigar Lounge Heat',
                'image' => asset('ecom/hero/cigar-burn.mp4'),
                'poster' => self::placeholder(),
                'is_video' => true,
            ],
            (object) [
                'title' => 'Roll & Wrap',
                'image' => asset('ecom/hero/cigar-roll.mp4'),
                'poster' => self::placeholder(),
                'is_video' => true,
            ],
            (object) [
                'title' => 'Hookah Night',
                'image' => asset('ecom/hero/hookah.mp4'),
                'poster' => self::placeholder(),
                'is_video' => true,
            ],
        ]);
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
