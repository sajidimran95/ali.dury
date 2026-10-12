<?php

namespace App\Console\Commands;

use App\Brands;
use App\Category;
use App\Product;
use App\ProductVariation;
use App\Unit;
use App\User;
use App\Variation;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Modules\Ecommerce\Entities\EcomBanner;
use Modules\Ecommerce\Entities\EcomCollection;
use Modules\Ecommerce\Entities\EcommerceSetting;

class SeedEcommerceDemoCommand extends Command
{
    protected $signature = 'ecommerce:seed-demo {--business_id= : Business to seed}';

    protected $description = 'Seed 50 vape/cigar/smoking ecommerce demo products, categories, and video banners';

    public function handle(): int
    {
        $businessId = (int) ($this->option('business_id')
            ?: EcommerceSetting::where('is_enabled', 1)->value('business_id')
            ?: 1);

        $unitId = (int) (Unit::where('business_id', $businessId)->value('id') ?: Unit::query()->value('id') ?: 1);
        $userId = (int) (User::query()->value('id') ?: 1);

        $cats = $this->categories();
        $catIds = [];
        foreach ($cats as $name) {
            $cat = Category::withTrashed()
                ->where('business_id', $businessId)
                ->where('name', $name)
                ->where(function ($q) {
                    $q->whereNull('parent_id')->orWhere('parent_id', 0);
                })
                ->first();
            if ($cat) {
                if ($cat->trashed()) {
                    $cat->restore();
                }
            } else {
                $cat = Category::create([
                    'name' => $name,
                    'business_id' => $businessId,
                    'short_code' => Str::slug($name),
                    'parent_id' => 0,
                    'created_by' => $userId,
                    'category_type' => 'product',
                    'slug' => Str::slug($name),
                ]);
            }
            $catIds[$name] = $cat->id;
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
            } else {
                $brand = Brands::create([
                    'business_id' => $businessId,
                    'name' => $name,
                    'created_by' => $userId,
                ]);
            }
            $brandIds[$name] = $brand->id;
        }

        $created = 0;
        $productIds = [];
        foreach ($this->products() as $i => $row) {
            $sku = sprintf('EDEMO-%02d', $i + 1);
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
                'sales_channel' => 'pos_ecommerce',
                'ecom_slug' => Str::slug($row['name']).'-edemo-'.($i + 1),
                'ecom_short_description' => $row['case'],
                'ecom_long_description' => $row['blurb'],
                'ecom_is_featured' => ! empty($row['featured']),
                'ecom_is_new_arrival' => ! empty($row['new']),
                'ecom_online_price' => $row['price'],
                'ecom_discount_price' => $row['sale'] ?? null,
                'image' => $row['image'],
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
            $productIds[] = $product->id;
        }

        $this->seedBanners($businessId);
        $this->seedCollections($businessId, $productIds, $catIds);

        $this->info("Demo catalog ready for business {$businessId}. New products: {$created}. Total demo SKUs: ".count($this->products()));

