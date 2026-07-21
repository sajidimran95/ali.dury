<?php

use Illuminate\Support\Facades\Route;
use Modules\Ecommerce\Http\Controllers\Admin\BannerController;
use Modules\Ecommerce\Http\Controllers\Admin\CollectionController;
use Modules\Ecommerce\Http\Controllers\Admin\CouponController;
use Modules\Ecommerce\Http\Controllers\Admin\CustomerController;
use Modules\Ecommerce\Http\Controllers\Admin\DashboardController;
use Modules\Ecommerce\Http\Controllers\Admin\OrderController;
use Modules\Ecommerce\Http\Controllers\Admin\ProductController as AdminProductController;
use Modules\Ecommerce\Http\Controllers\Admin\ReportController;
use Modules\Ecommerce\Http\Controllers\Admin\ReviewController;
use Modules\Ecommerce\Http\Controllers\Admin\SettingsController;
use Modules\Ecommerce\Http\Controllers\Admin\ShippingTaxController;
use Modules\Ecommerce\Http\Controllers\Front\AccountController;
use Modules\Ecommerce\Http\Controllers\Front\AuthController;
use Modules\Ecommerce\Http\Controllers\Front\CartController;
use Modules\Ecommerce\Http\Controllers\Front\CheckoutController;
use Modules\Ecommerce\Http\Controllers\Front\CollectionFrontController;
use Modules\Ecommerce\Http\Controllers\Front\ContactPageController;
use Modules\Ecommerce\Http\Controllers\Front\HomeController;
use Modules\Ecommerce\Http\Controllers\Front\ProductDetailController;
use Modules\Ecommerce\Http\Controllers\Front\ShopController;
use Modules\Ecommerce\Http\Controllers\InstallController;

Route::middleware(['setData', 'auth', 'SetSessionData', 'language', 'timezone', 'AdminSidebarMenu', 'CheckUserLogin'])
    ->prefix('ecommerce')
    ->name('ecommerce.admin.')
    ->group(function () {
        Route::get('install', [InstallController::class, 'index'])->name('install');
        Route::post('install', [InstallController::class, 'install']);

        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('products', [AdminProductController::class, 'index'])->name('products');
        Route::get('orders', [OrderController::class, 'index'])->name('orders');
        Route::get('orders/{id}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.status');

        Route::get('banners', [BannerController::class, 'index'])->name('banners');
        Route::post('banners', [BannerController::class, 'store'])->name('banners.store');
        Route::delete('banners/{id}', [BannerController::class, 'destroy'])->name('banners.destroy');

        Route::get('collections', [CollectionController::class, 'index'])->name('collections');
        Route::post('collections', [CollectionController::class, 'store'])->name('collections.store');
        Route::delete('collections/{id}', [CollectionController::class, 'destroy'])->name('collections.destroy');

        Route::get('coupons', [CouponController::class, 'index'])->name('coupons');
        Route::post('coupons', [CouponController::class, 'store'])->name('coupons.store');
        Route::delete('coupons/{id}', [CouponController::class, 'destroy'])->name('coupons.destroy');

        Route::get('reviews', [ReviewController::class, 'index'])->name('reviews');
        Route::post('reviews/{id}/status', [ReviewController::class, 'updateStatus'])->name('reviews.status');

        Route::get('customers', [CustomerController::class, 'index'])->name('customers');
        Route::get('reports', [ReportController::class, 'index'])->name('reports');

        Route::get('settings', [SettingsController::class, 'index'])->name('settings');
        Route::post('settings', [SettingsController::class, 'update'])->name('settings.update');

        Route::get('shipping-tax', [ShippingTaxController::class, 'index'])->name('shipping_tax');
        Route::post('shipping-tax/shipping', [ShippingTaxController::class, 'storeShipping'])->name('shipping.store');
        Route::post('shipping-tax/tax', [ShippingTaxController::class, 'storeTax'])->name('tax.store');
    });

Route::prefix(config('ecommerce.route_prefix', 'shop'))
    ->middleware(['ecommerce.enabled'])
    ->name('ecommerce.')
    ->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('home');
        Route::get('/shop', [ShopController::class, 'index'])->name('shop');
        Route::get('/c/{slug}', [ShopController::class, 'category'])->name('category');
        Route::get('/p/{slug}', [ProductDetailController::class, 'show'])->name('product');
        Route::get('/collections/{slug}', [CollectionFrontController::class, 'show'])->name('collection');
        Route::get('/contact', [ContactPageController::class, 'index'])->name('contact');
        Route::post('/contact', [ContactPageController::class, 'send'])->name('contact.send');

        Route::get('/cart', [CartController::class, 'index'])->name('cart');
        Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
        Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
        Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
        Route::post('/cart/coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon');

        Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
        Route::post('/checkout', [CheckoutController::class, 'place'])->name('checkout.place');
        Route::get('/order/thanks/{token}', [CheckoutController::class, 'thanks'])->name('thanks');
        Route::get('/track', [CheckoutController::class, 'track'])->name('track');

        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.post');
        Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [AuthController::class, 'register'])->name('register.post');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        Route::middleware('auth:customer')->group(function () {
            Route::get('/account', [AccountController::class, 'index'])->name('account');
            Route::get('/account/orders', [AccountController::class, 'orders'])->name('account.orders');
            Route::match(['get', 'post'], '/account/profile', [AccountController::class, 'profile'])->name('account.profile');
            Route::match(['get', 'post'], '/account/password', [AccountController::class, 'password'])->name('account.password');
            Route::match(['get', 'post'], '/account/addresses', [AccountController::class, 'addresses'])->name('account.addresses');
            Route::get('/wishlist', [AccountController::class, 'wishlist'])->name('wishlist');
            Route::post('/wishlist', [AccountController::class, 'toggleWishlist'])->name('wishlist.toggle');
            Route::post('/review', [AccountController::class, 'review'])->name('review');
        });
    });
