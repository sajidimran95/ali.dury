<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountReportsController;
use App\Http\Controllers\AccountTypeController;
// use App\Http\Controllers\Auth;
use App\Http\Controllers\BackUpController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\PosPwaController;
use App\Http\Controllers\BusinessLocationController;
use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CombinedPurchaseReturnController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomerGroupController;
use App\Http\Controllers\DashboardConfiguratorController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\DocumentAndNoteController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\GroupTaxController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImportOpeningStockController;
use App\Http\Controllers\ImportProductsController;
use App\Http\Controllers\ImportSalesController;
use App\Http\Controllers\ManageModulesController;
use App\Http\Controllers\InvoiceLayoutController;
use App\Http\Controllers\InvoiceSchemeController;
use App\Http\Controllers\LabelsController;
use App\Http\Controllers\LedgerDiscountController;
use App\Http\Controllers\LocationSettingsController;
use App\Http\Controllers\ManageUserController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotificationTemplateController;
use App\Http\Controllers\OpeningStockController;
use App\Http\Controllers\PrinterController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseRequisitionController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Restaurant;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SalesCommissionAgentController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\FulfillmentController;
use App\Http\Controllers\SellController;
use App\Http\Controllers\ShippingMethodController;
use App\Http\Controllers\SellingPriceGroupController;
use App\Http\Controllers\SellPosController;
use App\Http\Controllers\SellReturnController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\TaxonomyController;
use App\Http\Controllers\TaxRateController;
use App\Http\Controllers\TransactionPaymentController;
use App\Http\Controllers\TypesOfServiceController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VariationTemplateController;
use App\Http\Controllers\WarrantyController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\TeamScheduleController;
use App\Http\Controllers\TimeOffController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['setData'])->group(function () {
    Route::get('/', function (\Illuminate\Http\Request $request) {
        try {
            if (class_exists(\Modules\Ecommerce\Entities\EcommerceSetting::class)
                && \Illuminate\Support\Facades\Schema::hasTable('ecommerce_settings')) {
                $business_id = \Modules\Ecommerce\Entities\EcommerceSetting::where('is_enabled', 1)->value('business_id');
                if (! $business_id) {
                    $business_id = \App\Business::query()->value('id');
                }

                if ($business_id) {
                    $settings = \Modules\Ecommerce\Entities\EcommerceSetting::forBusiness((int) $business_id);
                    if ($settings && $settings->is_enabled) {
                        // Public homepage = ecommerce storefront (POS staff login remains at /login)
                        $request->attributes->set('ecommerce_business_id', (int) $business_id);
                        $request->attributes->set('ecommerce_settings', $settings);
                        view()->share('ecom_settings', $settings);
                        view()->share('ecom_business_id', (int) $business_id);
                        view()->share('cart_totals', [
                            'count' => 0,
                            'subtotal' => 0,
                            'total' => 0,
                            'shipping' => 0,
                            'tax' => 0,
                            'discount' => 0,
                        ]);

                        return app(\Modules\Ecommerce\Http\Controllers\Front\HomeController::class)
                            ->index(app(\Modules\Ecommerce\Services\CartService::class));
                    }
                }
            }
        } catch (\Throwable $e) {
            // Module/DB not ready — fall back to POS welcome
        }

        return view('welcome');
    })->name('home.public');

    Auth::routes();

    Route::get('/business/register', [BusinessController::class, 'getRegister'])->name('business.getRegister');
    Route::post('/business/register', [BusinessController::class, 'postRegister'])->name('business.postRegister');
    Route::post('/business/register/check-username', [BusinessController::class, 'postCheckUsername'])->name('business.postCheckUsername');
    Route::post('/business/register/check-email', [BusinessController::class, 'postCheckEmail'])->name('business.postCheckEmail');

    Route::get('/invoice/{token}', [SellPosController::class, 'showInvoice'])
        ->name('show_invoice');
    Route::get('/quote/{token}', [SellPosController::class, 'showInvoice'])
        ->name('show_quote');

    Route::get('/pay/{token}', [SellPosController::class, 'invoicePayment'])
        ->name('invoice_payment');
    Route::post('/confirm-payment/{id}', [SellPosController::class, 'confirmPayment'])
        ->name('confirm_payment');

    // POS Progressive Web App (public — needed for install / offline)
    Route::get('/pos-pwa/manifest.webmanifest', [PosPwaController::class, 'manifest'])
        ->name('pos.pwa.manifest');
    Route::get('/pos-pwa/sw.js', [PosPwaController::class, 'serviceWorker'])
        ->name('pos.pwa.sw');
    Route::get('/pos-pwa/offline', [PosPwaController::class, 'offline'])
        ->name('pos.pwa.offline');
});

