@extends('customer.layout')
@section('title', 'Create Order')
@section('content')
<div class="flex items-start justify-between gap-3 mb-4">
    <div>
        <h1 class="ca-page-title">Create Order</h1>
        <div class="ca-page-sub">{{ $initials }}</div>
    </div>
    <button type="button" id="camScanBtn" class="w-11 h-11 rounded-2xl bg-gradient-to-br from-rose-500 to-rose-700 text-white flex items-center justify-center shadow-lg shadow-rose-500/30" title="Scan with camera" aria-label="Scan with camera">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M7 4H5a1 1 0 0 0-1 1v2"/><path d="M17 4h2a1 1 0 0 1 1 1v2"/><path d="M7 20H5a1 1 0 0 1-1-1v-2"/><path d="M17 20h2a1 1 0 0 0 1-1v-2"/></svg>
    </button>
</div>

<div class="grid grid-cols-3 gap-2 mb-3" id="fulfillGroup">
    <button type="button" class="fulfill-opt" data-value="Pickup">PICKUP</button>
    <button type="button" class="fulfill-opt is-on" data-value="Delivery">DELIVERY</button>
    <button type="button" class="fulfill-opt" data-value="Shipping">SHIPPING</button>
</div>

@if($locations->count())
<div class="mb-3">
    <select id="orderLocation" class="w-full border border-slate-200 rounded-xl px-3 py-3 text-sm font-semibold bg-white">
        @foreach($locations as $id => $name)
            <option value="{{ $id }}" @selected($id == $default_location)>{{ $name }}</option>
        @endforeach
    </select>
</div>
@endif

<div class="relative mb-3">
    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">🔍</span>
    <input type="search" id="productSearch" class="ca-input" placeholder="Enter Product Name / SKU" autocomplete="off">
</div>
<div id="productResults" class="hidden space-y-2 mb-3"></div>

<div class="flex items-center justify-between mb-2">
    <div>
        <div class="font-extrabold">Added Items</div>
        <div class="text-sm text-slate-500">Total : <span id="cartTotal" class="font-bold text-slate-800">$0.00</span></div>
    </div>
    <div class="flex items-center gap-3">
        <button type="button" id="catalogOpenBtn" class="ca-link text-sm">Product Catalog</button>
        <button type="button" id="clearCartBtn" class="text-slate-400 text-lg" title="Clear">🗑</button>
    </div>
</div>

<div id="cartEmpty" class="ca-card text-center py-10 mb-4">
    <div class="text-4xl mb-2">🛒</div>
    <div class="font-extrabold">Ready to create your order?</div>
    <div class="text-sm text-slate-500 mt-1">Search, scan, or open Product Catalog.</div>
    <button type="button" id="catalogEmptyBtn" class="ca-btn mt-4 !w-auto px-6 mx-auto">Browse Catalog</button>
</div>
<div id="cartLines" class="space-y-2 mb-4 hidden"></div>

<form method="POST" action="{{ route('customer.orders.store') }}" id="orderForm">
    @csrf
    <input type="hidden" name="location_id" id="location_id" value="{{ $default_location }}">
    <input type="hidden" name="shipping_method" id="shipping_method" value="Delivery">
    <input type="hidden" name="shipping_details" id="shipping_details" value="Delivery">
    <textarea name="shipping_address" class="hidden">{{ $contact->shipping_address ?? '' }}</textarea>
    <div id="productsFields"></div>
    <button type="submit" id="submitBtn" class="ca-btn" disabled>SUBMIT</button>
</form>

