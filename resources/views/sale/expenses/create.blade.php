@extends('sale.layout')
@section('title', 'Add Expense')
@section('header', 'Add Expense')
@section('content')
{{-- Fields match existing admin Expense (TransactionUtil::createExpense) --}}
<div class="sale-exp-form">
    <p class="text-sm text-slate-500 mb-3">Same expense fields as your system. Fill details to add an expense.</p>

    <form method="POST" action="{{ route('sale.expenses.store') }}" enctype="multipart/form-data" class="space-y-3" id="saleExpenseForm">
        @csrf
        <input type="hidden" name="contact_id" id="contact_id" value="{{ old('contact_id', $selected_contact['id'] ?? '') }}">

        <div>
            <label class="text-xs font-bold text-slate-500 mb-1.5 block">Location *</label>
            <select name="location_id" id="location_id" class="sale-input" required>
                <option value="">Select location</option>
                @foreach($locations as $id => $name)
                    <option value="{{ $id }}" @selected((string) old('location_id', $current_location_id) === (string) $id)>{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-xs font-bold text-slate-500 mb-1.5 block">Expense Category</label>
            <select name="expense_category_id" id="expense_category_id" class="sale-input">
                <option value="">Please select</option>
                @foreach($categories as $id => $name)
                    <option value="{{ $id }}" @selected((string) old('expense_category_id') === (string) $id)>{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-xs font-bold text-slate-500 mb-1.5 block">Sub Category</label>
            <select name="expense_sub_category_id" id="expense_sub_category_id" class="sale-input">
                <option value="">Please select</option>
            </select>
        </div>

        <div>
            <label class="text-xs font-bold text-slate-500 mb-1.5 block">Reference No</label>
            <input type="text" name="ref_no" class="sale-input" placeholder="Leave empty to auto-generate" value="{{ old('ref_no') }}">
        </div>

        <div>
            <label class="text-xs font-bold text-slate-500 mb-1.5 block">Date *</label>
            <input type="date" name="transaction_date" class="sale-input" required value="{{ old('transaction_date', now()->toDateString()) }}">
        </div>

        <div class="sale-exp-customer">
            <div class="sale-exp-customer__head">
                <label class="text-xs font-bold text-slate-500">Expense for Customer</label>
                <button type="button" id="clearCustomerBtn" class="sale-exp-customer__clear {{ empty($selected_contact) ? 'hidden' : '' }}">Clear</button>
            </div>

            <button type="button" id="customerSelected" class="sale-exp-customer__chip {{ empty($selected_contact) ? 'hidden' : '' }}">
                <span class="sale-pick-avatar" id="customerAvatar">{{ strtoupper(mb_substr($selected_contact['text'] ?? 'C', 0, 1)) }}</span>
                <span class="sale-exp-customer__chip-meta min-w-0">
                    <span class="sale-exp-customer__chip-name truncate" id="customerLabel">{{ $selected_contact['text'] ?? '' }}</span>
                    <span class="sale-exp-customer__chip-hint">Tap to change</span>
                </span>
                <svg class="shrink-0 text-slate-400" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 9l6 6 6-6"/></svg>
            </button>

            <div id="customerSearchWrap" class="sale-exp-customer__search {{ !empty($selected_contact) ? 'hidden' : '' }}">
                <div class="sale-pick-search sale-exp-customer__searchbox">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                    <input type="search" id="customerSearch" class="sale-pick-search__input" placeholder="Search customer name / mobile" autocomplete="off">
                </div>
                <div id="customerResults" class="sale-exp-customer__list"></div>
            </div>
        </div>

        <div>
            <label class="text-xs font-bold text-slate-500 mb-1.5 block">Total Amount *</label>
            <input type="number" step="0.01" min="0.01" name="final_total" class="sale-input" required placeholder="0.00" value="{{ old('final_total') }}">
        </div>

        <div>
            <label class="text-xs font-bold text-slate-500 mb-1.5 block">Payment Method</label>
            <select name="payment_method" id="payment_method" class="sale-input">
                @forelse($payment_types as $key => $label)
                    <option value="{{ $key }}" @selected((string) old('payment_method', array_key_first($payment_types) ?: 'cash') === (string) $key)>{{ $label }}</option>
                @empty
                    <option value="cash">Cash</option>
                @endforelse
            </select>
            <p class="text-[11px] text-slate-400 mt-1 font-semibold">Only active methods for the selected location</p>
        </div>

        <div>
            <label class="text-xs font-bold text-slate-500 mb-1.5 block">Expense Note</label>
            <textarea name="additional_notes" class="sale-input" rows="3" placeholder="Optional note">{{ old('additional_notes') }}</textarea>
        </div>

        <div>
            <label class="sale-exp-upload">
                <input type="file" name="document" accept="image/*,.pdf" class="hidden" id="expenseDocument">
                <span class="sale-exp-upload__box">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 16V6"/><path d="m8 9 4-4 4 4"/><path d="M4 18h16"/></svg>
                    <strong>Attach Document</strong>
                    <small id="expenseDocName">Camera, Gallery or PDF</small>
                </span>
            </label>
        </div>

        <div class="grid grid-cols-2 gap-3 pt-2 pb-4">
            <a href="{{ route('sale.expenses') }}" class="sale-btn-ghost inline-flex items-center justify-center no-underline">Cancel</a>
            <button type="submit" class="sale-btn">Save Expense</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const subMap = @json($subCategories ?? []);
    const oldSub = @json(old('expense_sub_category_id'));
    const paymentByLocation = @json($payment_types_by_location ?? []);
    const catEl = document.getElementById('expense_category_id');
    const subEl = document.getElementById('expense_sub_category_id');
    const locationEl = document.getElementById('location_id');
    const paymentEl = document.getElementById('payment_method');

    function fillSubs(catId, selected) {
        subEl.innerHTML = '<option value="">Please select</option>';
        const rows = subMap[catId] || subMap[String(catId)] || [];
        rows.forEach(r => {
            const opt = document.createElement('option');
            opt.value = r.id;
            opt.textContent = r.name;
            if (selected && String(selected) === String(r.id)) opt.selected = true;
            subEl.appendChild(opt);
        });
    }

    catEl.addEventListener('change', () => fillSubs(catEl.value, null));
    if (catEl.value) fillSubs(catEl.value, oldSub);

    function fillPaymentMethods(locationId, keepValue) {
        const map = paymentByLocation[String(locationId)] || paymentByLocation[locationId] || {};
        const keys = Object.keys(map);
        const prev = keepValue || paymentEl.value;
        paymentEl.innerHTML = '';
        if (!keys.length) {
            const opt = document.createElement('option');
            opt.value = 'cash';
            opt.textContent = 'Cash';
            paymentEl.appendChild(opt);
            return;
        }
        keys.forEach(k => {
            const opt = document.createElement('option');
            opt.value = k;
            opt.textContent = map[k];
            if (String(prev) === String(k)) opt.selected = true;
            paymentEl.appendChild(opt);
        });
        if (![...paymentEl.options].some(o => o.selected)) {
            paymentEl.selectedIndex = 0;
        }
    }

    if (locationEl) {
        locationEl.addEventListener('change', () => fillPaymentMethods(locationEl.value, null));
        if (locationEl.value) fillPaymentMethods(locationEl.value, @json(old('payment_method')));
    }

    const contactId = document.getElementById('contact_id');
    const customerSearch = document.getElementById('customerSearch');
    const customerResults = document.getElementById('customerResults');
    const customerLabel = document.getElementById('customerLabel');
    const customerAvatar = document.getElementById('customerAvatar');
    const customerSelected = document.getElementById('customerSelected');
    const customerSearchWrap = document.getElementById('customerSearchWrap');
    const clearCustomerBtn = document.getElementById('clearCustomerBtn');
    let custTimer = null;

    function esc(s) {
        return String(s ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function setCustomer(id, text, initials) {
        contactId.value = id || '';
        if (id) {
            customerLabel.textContent = text || '';
            if (customerAvatar) customerAvatar.textContent = (initials || (text || 'C').charAt(0)).toUpperCase().slice(0, 2);
            customerSelected.classList.remove('hidden');
            customerSearchWrap.classList.add('hidden');
            clearCustomerBtn.classList.remove('hidden');
            customerSearch.value = '';
            customerResults.innerHTML = '';
        } else {
            customerLabel.textContent = '';
            if (customerAvatar) customerAvatar.textContent = 'C';
            customerSelected.classList.add('hidden');
            customerSearchWrap.classList.remove('hidden');
            clearCustomerBtn.classList.add('hidden');
            loadCustomers('');
        }
    }

    customerSelected.addEventListener('click', () => {
        customerSelected.classList.add('hidden');
        customerSearchWrap.classList.remove('hidden');
        clearCustomerBtn.classList.remove('hidden');
        customerSearch.focus();
        loadCustomers(customerSearch.value.trim());
    });
    clearCustomerBtn.addEventListener('click', () => setCustomer('', '', 'C'));

    async function fetchCustomers(q) {
        const res = await fetch(@json(route('sale.api.customers')) + '?q=' + encodeURIComponent(q || ''), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        return Array.isArray(data) ? data : [];
    }

    function renderCustomerRows(rows) {
        customerResults.innerHTML = '';
        if (!rows.length) {
            customerResults.innerHTML = '<div class="sale-pick-empty">No customers found</div>';
            return;
        }
        rows.forEach(r => {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'sale-pick-row';
            const name = r.display_name || r.text || r.name || '';
            const sub = r.address || r.mobile || '';
            b.innerHTML =
                '<span class="sale-pick-avatar">' + esc(r.initials || 'C') + '</span>' +
                '<span class="sale-pick-meta">' +
                    '<span class="sale-pick-name">' + esc(name) + '</span>' +
                    (sub ? '<span class="sale-pick-addr">' + esc(sub) + '</span>' : '') +
                '</span>' +
                '<span class="sale-pick-chev">›</span>';
            b.onclick = () => setCustomer(r.id, r.text || name, r.initials);
            customerResults.appendChild(b);
        });
    }

    async function loadCustomers(q) {
        customerResults.innerHTML = '<div class="sale-pick-empty">Loading…</div>';
        try {
            const rows = await fetchCustomers(q);
            renderCustomerRows(rows);
        } catch (e) {
            customerResults.innerHTML = '<div class="sale-pick-empty">Could not load customers</div>';
        }
    }

    customerSearch.addEventListener('input', () => {
        clearTimeout(custTimer);
        custTimer = setTimeout(() => loadCustomers(customerSearch.value.trim()), 250);
    });
    customerSearch.addEventListener('focus', () => {
        if (!customerResults.children.length) loadCustomers(customerSearch.value.trim());
    });

    if (!contactId.value) {
        loadCustomers('');
    }

    const docInput = document.getElementById('expenseDocument');
    const docName = document.getElementById('expenseDocName');
    if (docInput && docName) {
        docInput.addEventListener('change', () => {
            docName.textContent = docInput.files && docInput.files[0] ? docInput.files[0].name : 'Camera, Gallery or PDF';
        });
    }
})();
</script>
@endpush
