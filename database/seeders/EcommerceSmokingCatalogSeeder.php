<?php

namespace Database\Seeders;

use App\Brands;
use App\Category;
use App\Product;
use App\ProductVariation;
use App\Unit;
use App\User;
use App\Variation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Ecommerce\Entities\EcommerceSetting;

class EcommerceSmokingCatalogSeeder extends Seeder
{
    public const SKU_PREFIX = 'ECOMV-';

    public function run(): void
    {
        $businessId = (int) (EcommerceSetting::where('is_enabled', 1)->value('business_id') ?: 1);
        $unitId = (int) (Unit::where('business_id', $businessId)->value('id') ?: Unit::query()->value('id') ?: 1);
        $userId = (int) (User::query()->value('id') ?: 1);

        Product::where('business_id', $businessId)
            ->whereIn('sales_channel', ['pos_ecommerce', 'ecommerce_only'])
            ->where('sku', 'not like', self::SKU_PREFIX.'%')
            ->update(['sales_channel' => 'pos_only']);

        $hasShowInPos = Schema::hasColumn('categories', 'show_in_pos');
        $catIds = [];
        $covers = [];
        $imgDir = public_path('uploads/img');
        File::ensureDirectoryExists($imgDir);

        foreach ($this->categories() as $row) {
            $slug = 'ecom-'.Str::slug($row['name']);
            $plainSlug = Str::slug($row['name']);
            $cat = Category::withTrashed()
                ->where('business_id', $businessId)
                ->where(function ($q) {
                    $q->whereNull('parent_id')->orWhere('parent_id', 0);
                })
                ->where(function ($q) use ($row, $slug, $plainSlug) {
                    $q->where('slug', $slug)
                        ->orWhere('slug', $plainSlug)
                        ->orWhere('short_code', $slug)
                        ->orWhere('name', $row['name']);
                })
                ->orderByRaw('CASE WHEN slug = ? THEN 0 WHEN slug = ? THEN 1 ELSE 2 END', [$slug, $plainSlug])
                ->orderBy('id')
                ->first();
            $payload = [
                'name' => $row['name'],
                'business_id' => $businessId,
                'short_code' => $slug,
                'parent_id' => 0,
                'created_by' => $userId,
                'category_type' => 'product',
                'slug' => $slug,
                'description' => $row['blurb'],
            ];
            if ($hasShowInPos) {
                $payload['show_in_pos'] = 0;
            }
            if ($cat) {
                if ($cat->trashed()) {
                    $cat->restore();
                }
                $cat->fill($payload)->save();
            } else {
                $cat = Category::create($payload);
            }
            $catIds[$row['name']] = $this->mergeDuplicateCategories($businessId, $row['name'], $cat->id);
            $cat = Category::find($catIds[$row['name']]);

            $filename = 'ecom-cat-'.$cat->id.'.jpg';
            $this->saveUnsplashFile($row['image'], $imgDir.'/'.$filename);
            $covers[$cat->id] = $filename;
        }

        File::put(storage_path('app/ecom_category_covers.json'), json_encode($covers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $greenDir = public_path('uploads/img/greencloud');
        File::ensureDirectoryExists($greenDir);
        foreach (\Modules\Ecommerce\Support\Storefront::heroPhotos() as $i => $photoId) {
            $src = $this->categories()[$i % count($this->categories())]['image'];
            $this->saveUnsplashFile($src, $greenDir.'/'.$photoId.'.jpg');
        }

        $brandIds = [];
        foreach ($this->brands() as $name) {
            $brand = Brands::withTrashed()
                ->where('business_id', $businessId)
                ->where('name', $name)
                ->first();
            if ($brand) {
                if ($brand->trashed()) {
                    $brand->restore();
                }
                if (Schema::hasColumn('brands', 'show_in_pos')) {
                    $brand->show_in_pos = 0;
                    $brand->save();
                }
            } else {
                $payload = [
                    'business_id' => $businessId,
                    'name' => $name,
                    'created_by' => $userId,
                ];
                if (Schema::hasColumn('brands', 'show_in_pos')) {
                    $payload['show_in_pos'] = 0;
                }
                $brand = Brands::create($payload);
            }
            $brandIds[$name] = $brand->id;
        }

        $created = 0;
        foreach ($this->uniqueProducts() as $i => $row) {
            $sku = sprintf(self::SKU_PREFIX.'%03d', $i + 1);
            $product = Product::where('business_id', $businessId)->where('sku', $sku)->first();
            $payload = [
                'name' => $row['name'],
                'business_id' => $businessId,
                'type' => 'single',
                'unit_id' => $unitId,
                'brand_id' => $brandIds[$row['brand']] ?? null,
                'category_id' => $catIds[$row['category']] ?? null,
                'tax_type' => 'inclusive',
                'enable_stock' => 0,
                'alert_quantity' => 0,
                'sku' => $sku,
                'barcode_type' => 'C128',
                'created_by' => $userId,
                'not_for_selling' => 0,
                'is_inactive' => 0,
                'sales_channel' => 'ecommerce_only',
                'ecom_slug' => Str::slug($row['name']).'-'.($i + 1),
                'ecom_short_description' => $row['case'],
                'ecom_long_description' => $row['blurb'],
                'ecom_is_featured' => ! empty($row['featured']),
                'ecom_is_new_arrival' => ! empty($row['new']),
                'ecom_online_price' => $row['price'],
                'ecom_discount_price' => $row['sale'] ?? null,
                'image' => $row['image'],
                'product_description' => $row['blurb'],
            ];
            if ($product) {
                $product->fill($payload)->save();
            } else {
                $product = Product::create($payload);
                $created++;
            }

            $pv = ProductVariation::firstOrCreate(
                ['product_id' => $product->id, 'is_dummy' => 1],
                ['name' => 'DUMMY', 'variation_template_id' => null]
            );
            $variation = Variation::withTrashed()->where('product_id', $product->id)->first();
            $vData = [
                'name' => 'DUMMY',
                'product_id' => $product->id,
                'sub_sku' => $sku,
                'product_variation_id' => $pv->id,
                'default_purchase_price' => round($row['price'] * 0.72, 2),
                'dpp_inc_tax' => round($row['price'] * 0.72, 2),
                'profit_percent' => 38.89,
                'default_sell_price' => $row['price'],
                'sell_price_inc_tax' => $row['price'],
            ];
            if ($variation) {
                if ($variation->trashed()) {
                    $variation->restore();
                }
                $variation->fill($vData)->save();
            } else {
                Variation::create($vData);
            }
        }

        $this->command?->info("Ecommerce smoking catalog ready. New products: {$created}. Total SKUs: 300. Existing POS products left as pos_only.");
    }

    protected function categories(): array
    {
        $u = fn (string $id) => $this->unsplash($id, 800);

        return [
            ['name' => 'Disposable Vapes', 'blurb' => 'High-puff bars for licensed smoke shops.', 'image' => $u('photo-1715613814529-939dcaaa33d3')],
            ['name' => 'Vape Devices & Pods', 'blurb' => 'Pod kits, mods, and coils.', 'image' => $u('photo-1742934753099-0e39c6fd120a')],
            ['name' => 'E-Liquids & Nic Salts', 'blurb' => 'Salts and freebase in case packs.', 'image' => $u('photo-1708326481491-c89676ebdca5')],
            ['name' => 'Cigars', 'blurb' => 'Robusto, Churchill, and boxed cigars.', 'image' => $u('photo-1631227852854-7c0fac3c9aeb')],
            ['name' => 'Cigarillos', 'blurb' => 'Sweet-tip and natural leaf cigarillos.', 'image' => $u('photo-1749842893762-d94717e28a9f')],
            ['name' => 'Rolling Papers & Wraps', 'blurb' => 'Papers, hemp wraps, and cones.', 'image' => $u('photo-1515697203610-d0513edf9cb4')],
            ['name' => 'Hookah & Shisha', 'blurb' => 'Stems, bowls, hoses, and charcoal.', 'image' => $u('photo-1574751749605-0f1e41b6cf67')],
            ['name' => 'Lighters & Torches', 'blurb' => 'Soft flame, jet, and table torches.', 'image' => $u('photo-1674907261987-16698edb4650')],
            ['name' => 'Grinders & Trays', 'blurb' => 'Grinders and rolling trays.', 'image' => $u('photo-1742934753099-0e39c6fd120a')],
            ['name' => 'Smoke Shop Accessories', 'blurb' => 'Humidors, tips, tools, and trays.', 'image' => $u('photo-1768224949425-8c317d30e2d4')],
        ];
    }

    protected function brands(): array
    {
        return [
            'CloudLine Vapor', 'Nightshade Vapes', 'Drift Coil Vapes', 'Harbor Leaf Cigars',
            'Ember & Oak Cigars', 'Velvet Puff Papers', 'Iron Flint Torches', 'Ash & Ember Smoke',
            'Lost Coast Clouds', 'Black Ember Supply',
        ];
    }

    protected function photos(string $category): array
    {
        $u = fn (string $id) => $this->unsplash($id, 900);
        $map = [
            'Disposable Vapes' => ['photo-1715613814529-939dcaaa33d3', 'photo-1708326481491-c89676ebdca5', 'photo-1558618666-fcd25c85cd64'],
            'Vape Devices & Pods' => ['photo-1742934753099-0e39c6fd120a', 'photo-1715613814529-939dcaaa33d3', 'photo-1578662996442-48f60103fc96'],
            'E-Liquids & Nic Salts' => ['photo-1708326481491-c89676ebdca5', 'photo-1715613814529-939dcaaa33d3', 'photo-1551024601-bec78aea704b'],
            'Cigars' => ['photo-1631227852854-7c0fac3c9aeb', 'photo-1768224949425-8c317d30e2d4', 'photo-1749842893762-d94717e28a9f', 'photo-1501612164070-9919a55f7563'],
            'Cigarillos' => ['photo-1749842893762-d94717e28a9f', 'photo-1631227852854-7c0fac3c9aeb', 'photo-1768224949425-8c317d30e2d4'],
            'Rolling Papers & Wraps' => ['photo-1515697203610-d0513edf9cb4', 'photo-1742934753099-0e39c6fd120a'],
            'Hookah & Shisha' => ['photo-1574751749605-0f1e41b6cf67', 'photo-1587740851725-3d3e64e9f19e', 'photo-1574238752695-675b86d49267', 'photo-1635547821500-77542481940c', 'photo-1511302188604-d1d5ba206381'],
            'Lighters & Torches' => ['photo-1674907261987-16698edb4650', 'photo-1705865045357-e52cbcb187df', 'photo-1519671482749-fd09be7ccebf'],
            'Grinders & Trays' => ['photo-1742934753099-0e39c6fd120a', 'photo-1515697203610-d0513edf9cb4'],
            'Smoke Shop Accessories' => ['photo-1768224949425-8c317d30e2d4', 'photo-1631227852854-7c0fac3c9aeb', 'photo-1674907261987-16698edb4650'],
        ];
        $ids = $map[$category] ?? ['photo-1578662996442-48f60103fc96'];

        return array_map($u, $ids);
    }

    protected function products(): array
    {
        $flavors = [
            'Frozen Mint', 'Blue Razz', 'Mango Ice', 'Watermelon', 'Grape Ice', 'Peach', 'Menthol',
            'Tobacco Gold', 'Strawberry Kiwi', 'Iced Apple', 'Berry Frost', 'Vanilla Custard',
            'Cool Mint', 'Classic Tobacco', 'Pineapple Ice', 'Lychee', 'Banana Ice', 'Cola',
            'Sour Apple', 'Dragonfruit', 'Cucumber Mint', 'Lemon Ice', 'Coffee', 'Creamy Toffee',
        ];
        $brands = $this->brands();
        $rows = [];

        $push = function (array $row) use (&$rows) {
            $rows[] = $row;
        };

        $i = 0;
        foreach (['Bar 5000' => 89.9, 'Stick 3500' => 72.0, 'Nano 2K' => 64.5, 'Cloud 8K' => 79.0] as $line => $price) {
            foreach ($flavors as $flavor) {
                if ($i >= 80) {
                    break 2;
                }
                $brand = $brands[$i % 3];
                $push($this->row(
                    $brand.' '.$line.' '.$flavor,
                    $brand,
                    'Disposable Vapes',
                    'CASE: 10CT',
                    $price,
                    'High-puff disposable, '.$flavor.'. Wholesale 10-count case.',
                    $i
                ));
                $i++;
            }
        }

        $deviceNames = [
            'Pod Kit', 'Replacement Pods 4pk', 'Aegis Device', 'Draw Pod System', 'Box Mod',
            'Mesh Coils 5pk', 'USB-C Dock', 'Empty Pods', 'Rugged AIO', 'Tank Kit',
        ];
        for ($n = 0; $n < 40; $n++) {
            $brand = $brands[$n % 3 === 0 ? 2 : $n % 3];
            $push($this->row(
                $brand.' '.$deviceNames[$n % count($deviceNames)].($n > 9 ? ' '.($n + 1) : ''),
                $brand,
                'Vape Devices & Pods',
                'CASE: 8CT',
                48 + ($n % 12) * 9,
                'Hardware for licensed retailers. Case packed.',
                $n
            ));
        }

        $liquids = ['Salt 30ml', 'Freebase 60ml', 'Nic Salt Ice', 'Dessert Blend', 'Menthol Salt'];
        for ($n = 0; $n < 40; $n++) {
            $brand = $brands[$n % 10];
            $push($this->row(
                $brand.' '.$liquids[$n % 5].' '.$flavors[$n % count($flavors)],
                $brand,
                'E-Liquids & Nic Salts',
                'CASE: 12CT',
                54 + ($n % 8) * 4,
                'Bottle packed nic salt / freebase. 12-count case.',
                $n
            ));
        }

        $cigars = ['Robusto 25ct Box', 'Churchill 20ct', 'Maduro Toro 25ct', 'Connecticut Corona', 'Sampler 8-Pack'];
        for ($n = 0; $n < 40; $n++) {
            $brand = $n % 2 ? 'Harbor Leaf Cigars' : 'Ember & Oak Cigars';
            $push($this->row(
                $brand.' '.$cigars[$n % 5].($n > 4 ? ' #'.($n + 1) : ''),
                $brand,
                'Cigars',
                'CASE: 4 BOX',
                142 + ($n % 10) * 6,
                'Handmade cigars, boxed for wholesale.',
                $n
            ));
        }

        for ($n = 0; $n < 20; $n++) {
            $kind = $n % 2 ? 'Sweet 50ct' : 'Natural 50ct';
            $push($this->row(
                'Ash & Ember Smoke Cigarillos '.$kind.($n > 1 ? ' '.($n + 1) : ''),
                'Ash & Ember Smoke',
                'Cigarillos',
                'CASE: 10CT',
                88 + $n,
                'Cigarillos, 50-count tins.',
                $n
            ));
        }

        $papers = ['King Size 50ct', '1.25 50ct', 'Hemp Wraps 25ct', 'Cones 100ct', 'Unbleached 50ct'];
        for ($n = 0; $n < 25; $n++) {
            $brand = $n % 2 ? 'Velvet Puff Papers' : 'Nightshade Vapes';
            $push($this->row(
                $brand.' '.$papers[$n % 5].($n > 4 ? ' '.($n + 1) : ''),
                $brand,
                'Rolling Papers & Wraps',
                'CASE: 50CT',
                32 + ($n % 6) * 3,
                'Papers, wraps, and cones for the smoke counter.',
                $n
            ));
        }

        $hookah = ['Stem Set', 'Clay Bowl', 'Silicone Hose', 'Charcoal Cubes 1kg', 'Base Glass'];
        for ($n = 0; $n < 20; $n++) {
            $brand = $brands[3 + ($n % 3)];
            $push($this->row(
                $brand.' '.$hookah[$n % 5].($n > 4 ? ' '.($n + 1) : ''),
                $brand,
                'Hookah & Shisha',
                'CASE: 4CT',
                48 + ($n % 9) * 12,
                'Hookah hardware and charcoal for retailers.',
                $n
            ));
        }

        $flame = ['Triple Torch', 'Soft Flame', 'Stick Lighter 50ct', 'Dual Torch Mini', 'Table Jet'];
        for ($n = 0; $n < 15; $n++) {
            $brand = $n % 2 ? 'Iron Flint Torches' : 'Nightshade Vapes';
            $push($this->row(
                $brand.' '.$flame[$n % 5].($n > 4 ? ' '.($n + 1) : ''),
                $brand,
                'Lighters & Torches',
                'CASE: 12CT',
                28 + ($n % 8) * 7,
                'Flame for cigars and the smoke counter.',
                $n
            ));
        }

        for ($n = 0; $n < 10; $n++) {
            $kind = $n % 2 ? '4-Piece Grinder' : 'Rolling Tray';
            $push($this->row(
                'Nightshade Vapes '.$kind.($n > 1 ? ' '.($n + 1) : ''),
                'Nightshade Vapes',
                'Grinders & Trays',
                'CASE: 12CT',
                58 + $n * 2,
                'Counter grinders and trays.',
                $n
            ));
        }

        $acc = ['Humidor 25ct', 'Cotton Tips 200ct', 'Tool Kit', 'Cigar Cutter', 'Ashtray 6ct'];
        for ($n = 0; $n < 10; $n++) {
            $brand = $brands[7 + ($n % 3)];
            $push($this->row(
                $brand.' '.$acc[$n % 5].($n > 4 ? ' '.($n + 1) : ''),
                $brand,
                'Smoke Shop Accessories',
                'CASE: 6CT',
                34 + $n * 14,
                'Smoke shop counter accessories.',
                $n
            ));
        }

        return array_slice($rows, 0, 300);
    }

    protected function mergeDuplicateCategories(int $businessId, string $name, int $keepId): int
    {
        $dupes = Category::withTrashed()
            ->where('business_id', $businessId)
            ->where('name', $name)
            ->where('id', '!=', $keepId)
            ->where(function ($q) {
                $q->whereNull('parent_id')->orWhere('parent_id', 0);
            })
            ->get();

        foreach ($dupes as $dupe) {
            Product::where('category_id', $dupe->id)->update(['category_id' => $keepId]);
            Product::where('sub_category_id', $dupe->id)->update(['sub_category_id' => $keepId]);
            $dupe->forceDelete();
        }

        return $keepId;
    }

    protected function uniqueProducts(): array
    {
        $seen = [];
        $rows = [];
        foreach ($this->products() as $row) {
            $name = $row['name'];
            $n = 2;
            while (isset($seen[mb_strtolower($name)])) {
                $name = $row['name'].' '.$n;
                $n++;
            }
            $seen[mb_strtolower($name)] = true;
            $row['name'] = $name;
            $rows[] = $row;
        }

        return $rows;
    }

    protected function saveUnsplashFile(string $url, string $dest): void
    {
        $bytes = @file_get_contents($url);
        if ($bytes !== false && $bytes !== '') {
            File::put($dest, $bytes);
        }
    }

    protected function unsplash(string $id, int $width = 900): string
    {
        return 'https://images.unsplash.com/'.$id.'?auto=format&fit=crop&w='.$width.'&q=80';
    }

    protected function row(string $name, string $brand, string $category, string $case, float $price, string $blurb, int $n): array
    {
        $photos = $this->photos($category);
        $row = [
            'name' => trim($name),
            'brand' => $brand,
            'category' => $category,
            'case' => $case,
            'price' => round($price, 2),
            'blurb' => $blurb,
            'image' => $photos[$n % count($photos)],
        ];
        if ($n % 15 === 0) {
            $row['featured'] = 1;
        }
        if ($n % 8 === 1) {
            $row['new'] = 1;
        }
        if ($n % 12 === 3) {
            $row['sale'] = round($price * 0.88, 2);
        }

        return $row;
    }
}
