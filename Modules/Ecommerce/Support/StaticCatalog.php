<?php

namespace Modules\Ecommerce\Support;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class StaticCatalog
{
    public static function videos(): Collection
    {
        return collect([
            (object) [
                'title' => 'Wholesale Vape Cloud',
                'subtitle' => 'Disposables, pods, and nic salts for licensed smoke shops',
                'image' => asset('ecom/hero/vape.mp4'),
                'poster' => self::img(17962160),
                'button_text' => 'Shop Vape',
                'button_link' => '/shop/catalog?q=vape',
            ],
            (object) [
                'title' => 'Cigar Lounge Heat',
                'subtitle' => 'Boxes, cigarillos, and humidors — case packed',
                'image' => asset('ecom/hero/cigar-burn.mp4'),
                'poster' => self::img(4110409),
                'button_text' => 'Shop Cigars',
                'button_link' => '/shop/catalog?q=cigar',
            ],
            (object) [
                'title' => 'Roll & Wrap',
                'subtitle' => 'Papers, wraps, and cones for the smoke counter',
                'image' => asset('ecom/hero/cigar-roll.mp4'),
                'poster' => self::img(29474381),
                'button_text' => 'Shop Papers',
                'button_link' => '/shop/catalog?category=5',
            ],
            (object) [
                'title' => 'Hookah Night',
                'subtitle' => 'Stems, bowls, hoses, and charcoal cubes',
                'image' => asset('ecom/hero/hookah.mp4'),
                'poster' => self::img(33698249),
                'button_text' => 'Shop Hookah',
                'button_link' => '/shop/catalog?category=6',
            ],
        ]);
    }

    public static function photos(): array
    {
        return [
            'vape' => self::img(17962160),
            'vape2' => self::img(11698682),
            'vape3' => self::img(3756766),
            'vape4' => self::img(3756768),
            'vape5' => self::img(7404897),
            'vape6' => self::img(2730576),
            'device' => self::img(11698682),
            'liquid' => self::img(16997535),
            'cigar' => self::img(4110409),
            'cigar2' => self::img(1637114),
            'cigar3' => self::img(265791),
            'paper' => self::img(29474381),
            'hookah' => self::img(33698249),
            'flame' => self::img(16997535),
            'acc' => self::img(29474381),
            'humidor' => self::img(1637114),
        ];
    }

    public static function img(int $id): string
    {
        return 'https://images.pexels.com/photos/'.$id.'/pexels-photo-'.$id.'.jpeg?auto=compress&cs=tinysrgb&w=900';
    }

    public static function categories(): Collection
    {
        $names = [
            1 => 'Disposable Vapes',
            2 => 'Vape Devices & Pods',
            3 => 'E-Liquids & Nic Salts',
            4 => 'Cigars & Cigarillos',
            5 => 'Rolling Papers & Wraps',
            6 => 'Hookah & Shisha',
            7 => 'Lighters & Torches',
            8 => 'Smoke Shop Accessories',
        ];

        return collect($names)->map(fn ($name, $id) => (object) [
            'id' => (int) $id,
            'name' => $name,
            'slug' => Str::slug($name),
            'sub_categories' => collect(),
        ])->values();
    }

    public static function brands(): Collection
    {
        $names = [
            1 => 'CloudLine Vapor',
            2 => 'Nightshade Vapes',
            3 => 'Drift Coil Vapes',
            4 => 'Harbor Leaf Cigars',
            5 => 'Ember & Oak Cigars',
            6 => 'Velvet Puff Papers',
            7 => 'Iron Flint Torches',
            8 => 'Ash & Ember Smoke',
        ];

        return collect($names)->map(fn ($name, $id) => (object) [
            'id' => (int) $id,
            'name' => $name,
        ])->values();
    }

    public static function collections(): Collection
    {
        return collect([
            (object) ['name' => 'Vape Essentials', 'slug' => 'vape-essentials', 'description' => 'Disposable vapes, pod kits, and nic salts for licensed smoke shops.', 'ids' => range(1001, 1008)],
            (object) ['name' => 'Cigar Lounge', 'slug' => 'cigar-lounge', 'description' => 'Robusto and Churchill boxes, cigarillos, and humidors.', 'ids' => range(1027, 1034)],
            (object) ['name' => 'Smoke Shop Counter', 'slug' => 'smoke-shop-tools', 'description' => 'Rolling papers, wraps, grinders, torches, and trays.', 'ids' => range(1043, 1050)],
        ]);
    }

