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
use Modules\Ecommerce\Http\Controllers\Front\QuickOrderController;
use Modules\Ecommerce\Http\Controllers\Front\EcomPwaController;
use Modules\Ecommerce\Http\Controllers\Front\ShopController;
use Modules\Ecommerce\Http\Controllers\Front\StorePageController;
use Modules\Ecommerce\Http\Controllers\InstallController;

// Ecommerce storefront PWA (public — customers only; not admin panel)
Route::get('/ecom-pwa/manifest.webmanifest', [EcomPwaController::class, 'manifest'])
    ->name('ecommerce.pwa.manifest');
Route::get('/ecom-pwa/sw.js', [EcomPwaController::class, 'serviceWorker'])
    ->name('ecommerce.pwa.sw');
Route::get('/ecom-pwa/offline', [EcomPwaController::class, 'offline'])
    ->name('ecommerce.pwa.offline');

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
        Route::get('orders/{id}/pdf', [OrderController::class, 'downloadPdf'])->name('orders.pdf');
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
        Route::get('customers/{id}', [CustomerController::class, 'show'])->name('customers.show');
        Route::post('customers/{id}/status', [CustomerController::class, 'updateStatus'])->name('customers.status');
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
        // Catalog lives at /shop (not /shop/shop)
        Route::get('/', [ShopController::class, 'index'])->name('shop');
        Route::get('/shop', function () {
            return redirect('/shop', 301);
        });

        Route::get('/home', [HomeController::class, 'index'])->name('home');
        Route::get('/c/{slug}', [ShopController::class, 'category'])->name('category');
        Route::get('/p/{slug}', [ProductDetailController::class, 'show'])->name('product');
        Route::get('/collections/{slug}', [CollectionFrontController::class, 'show'])->name('collection');
        Route::get('/contact', [ContactPageController::class, 'index'])->name('contact');
        Route::post('/contact', [ContactPageController::class, 'send'])->name('contact.send');
        Route::post('/subscribe', [StorePageController::class, 'subscribe'])->name('subscribe');
        Route::get('/categories', [StorePageController::class, 'categories'])->name('categories');
        Route::get('/brands', [StorePageController::class, 'brands'])->name('brands');
        Route::get('/brand/{id}', [StorePageController::class, 'brand'])->name('brand.show');
        Route::post('/brand-request', [StorePageController::class, 'brandRequest'])->name('brand.request');
        Route::get('/promotions/{id?}', [StorePageController::class, 'promotions'])->name('promotions');
        Route::get('/new-arrivals/{sub?}', [StorePageController::class, 'newArrivals'])->name('new_arrivals');
        Route::get('/search', [StorePageController::class, 'search'])->name('search');
        Route::get('/about', fn () => app(StorePageController::class)->page('about'))->name('about');
        Route::get('/faq', fn () => app(StorePageController::class)->page('faq'))->name('faq');
        Route::get('/terms', fn () => app(StorePageController::class)->page('terms'))->name('terms');
        Route::get('/privacy', fn () => app(StorePageController::class)->page('privacy'))->name('privacy');
        Route::get('/shipping', fn () => app(StorePageController::class)->page('shipping'))->name('shipping');
        Route::get('/returns', fn () => app(StorePageController::class)->page('returns'))->name('returns');
        Route::get('/compliance', fn () => app(StorePageController::class)->page('compliance'))->name('compliance');
        Route::get('/wholesale-agreement', fn () => app(StorePageController::class)->page('wholesale-agreement'))->name('wholesale_agreement');
        Route::post('/stock-alert', [ProductDetailController::class, 'stockAlert'])->name('stock_alert');
        Route::get('/cart', [CartController::class, 'index'])->name('cart');
        Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
        Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
        Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
        Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
        Route::post('/cart/coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon');

        Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
        Route::post('/checkout', [CheckoutController::class, 'place'])->name('checkout.place');
        Route::get('/order/thanks/{token}', [CheckoutController::class, 'thanks'])->name('thanks');
        Route::get('/track', [CheckoutController::class, 'track'])->name('track');

        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.post');
        Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [AuthController::class, 'register'])->name('register.post');
        Route::get('/account/apply', [AuthController::class, 'showRegister'])->name('account.apply');
        Route::get('/forgot-password', [AuthController::class, 'showForgot'])->name('password.request');
        Route::post('/forgot-password', [AuthController::class, 'sendReset'])->name('password.email');
        Route::get('/reset-password/{token}', [AuthController::class, 'showReset'])->name('password.reset');
        Route::post('/reset-password', [AuthController::class, 'updatePassword'])->name('password.update');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        Route::middleware('auth:customer')->group(function () {
            Route::get('/quick-order', [QuickOrderController::class, 'index'])->name('quick_order');
            Route::post('/quick-order', [QuickOrderController::class, 'submit'])->name('quick_order.submit');
            Route::get('/account', [AccountController::class, 'index'])->name('account');
            Route::get('/account/orders', [AccountController::class, 'orders'])->name('account.orders');
            Route::match(['get', 'post'], '/account/licenses', [AccountController::class, 'licenses'])->name('account.licenses');
            Route::match(['get', 'post'], '/account/profile', [AccountController::class, 'profile'])->name('account.profile');
            Route::match(['get', 'post'], '/account/password', [AccountController::class, 'password'])->name('account.password');
            Route::match(['get', 'post'], '/account/addresses', [AccountController::class, 'addresses'])->name('account.addresses');
            Route::get('/wishlist', [AccountController::class, 'wishlist'])->name('wishlist');
            Route::post('/wishlist', [AccountController::class, 'toggleWishlist'])->name('wishlist.toggle');
            Route::post('/review', [AccountController::class, 'review'])->name('review');
        });
    });

