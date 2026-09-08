@extends('sale.layout')
@section('title', 'Catalog')
@section('header', 'Catalog')
@section('content')
<input type="hidden" id="prodLoc" value="{{ $default_location }}">

<div class="sale-prod-app">
    <div class="sale-prod-app__toolbar">
        <div class="sale-prod-app__search">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3-3"/></svg>
            <input type="search" id="prodQ" placeholder="Search name / SKU" autocomplete="off">
            <button type="button" id="prodFilterBtn" class="sale-prod-app__filter-btn" aria-label="Filter" onclick="return saleOpenSheet('prodFilterSheet', event)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M7 12h10M10 18h4"/></svg>
                <span id="prodFilterDot" class="sale-prod-app__dot hidden"></span>
            </button>
        </div>

        <div class="sale-prod-app__chips" id="prodCatChips">
            <button type="button" class="sale-chip active" data-cat="">All</button>
            @foreach($categories as $cat)
                <button type="button" class="sale-chip" data-cat="{{ $cat['id'] }}">{{ $cat['name'] }}</button>
            @endforeach
        </div>

        <div class="sale-prod-app__chips sale-prod-app__chips--sub hidden" id="prodSubChips"></div>

        <div class="sale-prod-app__meta">
            <span id="prodCount">0 products</span>
            <button type="button" id="prodClearFilters" class="sale-prod-app__clear hidden">Clear filters</button>
        </div>
    </div>

    <div id="prodList" class="sale-prod-app__list"></div>
    <p id="prodEmpty" class="sale-prod-app__empty hidden">No products found</p>
</div>