    public static function collection(string $slug): ?object
    {
        $col = self::collections()->firstWhere('slug', $slug);
        if (! $col) {
            return null;
        }
        $col->is_active = true;
        $col->products = self::all()->whereIn('id', $col->ids)->values();

        return $col;
    }

    public static function find(int|string $key): ?object
    {
        return self::all()->first(function ($p) use ($key) {
            return (string) $p->id === (string) $key
                || (string) $p->ecom_slug === (string) $key
                || strcasecmp((string) $p->sku, (string) $key) === 0;
        });
    }

    public static function related(object $product, int $limit = 4): Collection
    {
        return self::all()
            ->where('id', '!=', $product->id)
            ->filter(fn ($p) => $p->category_id === $product->category_id || $p->brand_id === $product->brand_id)
            ->take($limit)
            ->values();
    }

    public static function paginate($request, int $perPage = 16): LengthAwarePaginator
    {
        $items = self::filtered($request);
        $page = max(1, (int) $request->get('page', 1));
        $slice = $items->forPage($page, $perPage)->values();

        return new LengthAwarePaginator($slice, $items->count(), $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);
    }

    public static function filtered($request): Collection
    {
        $items = self::all();
        if ($request->filled('category')) {
            $cid = (int) $request->category;
            $items = $items->where('category_id', $cid);
        }
        if ($request->filled('brand')) {
            $items = $items->where('brand_id', (int) $request->brand);
        }
        if ($request->filled('q')) {
            $q = mb_strtolower((string) $request->q);
            $items = $items->filter(fn ($p) => str_contains(mb_strtolower($p->name.' '.$p->sku.' '.$p->brand->name), $q));
        }
        if ($request->filled('min_price')) {
            $items = $items->filter(fn ($p) => $p->ecom_price >= (float) $request->min_price);
        }
        if ($request->filled('max_price')) {
            $items = $items->filter(fn ($p) => $p->ecom_price <= (float) $request->max_price);
        }
        if ($request->boolean('on_sale') || $request->get('sort') === 'sale') {
            $items = $items->filter(fn ($p) => $p->ecom_discount_price && $p->ecom_discount_price < $p->ecom_online_price);
        }
        if ($request->boolean('featured')) {
            $items = $items->filter(fn ($p) => $p->ecom_is_featured);
        }
        if ($request->boolean('new_only')) {
            $items = $items->filter(fn ($p) => $p->ecom_is_new_arrival);
        }

        $sort = $request->get('sort', 'newest');
        $items = match ($sort) {
            'price_asc' => $items->sortBy('ecom_price'),
            'price_desc' => $items->sortByDesc('ecom_price'),
            default => $items->sortByDesc('id'),
        };

        return $items->values();
    }

    public static function search(string $q, int $limit = 12): Collection
    {
        $q = mb_strtolower(trim($q));
        if (mb_strlen($q) < 2) {
            return collect();
        }

        return self::all()
            ->filter(fn ($p) => str_contains(mb_strtolower($p->name.' '.$p->sku.' '.$p->brand->name), $q))
            ->take($limit)
            ->values();
    }

