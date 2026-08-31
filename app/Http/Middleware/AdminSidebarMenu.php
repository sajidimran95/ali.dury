<?php

namespace App\Http\Middleware;

use App\Utils\ModuleUtil;
use Closure;
use Menu;
use Modules\CustomDashboard\Entities\CustomDashboard;

class AdminSidebarMenu
{
    /**
     * Zoho-style Sales submenu: customers first, then sales transactions.
     * When CRM is installed, customers live under the CRM menu instead.
     */
    protected function addSalesCustomerMenuItems($sub): void
    {
        $moduleUtil = new ModuleUtil();
        $crm_installed = $moduleUtil->isModuleInstalled('Crm');

        if (! $crm_installed && (auth()->user()->can('customer.view') || auth()->user()->can('customer.view_own'))) {
            $customerAttrs = [
                'icon' => '',
                'active' => request()->segment(1) == 'contacts' && request()->input('type') == 'customer',
            ];
            if (auth()->user()->can('customer.create')) {
                $customerAttrs['add_modal'] = [
                    'href' => action([\App\Http\Controllers\ContactController::class, 'create'], ['type' => 'customer']),
                    'container' => '#global_contact_modal',
                ];
                $customerAttrs['add_label'] = __('contact.add_contact');
            }
            $sub->url(
                action([\App\Http\Controllers\ContactController::class, 'index'], ['type' => 'customer']),
                __('report.customer'),
                $customerAttrs
            );
            $cgAttrs = [
                'icon' => '',
                'active' => request()->segment(1) == 'customer-group',
            ];
            if (auth()->user()->can('customer.create')) {
                $cgAttrs = $this->withQuickAddModal(
                    $cgAttrs,
                    action([\App\Http\Controllers\CustomerGroupController::class, 'create']),
                    __('lang_v1.add_customer_group')
                );
            }
            $sub->url(
                action([\App\Http\Controllers\CustomerGroupController::class, 'index']),
                __('lang_v1.customer_groups'),
                $cgAttrs
            );
        }
        if (! $crm_installed && auth()->user()->can('customer.create')) {
            $sub->url(
                action([\App\Http\Controllers\ContactController::class, 'getImportContacts']),
                __('lang_v1.menu_import_contacts'),
                ['icon' => '', 'active' => request()->segment(1) == 'contacts' && request()->segment(2) == 'import']
            );
        }
        if (! $crm_installed && ! empty(env('GOOGLE_MAP_API_KEY')) && (auth()->user()->can('customer.view') || auth()->user()->can('customer.view_own'))) {
            $sub->url(
                action([\App\Http\Controllers\ContactController::class, 'contactMap']),
                __('lang_v1.map'),
                ['icon' => 'fa fas fa-map-marker-alt', 'active' => request()->segment(1) == 'contacts' && request()->segment(2) == 'map']
            );
        }
    }

    /**
     * Sales submenu: 2-level Inventory pattern (section headers, no 3rd-level accordion).
     */
    protected function addSalesGroupedMenu($sub, array $enabled_modules, $is_admin, $pos_settings): void
    {
        $sub->url(
            action([\App\Http\Controllers\HomeController::class, 'salesDashboard']),
            __('lang_v1.sales_dashboard'),
            ['icon' => '', 'active' => request()->segment(1) == 'sales-dashboard']
        );

        $canSalesOrders = ! empty($pos_settings['enable_sales_order'])
            && ($is_admin || auth()->user()->hasAnyPermission(['so.view_own', 'so.view_all', 'so.create']));
        $canPosOrders = auth()->user()->can('sell.create') && in_array('pos_sale', $enabled_modules) && auth()->user()->can('sell.view');
        $canDrafts = in_array('add_sale', $enabled_modules) && ($is_admin || auth()->user()->hasAnyPermission(['draft.view_all', 'draft.view_own']));
        $hasOrdersGroup = $canSalesOrders || $canPosOrders || $canDrafts;

        if ($hasOrdersGroup) {
            $sub->header(__('lang_v1.menu_orders'));
            if ($canSalesOrders) {
                $soIndex = action([\App\Http\Controllers\SalesOrderController::class, 'index']);
                $soAttrs = [
                    'icon' => '',
                    'active' => request()->segment(1) == 'sales-order' && empty(request()->get('source')),
                ];
                if (auth()->user()->can('so.create')) {
                    $soAttrs['add_url'] = action([\App\Http\Controllers\SellController::class, 'create'], ['sale_type' => 'sales_order']);
                    $soAttrs['add_label'] = __('lang_v1.add_sales_order');
                }
                $sub->url($soIndex, __('lang_v1.menu_all_orders'), $soAttrs);
            }

            if ($canPosOrders) {
                $posAttrs = [
                    'icon' => '',
                    'active' => request()->segment(1) == 'pos' && in_array(request()->segment(2), [null, 'create'], true),
                ];
                $posAttrs['add_url'] = action([\App\Http\Controllers\SellPosController::class, 'create']);
                $posAttrs['add_label'] = __('sale.pos_sale');
                $sub->url(
                    action([\App\Http\Controllers\SellPosController::class, 'index']),
                    __('lang_v1.menu_pos_orders'),
                    $posAttrs
                );
            }

            if ($canSalesOrders) {
                $soIndex = action([\App\Http\Controllers\SalesOrderController::class, 'index']);
                $sub->url(
                    $soIndex.'?source=ecommerce',
                    __('lang_v1.menu_online_orders'),
                    [
                        'icon' => '',
                        'active' => request()->segment(1) == 'sales-order' && request()->get('source') == 'ecommerce',
                    ]
                );
                $sub->url(
                    $soIndex.'?source=sale_app',
                    __('lang_v1.menu_field_sales_orders'),
                    [
                        'icon' => '',
                        'active' => request()->segment(1) == 'sales-order' && request()->get('source') == 'sale_app',
                    ]
                );
            }

            if ($canDrafts) {
                $draftAttrs = [
                    'icon' => '',
                    'active' => request()->segment(1) == 'sells' && request()->segment(2) == 'drafts',
                ];
                if (auth()->user()->can('direct_sell.access')) {
                    $draftAttrs = $this->withQuickAddUrl(
                        $draftAttrs,
                        action([\App\Http\Controllers\SellController::class, 'create'], ['status' => 'draft']),
                        __('lang_v1.add_draft')
                    );
                    $draftAttrs['active'] = request()->segment(1) == 'sells' && (
                        request()->segment(2) == 'drafts' ||
                        (request()->segment(2) == 'create' && request()->get('status') == 'draft')
                    );
                }
                $sub->url(
                    action([\App\Http\Controllers\SellController::class, 'getDrafts']),
                    __('lang_v1.menu_draft_orders'),
                    $draftAttrs
                );
            }

            if ($canSalesOrders) {
                $sub->url(
                    action([\App\Http\Controllers\SalesOrderController::class, 'index']),
                    __('lang_v1.manage_order_status'),
                    [
                        'icon' => '',
                        'active' => false,
                        'data-search-terms' => 'order status sales order',
                    ]
                );
            }
        }

        if ($is_admin || auth()->user()->hasAnyPermission(['sell.view', 'sell.create', 'direct_sell.access', 'direct_sell.view', 'view_own_sell_only', 'view_commission_agent_sell', 'access_shipping', 'access_own_shipping', 'access_commission_agent_shipping'])) {
            $sub->header(__('lang_v1.invoices'));
            $salesAttrs = [
                'icon' => '',
                'active' => request()->segment(1) == 'sells' && request()->segment(2) == null,
            ];
            if (in_array('add_sale', $enabled_modules) && auth()->user()->can('direct_sell.access')) {
                $salesAttrs['add_url'] = action([\App\Http\Controllers\SellController::class, 'create']);
                $salesAttrs['add_label'] = __('sale.add_sale');
                $salesAttrs['active'] = request()->segment(1) == 'sells' && in_array(request()->segment(2), [null, 'create'], true) && empty(request()->get('status'));
            }
            $sub->url(
                action([\App\Http\Controllers\SellController::class, 'index']),
                __('lang_v1.invoices'),
                $salesAttrs
            );
        }

        $canFulfillment = $is_admin || auth()->user()->hasAnyPermission(['access_shipping', 'access_own_shipping', 'access_commission_agent_shipping']);
        if ($canFulfillment) {
            $sub->header(__('lang_v1.menu_fulfillment'));
            $sub->url(
                action([\App\Http\Controllers\FulfillmentController::class, 'picking']),
                __('lang_v1.menu_picking'),
                [
                    'icon' => '',
                    'active' => request()->segment(1) == 'fulfillment' && request()->segment(2) == 'picking',
                ]
            );
            $sub->url(
                action([\App\Http\Controllers\FulfillmentController::class, 'packing']),
                __('lang_v1.menu_packing'),
                [
                    'icon' => '',
                    'active' => request()->segment(1) == 'fulfillment' && request()->segment(2) == 'packing',
                ]
            );
            $sub->url(
                action([\App\Http\Controllers\FulfillmentController::class, 'shipment']),
                __('lang_v1.shipments'),
                [
                    'icon' => '',
                    'active' => request()->segment(1) == 'fulfillment' && request()->segment(2) == 'shipment',
                    'data-search-terms' => 'shipment shipments shipping ship delivery package packages tracking boxes picking packing fulfillment',
                ]
            );
        }

        $canReturns = auth()->user()->can('access_sell_return') || auth()->user()->can('access_own_sell_return');
        $canDiscounts = auth()->user()->can('discount.access');
        $canSalesTeam = auth()->user()->can('user.create');
        $canImportOrders = auth()->user()->can('sell.create');
        if ($canReturns || $canDiscounts || $canSalesTeam || $canImportOrders) {
            $sub->header(__('lang_v1.menu_more'));
        }

        if ($canReturns) {
            $sub->url(
                action([\App\Http\Controllers\SellReturnController::class, 'index']),
                __('lang_v1.menu_returns_refunds'),
                [
                    'icon' => '',
                    'active' => request()->segment(1) == 'sell-return' && request()->segment(2) == null,
                    'data-search-terms' => 'return returns refund refunds sell return list sell return',
                ]
            );
        }

        if ($canDiscounts) {
            $discountAttrs = [
                'icon' => '',
                'active' => request()->segment(1) == 'discount',
                'data-search-terms' => 'discount discounts promotion promotions offer offers',
            ];
            $discountAttrs = $this->withQuickAddModal(
                $discountAttrs,
                action([\App\Http\Controllers\DiscountController::class, 'create']),
                __('lang_v1.add_discount')
            );
            $sub->url(
                action([\App\Http\Controllers\DiscountController::class, 'index']),
                __('lang_v1.menu_discounts_promotions'),
                $discountAttrs
            );
        }

        if ($canSalesTeam) {
            $agentAttrs = [
                'icon' => '',
                'active' => request()->segment(1) == 'sales-commission-agents',
            ];
            $agentAttrs = $this->withQuickAddModal(
                $agentAttrs,
                action([\App\Http\Controllers\SalesCommissionAgentController::class, 'create']),
                __('lang_v1.add_sales_commission_agent')
            );
            $sub->url(
                action([\App\Http\Controllers\SalesCommissionAgentController::class, 'index']),
                __('lang_v1.menu_sales_team_commissions'),
                $agentAttrs
            );
        }

        if ($canImportOrders) {
            $sub->url(
                action([\App\Http\Controllers\ImportSalesController::class, 'index']),
                __('lang_v1.menu_import_orders'),
                ['icon' => '', 'active' => request()->segment(1) == 'import-sales']
            );
        }

        $moduleUtil = new ModuleUtil();
        if (! $moduleUtil->isModuleInstalled('Crm')) {
            $this->addSalesCustomerMenuItems($sub);
        }

        if (in_array('add_sale', $enabled_modules) && ($is_admin || auth()->user()->hasAnyPermission(['quotation.view_all', 'quotation.view_own']))) {
            $quoteAttrs = [
                'icon' => '',
                'active' => request()->segment(1) == 'sells' && request()->segment(2) == 'quotations',
            ];
            if (auth()->user()->can('direct_sell.access')) {
                $quoteAttrs = $this->withQuickAddUrl(
                    $quoteAttrs,
                    action([\App\Http\Controllers\SellController::class, 'create'], ['status' => 'quotation']),
                    __('lang_v1.add_quotation')
                );
                $quoteAttrs['active'] = request()->segment(1) == 'sells' && (
                    request()->segment(2) == 'quotations' ||
                    (request()->segment(2) == 'create' && request()->get('status') == 'quotation')
                );
            }
            $sub->url(
                action([\App\Http\Controllers\SellController::class, 'getQuotations']),
                __('lang_v1.menu_quotations'),
                $quoteAttrs
            );
        }

        if (in_array('subscription', $enabled_modules) && auth()->user()->can('direct_sell.access')) {
            $sub->url(
                action([\App\Http\Controllers\SellPosController::class, 'listSubscriptions']),
                __('lang_v1.subscriptions'),
                ['icon' => '', 'active' => request()->segment(1) == 'subscriptions']
            );
        }
    }

