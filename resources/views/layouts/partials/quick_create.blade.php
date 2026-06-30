@php
    $pos_settings = ! empty(session('business.pos_settings'))
        ? json_decode(session('business.pos_settings'), true)
        : [];
    $pos_settings = is_array($pos_settings) ? $pos_settings : [];
    $common_settings = ! empty(session('business.common_settings'))
        ? session('business.common_settings')
        : [];
    $enable_sales_order = ! empty($pos_settings['enable_sales_order']);
    $enable_purchase_order = ! empty($common_settings['enable_purchase_order']);

    $has_general = auth()->user()->can('product.create');
    $has_inventory = auth()->user()->can('stock_adjustment.create')
        || (in_array('stock_transfers', $enabled_modules) && auth()->user()->can('stock_transfer.create'))
        || auth()->user()->hasAnyPermission(['access_shipping', 'access_own_shipping', 'access_commission_agent_shipping']);
    $has_sales = auth()->user()->can('customer.create')
        || auth()->user()->can('direct_sell.access')
        || auth()->user()->can('sell.create')
        || auth()->user()->can('so.create')
        || auth()->user()->can('access_sell_return')
        || auth()->user()->can('access_own_sell_return');
    $has_purchases = auth()->user()->can('supplier.create')
        || auth()->user()->can('purchase.create')
        || auth()->user()->can('purchase.update')
        || auth()->user()->can('purchase_order.create')
        || auth()->user()->can('supplier.view')
        || auth()->user()->can('supplier.view_own');
@endphp

