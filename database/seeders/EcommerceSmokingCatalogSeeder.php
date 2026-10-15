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

        Product::where('business_id', $businessId)
            ->where('sku', 'like', self::SKU_PREFIX.'%')
            ->update(['ecom_is_featured' => 0]);

        $locationIds = BusinessLocation::where('business_id', $businessId)
            ->when(
                Schema::hasColumn('business_locations', 'is_active'),
                fn ($q) => $q->where('is_active', 1)
            )
            ->pluck('id');
        if ($locationIds->isEmpty()) {
            $locationIds = BusinessLocation::where('business_id', $businessId)->pluck('id');
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

        $this->command?->info("Ecommerce smoking catalog ready. New products: {$created}. Total SKUs: 300. Existing POS products left as pos_only.");
    }

    protected function categories(): array
    {
        return [
            ['name' => 'Disposable Vapes', 'blurb' => 'High-puff bars for licensed smoke shops.', 'image' => $this->photos('Disposable Vapes')[0]],
            ['name' => 'Vape Devices & Pods', 'blurb' => 'Pod kits, mods, and coils.', 'image' => $this->photos('Vape Devices & Pods')[0]],
            ['name' => 'E-Liquids & Nic Salts', 'blurb' => 'Salts and freebase in case packs.', 'image' => $this->photos('E-Liquids & Nic Salts')[0]],
            ['name' => 'Cigars', 'blurb' => 'Robusto, Churchill, and boxed cigars.', 'image' => $this->photos('Cigars')[0]],
            ['name' => 'Cigarillos', 'blurb' => 'Sweet-tip and natural leaf cigarillos.', 'image' => $this->photos('Cigarillos')[0]],
            ['name' => 'Rolling Papers & Wraps', 'blurb' => 'Papers, hemp wraps, and cones.', 'image' => $this->photos('Rolling Papers & Wraps')[0]],
            ['name' => 'Hookah & Shisha', 'blurb' => 'Stems, bowls, hoses, and charcoal.', 'image' => $this->photos('Hookah & Shisha')[0]],
            ['name' => 'Lighters & Torches', 'blurb' => 'Soft flame, jet, and table torches.', 'image' => $this->photos('Lighters & Torches')[0]],
            ['name' => 'Grinders & Trays', 'blurb' => 'Grinders and rolling trays.', 'image' => $this->photos('Grinders & Trays')[0]],
            ['name' => 'Smoke Shop Accessories', 'blurb' => 'Humidors, tips, tools, and trays.', 'image' => $this->photos('Smoke Shop Accessories')[0]],
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
        $ids = $this->googleVideoIds()[$category] ?? ['XP2m1vtcWCQ'];

        return array_map(fn (string $id) => $this->googleCdn($id), $ids);
    }

    protected function uniqueImage(string $category, int $slot): string
    {
        static $used = [];

        $pick = function (array $ids) use (&$used) {
            foreach ($ids as $id) {
                if (! isset($used[$id])) {
                    $used[$id] = true;

                    return $this->googleCdn($id);
                }
            }

            return null;
        };

        $url = $pick($this->googleVideoIds()[$category] ?? []);
        if ($url) {
            return $url;
        }
        foreach ($this->googleVideoIds() as $ids) {
            $url = $pick($ids);
            if ($url) {
                return $url;
            }
        }

        $ids = $this->googleVideoIds()[$category] ?? ['XP2m1vtcWCQ'];

        return $this->googleCdn($ids[$slot % count($ids)]);
    }

    protected function googleCdn(string $videoId): string
    {
        return 'https://i.ytimg.com/vi/'.$videoId.'/hqdefault.jpg';
    }

    protected function googleVideoIds(): array
    {
        return [
            'Disposable Vapes' => [
                'XP2m1vtcWCQ', 'I94BgZcs_fg', 'eAhnJKW3T3Q', 'DZNiQV2NmLY', 'gjdYUqwqhGs', '6Wcmeo_mNfA',
                '0y8n2aMq0yY', 'eL-FRHC_Rz4', '5BSd4tq8BD4', 'gUto7wnl20M', '_z6xXqiUewo', 'DtJjpsfafbw',
                '2HnM-9E2WuE', 'TUdNa7x9ak8', 'e5iXmYLk9MI', 'SLGXQzbNym0', 'eNuVXVaBdMk', '2w-AvM6gteo',
                'I5BtjQNAoS4', '45fLkrA8o4g', 'EzFMv_51uwE', 'MskjfOI1-as', 'CaQwe0kqXgc', 'N4aU41ZKzeQ',
                'LjFojmEXdXw', 'tr2GnxNGYZ8', 'p3tc9lI6R-0', '0fsHFbFna-4', 'YputkESFs0I', 'DAh1v9kGz5M',
                'FZIn9VV5P7w', 'x5EnFm1TDug', 'FHkULVU8zAs', 'CUWr_9bZY1Q', '8roR5WqnatQ', '3O1_wXYrwro',
                '0P2uvsv-cmo', 'fxwR06mrxY8', 'dsi_QeWwRC4',
            ],
            'Vape Devices & Pods' => [
                'xnwhTaJrQ9w', 'KY2UydjqBSI', 'NUVTl_-4zaM', 'QNd0Ew908eA', 'kNL-CE4fOMM', 'fhy0690XLPM',
                '8EEzmZXRwR4', '_4jSF_pG7D4', 'mX__oaP0hQI', 'ujsVpvPUnZA', 'XEikKbSeGAc', 'B6_NbIjepg0',
                'g3EFEjNsJ18', 'VtsG01kpIJo', 'OhHF1PXwJGQ', 'jCNm0vqOReE', 'jlTJumRThvU', 'RDbFQQ8kE_Y',
                'aqHvE5BhfLQ', 'EPxkEKX0OoM', 'FZXaKkz4RCg', 'CleH3muCPK4', 'ocmQQnuMGOw', '_DK6QXFMaVs',
                'OAoqw7W3YPE', 'XVrvxkC6qrk', 'NzT3G_wyHmo', 'NhIJTMVAs8M', 'j3Jdy7VNnXA', 'FzmxVmxbvo0',
                'Q-wZ2mH8ccc', '4RWeWSHNq8k', 'mOHJGqBG7Ac', 'pTx-AZHRFrY', 'GtyMT-B7big', 'mg08MuAJWWE',
                'HInmCQxXLLw', 'mSXu01XkOgM', 'FCv9aU-3D2w', 'G_3wWKFfBHQ', 'NwSAq_cKzqU', 'YsBs0-REgmM',
                'wTZL0xYyaBs', 'NMvWvYmo9dU', '1_bl32yJJuI', 'gppEe81QThI', 'xNasJh8xBzk', '8O3yQ6-uJ_U',
                '9dUnc5z4g4M', 'KR7_xwQbYp0', 'IbWX5hfVAJg', 'iSSAnh3FzBg', 'CH67bGiw-LE', 'B1awVlhY3hE',
                'yCpNNn700Rs', 'XGN7cjzK9GQ', '4j7wJ3WgaPI', 'vY4SnYLjJrI', 'VTr-iMgCQgM', 'VoRQn-XNCsE',
            ],
            'E-Liquids & Nic Salts' => [
                'Wn7fhNzqEFc', 'YvTpk25aaJs', 'j0Qka7xN6hM', 'QWifXDGHZQg', 'ERF999B4WOg', '17sb59A3UEo',
                'fprytm7Nycs', 'nWnIK_hwn6U', 'gf5_XDQYrYc', 'WLLAA61VI2k', 'MiDoXuxR2co', 'fIEahxi79us',
                'Q4MqXe-K6Vo', 'w1_8Ql4chd8', 'MwHT3JLjDZw', 'mhTlCdzgkV8', 'a67sALbH3K8', 'eKAHpU2sQ0c',
                'Z3q9598lebU', 'IDWRlKviiCw',
            ],
            'Cigars' => [
                'b49Fmc7MIT8', 'LjBogr2B-IU', 'O7I60tncoN0', 'puDq5_PC1gc', 'Hggs75_neug', 'jp5K2Jchkpw',
                'ZYfXEXpR77A', '_iYj3nAMwDY', '3DP5jErWILk', 'WNxE9q7MHAM', 'RuMPrfZ7xC0', 's9Yg3i1T7YM',
                'D0Jr-go-feI', 'jc73dAHliaM', 'BmU9BOQza7w', 'FSHryE20MCI', 'nvIvlexfmFM', 'Glas8lWkYgM',
                '7NuhJOvqHgk', '0Sbp338P598', 'WvMblsruaso', 'EjUatqNXKOo', 'fFSk2jQZrdA', 'rQSPKDnvbrM',
                'EgPkDt-AAFs', 'PxyB3DE7CQw', '5JbQ2giAKuc', 'crxk9MFiRds', 'cszi8JWTlvY', 'GZcP2-2e264',
                'MEOAgylq6Q4', 'pMGRMrqQC2c', 'Mrq16ZM46V0', 'j4elYJIGXHA', '6hldxtXu1wk', 'bEHFBYqb38E',
                '3uD4nxFwcBo', 'XMWaZr2dTtA', '720D5Tn9r-c', 'IIlhbal-HtQ', 'vA_j6rjsBec', 'TiZENUR605s',
                'AA4Pn51MOXs', 'W6rnNxpoV8g', '1Sw1qmQ0o8c', 'sTamHW3jPDs', 'E4U_L_Kp6cM', 'QBLVSSjYf6Y',
                'EVM6XB4LvoI', 'KTwFfbIfSN4', 'ZGviXJlTjOI', 'gwgO1wDHtfU', 'BeP3mciLG4w', 'koI5np5M5vU',
                '_3P_TgKon38', '_AMGttWjHTo', 'IqIoRQWsBZc',
            ],
            'Cigarillos' => [
                'NMmWRFdzaaI', 'E4gKBWLA-Yw', 'c3FYbP-I6dY', 'MQwWXCScsD0', 'wn5J9ssOGXk', 'DldLb0NL7eI',
                'BwNRAjjepEk', 'wvKOeDVyDMA', 'adpRWYyRE80', 'nCnkX1bX0Lo', 'HOSUM89T-L8', 'T8ndEV2LM9I',
                'LjyTTcxWn1k', '2qBwwKezo2I', 'v4Ey36QNyks', 'Bab99pIOA8Q', 'dT3Z74uhmOA', 'NGpLR7iWTmY',
                '06hs-VNYF3I', 'z9ATWKNzp0U', 'DEmDxm8iGms', 'xGSW6JpHCUo', 'z6G7oMMAhpU', 'Od7vIWarQrs',
                'gyxYHYjv1VI', 'IoN9r1ABBN4', 'iFEnVpSjYmM', '5knzm976zx0',
            ],
            'Rolling Papers & Wraps' => [
                'J3lDbTBmOMg', '6HKUWY38zxM', 'ZBwmsDEmPGU', '3VRNkUGlOR8', 'lI_NhWHeMAU', 'Ld9usUjcNdY',
                'm-CeVM8H5rg', 'Us00bQ6m_TY', 'SkALgMHWa08', 'bLSWJVEXbrc', 'Otwps3GTunE', '_DNHeV1RDrE',
                'uNAIMfPAALo', 'ZpUQQ4pQkao', 'hMqNGm9zUPE', 'GyysuhIHHf4', 'E2eceUW_XD0', 'Tt7w6wihPeQ',
                'hfjm4B1iN8w', '6NjlSubXCzg',
            ],
            'Hookah & Shisha' => [
                'cjdR7bUj7LI', 'Lo9yfdaVOrQ', 'b4b1cMVZOUU', 'n8_rjqoc6gU', 'mLWF4iCH98A', 'RJXCcStSPnM',
                'NvsB4t6nal0', 'HMt146kimDQ', 'XgHEtIs1tY8', 'fkGvUThwbdk', 'QRHwOKGTn8M', 'FO5qOSFmGN4',
                'p36B2ekJ5H8', 'EvMKzUCifiU', '5HAL5PywK3I', 'evM-ZPpKrNY', 'O45ennOpQrU', 'SuZ85SrV9oI',
                'eHyEap7rNig', '7MWAGzDeawI', '7XsVSAseoHs', '9NImA2SeCNc', 'Zlyg9hK1vuQ', '__57A-7J_ec',
                'gBHZHfadDc4', 'EEHlpZURaNA', '5hZuLbSpmLg', '2nEpwDK4HdY', 'zPHOjecuHpk', 'HAIOEK6eJoE',
                '9I0I_uNWr1s', 'ljAR6a5s_Mc', 'ackaozzwzZQ', 'PhwXHuHqsxs', 'Nre6tbuRl1c', 'jHQKMT1tJG8',
                'QDLkU8VqXDc', 'c2KiigWWudQ', 'wE_kx5msWTQ', 'Y5iDqjIliAs', '4h_A0XLpjuw', 'Y8h6orzxnPk',
                'EapSGzhqrzw', 'OR-V6Qle6Os', 'EK11XEU1a6E', 'PoDtTw_JRGQ', '_r4Tx2utP1A', 'HlFt6nmO9Dc',
                'QVx5Z8Aook4', 'lqQNglnvAUU', '60RN3tyi5f0', 'krX3_DKFTkI', 'eqt8e8vZ6TU', 'M2gNGmBOlTc',
                'KiLJ-q8WNJM', 'Gq5dH_zdP9c', 'Vy1dtq999Xk', 'xjA6ByNxgyc', 'qUaJY3qCwRA', 'OSLPKzU9FAg',
                'MsxUGgtm9Ok', 'qhsJp5bD98k', '1LnSgVkEW9o', 'zmdF-5uknZg', '7JlAHlGnaLE', 'UnvrsOg-oKE',
                'H-OnXHeThyE', 'Oh9fIDPP2JA', '8CcQzai7VhE', 'bVgSKx879Dw', '3TkOs6uh5K0', 'VuznzG9X2kk',
                'nsAgBH2xXUM', '5HRbsKyDjgI', 'b7XyJTtnUZ8', 'l-ETe9qk6-o', 'hIo94pZKdt4', 'L_wg--lh5Rs',
            ],
            'Lighters & Torches' => [
                '0DpJ5fAKhiM', 'XuoNdWpGVyg', 'N2IsE4cTP-U', '18yyKavYGxg', '_8BT1VUgYns', 'g0wOK89tfwc',
                'ZkoltNLTI_o', 'LGDHq2ulXyc', 'BV7B78jCohQ', 'pJFtsGmfMo4', 'j3ZeJ79sDtU', 'oNddwzEZMzo',
                '0nMHMKUcX6E', 'BDkPgo8GlnY', '7DC4e6Uz398', 'iL9gr69ZoWI', 'vZ1hyrPT6JU', 'UTA9RYPfCnE',
                'brj8qF48k8I', 'sWNGrCjXZDw', 'TOrv0qoVCnU', 'f1azCHoc4FE', 'aVerbxC3uSo', '89AqAOUo808',
                'G1uYkv_f52I', 'MHZ59YNZQn0', 'h2ySGiHxBjk', 'Xd5AVEj2Vyw', '8d1W2vZNDig', '5UCv9HQ3n-w',
                'LWFatG_G_q8', 'G3tzfIDywIY', 'mrIUEoVaudY', 'qepBNRLGBIU', 'RMkT8KNIRkU', '0QiHGZthDYs',
                '9pyCY5rioFU', 'Q8hPoxemqBU', 'eBTUSPt3x1M', '3upHpX2dhck', 'YdyqmvohV5Y', 'sg1zsbnKYj0',
                'c2Wm69FZciI', 'bH49mcwqHng', 'djSfgOLsJIU', 'yczQU7h9ATw', 'xUTXwUWM944',
            ],
            'Grinders & Trays' => [
                'uIF0I5EkXzM', 'iF_yufpywHg', 'nSkOJ9NElS8', '75YHJn9hi54', 'liR8vm1reVw', 'K6M4gAjy7BI',
                'qN0Zz4TZ7SE', 'MwLdUdYqzFo', 'mFet8rEQDg0', 'NLTQcXAWBBQ', 'ivtMKnIKEjw', 'MFiy3RWkzUA',
                'Rqvo_BSWy6c', 'U34QF2jGkKY', 'zVpe61eMIak', 'R6RUWCgCc8s', 'iJqrbn_NHO4', 'c89YuXhMIyM',
                'iilaIgguWJw', '26XO7LTIi_M',
            ],
            'Smoke Shop Accessories' => [
                'qbMuWBWJX1M', 'eooNc9c8MLo', 'T6LRPNIFEIQ', 'Nl7ZkuhiHiM', 'wRbgXerhpMI', 'k8w3_5FQEZo',
                'DypqA4DFVV4', 'k4diO-GF0GA', 'G3t1dOF0xzI', 'OcYvdqV6qtQ', '8ENtSB4TV_U', 'p84LCR46h2Q',
                'PvZZvxdqb60', 'xS3vxfx0STI', 'FsFqzFhA8uE', 'QBTRHXYAv9o', 'eVnQ-3vg_78', '545_iPjH9aY',
                '4ofRnGXD2NE', 'Dz5kXaSj918', 'iIu2qy-Vf8Q', 'b81t3hbAtPk', '-Hazg-X7F7Y', '3Sk2SygugvI',
                'okZ2fdtbU1Q', 'HGt3EDe94sI', 'PriL_cZkUAk', 'axaTqjdJccE', '1J6W149N3nI',
            ],
        ];
    }


    protected function products(): array
    {
        $brands = $this->brands();
        $rows = [];
        $push = function (string $name, string $brand, string $category, string $case, float $price, string $blurb) use (&$rows) {
            $rows[] = [
                'name' => $name,
                'brand' => $brand,
                'category' => $category,
                'case' => $case,
                'price' => round($price, 2),
                'blurb' => $blurb,
            ];
        };

        $disp = [
            'Pulse 5000 Disposable', 'Arctic Drift Bar', 'Nova Ice Stick', 'Ember Glow 6K', 'Harbor Mist Pen',
            'Volt Cloud 8K', 'Apex Draw Bar', 'Silk Stream Disposable', 'Nightfall 3500', 'Copper Tip Stick',
            'Ridge Line 7K', 'Foghorn Disposable', 'Marble Ice Bar', 'Summit Puff 9K', 'Cedar Smoke Stick',
            'Ion Mesh Disposable', 'Quartz Loop Bar', 'Tidal 4K Stick', 'Obsidian Draw', 'Lumen Pocket Bar',
            'Brass Ring Disposable', 'Cinder Trail 5K', 'Maple Frost Stick', 'Pilot Cloud Pen', 'Nimbus 10K Bar',
            'Sable Drift Disposable', 'Ironbark Stick', 'Halo Mint Bar', 'Dune Heat 6K', 'Cove Breeze Pen',
            'Flint Spark Disposable', 'Aurora Ice Stick', 'Granite Puff Bar', 'Wick & Wire 8K', 'Pebble Draw Stick',
            'Stormline Disposable', 'Amber Coil Bar', 'North Pier 7K', 'Velvet Fog Stick', 'Compass Cloud Pen',
            'Redwood Disposable', 'Glacier Mesh Bar', 'Lantern 4K Stick', 'Ashlar Draw', 'Tidepool Pocket Bar',
            'Slingshot Disposable', 'Pinecrest Stick', 'Moonwell Ice Bar', 'Breaker 9K', 'Harborline Pen',
            'Kestrel Disposable', 'Slate Coil Bar', 'West End 5K', 'Cinderbox Stick', 'Onyx Stream Pen',
            'Falcon Ridge Disposable', 'Lowland Ice Bar', 'Beacon 6K Stick', 'Driftwood Draw', 'Silverwake Bar',
            'Palisade Disposable', 'Emberfork Stick', 'Glasshouse Ice', 'Riptide 8K', 'Northstar Pen',
            'Cobalt Disposable', 'Windrow Bar', 'Hearthline 3500', 'Soot & Silk Stick', 'Clearwater Pen',
            'Bramble Disposable', 'Ironclad Ice Bar', 'Lookout 7K', 'Softglow Stick', 'Daymark Cloud Pen',
            'Thistle Disposable', 'Copperhead Bar', 'Seagate 9K Stick', 'Kindling Draw', 'Afterglow Pocket Bar',
        ];
        foreach ($disp as $n => $title) {
            $brand = $brands[$n % 3];
            $push($brand.' '.$title, $brand, 'Disposable Vapes', 'CASE: 10CT', 64.5 + ($n % 18) * 2.5, 'Wholesale disposable vape. 10-count case.');
        }

        $devices = [
            'Aegis Pod Kit', 'Draw AIO System', 'Mesh Tank Kit', 'Rugged Box Mod', 'USB-C Charge Dock',
            'Empty Refill Pods', 'Replacement Coil 5pk', 'Ceramic Pod System', 'Leather-Wrap Mod', 'Glass Drip Tip Set',
            'Side-Fill Pod Kit', 'Dual-Battery Box', 'MTL Starter Kit', 'DL Sub-Ohm Kit', 'Magnetic Pod Cradle',
            'Carbon Fiber Mod', 'Top-Airflow Tank', 'Rebuildable AIO', 'Travel Pouch Kit', 'Screened Pod Device',
            'Compact Draw Pen', 'External 18650 Mod', 'Bubble Glass Tank', 'Silicone Sleeve Kit', 'Quick-Swap Coil Head',
            ' palo',
        ];
        $devices = array_values(array_filter($devices, fn ($t) => $t !== ' palo'));
        $deviceExtra = [
            'Nightshade Pulse Pod', 'CloudLine Forge Mod', 'Drift Coil Nano Kit', 'Harbor Leaf Draw Pen',
            'Ember Oak Tank Kit', 'Lost Coast AIO', 'Black Ember Box Mod', 'Velvet Puff Pod',
            'Iron Flint Charge Dock', 'Ash Ember Sleeve Kit', 'CloudLine Mesh Head 5pk', 'Nightshade Empty Pods',
            'Drift Coil Drip Tips', 'Harbor Leaf Travel Kit', 'Ember Oak MTL Kit',
        ];
        $devices = array_merge($devices, $deviceExtra);
        foreach (array_slice($devices, 0, 40) as $n => $title) {
            $brand = $brands[$n % 3 === 0 ? 2 : $n % 3];
            $push($brand.' '.$title, $brand, 'Vape Devices & Pods', 'CASE: 8CT', 48 + ($n % 12) * 9, 'Vape hardware, case packed for retailers.');
        }

        $liquids = [
            'Frozen Mint Salt 30ml', 'Blue Razz Freebase 60ml', 'Mango Ice Nic Salt', 'Watermelon Ice 30ml',
            'Grape Ice Salt 30ml', 'Peach Rings Freebase', 'Menthol Blast 30ml', 'Tobacco Gold 60ml',
            'Strawberry Kiwi Salt', 'Iced Apple Freebase', 'Berry Frost 30ml', 'Vanilla Custard 60ml',
            'Cool Mint Salt 30ml', 'Classic Tobacco 60ml', 'Pineapple Ice Salt', 'Lychee Ice 30ml',
            'Banana Ice Freebase', 'Cola Fizz Salt 30ml', 'Sour Apple 60ml', 'Dragonfruit Ice',
            'Cucumber Mint Salt', 'Lemon Ice 30ml', 'Coffee Cream 60ml', 'Toffee Custard Salt',
            'Honeydew Ice 30ml', 'Blackberry Menthol', 'Guava Ice Freebase', 'Spearmint Salt 30ml',
            'Cherry Cola 60ml', 'Wintergreen Salt', 'Passionfruit Ice', 'Butterscotch 60ml',
            'Coconut Ice Salt', 'Lime Freeze 30ml', 'Hazelnut Tobacco', 'Raspberry Menthol',
            'Orange Cream 60ml', 'Peppermint Ice Salt', 'Mango Tango 30ml', 'Nightshade Dessert 60ml',
        ];
        foreach ($liquids as $n => $title) {
            $brand = $brands[$n % 10];
            $push($brand.' '.$title, $brand, 'E-Liquids & Nic Salts', 'CASE: 12CT', 54 + ($n % 8) * 4, 'Nic salt / freebase, 12-count wholesale case.');
        }

        $cigars = [
            'Robusto Reserva 25ct', 'Churchill Maduro 20ct', 'Toro Connecticut 25ct', 'Corona Claro Box',
            'Sampler Humidor 8pk', 'Double Corona Oscuro', 'Petit Corona Natural', 'Gordo Maduro 20ct',
            'Lonsdale Connecticut', 'Torpedo Reserva Box', 'Panatela Claro 25ct', 'Belicoso Maduro 20ct',
            'Robusto Broadleaf', 'Churchill Connecticut', 'Toro Oscuro 25ct', 'Corona Gorda Box',
            'Figurado Sampler 6pk', 'Short Robusto Natural', 'Churchill Broadleaf', 'Toro Claro 20ct',
            'Perfecto Reserva', 'Lancero Maduro Box', 'Robusto Shade Grown', 'Churchill Oscuro 20ct',
            'Toro Maduro 25ct', 'Corona Extra Claro', 'Cabinet Sampler 10pk', 'Double Toro Natural',
            'Petit Robusto Box', 'Belicoso Connecticut', 'Gordo Claro 20ct', 'Panatela Maduro',
            'Torpedo Oscuro Box', 'Lonsdale Natural', 'Robusto Sungrown', 'Churchill Claro 20ct',
            'Toro Broadleaf 25ct', 'Corona Maduro Box', 'Figurado Reserva', 'Short Churchill Natural',
        ];
        foreach ($cigars as $n => $title) {
            $brand = $n % 2 ? 'Harbor Leaf Cigars' : 'Ember & Oak Cigars';
            $push($brand.' '.$title, $brand, 'Cigars', 'CASE: 4 BOX', 142 + ($n % 10) * 6, 'Boxed cigars for licensed retailers.');
        }

        $cigarillos = [
            'Sweet Tip 50ct Tin', 'Natural Leaf 50ct Tin', 'Honey Tip 50ct', 'Wine Tip 50ct',
            'Vanilla Tip 50ct', 'Original Natural 50ct', 'Grape Tip 50ct', 'Wood Tip 50ct',
            'Dark Natural 50ct', 'Cream Tip 50ct', 'Cherry Tip 50ct', 'Filter Tip 50ct',
            'Gold Foil 50ct Tin', 'Silver Foil 50ct', 'Mini Sweet 50ct', 'Mini Natural 50ct',
            'Russet Leaf 50ct', 'Cocoa Tip 50ct', 'Maple Tip 50ct', 'Black Natural 50ct',
        ];
        foreach ($cigarillos as $n => $title) {
            $push('Ash & Ember Smoke '.$title, 'Ash & Ember Smoke', 'Cigarillos', 'CASE: 10CT', 88 + $n, 'Cigarillo tins, 10-count wholesale case.');
        }

        $papers = [
            'King Size Unbleached 50ct', '1.25 Classic White 50ct', 'Hemp Wraps Natural 25ct', 'Pre-Roll Cones 100ct',
            'Organic Hemp Kings 50ct', 'Rice Paper 1.25 50ct', 'Slow-Burn Brown 50ct', 'Flavored Wraps Grape 25ct',
            'King Size Slim 50ct', 'Double Wide 50ct', 'Hemp Cones 50ct', 'Unbleached 1.25 50ct',
            'Clear Cellulose 50ct', 'Gold Leaf Wraps 25ct', 'Corn Husk Wraps 25ct', 'King Size Hemp 50ct',
            'Perfect Fold 1.25 50ct', 'Ultra Thin Kings 50ct', 'Berry Wraps 25ct', 'Mint Wraps 25ct',
            'Cone Loader Kit 100ct', 'Organic 1.25 50ct', 'Arabic Style 50ct', 'Palm Leaf Wraps 25ct',
            'Nightshade Slow-Burn 50ct',
        ];
        foreach ($papers as $n => $title) {
            $brand = $n % 2 ? 'Velvet Puff Papers' : 'Nightshade Vapes';
            $push($brand.' '.$title, $brand, 'Rolling Papers & Wraps', 'CASE: 50CT', 32 + ($n % 6) * 3, 'Papers, wraps, and cones for the smoke counter.');
        }

        $hookah = [
            'Stainless Stem Set', 'Egyptian Clay Bowl', 'Silicone Hose Black', 'Coconut Charcoal 1kg',
            'Hand-Cut Base Glass', 'Phunnel Bowl Glazed', 'Leather-Wrap Hose', 'Quick-Light Coals 1kg',
            'Modern Stem Chrome', 'Vortex Bowl Set', 'Ice Chamber Adapter', 'Natural Coco Cubes 1kg',
            'Wide-Base Glass', 'Traditional Clay Head', 'Washable Hose Set', 'Cube Charcoal 2kg',
            'Gold-Tone Stem', 'Silicone Bowl Insert', 'Wooden-Tip Hose', 'Flake Charcoal 1kg',
        ];
        foreach ($hookah as $n => $title) {
            $brand = $brands[3 + ($n % 3)];
            $push($brand.' '.$title, $brand, 'Hookah & Shisha', 'CASE: 4CT', 48 + ($n % 9) * 12, 'Hookah hardware and charcoal for retailers.');
        }

        $flame = [
            'Triple Jet Torch', 'Soft Flame Pocket', 'Stick Lighter Display 50ct', 'Dual Torch Mini',
            'Table Cigar Jet', 'Single Jet Punch Torch', 'Windproof Soft Flame', 'Butane Refill 12ct',
            'Quad Jet Table Torch', 'Slim Soft Flame', 'Cigar Punch Combo', 'Matte Black Dual Jet',
            'Vintage Soft Flame', 'Clear-Tank Stick 50ct', 'Standing Table Torch',
        ];
        foreach ($flame as $n => $title) {
            $brand = $n % 2 ? 'Iron Flint Torches' : 'Nightshade Vapes';
            $push($brand.' '.$title, $brand, 'Lighters & Torches', 'CASE: 12CT', 28 + ($n % 8) * 7, 'Flame for cigars and the smoke counter.');
        }

        $grinders = [
            '4-Piece Aluminum Grinder', 'Bamboo Rolling Tray', '2-Piece Pocket Grinder', 'Magnetic Lid Tray',
            'Clear-Top 4-Piece', 'Leather-Trim Tray', 'Hex Body Grinder', 'Wood Inlay Tray',
            'Titanium-Look Grinder', 'Oversized Shop Tray',
        ];
        foreach ($grinders as $n => $title) {
            $push('Nightshade Vapes '.$title, 'Nightshade Vapes', 'Grinders & Trays', 'CASE: 12CT', 58 + $n * 2, 'Counter grinders and rolling trays.');
        }

        $acc = [
            'Desktop Humidor 25ct', 'Cigar Cotton Tips 200ct', 'Travel Tool Kit', 'Double Guillotine Cutter',
            'Glass Ashtray 6ct', 'V-Cut Cigar Cutter', 'Cedar Humidor Tray', 'Punch Cutter 12ct',
            'Hygrometer Pack', 'Leather Cigar Case',
        ];
        foreach ($acc as $n => $title) {
            $brand = $brands[7 + ($n % 3)];
            $push($brand.' '.$title, $brand, 'Smoke Shop Accessories', 'CASE: 6CT', 34 + $n * 14, 'Smoke shop counter accessories.');
        }

        foreach ($rows as $i => &$row) {
            $row['image'] = $this->uniqueImage($row['category'], $i);
            $row['new'] = $i % 9 === 2;
            $row['sale'] = $i % 11 === 4 ? round($row['price'] * 0.88, 2) : null;
        }
        unset($row);

        foreach ([221 => 'E2eceUW_XD0', 298 => 'k4diO-GF0GA'] as $n => $videoId) {
            if (isset($rows[$n - 1])) {
                $rows[$n - 1]['image'] = $this->googleCdn($videoId);
            }
        }

        $featuredSlots = [];
        $byCat = [];
        foreach ($rows as $i => $row) {
            $byCat[$row['category']][] = $i;
        }
        foreach ($byCat as $indexes) {
            if (count($featuredSlots) >= 12) {
                break;
            }
            $featuredSlots[] = $indexes[0];
        }
        $n = 1;
        while (count($featuredSlots) < 12) {
            foreach ($byCat as $indexes) {
                if (count($featuredSlots) >= 12) {
                    break;
                }
                if (isset($indexes[$n])) {
                    $featuredSlots[] = $indexes[$n];
                }
            }
            $n++;
        }
        foreach ($featuredSlots as $i) {
            $rows[$i]['featured'] = 1;
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
        $ctx = stream_context_create([
            'http' => [
                'header' => "User-Agent: Mozilla/5.0 (compatible; JapsPOS-catalog/1.0)\r\n",
                'timeout' => 20,
            ],
            'ssl' => ['verify_peer' => true],
        ]);
        $bytes = @file_get_contents($url, false, $ctx);
        if ($bytes !== false && $bytes !== '') {
            File::put($dest, $bytes);
        }
    }
}