    /**
     * Zoho-style Purchases submenu: vendors/suppliers first, then purchase transactions.
     */
    protected function addPurchaseSupplierMenuItems($sub): void
    {
        if (auth()->user()->can('supplier.view') || auth()->user()->can('supplier.view_own')) {
            $supplierAttrs = [
                'icon' => '',
                'active' => request()->segment(1) == 'contacts' && request()->input('type') == 'supplier',
            ];
            if (auth()->user()->can('supplier.create')) {
                $supplierAttrs['add_modal'] = [
                    'href' => action([\App\Http\Controllers\ContactController::class, 'create'], ['type' => 'supplier']),
                    'container' => '#global_contact_modal',
                ];
                $supplierAttrs['add_label'] = __('contact.add_contact');
            }
            $sub->url(
                action([\App\Http\Controllers\ContactController::class, 'index'], ['type' => 'supplier']),
                __('lang_v1.menu_vendors'),
                $supplierAttrs
            );
        }
        if (auth()->user()->can('supplier.create')) {
            $sub->url(
                action([\App\Http\Controllers\ContactController::class, 'getImportContacts']),
                __('lang_v1.menu_import_contacts'),
                ['icon' => '', 'active' => request()->segment(1) == 'contacts' && request()->segment(2) == 'import']
            );
        }
    }

    /**
     * Purchases submenu with section headers (mirrors Sales layout). All items kept.
     */
    protected function addPurchasesGroupedMenu($sub, $common_settings): void
    {
        if (auth()->user()->can('supplier.view') || auth()->user()->can('supplier.view_own') || auth()->user()->can('supplier.create')) {
            $sub->header(__('lang_v1.menu_vendors'));
            $this->addPurchaseSupplierMenuItems($sub);
        }

        $hasOrders = (! empty($common_settings['enable_purchase_requisition']) && (auth()->user()->can('purchase_requisition.view_all') || auth()->user()->can('purchase_requisition.view_own')))
            || (! empty($common_settings['enable_purchase_order']) && (auth()->user()->can('purchase_order.view_all') || auth()->user()->can('purchase_order.view_own')));

        if ($hasOrders) {
            $sub->header(__('lang_v1.menu_purchase_orders'));
        }

        if (! empty($common_settings['enable_purchase_requisition']) && (auth()->user()->can('purchase_requisition.view_all') || auth()->user()->can('purchase_requisition.view_own'))) {
            $prAttrs = [
                'icon' => '',
                'active' => request()->segment(1) == 'purchase-requisition',
            ];
            if (auth()->user()->can('purchase_requisition.create')) {
                $prAttrs['add_url'] = action([\App\Http\Controllers\PurchaseRequisitionController::class, 'create']);
                $prAttrs['add_label'] = __('lang_v1.add_purchase_requisition');
            }
            $sub->url(
                action([\App\Http\Controllers\PurchaseRequisitionController::class, 'index']),
                __('lang_v1.purchase_requisition'),
                $prAttrs
            );
        }

        if (! empty($common_settings['enable_purchase_order']) && (auth()->user()->can('purchase_order.view_all') || auth()->user()->can('purchase_order.view_own'))) {
            $poAttrs = [
                'icon' => '',
                'active' => request()->segment(1) == 'purchase-order',
            ];
            if (auth()->user()->can('purchase_order.create')) {
                $poAttrs['add_url'] = action([\App\Http\Controllers\PurchaseOrderController::class, 'create']);
                $poAttrs['add_label'] = __('lang_v1.add_purchase_order');
            }
            $sub->url(
                action([\App\Http\Controllers\PurchaseOrderController::class, 'index']),
                __('lang_v1.purchase_order'),
                $poAttrs
            );
        }

        if (auth()->user()->can('purchase.view') || auth()->user()->can('view_own_purchase')) {
            $sub->header(__('lang_v1.menu_purchase_bills'));
            $purchaseAttrs = [
                'icon' => '',
                'active' => request()->segment(1) == 'purchases' && request()->segment(2) == null,
            ];
            if (auth()->user()->can('purchase.create')) {
                $purchaseAttrs['add_url'] = action([\App\Http\Controllers\PurchaseController::class, 'create']);
                $purchaseAttrs['add_label'] = __('purchase.add_purchase');
                $purchaseAttrs['active'] = request()->segment(1) == 'purchases' && in_array(request()->segment(2), [null, 'create'], true);
            }
            $sub->url(
                action([\App\Http\Controllers\PurchaseController::class, 'index']),
                __('lang_v1.menu_purchases_all'),
                $purchaseAttrs
            );
        }

        if (auth()->user()->can('purchase.update')) {
            $sub->header(__('lang_v1.menu_purchase_returns'));
            $prReturnAttrs = [
                'icon' => '',
                'active' => request()->segment(1) == 'purchase-return',
            ];
            $prReturnAttrs = $this->withQuickAddUrl(
                $prReturnAttrs,
                action([\App\Http\Controllers\CombinedPurchaseReturnController::class, 'create']),
                __('lang_v1.add_purchase_return')
            );
            $sub->url(
                action([\App\Http\Controllers\PurchaseReturnController::class, 'index']),
                __('lang_v1.menu_purchase_returns'),
                $prReturnAttrs
            );
        }
    }