/*
|--------------------------------------------------------------------------
| Sales Representative app (/sale) — mobile PWA portal
| Staff users with sell permissions. Orders = same transactions table for admin.
| Does not change POS or admin routes.
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Sale\SaleAuthController;
use App\Http\Controllers\Sale\SalePortalController;
use App\Http\Controllers\Sale\SalePwaController;

/*
|--------------------------------------------------------------------------
| Customer App (/customer) — contact login (email or mobile)
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Customer\CustomerAuthController;
use App\Http\Controllers\Customer\CustomerPortalController;
use App\Http\Controllers\Customer\CustomerPwaController;

Route::prefix('customer')->name('customer.')->middleware(['setData', 'language'])->group(function () {
    // PWA assets (public — needed for install / offline, no auth)
    Route::get('/pwa/manifest.webmanifest', [CustomerPwaController::class, 'manifest'])->name('pwa.manifest');
    Route::get('/pwa/sw.js', [CustomerPwaController::class, 'serviceWorker'])->name('pwa.sw');
    Route::get('/pwa/offline', [CustomerPwaController::class, 'offline'])->name('pwa.offline');

    Route::get('/', function () {
        return auth('customer')->check()
            ? redirect()->route('customer.home')
            : redirect()->route('customer.login');
    })->name('index');

    Route::middleware('guest:customer')->group(function () {
        Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [CustomerAuthController::class, 'login'])->name('login.post');
    });

    Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout')->middleware('auth:customer');

    Route::middleware(['auth:customer', 'customer.app'])->group(function () {
        Route::get('/home', [CustomerPortalController::class, 'home'])->name('home');
        Route::get('/orders', [CustomerPortalController::class, 'orders'])->name('orders');
        Route::get('/orders/create', [CustomerPortalController::class, 'createOrder'])->name('orders.create');
        Route::post('/orders', [CustomerPortalController::class, 'storeOrder'])->name('orders.store');
        Route::get('/orders/{id}', [CustomerPortalController::class, 'showOrder'])->name('orders.show');
        Route::get('/documents', [CustomerPortalController::class, 'documents'])->name('documents');
        Route::get('/documents/{id}/invoice', [CustomerPortalController::class, 'showInvoice'])->name('documents.invoice');
        Route::get('/price-check', [CustomerPortalController::class, 'priceCheck'])->name('price');
        Route::get('/profile', [CustomerPortalController::class, 'profile'])->name('profile');
        Route::post('/profile/password', [CustomerPortalController::class, 'updatePassword'])->name('profile.password');
        Route::post('/profile/location', [CustomerPortalController::class, 'updateLocation'])->name('profile.location');
        Route::get('/api/products', [CustomerPortalController::class, 'searchProducts'])->name('api.products');
        Route::get('/api/categories', [CustomerPortalController::class, 'categoriesTree'])->name('api.categories');
    });
});

Route::prefix('sale')->name('sale.')->middleware(['setData', \App\Http\Middleware\UseSaleGuard::class])->group(function () {
    // PWA assets (public — no auth / timezone session required)
    Route::get('/pwa/manifest.webmanifest', [SalePwaController::class, 'manifest'])->name('pwa.manifest');
    Route::get('/pwa/sw.js', [SalePwaController::class, 'serviceWorker'])->name('pwa.sw');
    Route::get('/pwa/offline', [SalePwaController::class, 'offline'])->name('pwa.offline');

    // Auth (guest — avoid timezone middleware before business session exists)
    Route::middleware(['guest:sale', 'language'])->group(function () {
        Route::get('/login', [SaleAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [SaleAuthController::class, 'login'])->name('login.post');
    });
    Route::post('/logout', [SaleAuthController::class, 'logout'])->name('logout')->middleware('auth:sale');

    // Portal (authenticated sales users — SetSessionData before timezone)
    Route::middleware(['auth:sale', 'SetSessionData', 'language', 'timezone', 'CheckUserLogin', 'sale.rep'])->group(function () {
        Route::get('/', [SalePortalController::class, 'home'])->name('home');
        Route::get('/orders', [SalePortalController::class, 'orders'])->name('orders');
        Route::get('/saved-orders', [SalePortalController::class, 'savedOrders'])->name('saved_orders');
        Route::delete('/saved-orders/{id}', [SalePortalController::class, 'destroySavedOrder'])->name('saved_orders.destroy');
        Route::get('/orders/create', [SalePortalController::class, 'create'])->name('orders.create');
        Route::post('/orders', [SalePortalController::class, 'store'])->name('orders.store');
        Route::get('/orders/{id}/edit', [SalePortalController::class, 'edit'])->name('orders.edit');
        Route::put('/orders/{id}', [SalePortalController::class, 'update'])->name('orders.update');
        Route::get('/orders/{id}', [SalePortalController::class, 'show'])->name('orders.show');
        Route::get('/orders/{id}/invoice', [SalePortalController::class, 'downloadInvoice'])->name('orders.invoice');
        Route::delete('/orders/{id}', [SalePortalController::class, 'destroy'])->name('orders.destroy');
        Route::get('/account', [SalePortalController::class, 'account'])->name('account');
        Route::post('/account/location', [SalePortalController::class, 'updateLocation'])->name('account.location');
        Route::get('/expenses', [SalePortalController::class, 'expenses'])->name('expenses');
        Route::get('/expenses/create', [SalePortalController::class, 'createExpense'])->name('expenses.create');
        Route::post('/expenses', [SalePortalController::class, 'storeExpense'])->name('expenses.store');
        Route::get('/delivery', [SalePortalController::class, 'delivery'])->name('delivery');
        Route::get('/timesheet', [SalePortalController::class, 'timesheet'])->name('timesheet');
        Route::get('/timesheet/status', [AttendanceController::class, 'status'])->name('timesheet.status');
        Route::post('/timesheet/clock-in', [AttendanceController::class, 'clockIn'])->name('timesheet.clock_in');
        Route::post('/timesheet/start-break', [AttendanceController::class, 'startBreak'])->name('timesheet.start_break');
        Route::post('/timesheet/end-break', [AttendanceController::class, 'endBreak'])->name('timesheet.end_break');
        Route::post('/timesheet/clock-out', [AttendanceController::class, 'clockOut'])->name('timesheet.clock_out');
        Route::get('/products', [SalePortalController::class, 'products'])->name('products');
        Route::get('/customers', [SalePortalController::class, 'customers'])->name('customers');
        Route::get('/customers/create', [SalePortalController::class, 'createCustomer'])->name('customers.create');
        Route::post('/customers', [SalePortalController::class, 'storeCustomer'])->name('customers.store');
        Route::get('/customers/{id}/invoices/{invoiceId}/print', [SalePortalController::class, 'printCustomerInvoice'])->name('customers.invoice.print');
        Route::get('/customers/{id}/invoices', [SalePortalController::class, 'customerOpenInvoices'])->name('customers.invoices');
        Route::get('/customers/{id}/history', [SalePortalController::class, 'customerHistory'])->name('customers.history');
        Route::get('/customers/{id}/payment', [SalePortalController::class, 'customerPayment'])->name('customers.payment');
        Route::post('/customers/{id}/payment', [SalePortalController::class, 'storeCustomerPayment'])->name('customers.payment.store');
        Route::post('/customers/{id}/zero-sale', [SalePortalController::class, 'storeCustomerZeroSale'])->name('customers.zero_sale');
        Route::get('/customers/{id}', [SalePortalController::class, 'showCustomer'])->name('customers.show');
        Route::get('/api/customers', [SalePortalController::class, 'searchCustomers'])->name('api.customers');
        Route::get('/api/products', [SalePortalController::class, 'searchProducts'])->name('api.products');
        Route::get('/api/categories', [SalePortalController::class, 'categoriesTree'])->name('api.categories');
        Route::get('/api/last-purchases', [SalePortalController::class, 'lastPurchases'])->name('api.last_purchases');
        Route::get('/api/product-history', [SalePortalController::class, 'productOrderHistory'])->name('api.product_history');
        Route::get('/api/sync', [SalePortalController::class, 'syncApp'])->name('api.sync');
        Route::post('/api/order-draft', [SalePortalController::class, 'saveOrderDraft'])->name('api.order_draft');
        Route::delete('/api/order-draft', [SalePortalController::class, 'clearOrderDraft'])->name('api.order_draft.clear');
        
        // Stock Transfer routes
        Route::get('/transfers', [SalePortalController::class, 'stockTransfers'])->name('transfers');
        Route::get('/transfers/create', [SalePortalController::class, 'createStockTransfer'])->name('transfers.create');
        Route::post('/transfers', [SalePortalController::class, 'storeStockTransfer'])->name('transfers.store');
        Route::get('/transfers/{id}', [SalePortalController::class, 'showStockTransfer'])->name('transfers.show');
    });
});

// Alias: /sales → /sale (common typo)
Route::any('/sales/{any?}', function ($any = null) {
    $target = '/sale'.($any ? '/'.$any : '');
    $qs = request()->getQueryString();
    if ($qs) {
        $target .= '?'.$qs;
    }

    return redirect($target, 301);
})->where('any', '.*');

/*
|--------------------------------------------------------------------------
| Delivery driver PWA (/delivery)
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\DeliveryApp\DeliveryAppController;
use App\Http\Controllers\DeliveryApp\DeliveryAuthController;
use App\Http\Controllers\DeliveryApp\DeliveryPwaController;
use App\Http\Controllers\DeliveryManagementController;

Route::prefix('delivery')->name('delivery.app.')->middleware(['setData', \App\Http\Middleware\UseDeliveryGuard::class])->group(function () {
    Route::get('/pwa/manifest.webmanifest', [DeliveryPwaController::class, 'manifest'])->name('pwa.manifest');
    Route::get('/pwa/sw.js', [DeliveryPwaController::class, 'serviceWorker'])->name('pwa.sw');
    Route::get('/pwa/offline', [DeliveryPwaController::class, 'offline'])->name('pwa.offline');

    Route::middleware(['guest:delivery', 'language'])->group(function () {
        Route::get('/login', [DeliveryAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [DeliveryAuthController::class, 'login'])->name('login.post');
    });
    Route::post('/logout', [DeliveryAuthController::class, 'logout'])->name('logout')->middleware('auth:delivery');

    Route::middleware(['auth:delivery', 'SetSessionData', 'language', 'timezone', 'CheckUserLogin', 'delivery.app'])->group(function () {
        Route::get('/', [DeliveryAppController::class, 'home'])->name('home');
        Route::get('/route', [DeliveryAppController::class, 'route'])->name('route');
        Route::get('/assigned', [DeliveryAppController::class, 'assigned'])->name('assigned');
        Route::get('/all', [DeliveryAppController::class, 'all'])->name('all');
        Route::get('/delivered', [DeliveryAppController::class, 'history'])->name('history');
        Route::post('/start', [DeliveryAppController::class, 'start'])->name('start');
        Route::post('/stops/{stop}/arrived', [DeliveryAppController::class, 'arrived'])->name('arrived')->whereNumber('stop');
        Route::post('/stops/{stop}/delivered', [DeliveryAppController::class, 'delivered'])->name('delivered')->whereNumber('stop');
        Route::post('/stops/{stop}/failed', [DeliveryAppController::class, 'failed'])->name('failed')->whereNumber('stop');
        Route::post('/stops/{stop}/notes', [DeliveryAppController::class, 'notes'])->name('notes')->whereNumber('stop');
    });
});

//Routes for authenticated users only
Route::middleware(['setData', 'auth', 'SetSessionData', 'language', 'timezone', 'AdminSidebarMenu', 'CheckUserLogin'])->group(function () {
    Route::get('pos/payment/{id}', [SellPosController::class, 'edit'])->name('edit-pos-payment');
    Route::get('service-staff-availability', [SellPosController::class, 'showServiceStaffAvailibility']);
    Route::get('pause-resume-service-staff-timer/{user_id}', [SellPosController::class, 'pauseResumeServiceStaffTimer']);
    Route::get('mark-as-available/{user_id}', [SellPosController::class, 'markAsAvailable']);

    Route::resource('purchase-requisition', PurchaseRequisitionController::class)->except(['edit', 'update']);
    Route::post('/get-requisition-products', [PurchaseRequisitionController::class, 'getRequisitionProducts'])->name('get-requisition-products');
    Route::get('get-purchase-requisitions/{location_id}', [PurchaseRequisitionController::class, 'getPurchaseRequisitions']);
    Route::get('get-purchase-requisition-lines/{purchase_requisition_id}', [PurchaseRequisitionController::class, 'getPurchaseRequisitionLines']);

    Route::get('/sign-in-as-user/{id}', [ManageUserController::class, 'signInAsUser'])->name('sign-in-as-user');

    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/sales-dashboard', [HomeController::class, 'salesDashboard'])->name('sales.dashboard');
    Route::get('/home/get-totals', [HomeController::class, 'getTotals']);
    Route::get('/home/ops-overview', [HomeController::class, 'getHomeOpsOverview']);
    Route::get('/home/product-stock-alert', [HomeController::class, 'getProductStockAlert']);
    Route::get('/home/purchase-payment-dues', [HomeController::class, 'getPurchasePaymentDues']);
    Route::get('/home/sales-payment-dues', [HomeController::class, 'getSalesPaymentDues']);
    Route::get('/home/sales-order-summary', [HomeController::class, 'getSalesOrderSummary']);
    Route::get('/home/dashboard-side-panel', [HomeController::class, 'getDashboardSidePanel']);
    Route::get('/home/payment-method-mix', [HomeController::class, 'getPaymentMethodMix']);
    Route::get('/home/revenue-insights', [HomeController::class, 'getRevenueInsights']);
    Route::get('/home/orders-by-status-weekly', [HomeController::class, 'getOrdersByStatusWeekly']);
    Route::get('/home/recent-orders', [HomeController::class, 'getRecentOrders']);
    Route::post('/attach-medias-to-model', [HomeController::class, 'attachMediasToGivenModel'])->name('attach.medias.to.model');
    Route::get('/calendar', [HomeController::class, 'getCalendar'])->name('calendar');

    // Inventory dashboard
    Route::get('/inventory', [\App\Http\Controllers\InventoryDashboardController::class, 'index'])->name('inventory.dashboard');
    Route::get('/inventory/dashboard', [\App\Http\Controllers\InventoryDashboardController::class, 'index']);

    // Employee attendance (clock in / out / break / report / manage)
    Route::get('/attendance/status', [AttendanceController::class, 'status'])->name('attendance.status');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock_in');
    Route::post('/attendance/start-break', [AttendanceController::class, 'startBreak'])->name('attendance.start_break');
    Route::post('/attendance/end-break', [AttendanceController::class, 'endBreak'])->name('attendance.end_break');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock_out');
    Route::get('/attendance/report', [AttendanceController::class, 'report']);
    Route::get('/attendance/calendar-events', [AttendanceController::class, 'calendarEvents']);
    Route::get('/attendance/{id}', [AttendanceController::class, 'show'])->where('id', '[0-9]+');
    Route::post('/attendance', [AttendanceController::class, 'store']);
    Route::put('/attendance/{id}', [AttendanceController::class, 'update'])->where('id', '[0-9]+');
    Route::delete('/attendance/{id}', [AttendanceController::class, 'destroy'])->where('id', '[0-9]+');

    // Team Schedule hub
    Route::get('/team-schedule/calendar', [TeamScheduleController::class, 'calendar']);
    Route::get('/team-schedule/calendar-events', [TeamScheduleController::class, 'calendarEvents']);
    Route::get('/team-schedule/shifts', [TeamScheduleController::class, 'shifts']);
    Route::post('/team-schedule/shifts', [TeamScheduleController::class, 'storeShift']);
    Route::get('/team-schedule/shifts/{id}', [TeamScheduleController::class, 'showShift'])->where('id', '[0-9]+');
    Route::put('/team-schedule/shifts/{id}', [TeamScheduleController::class, 'updateShift'])->where('id', '[0-9]+');
    Route::delete('/team-schedule/shifts/{id}', [TeamScheduleController::class, 'destroyShift'])->where('id', '[0-9]+');
    Route::get('/team-schedule/colors', [TeamScheduleController::class, 'colors']);
    Route::post('/team-schedule/colors', [TeamScheduleController::class, 'saveColors']);
    Route::get('/team-schedule/time-off', [TimeOffController::class, 'index']);
    Route::post('/team-schedule/time-off', [TimeOffController::class, 'store']);
    Route::post('/team-schedule/time-off/{id}/review', [TimeOffController::class, 'review'])->where('id', '[0-9]+');
    Route::post('/team-schedule/time-off/{id}/cancel', [TimeOffController::class, 'cancel'])->where('id', '[0-9]+');

    Route::post('/test-email', [BusinessController::class, 'testEmailConfiguration']);
    Route::post('/test-sms', [BusinessController::class, 'testSmsConfiguration']);
    Route::get('/business/settings', [BusinessController::class, 'getBusinessSettings'])->name('business.getBusinessSettings');
    Route::post('/business/update', [BusinessController::class, 'postBusinessSettings'])->name('business.postBusinessSettings');
    Route::post('/business/clear-cache', [BusinessController::class, 'clearApplicationCache'])->name('business.clearCache');
    Route::post('/business/reset-database', [BusinessController::class, 'resetDatabaseToFreshPos'])->name('business.resetDatabase');
    Route::get('/user/profile', [UserController::class, 'getProfile'])->name('user.getProfile');
    Route::post('/user/update', [UserController::class, 'updateProfile'])->name('user.updateProfile');
    Route::post('/user/update-password', [UserController::class, 'updatePassword'])->name('user.updatePassword');

    Route::resource('brands', BrandController::class);
    Route::resource('shipping-methods', ShippingMethodController::class)->except(['show']);

    // PaymentAccountController removed (legacy); payment account report remains under account routes.

    Route::resource('tax-rates', TaxRateController::class);

    Route::resource('units', UnitController::class);

    Route::resource('ledger-discount', LedgerDiscountController::class)->only('edit', 'destroy', 'store', 'update');

    Route::post('check-mobile', [ContactController::class, 'checkMobile']);
    Route::get('/get-contact-due/{contact_id}', [ContactController::class, 'getContactDue']);
    Route::get('/contacts/purchase-history/{contact_id}', [ContactController::class, 'getPurchaseHistory']);
    Route::get('/contacts/payments/{contact_id}', [ContactController::class, 'getContactPayments']);
    Route::get('/contacts/map', [ContactController::class, 'contactMap']);
    Route::get('/contacts/update-status/{id}', [ContactController::class, 'updateStatus']);
    Route::get('/contacts/stock-report/{supplier_id}', [ContactController::class, 'getSupplierStockReport']);
    Route::get('/contacts/ledger', [ContactController::class, 'getLedger']);
    Route::post('/contacts/send-ledger', [ContactController::class, 'sendLedger']);
    Route::get('/contacts/import', [ContactController::class, 'getImportContacts'])->name('contacts.import');
    Route::post('/contacts/import', [ContactController::class, 'postImportContacts']);
    Route::post('/contacts/check-contacts-id', [ContactController::class, 'checkContactId']);

    Route::post('/contacts/check-tax-number', [ContactController::class, 'checkTaxNumber']);

    Route::get('/contacts/customers', [ContactController::class, 'getCustomers']);
    Route::resource('contacts', ContactController::class);

    Route::get('taxonomies-ajax-index-page', [TaxonomyController::class, 'getTaxonomyIndexPage']);
    Route::resource('taxonomies', TaxonomyController::class);

    Route::resource('variation-templates', VariationTemplateController::class);

    Route::get('/products/download-excel', [ProductController::class, 'downloadExcel']);

    Route::get('/products/stock-history/{id}', [ProductController::class, 'productStockHistory']);
    Route::get('/products/inventory-timeline/{id}', [ProductController::class, 'inventoryTimeline']);
    Route::get('/delete-media/{media_id}', [ProductController::class, 'deleteMedia']);
    Route::post('/products/mass-deactivate', [ProductController::class, 'massDeactivate']);
    Route::get('/products/activate/{id}', [ProductController::class, 'activate']);
    Route::get('/products/view-product-group-price/{id}', [ProductController::class, 'viewGroupPrice']);
    Route::get('/products/add-selling-prices/{id}', [ProductController::class, 'addSellingPrices']);
    Route::post('/products/save-selling-prices', [ProductController::class, 'saveSellingPrices']);
    Route::post('/products/mass-delete', [ProductController::class, 'massDestroy']);
    Route::get('/products/view/{id}', [ProductController::class, 'view']);
    Route::get('/products/list', [ProductController::class, 'getProducts']);
    Route::get('/products/list-no-variation', [ProductController::class, 'getProductsWithoutVariations']);
    Route::post('/products/bulk-edit', [ProductController::class, 'bulkEdit']);
    Route::post('/products/bulk-update', [ProductController::class, 'bulkUpdate']);
    Route::post('/products/bulk-update-location', [ProductController::class, 'updateProductLocation']);
    Route::get('/products/get-product-to-edit/{product_id}', [ProductController::class, 'getProductToEdit']);

    Route::post('/products/get_sub_categories', [ProductController::class, 'getSubCategories']);
    Route::get('/products/get_sub_units', [ProductController::class, 'getSubUnits']);
    Route::post('/products/product_form_part', [ProductController::class, 'getProductVariationFormPart']);
    Route::post('/products/get_product_variation_row', [ProductController::class, 'getProductVariationRow']);
    Route::post('/products/get_variation_template', [ProductController::class, 'getVariationTemplate']);
    Route::get('/products/get_variation_value_row', [ProductController::class, 'getVariationValueRow']);
    Route::post('/products/check_product_sku', [ProductController::class, 'checkProductSku']);
    Route::post('/products/check_product_name', [ProductController::class, 'checkProductName']);
    Route::post('/products/validate_variation_skus', [ProductController::class, 'validateVaritionSkus']); //validates multiple skus at once
    Route::get('/products/quick_add', [ProductController::class, 'quickAdd']);
    Route::post('/products/save_quick_product', [ProductController::class, 'saveQuickProduct']);
    Route::get('/products/get-combo-product-entry-row', [ProductController::class, 'getComboProductEntryRow']);
    Route::post('/products/toggle-woocommerce-sync', [ProductController::class, 'toggleWooCommerceSync']);

    Route::resource('products', ProductController::class);
    Route::get('/toggle-subscription/{id}', [SellPosController::class, 'toggleRecurringInvoices']);
    Route::post('/sells/pos/get-types-of-service-details', [SellPosController::class, 'getTypesOfServiceDetails']);
    Route::get('/sells/subscriptions', [SellPosController::class, 'listSubscriptions']);
    Route::get('/sells/duplicate/{id}', [SellController::class, 'duplicateSell']);
    Route::get('/sells/drafts', [SellController::class, 'getDrafts']);
    Route::get('/sells/convert-to-draft/{id}', [SellPosController::class, 'convertToInvoice']);
    Route::get('/sells/convert-to-proforma/{id}', [SellPosController::class, 'convertToProforma']);
    Route::get('/sells/quotations', [SellController::class, 'getQuotations']);
    Route::get('/sells/draft-dt', [SellController::class, 'getDraftDatables']);
    Route::resource('sells', SellController::class)->except(['show']);
    Route::get('/sells/copy-quotation/{id}', [SellPosController::class, 'copyQuotation']);

    Route::post('/import-purchase-products', [PurchaseController::class, 'importPurchaseProducts']);
    Route::post('/purchases/update-status', [PurchaseController::class, 'updateStatus']);
    Route::get('/purchases/get_products', [PurchaseController::class, 'getProducts']);
    Route::get('/purchases/get_suppliers', [PurchaseController::class, 'getSuppliers']);
    Route::post('/purchases/get_purchase_entry_row', [PurchaseController::class, 'getPurchaseEntryRow']);
    Route::post('/purchases/check_ref_number', [PurchaseController::class, 'checkRefNumber']);
    Route::resource('purchases', PurchaseController::class)->except(['show']);

    Route::get('/toggle-subscription/{id}', [SellPosController::class, 'toggleRecurringInvoices']);
    Route::post('/sells/pos/get-types-of-service-details', [SellPosController::class, 'getTypesOfServiceDetails']);
    Route::get('/sells/subscriptions', [SellPosController::class, 'listSubscriptions']);
    Route::get('/sells/duplicate/{id}', [SellController::class, 'duplicateSell']);
    Route::get('/sells/drafts', [SellController::class, 'getDrafts']);
    Route::get('/sells/convert-to-draft/{id}', [SellPosController::class, 'convertToInvoice']);
    Route::get('/sells/convert-to-proforma/{id}', [SellPosController::class, 'convertToProforma']);
    Route::get('/sells/quotations', [SellController::class, 'getQuotations']);
    Route::get('/sells/draft-dt', [SellController::class, 'getDraftDatables']);
    Route::resource('sells', SellController::class)->except(['show']);

    Route::get('/import-sales', [ImportSalesController::class, 'index']);
    Route::post('/import-sales/preview', [ImportSalesController::class, 'preview']);
    Route::post('/import-sales', [ImportSalesController::class, 'import']);
    Route::get('/revert-sale-import/{batch}', [ImportSalesController::class, 'revertSaleImport']);

    Route::post('/sells/pos/print-per-sale-settings', [SellPosController::class, 'savePrintPerSaleSettings']);
    Route::post('/sells/pos/screen-settings', [SellPosController::class, 'savePosScreenSettings']);
    Route::get('/pos/quick-keys', [\App\Http\Controllers\PosQuickKeyController::class, 'index'])->name('pos.quick_keys.index');
    Route::post('/pos/quick-keys', [\App\Http\Controllers\PosQuickKeyController::class, 'store'])->name('pos.quick_keys.store');
    Route::post('/pos/quick-keys/toggle', [\App\Http\Controllers\PosQuickKeyController::class, 'toggleEnabled'])->name('pos.quick_keys.toggle');
    Route::get('/pos/quick-keys/categories', [\App\Http\Controllers\PosQuickKeyController::class, 'searchCategories'])->name('pos.quick_keys.categories');
    Route::get('/pos/quick-keys/{id}/edit', [\App\Http\Controllers\PosQuickKeyController::class, 'edit'])->name('pos.quick_keys.edit');
    Route::put('/pos/quick-keys/{id}', [\App\Http\Controllers\PosQuickKeyController::class, 'update'])->name('pos.quick_keys.update');
    Route::delete('/pos/quick-keys/{id}', [\App\Http\Controllers\PosQuickKeyController::class, 'destroy'])->name('pos.quick_keys.destroy');
    Route::post('/pos/quick-keys/{id}/duplicate', [\App\Http\Controllers\PosQuickKeyController::class, 'duplicate'])->name('pos.quick_keys.duplicate');
    Route::post('/pos/quick-keys/{id}/current', [\App\Http\Controllers\PosQuickKeyController::class, 'setCurrent'])->name('pos.quick_keys.current');
    Route::get('/sells/pos/get_product_row/{variation_id}/{location_id}', [SellPosController::class, 'getProductRow']);
    Route::post('/sells/pos/get-product-line-discounts', [SellPosController::class, 'getProductLineDiscounts']);
    Route::post('/sells/pos/get_payment_row', [SellPosController::class, 'getPaymentRow']);
    Route::post('/sells/pos/get-reward-details', [SellPosController::class, 'getRewardDetails']);
    Route::get('/sells/pos/get-recent-transactions', [SellPosController::class, 'getRecentTransactions']);
    Route::get('/sells/pos/get-product-suggestion', [SellPosController::class, 'getProductSuggestion']);
    Route::get('/sells/pos/get-live-stock', [SellPosController::class, 'getLiveStock']);
    Route::get('/sells/pos/get-featured-products/{location_id}', [SellPosController::class, 'getFeaturedProducts']);
    Route::get('/reset-mapping', [SellController::class, 'resetMapping']);
    // pos display screen route
    Route::get('/customer-display', [SellPosController::class, 'posDisplay'])->name('pos_display');

    Route::get('/pos/variations/bulk', [\App\Http\Controllers\ProductController::class, 'getVariationDetailsBulk']);
    // end pos display screen route
    Route::resource('pos', SellPosController::class);

    Route::resource('roles', RoleController::class);

    Route::get('/users/barcode-image', [ManageUserController::class, 'barcodeImage']);
    Route::post('/users/{user}/toggle-enabled', [ManageUserController::class, 'toggleEnabled']);
    Route::post('/users/{user}/sales-targets', [ManageUserController::class, 'updateSalesTargets']);
    Route::resource('users', ManageUserController::class);

    Route::resource('group-taxes', GroupTaxController::class);

    Route::get('/barcodes/set_default/{id}', [BarcodeController::class, 'setDefault']);
    Route::resource('barcodes', BarcodeController::class);

    //Invoice schemes..
    Route::get('/invoice-schemes/set_default/{id}', [InvoiceSchemeController::class, 'setDefault']);
    Route::resource('invoice-schemes', InvoiceSchemeController::class);

    //Print Labels
    Route::get('/labels/show', [LabelsController::class, 'show']);
    Route::get('/labels/add-product-row', [LabelsController::class, 'addProductRow']);
    Route::get('/labels/preview', [LabelsController::class, 'preview']);

    //Reports...
    Route::get('/reports/gst-purchase-report', [ReportController::class, 'gstPurchaseReport']);
    Route::get('/reports/gst-sales-report', [ReportController::class, 'gstSalesReport']);
    Route::get('/reports/get-stock-by-sell-price', [ReportController::class, 'getStockBySellingPrice']);
    Route::get('/reports/purchase-report', [ReportController::class, 'purchaseReport']);
    Route::get('/reports/sale-report', [ReportController::class, 'saleReport']);
    Route::get('/reports/service-staff-report', [ReportController::class, 'getServiceStaffReport']);
    Route::get('/reports/service-staff-line-orders', [ReportController::class, 'serviceStaffLineOrders']);
    Route::get('/reports/table-report', [ReportController::class, 'getTableReport']);
    Route::get('/reports/profit-loss', [ReportController::class, 'getProfitLoss']);
    Route::get('/reports/get-opening-stock', [ReportController::class, 'getOpeningStock']);
    Route::get('/reports/purchase-sell', [ReportController::class, 'getPurchaseSell']);
    Route::get('/reports/customer-supplier', [ReportController::class, 'getCustomerSuppliers']);
    Route::get('/reports/stock-report', [ReportController::class, 'getStockReport']);
    Route::get('/reports/stock-details', [ReportController::class, 'getStockDetails']);
        Route::get('/reports/tax-report', [ReportController::class, 'getTaxReport']);
        Route::get('/reports/msa-report', [ReportController::class, 'getMsaReport']);
        Route::post('/reports/msa-report/export', [ReportController::class, 'exportMsaReport']);
        Route::get('/reports/tax-details', [ReportController::class, 'getTaxDetails']);
    Route::get('/reports/trending-products', [ReportController::class, 'getTrendingProducts']);
    Route::get('/reports/expense-report', [ReportController::class, 'getExpenseReport']);
    Route::get('/reports/stock-adjustment-report', [ReportController::class, 'getStockAdjustmentReport']);
    Route::get('/reports/register-report', [ReportController::class, 'getRegisterReport']);
    Route::get('/reports/sales-representative-report', [ReportController::class, 'getSalesRepresentativeReport']);
    Route::get('/reports/sales-representative-total-expense', [ReportController::class, 'getSalesRepresentativeTotalExpense']);
    Route::get('/reports/sales-representative-total-sell', [ReportController::class, 'getSalesRepresentativeTotalSell']);
    Route::get('/reports/sales-representative-total-commission', [ReportController::class, 'getSalesRepresentativeTotalCommission']);
    Route::get('/reports/stock-expiry', [ReportController::class, 'getStockExpiryReport']);
    Route::get('/reports/stock-expiry-edit-modal/{purchase_line_id}', [ReportController::class, 'getStockExpiryReportEditModal']);
    Route::post('/reports/stock-expiry-update', [ReportController::class, 'updateStockExpiryReport'])->name('updateStockExpiryReport');
    Route::get('/reports/customer-group', [ReportController::class, 'getCustomerGroup']);
    Route::get('/reports/product-purchase-report', [ReportController::class, 'getproductPurchaseReport']);
    Route::get('/reports/product-sell-grouped-by', [ReportController::class, 'productSellReportBy']);
    Route::get('/reports/product-sell-report', [ReportController::class, 'getproductSellReport']);
    Route::get('/reports/product-sell-report-with-purchase', [ReportController::class, 'getproductSellReportWithPurchase']);
    Route::get('/reports/product-sell-grouped-report', [ReportController::class, 'getproductSellGroupedReport']);
    Route::get('/reports/lot-report', [ReportController::class, 'getLotReport']);
    Route::get('/reports/purchase-payment-report', [ReportController::class, 'purchasePaymentReport']);
    Route::get('/reports/sell-payment-report', [ReportController::class, 'sellPaymentReport']);
    Route::get('/reports/product-stock-details', [ReportController::class, 'productStockDetails']);
    Route::get('/reports/adjust-product-stock', [ReportController::class, 'adjustProductStock']);
    Route::get('/reports/get-profit/{by?}', [ReportController::class, 'getProfit']);
    Route::get('/reports/items-report', [ReportController::class, 'itemsReport']);
    Route::get('/reports/get-stock-value', [ReportController::class, 'getStockValue']);

    Route::get('business-location/activate-deactivate/{location_id}', [BusinessLocationController::class, 'activateDeactivateLocation']);

    //Business Location Settings...
    Route::prefix('business-location/{location_id}')->name('location.')->group(function () {
        Route::get('settings', [LocationSettingsController::class, 'index'])->name('settings');
        Route::post('settings', [LocationSettingsController::class, 'updateSettings'])->name('settings_update');
    });

    //Business Locations...
    Route::post('business-location/check-location-id', [BusinessLocationController::class, 'checkLocationId']);
    Route::resource('business-location', BusinessLocationController::class);

    //Invoice layouts..
    Route::resource('invoice-layouts', InvoiceLayoutController::class);

    Route::post('get-expense-sub-categories', [ExpenseCategoryController::class, 'getSubCategories']);

    //Expense Categories...
    Route::resource('expense-categories', ExpenseCategoryController::class);

    //Expenses...
    Route::get('expenses/print/{id}', [ExpenseController::class, 'printInvoice']);
    Route::resource('expenses', ExpenseController::class);
    Route::get('import-expense', [ExpenseController::class, 'importExpense']);
    Route::post('store-import-expense', [ExpenseController::class, 'storeExpenseImport']);

    //Transaction payments...
    // Route::get('/payments/opening-balance/{contact_id}', 'TransactionPaymentController@getOpeningBalancePayments');
    Route::get('/payments/show-child-payments/{payment_id}', [TransactionPaymentController::class, 'showChildPayments']);
    Route::get('/payments/view-payment/{payment_id}', [TransactionPaymentController::class, 'viewPayment']);
    Route::get('/payments/add_payment/{transaction_id}', [TransactionPaymentController::class, 'addPayment']);
    Route::get('/payments/pay-contact-due/{contact_id}', [TransactionPaymentController::class, 'getPayContactDue']);
    Route::post('/payments/pay-contact-due', [TransactionPaymentController::class, 'postPayContactDue']);
    Route::resource('payments', TransactionPaymentController::class);

    //Printers...
    Route::resource('printers', PrinterController::class);

    Route::get('/stock-adjustments/remove-expired-stock/{purchase_line_id}', [StockAdjustmentController::class, 'removeExpiredStock']);
    Route::post('/stock-adjustments/get_product_row', [StockAdjustmentController::class, 'getProductRow']);
    Route::resource('stock-adjustments', StockAdjustmentController::class);

    Route::get('/cash-register/register-details', [CashRegisterController::class, 'getRegisterDetails']);
    Route::get('/cash-register/close-register/{id?}', [CashRegisterController::class, 'getCloseRegister']);
    Route::post('/cash-register/close-register', [CashRegisterController::class, 'postCloseRegister']);
    Route::resource('cash-register', CashRegisterController::class);

    //Import products
    Route::get('/import-products', [ImportProductsController::class, 'index']);
    Route::post('/import-products/store', [ImportProductsController::class, 'store']);

    //Sales Commission Agent
    Route::resource('sales-commission-agents', SalesCommissionAgentController::class);

    //Stock Transfer
    Route::get('stock-transfers/print/{id}', [StockTransferController::class, 'printInvoice']);
    Route::post('stock-transfers/update-status/{id}', [StockTransferController::class, 'updateStatus']);
    Route::resource('stock-transfers', StockTransferController::class);

    Route::get('/opening-stock/add/{product_id}', [OpeningStockController::class, 'add']);
    Route::post('/opening-stock/save', [OpeningStockController::class, 'save']);

    //Customer Groups
    Route::get('customer-group/{id}/members', [CustomerGroupController::class, 'members']);
    Route::post('customer-group/{id}/members', [CustomerGroupController::class, 'addMembers']);
    Route::delete('customer-group/{id}/members/{contactId}', [CustomerGroupController::class, 'removeMember']);
    Route::get('customer-group/{id}/products', [CustomerGroupController::class, 'products']);
    Route::post('customer-group/{id}/products', [CustomerGroupController::class, 'saveProducts']);
    Route::post('customer-group/{id}/discount', [CustomerGroupController::class, 'saveDiscount']);
    Route::get('customer-group/{id}/products/search', [CustomerGroupController::class, 'searchProducts']);
    Route::post('customer-group/{id}/products/override', [CustomerGroupController::class, 'addProductOverride']);
    Route::post('customer-group/{id}/products/apply-all', [CustomerGroupController::class, 'applyAllProducts']);
    Route::resource('customer-group', CustomerGroupController::class);

    //Import opening stock
    Route::get('/import-opening-stock', [ImportOpeningStockController::class, 'index']);
    Route::post('/import-opening-stock/store', [ImportOpeningStockController::class, 'store']);

    //Sell return
    Route::get('validate-invoice-to-return/{invoice_no}', [SellReturnController::class, 'validateInvoiceToReturn']);
    // service staff replacement
    Route::get('validate-invoice-to-service-staff-replacement/{invoice_no}', [SellPosController::class, 'validateInvoiceToServiceStaffReplacement']);
    Route::put('change-service-staff/{id}', [SellPosController::class, 'change_service_staff'])->name('change_service_staff');

    Route::resource('sell-return', SellReturnController::class);
    Route::get('sell-return/get-product-row', [SellReturnController::class, 'getProductRow']);
    Route::get('/sell-return/print/{id}', [SellReturnController::class, 'printInvoice']);
    Route::get('/sell-return/add/{id}', [SellReturnController::class, 'add']);

    //Backup
    Route::get('backup/download/{file_name}', [BackUpController::class, 'download']);
    Route::get('backup/{id}/delete', [BackUpController::class, 'delete'])->name('delete_backup');
    Route::resource('backup', BackUpController::class)->only('index', 'create', 'store');

    Route::get('selling-price-group/activate-deactivate/{id}', [SellingPriceGroupController::class, 'activateDeactivate']);
    Route::get('update-product-price', [SellingPriceGroupController::class, 'updateProductPrice'])->name('update-product-price');
    Route::get('export-product-price', [SellingPriceGroupController::class, 'export']);
    Route::post('import-product-price', [SellingPriceGroupController::class, 'import']);

    Route::resource('selling-price-group', SellingPriceGroupController::class);

    Route::resource('notification-templates', NotificationTemplateController::class)->only(['index', 'store']);
    Route::get('notification/get-template/{transaction_id}/{template_for}', [NotificationController::class, 'getTemplate']);
    Route::post('notification/send', [NotificationController::class, 'send']);

    Route::post('/purchase-return/update', [CombinedPurchaseReturnController::class, 'update']);
    Route::get('/purchase-return/edit/{id}', [CombinedPurchaseReturnController::class, 'edit']);
    Route::post('/purchase-return/save', [CombinedPurchaseReturnController::class, 'save']);
    Route::post('/purchase-return/get_product_row', [CombinedPurchaseReturnController::class, 'getProductRow']);
    Route::get('/purchase-return/create', [CombinedPurchaseReturnController::class, 'create']);
    Route::get('/purchase-return/add/{id}', [PurchaseReturnController::class, 'add']);
    Route::resource('/purchase-return', PurchaseReturnController::class)->except('create');

    Route::get('/discount/activate/{id}', [DiscountController::class, 'activate']);
    Route::post('/discount/mass-deactivate', [DiscountController::class, 'massDeactivate']);
    Route::resource('discount', DiscountController::class);

    Route::prefix('account')->group(function () {
        Route::resource('/account', AccountController::class);
        Route::get('/fund-transfer/{id}', [AccountController::class, 'getFundTransfer']);
        Route::post('/fund-transfer', [AccountController::class, 'postFundTransfer']);
        Route::get('/deposit/{id}', [AccountController::class, 'getDeposit']);
        Route::post('/deposit', [AccountController::class, 'postDeposit']);
        Route::get('/close/{id}', [AccountController::class, 'close']);
        Route::get('/activate/{id}', [AccountController::class, 'activate']);
        Route::get('/delete-account-transaction/{id}', [AccountController::class, 'destroyAccountTransaction']);
        Route::get('/edit-account-transaction/{id}', [AccountController::class, 'editAccountTransaction']);
        Route::post('/update-account-transaction/{id}', [AccountController::class, 'updateAccountTransaction']);
        Route::get('/get-account-balance/{id}', [AccountController::class, 'getAccountBalance']);
        Route::get('/balance-sheet', [AccountReportsController::class, 'balanceSheet']);
        Route::get('/trial-balance', [AccountReportsController::class, 'trialBalance']);
        Route::get('/payment-account-report', [AccountReportsController::class, 'paymentAccountReport']);
        Route::get('/link-account/{id}', [AccountReportsController::class, 'getLinkAccount']);
        Route::post('/link-account', [AccountReportsController::class, 'postLinkAccount']);
        Route::get('/cash-flow', [AccountController::class, 'cashFlow']);
    });

    Route::resource('account-types', AccountTypeController::class);

    //Restaurant module
    Route::prefix('modules')->group(function () {
        Route::resource('tables', Restaurant\TableController::class);
        Route::resource('modifiers', Restaurant\ModifierSetsController::class);

        //Map modifier to products
        Route::get('/product-modifiers/{id}/edit', [Restaurant\ProductModifierSetController::class, 'edit']);
        Route::post('/product-modifiers/{id}/update', [Restaurant\ProductModifierSetController::class, 'update']);
        Route::get('/product-modifiers/product-row/{product_id}', [Restaurant\ProductModifierSetController::class, 'product_row']);

        Route::get('/add-selected-modifiers', [Restaurant\ProductModifierSetController::class, 'add_selected_modifiers']);

        Route::get('/kitchen', [Restaurant\KitchenController::class, 'index']);
        Route::get('/kitchen/mark-as-cooked/{id}', [Restaurant\KitchenController::class, 'markAsCooked']);
        Route::post('/refresh-orders-list', [Restaurant\KitchenController::class, 'refreshOrdersList']);
        Route::post('/refresh-line-orders-list', [Restaurant\KitchenController::class, 'refreshLineOrdersList']);

        Route::get('/orders', [Restaurant\OrderController::class, 'index']);
        Route::get('/orders/mark-as-served/{id}', [Restaurant\OrderController::class, 'markAsServed']);
        Route::get('/data/get-pos-details', [Restaurant\DataController::class, 'getPosDetails']);
        Route::get('/data/check-staff-pin', [Restaurant\DataController::class, 'checkStaffPin']);
        Route::get('/orders/mark-line-order-as-served/{id}', [Restaurant\OrderController::class, 'markLineOrderAsServed']);
        Route::get('/print-line-order', [Restaurant\OrderController::class, 'printLineOrder']);
    });

    Route::get('bookings/get-todays-bookings', [Restaurant\BookingController::class, 'getTodaysBookings']);
    Route::resource('bookings', Restaurant\BookingController::class);

    Route::resource('types-of-service', TypesOfServiceController::class);
    Route::get('sells/edit-shipping/{id}', [SellController::class, 'editShipping']);
    Route::put('sells/update-shipping/{id}', [SellController::class, 'updateShipping']);
    Route::get('shipments', [SellController::class, 'shipments']);
    Route::get('fulfillment/picking', [FulfillmentController::class, 'picking'])->name('fulfillment.picking');
    Route::get('fulfillment/packing', [FulfillmentController::class, 'packing'])->name('fulfillment.packing');
    Route::get('fulfillment/shipment', [FulfillmentController::class, 'shipment'])->name('fulfillment.shipment');
    Route::get('fulfillment/{id}', [FulfillmentController::class, 'show'])->name('fulfillment.show');
    Route::post('fulfillment/{id}/start-picking', [FulfillmentController::class, 'startPicking'])->name('fulfillment.start-picking');
    Route::post('fulfillment/{id}/pick', [FulfillmentController::class, 'pick'])->name('fulfillment.pick');
    Route::post('fulfillment/{id}/pack', [FulfillmentController::class, 'pack'])->name('fulfillment.pack');
    Route::post('fulfillment/{id}/ship', [FulfillmentController::class, 'ship'])->name('fulfillment.ship');

    // Delivery Management (admin assign + auto route)
    Route::get('deliveries/assign', [DeliveryManagementController::class, 'assign'])->name('deliveries.assign');
    Route::post('deliveries/assign', [DeliveryManagementController::class, 'assignStore'])->name('deliveries.assign.store');
    Route::get('deliveries/routes', [DeliveryManagementController::class, 'routes'])->name('deliveries.routes');
    Route::post('deliveries/generate', [DeliveryManagementController::class, 'generate'])->name('deliveries.generate');
    Route::get('deliveries/routes/{id}', [DeliveryManagementController::class, 'show'])->name('deliveries.routes.show')->whereNumber('id');

    Route::post('upload-module', [ManageModulesController::class, 'uploadModule']);
    Route::delete('manage-modules/destroy/{module_name}', [ManageModulesController::class, 'destroy']);
    Route::resource('manage-modules', ManageModulesController::class)
        ->only(['index', 'update']);
    Route::get('regenerate', [ManageModulesController::class, 'regenerate']);

    Route::resource('warranties', WarrantyController::class);

    Route::resource('dashboard-configurator', DashboardConfiguratorController::class)
    ->only(['edit', 'update']);

    Route::get('view-media/{model_id}', [SellController::class, 'viewMedia']);

    //common controller for document & note
    Route::get('get-document-note-page', [DocumentAndNoteController::class, 'getDocAndNoteIndexPage']);
    Route::post('post-document-upload', [DocumentAndNoteController::class, 'postMedia']);
    Route::resource('note-documents', DocumentAndNoteController::class);
    Route::resource('purchase-order', PurchaseOrderController::class);
    Route::get('get-purchase-orders/{contact_id}', [PurchaseOrderController::class, 'getPurchaseOrders']);
    Route::get('get-purchase-order-lines/{purchase_order_id}', [PurchaseController::class, 'getPurchaseOrderLines']);
    Route::get('edit-purchase-orders/{id}/status', [PurchaseOrderController::class, 'getEditPurchaseOrderStatus']);
    Route::put('update-purchase-orders/{id}/status', [PurchaseOrderController::class, 'postEditPurchaseOrderStatus']);
    Route::resource('sales-order', SalesOrderController::class)->only(['index', 'show']);
    Route::post('sales-order/{id}/status', [SalesOrderController::class, 'updateStatus'])->name('sales-order.status');
    Route::post('sales-order/{id}/shipping', [SalesOrderController::class, 'updateShipping'])->name('sales-order.shipping');
    Route::post('sales-order/{id}/fulfillment', [SalesOrderController::class, 'updateFulfillment'])->name('sales-order.fulfillment');
    Route::get('get-sales-orders/{customer_id}', [SalesOrderController::class, 'getSalesOrders']);
    Route::get('get-sales-order-lines', [SellPosController::class, 'getSalesOrderLines']);
    Route::get('edit-sales-orders/{id}/status', [SalesOrderController::class, 'getEditSalesOrderStatus']);
    Route::put('update-sales-orders/{id}/status', [SalesOrderController::class, 'postEditSalesOrderStatus']);
    Route::get('reports/activity-log', [ReportController::class, 'activityLog']);
    Route::get('user-location/{latlng}', [HomeController::class, 'getUserLocation']);
});

// Route::middleware(['EcomApi'])->prefix('api/ecom')->group(function () {
//     Route::get('products/{id?}', [ProductController::class, 'getProductsApi']);
//     Route::get('categories', [CategoryController::class, 'getCategoriesApi']);
//     Route::get('brands', [BrandController::class, 'getBrandsApi']);
//     Route::post('customers', [ContactController::class, 'postCustomersApi']);
//     Route::get('settings', [BusinessController::class, 'getEcomSettings']);
//     Route::get('variations', [ProductController::class, 'getVariationsApi']);
//     Route::post('orders', [SellPosController::class, 'placeOrdersApi']);
// });

//common route
Route::middleware(['auth'])->group(function () {
    Route::get('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
});

Route::middleware(['setData', 'auth', 'SetSessionData', 'language', 'timezone'])->group(function () {
    Route::get('/load-more-notifications', [HomeController::class, 'loadMoreNotifications']);
    Route::get('/get-total-unread', [HomeController::class, 'getTotalUnreadNotifications']);
    Route::get('/purchases/print/{id}', [PurchaseController::class, 'printInvoice']);
    Route::get('/purchases/{id}', [PurchaseController::class, 'show']);
    Route::get('/download-purchase-order/{id}/pdf', [PurchaseOrderController::class, 'downloadPdf'])->name('purchaseOrder.downloadPdf');
    Route::get('/sells/{id}', [SellController::class, 'show']);
    Route::get('/sells/{transaction_id}/print', [SellPosController::class, 'printInvoice'])->name('sell.printInvoice');
    Route::get('/sells/{transaction_id}/print-pdf', [SellPosController::class, 'printInvoicePdf'])->name('sell.printInvoicePdf');
    Route::get('/download-sells/{transaction_id}/pdf', [SellPosController::class, 'downloadPdf'])->name('sell.downloadPdf');
    Route::get('/download-quotation/{id}/pdf', [SellPosController::class, 'downloadQuotationPdf'])
        ->name('quotation.downloadPdf');
    Route::get('/download-packing-list/{id}/pdf', [SellPosController::class, 'downloadPackingListPdf'])
        ->name('packing.downloadPdf');
    Route::get('/sells/invoice-url/{id}', [SellPosController::class, 'showInvoiceUrl']);
    Route::get('/show-notification/{id}', [HomeController::class, 'showNotification']);
    Route::post('/sell/check-invoice-number', [SellController::class, 'checkInvoiceNumber']);
});