    public static function all(): Collection
    {
        static $hydrated;
        if ($hydrated) {
            return $hydrated;
        }

        $cats = self::categories()->keyBy('id');
        $brands = self::brands()->keyBy('name');
        $photos = self::photos();
        $byCat = [
            'Disposable Vapes' => [$photos['vape'], $photos['vape2'], $photos['vape3'], $photos['vape4'], $photos['vape5'], $photos['vape6']],
            'Vape Devices & Pods' => [$photos['device'], $photos['vape'], $photos['liquid']],
            'E-Liquids & Nic Salts' => [$photos['liquid'], $photos['vape'], $photos['vape3']],
            'Cigars & Cigarillos' => [$photos['cigar'], $photos['cigar2'], $photos['cigar3'], $photos['humidor']],
            'Rolling Papers & Wraps' => [$photos['paper'], $photos['acc']],
            'Hookah & Shisha' => [$photos['hookah'], $photos['cigar3']],
            'Lighters & Torches' => [$photos['flame'], $photos['cigar']],
            'Smoke Shop Accessories' => [$photos['acc'], $photos['humidor'], $photos['paper'], $photos['cigar2']],
        ];

        $brandAliases = [
            'CloudLine' => 'CloudLine Vapor',
            'Nightshade' => 'Nightshade Vapes',
            'Drift Coil' => 'Drift Coil Vapes',
            'Harbor Leaf' => 'Harbor Leaf Cigars',
            'Ember & Oak' => 'Ember & Oak Cigars',
            'Velvet Puff' => 'Velvet Puff Papers',
            'Iron Flint' => 'Iron Flint Torches',
            'Ash & Ember' => 'Ash & Ember Smoke',
        ];
        $catAliases = [
            'Pod Systems & Devices' => 'Vape Devices & Pods',
            'E-Liquids & Salts' => 'E-Liquids & Nic Salts',
            'Papers & Wraps' => 'Rolling Papers & Wraps',
            'Smoking Accessories' => 'Smoke Shop Accessories',
        ];

        $hydrated = collect(self::rows())->values()->map(function ($row, $i) use ($cats, $brands, $byCat, $brandAliases, $catAliases) {
            $id = 1001 + $i;
            $catName = $catAliases[$row['category']] ?? $row['category'];
            $brandName = $brandAliases[$row['brand']] ?? $row['brand'];
            $cat = $cats->firstWhere('name', $catName);
            $brand = $brands[$brandName] ?? (object) ['id' => 0, 'name' => $brandName];
            $pool = $byCat[$catName] ?? [self::img(17962160)];
            $image = $pool[$i % count($pool)];
            $price = (float) $row['price'];
            $sale = isset($row['sale']) ? (float) $row['sale'] : null;
            $sku = sprintf('EDEMO-%02d', $i + 1);
            $variation = (object) ['id' => $id, 'sub_sku' => $sku];

            return (object) [
                'id' => $id,
                'name' => $row['name'],
                'sku' => $sku,
                'ecom_slug' => Str::slug($row['name']).'-'.$id,
                'ecom_short_description' => $row['case'],
                'ecom_long_description' => $row['blurb'],
                'ecom_seo_title' => $row['name'],
                'ecom_seo_description' => $row['blurb'],
                'ecom_is_featured' => ! empty($row['featured']),
                'ecom_is_new_arrival' => ! empty($row['new']),
                'ecom_online_price' => $price,
                'ecom_discount_price' => $sale,
                'ecom_price' => $sale ?: $price,
                'ecom_gallery' => [],
                'image' => $image,
                'image_url' => $image,
                'enable_stock' => 0,
                'category_id' => $cat->id ?? 0,
                'brand_id' => $brand->id ?? 0,
                'category' => $cat,
                'brand' => $brand,
                'unit' => (object) ['short_name' => 'Cs'],
                'variations' => collect([$variation]),
                'product_description' => $row['blurb'],
            ];
        });

        return $hydrated;
    }