    /**
     * Inventory submenu grouped to match requirements PDF.
     * All existing items kept — only rearranged.
     */
    protected function addItemsMenuItems($sub): void
    {
        // Groups are built in addInventoryGroupedMenu() — kept for hasItemsMenu compatibility.
    }

    /**
     * Inventory submenu rearranged with section headers (no 3rd-level nesting).
     * All existing items kept — only order / grouping labels changed.
     */
    protected function addInventoryGroupedMenu($sub, array $enabled_modules): void
    {
        // —— Inventory ——
        $sub->header(__('lang_v1.menu_inventory'));
        if (auth()->user()->can('product.view') || auth()->user()->can('dashboard.data') || auth()->user()->can('stock_report.view')) {
            $sub->url(
                action([\App\Http\Controllers\InventoryDashboardController::class, 'index']),
                __('lang_v1.menu_dashboard'),
                ['icon' => '', 'active' => request()->segment(1) == 'inventory' && (request()->segment(2) == null || request()->segment(2) == 'dashboard')]
            );
        }
        if (auth()->user()->can('product.view')) {
            $productAttrs = [
                'icon' => '',
                'active' => request()->segment(1) == 'products' && request()->segment(2) == '',
            ];
            if (auth()->user()->can('product.create')) {
                $productAttrs['add_url'] = action([\App\Http\Controllers\ProductController::class, 'create']);
                $productAttrs['add_label'] = __('product.add_product');
                $productAttrs['active'] = request()->segment(1) == 'products' && in_array(request()->segment(2), [null, 'create'], true);
            }
            $sub->url(
                action([\App\Http\Controllers\ProductController::class, 'index']),
                __('lang_v1.menu_products'),
                $productAttrs
            );
        }
        $this->addInventoryStockLinks($sub, $enabled_modules);

        // —— Product Catalog ——
        $sub->header(__('lang_v1.menu_product_catalog'));
        if (auth()->user()->can('product.create')) {
            $variationAttrs = [
                'icon' => '',
                'active' => request()->segment(1) == 'variation-templates',
            ];
            $variationAttrs = $this->withQuickAddModal(
                $variationAttrs,
                action([\App\Http\Controllers\VariationTemplateController::class, 'create']),
                __('product.add_variation')
            );
            $sub->url(
                action([\App\Http\Controllers\VariationTemplateController::class, 'index']),
                __('product.variations'),
                $variationAttrs
            );
        }
        if (auth()->user()->can('category.view') || auth()->user()->can('category.create')) {
            $categoryAttrs = [
                'icon' => '',
                'active' => request()->segment(1) == 'taxonomies' && request()->get('type') == 'product',
            ];
            if (auth()->user()->can('category.create')) {
                $categoryAttrs = $this->withQuickAddModal(
                    $categoryAttrs,
                    action([\App\Http\Controllers\TaxonomyController::class, 'create']) . '?type=product',
                    __('category.add_category')
                );
            }
            $sub->url(
                action([\App\Http\Controllers\TaxonomyController::class, 'index']) . '?type=product',
                __('category.categories'),
                $categoryAttrs
            );
        }
        if (auth()->user()->can('brand.view') || auth()->user()->can('brand.create')) {
            $brandAttrs = [
                'icon' => '',
                'active' => request()->segment(1) == 'brands',
            ];
            if (auth()->user()->can('brand.create')) {
                $brandAttrs = $this->withQuickAddModal(
                    $brandAttrs,
                    action([\App\Http\Controllers\BrandController::class, 'create']),
                    __('brand.add_brand')
                );
            }
            $sub->url(
                action([\App\Http\Controllers\BrandController::class, 'index']),
                __('brand.brands'),
                $brandAttrs
            );
        }
        if (auth()->user()->can('unit.view') || auth()->user()->can('unit.create')) {
            $unitAttrs = [
                'icon' => '',
                'active' => request()->segment(1) == 'units',
            ];
            if (auth()->user()->can('unit.create')) {
                $unitAttrs = $this->withQuickAddModal(
                    $unitAttrs,
                    action([\App\Http\Controllers\UnitController::class, 'create']),
                    __('unit.add_unit')
                );
            }
            $sub->url(
                action([\App\Http\Controllers\UnitController::class, 'index']),
                __('unit.units'),
                $unitAttrs
            );
        }
        $warrantyAttrs = [
            'icon' => '',
            'active' => request()->segment(1) == 'warranties',
        ];
        if (auth()->user()->can('product.create')) {
            $warrantyAttrs = $this->withQuickAddModal(
                $warrantyAttrs,
                action([\App\Http\Controllers\WarrantyController::class, 'create']),
                __('lang_v1.add_warranty')
            );
        }
        $sub->url(
            action([\App\Http\Controllers\WarrantyController::class, 'index']),
            __('lang_v1.warranties'),
            $warrantyAttrs
        );

        // —— Pricing & Labels ——
        if (auth()->user()->can('product.create') || auth()->user()->can('product.view')) {
            $sub->header(__('lang_v1.menu_pricing_labels'));
        }
        if (auth()->user()->can('product.create')) {
            $sub->url(
                action([\App\Http\Controllers\SellingPriceGroupController::class, 'updateProductPrice']),
                __('lang_v1.update_product_price'),
                ['icon' => '', 'active' => request()->segment(1) == 'update-product-price']
            );
            $spgAttrs = [
                'icon' => '',
                'active' => request()->segment(1) == 'selling-price-group',
            ];
            $spgAttrs = $this->withQuickAddModal(
                $spgAttrs,
                action([\App\Http\Controllers\SellingPriceGroupController::class, 'create']),
                __('lang_v1.add_selling_price_group')
            );
            $sub->url(
                action([\App\Http\Controllers\SellingPriceGroupController::class, 'index']),
                __('lang_v1.menu_selling_price_groups'),
                $spgAttrs
            );
        }
        if (auth()->user()->can('product.view')) {
            $sub->url(
                action([\App\Http\Controllers\LabelsController::class, 'show']),
                __('barcode.print_labels'),
                ['icon' => '', 'active' => request()->segment(1) == 'labels' && request()->segment(2) == 'show']
            );
        }

        // —— Import Data ——
        if (auth()->user()->can('product.create')) {
            $sub->header(__('lang_v1.menu_import_data'));
            $sub->url(
                action([\App\Http\Controllers\ImportProductsController::class, 'index']),
                __('lang_v1.menu_import_products'),
                ['icon' => '', 'active' => request()->segment(1) == 'import-products']
            );
        }

        // —— Shipping Settings ——
        if (auth()->user()->can('shipping_method.view') || auth()->user()->can('shipping_method.create')) {
            $sub->header(__('lang_v1.menu_shipping_settings'));
            $sub->url(
                action([\App\Http\Controllers\ShippingMethodController::class, 'index']),
                __('lang_v1.shipping_methods'),
                [
                    'icon' => '',
                    'active' => request()->segment(1) == 'shipping-methods',
                    'data-search-terms' => 'shipping method methods carrier carriers shipment ship delivery freight ups fedex usps dhl',
                ]
            );
        }
    }