        return self::SUCCESS;
    }

    protected function seedBanners(int $businessId): void
    {
        $videos = [
            [
                'title' => 'Wholesale Vape Cloud',
                'subtitle' => 'Disposables, pods, and salts for licensed retailers',
                'image' => 'https://videos.pexels.com/video-files/1722881/1722881-hd_1920_1080_25fps.mp4',
                'button_text' => 'Shop Vape',
                'button_link' => '/shop?q=vape',
            ],
            [
                'title' => 'Cigar Lounge Heat',
                'subtitle' => 'Boxes, cigarillos, and humidors — case packed',
                'image' => 'https://cdn.coverr.co/videos/coverr-smoke-in-slow-motion-5583/1080p.mp4',
                'button_text' => 'Shop Cigars',
                'button_link' => '/shop?q=cigar',
            ],
            [
                'title' => 'Papers, Flame & Glass',
                'subtitle' => 'Accessories that actually turn on the shelf',
                'image' => 'https://videos.pexels.com/video-files/5377684/5377684-hd_1920_1080_25fps.mp4',
                'button_text' => 'Shop Accessories',
                'button_link' => '/shop?q=grinder',
            ],
        ];

        foreach ($videos as $i => $row) {
            EcomBanner::updateOrCreate(
                ['business_id' => $businessId, 'title' => $row['title']],
                array_merge($row, ['is_active' => 1, 'sort_order' => $i + 1])
            );
        }
    }

    protected function seedCollections(int $businessId, array $productIds, array $catIds): void
    {
        $groups = [
            ['name' => 'Vape Essentials', 'slug' => 'vape-essentials', 'description' => 'Disposables, devices, and nic salts for licensed shops.'],
            ['name' => 'Cigar Lounge', 'slug' => 'cigar-lounge', 'description' => 'Premium boxes, cigarillos, and humidors.'],
            ['name' => 'Smoke Shop Tools', 'slug' => 'smoke-shop-tools', 'description' => 'Grinders, papers, lighters, and trays.'],
        ];
        $chunks = array_chunk($productIds, 8);
        foreach ($groups as $i => $row) {
            $col = EcomCollection::updateOrCreate(
                ['business_id' => $businessId, 'slug' => $row['slug']],
                ['name' => $row['name'], 'description' => $row['description'], 'is_active' => 1, 'sort_order' => $i + 1]
            );
            $ids = $chunks[$i] ?? array_slice($productIds, $i * 8, 8);
            $sync = [];
            foreach ($ids as $sort => $pid) {
                $sync[$pid] = ['sort_order' => $sort];
            }
            $col->products()->sync($sync);
        }
    }

    protected function categories(): array
    {
        return [
            'Disposable Vapes',
            'Pod Systems & Devices',
            'E-Liquids & Salts',
            'Cigars & Cigarillos',
            'Papers & Wraps',
            'Hookah & Shisha',
            'Lighters & Torches',
            'Smoking Accessories',
        ];
    }

    protected function brands(): array
    {
        return ['CloudLine', 'Nightshade', 'Drift Coil', 'Harbor Leaf', 'Ember & Oak', 'Velvet Puff', 'Iron Flint', 'Ash & Ember'];
    }

    protected function img(string $id): string
    {
        return 'https://images.unsplash.com/'.$id.'?auto=format&fit=crop&w=800&q=70';
    }

    protected function products(): array
    {
        $vape = $this->img('photo-1558618666-fcd25c85cd64');
        $device = $this->img('photo-1518770660439-4636190af475');
        $liquid = $this->img('photo-1519671482749-fd09be7ccebf');
        $cigar = $this->img('photo-1511689660979-10d2b1aada49');
        $paper = $this->img('photo-1563906267088-b029e7101114');
        $hookah = $this->img('photo-1518531933037-91b2f5f229cc');
        $flame = $this->img('photo-1461532257246-777de18cd58b');
        $acc = $this->img('photo-1556742049-0cfed4f6a45d');

        return [
            ['name' => 'CloudLine Bar 5000 Frozen Mint', 'brand' => 'CloudLine', 'category' => 'Disposable Vapes', 'case' => 'CASE: 10CT', 'price' => 89.90, 'featured' => 1, 'image' => $vape, 'blurb' => '5,000 puff disposable, menthol-forward mint. Wholesale 10-count case.'],
            ['name' => 'CloudLine Bar 5000 Blue Razz', 'brand' => 'CloudLine', 'category' => 'Disposable Vapes', 'case' => 'CASE: 10CT', 'price' => 89.90, 'featured' => 1, 'image' => $vape, 'blurb' => 'Blue raspberry ice disposable bar. Licensed retailers only.'],
            ['name' => 'Nightshade Stick 3500 Tobacco Gold', 'brand' => 'Nightshade', 'category' => 'Disposable Vapes', 'case' => 'CASE: 10CT', 'price' => 72.00, 'new' => 1, 'image' => $vape, 'blurb' => 'Classic tobacco profile, 3500 puffs, compact stick.'],
            ['name' => 'Nightshade Stick 3500 Mango Ice', 'brand' => 'Nightshade', 'category' => 'Disposable Vapes', 'case' => 'CASE: 10CT', 'price' => 72.00, 'image' => $vape, 'blurb' => 'Ripe mango with a cooling finish.'],
            ['name' => 'Velvet Puff Nano Watermelon', 'brand' => 'Velvet Puff', 'category' => 'Disposable Vapes', 'case' => 'CASE: 10CT', 'price' => 64.50, 'sale' => 54.90, 'featured' => 1, 'image' => $vape, 'blurb' => 'Compact nano disposable. Watermelon candy.'],
            ['name' => 'Velvet Puff Nano Grape Ice', 'brand' => 'Velvet Puff', 'category' => 'Disposable Vapes', 'case' => 'CASE: 10CT', 'price' => 64.50, 'image' => $vape, 'blurb' => 'Iced grape nano bar.'],
            ['name' => 'Drift Coil 8K Peach', 'brand' => 'Drift Coil', 'category' => 'Disposable Vapes', 'case' => 'CASE: 5CT', 'price' => 79.00, 'new' => 1, 'image' => $vape, 'blurb' => 'High-puff 8K peach ice, 5-count inner.'],
            ['name' => 'Drift Coil 8K Menthol', 'brand' => 'Drift Coil', 'category' => 'Disposable Vapes', 'case' => 'CASE: 5CT', 'price' => 79.00, 'image' => $vape, 'blurb' => 'Straight menthol 8K disposable.'],
            ['name' => 'Harbor Mist 600 Classic Tobacco', 'brand' => 'Harbor Leaf', 'category' => 'Disposable Vapes', 'case' => 'CASE: 20CT', 'price' => 58.00, 'image' => $vape, 'blurb' => 'Small-format 600 puff tobacco, 20-count.'],

            ['name' => 'Drift Coil Pod Kit', 'brand' => 'Drift Coil', 'category' => 'Pod Systems & Devices', 'case' => 'CASE: 8CT', 'price' => 112.00, 'featured' => 1, 'image' => $device, 'blurb' => 'Refillable pod kit with dual coils. 8 kits per case.'],
            ['name' => 'Drift Coil Replacement Pods 4pk', 'brand' => 'Drift Coil', 'category' => 'Pod Systems & Devices', 'case' => 'CASE: 20CT', 'price' => 96.00, 'image' => $device, 'blurb' => '4-pack pods, 20 inner boxes.'],
            ['name' => 'CloudLine Aegis Device', 'brand' => 'CloudLine', 'category' => 'Pod Systems & Devices', 'case' => 'CASE: 6CT', 'price' => 138.00, 'new' => 1, 'image' => $device, 'blurb' => 'Rugged Aegis-style pod mod.'],
            ['name' => 'Nightshade Pod Kit', 'brand' => 'Nightshade', 'category' => 'Pod Systems & Devices', 'case' => 'CASE: 10CT', 'price' => 99.00, 'image' => $device, 'blurb' => 'Draw-activated pod system, matte black.'],
            ['name' => 'Iron Flint Box Mod', 'brand' => 'Iron Flint', 'category' => 'Pod Systems & Devices', 'case' => 'CASE: 4CT', 'price' => 156.00, 'featured' => 1, 'image' => $device, 'blurb' => 'Single-battery box mod for rebuildable tanks.'],
            ['name' => 'Drift Mesh Coils 5pk', 'brand' => 'Drift Coil', 'category' => 'Pod Systems & Devices', 'case' => 'CASE: 40CT', 'price' => 84.00, 'image' => $device, 'blurb' => '0.2Ω mesh coils, 5-pack, 40 inners.'],
            ['name' => 'CloudLine USB-C Dock', 'brand' => 'CloudLine', 'category' => 'Pod Systems & Devices', 'case' => 'CASE: 12CT', 'price' => 48.00, 'image' => $device, 'blurb' => 'USB-C charging dock for CloudLine bars.'],

            ['name' => 'CloudLine Salt 30ml Berry Frost', 'brand' => 'CloudLine', 'category' => 'E-Liquids & Salts', 'case' => 'CASE: 12CT', 'price' => 66.00, 'featured' => 1, 'image' => $liquid, 'blurb' => '50mg nic salt, berry frost, 30ml.'],
            ['name' => 'CloudLine Salt 30ml Vanilla Custard', 'brand' => 'CloudLine', 'category' => 'E-Liquids & Salts', 'case' => 'CASE: 12CT', 'price' => 66.00, 'image' => $liquid, 'blurb' => 'Dessert nic salt, 30ml bottles.'],
            ['name' => 'Nightshade Freebase 60ml Tobacco Reserve', 'brand' => 'Nightshade', 'category' => 'E-Liquids & Salts', 'case' => 'CASE: 12CT', 'price' => 78.00, 'image' => $liquid, 'blurb' => '3mg/6mg tobacco reserve freebase.'],
            ['name' => 'Velvet Puff Salt Strawberry Kiwi', 'brand' => 'Velvet Puff', 'category' => 'E-Liquids & Salts', 'case' => 'CASE: 12CT', 'price' => 62.00, 'sale' => 54.00, 'new' => 1, 'image' => $liquid, 'blurb' => 'Fruit salt, 35mg, 30ml.'],
            ['name' => 'Drift Nic Salt Iced Apple', 'brand' => 'Drift Coil', 'category' => 'E-Liquids & Salts', 'case' => 'CASE: 12CT', 'price' => 64.00, 'image' => $liquid, 'blurb' => 'Green apple ice nic salt.'],
            ['name' => 'Harbor Leaf Salt Menthol', 'brand' => 'Harbor Leaf', 'category' => 'E-Liquids & Salts', 'case' => 'CASE: 12CT', 'price' => 61.00, 'image' => $liquid, 'blurb' => 'Straight menthol nic salt.'],
            ['name' => 'Ember Oak Blend 60ml', 'brand' => 'Ember & Oak', 'category' => 'E-Liquids & Salts', 'case' => 'CASE: 12CT', 'price' => 82.00, 'featured' => 1, 'image' => $liquid, 'blurb' => 'Oak-cask tobacco freebase 60ml.'],
            ['name' => 'Nightshade Menthol 30ml', 'brand' => 'Nightshade', 'category' => 'E-Liquids & Salts', 'case' => 'CASE: 12CT', 'price' => 59.00, 'image' => $liquid, 'blurb' => 'Ice-cold menthol salt.'],

            ['name' => 'Ember & Oak Robusto 25ct Box', 'brand' => 'Ember & Oak', 'category' => 'Cigars & Cigarillos', 'case' => 'CASE: 4 BOX', 'price' => 168.00, 'featured' => 1, 'image' => $cigar, 'blurb' => 'Handmade robusto, 25-count boxes, 4 per case.'],
            ['name' => 'Ember & Oak Churchill 20ct', 'brand' => 'Ember & Oak', 'category' => 'Cigars & Cigarillos', 'case' => 'CASE: 4 BOX', 'price' => 186.00, 'image' => $cigar, 'blurb' => 'Churchill format, Connecticut wrapper.'],
            ['name' => 'Harbor Leaf Maduro Toro 25ct', 'brand' => 'Harbor Leaf', 'category' => 'Cigars & Cigarillos', 'case' => 'CASE: 4 BOX', 'price' => 174.00, 'new' => 1, 'image' => $cigar, 'blurb' => 'Dark maduro toro, boxed 25s.'],
            ['name' => 'Harbor Leaf Connecticut Corona', 'brand' => 'Harbor Leaf', 'category' => 'Cigars & Cigarillos', 'case' => 'CASE: 5 BOX', 'price' => 142.00, 'image' => $cigar, 'blurb' => 'Mild corona, Connecticut shade.'],
            ['name' => 'Ash & Ember Cigarillos Sweet 50ct', 'brand' => 'Ash & Ember', 'category' => 'Cigars & Cigarillos', 'case' => 'CASE: 10CT', 'price' => 98.00, 'featured' => 1, 'sale' => 88.00, 'image' => $cigar, 'blurb' => 'Sweet-tip cigarillos, 50-count tins.'],
            ['name' => 'Ash & Ember Cigarillos Natural 50ct', 'brand' => 'Ash & Ember', 'category' => 'Cigars & Cigarillos', 'case' => 'CASE: 10CT', 'price' => 98.00, 'image' => $cigar, 'blurb' => 'Natural leaf cigarillos, 50-count.'],
            ['name' => 'Velvet Leaf Natural Wraps', 'brand' => 'Velvet Puff', 'category' => 'Cigars & Cigarillos', 'case' => 'CASE: 25CT', 'price' => 44.00, 'image' => $cigar, 'blurb' => 'Natural leaf wraps, 2-pack inners.'],
            ['name' => 'Harbor Cigar Sampler 8-Pack', 'brand' => 'Harbor Leaf', 'category' => 'Cigars & Cigarillos', 'case' => 'CASE: 12CT', 'price' => 126.00, 'new' => 1, 'image' => $cigar, 'blurb' => 'Mixed sampler of 8 cigars.'],

            ['name' => 'Velvet Papers King Size 50ct', 'brand' => 'Velvet Puff', 'category' => 'Papers & Wraps', 'case' => 'CASE: 50CT', 'price' => 36.00, 'image' => $paper, 'blurb' => 'King size rolling papers, 50 booklets.'],
            ['name' => 'Velvet Papers 1.25 50ct', 'brand' => 'Velvet Puff', 'category' => 'Papers & Wraps', 'case' => 'CASE: 50CT', 'price' => 32.00, 'image' => $paper, 'blurb' => '1.25 papers, 50 booklets.'],
            ['name' => 'Harbor Hemp Wraps 25ct', 'brand' => 'Harbor Leaf', 'category' => 'Papers & Wraps', 'case' => 'CASE: 25CT', 'price' => 41.00, 'featured' => 1, 'image' => $paper, 'blurb' => 'Hemp wraps, unsweetened.'],
            ['name' => 'Nightshade Cones 100ct', 'brand' => 'Nightshade', 'category' => 'Papers & Wraps', 'case' => 'CASE: 10CT', 'price' => 55.00, 'new' => 1, 'image' => $paper, 'blurb' => 'Pre-rolled cones, 100-count jars.'],

            ['name' => 'Ember Hookah Stem Set', 'brand' => 'Ember & Oak', 'category' => 'Hookah & Shisha', 'case' => 'CASE: 4CT', 'price' => 164.00, 'featured' => 1, 'image' => $hookah, 'blurb' => 'Stainless stem + base set.'],
            ['name' => 'Harbor Shisha Bowl', 'brand' => 'Harbor Leaf', 'category' => 'Hookah & Shisha', 'case' => 'CASE: 12CT', 'price' => 48.00, 'image' => $hookah, 'blurb' => 'Clay phunnel bowl, 12-count.'],
            ['name' => 'CloudLine Hookah Hose', 'brand' => 'CloudLine', 'category' => 'Hookah & Shisha', 'case' => 'CASE: 12CT', 'price' => 54.00, 'image' => $hookah, 'blurb' => 'Silicone hose with washable core.'],
            ['name' => 'Nightshade Charcoal Cubes 1kg', 'brand' => 'Nightshade', 'category' => 'Hookah & Shisha', 'case' => 'CASE: 10CT', 'price' => 62.00, 'image' => $hookah, 'blurb' => 'Coconut cube charcoal, 1kg flats.'],

            ['name' => 'Iron Flint Triple Torch', 'brand' => 'Iron Flint', 'category' => 'Lighters & Torches', 'case' => 'CASE: 12CT', 'price' => 86.00, 'featured' => 1, 'image' => $flame, 'blurb' => 'Triple-jet table torch.'],
            ['name' => 'Iron Flint Soft Flame', 'brand' => 'Iron Flint', 'category' => 'Lighters & Torches', 'case' => 'CASE: 25CT', 'price' => 49.00, 'image' => $flame, 'blurb' => 'Refillable soft-flame metal lighter.'],
            ['name' => 'Ash & Ember Stick Lighter 50ct', 'brand' => 'Ash & Ember', 'category' => 'Lighters & Torches', 'case' => 'CASE: 50CT', 'price' => 28.00, 'image' => $flame, 'blurb' => 'Disposable stick lighters, 50-count.'],
            ['name' => 'Nightshade Dual Torch Mini', 'brand' => 'Nightshade', 'category' => 'Lighters & Torches', 'case' => 'CASE: 16CT', 'price' => 72.00, 'new' => 1, 'image' => $flame, 'blurb' => 'Pocket dual-flame torch.'],

            ['name' => 'Nightshade 4-Piece Grinder', 'brand' => 'Nightshade', 'category' => 'Smoking Accessories', 'case' => 'CASE: 12CT', 'price' => 76.00, 'featured' => 1, 'image' => $acc, 'blurb' => 'Aircraft aluminum 4-piece grinder.'],
            ['name' => 'Nightshade Rolling Tray', 'brand' => 'Nightshade', 'category' => 'Smoking Accessories', 'case' => 'CASE: 20CT', 'price' => 58.00, 'image' => $acc, 'blurb' => 'Metal rolling tray, branded.'],
            ['name' => 'CloudLine Cotton Tips 200ct', 'brand' => 'CloudLine', 'category' => 'Smoking Accessories', 'case' => 'CASE: 24CT', 'price' => 34.00, 'image' => $acc, 'blurb' => 'Rebuild cotton, 200 tips.'],
            ['name' => 'Ash Catcher Silicone Jar 6ct', 'brand' => 'Ash & Ember', 'category' => 'Smoking Accessories', 'case' => 'CASE: 24CT', 'price' => 39.00, 'image' => $acc, 'blurb' => 'Non-stick silicone jars, 6-pack.'],
            ['name' => 'Drift Coil Tool Kit', 'brand' => 'Drift Coil', 'category' => 'Smoking Accessories', 'case' => 'CASE: 10CT', 'price' => 67.00, 'new' => 1, 'image' => $acc, 'blurb' => 'Coil jig, snips, ceramic tweezers.'],
            ['name' => 'Harbor Humidor 25ct', 'brand' => 'Harbor Leaf', 'category' => 'Smoking Accessories', 'case' => 'CASE: 2CT', 'price' => 188.00, 'featured' => 1, 'image' => $acc, 'blurb' => 'Desktop cedar humidor, 25-count.'],
        ];
    }
}
