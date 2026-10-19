<?php

namespace Database\Seeders;

use App\Brands;
use App\BusinessLocation;
use App\Category;
use App\Product;
use App\ProductVariation;
use App\Unit;
use App\User;
use App\Variation;
use App\VariationLocationDetails;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Ecommerce\Entities\EcommerceSetting;

class EcommerceSmokingCatalogSeeder extends Seeder
{
    public const SKU_PREFIX = 'GS-';

    public const OLD_SKU_PREFIX = 'ECOMV-';

    public function run(): void
    {
        $businessId = (int) (EcommerceSetting::where('is_enabled', 1)->value('business_id') ?: 1);
        $unitId = (int) (Unit::where('business_id', $businessId)->value('id') ?: Unit::query()->value('id') ?: 1);
        $userId = (int) (User::query()->value('id') ?: 1);
        $catalog = $this->catalog();

        Product::where('business_id', $businessId)
            ->whereIn('sales_channel', ['pos_ecommerce', 'ecommerce_only'])
            ->where('sku', 'not like', self::SKU_PREFIX.'%')
            ->update(['sales_channel' => 'pos_only']);

        Product::where('business_id', $businessId)
            ->where('sku', 'like', self::OLD_SKU_PREFIX.'%')
            ->update(['sales_channel' => 'pos_only', 'is_inactive' => 1]);

        $hasShowInPos = Schema::hasColumn('categories', 'show_in_pos');
        $placeholder = 'https://khub-app.s3-accelerate.amazonaws.com/media/product/images/product-coming-soon.png';
        $logo = $catalog['media']['logo'] ?? $placeholder;

        $catIds = [];
        $covers = [];
        $parentNames = [];
        $productCatHits = [];
        foreach ($catalog['products'] as $p) {
            foreach ([$p['top_category'] ?? null, $p['category'] ?? null, $p['sub_category'] ?? null] as $hit) {
                if ($hit) {
                    $productCatHits[mb_strtolower((string) $hit)] = true;
                }
            }
        }
        foreach ($catalog['categories'] as $row) {
            $parentNames[$row['raw_name']] = $row['parent'];
            $parentNames[$row['name']] = $row['parent'];
        }

        $topFirst = collect($catalog['categories'])->sortBy(fn ($r) => $r['parent'] ? 1 : 0);
        foreach ($topFirst as $row) {
            $hasProducts = isset($productCatHits[mb_strtolower($row['name'])])
                || isset($productCatHits[mb_strtolower($row['raw_name'])]);
            if (! empty($row['parent']) && ! $hasProducts) {
                continue;
            }
            $parentId = 0;
            if (! empty($row['parent'])) {
                $parentId = $catIds[$row['parent']] ?? $catIds[Str::title($row['parent'])] ?? 0;
            }
            $slug = 'ecom-'.Str::slug($row['raw_name'] ?: $row['name']);
            $cat = Category::withTrashed()
                ->where('business_id', $businessId)
                ->where(function ($q) use ($row, $slug) {
                    $q->where('slug', $slug)
                        ->orWhere('short_code', $slug)
                        ->orWhere('name', $row['name'])
                        ->orWhere('name', $row['raw_name']);
                })
                ->orderBy('id')
                ->first();
            $payload = [
                'name' => $row['name'],
                'business_id' => $businessId,
                'short_code' => $slug,
                'parent_id' => $parentId ?: 0,
                'created_by' => $userId,
                'category_type' => 'product',
                'slug' => $slug,
                'description' => $row['blurb'] ?: ($row['parent'] ? $row['parent'].' · '.$row['name'] : $row['name'].' wholesale'),
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
            $keepId = $this->mergeDuplicateCategories($businessId, $row['name'], $cat->id);
            $catIds[$row['name']] = $keepId;
            $catIds[$row['raw_name']] = $keepId;
            $covers[$keepId] = $this->cdn($row['image'] ?? null, $logo);
        }

        File::put(storage_path('app/ecom_category_covers.json'), json_encode($covers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $media = $catalog['media'] ?? [];
        File::put(storage_path('app/ecom_gs_media.json'), json_encode($media, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $usedBrandNames = [];
        foreach ($catalog['products'] as $p) {
            $n = trim((string) ($p['brand'] ?? ''));
            if ($n !== '') {
                $usedBrandNames[mb_strtolower($n)] = $n;
            }
        }
        $brandMeta = [];
        foreach ($catalog['brands'] as $b) {
            $brandMeta[mb_strtolower($b['name'])] = $b;
        }

        $brandIds = [];
        $brandCovers = [];
        foreach ($usedBrandNames as $key => $name) {
            $meta = $brandMeta[$key] ?? ['name' => $name, 'image' => null];
            $brand = Brands::withTrashed()
                ->where('business_id', $businessId)
                ->where('name', $meta['name'])
                ->first();
            $payload = [
                'business_id' => $businessId,
                'name' => $meta['name'],
                'created_by' => $userId,
            ];
            if (Schema::hasColumn('brands', 'description')) {
                $payload['description'] = $this->cdn($meta['image'] ?? null, null) ?: '';
            }
            if (Schema::hasColumn('brands', 'show_in_pos')) {
                $payload['show_in_pos'] = 0;
            }
            if ($brand) {
                if ($brand->trashed()) {
                    $brand->restore();
                }
                $brand->fill($payload)->save();
            } else {
                $brand = Brands::create($payload);
            }
            $brandIds[$meta['name']] = $brand->id;
            $brandIds[$name] = $brand->id;
            $img = $this->cdn($meta['image'] ?? null, null);
            if ($img) {
                $brandCovers[$brand->id] = $img;
            }
        }

        $locationIds = BusinessLocation::where('business_id', $businessId)
            ->when(
                Schema::hasColumn('business_locations', 'is_active'),
                fn ($q) => $q->where('is_active', 1)
            )
            ->pluck('id');
        if ($locationIds->isEmpty()) {
            $locationIds = BusinessLocation::where('business_id', $businessId)->pluck('id');
        }

        $rows = $this->paddedProducts($catalog, $covers, $logo);

        Product::where('business_id', $businessId)
            ->where('sku', 'like', self::SKU_PREFIX.'%')
            ->update(['ecom_is_featured' => 0]);

        $created = 0;
        $seenSkus = [];
        foreach ($rows as $i => $row) {
            $sku = sprintf(self::SKU_PREFIX.'%04d', $i + 1);
            while (isset($seenSkus[$sku])) {
                $i++;
                $sku = sprintf(self::SKU_PREFIX.'%04d', $i + 1);
            }
            $seenSkus[$sku] = true;
            $catName = $row['category'];
            $subName = $row['sub_category'] ?? null;
            $categoryId = $catIds[$catName] ?? null;
            $subId = $subName ? ($catIds[$subName] ?? null) : null;
            if ($subId && ! $categoryId) {
                $categoryId = $catIds[$parentNames[$subName] ?? ''] ?? $subId;
            }
            if ($subId && $categoryId === $subId) {
                $subId = null;
            }
            $brandName = $row['brand'] ?? null;
            $price = max(0.99, (float) ($row['price'] ?? 9.99));
            $image = $this->cdn($row['image'] ?? null, $covers[$categoryId ?? 0] ?? $logo);
            $product = Product::where('business_id', $businessId)->where('sku', $sku)->first();
            $payload = [
                'name' => $row['name'],
                'business_id' => $businessId,
                'type' => 'single',
                'unit_id' => $unitId,
                'brand_id' => $brandName ? ($brandIds[$brandName] ?? null) : null,
                'category_id' => $categoryId,
                'sub_category_id' => $subId,
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
                'ecom_short_description' => $row['case'] ?? 'Wholesale case pack',
                'ecom_long_description' => $row['blurb'] ?: ($row['name'].' from G&S Wholesale Distribution.'),
                'ecom_is_featured' => ! empty($row['featured']),
                'ecom_is_new_arrival' => ! empty($row['new']),
                'ecom_online_price' => $price,
                'ecom_discount_price' => $row['sale'] ?? null,
                'image' => $image,
                'product_description' => $row['blurb'] ?: $row['name'],
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
                'default_purchase_price' => round($price * 0.72, 2),
                'dpp_inc_tax' => round($price * 0.72, 2),
                'profit_percent' => 38.89,
                'default_sell_price' => $price,
                'sell_price_inc_tax' => $price,
            ];
            if ($variation) {
                if ($variation->trashed()) {
                    $variation->restore();
                }
                $variation->fill($vData)->save();
            } else {
                $variation = Variation::create($vData);
            }

            $product->product_locations()->sync($locationIds->all());
            foreach ($locationIds as $locationId) {
                VariationLocationDetails::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'product_variation_id' => $pv->id,
                        'variation_id' => $variation->id,
                        'location_id' => $locationId,
                    ],
                    ['qty_available' => 0]
                );
            }
        }

        File::put(storage_path('app/ecom_brand_covers.json'), json_encode($brandCovers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->dropEmptyEcommerceTaxonomy($businessId, array_values(array_unique($catIds)), array_values(array_unique($brandIds)));

        $this->command?->info('G&S ecommerce catalog ready. New products: '.$created.'. SKUs: '.count($rows).'. CDN images from gsdistributor.com.');
    }

    protected function catalog(): array
    {
        $path = database_path('seeders/data/gs_catalog.json');
        $data = json_decode(File::get($path), true);

        return is_array($data) ? $data : ['categories' => [], 'brands' => [], 'products' => [], 'media' => []];
    }

    protected function cdn(?string $url, ?string $fallback): ?string
    {
        if (is_string($url) && preg_match('#^https?://#i', $url) && ! str_contains($url, 'product-coming-soon')) {
            return $url;
        }

        return $fallback;
    }

    protected function paddedProducts(array $catalog, array $covers, string $logo): array
    {
        $rows = [];
        $seen = [];
        foreach ($catalog['products'] as $p) {
            $name = trim((string) ($p['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $key = mb_strtolower($name);
            $n = 2;
            while (isset($seen[$key])) {
                $name = $p['name'].' '.$n;
                $key = mb_strtolower($name);
                $n++;
            }
            $seen[$key] = true;
            $top = $p['top_category'] ?? $p['category'] ?? 'General Merchandise';
            $sub = $p['sub_category'] ?? null;
            $rows[] = [
                'name' => $name,
                'brand' => $p['brand'] ?: 'G&S Wholesale',
                'category' => $top,
                'sub_category' => $sub,
                'case' => 'Wholesale pack',
                'price' => (float) ($p['price'] ?? 9.99),
                'blurb' => $p['blurb'] ?? '',
                'image' => $this->cdn($p['image'] ?? null, $logo),
                'featured' => ! empty($p['featured']),
                'new' => ! empty($p['new']) || ! empty($p['hot']),
                'sale' => null,
            ];
        }

        $rows = $this->padBy($rows, fn ($r) => $r['category'], 9);
        $rows = $this->padBy($rows, fn ($r) => $r['sub_category'] ?: '__none__', 9, skip: ['__none__']);
        $rows = $this->padBy($rows, fn ($r) => $r['brand'], 9);

        $featured = 0;
        foreach ($rows as &$row) {
            if (! empty($row['featured'])) {
                $featured++;
            }
        }
        unset($row);
        if ($featured < 12) {
            foreach ($rows as &$row) {
                if ($featured >= 12) {
                    break;
                }
                if (empty($row['featured'])) {
                    $row['featured'] = 1;
                    $featured++;
                }
            }
            unset($row);
        }

        return $rows;
    }

    protected function padBy(array $rows, callable $keyFn, int $min, array $skip = []): array
    {
        $groups = [];
        foreach ($rows as $i => $row) {
            $groups[$keyFn($row)][] = $i;
        }
        foreach ($groups as $key => $indexes) {
            if (in_array($key, $skip, true) || $key === '' || $key === null) {
                continue;
            }
            $need = $min - count($indexes);
            $n = 1;
            while ($need > 0) {
                $src = $rows[$indexes[($n - 1) % count($indexes)]];
                $clone = $src;
                $clone['name'] = $src['name'].' Pack '.$n;
                $clone['featured'] = 0;
                $rows[] = $clone;
                $need--;
                $n++;
            }
        }

        return $rows;
    }

    protected function dropEmptyEcommerceTaxonomy(int $businessId, array $keepCatIds, array $keepBrandIds): void
    {
        Category::where('business_id', $businessId)
            ->where('category_type', 'product')
            ->where('slug', 'like', 'ecom-%')
            ->whereNotIn('id', $keepCatIds ?: [0])
            ->get()
            ->each(function (Category $cat) use ($businessId) {
                $has = Product::storefrontQuery($businessId)
                    ->where(function ($q) use ($cat) {
                        $q->where('category_id', $cat->id)->orWhere('sub_category_id', $cat->id);
                    })
                    ->exists();
                if (! $has) {
                    $cat->delete();
                }
            });
    }

    protected function mergeDuplicateCategories(int $businessId, string $name, int $keepId): int
    {
        $dupes = Category::withTrashed()
            ->where('business_id', $businessId)
            ->where('name', $name)
            ->where('id', '!=', $keepId)
            ->get();

        foreach ($dupes as $dupe) {
            Product::where('category_id', $dupe->id)->update(['category_id' => $keepId]);
            Product::where('sub_category_id', $dupe->id)->update(['sub_category_id' => $keepId]);
            $dupe->forceDelete();
        }

        return $keepId;
    }
}