    protected static function rows(): array
    {
        return [
            ['name' => 'CloudLine Bar 5000 Frozen Mint', 'brand' => 'CloudLine Vapor', 'category' => 'Disposable Vapes', 'case' => 'CASE: 10CT', 'price' => 89.90, 'featured' => 1, 'blurb' => '5,000 puff disposable, menthol-forward mint. Wholesale 10-count case.'],
            ['name' => 'CloudLine Bar 5000 Blue Razz', 'brand' => 'CloudLine Vapor', 'category' => 'Disposable Vapes', 'case' => 'CASE: 10CT', 'price' => 89.90, 'featured' => 1, 'blurb' => 'Blue raspberry ice disposable bar. Licensed retailers only.'],
            ['name' => 'Nightshade Stick 3500 Tobacco Gold', 'brand' => 'Nightshade Vapes', 'category' => 'Disposable Vapes', 'case' => 'CASE: 10CT', 'price' => 72.00, 'new' => 1, 'blurb' => 'Classic tobacco profile, 3500 puffs, compact stick.'],
            ['name' => 'Nightshade Stick 3500 Mango Ice', 'brand' => 'Nightshade Vapes', 'category' => 'Disposable Vapes', 'case' => 'CASE: 10CT', 'price' => 72.00, 'blurb' => 'Ripe mango with a cooling finish.'],
            ['name' => 'Velvet Puff Nano Watermelon', 'brand' => 'Velvet Puff Papers', 'category' => 'Disposable Vapes', 'case' => 'CASE: 10CT', 'price' => 64.50, 'sale' => 54.90, 'featured' => 1, 'blurb' => 'Compact nano disposable. Watermelon candy.'],
            ['name' => 'Velvet Puff Nano Grape Ice', 'brand' => 'Velvet Puff Papers', 'category' => 'Disposable Vapes', 'case' => 'CASE: 10CT', 'price' => 64.50, 'blurb' => 'Iced grape nano bar.'],
            ['name' => 'Drift Coil 8K Peach', 'brand' => 'Drift Coil Vapes', 'category' => 'Disposable Vapes', 'case' => 'CASE: 5CT', 'price' => 79.00, 'new' => 1, 'blurb' => 'High-puff 8K peach ice, 5-count inner.'],
            ['name' => 'Drift Coil 8K Menthol', 'brand' => 'Drift Coil Vapes', 'category' => 'Disposable Vapes', 'case' => 'CASE: 5CT', 'price' => 79.00, 'blurb' => 'Straight menthol 8K disposable.'],
            ['name' => 'Harbor Mist 600 Classic Tobacco', 'brand' => 'Harbor Leaf Cigars', 'category' => 'Disposable Vapes', 'case' => 'CASE: 20CT', 'price' => 58.00, 'blurb' => 'Small-format 600 puff tobacco, 20-count.'],
            ['name' => 'Harbor Mist 600 Cool Mint', 'brand' => 'Harbor Leaf Cigars', 'category' => 'Disposable Vapes', 'case' => 'CASE: 20CT', 'price' => 58.00, 'blurb' => 'Cool mint 600 puff sleeve pack.'],
            ['name' => 'Drift Coil Pod Kit', 'brand' => 'Drift Coil', 'category' => 'Pod Systems & Devices', 'case' => 'CASE: 8CT', 'price' => 112.00, 'featured' => 1, 'blurb' => 'Refillable pod kit with dual coils. 8 kits per case.'],
            ['name' => 'Drift Coil Replacement Pods 4pk', 'brand' => 'Drift Coil', 'category' => 'Pod Systems & Devices', 'case' => 'CASE: 20CT', 'price' => 96.00, 'blurb' => '4-pack pods, 20 inner boxes.'],
            ['name' => 'CloudLine Aegis Device', 'brand' => 'CloudLine', 'category' => 'Pod Systems & Devices', 'case' => 'CASE: 6CT', 'price' => 138.00, 'new' => 1, 'blurb' => 'Rugged Aegis-style pod mod.'],
            ['name' => 'Nightshade Pod Kit', 'brand' => 'Nightshade', 'category' => 'Pod Systems & Devices', 'case' => 'CASE: 10CT', 'price' => 99.00, 'blurb' => 'Draw-activated pod system, matte black.'],
            ['name' => 'Iron Flint Box Mod', 'brand' => 'Iron Flint', 'category' => 'Pod Systems & Devices', 'case' => 'CASE: 4CT', 'price' => 156.00, 'featured' => 1, 'blurb' => 'Single-battery box mod for rebuildable tanks.'],
            ['name' => 'Drift Mesh Coils 5pk', 'brand' => 'Drift Coil', 'category' => 'Pod Systems & Devices', 'case' => 'CASE: 40CT', 'price' => 84.00, 'blurb' => '0.2Ω mesh coils, 5-pack, 40 inners.'],
            ['name' => 'CloudLine USB-C Dock', 'brand' => 'CloudLine', 'category' => 'Pod Systems & Devices', 'case' => 'CASE: 12CT', 'price' => 48.00, 'blurb' => 'USB-C charging dock for CloudLine bars.'],
            ['name' => 'Velvet Puff Empty Pods', 'brand' => 'Velvet Puff', 'category' => 'Pod Systems & Devices', 'case' => 'CASE: 24CT', 'price' => 52.00, 'blurb' => 'Empty refill pods, 24-count.'],
            ['name' => 'CloudLine Salt 30ml Berry Frost', 'brand' => 'CloudLine', 'category' => 'E-Liquids & Salts', 'case' => 'CASE: 12CT', 'price' => 66.00, 'featured' => 1, 'blurb' => '50mg nic salt, berry frost, 30ml.'],
            ['name' => 'CloudLine Salt 30ml Vanilla Custard', 'brand' => 'CloudLine', 'category' => 'E-Liquids & Salts', 'case' => 'CASE: 12CT', 'price' => 66.00, 'blurb' => 'Dessert nic salt, 30ml bottles.'],
            ['name' => 'Nightshade Freebase 60ml Tobacco Reserve', 'brand' => 'Nightshade', 'category' => 'E-Liquids & Salts', 'case' => 'CASE: 12CT', 'price' => 78.00, 'blurb' => '3mg/6mg tobacco reserve freebase.'],
            ['name' => 'Velvet Puff Salt Strawberry Kiwi', 'brand' => 'Velvet Puff', 'category' => 'E-Liquids & Salts', 'case' => 'CASE: 12CT', 'price' => 62.00, 'sale' => 54.00, 'new' => 1, 'blurb' => 'Fruit salt, 35mg, 30ml.'],
            ['name' => 'Drift Nic Salt Iced Apple', 'brand' => 'Drift Coil', 'category' => 'E-Liquids & Salts', 'case' => 'CASE: 12CT', 'price' => 64.00, 'blurb' => 'Green apple ice nic salt.'],
            ['name' => 'Harbor Leaf Salt Menthol', 'brand' => 'Harbor Leaf', 'category' => 'E-Liquids & Salts', 'case' => 'CASE: 12CT', 'price' => 61.00, 'blurb' => 'Straight menthol nic salt.'],
            ['name' => 'Ember Oak Blend 60ml', 'brand' => 'Ember & Oak', 'category' => 'E-Liquids & Salts', 'case' => 'CASE: 12CT', 'price' => 82.00, 'featured' => 1, 'blurb' => 'Oak-cask tobacco freebase 60ml.'],
            ['name' => 'Nightshade Menthol 30ml', 'brand' => 'Nightshade', 'category' => 'E-Liquids & Salts', 'case' => 'CASE: 12CT', 'price' => 59.00, 'blurb' => 'Ice-cold menthol salt.'],
            ['name' => 'Ember & Oak Robusto 25ct Box', 'brand' => 'Ember & Oak', 'category' => 'Cigars & Cigarillos', 'case' => 'CASE: 4 BOX', 'price' => 168.00, 'featured' => 1, 'blurb' => 'Handmade robusto, 25-count boxes, 4 per case.'],
            ['name' => 'Ember & Oak Churchill 20ct', 'brand' => 'Ember & Oak', 'category' => 'Cigars & Cigarillos', 'case' => 'CASE: 4 BOX', 'price' => 186.00, 'blurb' => 'Churchill format, Connecticut wrapper.'],
            ['name' => 'Harbor Leaf Maduro Toro 25ct', 'brand' => 'Harbor Leaf', 'category' => 'Cigars & Cigarillos', 'case' => 'CASE: 4 BOX', 'price' => 174.00, 'new' => 1, 'blurb' => 'Dark maduro toro, boxed 25s.'],
            ['name' => 'Harbor Leaf Connecticut Corona', 'brand' => 'Harbor Leaf', 'category' => 'Cigars & Cigarillos', 'case' => 'CASE: 5 BOX', 'price' => 142.00, 'blurb' => 'Mild corona, Connecticut shade.'],
            ['name' => 'Ash & Ember Cigarillos Sweet 50ct', 'brand' => 'Ash & Ember', 'category' => 'Cigars & Cigarillos', 'case' => 'CASE: 10CT', 'price' => 98.00, 'featured' => 1, 'sale' => 88.00, 'blurb' => 'Sweet-tip cigarillos, 50-count tins.'],
            ['name' => 'Ash & Ember Cigarillos Natural 50ct', 'brand' => 'Ash & Ember', 'category' => 'Cigars & Cigarillos', 'case' => 'CASE: 10CT', 'price' => 98.00, 'blurb' => 'Natural leaf cigarillos, 50-count.'],
            ['name' => 'Velvet Leaf Natural Wraps', 'brand' => 'Velvet Puff', 'category' => 'Cigars & Cigarillos', 'case' => 'CASE: 25CT', 'price' => 44.00, 'blurb' => 'Natural leaf wraps, 2-pack inners.'],
            ['name' => 'Harbor Cigar Sampler 8-Pack', 'brand' => 'Harbor Leaf', 'category' => 'Cigars & Cigarillos', 'case' => 'CASE: 12CT', 'price' => 126.00, 'new' => 1, 'blurb' => 'Mixed sampler of 8 cigars.'],
            ['name' => 'Velvet Papers King Size 50ct', 'brand' => 'Velvet Puff', 'category' => 'Papers & Wraps', 'case' => 'CASE: 50CT', 'price' => 36.00, 'blurb' => 'King size rolling papers, 50 booklets.'],
            ['name' => 'Velvet Papers 1.25 50ct', 'brand' => 'Velvet Puff', 'category' => 'Papers & Wraps', 'case' => 'CASE: 50CT', 'price' => 32.00, 'blurb' => '1.25 papers, 50 booklets.'],
            ['name' => 'Harbor Hemp Wraps 25ct', 'brand' => 'Harbor Leaf', 'category' => 'Papers & Wraps', 'case' => 'CASE: 25CT', 'price' => 41.00, 'featured' => 1, 'blurb' => 'Hemp wraps, unsweetened.'],
            ['name' => 'Nightshade Cones 100ct', 'brand' => 'Nightshade', 'category' => 'Papers & Wraps', 'case' => 'CASE: 10CT', 'price' => 55.00, 'new' => 1, 'blurb' => 'Pre-rolled cones, 100-count jars.'],
            ['name' => 'Ember Hookah Stem Set', 'brand' => 'Ember & Oak', 'category' => 'Hookah & Shisha', 'case' => 'CASE: 4CT', 'price' => 164.00, 'featured' => 1, 'blurb' => 'Stainless stem + base set.'],
            ['name' => 'Harbor Shisha Bowl', 'brand' => 'Harbor Leaf', 'category' => 'Hookah & Shisha', 'case' => 'CASE: 12CT', 'price' => 48.00, 'blurb' => 'Clay phunnel bowl, 12-count.'],
            ['name' => 'CloudLine Hookah Hose', 'brand' => 'CloudLine', 'category' => 'Hookah & Shisha', 'case' => 'CASE: 12CT', 'price' => 54.00, 'blurb' => 'Silicone hose with washable core.'],
            ['name' => 'Nightshade Charcoal Cubes 1kg', 'brand' => 'Nightshade', 'category' => 'Hookah & Shisha', 'case' => 'CASE: 10CT', 'price' => 62.00, 'blurb' => 'Coconut cube charcoal, 1kg flats.'],
            ['name' => 'Iron Flint Triple Torch', 'brand' => 'Iron Flint', 'category' => 'Lighters & Torches', 'case' => 'CASE: 12CT', 'price' => 86.00, 'featured' => 1, 'blurb' => 'Triple-jet table torch.'],
            ['name' => 'Iron Flint Soft Flame', 'brand' => 'Iron Flint', 'category' => 'Lighters & Torches', 'case' => 'CASE: 25CT', 'price' => 49.00, 'blurb' => 'Refillable soft-flame metal lighter.'],
            ['name' => 'Ash & Ember Stick Lighter 50ct', 'brand' => 'Ash & Ember', 'category' => 'Lighters & Torches', 'case' => 'CASE: 50CT', 'price' => 28.00, 'blurb' => 'Disposable stick lighters, 50-count.'],
            ['name' => 'Nightshade Dual Torch Mini', 'brand' => 'Nightshade', 'category' => 'Lighters & Torches', 'case' => 'CASE: 16CT', 'price' => 72.00, 'new' => 1, 'blurb' => 'Pocket dual-flame torch.'],
            ['name' => 'Nightshade 4-Piece Grinder', 'brand' => 'Nightshade', 'category' => 'Smoking Accessories', 'case' => 'CASE: 12CT', 'price' => 76.00, 'featured' => 1, 'blurb' => 'Aircraft aluminum 4-piece grinder.'],
            ['name' => 'Nightshade Rolling Tray', 'brand' => 'Nightshade', 'category' => 'Smoking Accessories', 'case' => 'CASE: 20CT', 'price' => 58.00, 'blurb' => 'Metal rolling tray, branded.'],
            ['name' => 'Drift Coil Tool Kit', 'brand' => 'Drift Coil', 'category' => 'Smoking Accessories', 'case' => 'CASE: 10CT', 'price' => 67.00, 'new' => 1, 'blurb' => 'Coil jig, snips, ceramic tweezers.'],
            ['name' => 'Harbor Humidor 25ct', 'brand' => 'Harbor Leaf', 'category' => 'Smoking Accessories', 'case' => 'CASE: 2CT', 'price' => 188.00, 'featured' => 1, 'blurb' => 'Desktop cedar humidor, 25-count.'],
        ];
    }
}