    /**
     * Stock transfers / adjustments / opening stock (Inventory group).
     */
    protected function addInventoryStockLinks($sub, array $enabled_modules): void
    {
        if (in_array('stock_transfers', $enabled_modules) && (auth()->user()->can('stock_transfer.view') || auth()->user()->can('stock_transfer.create') || auth()->user()->can('stock_transfer.view_own'))) {
            if (auth()->user()->can('stock_transfer.view') || auth()->user()->can('stock_transfer.view_own')) {
                $transferAttrs = [
                    'icon' => '',
                    'active' => request()->segment(1) == 'stock-transfers' && request()->segment(2) == null,
                ];
                if (auth()->user()->can('stock_transfer.create')) {
                    $transferAttrs['add_url'] = action([\App\Http\Controllers\StockTransferController::class, 'create']);
                    $transferAttrs['add_label'] = __('lang_v1.add_stock_transfer');
                    $transferAttrs['active'] = request()->segment(1) == 'stock-transfers' && in_array(request()->segment(2), [null, 'create'], true);
                }
                $sub->url(
                    action([\App\Http\Controllers\StockTransferController::class, 'index']),
                    __('lang_v1.menu_stock_transfers'),
                    $transferAttrs
                );
            }
        }

        if (in_array('stock_adjustment', $enabled_modules) && (auth()->user()->can('stock_adjustment.view') || auth()->user()->can('stock_adjustment.create') || auth()->user()->can('view_own_stock_adjustment'))) {
            if (auth()->user()->can('stock_adjustment.view') || auth()->user()->can('view_own_stock_adjustment')) {
                $adjustAttrs = [
                    'icon' => '',
                    'active' => request()->segment(1) == 'stock-adjustments' && request()->segment(2) == null,
                ];
                if (auth()->user()->can('stock_adjustment.create')) {
                    $adjustAttrs['add_url'] = action([\App\Http\Controllers\StockAdjustmentController::class, 'create']);
                    $adjustAttrs['add_label'] = __('stock_adjustment.add');
                    $adjustAttrs['active'] = request()->segment(1) == 'stock-adjustments' && in_array(request()->segment(2), [null, 'create'], true);
                }
                $sub->url(
                    action([\App\Http\Controllers\StockAdjustmentController::class, 'index']),
                    __('lang_v1.menu_stock_adjustments'),
                    $adjustAttrs
                );
            }
        }

        if (auth()->user()->can('product.opening_stock')) {
            $sub->url(
                action([\App\Http\Controllers\ImportOpeningStockController::class, 'index']),
                __('lang_v1.menu_opening_stock'),
                ['icon' => '', 'active' => request()->segment(1) == 'import-opening-stock']
            );
        }
    }

    protected function hasItemsMenu(): bool
    {
        return auth()->user()->can('product.view') || auth()->user()->can('product.create') ||
            auth()->user()->can('brand.view') || auth()->user()->can('unit.view') ||
            auth()->user()->can('category.view') || auth()->user()->can('brand.create') ||
            auth()->user()->can('unit.create') || auth()->user()->can('category.create') ||
            auth()->user()->can('shipping_method.view') || auth()->user()->can('shipping_method.create');
    }

    /**
     * @deprecated Use addInventoryStockLinks — kept so hasInventoryMenu stays accurate.
     */
    protected function addInventoryMenuItems($sub, array $enabled_modules): void
    {
        $this->addInventoryStockLinks($sub, $enabled_modules);
    }

    protected function hasInventoryMenu(array $enabled_modules): bool
    {
        $has_stock = in_array('stock_transfers', $enabled_modules)
            && (auth()->user()->can('stock_transfer.view') || auth()->user()->can('stock_transfer.create') || auth()->user()->can('stock_transfer.view_own'));
        $has_adjust = in_array('stock_adjustment', $enabled_modules)
            && (auth()->user()->can('stock_adjustment.view') || auth()->user()->can('stock_adjustment.create') || auth()->user()->can('view_own_stock_adjustment'));
        $has_opening = auth()->user()->can('product.opening_stock');

        return $has_stock || $has_adjust || $has_opening;
    }

    /**
     * Zoho-style sidebar + button — opens create form in global modal.
     */
    protected function withQuickAddModal(array $attrs, ?string $href, ?string $label = null, string $container = '.view_modal'): array
    {
        if (! empty($href)) {
            $attrs['add_modal'] = [
                'href' => $href,
                'container' => $container,
            ];
            $attrs['add_label'] = $label ?? __('messages.add');
        }

        return $attrs;
    }

    /**
     * Zoho-style sidebar + button — navigates to create page.
     */
    protected function withQuickAddUrl(array $attrs, ?string $url, ?string $label = null): array
    {
        if (! empty($url)) {
            $attrs['add_url'] = $url;
            $attrs['add_label'] = $label ?? __('messages.add');
        }

        return $attrs;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->ajax()) {
            return $next($request);
        }

        Menu::create('admin-sidebar-menu', function ($menu) {
            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];

            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];

            $is_admin = auth()->user()->hasRole('Admin#' . session('business.id')) ? true : false;

            $menu->addHeader(__('home.main_menu'), 1);

            // Order: core ops first → CRM/ecom → finance → reports → secondary → settings last
            // 10 Home | 20 Inventory | 30 Sales | 40 Purchases | 50 CRM | 60 Ecommerce
            // 70 Expenses | 80 Accounts | 90 Reports | 100–150 restaurant/tools | 160 Settings