@if ($has_general || $has_inventory || $has_sales || $has_purchases)
    <div class="zoho-quick-create" id="zoho_quick_create">
        <button type="button"
            class="zoho-topbar__btn zoho-topbar__btn--primary zoho-quick-create__trigger"
            id="zoho_quick_create_btn"
            aria-expanded="false"
            aria-controls="zoho_quick_create_panel"
            title="@lang('lang_v1.quick_create')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <path d="M12 5v14M5 12h14"/>
            </svg>
        </button>

        <div class="zoho-quick-create__panel" id="zoho_quick_create_panel" hidden>
            <div class="zoho-quick-create__grid">
                @if ($has_general)
                    <div class="zoho-quick-create__col">
                        <div class="zoho-quick-create__col-head">
                            <svg class="zoho-quick-create__col-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                                <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
                            </svg>
                            <span>@lang('lang_v1.quick_create_general')</span>
                        </div>
                        <ul class="zoho-quick-create__list">
                            @can('product.create')
                                <li>
                                    <a href="{{ action([\App\Http\Controllers\ProductController::class, 'create']) }}"
                                        class="zoho-quick-create__link">
                                        <span class="zoho-quick-create__plus" aria-hidden="true">+</span>
                                        @lang('product.add_product')
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ action([\App\Http\Controllers\ProductController::class, 'create'], ['type' => 'combo']) }}"
                                        class="zoho-quick-create__link">
                                        <span class="zoho-quick-create__plus" aria-hidden="true">+</span>
                                        @lang('lang_v1.combo')
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </div>
                @endif

                @if ($has_inventory)
                    <div class="zoho-quick-create__col">
                        <div class="zoho-quick-create__col-head">
                            <svg class="zoho-quick-create__col-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                                <path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                                <path d="M17 17h-11v-14h-2"/><path d="M6 5l14 1l-1 7h-13"/>
                            </svg>
                            <span>@lang('lang_v1.menu_inventory')</span>
                        </div>
                        <ul class="zoho-quick-create__list">
                            @can('stock_adjustment.create')
                                <li>
                                    <a href="{{ action([\App\Http\Controllers\StockAdjustmentController::class, 'create']) }}"
                                        class="zoho-quick-create__link">
                                        <span class="zoho-quick-create__plus" aria-hidden="true">+</span>
                                        @lang('lang_v1.quick_create_stock_count')
                                    </a>
                                </li>
                            @endcan
                            @if (auth()->user()->hasAnyPermission(['access_shipping', 'access_own_shipping', 'access_commission_agent_shipping']))
                                <li>
                                    <a href="{{ action([\App\Http\Controllers\SellController::class, 'shipments']) }}"
                                        class="zoho-quick-create__link">
                                        <span class="zoho-quick-create__plus" aria-hidden="true">+</span>
                                        @lang('lang_v1.quick_create_shipment')
                                    </a>
                                </li>
                            @endif
                            @if (in_array('stock_transfers', $enabled_modules) && auth()->user()->can('stock_transfer.create'))
                                <li>
                                    <a href="{{ action([\App\Http\Controllers\StockTransferController::class, 'create']) }}"
                                        class="zoho-quick-create__link">
                                        <span class="zoho-quick-create__plus" aria-hidden="true">+</span>
                                        @lang('lang_v1.add_stock_transfer')
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                @endif

                @if ($has_sales)
                    <div class="zoho-quick-create__col">
                        <div class="zoho-quick-create__col-head">
                            <svg class="zoho-quick-create__col-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                                <path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                                <path d="M17 17h-11v-14h-2"/><path d="M6 5l14 1l-1 7h-13"/>
                            </svg>
                            <span>@lang('lang_v1.menu_sales')</span>
                        </div>
                        <ul class="zoho-quick-create__list">
                            @can('customer.create')
                                <li>
                                    <a href="#"
                                        class="zoho-quick-create__link btn-modal"
                                        data-href="{{ action([\App\Http\Controllers\ContactController::class, 'create'], ['type' => 'customer']) }}"
                                        data-container="#global_contact_modal">
                                        <span class="zoho-quick-create__plus" aria-hidden="true">+</span>
                                        @lang('report.customer')
                                    </a>
                                </li>
                            @endcan
                            @if (in_array('add_sale', $enabled_modules) && auth()->user()->can('direct_sell.access'))
                                <li>
                                    <a href="{{ action([\App\Http\Controllers\SellController::class, 'create']) }}"
                                        class="zoho-quick-create__link">
                                        <span class="zoho-quick-create__plus" aria-hidden="true">+</span>
                                        @lang('lang_v1.quick_create_invoices')
                                    </a>
                                </li>
                            @endif
                            @if (in_array('pos_sale', $enabled_modules) && auth()->user()->can('sell.create'))
                                <li>
                                    <a href="{{ action([\App\Http\Controllers\SellPosController::class, 'create']) }}"
                                        class="zoho-quick-create__link">
                                        <span class="zoho-quick-create__plus" aria-hidden="true">+</span>
                                        @lang('lang_v1.quick_create_sales_receipts')
                                    </a>
                                </li>
                            @endif
                            @if ($enable_sales_order && auth()->user()->can('so.create'))
                                <li>
                                    <a href="{{ action([\App\Http\Controllers\SellController::class, 'create'], ['sale_type' => 'sales_order']) }}"
                                        class="zoho-quick-create__link">
                                        <span class="zoho-quick-create__plus" aria-hidden="true">+</span>
                                        @lang('lang_v1.sales_order')
                                    </a>
                                </li>
                            @endif
                            @if (auth()->user()->can('customer.view') || auth()->user()->can('customer.view_own'))
                                <li>
                                    <a href="{{ action([\App\Http\Controllers\ContactController::class, 'index'], ['type' => 'customer']) }}"
                                        class="zoho-quick-create__link">
                                        <span class="zoho-quick-create__plus" aria-hidden="true">+</span>
                                        @lang('lang_v1.quick_create_customer_payment')
                                    </a>
                                </li>
                            @endif
                            @if (auth()->user()->can('access_sell_return') || auth()->user()->can('access_own_sell_return'))
                                <li>
                                    <a href="{{ action([\App\Http\Controllers\SellReturnController::class, 'index']) }}"
                                        class="zoho-quick-create__link">
                                        <span class="zoho-quick-create__plus" aria-hidden="true">+</span>
                                        @lang('lang_v1.quick_create_credit_notes')
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                @endif

                @if ($has_purchases)
                    <div class="zoho-quick-create__col">
                        <div class="zoho-quick-create__col-head">
                            <svg class="zoho-quick-create__col-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                                <path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
                                <path d="M17 17h-11v-14h-2"/><path d="M6 5l14 1l-1 7h-13"/>
                            </svg>
                            <span>@lang('lang_v1.menu_purchases')</span>
                        </div>
                        <ul class="zoho-quick-create__list">
                            @can('supplier.create')
                                <li>
                                    <a href="#"
                                        class="zoho-quick-create__link btn-modal"
                                        data-href="{{ action([\App\Http\Controllers\ContactController::class, 'create'], ['type' => 'supplier']) }}"
                                        data-container="#global_contact_modal">
                                        <span class="zoho-quick-create__plus" aria-hidden="true">+</span>
                                        @lang('lang_v1.menu_vendors')
                                    </a>
                                </li>
                            @endcan
                            @can('purchase.create')
                                <li>
                                    <a href="{{ action([\App\Http\Controllers\PurchaseController::class, 'create']) }}"
                                        class="zoho-quick-create__link">
                                        <span class="zoho-quick-create__plus" aria-hidden="true">+</span>
                                        @lang('lang_v1.quick_create_bill')
                                    </a>
                                </li>
                            @endcan
                            @if ($enable_purchase_order && auth()->user()->can('purchase_order.create'))
                                <li>
                                    <a href="{{ action([\App\Http\Controllers\PurchaseOrderController::class, 'create']) }}"
                                        class="zoho-quick-create__link">
                                        <span class="zoho-quick-create__plus" aria-hidden="true">+</span>
                                        @lang('lang_v1.purchase_order')
                                    </a>
                                </li>
                            @endif
                            @can('purchase.create')
                                <li>
                                    <a href="{{ action([\App\Http\Controllers\PurchaseController::class, 'create']) }}"
                                        class="zoho-quick-create__link">
                                        <span class="zoho-quick-create__plus" aria-hidden="true">+</span>
                                        @lang('lang_v1.quick_create_purchase_receive')
                                    </a>
                                </li>
                            @endcan
                            @if (auth()->user()->can('supplier.view') || auth()->user()->can('supplier.view_own'))
                                <li>
                                    <a href="{{ action([\App\Http\Controllers\ContactController::class, 'index'], ['type' => 'supplier']) }}"
                                        class="zoho-quick-create__link">
                                        <span class="zoho-quick-create__plus" aria-hidden="true">+</span>
                                        @lang('lang_v1.quick_create_vendor_payment')
                                    </a>
                                </li>
                            @endif
                            @can('purchase.update')
                                <li>
                                    <a href="{{ action([\App\Http\Controllers\CombinedPurchaseReturnController::class, 'create']) }}"
                                        class="zoho-quick-create__link">
                                        <span class="zoho-quick-create__plus" aria-hidden="true">+</span>
                                        @lang('lang_v1.quick_create_vendor_credit')
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif
