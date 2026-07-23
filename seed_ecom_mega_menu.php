<?php

/**
 * Seeds brands + snack subcategories for wholesale mega menus.
 * Run: php seed_ecom_mega_menu.php
 */

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Brands;
use App\Category;
use App\Product;
use App\User;
use Illuminate\Support\Str;

$business_id = 1;
$user_id = User::where('business_id', $business_id)->value('id') ?? 1;

$brandNames = [
    'American Club', 'Bugler', 'Grabba Leaf', 'Royal Blunts', 'Phillies',
    'Hype', 'Jungo Leaf', 'Throwback', 'Big Dog', 'Hi-Fi',
    'Artisan Snacks', 'Cocoa House', 'Citrus Wave', 'Fresh Care', 'Home Basics',
];

$brands = [];
foreach ($brandNames as $name) {
    $brands[$name] = Brands::withTrashed()->firstOrCreate(
        ['business_id' => $business_id, 'name' => $name],
        ['created_by' => $user_id, 'description' => $name]
    );
    if ($brands[$name]->trashed()) {
        $brands[$name]->restore();
    }
}

$snackParent = Category::where('business_id', $business_id)
    ->where('name', 'Snacks')
    ->where(function ($q) {
        $q->whereNull('parent_id')->orWhere('parent_id', 0);
    })
    ->first();

$snackSubs = ['Chips & Cookies', 'Chocolate', 'Gum', 'Gummies & Chews', 'Lollipops', 'Mints', 'Peg Bag', '5lb Bag'];
if ($snackParent) {
    foreach ($snackSubs as $sub) {
        Category::firstOrCreate(
            [
                'business_id' => $business_id,
                'name' => $sub,
                'category_type' => 'product',
                'parent_id' => $snackParent->id,
            ],
            [
                'short_code' => Str::slug($sub),
                'created_by' => $user_id,
            ]
        );
    }
}

// Map ecommerce products to brands by category
$brandCycle = array_values($brands);
$i = 0;
$products = Product::where('business_id', $business_id)
    ->whereIn('sales_channel', ['pos_ecommerce', 'ecommerce_only'])
    ->get();

foreach ($products as $p) {
    $brand = $brandCycle[$i % count($brandCycle)];
    $p->brand_id = $brand->id;
    $p->save();
    $i++;
}

echo 'Brands: '.count($brands).PHP_EOL;
echo 'Snack subs: '.($snackParent ? count($snackSubs) : 0).PHP_EOL;
echo 'Products branded: '.$products->count().PHP_EOL;
