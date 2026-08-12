@extends('sale.layout')
@section('title', 'Create order')
@section('header', 'Create order')
@section('content')
<form method="POST" action="{{ route('sale.orders.store') }}" id="saleOrderForm" class="sale-create-form">
    @csrf
    <input type="hidden" name="contact_id" id="contact_id" value="{{ old('contact_id') }}" required>
    <div id="productsJson" class="hidden"></div>

    <div class="sale-layout-create">
        <div class="sale-create-main sale-stack">
            <div class="sale-card space-y-3">
                <div class="sale-sec-title !mb-1">
                    <span class="sale-sec-title__ico">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c1.5-4 14.5-4 16 0"/></svg>
                    </span>
                    Customer & location
                </div>
                <div class="sale-layout-fields space-y-3 lg:space-y-0">
                    <div>
                        <label class="text-xs font-bold text-slate-500 mb-1.5 flex items-center gap-1.5">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s7-4.5 7-11a7 7 0 1 0-14 0c0 6.5 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                            Location
                        </label>
                        <select name="location_id" id="location_id" class="sale-input" required>
                            @foreach($locations as $id => $name)
                                <option value="{{ $id }}" @selected((string) old('location_id', $default_location) === (string) $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sale-field-full">
                        <label class="text-xs font-bold text-slate-500 mb-1.5 flex items-center gap-1.5">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c1.5-4 14.5-4 16 0"/></svg>
                            Customer
                        </label>
                        <div id="customerLabel" class="sale-input text-slate-400 mb-2 hidden"></div>
                        <input type="search" id="customerSearch" class="sale-input" placeholder="Search customer name / mobile" autocomplete="off">
                        <div id="customerResults" class="mt-2 border border-sale-line rounded-xl bg-white max-h-48 overflow-auto hidden"></div>
                    </div>
                </div>
            </div>

            <div class="sale-card sale-create-products space-y-3">
                <div class="sale-sec-title !mb-1">
                    <span class="sale-sec-title__ico">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.3 7 12 12l8.7-5M12 22V12"/></svg>
                    </span>
                    Products
                </div>
                <input type="search" id="productSearch" class="sale-input" placeholder="Search product name / SKU" autocomplete="off">
                <div id="productResults" class="border border-sale-line rounded-xl bg-white max-h-48 lg:max-h-72 overflow-auto hidden"></div>
                <div id="cartLines" class="space-y-2 flex-1"></div>
            </div>
        </div>

        <div class="sale-create-side">
            <div class="sale-card space-y-3 sale-sticky-panel">
                <div class="sale-sec-title !mb-2">
                    <span class="sale-sec-title__ico">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18"/></svg>
                    </span>
                    Payment
                </div>
                <div class="flex justify-between items-center font-extrabold text-base lg:text-lg">
                    <span>Total</span>
                    <span id="cartTotal" class="tabular-nums text-sale">$0.00</span>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 mb-1.5 block">Payment type</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="sale-card !p-3 flex items-center gap-2 cursor-pointer border-2 border-transparent has-[:checked]:border-sale">
                            <input type="radio" name="payment_type" value="cash" class="accent-sale">
                            <span class="text-sm font-bold">Paid (Cash)</span>
                        </label>
                        <label class="sale-card !p-3 flex items-center gap-2 cursor-pointer border-2 border-transparent has-[:checked]:border-sale">
                            <input type="radio" name="payment_type" value="due" checked class="accent-sale">
                            <span class="text-sm font-bold">Due later</span>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-500 mb-1.5 block">Note (optional)</label>
                    <textarea name="sale_note" rows="3" class="sale-input" placeholder="Order note">{{ old('sale_note') }}</textarea>
                </div>
                <button type="submit" class="sale-btn !w-full inline-flex items-center justify-center gap-2" id="submitOrderBtn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                    Create order
                </button>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
(function () {
    const cart = [];
    const customerSearch = document.getElementById('customerSearch');
    const customerResults = document.getElementById('customerResults');
    const customerLabel = document.getElementById('customerLabel');
    const contactId = document.getElementById('contact_id');
    const productSearch = document.getElementById('productSearch');
    const productResults = document.getElementById('productResults');
    const cartLines = document.getElementById('cartLines');
    const cartTotal = document.getElementById('cartTotal');
    const locationId = document.getElementById('location_id');
    const form = document.getElementById('saleOrderForm');
    const productsJson = document.getElementById('productsJson');
    let custTimer = null, prodTimer = null;

    function money(n) { return '$' + (Number(n) || 0).toFixed(2); }

    function renderCart() {
        cartLines.innerHTML = '';
        let total = 0;
        cart.forEach((line, idx) => {
            const lineTot = line.quantity * line.unit_price;
            total += lineTot;
            const el = document.createElement('div');
            el.className = 'rounded-xl border border-slate-200 bg-slate-50 p-3';
            el.innerHTML = `
                <div class="flex justify-between gap-2">
                    <div class="min-w-0">
                        <div class="font-bold text-sm truncate">${line.name}</div>
                        <div class="text-xs text-slate-500 mt-0.5">${money(line.unit_price)} each</div>
                    </div>
                    <button type="button" data-rm="${idx}" class="text-rose-600 text-xs font-bold">Remove</button>
                </div>
                <div class="flex items-center gap-2 mt-2">
                    <button type="button" data-dec="${idx}" class="h-9 w-9 rounded-lg bg-white border border-slate-200 font-bold">−</button>
                    <input type="number" min="0.01" step="0.01" value="${line.quantity}" data-qty="${idx}" class="sale-input !py-1.5 text-center font-bold max-w-[90px]">
                    <button type="button" data-inc="${idx}" class="h-9 w-9 rounded-lg bg-white border border-slate-200 font-bold">+</button>
                    <span class="ml-auto font-extrabold tabular-nums text-sm">${money(lineTot)}</span>
                </div>`;
            cartLines.appendChild(el);
        });
        cartTotal.textContent = money(total);

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
        return res.json();
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
                a.onclick = () => {
                    contactId.value = r.id;
                    customerLabel.textContent = r.text;
                    customerLabel.classList.remove('hidden', 'text-slate-400');
                    customerLabel.classList.add('text-sale', 'font-bold');
                    customerSearch.value = '';
                    customerResults.classList.add('hidden');
                };
                customerResults.appendChild(a);
            });
            customerResults.classList.remove('hidden');
        }, 250);
    });

    productSearch.addEventListener('input', () => {
        clearTimeout(prodTimer);
        prodTimer = setTimeout(async () => {
            const q = productSearch.value.trim();
            if (q.length < 1) { productResults.classList.add('hidden'); return; }
            const loc = locationId.value;
            const rows = await fetchJson(@json(route('sale.api.products')) + '?q=' + encodeURIComponent(q) + '&location_id=' + encodeURIComponent(loc));
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
                    const existing = cart.find(c => c.variation_id === r.variation_id);
                    if (existing) existing.quantity = +existing.quantity + 1;
                    else cart.push({
                        product_id: r.product_id,
                        variation_id: r.variation_id,
                        name: r.name,
                        unit_price: r.price,
                        quantity: 1,
                        enable_stock: r.enable_stock,
                        product_type: r.product_type,
                    });
                    productSearch.value = '';
                    productResults.classList.add('hidden');
                    renderCart();
                };
                productResults.appendChild(a);
            });
            productResults.classList.remove('hidden');
        }, 250);
    });

    form.addEventListener('submit', (e) => {
        if (!contactId.value) {
            e.preventDefault();
            alert('Select a customer');
            return;
        }
        if (!cart.length) {
            e.preventDefault();
            alert('Add at least one product');
            return;
        }
        productsJson.innerHTML = '';
        cart.forEach((line, i) => {
            ['product_id', 'variation_id', 'quantity', 'unit_price'].forEach(k => {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = `products[${i}][${k}]`;
                inp.value = line[k];
                productsJson.appendChild(inp);
            });
        });
    });

    renderCart();
})();
</script>
@endpush