{{-- Filter bottom sheet --}}
<div id="prodFilterSheet" class="sale-sheet" hidden>
    <div class="sale-sheet__panel">
        <div class="sale-sheet__head is-center">
            <div class="sale-sheet__title">Filter</div>
            <button type="button" class="sale-sheet__close" id="prodFilterClose" onclick="saleCloseSheet('prodFilterSheet')" aria-label="Close">×</button>
        </div>
        <div class="sale-sheet__body sale-filter-body">
            <label class="sale-filter-label">Category</label>
            <select id="prodCat" class="sale-input sale-filter-select">
                <option value="">All categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat['id'] }}">{{ $cat['name'] }}</option>
                @endforeach
            </select>
            <label class="sale-filter-label">Subcategory</label>
            <select id="prodSub" class="sale-input sale-filter-select" disabled>
                <option value="">All</option>
            </select>
        </div>
        <div class="sale-sheet__foot sale-filter-foot">
            <button type="button" id="prodFilterReset" class="sale-more-cancel">RESET</button>
            <button type="button" id="prodFilterApply" class="sale-btn sale-filter-apply">Apply</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const categories = @json($categoriesJson);
    const loc = document.getElementById('prodLoc');
    const q = document.getElementById('prodQ');
    const cat = document.getElementById('prodCat');
    const sub = document.getElementById('prodSub');
    const list = document.getElementById('prodList');
    const empty = document.getElementById('prodEmpty');
    const catChips = document.getElementById('prodCatChips');
    const subChips = document.getElementById('prodSubChips');
    const countEl = document.getElementById('prodCount');
    const clearBtn = document.getElementById('prodClearFilters');
    const filterDot = document.getElementById('prodFilterDot');
    const sheet = document.getElementById('prodFilterSheet');
    let timer = null;

    function money(n) { return '$' + (Number(n) || 0).toFixed(2); }

    function syncCatChips() {
        catChips.querySelectorAll('.sale-chip').forEach(chip => {
            chip.classList.toggle('active', String(chip.dataset.cat || '') === String(cat.value || ''));
        });
    }

    function fillSubs(keepValue) {
        const id = cat.value;
        const prev = keepValue ? sub.value : '';
        sub.innerHTML = '<option value="">All</option>';
        subChips.innerHTML = '';
        if (!id) {
            sub.disabled = true;
            subChips.classList.add('hidden');
            return;
        }
        const found = categories.find(c => String(c.id) === String(id));
        const subs = found ? found.sub_categories : [];
        if (!subs.length) {
            sub.disabled = true;
            subChips.classList.add('hidden');
            return;
        }
        sub.disabled = false;
        const allChip = document.createElement('button');
        allChip.type = 'button';
        allChip.className = 'sale-chip' + (!prev ? ' active' : '');
        allChip.dataset.sub = '';
        allChip.textContent = 'All';
        subChips.appendChild(allChip);
        subs.forEach(s => {
            const o = document.createElement('option');
            o.value = s.id;
            o.textContent = s.name;
            sub.appendChild(o);
            const chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'sale-chip' + (String(prev) === String(s.id) ? ' active' : '');
            chip.dataset.sub = s.id;
            chip.textContent = s.name;
            subChips.appendChild(chip);
        });
        if (prev && [...sub.options].some(o => String(o.value) === String(prev))) {
            sub.value = prev;
        } else {
            sub.value = '';
        }
        subChips.classList.remove('hidden');
        syncSubChips();
    }

    function syncSubChips() {
        subChips.querySelectorAll('.sale-chip').forEach(chip => {
            chip.classList.toggle('active', String(chip.dataset.sub || '') === String(sub.value || ''));
        });
    }

    function updateFilterUi() {
        const active = !!(cat.value || sub.value || q.value.trim());
        filterDot.classList.toggle('hidden', !(cat.value || sub.value));
        clearBtn.classList.toggle('hidden', !active);
        syncCatChips();
        syncSubChips();
    }

    async function load() {
        updateFilterUi();
        if (!loc.value) {
            list.innerHTML = '';
            empty.classList.remove('hidden');
            empty.textContent = 'Set a default location in Account first';
            countEl.textContent = '0 products';
            return;
        }
        let url = @json(route('sale.api.products')) + '?location_id=' + encodeURIComponent(loc.value) + '&limit=80';
        const term = q.value.trim();
        if (term) url += '&q=' + encodeURIComponent(term);
        if (sub.value) url += '&sub_category_id=' + encodeURIComponent(sub.value);
        else if (cat.value) url += '&category_id=' + encodeURIComponent(cat.value);

        const res = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
        const rows = await res.json();
        list.innerHTML = '';
        countEl.textContent = rows.length + (rows.length === 1 ? ' product' : ' products');
        if (!rows.length) {
            empty.classList.remove('hidden');
            empty.textContent = 'No products found';
            return;
        }
        empty.classList.add('hidden');
        rows.forEach(r => {
            const el = document.createElement('div');
            el.className = 'sale-prod-card';
            const thumb = r.has_image
                ? `<img src="${r.image}" alt="" loading="lazy">`
                : `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="10.5" r="1.5"/><path d="m21 15-4.5-4.5L9 18"/></svg>`;
            const packs = Array.isArray(r.units) && r.units.length
                ? r.units.map(u => {
                    const m = Number(u.multiplier) || 1;
                    return '(' + u.name + ' x ' + m + ')';
                }).join(' ')
                : (r.unit_name ? '(' + r.unit_name + ' x 1)' : '');
            const inStock = Number(r.enable_stock) !== 1 || Number(r.stock) > 0;
            el.innerHTML = `
                <div class="sale-prod-card__media">
                    <div class="sale-prod-card__thumb ${r.has_image ? '' : 'is-placeholder'}">${thumb}</div>
                    <span class="sale-prod-card__stock ${inStock ? 'in' : 'out'}">${inStock ? 'In Stock' : 'Out Stock'}</span>
                </div>
                <div class="sale-prod-card__info">
                    <div class="sale-prod-card__name">${r.name}</div>
                    <div class="sale-prod-card__price">List Price: ${money(r.price)}</div>
                    ${packs ? `<div class="sale-prod-card__units">${packs}</div>` : ''}
                    ${r.sku ? `<div class="sale-prod-card__code">Code: ${r.sku}</div>` : ''}
                </div>
                <a class="sale-prod-card__add" href="${@json(route('sale.orders.create'))}?add=${r.variation_id}">Add</a>`;
            list.appendChild(el);
        });
    }

    catChips.addEventListener('click', (e) => {
        const chip = e.target.closest('.sale-chip');
        if (!chip) return;
        cat.value = chip.dataset.cat || '';
        fillSubs(false);
        load();
    });

    subChips.addEventListener('click', (e) => {
        const chip = e.target.closest('.sale-chip');
        if (!chip) return;
        sub.value = chip.dataset.sub || '';
        load();
    });

    document.getElementById('prodFilterBtn').addEventListener('click', (e) => {
        e.preventDefault();
        if (typeof saleOpenSheet === 'function') saleOpenSheet('prodFilterSheet', e);
        else { sheet.hidden = false; sheet.classList.add('is-open'); }
    });
    document.getElementById('prodFilterClose').addEventListener('click', () => {
        if (typeof saleCloseSheet === 'function') saleCloseSheet('prodFilterSheet');
        else { sheet.hidden = true; sheet.classList.remove('is-open'); }
    });
    sheet.addEventListener('click', (e) => {
        if (e.target === sheet) {
            if (typeof saleCloseSheet === 'function') saleCloseSheet('prodFilterSheet');
            else { sheet.hidden = true; sheet.classList.remove('is-open'); }
        }
    });
    document.getElementById('prodFilterApply').addEventListener('click', () => {
        fillSubs(true);
        if (typeof saleCloseSheet === 'function') saleCloseSheet('prodFilterSheet');
        else { sheet.hidden = true; sheet.classList.remove('is-open'); }
        load();
    });
    document.getElementById('prodFilterReset').addEventListener('click', () => {
        cat.value = '';
        fillSubs(false);
        q.value = '';
        if (typeof saleCloseSheet === 'function') saleCloseSheet('prodFilterSheet');
        else { sheet.hidden = true; sheet.classList.remove('is-open'); }
        load();
    });
    clearBtn.addEventListener('click', () => {
        cat.value = '';
        fillSubs(false);
        q.value = '';
        load();
    });

    cat.addEventListener('change', () => fillSubs(false));
    q.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(load, 250);
    });

    fillSubs(false);
    load();
})();
</script>
@endpush
