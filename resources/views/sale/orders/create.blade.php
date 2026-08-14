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
    <div id="productsJson" class="hidden"></div>

    {{-- STEP 1: Customer + Cart --}}
    <div id="stepCart" class="sale-cart-flow">
        <div class="sale-card sale-cart-customer">
            <div class="sale-sec-title !mb-2">
                <span class="sale-sec-title__ico">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c1.5-4 14.5-4 16 0"/></svg>
                </span>
                Customer
            </div>
            <button type="button" id="customerSelected" class="hidden w-full text-left sale-input !flex items-center justify-between gap-2 text-sale font-bold cursor-pointer" aria-label="Change customer">
                <span id="customerLabel" class="min-w-0 truncate"></span>
                <svg class="shrink-0 text-slate-400" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M6 9l6 6 6-6"/></svg>
            </button>
            <div id="customerSearchWrap">
                <input type="search" id="customerSearch" class="sale-input" placeholder="Search customer name / mobile" autocomplete="off">
                <div id="customerResults" class="mt-2 border border-sale-line rounded-xl bg-white max-h-40 overflow-auto hidden"></div>
            </div>
        </div>

        <div class="sale-card sale-cart-panel">
            <div class="sale-prod-head">
                <div class="sale-sec-title !mb-0">
                    <span class="sale-sec-title__ico">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.3 7 12 12l8.7-5M12 22V12"/></svg>
                    </span>
                    Products
                </div>
                <button type="button" id="catalogOpenBtn" class="sale-catalog-btn" aria-label="Catalog">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h10"/></svg>
                    <span>Catalog</span>
                </button>
            </div>
            <div class="sale-prod-search-wrap">
                <input type="search" id="productSearch" class="sale-input" placeholder="Search product name / SKU" autocomplete="off">
                <div id="productResults" class="sale-prod-results hidden"></div>
            </div>

            <div class="sale-cart-scroll" id="cartScroll">
                <div id="cartEmpty" class="sale-cart-empty">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-slate-300 mx-auto mb-2"><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.4a2 2 0 0 0 2-1.5L21 8H7"/></svg>
                    <div class="text-sm font-semibold text-slate-400">Cart is empty</div>
                    <div class="text-xs text-slate-400 mt-1">Open Catalog to add products</div>
                </div>
                <div id="cartLines" class="sale-cart-lines"></div>
            </div>
        </div>

        <div class="sale-cart-footer sale-card">
            <div class="sale-cart-total">
                <span>Total</span>
                <strong id="cartTotal" class="tabular-nums">$0.00</strong>
            </div>
            <div class="sale-cart-pay">
                <input type="hidden" name="payment_type" value="due">
            </div>
            <button type="button" class="sale-btn !w-full inline-flex items-center justify-center gap-2" id="goShippingBtn">
                Order
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M9 6l6 6-6 6"/></svg>
            </button>
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

    function cartSum() {
        return cart.reduce((s, l) => s + (l.quantity * l.unit_price), 0);
    }

    function addToCart(r, silent) {
        if (!r || !r.variation_id) return;
        const vid = Number(r.variation_id);
        const existingIdx = cart.findIndex(c => Number(c.variation_id) === vid);
        if (existingIdx >= 0) {
            cart[existingIdx].quantity = +cart[existingIdx].quantity + (Number(r.quantity) || 1);
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
            });
        }
        renderCart();
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
        return line ? Number(line.quantity) : 0;
    }

    function setCustomer(id, text, shipAddr) {
        contactId.value = id || '';
        if (id) {
            lastCustomer = { id: String(id), text: text || '', shipAddr: shipAddr || '' };
            customerLabel.textContent = text;
            customerSelected.classList.remove('hidden');
            customerSearchWrap.classList.add('hidden');
            customerSearch.value = '';
            customerResults.classList.add('hidden');
            customerResults.innerHTML = '';
            savedShipAddr = shipAddr || '';
            if (savedShipAddr && !shippingAddress.value.trim()) {
                shippingAddress.value = savedShipAddr;
            }
        } else {
            lastCustomer = { id: '', text: '', shipAddr: '' };
            customerLabel.textContent = '';
            customerSelected.classList.add('hidden');
            customerSearchWrap.classList.remove('hidden');
            customerSearch.focus();
            savedShipAddr = '';
        }
    }

    function openCustomerSearch() {
        customerSelected.classList.add('hidden');
        customerSearchWrap.classList.remove('hidden');
        customerSearch.value = '';
        customerResults.classList.add('hidden');
        customerResults.innerHTML = '';
        customerSearch.focus();
    }

    customerSelected.addEventListener('click', openCustomerSearch);

    // If user closes search without picking, restore previous customer bar
    customerSearch.addEventListener('blur', () => {
        setTimeout(() => {
            if (document.activeElement && customerSearchWrap.contains(document.activeElement)) return;
            if (!customerResults.classList.contains('hidden')) return;
            if (lastCustomer.id && customerSearchWrap.classList.contains('hidden') === false && !customerSearch.value.trim()) {
                setCustomer(lastCustomer.id, lastCustomer.text, lastCustomer.shipAddr);
            }
        }, 200);
    });

    @if(!empty($default_customer))
    setCustomer(
        {{ (int) $default_customer['id'] }},
        @json($default_customer['text']),
        @json($default_customer['shipping_address'] ?? '')
    );
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
                        <div class="font-bold text-sm leading-snug">${line.name}</div>
                        <div class="text-xs text-slate-500 mt-0.5">${money(line.unit_price)} each</div>
                    </div>
                    <button type="button" data-rm="${idx}" class="sale-cart-item__rm" aria-label="Remove">×</button>
                </div>
                <div class="sale-cart-item__qty">
                    <button type="button" data-dec="${idx}" class="sale-qty-btn">−</button>
                    <input type="number" min="0.01" step="0.01" value="${line.quantity}" data-qty="${idx}" class="sale-qty-input">
                    <button type="button" data-inc="${idx}" class="sale-qty-btn">+</button>
                    <span class="ml-auto font-extrabold tabular-nums text-sm text-sale">${money(lineTot)}</span>
                </div>`;
            cartLines.appendChild(el);
        });
        cartTotal.textContent = money(total);
        shipTotal.textContent = money(total);

        cartLines.querySelectorAll('[data-rm]').forEach(b => b.onclick = () => { cart.splice(+b.dataset.rm, 1); renderCart(); });
        cartLines.querySelectorAll('[data-inc]').forEach(b => b.onclick = () => { cart[+b.dataset.inc].quantity = +(cart[+b.dataset.inc].quantity) + 1; renderCart(); });
        cartLines.querySelectorAll('[data-dec]').forEach(b => b.onclick = () => {
            const i = +b.dataset.dec;
            cart[i].quantity = Math.max(0.01, +(cart[i].quantity) - 1);
            renderCart();
        });
        cartLines.querySelectorAll('[data-qty]').forEach(inp => inp.onchange = () => {
            cart[+inp.dataset.qty].quantity = Math.max(0.01, parseFloat(inp.value) || 1);
            renderCart();
        });
    }

    async function fetchJson(url) {
        const res = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();
        return Array.isArray(data) ? data : [];
    }

    customerSearch.addEventListener('input', () => {
        clearTimeout(custTimer);
        custTimer = setTimeout(async () => {
            const q = customerSearch.value.trim();
            const rows = await fetchJson(@json(route('sale.api.customers')) + '?q=' + encodeURIComponent(q));
            customerResults.innerHTML = '';
            if (!rows.length) { customerResults.classList.add('hidden'); return; }
            rows.forEach(r => {
                const a = document.createElement('button');
                a.type = 'button';
                a.className = 'cust-pick w-full text-left px-3 py-2.5 text-sm border-b border-slate-100 font-semibold';
                a.textContent = r.text;
                a.onclick = () => setCustomer(r.id, r.text, r.shipping_address || '');
                customerResults.appendChild(a);
            });
            customerResults.classList.remove('hidden');
        }, 250);
    });

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

    document.getElementById('goShippingBtn').addEventListener('click', () => {
        if (!contactId.value) { alert('Select a customer'); return; }
        if (!cart.length) { alert('Add at least one product'); return; }
        if (savedShipAddr && !shippingAddress.value.trim()) {
            shippingAddress.value = savedShipAddr;
        }
        shipTotal.textContent = money(cartSum());
        stepCart.hidden = true;
        stepShipping.hidden = false;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    document.getElementById('backToCartBtn').addEventListener('click', () => {
        stepShipping.hidden = true;
        stepCart.hidden = false;
    });

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