<div id="catalogModal" class="ca-catalog" hidden>
    <div class="ca-catalog__panel">
        <div class="ca-catalog__head">
            <button type="button" id="catalogBackBtn" class="ca-catalog__back hidden" aria-label="Back">‹</button>
            <div class="font-extrabold text-base truncate flex-1" id="catalogTitle">Catalog</div>
            <button type="button" id="catalogCloseBtn" class="ca-catalog__close" aria-label="Close">×</button>
        </div>
        <div id="catalogBody" class="ca-catalog__body"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const cart = [];
    let timer = null;
    const search = document.getElementById('productSearch');
    const results = document.getElementById('productResults');
    const cartLines = document.getElementById('cartLines');
    const cartEmpty = document.getElementById('cartEmpty');
    const cartTotal = document.getElementById('cartTotal');
    const submitBtn = document.getElementById('submitBtn');
    const productsFields = document.getElementById('productsFields');
    const locationSel = document.getElementById('orderLocation');
    const locationId = document.getElementById('location_id');
    const shipMethod = document.getElementById('shipping_method');
    const shipDetails = document.getElementById('shipping_details');
    const api = @json(route('customer.api.products'));
    const catApi = @json(route('customer.api.categories'));
    const catalogModal = document.getElementById('catalogModal');
    const catalogBody = document.getElementById('catalogBody');
    const catalogTitle = document.getElementById('catalogTitle');
    const catalogBackBtn = document.getElementById('catalogBackBtn');
    let catalogTree = null;
    let catalogStack = [];

    function money(n) { return '$' + (Number(n) || 0).toFixed(2); }
    function sum() { return cart.reduce((s, l) => s + l.qty * l.price, 0); }
    function cartQty(vid) {
        const line = cart.find(c => c.variation_id === Number(vid));
        return line ? line.qty : 0;
    }
    function esc(s) {
        return String(s || '').replace(/[&<>"']/g, (c) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[c]));
    }

    function render() {
        cartTotal.textContent = money(sum());
        productsFields.innerHTML = '';
        if (!cart.length) {
            cartEmpty.classList.remove('hidden');
            cartLines.classList.add('hidden');
            cartLines.innerHTML = '';
            submitBtn.disabled = true;
            return;
        }
        cartEmpty.classList.add('hidden');
        cartLines.classList.remove('hidden');
        cartLines.innerHTML = '';
        cart.forEach((l, i) => {
            const row = document.createElement('div');
            row.className = 'ca-card flex items-center gap-3';
            row.innerHTML = '<div class="min-w-0 flex-1"><div class="font-bold text-sm"></div><div class="text-xs text-slate-500"></div></div>' +
                '<div class="flex items-center gap-2">' +
                '<button type="button" data-i="'+i+'" data-d="-1" class="qty-btn w-8 h-8 rounded-lg bg-slate-100 font-bold">−</button>' +
                '<span class="font-extrabold w-6 text-center">'+l.qty+'</span>' +
                '<button type="button" data-i="'+i+'" data-d="1" class="qty-btn w-8 h-8 rounded-lg bg-slate-100 font-bold">+</button></div>';
            row.querySelector('.font-bold').textContent = l.name;
            row.querySelector('.text-xs').textContent = money(l.price);
            cartLines.appendChild(row);

            const fields = {
                product_id: l.product_id,
                variation_id: l.variation_id,
                quantity: l.qty,
                unit_price: l.price
            };
            Object.keys(fields).forEach((k) => {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'products[' + i + '][' + k + ']';
                inp.value = fields[k];
                productsFields.appendChild(inp);
            });
        });
        submitBtn.disabled = false;
    }

    function addItem(r) {
        const vid = Number(r.variation_id);
        const ex = cart.find(c => c.variation_id === vid);
        if (ex) ex.qty += 1;
        else cart.push({
            product_id: Number(r.product_id),
            variation_id: vid,
            name: r.name,
            price: Number(r.price) || 0,
            qty: 1
        });
        render();
        showAddedMsg();
    }

    function showAddedMsg() {
        let el = document.getElementById('caAddedMsg');
        if (!el) {
            el = document.createElement('div');
            el.id = 'caAddedMsg';
            el.className = 'ca-added-msg';
            document.body.appendChild(el);
        }
        el.textContent = 'Added';
        el.hidden = false;
        clearTimeout(showAddedMsg._t);
        showAddedMsg._t = setTimeout(() => { el.hidden = true; }, 1400);
    }

    cartLines.addEventListener('click', (e) => {
        const btn = e.target.closest('.qty-btn');
        if (!btn) return;
        const i = Number(btn.dataset.i);
        const d = Number(btn.dataset.d);
        if (!cart[i]) return;
        cart[i].qty += d;
        if (cart[i].qty <= 0) cart.splice(i, 1);
        render();
    });

    document.getElementById('clearCartBtn')?.addEventListener('click', () => {
        cart.length = 0;
        render();
    });

    document.querySelectorAll('#fulfillGroup .fulfill-opt').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('#fulfillGroup .fulfill-opt').forEach(b => b.classList.remove('is-on'));
            btn.classList.add('is-on');
            shipMethod.value = btn.dataset.value;
            shipDetails.value = btn.dataset.value;
        });
    });

    if (locationSel) {
        locationSel.addEventListener('change', () => { locationId.value = locationSel.value; });
        locationId.value = locationSel.value;
    }

    async function searchProducts(q) {
        q = (q || '').trim();
        if (!q) { results.classList.add('hidden'); results.innerHTML = ''; return; }
        const url = api + '?q=' + encodeURIComponent(q) + '&location_id=' + encodeURIComponent(locationId.value);
        const rows = await fetch(url, { headers: { 'Accept': 'application/json' } }).then(r => r.json()).catch(() => []);
        results.innerHTML = '';
        if (!rows.length) {
            results.innerHTML = '<div class="ca-card text-sm text-slate-400">No products</div>';
            results.classList.remove('hidden');
            return;
        }
        if (rows.length === 1 && String(rows[0].sku || '').toLowerCase() === q.toLowerCase()) {
            addItem(rows[0]);
            search.value = '';
            results.classList.add('hidden');
            return;
        }
        rows.forEach(r => {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'ca-card w-full text-left';
            b.innerHTML = '<div class="font-bold text-sm"></div><div class="text-xs text-slate-500"></div>';
            b.querySelector('.font-bold').textContent = r.name;
            b.querySelector('.text-xs').textContent = money(r.price) + ' · SKU ' + (r.sku || '-');
            b.onclick = () => { addItem(r); search.value = ''; results.classList.add('hidden'); };
            results.appendChild(b);
        });
        results.classList.remove('hidden');
    }

    search.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(() => searchProducts(search.value), 250);
    });
    search.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') { e.preventDefault(); searchProducts(search.value); }
    });

    document.getElementById('camScanBtn')?.addEventListener('click', () => {
        if (!window.CustomerCameraScan) {
            alert('Camera scanner is not available.');
            return;
        }
        window.CustomerCameraScan.start((code) => {
            search.value = code;
            searchProducts(code);
        });
    });

    // —— Product Catalog ——
    function openCatalog() {
        catalogModal.hidden = false;
        catalogStack = [{ level: 'cats', title: 'Categories' }];
        renderCatalog();
    }
    function closeCatalog() { catalogModal.hidden = true; }

    async function ensureCatalogTree() {
        if (!catalogTree) {
            catalogTree = await fetch(catApi, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json()).catch(() => []);
        }
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
                b.className = 'ca-catalog__row';
                b.innerHTML = '<span class="font-bold">' + esc(cat.name) + '</span><span class="ca-catalog__chev">›</span>';
                b.onclick = () => {
                    if ((cat.sub_categories || []).length) {
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
            allBtn.className = 'ca-catalog__row ca-catalog__row--all';
            allBtn.innerHTML = '<span>All in ' + esc(state.title) + '</span><span class="ca-catalog__chev">›</span>';
            allBtn.onclick = () => {
                catalogStack.push({ level: 'products', title: state.title, catId: state.catId, subId: 0 });
                renderCatalog();
            };
            catalogBody.appendChild(allBtn);
            (state.subs || []).forEach(sub => {
                const b = document.createElement('button');
                b.type = 'button';
                b.className = 'ca-catalog__row';
                b.innerHTML = '<span class="font-bold">' + esc(sub.name) + '</span><span class="ca-catalog__chev">›</span>';
                b.onclick = () => {
                    catalogStack.push({ level: 'products', title: sub.name, catId: state.catId, subId: sub.id });
                    renderCatalog();
                };
                catalogBody.appendChild(b);
            });
            return;
        }

        let url = api + '?location_id=' + encodeURIComponent(locationId.value) + '&limit=80';
        if (state.subId) url += '&sub_category_id=' + state.subId;
        else if (state.catId) url += '&category_id=' + state.catId;
        const rows = await fetch(url, { headers: { 'Accept': 'application/json' } }).then(r => r.json()).catch(() => []);
        renderCatalogProducts(rows);
    }

    function renderCatalogProducts(rows) {
        if (!rows.length) {
            catalogBody.innerHTML = '<div class="px-3 py-4 text-sm text-slate-400">No products</div>';
            return;
        }
        catalogBody.innerHTML = '';
        rows.forEach(r => {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'ca-catalog__row ca-catalog__prod';
            const qty = cartQty(r.variation_id);
            const img = r.image
                ? '<img src="' + esc(r.image) + '" alt="">'
                : '<div class="w-12 h-12 rounded-xl bg-slate-100 flex-shrink-0"></div>';
            b.innerHTML =
                img +
                '<div class="min-w-0 flex-1"><div class="font-bold text-sm truncate">' + esc(r.name) + '</div>' +
                '<div class="text-xs text-slate-500">' + money(r.price) + (r.sku ? ' · ' + esc(r.sku) : '') + '</div></div>' +
                '<span class="ca-catalog__add">' + (qty || '+') + '</span>';
            b.onclick = (e) => {
                e.preventDefault();
                addItem(r);
                const addEl = b.querySelector('.ca-catalog__add');
                if (addEl) addEl.textContent = String(cartQty(r.variation_id) || '+');
            };
            catalogBody.appendChild(b);
        });
    }

    document.getElementById('catalogOpenBtn')?.addEventListener('click', openCatalog);
    document.getElementById('catalogEmptyBtn')?.addEventListener('click', openCatalog);
    document.getElementById('catalogCloseBtn')?.addEventListener('click', closeCatalog);
    catalogBackBtn?.addEventListener('click', () => {
        if (catalogStack.length > 1) { catalogStack.pop(); renderCatalog(); }
    });
    catalogModal?.addEventListener('click', (e) => { if (e.target === catalogModal) closeCatalog(); });

    @if(request('add'))
    (async function preload() {
        const vid = @json((string) request('add'));
        const url = api + '?variation_id=' + encodeURIComponent(vid) + '&location_id=' + encodeURIComponent(locationId.value);
        const rows = await fetch(url, { headers: { 'Accept': 'application/json' } }).then(r => r.json()).catch(() => []);
        if (rows[0]) addItem(rows[0]);
    })();
    @endif

    render();
})();
</script>
@endpush