            $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), ['icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="tw-size-5 tw-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V20a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V9.5"/></svg>', 'active' => request()->segment(1) == 'home'])->order(10);

            // Inventory (grouped: Inventory / Product Catalog / Pricing / Import / Shipping)
            if ($this->hasItemsMenu() || $this->hasInventoryMenu($enabled_modules) || auth()->user()->can('product.view') || auth()->user()->can('dashboard.data')) {
                $menu->dropdown(
                    __('lang_v1.menu_inventory'),
                    function ($sub) use ($enabled_modules) {
                        $this->addInventoryGroupedMenu($sub, $enabled_modules);
                    },
                    ['icon' => '<svg aria-hidden="true" class="tw-size-5 tw-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>', 'id' => 'tour_step5']
                )->order(20);
            }

            // Sales: Dashboard, nested Orders, Invoices, nested Fulfillment, then remaining items
            if ($is_admin || auth()->user()->hasAnyPermission(['sell.view', 'sell.create', 'direct_sell.access', 'view_own_sell_only', 'view_commission_agent_sell', 'access_shipping', 'access_own_shipping', 'access_commission_agent_shipping', 'access_sell_return', 'direct_sell.view', 'direct_sell.update', 'access_own_sell_return'])
                || auth()->user()->can('customer.view') || auth()->user()->can('customer.view_own')) {
                $menu->dropdown(
                    __('lang_v1.menu_sales'),
                    function ($sub) use ($enabled_modules, $is_admin, $pos_settings) {
                        $this->addSalesGroupedMenu($sub, $enabled_modules, $is_admin, $pos_settings);
                    },
                    ['icon' => '<svg aria-hidden="true" class="tw-size-5 tw-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="20" r="1.5"/><circle cx="17" cy="20" r="1.5"/><path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 2-1.5L21 8H7"/></svg>', 'id' => 'tour_step7']
                )->order(30);
            }

            // Purchases (Vendors / Orders / Purchases / Returns)
            if (in_array('purchases', $enabled_modules) && (auth()->user()->can('purchase.view') || auth()->user()->can('purchase.create') || auth()->user()->can('purchase.update')
                || auth()->user()->can('supplier.view') || auth()->user()->can('supplier.view_own'))) {
                $menu->dropdown(
                    __('lang_v1.menu_purchases'),
                    function ($sub) use ($common_settings) {
                        $this->addPurchasesGroupedMenu($sub, $common_settings);
                    },
                    ['icon' => '<svg aria-hidden="true" class="tw-size-5 tw-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M6 7h12l-1 13H7L6 7Z"/><path d="M9 7V5a3 3 0 0 1 6 0v2"/></svg>', 'id' => 'tour_step6']
                )->order(40);
            }

            //Expense dropdown
            if (in_array('expenses', $enabled_modules) && (auth()->user()->can('all_expense.access') || auth()->user()->can('view_own_expense'))) {
                $menu->dropdown(
                    __('expense.expenses'),
                    function ($sub) {
                        $sub->header(__('lang_v1.menu_expense_list'));
                        $expenseAttrs = [
                            'icon' => '',
                            'active' => request()->segment(1) == 'expenses' && request()->segment(2) == null,
                        ];
                        if (auth()->user()->can('expense.add')) {
                            $expenseAttrs = $this->withQuickAddUrl(
                                $expenseAttrs,
                                action([\App\Http\Controllers\ExpenseController::class, 'create']),
                                __('expense.add_expense')
                            );
                            $expenseAttrs['active'] = request()->segment(1) == 'expenses' && in_array(request()->segment(2), [null, 'create'], true);
                        }
                        $sub->url(
                            action([\App\Http\Controllers\ExpenseController::class, 'index']),
                            __('lang_v1.menu_expenses_all'),
                            $expenseAttrs
                        );
                        if (auth()->user()->can('expense.add')) {
                            $sub->url(
                                action([\App\Http\Controllers\ExpenseController::class, 'importExpense']),
                                __('lang_v1.menu_import_expense'),
                                ['icon' => '', 'active' => request()->segment(1) == 'import-expense']
                            );
                        }

                        if (auth()->user()->can('expense.add') || auth()->user()->can('expense.edit')) {
                            $sub->header(__('lang_v1.menu_expense_setup'));
                            $expCatAttrs = [
                                'icon' => '',
                                'active' => request()->segment(1) == 'expense-categories',
                            ];
                            if (auth()->user()->can('expense.add')) {
                                $expCatAttrs = $this->withQuickAddModal(
                                    $expCatAttrs,
                                    action([\App\Http\Controllers\ExpenseCategoryController::class, 'create']),
                                    __('expense.add_expense_category')
                                );
                            }
                            $sub->url(
                                action([\App\Http\Controllers\ExpenseCategoryController::class, 'index']),
                                __('expense.expense_categories'),
                                $expCatAttrs
                            );
                        }
                    },
                    ['icon' => '<svg aria-hidden="true" class="tw-size-5 tw-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3h8l4 4v14a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z"/><path d="M15 3v4h4"/><path d="M9 13h6"/><path d="M9 17h4"/></svg>']
                )->order(70);
            }
            //Accounts dropdown
            if (auth()->user()->can('account.access') && in_array('account', $enabled_modules)) {
                $menu->dropdown(
                    __('lang_v1.payment_accounts'),
                    function ($sub) {
                        $sub->header(__('lang_v1.menu_accounts'));
                        $accountAttrs = [
                            'icon' => '',
                            'active' => request()->segment(1) == 'account' && request()->segment(2) == 'account',
                        ];
                        if (auth()->user()->can('account.access')) {
                            $accountAttrs = $this->withQuickAddModal(
                                $accountAttrs,
                                action([\App\Http\Controllers\AccountController::class, 'create']),
                                __('account.add_account')
                            );
                        }
                        $sub->url(
                            action([\App\Http\Controllers\AccountController::class, 'index']),
                            __('account.list_accounts'),
                            $accountAttrs
                        );

                        $sub->header(__('lang_v1.menu_account_reports'));
                        $sub->url(
                            action([\App\Http\Controllers\AccountReportsController::class, 'balanceSheet']),
                            __('account.balance_sheet'),
                            ['icon' => '', 'active' => request()->segment(1) == 'account' && request()->segment(2) == 'balance-sheet']
                        );
                        $sub->url(
                            action([\App\Http\Controllers\AccountReportsController::class, 'trialBalance']),
                            __('account.trial_balance'),
                            ['icon' => '', 'active' => request()->segment(1) == 'account' && request()->segment(2) == 'trial-balance']
                        );
                        $sub->url(
                            action([\App\Http\Controllers\AccountController::class, 'cashFlow']),
                            __('lang_v1.cash_flow'),
                            ['icon' => '', 'active' => request()->segment(1) == 'account' && request()->segment(2) == 'cash-flow']
                        );
                        $sub->url(
                            action([\App\Http\Controllers\AccountReportsController::class, 'paymentAccountReport']),
                            __('account.payment_account_report'),
                            ['icon' => '', 'active' => request()->segment(1) == 'account' && request()->segment(2) == 'payment-account-report']
                        );
                    },
                    ['icon' => '<svg aria-hidden="true" class="tw-size-5 tw-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>']
                )->order(80);
            }

            // Team Schedule hub
            if (auth()->user()->can('schedule.view') || auth()->user()->can('schedule.manage')
                || auth()->user()->can('time_off.request') || auth()->user()->can('time_off.manage')
                || auth()->user()->can('attendance.view') || auth()->user()->can('attendance.view_all')
                || auth()->user()->can('attendance.edit') || $is_admin) {
                $menu->dropdown(
                    __('team_schedule.team_schedule'),
                    function ($sub) use ($is_admin) {
                        if (auth()->user()->can('schedule.view') || auth()->user()->can('schedule.manage')
                            || auth()->user()->can('attendance.view') || auth()->user()->can('attendance.view_all')
                            || $is_admin) {
                            $sub->header(__('team_schedule.menu_schedule'));
                            $sub->url(
                                action([\App\Http\Controllers\TeamScheduleController::class, 'calendar']),
                                __('team_schedule.shared_calendar'),
                                ['icon' => '', 'active' => request()->segment(1) == 'team-schedule' && request()->segment(2) == 'calendar']
                            );
                            $sub->url(
                                action([\App\Http\Controllers\TeamScheduleController::class, 'shifts']),
                                __('team_schedule.shifts'),
                                ['icon' => '', 'active' => request()->segment(1) == 'team-schedule' && request()->segment(2) == 'shifts']
                            );
                        }
                        if (auth()->user()->can('time_off.request') || auth()->user()->can('time_off.manage')
                            || auth()->user()->can('attendance.clock') || $is_admin) {
                            $sub->header(__('team_schedule.time_off'));
                            $sub->url(
                                action([\App\Http\Controllers\TimeOffController::class, 'index']),
                                __('team_schedule.time_off'),
                                ['icon' => '', 'active' => request()->segment(1) == 'team-schedule' && request()->segment(2) == 'time-off']
                            );
                        }
                        if (auth()->user()->can('attendance.view') || auth()->user()->can('attendance.view_all') || $is_admin) {
                            $sub->header(__('report.menu_time_clock'));
                            $sub->url(
                                action([\App\Http\Controllers\AttendanceController::class, 'report']),
                                __('attendance.attendance_report'),
                                ['icon' => '', 'active' => request()->segment(1) == 'attendance']
                            );
                        }
                        if (auth()->user()->can('schedule.manage') || auth()->user()->can('attendance.edit') || $is_admin) {
                            $sub->header(__('messages.settings'));
                            $sub->url(
                                action([\App\Http\Controllers\TeamScheduleController::class, 'colors']),
                                __('team_schedule.member_colors'),
                                ['icon' => '', 'active' => request()->segment(1) == 'team-schedule' && request()->segment(2) == 'colors']
                            );
                        }
                    },
                    ['icon' => '<svg aria-hidden="true" class="tw-size-5 tw-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>']
                )->order(55);
            }

            //Reports dropdown — grouped to match requirements PDF (all items kept)
            if (auth()->user()->can('purchase_n_sell_report.view') || auth()->user()->can('contacts_report.view')
                || auth()->user()->can('stock_report.view') || auth()->user()->can('tax_report.view')
                || auth()->user()->can('trending_product_report.view') || auth()->user()->can('sales_representative.view') || auth()->user()->can('register_report.view')
                || auth()->user()->can('expense_report.view')
                || auth()->user()->can('attendance.view') || auth()->user()->can('attendance.view_all')
                || auth()->user()->can('account.access') || $is_admin) {
                $menu->dropdown(
                    __('report.reports'),
                    function ($sub) use ($enabled_modules, $is_admin) {
                        $sub->header(__('report.menu_group_business_overview'));
                            if (auth()->user()->can('profit_loss_report.view')) {
                                $sub->url(
                                    action([\App\Http\Controllers\ReportController::class, 'getProfitLoss']),
                                    __('report.menu_profit_loss'),
                                    ['icon' => '', 'active' => request()->segment(2) == 'profit-loss']
                                );
                            }
                            if (auth()->user()->can('account.access') && in_array('account', $enabled_modules)) {
                                $sub->url(
                                    action([\App\Http\Controllers\AccountController::class, 'cashFlow']),
                                    __('lang_v1.cash_flow'),
                                    ['icon' => '', 'active' => request()->segment(1) == 'account' && request()->segment(2) == 'cash-flow']
                                );
                            }
                            if (in_array('expenses', $enabled_modules) && auth()->user()->can('expense_report.view')) {
                                $sub->url(
                                    action([\App\Http\Controllers\ReportController::class, 'getExpenseReport']),
                                    __('report.expense_report'),
                                    ['icon' => '', 'active' => request()->segment(2) == 'expense-report']
                                );
                            }
                            if (auth()->user()->can('register_report.view')) {
                                $sub->url(
                                    action([\App\Http\Controllers\ReportController::class, 'getRegisterReport']),
                                    __('report.register_report'),
                                    ['icon' => '', 'active' => request()->segment(2) == 'register-report']
                                );
                            }
                        $sub->header(__('report.menu_group_sales'));
                            if ((in_array('purchases', $enabled_modules) || in_array('add_sale', $enabled_modules) || in_array('pos_sale', $enabled_modules)) && auth()->user()->can('purchase_n_sell_report.view')) {
                                $sub->url(
                                    action([\App\Http\Controllers\ReportController::class, 'getPurchaseSell']),
                                    __('report.sales_summary'),
                                    ['icon' => '', 'active' => request()->segment(2) == 'purchase-sell']
                                );
                            }
                            if (auth()->user()->can('purchase_n_sell_report.view')) {
                                $sub->url(
                                    action([\App\Http\Controllers\ReportController::class, 'sellPaymentReport']),
                                    __('report.sales_by_customer'),
                                    ['icon' => '', 'active' => request()->segment(2) == 'sell-payment-report']
                                );
                                $sub->url(
                                    action([\App\Http\Controllers\ReportController::class, 'getproductSellReport']),
                                    __('report.sales_by_product'),
                                    ['icon' => '', 'active' => request()->segment(2) == 'product-sell-report']
                                );
                            }
                            if (auth()->user()->can('sales_representative.view')) {
                                $sub->url(
                                    action([\App\Http\Controllers\ReportController::class, 'getSalesRepresentativeReport']),
                                    __('report.menu_sales_representative'),
                                    ['icon' => '', 'active' => request()->segment(2) == 'sales-representative-report']
                                );
                            }
                            if (auth()->user()->can('contacts_report.view')) {
                                $sub->url(
                                    action([\App\Http\Controllers\ReportController::class, 'getCustomerGroup']),
                                    __('report.menu_customer_groups'),
                                    ['icon' => '', 'active' => request()->segment(2) == 'customer-group']
                                );
                            }
                        $sub->header(__('report.menu_group_purchasing'));
                            if (config('constants.show_report_606') == true) {
                                $sub->url(
                                    action([\App\Http\Controllers\ReportController::class, 'purchaseReport']),
                                    __('report.purchase_summary'),
                                    ['icon' => '', 'active' => request()->segment(2) == 'purchase-report']
                                );
                            } elseif (auth()->user()->can('purchase_n_sell_report.view')) {
                                $sub->url(
                                    action([\App\Http\Controllers\ReportController::class, 'getPurchaseSell']),
                                    __('report.purchase_summary'),
                                    ['icon' => '', 'active' => request()->segment(2) == 'purchase-sell']
                                );
                            }
                            if (auth()->user()->can('purchase_n_sell_report.view')) {
                                $sub->url(
                                    action([\App\Http\Controllers\ReportController::class, 'getproductPurchaseReport']),
                                    __('report.purchases_by_product'),
                                    ['icon' => '', 'active' => request()->segment(2) == 'product-purchase-report']
                                );
                                $sub->url(
                                    action([\App\Http\Controllers\ReportController::class, 'purchasePaymentReport']),
                                    __('report.menu_purchase_payments'),
                                    ['icon' => '', 'active' => request()->segment(2) == 'purchase-payment-report']
                                );
                            }
                            if (auth()->user()->can('contacts_report.view')) {
                                $sub->url(
                                    action([\App\Http\Controllers\ReportController::class, 'getCustomerSuppliers']),
                                    __('report.supplier'),
                                    ['icon' => '', 'active' => request()->segment(2) == 'customer-supplier']
                                );
                            }
                        $sub->header(__('report.menu_group_inventory'));
                            if (auth()->user()->can('stock_report.view')) {
                                $sub->url(
                                    action([\App\Http\Controllers\ReportController::class, 'getStockReport']),
                                    __('report.stock_report'),
                                    ['icon' => '', 'active' => request()->segment(2) == 'stock-report']
                                );
                                if (in_array('stock_adjustment', $enabled_modules)) {
                                    $sub->url(
                                        action([\App\Http\Controllers\ReportController::class, 'getStockAdjustmentReport']),
                                        __('report.menu_stock_adjustment'),
                                        ['icon' => '', 'active' => request()->segment(2) == 'stock-adjustment-report']
                                    );
                                }
                                $sub->url(
                                    action([\App\Http\Controllers\ReportController::class, 'getStockReport']),
                                    __('report.inventory_valuation'),
                                    ['icon' => '', 'active' => request()->segment(2) == 'stock-report']
                                );
                            }
                            if (auth()->user()->can('purchase_n_sell_report.view')) {
                                $sub->url(
                                    action([\App\Http\Controllers\ReportController::class, 'itemsReport']),
                                    __('lang_v1.items_report'),
                                    ['icon' => '', 'active' => request()->segment(2) == 'items-report']
                                );
                            }
                            if (auth()->user()->can('trending_product_report.view')) {
                                $sub->url(
                                    action([\App\Http\Controllers\ReportController::class, 'getTrendingProducts']),
                                    __('report.trending_products'),
                                    ['icon' => '', 'active' => request()->segment(2) == 'trending-products']
                                );
                            }
                        $sub->header(__('report.menu_group_customers_suppliers'));
                            if (auth()->user()->can('contacts_report.view')) {
                                $sub->url(
                                    action([\App\Http\Controllers\ReportController::class, 'getCustomerSuppliers']),
                                    __('report.customer_report'),
                                    ['icon' => '', 'active' => request()->segment(2) == 'customer-supplier']
                                );
                                $sub->url(
                                    action([\App\Http\Controllers\ReportController::class, 'getCustomerSuppliers']),
                                    __('report.supplier_report'),
                                    ['icon' => '', 'active' => request()->segment(2) == 'customer-supplier']
                                );
                                $sub->url(
                                    action([\App\Http\Controllers\ReportController::class, 'getCustomerGroup']),
                                    __('report.menu_customer_groups'),
                                    ['icon' => '', 'active' => request()->segment(2) == 'customer-group']
                                );
                            }
                        $sub->header(__('report.menu_group_taxes_compliance'));
                            if (auth()->user()->can('tax_report.view')) {
                                $sub->url(
                                    action([\App\Http\Controllers\ReportController::class, 'getTaxReport']),
                                    __('report.tax_report'),
                                    ['icon' => '', 'active' => request()->segment(2) == 'tax-report']
                                );
                                $sub->url(
                                    action([\App\Http\Controllers\ReportController::class, 'getMsaReport']),
                                    __('report.msa_tobacco_report'),
                                    ['icon' => '', 'active' => request()->segment(2) == 'msa-report']
                                );
                            }
                        $sub->header(__('report.menu_group_employees_activity'));
                            if (auth()->user()->can('attendance.view') || auth()->user()->can('attendance.view_all')) {
                                $sub->url(
                                    action([\App\Http\Controllers\AttendanceController::class, 'report']),
                                    __('report.menu_time_clock'),
                                    ['icon' => '', 'active' => request()->segment(1) == 'attendance']
                                );
                            }
                            if (auth()->user()->can('sales_representative.view')) {
                                $sub->url(
                                    action([\App\Http\Controllers\ReportController::class, 'getSalesRepresentativeReport']),
                                    __('report.menu_sales_representatives'),
                                    ['icon' => '', 'active' => request()->segment(2) == 'sales-representative-report']
                                );
                            }
                            if ($is_admin) {
                                $sub->url(
                                    action([\App\Http\Controllers\ReportController::class, 'activityLog']),
                                    __('lang_v1.activity_log'),
                                    ['icon' => '', 'active' => request()->segment(2) == 'activity-log']
                                );
                            }
                        $hasMore = config('constants.show_report_607') == true
                            || (auth()->user()->can('stock_report.view') && session('business.enable_product_expiry') == 1)
                            || (auth()->user()->can('stock_report.view') && session('business.enable_lot_number') == 1)
                            || (auth()->user()->can('tax_report.view') && ! empty(config('constants.enable_gst_report_india')))
                            || (auth()->user()->can('purchase_n_sell_report.view') && in_array('tables', $enabled_modules))
                            || (auth()->user()->can('sales_representative.view') && in_array('service_staff', $enabled_modules));

                        if ($hasMore) {
                            $sub->header(__('report.menu_group_other'));
                        }
                        if (config('constants.show_report_607') == true) {
                            $sub->url(
                                action([\App\Http\Controllers\ReportController::class, 'saleReport']),
                                'Report 607 (' . __('business.sale') . ')',
                                ['icon' => '', 'active' => request()->segment(2) == 'sale-report']
                            );
                        }
                        if (auth()->user()->can('stock_report.view') && session('business.enable_product_expiry') == 1) {
                            $sub->url(
                                action([\App\Http\Controllers\ReportController::class, 'getStockExpiryReport']),
                                __('report.stock_expiry_report'),
                                ['icon' => '', 'active' => request()->segment(2) == 'stock-expiry']
                            );
                        }
                        if (auth()->user()->can('stock_report.view') && session('business.enable_lot_number') == 1) {
                            $sub->url(
                                action([\App\Http\Controllers\ReportController::class, 'getLotReport']),
                                __('lang_v1.lot_report'),
                                ['icon' => '', 'active' => request()->segment(2) == 'lot-report']
                            );
                        }
                        if (auth()->user()->can('tax_report.view') && ! empty(config('constants.enable_gst_report_india'))) {
                            $sub->url(
                                action([\App\Http\Controllers\ReportController::class, 'gstSalesReport']),
                                __('lang_v1.gst_sales_report'),
                                ['icon' => '', 'active' => request()->segment(2) == 'gst-sales-report']
                            );
                            $sub->url(
                                action([\App\Http\Controllers\ReportController::class, 'gstPurchaseReport']),
                                __('lang_v1.gst_purchase_report'),
                                ['icon' => '', 'active' => request()->segment(2) == 'gst-purchase-report']
                            );
                        }
                        if (auth()->user()->can('purchase_n_sell_report.view') && in_array('tables', $enabled_modules)) {
                            $sub->url(
                                action([\App\Http\Controllers\ReportController::class, 'getTableReport']),
                                __('restaurant.table_report'),
                                ['icon' => '', 'active' => request()->segment(2) == 'table-report']
                            );
                        }
                        if (auth()->user()->can('sales_representative.view') && in_array('service_staff', $enabled_modules)) {
                            $sub->url(
                                action([\App\Http\Controllers\ReportController::class, 'getServiceStaffReport']),
                                __('restaurant.service_staff_report'),
                                ['icon' => '', 'active' => request()->segment(2) == 'service-staff-report']
                            );
                        }
                    },
                    ['icon' => '<svg aria-hidden="true" class="tw-size-5 tw-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19V5"/><path d="M4 19h16"/><path d="M8 16v-5"/><path d="M12 16V8"/><path d="M16 16v-8"/></svg>', 'id' => 'tour_step8']
                )->order(90);
            }

            //Backup menu
            if (auth()->user()->can('backup')) {
                $menu->url(action([\App\Http\Controllers\BackUpController::class, 'index']), __('lang_v1.backup'), ['icon' => '<svg aria-hidden="true" class="tw-size-5 tw-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                <path d="M12 18.004h-5.343c-2.572 -.004 -4.657 -2.011 -4.657 -4.487c0 -2.475 2.085 -4.482 4.657 -4.482c.393 -1.762 1.794 -3.2 3.675 -3.773c1.88 -.572 3.956 -.193 5.444 1c1.488 1.19 2.162 3.007 1.77 4.769h.99c1.38 0 2.57 .811 3.128 1.986"></path>
                <path d="M19 22v-6"></path>
                <path d="M22 19l-3 -3l-3 3"></path>
              </svg>', 'active' => request()->segment(1) == 'backup'])->order(140);
            }

            //Modules menu
            if (auth()->user()->can('manage_modules')) {
                $menu->url(action([\App\Http\Controllers\ManageModulesController::class, 'index']), __('lang_v1.modules'), ['icon' => '<svg aria-hidden="true" class="tw-size-5 tw-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
              <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
              <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
              <path d="M12 4l-8 4l8 4l8 -4l-8 -4"></path>
              <path d="M4 12l8 4l8 -4"></path>
              <path d="M4 16l8 4l8 -4"></path>
            </svg>', 'active' => request()->segment(1) == 'manage-modules'])->order(150);
            }

            //Booking menu
            if (in_array('booking', $enabled_modules) && (auth()->user()->can('crud_all_bookings') || auth()->user()->can('crud_own_bookings'))) {
                $menu->url(action([\App\Http\Controllers\Restaurant\BookingController::class, 'index']), __('restaurant.bookings'), ['icon' => '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-check"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M11.5 21h-5.5a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v6" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /><path d="M15 19l2 2l4 -4" /></svg>', 'active' => request()->segment(1) == 'bookings'])->order(100);
            }

            //Kitchen menu
            if (in_array('kitchen', $enabled_modules)) {
                $menu->url(action([\App\Http\Controllers\Restaurant\KitchenController::class, 'index']), __('restaurant.kitchen'), ['icon' => '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-flame"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12c2 -2.96 0 -7 -1 -8c0 3.038 -1.773 4.741 -3 6c-1.226 1.26 -2 3.24 -2 5a6 6 0 1 0 12 0c0 -1.532 -1.056 -3.94 -2 -5c-1.786 3 -2.791 3 -4 2z" /></svg>', 'active' => request()->segment(1) == 'modules' && request()->segment(2) == 'kitchen'])->order(110);
            }

            //Service Staff menu
            if (in_array('service_staff', $enabled_modules)) {
                $menu->url(action([\App\Http\Controllers\Restaurant\OrderController::class, 'index']), __('restaurant.orders'), ['icon' => '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="18"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-baseline-density-medium"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h16" /><path d="M4 12h16" /><path d="M4 4h16" /></svg>', 'active' => request()->segment(1) == 'modules' && request()->segment(2) == 'orders'])->order(120);
            }

            //Notification template menu
            if (auth()->user()->can('send_notifications')) {
                $menu->url(action([\App\Http\Controllers\NotificationTemplateController::class, 'index']), __('lang_v1.notification_templates'), ['icon' => '<svg aria-hidden="true" class="tw-size-5 tw-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16v12H4z"/><path d="m4 7 8 6 8-6"/></svg>', 'active' => request()->segment(1) == 'notification-templates'])->order(130);
            }

            //Settings Dropdown
            if (auth()->user()->can('business_settings.access') ||
                auth()->user()->can('barcode_settings.access') ||
                auth()->user()->can('invoice_settings.access') ||
                auth()->user()->can('tax_rate.view') ||
                auth()->user()->can('tax_rate.create') ||
                auth()->user()->can('access_package_subscriptions') ||
                auth()->user()->can('user.view') ||
                auth()->user()->can('roles.view') ||
                auth()->user()->can('ai.access') ||
                auth()->user()->can('ai.chat') ||
                auth()->user()->can('ai.settings')) {
                $menu->dropdown(
                    __('business.settings'),
                    function ($sub) use ($enabled_modules) {
                        if (auth()->user()->can('user.view') || auth()->user()->can('roles.view')) {
                            $sub->header(__('lang_v1.menu_users_access'));
                        }
                        if (auth()->user()->can('user.view')) {
                            $userAttrs = [
                                'icon' => '',
                                'active' => request()->segment(1) == 'users',
                            ];
                            if (auth()->user()->can('user.create')) {
                                $userAttrs = $this->withQuickAddUrl(
                                    $userAttrs,
                                    action([\App\Http\Controllers\ManageUserController::class, 'create']),
                                    __('user.add_user')
                                );
                                $userAttrs['active'] = request()->segment(1) == 'users' && in_array(request()->segment(2), [null, 'create'], true);
                            }
                            $sub->url(
                                action([\App\Http\Controllers\ManageUserController::class, 'index']),
                                __('user.users'),
                                $userAttrs
                            );
                        }
                        if (auth()->user()->can('roles.view')) {
                            $roleAttrs = [
                                'icon' => '',
                                'active' => request()->segment(1) == 'roles',
                            ];
                            if (auth()->user()->can('roles.create')) {
                                $roleAttrs = $this->withQuickAddUrl(
                                    $roleAttrs,
                                    action([\App\Http\Controllers\RoleController::class, 'create']),
                                    __('user.add_role')
                                );
                                $roleAttrs['active'] = request()->segment(1) == 'roles' && in_array(request()->segment(2), [null, 'create'], true);
                            }
                            $sub->url(
                                action([\App\Http\Controllers\RoleController::class, 'index']),
                                __('user.roles'),
                                $roleAttrs
                            );
                        }
                        if (auth()->user()->can('business_settings.access')) {
                            $sub->header(__('lang_v1.menu_business'));
                            $sub->url(
                                action([\App\Http\Controllers\BusinessController::class, 'getBusinessSettings']),
                                __('business.business_settings'),
                                ['icon' => '', 'active' => request()->segment(1) == 'business', 'id' => 'tour_step2']
                            );
                            $locationAttrs = [
                                'icon' => '',
                                'active' => request()->segment(1) == 'business-location',
                            ];
                            $locationAttrs = $this->withQuickAddModal(
                                $locationAttrs,
                                action([\App\Http\Controllers\BusinessLocationController::class, 'create']),
                                __('business.add_business_location')
                            );
                            $sub->url(
                                action([\App\Http\Controllers\BusinessLocationController::class, 'index']),
                                __('business.business_locations'),
                                $locationAttrs
                            );
                        }
                        if (auth()->user()->can('invoice_settings.access') || auth()->user()->can('barcode_settings.access') || auth()->user()->can('access_printers')) {
                            $sub->header(__('lang_v1.menu_documents_print'));
                        }
                        if (auth()->user()->can('invoice_settings.access')) {
                            $invoiceAttrs = [
                                'icon' => '',
                                'active' => in_array(request()->segment(1), ['invoice-schemes', 'invoice-layouts']),
                            ];
                            $invoiceAttrs = $this->withQuickAddModal(
                                $invoiceAttrs,
                                action([\App\Http\Controllers\InvoiceSchemeController::class, 'create']),
                                __('invoice.add_invoice')
                            );
                            $sub->url(
                                action([\App\Http\Controllers\InvoiceSchemeController::class, 'index']),
                                __('invoice.invoice_settings'),
                                $invoiceAttrs
                            );
                        }
                        if (auth()->user()->can('barcode_settings.access')) {
                            $barcodeAttrs = [
                                'icon' => '',
                                'active' => request()->segment(1) == 'barcodes',
                            ];
                            $barcodeAttrs = $this->withQuickAddUrl(
                                $barcodeAttrs,
                                action([\App\Http\Controllers\BarcodeController::class, 'create']),
                                __('barcode.add_barcode_setting')
                            );
                            $barcodeAttrs['active'] = request()->segment(1) == 'barcodes' && in_array(request()->segment(2), [null, 'create'], true);
                            $sub->url(
                                action([\App\Http\Controllers\BarcodeController::class, 'index']),
                                __('barcode.barcode_settings'),
                                $barcodeAttrs
                            );
                        }
                        if (auth()->user()->can('access_printers')) {
                            $printerAttrs = [
                                'icon' => '',
                                'active' => request()->segment(1) == 'printers',
                            ];
                            $printerAttrs = $this->withQuickAddUrl(
                                $printerAttrs,
                                action([\App\Http\Controllers\PrinterController::class, 'create']),
                                __('printer.add_printer')
                            );
                            $printerAttrs['active'] = request()->segment(1) == 'printers' && in_array(request()->segment(2), [null, 'create'], true);
                            $sub->url(
                                action([\App\Http\Controllers\PrinterController::class, 'index']),
                                __('printer.receipt_printers'),
                                $printerAttrs
                            );
                        }

                        if (auth()->user()->can('tax_rate.view') || auth()->user()->can('tax_rate.create')) {
                            $sub->header(__('lang_v1.menu_taxes'));
                            $taxAttrs = [
                                'icon' => '',
                                'active' => request()->segment(1) == 'tax-rates',
                            ];
                            if (auth()->user()->can('tax_rate.create')) {
                                $taxAttrs = $this->withQuickAddModal(
                                    $taxAttrs,
                                    action([\App\Http\Controllers\TaxRateController::class, 'create']),
                                    __('tax_rate.add_tax_rate')
                                );
                            }
                            $sub->url(
                                action([\App\Http\Controllers\TaxRateController::class, 'index']),
                                __('tax_rate.tax_rates'),
                                $taxAttrs
                            );
                        }

                        $hasRestaurant = (in_array('tables', $enabled_modules) && auth()->user()->can('access_tables'))
                            || (in_array('modifiers', $enabled_modules) && (auth()->user()->can('product.view') || auth()->user()->can('product.create')))
                            || (in_array('types_of_service', $enabled_modules) && auth()->user()->can('access_types_of_service'));
                        if ($hasRestaurant) {
                            $sub->header(__('lang_v1.menu_restaurant'));
                        }

                        if (in_array('tables', $enabled_modules) && auth()->user()->can('access_tables')) {
                            $tableAttrs = [
                                'icon' => '',
                                'active' => request()->segment(1) == 'modules' && request()->segment(2) == 'tables',
                            ];
                            $tableAttrs = $this->withQuickAddModal(
                                $tableAttrs,
                                action([\App\Http\Controllers\Restaurant\TableController::class, 'create']),
                                __('restaurant.add_table')
                            );
                            $sub->url(
                                action([\App\Http\Controllers\Restaurant\TableController::class, 'index']),
                                __('restaurant.tables'),
                                $tableAttrs
                            );
                        }

                        if (in_array('modifiers', $enabled_modules) && (auth()->user()->can('product.view') || auth()->user()->can('product.create'))) {
                            $modifierAttrs = [
                                'icon' => '',
                                'active' => request()->segment(1) == 'modules' && request()->segment(2) == 'modifiers',
                            ];
                            if (auth()->user()->can('product.create')) {
                                $modifierAttrs = $this->withQuickAddModal(
                                    $modifierAttrs,
                                    action([\App\Http\Controllers\Restaurant\ModifierSetsController::class, 'create']),
                                    __('restaurant.add_modifier')
                                );
                            }
                            $sub->url(
                                action([\App\Http\Controllers\Restaurant\ModifierSetsController::class, 'index']),
                                __('restaurant.modifiers'),
                                $modifierAttrs
                            );
                        }

                        if (in_array('types_of_service', $enabled_modules) && auth()->user()->can('access_types_of_service')) {
                            $tosAttrs = [
                                'icon' => '',
                                'active' => request()->segment(1) == 'types-of-service',
                            ];
                            $tosAttrs = $this->withQuickAddModal(
                                $tosAttrs,
                                action([\App\Http\Controllers\TypesOfServiceController::class, 'create']),
                                __('lang_v1.add_type_of_service')
                            );
                            $sub->url(
                                action([\App\Http\Controllers\TypesOfServiceController::class, 'index']),
                                __('lang_v1.types_of_service'),
                                $tosAttrs
                            );
                        }
                    },
                    ['icon' => '<svg aria-hidden="true" class="tw-size-5 tw-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9c0 .7.4 1.2 1 1.5H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1Z"/></svg>', 'id' => 'tour_step3']
                )->order(160);
            }
        });

        //Add menus from modules
        $moduleUtil = new ModuleUtil;
        $moduleUtil->getModuleData('modifyAdminMenu');

        return $next($request);
    }
}
