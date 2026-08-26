@extends('sale.layout')
@section('title', !empty($edit_order) ? 'Edit order' : 'Create order')
@section('header', !empty($edit_order) ? 'Edit order' : 'Create order')
@section('content')
<form method="POST" action="{{ !empty($edit_order) ? route('sale.orders.update', $edit_order->id) : route('sale.orders.store') }}" id="saleOrderForm" class="sale-create-form">
    @csrf
    @if(!empty($edit_order))
        @method('PUT')
    @endif
    <input type="hidden" name="contact_id" id="contact_id" value="{{ old('contact_id', $default_customer['id'] ?? '') }}" required>
    <input type="hidden" name="location_id" id="location_id" value="{{ old('location_id', $default_location) }}" required>
    <input type="hidden" name="shipping_status" value="ordered">
    {{-- Existing project modes: New Order = sales_order, Estimate = quotation, Back Order = sales_order --}}
    <input type="hidden" name="order_mode" id="order_mode" value="{{ old('order_mode', 'new_order') }}">
    <div id="productsJson" class="hidden"></div>

    {{-- STEP 0: Select customer (layout like app — uses existing customers API) --}}
    @if(empty($edit_order))
    <div id="stepCustomer" class="sale-pick-customer">
        <div class="sale-pick-customer__head">
            <h1 class="sale-pick-customer__title">Create Order</h1>
            <p class="sale-pick-customer__sub">Select Customer</p>
        </div>

        <div class="sale-pick-modes" role="radiogroup" aria-label="Order type">
            <label class="sale-pick-mode">
                <input type="radio" name="order_mode_ui" value="new_order" checked>
                <span>New Order</span>
            </label>
            <label class="sale-pick-mode">
                <input type="radio" name="order_mode_ui" value="estimate">
                <span>Estimate</span>
            </label>
            <label class="sale-pick-mode">
                <input type="radio" name="order_mode_ui" value="back_order">
                <span>Back Order</span>
            </label>
        </div>

        <div class="sale-pick-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            <input type="search" id="customerSearch" class="sale-pick-search__input" placeholder="Search Customer Name" autocomplete="off">
        </div>

        <div id="customerResults" class="sale-pick-list"></div>
    </div>
    @endif

    {{-- STEP 1: Build order (after customer) — Pickup/Delivery/Shipping + SKU/scan + catalog --}}
    <div id="stepCart" class="sale-order-build" @if(empty($edit_order)) hidden @endif>
        <div class="sale-order-build__bar">
            <button type="button" id="backToCustomerBtn" class="sale-order-build__iconbtn" aria-label="Back" @if(!empty($edit_order)) hidden @endif>
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
            <div class="sale-order-build__titles min-w-0">
                <div class="sale-order-build__title">Create Order</div>
                <div class="sale-order-build__sub truncate" id="orderCustomerName">{{ $default_customer['text'] ?? ($edit_order->contact->supplier_business_name ?? $edit_order->contact->name ?? '') }}</div>
            </div>
            <button type="button" class="sale-order-build__submit" id="goShippingBtn">SUBMIT</button>
        </div>

        <div class="sale-order-build__body">
            <div class="sale-order-build__row">
                <span class="sale-order-build__label">Use last purchased quantities?</span>
                <label class="sale-toggle" title="Fill order list from this customer’s last order">
                    <input type="checkbox" id="useLastQtyToggle">
                    <span class="sale-toggle__track"></span>
                </label>
            </div>
            <div id="lastQtyStatus" class="sale-last-qty-status" hidden></div>

            {{-- Existing system fulfillment → shipping_details / shipping_method --}}
            <div class="sale-fulfill" role="radiogroup" aria-label="Fulfillment">
                <label class="sale-fulfill__opt">
                    <input type="radio" name="fulfillment_ui" value="Pickup">
                    <span class="sale-fulfill__dot"></span>
                    <span>Pickup</span>
                </label>
                <label class="sale-fulfill__opt">
                    <input type="radio" name="fulfillment_ui" value="Delivery" checked>
                    <span class="sale-fulfill__dot"></span>
                    <span>Delivery</span>
                </label>
                <label class="sale-fulfill__opt">
                    <input type="radio" name="fulfillment_ui" value="Shipping">
                    <span class="sale-fulfill__dot"></span>
                    <span>Shipping</span>
                </label>
            </div>
            <input type="hidden" name="shipping_method" id="shipping_method" value="{{ old('shipping_method', $edit_order->shipping_method ?? 'Delivery') }}">

            <div class="sale-sku-block">
                <div class="sale-sku-row">
                    <div class="sale-sku-modes" role="group" aria-label="Search mode">
                        <button type="button" class="sale-sku-mode is-active" id="skuModeScan" title="Scan / SKU" aria-pressed="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2"/><path d="M7 8h1M10 8h2M14 8h3M7 12h3M12 12h1M15 12h2M7 16h2M11 16h4"/></svg>
                        </button>
                        <button type="button" class="sale-sku-mode" id="skuModeText" title="Search by name" aria-pressed="false">
                            <span class="font-extrabold text-sm leading-none">T</span>
                        </button>
                    </div>
                    <div class="sale-sku-search">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                        <input type="search" id="productSearch" class="sale-sku-search__input" placeholder="Enter SKU" autocomplete="off" enterkeyhint="search">
                    </div>
                </div>
                <div id="productResults" class="sale-prod-results hidden"></div>
            </div>

            <div class="sale-order-list-head">
                <strong>Order List</strong>
                <button type="button" id="catalogOpenBtn" class="sale-order-list-head__link">Product Catalog</button>
            </div>

            <div class="sale-cart-scroll" id="cartScroll">
                <div id="cartEmpty" class="sale-cart-empty">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-slate-300 mx-auto mb-2"><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.4a2 2 0 0 0 2-1.5L21 8H7"/></svg>
                    <div class="text-sm font-semibold text-slate-400">Order list is empty</div>
                    <div class="text-xs text-slate-400 mt-1">Scan SKU or open Product Catalog</div>
                </div>
                <div id="cartLines" class="sale-cart-lines"></div>
            </div>

            <div class="sale-order-build__total">
                <span>Total</span>
                <strong id="cartTotal" class="tabular-nums">$0.00</strong>
            </div>
        </div>

        <input type="hidden" name="payment_type" value="due">
        {{-- Keep selected customer chip for change (edit / change customer) --}}
        <button type="button" id="customerSelected" class="hidden" aria-hidden="true">
            <span id="customerLabel"></span>
        </button>
        <div id="customerSearchWrap" class="hidden">
            @if(!empty($edit_order))
                <input type="search" id="customerSearchEdit" class="sale-input" placeholder="Search customer name / mobile" autocomplete="off">
                <div id="customerResultsEdit" class="mt-2 border border-sale-line rounded-xl bg-white max-h-40 overflow-auto hidden"></div>
            @endif
        </div>
    </div>

    {{-- STEP 2: Shipping (shown after Order click) --}}
    <div id="stepShipping" class="sale-ship-flow" hidden>
        <button type="button" id="backToCartBtn" class="inline-flex items-center gap-1.5 text-sm font-bold text-sale mb-3">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M15 18l-6-6 6-6"/></svg>
            Back to cart
        </button>

        <div class="sale-card space-y-3 mb-3">
            <div class="sale-sec-title !mb-1">
                <span class="sale-sec-title__ico">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 21s7-4.5 7-11a7 7 0 1 0-14 0c0 6.5 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                </span>
                Shipping
            </div>
            <div class="flex justify-between text-sm font-bold mb-1">
                <span class="text-slate-500">Order total</span>
                <span id="shipTotal" class="tabular-nums text-sale">$0.00</span>
            </div>
            <div>
                <label class="text-xs font-bold text-slate-500 mb-1.5 block">Shipping details:*</label>
                <textarea name="shipping_details" id="shipping_details" rows="4" class="sale-input" required placeholder="Shipping details">{{ old('shipping_details', $edit_order->shipping_details ?? '') }}</textarea>
            </div>
            <div>
                <label class="text-xs font-bold text-slate-500 mb-1.5 block">Shipping address:</label>
                <textarea name="shipping_address" id="shipping_address" rows="4" class="sale-input" required placeholder="Shipping address">{{ old('shipping_address', $edit_order->shipping_address ?? '') }}</textarea>
            </div>
            <div>
                <label class="text-xs font-bold text-slate-500 mb-1.5 block">Note (optional)</label>
                <textarea name="sale_note" rows="2" class="sale-input" placeholder="Order note">{{ old('sale_note', $edit_order->additional_notes ?? '') }}</textarea>
            </div>
        </div>

        <div class="sale-create-bar">
            <button type="submit" class="sale-btn !w-full inline-flex items-center justify-center gap-2" id="submitOrderBtn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                {{ !empty($edit_order) ? 'Save order' : 'Create order' }}
            </button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<div id="catalogModal" class="sale-catalog" hidden>
    <div class="sale-catalog__panel">
        <div class="sale-catalog__head">
            <button type="button" id="catalogBackBtn" class="sale-catalog__back hidden" aria-label="Back">‹</button>
            <div class="font-extrabold text-base truncate flex-1" id="catalogTitle">Catalog</div>
            <button type="button" id="catalogCloseBtn" class="sale-catalog__close" aria-label="Close">×</button>
        </div>
        <div id="catalogBody" class="sale-catalog__body"></div>
    </div>
