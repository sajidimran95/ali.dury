<?php

use App\Http\Controllers\Api\CrossStoreSyncController;
use App\Http\Controllers\Api\Mobile\CustomerApiController;
use App\Http\Controllers\Api\Mobile\DeliveryApiController;
use App\Http\Controllers\Api\Mobile\MobileAuthController;
use App\Http\Controllers\Api\Mobile\SaleApiController;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Cross-store sync (Dury Wholesale ↔ ali.dury)
|--------------------------------------------------------------------------
| Auth header: X-Cross-Store-Token
*/
Route::prefix('cross-store')->middleware('cross.store')->group(function () {
    Route::post('/product', [CrossStoreSyncController::class, 'product']);
    Route::post('/stock', [CrossStoreSyncController::class, 'stock']);
    Route::get('/stock-map', [CrossStoreSyncController::class, 'stockMap']);
    Route::post('/stock-absolute', [CrossStoreSyncController::class, 'stockAbsolute']);
    Route::post('/stock-absolute-batch', [CrossStoreSyncController::class, 'stockAbsoluteBatch']);
});

/*
|--------------------------------------------------------------------------
| Native mobile apps API v1 — Sales / Delivery / Customer
|--------------------------------------------------------------------------
| Auth: "Authorization: Bearer {token}" from each app's /auth/login endpoint.
| Docs + Postman collections: docs/mobile-api/
*/
Route::prefix('v1')->middleware(['setData', 'mobile.json'])->group(function () {
    Route::get('/ping', [MobileAuthController::class, 'ping']);

    // ------------------------------------------------------------ Sales app
    Route::prefix('sale')->group(function () {
        Route::post('/auth/login', [MobileAuthController::class, 'saleLogin']);

        Route::middleware('mobile.auth:sale')->group(function () {
            Route::get('/auth/me', [MobileAuthController::class, 'me']);
            Route::post('/auth/logout', [MobileAuthController::class, 'logout']);
            Route::post('/auth/logout-all', [MobileAuthController::class, 'logoutAll']);

            Route::get('/dashboard', [SaleApiController::class, 'apiDashboard']);
            Route::get('/account', [SaleApiController::class, 'apiAccount']);
            Route::get('/locations', [SaleApiController::class, 'apiLocations']);
            Route::post('/locations/current', [SaleApiController::class, 'apiUpdateLocation']);
            Route::post('/sync', [SaleApiController::class, 'apiSync']);

            // Orders / estimates / back orders
            Route::get('/orders', [SaleApiController::class, 'apiOrders']);
            Route::get('/orders/create', [SaleApiController::class, 'apiCreateData']);
            Route::post('/orders', [SaleApiController::class, 'apiStore']);
            Route::get('/orders/{id}', [SaleApiController::class, 'apiShowOrder'])->whereNumber('id');
            Route::get('/orders/{id}/edit', [SaleApiController::class, 'apiEditData'])->whereNumber('id');
            Route::put('/orders/{id}', [SaleApiController::class, 'apiUpdate'])->whereNumber('id');
            Route::delete('/orders/{id}', [SaleApiController::class, 'apiDestroy'])->whereNumber('id');
            Route::get('/orders/{id}/invoice', [SaleApiController::class, 'apiOrderInvoice'])->whereNumber('id');

            // Saved carts (drafts)
            Route::get('/saved-orders', [SaleApiController::class, 'apiSavedOrders']);
            Route::delete('/saved-orders/{id}', [SaleApiController::class, 'apiDestroySavedOrder']);
            Route::post('/order-draft', [SaleApiController::class, 'apiSaveDraft']);
            Route::delete('/order-draft', [SaleApiController::class, 'apiClearDraft']);

            // Catalog
            Route::get('/catalog', [SaleApiController::class, 'apiCatalog']);
            Route::get('/products', [SaleApiController::class, 'apiSearchProducts']);
            Route::get('/categories', [SaleApiController::class, 'apiCategories']);
            Route::get('/last-purchases', [SaleApiController::class, 'apiLastPurchases']);
            Route::get('/product-history', [SaleApiController::class, 'apiProductHistory']);

            // Customers
            Route::get('/customers', [SaleApiController::class, 'apiCustomers']);
            Route::get('/customers/search', [SaleApiController::class, 'apiSearchCustomers']);
            Route::get('/customers/create', [SaleApiController::class, 'apiCustomerCreateData']);
            Route::post('/customers', [SaleApiController::class, 'apiStoreCustomer']);
            Route::get('/customers/{id}', [SaleApiController::class, 'apiShowCustomer'])->whereNumber('id');
            Route::get('/customers/{id}/invoices', [SaleApiController::class, 'apiCustomerOpenInvoices'])->whereNumber('id');
            Route::get('/customers/{id}/invoices/{invoiceId}/print', [SaleApiController::class, 'apiCustomerInvoicePrint'])->whereNumber(['id', 'invoiceId']);
            Route::get('/customers/{id}/history', [SaleApiController::class, 'apiCustomerHistory'])->whereNumber('id');
            Route::get('/customers/{id}/payment', [SaleApiController::class, 'apiCustomerPaymentData'])->whereNumber('id');
            Route::post('/customers/{id}/payment', [SaleApiController::class, 'apiStoreCustomerPayment'])->whereNumber('id');
            Route::post('/customers/{id}/zero-sale', [SaleApiController::class, 'apiCustomerZeroSale'])->whereNumber('id');
            Route::get('/zero-sale-reasons', [SaleApiController::class, 'apiZeroSaleReasons']);

            // Expenses
            Route::get('/expenses', [SaleApiController::class, 'apiExpenses']);
            Route::get('/expenses/create', [SaleApiController::class, 'apiExpenseCreateData']);
            Route::post('/expenses', [SaleApiController::class, 'apiStoreExpense']);

            // Rep's own delivery route
            Route::get('/delivery', [SaleApiController::class, 'apiDelivery']);

            // Timesheet (time clock)
            Route::get('/timesheet', [SaleApiController::class, 'apiTimesheet']);
            Route::get('/timesheet/status', [SaleApiController::class, 'apiClockStatus']);
            Route::post('/timesheet/clock-in', [SaleApiController::class, 'apiClockIn']);
            Route::post('/timesheet/start-break', [SaleApiController::class, 'apiStartBreak']);
            Route::post('/timesheet/end-break', [SaleApiController::class, 'apiEndBreak']);
            Route::post('/timesheet/clock-out', [SaleApiController::class, 'apiClockOut']);

            // Stock transfers
            Route::get('/transfers', [SaleApiController::class, 'apiTransfers']);
            Route::get('/transfers/create', [SaleApiController::class, 'apiTransferCreateData']);
            Route::get('/transfers/products', [SaleApiController::class, 'apiTransferProducts']);
            Route::post('/transfers', [SaleApiController::class, 'apiStoreTransfer']);
            Route::get('/transfers/{id}', [SaleApiController::class, 'apiShowTransfer'])->whereNumber('id');
        });
    });

    // --------------------------------------------------------- Delivery app
    Route::prefix('delivery')->group(function () {
        Route::post('/auth/login', [MobileAuthController::class, 'deliveryLogin']);

        Route::middleware('mobile.auth:delivery')->group(function () {
            Route::get('/auth/me', [MobileAuthController::class, 'me']);
            Route::post('/auth/logout', [MobileAuthController::class, 'logout']);
            Route::post('/auth/logout-all', [MobileAuthController::class, 'logoutAll']);

            Route::get('/dashboard', [DeliveryApiController::class, 'home']);
            Route::get('/route', [DeliveryApiController::class, 'route']);
            Route::post('/route/start', [DeliveryApiController::class, 'start']);
            Route::post('/location', [DeliveryApiController::class, 'location']);
            Route::get('/assigned', [DeliveryApiController::class, 'assigned']);
            Route::get('/all', [DeliveryApiController::class, 'all']);
            Route::get('/history', [DeliveryApiController::class, 'history']);
            Route::get('/fail-reasons', [DeliveryApiController::class, 'failReasons']);
            Route::get('/stops/{stop}', [DeliveryApiController::class, 'showStop'])->whereNumber('stop');
            Route::post('/stops/{stop}/arrived', [DeliveryApiController::class, 'arrived'])->whereNumber('stop');
            Route::post('/stops/{stop}/delivered', [DeliveryApiController::class, 'delivered'])->whereNumber('stop');
            Route::post('/stops/{stop}/failed', [DeliveryApiController::class, 'failed'])->whereNumber('stop');
            Route::post('/stops/{stop}/notes', [DeliveryApiController::class, 'notes'])->whereNumber('stop');
        });
    });

    // --------------------------------------------------------- Customer app
    Route::prefix('customer')->group(function () {
        Route::post('/auth/login', [MobileAuthController::class, 'customerLogin']);

        Route::middleware('mobile.auth:customer')->group(function () {
            Route::get('/auth/me', [MobileAuthController::class, 'me']);
            Route::post('/auth/logout', [MobileAuthController::class, 'logout']);
            Route::post('/auth/logout-all', [MobileAuthController::class, 'logoutAll']);

            Route::get('/home', [CustomerApiController::class, 'apiHome']);
            Route::get('/locations', [CustomerApiController::class, 'apiLocations']);
            Route::post('/locations/current', [CustomerApiController::class, 'apiUpdateLocation']);
            Route::get('/orders', [CustomerApiController::class, 'apiOrders']);
            Route::get('/orders/create', [CustomerApiController::class, 'apiCreateOrder']);
            Route::post('/orders', [CustomerApiController::class, 'apiStoreOrder']);
            Route::get('/orders/{id}', [CustomerApiController::class, 'apiShowOrder'])->whereNumber('id');
            Route::get('/documents', [CustomerApiController::class, 'apiDocuments']);
            Route::get('/documents/{id}/invoice', [CustomerApiController::class, 'apiInvoice'])->whereNumber('id');
            Route::get('/products', [CustomerApiController::class, 'apiProducts']);
            Route::get('/categories', [CustomerApiController::class, 'apiCategories']);
            Route::get('/profile', [CustomerApiController::class, 'apiProfile']);
            Route::post('/profile/password', [CustomerApiController::class, 'apiUpdatePassword']);
        });
    });
});