// Figma-site URL aliases at `/` (do NOT claim /login, /register, /account, /products — POS owns those).
Route::middleware(['setData', 'ecommerce.enabled'])->group(function () {
    Route::get('/product/{slug}', [ProductDetailController::class, 'show']);
    Route::get('/category/{slug}', [ShopController::class, 'category']);
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::post('/cart/update', [CartController::class, 'update']);
    Route::post('/cart/remove', [CartController::class, 'remove']);
    Route::post('/cart/clear', [CartController::class, 'clear']);
    Route::post('/cart/coupon', [CartController::class, 'applyCoupon']);
    Route::get('/checkout', [CheckoutController::class, 'index']);
    Route::post('/checkout', [CheckoutController::class, 'place']);
    Route::get('/promotions/{id?}', [StorePageController::class, 'promotions']);
    Route::get('/new-arrivals/{sub?}', [StorePageController::class, 'newArrivals']);
    Route::get('/search', [StorePageController::class, 'search']);
    Route::get('/contact', [ContactPageController::class, 'index']);
    Route::post('/contact', [ContactPageController::class, 'send']);
    Route::post('/subscribe', [StorePageController::class, 'subscribe']);
    // Note: /brands and /brand/{id} are owned by POS BrandController — use /shop/brands
    Route::get('/categories', [StorePageController::class, 'categories']);
    Route::get('/about', fn () => app(StorePageController::class)->page('about'));
    Route::get('/faq', fn () => app(StorePageController::class)->page('faq'));
    Route::get('/terms', fn () => app(StorePageController::class)->page('terms'));
    Route::get('/privacy', fn () => app(StorePageController::class)->page('privacy'));
    Route::get('/shipping', fn () => app(StorePageController::class)->page('shipping'));
    Route::get('/returns', fn () => app(StorePageController::class)->page('returns'));
    Route::get('/compliance', fn () => app(StorePageController::class)->page('compliance'));
    Route::get('/wholesale-agreement', fn () => app(StorePageController::class)->page('wholesale-agreement'));
    Route::get('/forgot-password', [AuthController::class, 'showForgot']);
    Route::post('/forgot-password', [AuthController::class, 'sendReset']);
    // Figma uses /account/apply — map to storefront register without touching POS /account
    Route::get('/account/apply', [AuthController::class, 'showRegister']);
});