</div>
<script>
(function () {
    const cart = [];
    const customerSearch = document.getElementById('customerSearch');
    const customerResults = document.getElementById('customerResults');
    const customerLabel = document.getElementById('customerLabel');
    const customerSelected = document.getElementById('customerSelected');
    const customerSearchWrap = document.getElementById('customerSearchWrap');
    const contactId = document.getElementById('contact_id');
    const productSearch = document.getElementById('productSearch');
    const productResults = document.getElementById('productResults');
    const cartLines = document.getElementById('cartLines');
    const cartEmpty = document.getElementById('cartEmpty');
    const cartTotal = document.getElementById('cartTotal');
    const shipTotal = document.getElementById('shipTotal');
    const locationId = document.getElementById('location_id');
    const shippingAddress = document.getElementById('shipping_address');
    const shippingDetails = document.getElementById('shipping_details');
    const form = document.getElementById('saleOrderForm');
    const productsJson = document.getElementById('productsJson');
    const stepCart = document.getElementById('stepCart');
    const stepShipping = document.getElementById('stepShipping');
    let custTimer = null, prodTimer = null;
    let savedShipAddr = '';
    let lastCustomer = { id: '', text: '', shipAddr: '' };

    const catalogModal = document.getElementById('catalogModal');
    const catalogBody = document.getElementById('catalogBody');
    const catalogTitle = document.getElementById('catalogTitle');
    const catalogBackBtn = document.getElementById('catalogBackBtn');
    let catalogTree = null;
    let catalogStack = [];

    function money(n) { return '$' + (Number(n) || 0).toFixed(2); }

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function cartSum() {
        return cart.reduce((s, l) => s + (l.quantity * l.unit_price), 0);
    }

    function addToCart(r, silent, skipRender) {
        if (!r || !r.variation_id) return;
        const vid = Number(r.variation_id);
        const existingIdx = cart.findIndex(c => Number(c.variation_id) === vid);
        if (existingIdx >= 0) {
            cart[existingIdx].quantity = +cart[existingIdx].quantity + (Number(r.quantity) || 1);
            if (typeof r.allow_decimal !== 'undefined') {
                cart[existingIdx].allow_decimal = !!Number(r.allow_decimal);
            }
            if (r.transaction_sell_lines_id && !cart[existingIdx].transaction_sell_lines_id) {
                cart[existingIdx].transaction_sell_lines_id = r.transaction_sell_lines_id;
            }
            cart.unshift(cart.splice(existingIdx, 1)[0]);
        } else {
            cart.unshift({
                product_id: Number(r.product_id),
                variation_id: vid,
                transaction_sell_lines_id: r.transaction_sell_lines_id || null,
                name: r.name,
                unit_price: Number(r.price) || 0,
                quantity: Number(r.quantity) || 1,
                enable_stock: r.enable_stock,
                product_type: r.product_type,
                allow_decimal: !!Number(r.allow_decimal),
            });
        }
        if (!skipRender) renderCart();
        if (!silent) showAddedMsg();
    }

    function showAddedMsg() {
        let el = document.getElementById('saleAddedMsg');
        if (!el) {
            el = document.createElement('div');
            el.id = 'saleAddedMsg';
            el.className = 'sale-added-msg';
            document.body.appendChild(el);
        }
        el.textContent = 'Added';
        el.hidden = false;
        clearTimeout(showAddedMsg._t);
        showAddedMsg._t = setTimeout(() => { el.hidden = true; }, 1400);
    }

    function cartQty(variationId) {
        const line = cart.find(c => Number(c.variation_id) === Number(variationId));
        if (!line) return 0;
        return formatQty(line);
    }

    function formatQty(line) {
        const q = Number(line.quantity) || 0;
        if (line.allow_decimal) {
            return String(Math.round(q * 100) / 100);
        }
        return String(Math.max(0, Math.round(q)));
    }

    function changeQty(i, delta) {
        const line = cart[i];
        if (!line) return;
        if (delta < 0 && Number(line.quantity) <= 1) return;
        let next = Number(line.quantity) + delta;
        if (!line.allow_decimal) {
            next = Math.round(next);
            if (next < 1) return;
        } else {
            next = Math.round(next * 100) / 100;
            if (next < 1) return;
        }
        line.quantity = next;
        renderCart();
    }

    function setQtyFromInput(i, raw) {
        const line = cart[i];
        if (!line) return;
        let q = parseFloat(String(raw).replace(',', '.'));
        if (!isFinite(q)) q = 1;
        if (!line.allow_decimal) {
            q = Math.round(q);
        } else {
            q = Math.round(q * 100) / 100;
        }
        if (q < 1) q = 1;
        line.quantity = q;
        renderCart();
    }

    const stepCustomer = document.getElementById('stepCustomer');
    const orderModeInput = document.getElementById('order_mode');
    const isEdit = !stepCustomer;
    const customerSearchEdit = document.getElementById('customerSearchEdit');
    const customerResultsEdit = document.getElementById('customerResultsEdit');

    function syncOrderMode() {
        if (!orderModeInput) return;
        const checked = document.querySelector('input[name="order_mode_ui"]:checked');
        if (checked) orderModeInput.value = checked.value;
        const submitBtn = document.getElementById('submitOrderBtn');
        if (!submitBtn || isEdit) return;
        const mode = orderModeInput.value;
        const label = mode === 'estimate' ? 'Create estimate' : (mode === 'back_order' ? 'Create back order' : 'Create order');
        const svgHtml = submitBtn.querySelector('svg') ? submitBtn.querySelector('svg').outerHTML : '';
        submitBtn.innerHTML = svgHtml + ' ' + label;
        // Keep cart-step SUBMIT label as-is (matches mobile create-order UI)
    }

    document.querySelectorAll('input[name="order_mode_ui"]').forEach(r => {
        r.addEventListener('change', syncOrderMode);
    });
    syncOrderMode();

    function showCustomerStep() {
        if (!stepCustomer) return;
        stepCustomer.hidden = false;
        stepCart.hidden = true;
        stepShipping.hidden = true;
        document.body.classList.add('sale-picking-customer');
        document.body.classList.remove('sale-building-order');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function showCartStep() {
        if (stepCustomer) stepCustomer.hidden = true;
        stepCart.hidden = false;
        stepShipping.hidden = true;
        document.body.classList.remove('sale-picking-customer');
        document.body.classList.add('sale-building-order');
        window.scrollTo({ top: 0, behavior: 'smooth' });
        applyFulfillment();
    }

    function setCustomer(id, text, shipAddr, goToCart) {
        contactId.value = id || '';
        const orderCustomerName = document.getElementById('orderCustomerName');
        if (id) {
            lastCustomer = { id: String(id), text: text || '', shipAddr: shipAddr || '' };
            if (customerLabel) customerLabel.textContent = text;
            if (orderCustomerName) orderCustomerName.textContent = text;
            // Keep chip hidden — customer name shows in order header only
            if (customerSelected) {
                customerSelected.classList.add('hidden');
                customerSelected.setAttribute('aria-hidden', 'true');
            }
            if (customerSearchWrap) customerSearchWrap.classList.add('hidden');
            if (customerSearch) customerSearch.value = '';
            if (customerResults && !stepCustomer) {
                customerResults.classList.add('hidden');
                customerResults.innerHTML = '';
            }
            if (customerResultsEdit) {
                customerResultsEdit.classList.add('hidden');
                customerResultsEdit.innerHTML = '';
            }
            savedShipAddr = shipAddr || '';
            if (savedShipAddr && shippingAddress && !shippingAddress.value.trim()) {
                shippingAddress.value = savedShipAddr;
            }
            if (goToCart !== false && stepCustomer) {
                showCartStep();
            }
        } else {
            lastCustomer = { id: '', text: '', shipAddr: '' };
            if (customerLabel) customerLabel.textContent = '';
            if (orderCustomerName) orderCustomerName.textContent = '';
            if (customerSelected) customerSelected.classList.add('hidden');
            savedShipAddr = '';
            if (stepCustomer) showCustomerStep();
        }
    }

    function openCustomerSearch() {
        if (stepCustomer) {
            showCustomerStep();
            if (customerSearch) customerSearch.focus();
            return;
        }
        customerSelected.classList.add('hidden');
        if (customerSearchWrap) customerSearchWrap.classList.remove('hidden');
        if (customerSearchEdit) {
            customerSearchEdit.value = '';
            customerSearchEdit.focus();
        }
    }

    customerSelected.addEventListener('click', openCustomerSearch);

    function esc(s) {
        return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function renderCustomerRows(rows, targetEl, pickFn) {
        targetEl.innerHTML = '';
        if (!rows.length) {
            targetEl.innerHTML = '<div class="sale-pick-empty">No customers found</div>';
            targetEl.classList.remove('hidden');
            return;
        }
        rows.forEach(r => {
            const a = document.createElement('button');
            a.type = 'button';
            if (targetEl === customerResults && stepCustomer) {
                a.className = 'sale-pick-row';
                a.innerHTML = `
                    <span class="sale-pick-avatar">${esc(r.initials || 'C')}</span>
                    <span class="sale-pick-meta min-w-0">
                        <span class="sale-pick-name">${esc(r.display_name || r.text || '')}</span>
                        <span class="sale-pick-addr">${esc(r.address || r.mobile || '')}</span>
                    </span>
                    <span class="sale-pick-chev" aria-hidden="true">›</span>`;
            } else {
                a.className = 'cust-pick w-full text-left px-3 py-2.5 text-sm border-b border-slate-100 font-semibold';
                a.textContent = r.text;
            }
            a.onclick = () => pickFn(r);
            targetEl.appendChild(a);
        });
        targetEl.classList.remove('hidden');
    }

    async function loadCustomers(q, targetEl, pickFn) {
        const rows = await fetchJson(@json(route('sale.api.customers')) + '?q=' + encodeURIComponent(q || ''));
        renderCustomerRows(rows, targetEl, pickFn);
    }

    if (customerSearch && customerResults) {
        customerSearch.addEventListener('input', () => {
            clearTimeout(custTimer);
            custTimer = setTimeout(() => {
                loadCustomers(customerSearch.value.trim(), customerResults, (r) => {
                    setCustomer(r.id, r.text, r.shipping_address || r.address || '', true);
                });
            }, 250);
        });
        if (stepCustomer) document.body.classList.add('sale-picking-customer');
    }

    if (customerSearchEdit && customerResultsEdit) {
        customerSearchEdit.addEventListener('input', () => {
            clearTimeout(custTimer);
            custTimer = setTimeout(() => {
                loadCustomers(customerSearchEdit.value.trim(), customerResultsEdit, (r) => {
                    setCustomer(r.id, r.text, r.shipping_address || r.address || '', false);
                    customerSearchWrap.classList.add('hidden');
                });
            }, 250);
        });
    }

    @if(!empty($default_customer))
    setCustomer(
        {{ (int) $default_customer['id'] }},
        @json($default_customer['text']),
        @json($default_customer['shipping_address'] ?? ''),
        true
    );
    @elseif(!empty($edit_order))
    document.body.classList.remove('sale-picking-customer');
    document.body.classList.add('sale-building-order');
    @endif

    function renderCart() {
        cartLines.innerHTML = '';
        const total = cartSum();
        cartEmpty.classList.toggle('hidden', cart.length > 0);
        cart.forEach((line, idx) => {
            const lineTot = line.quantity * line.unit_price;
            const el = document.createElement('div');
            el.className = 'sale-cart-item';
            el.innerHTML = `
                <div class="sale-cart-item__top">
                    <div class="min-w-0">
                        <div class="font-bold text-sm leading-snug break-words">${escapeHtml(line.name)}</div>
                        <div class="text-xs text-slate-500 mt-0.5">${money(line.unit_price)} each</div>
                    </div>
                    <button type="button" data-rm="${idx}" class="sale-cart-item__rm" aria-label="Remove">×</button>
                </div>
                <div class="sale-cart-item__qty">
                    <button type="button" data-dec="${idx}" class="sale-qty-btn"${Number(line.quantity) <= 1 ? ' disabled' : ''}>−</button>
                    <input type="text" inputmode="${line.allow_decimal ? 'decimal' : 'numeric'}" pattern="${line.allow_decimal ? '[0-9]*[.,]?[0-9]*' : '[0-9]*'}" value="${formatQty(line)}" data-qty="${idx}" class="sale-qty-input" autocomplete="off">
                    <button type="button" data-inc="${idx}" class="sale-qty-btn">+</button>
                    <span class="ml-auto font-extrabold tabular-nums text-sm text-sale">${money(lineTot)}</span>
                </div>`;
            cartLines.appendChild(el);
        });
        cartTotal.textContent = money(total);
        shipTotal.textContent = money(total);

        cartLines.querySelectorAll('[data-rm]').forEach(b => b.onclick = () => { cart.splice(+b.dataset.rm, 1); renderCart(); });
        cartLines.querySelectorAll('[data-inc]').forEach(b => b.onclick = () => changeQty(+b.dataset.inc, 1));
        cartLines.querySelectorAll('[data-dec]').forEach(b => b.onclick = () => changeQty(+b.dataset.dec, -1));
        cartLines.querySelectorAll('[data-qty]').forEach(inp => {
            inp.addEventListener('focus', () => inp.select());
            inp.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    inp.blur();
                }
            });
            inp.addEventListener('blur', () => setQtyFromInput(+inp.dataset.qty, inp.value));
        });
    }

    async function fetchJson(url) {
        const res = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();
        return Array.isArray(data) ? data : [];
    }

    if (customerSearch && customerResults && stepCustomer && !contactId.value) {
        loadCustomers('', customerResults, (r) => {
            setCustomer(r.id, r.text, r.shipping_address || r.address || '', true);
        });
    }

    productSearch.addEventListener('input', () => {
        clearTimeout(prodTimer);
        prodTimer = setTimeout(async () => {
            const q = productSearch.value.trim();
            if (q.length < 1) { productResults.classList.add('hidden'); productResults.innerHTML = ''; return; }
            const rows = await fetchJson(@json(route('sale.api.products')) + '?q=' + encodeURIComponent(q) + '&location_id=' + encodeURIComponent(locationId.value));
            productResults.innerHTML = '';
            if (!rows.length) {
                productResults.innerHTML = '<div class="px-3 py-3 text-sm text-slate-400">No products</div>';
                productResults.classList.remove('hidden');
                return;
            }
            // Scan mode: exact SKU match adds immediately when only one hit
            if (skuMode === 'scan' && rows.length === 1) {
                const exact = rows[0];
                const sku = String(exact.sku || '').toLowerCase();
                if (sku && sku === q.toLowerCase()) {
                    addToCart(exact);
                    productSearch.value = '';
                    productResults.classList.add('hidden');
                    productResults.innerHTML = '';
                    return;
                }
            }
            rows.forEach(r => {
                const a = document.createElement('button');
                a.type = 'button';
                a.className = 'product-pick w-full text-left px-3 py-2.5 text-sm border-b border-slate-100';
                a.innerHTML = `<div class="font-semibold">${r.name}</div><div class="text-xs text-slate-500">${money(r.price)} · Stock ${r.stock}</div>`;
                a.onclick = () => {
                    addToCart(r);
                    productSearch.value = '';
                    productResults.classList.add('hidden');
                    productResults.innerHTML = '';
                };
                productResults.appendChild(a);
            });
            productResults.classList.remove('hidden');
        }, 250);
    });

    productSearch.addEventListener('keydown', async (e) => {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        const q = productSearch.value.trim();
        if (!q) return;
        const rows = await fetchJson(@json(route('sale.api.products')) + '?q=' + encodeURIComponent(q) + '&location_id=' + encodeURIComponent(locationId.value));
        if (!rows.length) {
            productResults.innerHTML = '<div class="px-3 py-3 text-sm text-slate-400">No products</div>';
            productResults.classList.remove('hidden');
            return;
        }
        const exact = rows.find(r => String(r.sku || '').toLowerCase() === q.toLowerCase()) || (rows.length === 1 ? rows[0] : null);
        if (exact && skuMode === 'scan') {
            addToCart(exact);
            productSearch.value = '';
            productResults.classList.add('hidden');
            productResults.innerHTML = '';
            return;
        }
        productResults.innerHTML = '';
        rows.forEach(r => {
            const a = document.createElement('button');
            a.type = 'button';
            a.className = 'product-pick w-full text-left px-3 py-2.5 text-sm border-b border-slate-100';
            a.innerHTML = `<div class="font-semibold">${r.name}</div><div class="text-xs text-slate-500">${money(r.price)} · Stock ${r.stock}</div>`;
            a.onclick = () => {
                addToCart(r);
                productSearch.value = '';
                productResults.classList.add('hidden');
                productResults.innerHTML = '';
            };
            productResults.appendChild(a);
        });
        productResults.classList.remove('hidden');
    });

    document.getElementById('goShippingBtn').addEventListener('click', () => {
        if (!contactId.value) { alert('Select a customer'); return; }
        if (!cart.length) { alert('Add at least one product'); return; }
        applyFulfillment();
        if (savedShipAddr && !shippingAddress.value.trim()) {
            shippingAddress.value = savedShipAddr;
        }
        if (!shippingAddress.value.trim()) {
            shippingAddress.value = lastCustomer.text || 'Customer address';
        }
        shipTotal.textContent = money(cartSum());
        if (stepCustomer) stepCustomer.hidden = true;
        stepCart.hidden = true;
        stepShipping.hidden = false;
        document.body.classList.remove('sale-picking-customer');
        document.body.classList.remove('sale-building-order');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    document.getElementById('backToCartBtn').addEventListener('click', () => {
        stepShipping.hidden = true;
        stepCart.hidden = false;
        if (stepCustomer) stepCustomer.hidden = true;
        document.body.classList.remove('sale-picking-customer');
        document.body.classList.add('sale-building-order');
    });

    const backToCustomerBtn = document.getElementById('backToCustomerBtn');
    if (backToCustomerBtn) {
        backToCustomerBtn.addEventListener('click', () => showCustomerStep());
    }

    // Fulfillment: Pickup / Delivery / Shipping → existing shipping_details + shipping_method
    const shippingMethodInput = document.getElementById('shipping_method');
    function applyFulfillment() {
        const checked = document.querySelector('input[name="fulfillment_ui"]:checked');
        const mode = checked ? checked.value : 'Delivery';
        if (shippingMethodInput) shippingMethodInput.value = mode;
        if (shippingDetails) {
            const current = (shippingDetails.value || '').trim();
            if (!current || ['Pickup', 'Delivery', 'Shipping'].includes(current)) {
                shippingDetails.value = mode;
            } else if (!current.startsWith(mode)) {
                shippingDetails.value = mode + ': ' + current;
            }
        }
        if (mode === 'Pickup' && shippingAddress && !shippingAddress.value.trim()) {
            shippingAddress.value = 'Customer Pickup';
        }
        if ((mode === 'Delivery' || mode === 'Shipping') && shippingAddress && !shippingAddress.value.trim() && savedShipAddr) {
            shippingAddress.value = savedShipAddr;
        }
    }
    document.querySelectorAll('input[name="fulfillment_ui"]').forEach(r => {
        r.addEventListener('change', applyFulfillment);
    });
    @if(!empty($edit_order))
    (function syncEditFulfillment() {
        const method = @json($edit_order->shipping_method ?? 'Delivery');
        const match = Array.from(document.querySelectorAll('input[name="fulfillment_ui"]'))
            .find(r => r.value === method);
        if (match) match.checked = true;
    })();
    @endif
    applyFulfillment();

    // SKU scan vs name search (same products API)
    let skuMode = 'scan';
    const skuModeScan = document.getElementById('skuModeScan');
    const skuModeText = document.getElementById('skuModeText');
    function setSkuMode(mode) {
        skuMode = mode;
        if (skuModeScan) {
            skuModeScan.classList.toggle('is-active', mode === 'scan');
            skuModeScan.setAttribute('aria-pressed', mode === 'scan' ? 'true' : 'false');
        }
        if (skuModeText) {
            skuModeText.classList.toggle('is-active', mode === 'text');
            skuModeText.setAttribute('aria-pressed', mode === 'text' ? 'true' : 'false');
        }
        if (productSearch) {
            productSearch.placeholder = mode === 'scan' ? 'Enter SKU' : 'Search product name / SKU';
            productSearch.focus();
        }
    }
    if (skuModeScan) skuModeScan.addEventListener('click', () => setSkuMode('scan'));
    if (skuModeText) skuModeText.addEventListener('click', () => setSkuMode('text'));

    // Last purchased quantities (existing sell/SO history)
    const useLastQtyToggle = document.getElementById('useLastQtyToggle');
    const lastQtyStatus = document.getElementById('lastQtyStatus');
    function setLastQtyStatus(msg, isError) {
        if (!lastQtyStatus) return;
        if (!msg) {
            lastQtyStatus.hidden = true;
            lastQtyStatus.textContent = '';
            lastQtyStatus.classList.remove('is-error');
            return;
        }
        lastQtyStatus.hidden = false;
        lastQtyStatus.textContent = msg;
        lastQtyStatus.classList.toggle('is-error', !!isError);
    }
    if (useLastQtyToggle) {
        useLastQtyToggle.addEventListener('change', async () => {
            if (!useLastQtyToggle.checked) {
                setLastQtyStatus('');
                return;
            }
            if (!contactId || !contactId.value) {
                useLastQtyToggle.checked = false;
                setLastQtyStatus('Select a customer first', true);
                return;
            }
            useLastQtyToggle.disabled = true;
            setLastQtyStatus('Loading last purchased quantities…');
            try {
                const loc = (locationId && locationId.value) ? locationId.value : '';
                const rows = await fetchJson(
                    @json(route('sale.api.last_purchases'))
                    + '?contact_id=' + encodeURIComponent(contactId.value)
                    + '&location_id=' + encodeURIComponent(loc)
                );
                if (!rows.length) {
                    useLastQtyToggle.checked = false;
                    setLastQtyStatus('No previous purchases for this customer', true);
                    return;
                }
                // Replace list with last order lines (system-wise fill)
                cart.length = 0;
                rows.forEach(r => {
                    addToCart({
                        product_id: r.product_id,
                        variation_id: r.variation_id,
                        name: r.name,
                        price: r.price,
                        quantity: r.quantity || 1,
                        enable_stock: r.enable_stock,
                        product_type: r.product_type,
                        allow_decimal: r.allow_decimal,
                    }, true, true);
                });
                renderCart();
                const scroll = document.getElementById('cartScroll');
                if (scroll) scroll.scrollTop = 0;
                setLastQtyStatus(rows.length + ' item(s) loaded from last purchase');
                showAddedMsg();
            } catch (err) {
                console.error(err);
                useLastQtyToggle.checked = false;
                setLastQtyStatus('Could not load last purchases', true);
            } finally {
                useLastQtyToggle.disabled = false;
            }
        });
    }

    // Catalog
    function openCatalog() {
        catalogModal.hidden = false;
        catalogStack = [{ level: 'cats', title: 'Categories' }];
        renderCatalog();
    }
    function closeCatalog() { catalogModal.hidden = true; }

    async function ensureCatalogTree() {
        if (!catalogTree) catalogTree = await fetchJson(@json(route('sale.api.categories')));
        return catalogTree;
    }

    async function renderCatalog() {
        const state = catalogStack[catalogStack.length - 1];
        catalogTitle.textContent = state.title;
        catalogBackBtn.classList.toggle('hidden', catalogStack.length <= 1);
        catalogBody.innerHTML = '<div class="px-3 py-4 text-sm text-slate-400">Loading…</div>';

        const tree = await ensureCatalogTree();
        if (state.level === 'cats') {
            if (!tree.length) {
                catalogBody.innerHTML = '<div class="px-3 py-4 text-sm text-slate-400">No categories</div>';
                return;
            }
            catalogBody.innerHTML = '';
            tree.forEach(cat => {
                const b = document.createElement('button');
                b.type = 'button';
                b.className = 'sale-catalog__row';
                b.innerHTML = `<span>${cat.name}</span><span class="sale-catalog__chev">›</span>`;
                b.onclick = () => {
                    if (cat.sub_categories && cat.sub_categories.length) {
                        catalogStack.push({ level: 'subs', title: cat.name, catId: cat.id, subs: cat.sub_categories });
                    } else {
                        catalogStack.push({ level: 'products', title: cat.name, catId: cat.id, subId: 0 });
                    }
                    renderCatalog();
                };
                catalogBody.appendChild(b);
            });
            return;
        }

        if (state.level === 'subs') {
            catalogBody.innerHTML = '';
            const allBtn = document.createElement('button');
            allBtn.type = 'button';
            allBtn.className = 'sale-catalog__row sale-catalog__row--all';
            allBtn.innerHTML = `<span>All in ${state.title}</span><span class="sale-catalog__chev">›</span>`;
            allBtn.onclick = () => {
                catalogStack.push({ level: 'products', title: state.title, catId: state.catId, subId: 0 });
                renderCatalog();
            };
            catalogBody.appendChild(allBtn);
            (state.subs || []).forEach(sub => {
                const b = document.createElement('button');
                b.type = 'button';
                b.className = 'sale-catalog__row';
                b.innerHTML = `<span>${sub.name}</span><span class="sale-catalog__chev">›</span>`;
                b.onclick = () => {
                    catalogStack.push({ level: 'products', title: sub.name, catId: state.catId, subId: sub.id });
                    renderCatalog();
                };
                catalogBody.appendChild(b);
            });
            return;
        }

        const loc = locationId.value;
        let url = @json(route('sale.api.products')) + '?location_id=' + encodeURIComponent(loc) + '&limit=80';
        if (state.subId) url += '&sub_category_id=' + state.subId;
        else if (state.catId) url += '&category_id=' + state.catId;
        renderProductRows(await fetchJson(url));
    }

    function renderProductRows(rows) {
        if (!rows.length) {
            catalogBody.innerHTML = '<div class="px-3 py-4 text-sm text-slate-400">No products</div>';
            return;
        }
        catalogBody.innerHTML = '';
        rows.forEach(r => {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'sale-catalog__row sale-catalog__prod';
            const qty = cartQty(r.variation_id);
            b.innerHTML = `<div class="min-w-0"><div class="font-bold text-sm truncate">${r.name}</div><div class="text-xs text-slate-500">${money(r.price)} · Stock ${r.stock}</div></div><span class="sale-catalog__add">${qty || '+'}</span>`;
            b.onclick = (e) => {
                e.preventDefault();
                e.stopPropagation();
                addToCart(r);
                const addEl = b.querySelector('.sale-catalog__add');
                if (addEl) addEl.textContent = String(cartQty(r.variation_id) || '+');
            };
            catalogBody.appendChild(b);
        });
    }

    document.getElementById('catalogOpenBtn').addEventListener('click', openCatalog);
    document.getElementById('catalogCloseBtn').addEventListener('click', closeCatalog);
    catalogBackBtn.addEventListener('click', () => {
        if (catalogStack.length > 1) { catalogStack.pop(); renderCatalog(); }
    });
    catalogModal.addEventListener('click', (e) => { if (e.target === catalogModal) closeCatalog(); });

    form.addEventListener('submit', (e) => {
        if (!contactId.value) { e.preventDefault(); alert('Select a customer'); return; }
        if (!cart.length) { e.preventDefault(); alert('Add at least one product'); return; }
        if (!shippingDetails.value.trim()) { e.preventDefault(); alert('Enter shipping details'); return; }
        if (!shippingAddress.value.trim()) { e.preventDefault(); alert('Enter shipping address'); return; }
        productsJson.innerHTML = '';
        cart.forEach((line, i) => {
            ['product_id', 'variation_id', 'quantity', 'unit_price', 'transaction_sell_lines_id'].forEach(k => {
                if (k === 'transaction_sell_lines_id' && !line[k]) return;
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = `products[${i}][${k}]`;
                inp.value = line[k];
                productsJson.appendChild(inp);
            });
        });
    });

    (async function prefillFromQuery() {
        const editLines = @json($edit_lines ?? []);
        if (editLines.length) {
            editLines.forEach(r => addToCart(r, true));
            return;
        }
        const params = new URLSearchParams(window.location.search);
        const vid = params.get('add');
        if (!vid || !locationId.value) return;
        const rows = await fetchJson(@json(route('sale.api.products')) + '?location_id=' + encodeURIComponent(locationId.value) + '&variation_id=' + encodeURIComponent(vid));
        if (rows[0]) addToCart(rows[0]);
    })();

    renderCart();
})();
</script>
@endpush
