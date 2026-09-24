@php
    $posQkCatsJson = collect($categories ?? [])->map(function ($c) {
        return ['id' => (int) ($c['id'] ?? 0), 'name' => $c['name'] ?? ''];
    })->values();
    $posQkLayoutsJson = collect($posQuickLayouts ?? [])->map(function ($l) {
        return [
            'id' => (int) $l->id,
            'name' => $l->name,
            'is_current' => (int) $l->is_current === 1,
            'keep_folder_open' => (bool) $l->keep_folder_open,
            'keys' => $l->keys ?: [],
        ];
    })->values();
@endphp
<div class="modal fade no-print" id="pos_quick_keys_modal" tabindex="-1" role="dialog" aria-labelledby="posQuickKeysTitle">
<script>
window.__posQkCats = @json($posQkCatsJson);
window.__posQkLayouts = @json($posQkLayoutsJson);
</script>
    <div class="modal-dialog pos-qk-dialog" role="document" id="posQkDialog">
        <div class="modal-content pos-qk-sheet">
            <div class="pos-qk-top">
                <div>
                    <div class="pos-qk-kicker" id="posQkKicker" hidden></div>
                    <h4 class="modal-title" id="posQuickKeysTitle">Setting</h4>
                    <p class="pos-qk-sub" id="posQkHint">Choose which categories show on the POS. Add, edit, copy or delete a layout.</p>
                </div>
                <div class="pos-qk-top-actions">
                    <button type="button" class="pos-qk-done" id="posQkBackBtn" hidden>Back</button>
                    <button type="button" class="pos-qk-done" id="posQkSaveBtn" hidden>Done</button>
                    <button type="button" class="pos-qk-close" data-dismiss="modal" aria-label="Close" id="posQkDoneBtn">&times;</button>
                </div>
            </div>
            <div class="pos-qk-body" id="posQkBody">
                <div id="posQkListPane">
                    <section class="pos-qk-block">
                        <label class="pos-qk-enable">
                            <span>
                                <strong>Enable for this register</strong>
                            </span>
                            <span class="pos-qk-switch">
                                <input type="checkbox" id="posQkEnabled" {{ !empty($posQuickKeysEnabled) ? 'checked' : '' }}>
                                <i></i>
                            </span>
                        </label>
                        <button type="button" class="pos-qk-add" id="posQkAddBtn">Add layout</button>
                        <div id="posQkLayouts" class="pos-qk-layouts">
                            @forelse(($posQuickLayouts ?? []) as $layout)
                                <div class="pos-qk-layout {{ !empty($layout->is_current) ? 'is-current' : '' }}">
                                    <div class="pos-qk-layout__name">
                                        <strong>{{ $layout->name }}</strong>
                                        @if(!empty($layout->is_current))
                                            <span class="pos-qk-badge">Current layout</span>
                                        @endif
                                    </div>
                                    <div class="pos-qk-acts">
                                        @if(empty($layout->is_current))
                                            <button type="button" class="pos-qk-text" data-act="current" data-id="{{ $layout->id }}">Set as current layout</button>
                                        @endif
                                        <button type="button" class="pos-qk-ico" data-act="edit" data-id="{{ $layout->id }}" title="Edit"><i class="fas fa-pen"></i></button>
                                        <button type="button" class="pos-qk-ico" data-act="copy" data-id="{{ $layout->id }}" title="Copy"><i class="fas fa-copy"></i></button>
                                        <button type="button" class="pos-qk-ico pos-qk-text--danger" data-act="del" data-id="{{ $layout->id }}" title="Delete"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </div>
                            @empty
                                <p class="pos-qk-mini">No layouts yet. Click Add layout, then Edit or Delete from this list.</p>
                            @endforelse
                        </div>
                        <p class="pos-print-sale-modal__status" id="posQkStatus" hidden></p>
                    </section>
                </div>
                <div id="posQkEditPane" hidden>
                    <input type="hidden" id="posQkEditId">
                    <section class="pos-qk-block">
                        <h2>General</h2>
                        <label class="pos-qk-field">Layout name
                            <input type="text" id="posQkName" class="form-control" placeholder="QUICK ITEMS">
                        </label>
                        <label class="pos-qk-check">
                            <input type="checkbox" id="posQkKeepOpen" checked>
                            Leave selected folder open until end of the sale
                        </label>
                    </section>
                    <section class="pos-qk-block">
                        <h2>Add to layout</h2>
                        <p>Top slots are <strong>category</strong> tabs. Bottom grid is for <strong>products</strong>. Layout becomes current when you edit it.</p>
                        <label class="pos-qk-field">Search categories or products
                            <input type="search" id="posQkSearch" class="form-control" placeholder="Start typing or scanning" autocomplete="off">
                        </label>
                        <div id="posQkSearchResults" class="pos-qk-cats"></div>
                        <div class="pos-qk-colors" id="posQkColors">
                            @foreach(['#3a3f4b','#e67e22','#2980b9','#e53935','#16a34a','#7c3aed','#db2777','#0f766e'] as $c)
                                <button type="button" class="pos-qk-dot{{ $c === '#3a3f4b' ? ' is-on' : '' }}" data-color="{{ $c }}" style="background:{{ $c }}"></button>
                            @endforeach
                        </div>
                        <div class="pos-qk-zone-label">Category tabs</div>
                        <div class="pos-qk-grid pos-qk-grid--cats" id="posQkGridCats"></div>
                        <div class="pos-qk-zone-label" style="margin-top:14px;">Products</div>
                        <div class="pos-qk-grid" id="posQkGrid"></div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    if (window.__posQkBound) return;
    window.__posQkBound = true;

    var CAT_SLOTS = 8;
    var PRODUCT_SLOTS = 26;
    var SLOTS = CAT_SLOTS + PRODUCT_SLOTS;
    var PALETTE = ['#3a3f4b','#e67e22','#2980b9','#e53935','#16a34a','#7c3aed','#db2777','#0f766e'];
    var DEFAULT_PRODUCT_IMG = @json(asset('/img/default.png'));
    var data = { enabled: true, layouts: window.__posQkLayouts || [] };
    var picked = null;
    var color = '#3a3f4b';
    var lastCatId = null;
    var dragFrom = null;
    var cats = [];
    var searchTimer = null;
    var activeManualId = null;
    var looseProductCache = [];
    function syncActiveFolderFromProductGrid() {}
    function loadFolderIntoProductGrid() {}
    function updateProdHint() {}

    function csrf() {
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }
    function base() {
        return @json(url('/'));
    }
    function jsonHeaders() {
        return { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
    }
    function status(msg, err) {
        var el = document.getElementById('posQkStatus');
        if (!el) return;
        if (!msg) { el.hidden = true; el.textContent = ''; return; }
        el.hidden = false;
        el.textContent = msg;
        el.classList.toggle('is-error', !!err);
        el.classList.toggle('is-ok', !err);
    }
    function findLayout(id) {
        return (data.layouts || []).filter(function (l) { return String(l.id) === String(id); })[0] || null;
    }
    function openEditById(id) {
        // Editing a layout always makes it the active/current layout.
        post('/pos/quick-keys/' + id + '/current', {}).then(function (j) {
            if (j && j.layouts) data = j;
            if (j && typeof j.enabled !== 'undefined') data.enabled = !!j.enabled;
            renderList();
            applyPosLayout();
            var lay = findLayout(id);
            if (lay) {
                openEditor(lay);
                return;
            }
            return loadList().then(function () {
                var found = findLayout(id);
                if (found) openEditor(found);
                else status('Could not open layout', true);
            });
        }).catch(function () {
            var lay = findLayout(id);
            if (lay) openEditor(lay);
            else status('Could not open layout', true);
        });
    }
    function wide(on) {}
    function showList() {
        document.getElementById('posQkListPane').hidden = false;
        document.getElementById('posQkEditPane').hidden = true;
        document.getElementById('posQkBackBtn').hidden = true;
        document.getElementById('posQkSaveBtn').hidden = true;
        document.getElementById('posQkDoneBtn').hidden = false;
        document.getElementById('posQkKicker').hidden = true;
        document.getElementById('posQuickKeysTitle').textContent = 'Setting';
        document.getElementById('posQkHint').textContent = 'Choose which categories show on the POS. Add, edit, copy or delete a layout.';
        wide(false);
    }
    function showEdit() {
        document.getElementById('posQkListPane').hidden = true;
        document.getElementById('posQkEditPane').hidden = false;
        document.getElementById('posQkBackBtn').hidden = false;
        document.getElementById('posQkSaveBtn').hidden = false;
        document.getElementById('posQkDoneBtn').hidden = true;
        document.getElementById('posQkKicker').hidden = true;
        document.getElementById('posQuickKeysTitle').textContent = 'Edit layout';
        document.getElementById('posQkHint').textContent = 'Rename, reposition and recolor keys, or organize products into folders.';
        wide(true);
    }
    function isCurrent(l) {
        return !!(l && (l.is_current === true || l.is_current === 1 || l.is_current === '1'));
    }
    function currentLayoutKeys() {
        var current = (data.layouts || []).filter(isCurrent)[0];
        if (!data.enabled || !current || !current.keys) return { categories: [], products: [] };
        var catsMap = {};
        (window.__posQkCats || []).forEach(function (c) { catsMap[String(c.id)] = c; });
        var categories = [];
        var products = [];
        current.keys.slice().sort(function (a, b) {
            return (parseInt(a.slot, 10) || 0) - (parseInt(b.slot, 10) || 0);
        }).forEach(function (k) {
            var type = k.type || (k.variation_id ? 'product' : 'category');
            // Ignore legacy manual folders
            if (type === 'manual') return;
            if (type === 'product' && k.variation_id) {
                products.push({
                    variation_id: k.variation_id,
                    name: k.label || ('#' + k.variation_id),
                    color: k.color || ''
                });
            } else if (type === 'category' && k.category_id && categories.length < CAT_SLOTS) {
                var c = catsMap[String(k.category_id)];
                categories.push({
                    id: k.category_id,
                    name: k.label || (c ? c.name : ('#' + k.category_id)),
                    color: k.color || '',
                    count: k.count
                });
            }
        });
        return { categories: categories, products: products };
    }
    function renderQuickProducts(products) {
        var box = document.getElementById('product_list_items') || document.getElementById('product_list_body');
        if (!box) return false;
        window.__posQkQuickProducts = products || [];
        if (!products || !products.length) return false;
        var defaultImg = DEFAULT_PRODUCT_IMG;
        var colorByVid = {};
        var labelByVid = {};
        products.forEach(function (p) {
            colorByVid[String(p.variation_id)] = p.color || '';
            labelByVid[String(p.variation_id)] = p.name || '';
        });
        var ids = products.map(function (p) { return p.variation_id; }).join(',');
        box.innerHTML = '<div class="col-md-12 text-center" style="padding:16px;color:#9aa3b5;"><i class="fa fa-spinner fa-spin"></i></div>';
        fetch(base() + '/pos/quick-keys/products?ids=' + encodeURIComponent(ids), { credentials: 'same-origin', headers: jsonHeaders() })
            .then(function (r) { return r.json(); })
            .then(function (rows) {
                var list = Array.isArray(rows) && rows.length ? rows : products.map(function (p) {
                    return { variation_id: p.variation_id, name: p.name, image: defaultImg };
                });
                var html = list.map(function (p) {
                    var vid = p.variation_id;
                    var name = labelByVid[String(vid)] || p.name || ('#' + vid);
                    var img = p.image || defaultImg;
                    var color = colorByVid[String(vid)] || '';
                    var border = color ? ' style="outline:2px solid ' + color + ';"' : '';
                    return '<div class="col-md-3 col-xs-4 no-print !tw-px-[3px]">' +
                        '<div class="product_box tw-w-full tw-mb-1 tw-text-center tw-font-semibold tw-rounded-lg tw-p-1 tw-cursor-pointer pos-qk-quick-product"' + border +
                        ' data-variation_id="' + vid + '" title="' + escapeHtml(name) + '">' +
                        '<div class="image-container tw-mx-auto tw-w-full">' +
                        '<img class="product_tile_img" src="' + String(img).replace(/"/g, '') + '" alt="" loading="lazy" onerror="this.onerror=null;this.src=\'' + String(defaultImg).replace(/'/g, '') + '\';">' +
                        '</div>' +
                        '<div class="text_div"><span class="product_name">' + escapeHtml(name) + '</span></div>' +
                        '</div></div>';
                }).join('');
                box.innerHTML = html + '<input type="hidden" id="no_products_found">';
                if (typeof window.posSyncProductStockDisplay === 'function') {
                    window.posSyncProductStockDisplay();
                }
            })
            .catch(function () {
                var html = products.map(function (p) {
                    return '<div class="col-md-3 col-xs-4 no-print !tw-px-[3px]">' +
                        '<div class="product_box tw-w-full tw-mb-1 tw-text-center tw-font-semibold tw-rounded-lg tw-p-1 tw-cursor-pointer" data-variation_id="' + p.variation_id + '">' +
                        '<div class="image-container tw-mx-auto tw-w-full"><img class="product_tile_img" src="' + String(defaultImg).replace(/"/g, '') + '" alt=""></div>' +
                        '<div class="text_div"><span class="product_name">' + escapeHtml(p.name) + '</span></div></div></div>';
                }).join('');
                box.innerHTML = html + '<input type="hidden" id="no_products_found">';
            });
        var page = document.getElementById('suggestion_page');
        if (page) page.value = '1';
        return true;
    }
    function applyPosLayout() {
        var grid = document.querySelector('.pos-card-grid-categories');
        if (!grid) return;
        var allTile = grid.querySelector('.main-category[data-value="all"]');
        grid.querySelectorAll('.main-category-div.main-category, .main-category-div.qk-product-key, .main-category-div.qk-manual-folder').forEach(function (el) {
            if (el.getAttribute('data-value') !== 'all') el.remove();
        });
        var parts = currentLayoutKeys();
        var html = '';
        (parts.categories || []).forEach(function (c) {
            var style = c.color ? ' style="--qk-color:' + c.color + '"' : '';
            var custom = c.color ? ' pos-cat-tile--custom' : '';
            var badge = (c.count != null && c.count > 0) ? '<span class="pos-qk-count">' + c.count + '</span>' : '';
            html += '<div class="col-md-2 col-xs-4 tw-mb-2 tw-cursor-pointer main-category-div main-category no-print" data-value="' + c.id + '" data-name="' + String(c.name).replace(/"/g, '') + '" data-parent="0" style="padding-left: 4px; padding-right: 4px;">' +
                '<div class="pos-card cat pos-cat-tile pos-qk-folder' + custom + '"' + style + '>' + badge + '<h4 class="pos-card-name">' + escapeHtml(c.name) + '</h4></div></div>';
        });
        if (allTile) {
            allTile.insertAdjacentHTML('afterend', html);
        } else {
            grid.insertAdjacentHTML('afterbegin', html);
        }
        if (typeof window.pos_hide_selected_category === 'function') {
            window.pos_hide_selected_category();
        }
        if (typeof window.pos_set_active_category_tile === 'function') {
            window.pos_set_active_category_tile('all');
        } else {
            grid.querySelectorAll('.pos-card.cat').forEach(function (el) { el.classList.remove('is-active'); });
            var allCard = grid.querySelector('.main-category[data-value="all"] .pos-card.cat');
            if (allCard) allCard.classList.add('is-active');
        }
        if (typeof window.global_p_category_id !== 'undefined') {
            window.global_p_category_id = null;
        }
        if (renderQuickProducts(parts.products)) {
            return;
        }
        if (typeof window.get_product_suggestion_list === 'function' && document.querySelector('input#location_id')) {
            var loc = document.querySelector('input#location_id').value;
            window.get_product_suggestion_list(null, window.global_brand_id || null, loc, null);
        }
    }
    function escapeHtml(s) {
        return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
    function renderList() {
        var box = document.getElementById('posQkLayouts');
        var en = document.getElementById('posQkEnabled');
        if (en) en.checked = !!data.enabled;
        if (!box) return;
        if (!data.layouts.length) {
            box.innerHTML = '<p class="pos-qk-mini">No layouts yet. Add a layout to choose POS categories.</p>';
            return;
        }
        box.innerHTML = data.layouts.map(function (l) {
            var cur = isCurrent(l);
            return '<div class="pos-qk-layout' + (cur ? ' is-current' : '') + '">' +
                '<div class="pos-qk-layout__name"><strong>' + (l.name || 'Layout') + '</strong>' +
                (cur ? '<span class="pos-qk-badge">Current layout</span>' : '') +
                '</div>' +
                '<div class="pos-qk-acts">' +
                (cur ? '' : '<button type="button" class="pos-qk-text" data-act="current" data-id="' + l.id + '">Set as current layout</button>') +
                '<button type="button" class="pos-qk-ico" data-act="edit" data-id="' + l.id + '" title="Edit"><i class="fas fa-pen"></i></button>' +
                '<button type="button" class="pos-qk-ico" data-act="copy" data-id="' + l.id + '" title="Copy"><i class="fas fa-copy"></i></button>' +
                '<button type="button" class="pos-qk-ico pos-qk-text--danger" data-act="del" data-id="' + l.id + '" title="Delete"><i class="fas fa-trash-alt"></i></button>' +
                '</div></div>';
        }).join('');
    }
    function loadList() {
        return fetch(base() + '/pos/quick-keys', { credentials: 'same-origin', headers: jsonHeaders() })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (!j || !Array.isArray(j.layouts)) return;
                data = j;
                renderList();
            });
    }
    function colorForCat(id) {
        var n = parseInt(id, 10) || 0;
        return PALETTE[Math.abs(n) % PALETTE.length];
    }
    function usedColorMap() {
        var map = {};
        gridCells().forEach(function (el) {
            var d = readCell(el);
            if (d && d.type === 'category') map[d.category_id] = d.color || colorForCat(d.category_id);
        });
        return map;
    }
    function setPaletteOn(hex) {
        color = hex || color;
        document.querySelectorAll('#posQkColors .pos-qk-dot').forEach(function (d) {
            d.classList.toggle('is-on', d.getAttribute('data-color') === color);
        });
    }
    function usedCatIds() {
        var ids = {};
        gridCells().forEach(function (el) {
            var d = readCell(el);
            if (d && d.type === 'category') ids[d.category_id] = true;
        });
        return ids;
    }
    function nextEmptyCategory() {
        var cells = document.querySelectorAll('#posQkGridCats .qk-cell');
        for (var i = 0; i < cells.length; i++) {
            if (!readCell(cells[i])) return cells[i];
        }
        return null;
    }
    function nextEmptyProduct() {
        var cells = document.querySelectorAll('#posQkGrid .qk-cell');
        for (var i = 0; i < cells.length; i++) {
            if (!readCell(cells[i])) return cells[i];
        }
        return null;
    }
    function nextEmpty() {
        return nextEmptyProduct();
    }
    function cellForCategory(id) {
        var cells = document.querySelectorAll('#posQkGridCats .qk-cell');
        for (var i = 0; i < cells.length; i++) {
            var d = readCell(cells[i]);
            if (d && d.type === 'category' && d.category_id === id) return cells[i];
        }
        return null;
    }
    function cellForVariation(id) {
        var cells = document.querySelectorAll('#posQkGrid .qk-cell');
        for (var i = 0; i < cells.length; i++) {
            var d = readCell(cells[i]);
            if (d && d.type === 'product' && d.variation_id === id) return cells[i];
        }
        return null;
    }
    function usedVariationIds() {
        var ids = {};
        document.querySelectorAll('#posQkGrid .qk-cell').forEach(function (el) {
            var d = readCell(el);
            if (d && d.type === 'product') ids[d.variation_id] = true;
        });
        return ids;
    }
    function loadCats() {
        if (window.__posQkCats && window.__posQkCats.length) {
            cats = window.__posQkCats.map(function (c) {
                return { id: c.id, name: c.name, type: 'category', count: c.count };
            });
            return Promise.resolve();
        }
        return fetch(base() + '/pos/quick-keys/categories', { credentials: 'same-origin', headers: jsonHeaders() })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                cats = (j || []).map(function (c) {
                    return { id: c.id, name: c.name, type: 'category', count: c.count };
                });
            });
    }
    function runSearch(q) {
        q = (q || '').trim();
        var el = document.getElementById('posQkSearchResults');
        if (!el) return;
        if (!q) {
            var used = usedCatIds();
            var colors = usedColorMap();
            el.innerHTML = '<div class="pos-qk-mini" style="padding:8px 12px;">Category folders — click to add as tabs. Type above to find products.</div>' +
                (cats || []).slice(0, 40).map(function (c) {
                    var on = !!used[c.id];
                    var bg = on ? (colors[c.id] || colorForCat(c.id)) : '';
                    var st = bg ? ' style="background:' + bg + ';color:#fff;border-left:4px solid #fff;"' : '';
                    return '<button type="button" class="' + (on ? 'is-on' : '') + '" data-kind="category" data-id="' + c.id + '" data-name="' + escapeHtml(c.name).replace(/"/g, '') + '" data-count="' + (c.count || 0) + '"' + st + '>' +
                        (on ? '<span class="pos-qk-tick">✓</span> ' : '') +
                        '<i class="fa fa-folder-o"></i> ' + escapeHtml(c.name) +
                        (c.count ? ' <span class="pos-qk-mini-count">(' + c.count + ')</span>' : '') +
                        '</button>';
                }).join('') || '<div class="pos-qk-mini">No categories found.</div>';
            bindSearchButtons(el);
            return;
        }
        el.innerHTML = '<div class="pos-qk-mini">Searching…</div>';
        Promise.all([
            fetch(base() + '/pos/quick-keys/products?q=' + encodeURIComponent(q), { credentials: 'same-origin', headers: jsonHeaders() }).then(function (r) { return r.json(); }),
            Promise.resolve((cats || []).filter(function (c) {
                return (c.name || '').toLowerCase().indexOf(q.toLowerCase()) >= 0;
            }))
        ]).then(function (parts) {
            var products = parts[0] || [];
            var matchedCats = parts[1] || [];
            var usedV = usedVariationIds();
            var usedC = usedCatIds();
            var html = '';
            matchedCats.forEach(function (c) {
                var on = !!usedC[c.id];
                html += '<button type="button" class="' + (on ? 'is-on' : '') + '" data-kind="category" data-id="' + c.id + '" data-name="' + escapeHtml(c.name).replace(/"/g, '') + '" data-count="' + (c.count || 0) + '">' +
                    (on ? '<span class="pos-qk-tick">✓</span> ' : '') +
                    '<i class="fa fa-folder-o"></i> ' + escapeHtml(c.name) + ' <span class="pos-qk-mini-count">folder</span></button>';
            });
            products.forEach(function (p) {
                var on = !!usedV[p.variation_id];
                html += '<button type="button" class="' + (on ? 'is-on' : '') + '" data-kind="product" data-variation-id="' + p.variation_id + '" data-product-id="' + (p.product_id || '') + '" data-name="' + escapeHtml(p.name).replace(/"/g, '') + '">' +
                    (on ? '<span class="pos-qk-tick">✓</span> ' : '') +
                    '<i class="fa fa-cube"></i> ' + escapeHtml(p.name) +
                    (p.sku ? ' <span class="pos-qk-mini-count">' + escapeHtml(p.sku) + '</span>' : '') +
                    '</button>';
            });
            el.innerHTML = html || '<div class="pos-qk-mini">No products or folders match.</div>';
            bindSearchButtons(el);
        }).catch(function () {
            el.innerHTML = '<div class="pos-qk-mini">Search failed.</div>';
        });
    }
    function bindSearchButtons(el) {
        el.querySelectorAll('button').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var kind = btn.getAttribute('data-kind');
                if (kind === 'category') {
                    var id = parseInt(btn.getAttribute('data-id'), 10);
                    var label = btn.getAttribute('data-name');
                    var count = parseInt(btn.getAttribute('data-count'), 10) || 0;
                    var existing = cellForCategory(id);
                    if (existing) {
                        paintCell(existing, null);
                    } else {
                        var slot = nextEmptyCategory();
                        if (!slot) { status('Category tabs are full — remove one first', true); return; }
                        var useColor = colorForCat(id);
                        setPaletteOn(useColor);
                        paintCell(slot, { type: 'category', category_id: id, label: label, color: useColor, count: count });
                    }
                } else {
                    var vid = parseInt(btn.getAttribute('data-variation-id'), 10);
                    var pid = parseInt(btn.getAttribute('data-product-id'), 10) || null;
                    var pname = btn.getAttribute('data-name');
                    var existingP = cellForVariation(vid);
                    if (existingP && existingP.nodeType) {
                        paintCell(existingP, null);
                    } else if (!existingP) {
                        var slotP = nextEmptyProduct();
                        if (!slotP) { status('Product grid is full', true); return; }
                        setPaletteOn(color);
                        paintCell(slotP, { type: 'product', variation_id: vid, product_id: pid, label: pname, color: color });
                    }
                }
                runSearch(document.getElementById('posQkSearch').value);
            });
        });
    }
    function paintCats(q) { runSearch(q); }
    function gridCells() {
        return document.querySelectorAll('#posQkGridCats .qk-cell, #posQkGrid .qk-cell');
    }
    function readCell(el) {
        if (!el) return null;
        var type = el.getAttribute('data-type');
        if (!type && el.getAttribute('data-variation-id')) type = 'product';
        if (!type && el.getAttribute('data-manual-id')) type = 'manual';
        if (!type && el.getAttribute('data-category-id')) type = 'category';
        if (!type) return null;
        if (type === 'product') {
            return {
                slot: parseInt(el.getAttribute('data-slot'), 10),
                type: 'product',
                variation_id: parseInt(el.getAttribute('data-variation-id'), 10),
                product_id: parseInt(el.getAttribute('data-product-id'), 10) || null,
                label: el.getAttribute('data-label') || '',
                color: el.getAttribute('data-color') || '#3a3f4b'
            };
        }
        if (type === 'manual') {
            var products = [];
            try {
                var raw = el.getAttribute('data-products') || '';
                if (raw.indexOf('%') === 0 || raw.indexOf('%5B') === 0 || /%[0-9A-Fa-f]{2}/.test(raw)) {
                    raw = decodeURIComponent(raw);
                }
                products = JSON.parse(raw || '[]') || [];
            } catch (e) { products = []; }
            if ((!products || !products.length) && window.__posQkEditorFolders) {
                var mid = el.getAttribute('data-manual-id') || '';
                if (mid && window.__posQkEditorFolders[mid]) {
                    products = window.__posQkEditorFolders[mid].products || [];
                }
            }
            return {
                slot: parseInt(el.getAttribute('data-slot'), 10),
                type: 'manual',
                manual_id: el.getAttribute('data-manual-id') || '',
                label: el.getAttribute('data-label') || '',
                color: el.getAttribute('data-color') || '#3a3f4b',
                count: products.length,
                products: products
            };
        }
        return {
            slot: parseInt(el.getAttribute('data-slot'), 10),
            type: 'category',
            category_id: parseInt(el.getAttribute('data-category-id'), 10),
            label: el.getAttribute('data-label') || '',
            color: el.getAttribute('data-color') || '#3a3f4b',
            count: parseInt(el.getAttribute('data-count'), 10) || null
        };
    }
    function paintCell(el, d) {
        if (!d) {
            var oldMid = el.getAttribute('data-manual-id');
            el.className = 'qk-cell is-empty';
            ['data-type','data-category-id','data-manual-id','data-variation-id','data-product-id','data-label','data-color','data-count','data-products'].forEach(function (a) {
                el.removeAttribute(a);
            });
            el.style.background = '';
            el.draggable = false;
            el.innerHTML = '';
            if (oldMid && window.__posQkEditorFolders) delete window.__posQkEditorFolders[oldMid];
            return;
        }
        var type = d.type || (d.variation_id ? 'product' : (d.manual_id ? 'manual' : 'category'));
        el.className = 'qk-cell' + (type === 'product' ? ' is-product' : ' is-folder');
        el.setAttribute('data-type', type);
        el.setAttribute('data-label', d.label || '');
        el.setAttribute('data-color', d.color || '#3a3f4b');
        el.style.background = d.color || '#3a3f4b';
        el.draggable = true;
        var badge = '';
        if (type === 'category') {
            el.setAttribute('data-category-id', d.category_id);
            el.removeAttribute('data-manual-id');
            el.removeAttribute('data-variation-id');
            el.removeAttribute('data-product-id');
            el.removeAttribute('data-products');
            if (d.count != null) {
                el.setAttribute('data-count', d.count);
                badge = '<span class="qk-badge">' + d.count + '</span>';
            }
        } else if (type === 'manual') {
            el.setAttribute('data-manual-id', d.manual_id);
            el.removeAttribute('data-category-id');
            el.removeAttribute('data-variation-id');
            el.removeAttribute('data-product-id');
            var products = Array.isArray(d.products) ? d.products : [];
            // URI-encode so JSON quotes don't break the HTML attribute
            el.setAttribute('data-products', encodeURIComponent(JSON.stringify(products)));
            el.setAttribute('data-count', products.length);
            if (!window.__posQkEditorFolders) window.__posQkEditorFolders = {};
            window.__posQkEditorFolders[d.manual_id] = {
                manual_id: d.manual_id,
                label: d.label || '',
                color: d.color || '#3a3f4b',
                products: products
            };
            badge = '<span class="qk-badge">' + products.length + '</span>';
        } else {
            el.setAttribute('data-variation-id', d.variation_id);
            if (d.product_id) el.setAttribute('data-product-id', d.product_id);
            el.removeAttribute('data-category-id');
            el.removeAttribute('data-manual-id');
            el.removeAttribute('data-count');
            el.removeAttribute('data-products');
        }
        el.innerHTML = badge + '<button type="button" class="qk-x">&times;</button><span class="qk-label">' + escapeHtml(d.label || '') + '</span>';
    }
    function bindGrid() {
        gridCells().forEach(function (el) {
            el.onclick = function (e) {
                if (e.target.classList.contains('qk-x')) {
                    var was = readCell(el);
                    paintCell(el, null);
                    if (was && was.type === 'manual' && was.manual_id === activeManualId) {
                        activeManualId = null;
                        loadFolderIntoProductGrid(null);
                    } else if (el.closest('#posQkGrid') && activeManualId) {
                        syncActiveFolderFromProductGrid();
                    }
                    runSearch(document.getElementById('posQkSearch').value);
                    return;
                }
                gridCells().forEach(function (c) { c.classList.remove('is-on'); });
                el.classList.add('is-on');
                var d = readCell(el);
                if (d) {
                    lastCatId = d.type === 'category' ? d.category_id : null;
                    setPaletteOn(d.color || color);
                    if (d.type === 'manual') {
                        loadFolderIntoProductGrid(d.manual_id);
                    } else if (el.closest('#posQkGridCats')) {
                        loadFolderIntoProductGrid(null);
                    }
                }
            };
            el.ondragstart = function () { dragFrom = el; };
            el.ondragover = function (e) { e.preventDefault(); };
            el.ondrop = function (e) {
                e.preventDefault();
                if (!dragFrom || dragFrom === el) return;
                var a = readCell(dragFrom), b = readCell(el);
                var fromZone = dragFrom.closest('#posQkGridCats') ? 'cat' : 'prod';
                var toZone = el.closest('#posQkGridCats') ? 'cat' : 'prod';
                if (a && (a.type === 'category' || a.type === 'manual') && toZone !== 'cat') { status('Category tabs stay on top', true); return; }
                if (a && a.type === 'product' && toZone !== 'prod') { status('Products stay in the product grid', true); return; }
                if (b && (b.type === 'category' || b.type === 'manual') && fromZone !== 'cat') { status('Category tabs stay on top', true); return; }
                if (b && b.type === 'product' && fromZone !== 'prod') { status('Products stay in the product grid', true); return; }
                paintCell(el, a); paintCell(dragFrom, b); dragFrom = null;
                if (activeManualId) syncActiveFolderFromProductGrid();
            };
        });
    }
    function buildBlankGrids() {
        var catHtml = '';
        for (var i = 0; i < CAT_SLOTS; i++) {
            catHtml += '<div class="qk-cell is-empty" data-slot="' + i + '" data-zone="cat"></div>';
        }
        document.getElementById('posQkGridCats').innerHTML = catHtml;
        var prodHtml = '';
        for (var j = 0; j < PRODUCT_SLOTS; j++) {
            prodHtml += '<div class="qk-cell is-empty" data-slot="' + (CAT_SLOTS + j) + '" data-zone="prod"></div>';
        }
        document.getElementById('posQkGrid').innerHTML = prodHtml;
    }
    function openEditor(layout) {
        document.getElementById('posQkEditId').value = layout.id;
        document.getElementById('posQkName').value = layout.name || '';
        document.getElementById('posQkKeepOpen').checked = layout.keep_folder_open !== false;
        activeManualId = null;
        looseProductCache = [];
        var keys = (layout.keys || []).slice();
        buildBlankGrids();
        var catIdx = 0;
        var prodIdx = 0;
        keys.slice().sort(function (a, b) {
            return (parseInt(a.slot, 10) || 0) - (parseInt(b.slot, 10) || 0);
        }).forEach(function (k) {
            var type = k.type || (k.variation_id ? 'product' : 'category');
            if (type === 'manual') return; // legacy — ignored
            if (type === 'category' && catIdx < CAT_SLOTS) {
                var catCell = document.querySelector('#posQkGridCats .qk-cell[data-slot="' + catIdx + '"]');
                if (catCell) {
                    k.slot = catIdx;
                    paintCell(catCell, k);
                    catIdx++;
                }
            } else if (type === 'product') {
                var slot = CAT_SLOTS + prodIdx;
                var prodCell = document.querySelector('#posQkGrid .qk-cell[data-slot="' + slot + '"]');
                if (prodCell) {
                    k.slot = slot;
                    paintCell(prodCell, k);
                    prodIdx++;
                }
            }
        });
        bindGrid();
        showEdit();
        document.getElementById('posQuickKeysTitle').textContent = layout.name || 'New layout';
        document.getElementById('posQkSearch').value = '';
        loadCats().then(function () {
            runSearch('');
        });
    }
    function post(url, body) {
        var fd = body instanceof FormData ? body : new FormData();
        if (body && !(body instanceof FormData)) {
            Object.keys(body).forEach(function (k) { fd.append(k, body[k]); });
        }
        fd.append('_token', csrf());
        return fetch(base() + url, { method: 'POST', credentials: 'same-origin', headers: jsonHeaders(), body: fd })
            .then(function (r) { return r.json(); });
    }

    document.getElementById('posQkEnabled').addEventListener('change', function () {
        var box = this;
        var on = box.checked;
        fetch(base() + '/pos/quick-keys/toggle', {
            method: 'POST',
            credentials: 'same-origin',
            headers: Object.assign({}, jsonHeaders(), { 'Content-Type': 'application/json' }),
            body: JSON.stringify({ _token: csrf(), enabled: on })
        }).then(function (r) {
            if (!r.ok) throw new Error('save failed');
            return r.json();
        }).then(function (j) {
            if (j && typeof j.enabled !== 'undefined') {
                box.checked = !!j.enabled;
                data.enabled = !!j.enabled;
            }
            if (j && j.layouts) {
                data.layouts = j.layouts;
                renderList();
            }
            applyPosLayout();
            status('Saved', false);
            setTimeout(function () { status(''); }, 1200);
        }).catch(function () {
            box.checked = !on;
            status('Could not save', true);
        });
    });
    document.getElementById('posQkAddBtn').addEventListener('click', function () {
        post('/pos/quick-keys', { name: 'New layout' }).then(function (j) {
            data = j;
            renderList();
            var id = j.layout_id;
            var lay = findLayout(id) || (j.layouts || []).filter(function (l) { return String(l.id) === String(id); })[0];
            if (lay) openEditor(lay);
        });
    });
    document.getElementById('posQkLayouts').addEventListener('click', function (e) {
        var btn = e.target.closest('[data-act]');
        if (!btn) return;
        var id = btn.getAttribute('data-id');
        var act = btn.getAttribute('data-act');
        if (act === 'edit') {
            e.preventDefault();
            openEditById(id);
            return;
        }
        if (act === 'current') post('/pos/quick-keys/' + id + '/current', {}).then(function (j) {
            if (j && j.layouts) data = j;
            renderList();
            applyPosLayout();
        });
        if (act === 'copy') post('/pos/quick-keys/' + id + '/duplicate', {}).then(function (j) {
            if (j && j.layouts) data = j;
            renderList();
        });
        if (act === 'del' && confirm('Delete this layout?')) {
            fetch(base() + '/pos/quick-keys/' + id, {
                method: 'POST',
                credentials: 'same-origin',
                headers: jsonHeaders(),
                body: (function () { var f = new FormData(); f.append('_token', csrf()); f.append('_method', 'DELETE'); return f; })()
            }).then(function (r) { return r.json(); }).then(function (j) {
                if (j && j.layouts) data = j;
                else return loadList();
                renderList();
                applyPosLayout();
            });
        }
    });
    document.getElementById('posQkBackBtn').addEventListener('click', function () { showList(); loadList(); });
    document.getElementById('posQkSaveBtn').addEventListener('click', function () {
        var keys = [];
        gridCells().forEach(function (el) {
            var d = readCell(el);
            if (d && d.type !== 'manual') keys.push(d);
        });
        fetch(base() + '/pos/quick-keys/' + document.getElementById('posQkEditId').value, {
            method: 'POST',
            credentials: 'same-origin',
            headers: Object.assign({}, jsonHeaders(), { 'Content-Type': 'application/json' }),
            body: JSON.stringify({
                _token: csrf(),
                _method: 'PUT',
                name: document.getElementById('posQkName').value || 'New layout',
                keep_folder_open: document.getElementById('posQkKeepOpen').checked ? 1 : 0,
                keys: keys
            })
        }).then(function (r) {
            if (!r.ok) throw new Error('save failed');
            return r.json();
        }).then(function (j) {
            if (j && j.layouts) data = j;
            if (j && typeof j.enabled !== 'undefined') data.enabled = !!j.enabled;
            renderList();
            applyPosLayout();
            showList();
            status('Layout saved', false);
            setTimeout(function () { status(''); }, 1500);
        }).catch(function () { status('Could not save', true); });
    });
    document.getElementById('posQkSearch').addEventListener('input', function () {
        var q = this.value;
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { runSearch(q); }, 220);
    });
    document.querySelectorAll('#posQkColors .pos-qk-dot').forEach(function (dot) {
        dot.addEventListener('click', function () {
            document.querySelectorAll('#posQkColors .pos-qk-dot').forEach(function (d) { d.classList.remove('is-on'); });
            dot.classList.add('is-on');
            color = dot.getAttribute('data-color');
            setPaletteOn(color);
            if (lastCatId) {
                var cell = cellForCategory(lastCatId);
                if (cell) {
                    var d = readCell(cell);
                    if (d) {
                        d.color = color;
                        paintCell(cell, d);
                        runSearch(document.getElementById('posQkSearch').value);
                    }
                }
            } else {
                var sel = document.querySelector('#posQkGridCats .qk-cell.is-on, #posQkGrid .qk-cell.is-on');
                if (sel) {
                    var pd = readCell(sel);
                    if (pd) { pd.color = color; paintCell(sel, pd); }
                }
            }
            if (picked) picked.color = color;
        });
    });

    if (typeof jQuery !== 'undefined') {
        jQuery(document).off('click.qkProd', '.qk-product-key').on('click.qkProd', '.qk-product-key', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var vid = jQuery(this).data('variation-id');
            if (!vid) return;
            if (jQuery('input#location_id').val() === '') {
                if (typeof toastr !== 'undefined') toastr.warning((typeof LANG !== 'undefined' && LANG.select_location) || 'Select location');
                return;
            }
            if (typeof pos_product_row === 'function') pos_product_row(vid);
        });
        jQuery(document).off('click.qkManual', '.qk-manual-folder');
        jQuery(document).on('shown.bs.modal', '#pos_quick_keys_modal', function () {
            showList();
            loadList().then(function () { applyPosLayout(); });
            loadCats().then(function () { runSearch(''); });
        });
    }
    // When category is "All", show selected quick products in the bottom grid (not every product).
    function wrapProductSuggest() {
        if (window.__posQkWrappedSuggest) return;
        if (typeof window.get_product_suggestion_list !== 'function') return;
        window.__posQkWrappedSuggest = true;
        var orig = window.get_product_suggestion_list;
        window.get_product_suggestion_list = function (category_id, brand_id, location_id, url, is_enabled_stock, repair_model_id) {
            var parts = currentLayoutKeys();
            var noCat = category_id == null || category_id === '' || category_id === 'all';
            if (noCat && !brand_id && parts.products && parts.products.length) {
                return renderQuickProducts(parts.products);
            }
            return orig.apply(this, arguments);
        };
    }
    wrapProductSuggest();
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', wrapProductSuggest);
    } else {
        setTimeout(wrapProductSuggest, 0);
    }
    function bootLayout() {
        loadList().then(function () { applyPosLayout(); });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootLayout);
    } else {
        bootLayout();
    }
})();
</script>
<style>
#pos_quick_keys_modal {
  padding: 0 !important;
}
#pos_quick_keys_modal .modal-dialog.pos-qk-dialog {
  width: min(980px, 92vw);
  max-width: none;
  height: min(820px, 90vh);
  margin: max(16px, calc((100vh - min(820px, 90vh)) / 2)) auto;
}
#pos_quick_keys_modal .pos-qk-sheet {
  background: #1b1e24;
  color: #e8eaed;
  border: 1px solid rgba(255,255,255,.08);
  border-radius: 8px;
  box-shadow: 0 24px 60px rgba(0,0,0,.5);
  height: 100%;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
.pos-qk-top {
  display: flex; justify-content: space-between; align-items: flex-start; gap: 16px;
  padding: 18px 22px 10px; border-bottom: 1px solid rgba(255,255,255,.06);
  flex-shrink: 0;
}
.pos-qk-kicker { font-size: 11px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #8b93a7; margin-bottom: 4px; }
#pos_quick_keys_modal .modal-title { font-size: 24px; font-weight: 800; margin: 0; color: #fff; }
.pos-qk-sub { margin: 6px 0 0; color: #9aa3b5; font-size: 13px; }
.pos-qk-top-actions { display: flex; gap: 8px; align-items: center; }
.pos-qk-done {
  background: #3b82f6; color: #fff; border: 0; border-radius: 6px; padding: 8px 18px;
  font-weight: 800; cursor: pointer;
}
.pos-qk-close {
  background: transparent; border: 0; color: #9aa3b5; font-size: 28px; line-height: 1; cursor: pointer; padding: 0 6px;
}
.pos-qk-body {
  padding: 8px 22px 20px;
  flex: 1;
  min-height: 0;
  overflow: auto;
}
.pos-qk-block { padding: 14px 0 8px; }
.pos-qk-block h2 { font-size: 16px; font-weight: 800; margin: 0 0 8px; color: #fff; }
.pos-qk-block p { color: #9aa3b5; font-size: 13px; margin: 0 0 12px; line-height: 1.45; }
.pos-qk-enable {
  display: flex; align-items: center; justify-content: space-between; gap: 16px;
  padding: 10px 0 14px;
}
.pos-qk-switch { position: relative; width: 46px; height: 26px; flex-shrink: 0; overflow: hidden; }
#pos_quick_keys_modal .pos-qk-switch input[type="checkbox"],
body.zoho-theme #pos_quick_keys_modal .pos-qk-switch input[type="checkbox"],
body.lockscreen .pos-qk-switch input[type="checkbox"] {
  appearance: none !important;
  -webkit-appearance: none !important;
  opacity: 0 !important;
  width: 100% !important;
  height: 100% !important;
  min-width: 0 !important;
  min-height: 0 !important;
  position: absolute !important;
  inset: 0;
  z-index: 2;
  margin: 0 !important;
  padding: 0 !important;
  border: 0 !important;
  background: transparent !important;
  background-image: none !important;
  box-shadow: none !important;
  outline: none !important;
  cursor: pointer;
}
.pos-qk-switch i { position: absolute; inset: 0; background: #4b5563; border-radius: 999px; pointer-events: none; }
.pos-qk-switch i:before { content:''; position:absolute; width:20px; height:20px; left:3px; top:3px; background:#fff; border-radius:50%; transition:.2s; }
.pos-qk-switch input:checked + i { background: #7c3aed; }
.pos-qk-switch input:checked + i:before { transform: translateX(20px); }
.pos-qk-add {
  background: #7c3aed; color: #fff; border: 0; border-radius: 8px; padding: 10px 16px;
  font-weight: 800; cursor: pointer; margin: 4px 0 16px;
}
.pos-qk-layouts { display: flex; flex-direction: column; gap: 10px; }
.pos-qk-layout {
  display: flex; align-items: center; gap: 12px; padding: 14px 16px;
  background: #23262d; border: 1px solid rgba(255,255,255,.08); border-radius: 8px;
}
.pos-qk-layout__name { display: flex; align-items: center; gap: 10px; font-size: 13px; letter-spacing: .04em; }
.pos-qk-acts { margin-left: auto; display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }
.pos-qk-ico {
  background: transparent; border: 0; color: #c5cad6; cursor: pointer; padding: 8px;
  border-radius: 6px; font-size: 16px; line-height: 1;
}
.pos-qk-ico i { pointer-events: none; }
.pos-qk-text { font-weight: 700; font-size: 12px; color: #93c5fd; background: transparent; border: 0; cursor: pointer; padding: 6px 8px; }
.pos-qk-text--danger { color: #fca5a5; }
.pos-qk-layout.is-current { border-color: #7c3aed; background: #2a2438; }
.pos-qk-badge { font-size: 11px; font-weight: 800; color: #fff; background: #7c3aed; border-radius: 999px; padding: 2px 8px; }
.pos-qk-field { display: block; font-size: 13px; font-weight: 700; margin-bottom: 12px; max-width: 420px; }
.pos-qk-field .form-control {
  margin-top: 6px; background: #111318; border: 1px solid #3a3f4b; color: #fff; height: 40px; border-radius: 6px;
}
.pos-qk-check { display: flex; gap: 8px; font-size: 13px; margin: 8px 0 4px; font-weight: 600; color: #d1d5db; }
.pos-qk-cats { max-height: 160px; overflow: auto; border: 1px solid #3a3f4b; border-radius: 8px; margin-bottom: 10px; background: #111318; }
.pos-qk-cats button { display: block; width: 100%; text-align: left; border: 0; border-bottom: 1px solid #2a2e36; background: transparent; color: #e5e7eb; padding: 10px 12px; cursor: pointer; }
.pos-qk-cats button.is-on { font-weight: 800; background: rgba(124,58,237,.28); }
.pos-qk-zone-label {
  font-size: 12px;
  font-weight: 800;
  letter-spacing: .04em;
  text-transform: uppercase;
  color: #9aa3b5;
  margin: 4px 0 8px;
}
.pos-qk-grid--cats {
  grid-template-columns: repeat(4, minmax(0, 1fr));
  max-width: 720px;
}
.pos-qk-colors { display: flex; gap: 6px; flex-wrap: wrap; margin: 8px 0 14px; }
.pos-qk-dot { width: 22px; height: 22px; border-radius: 6px; border: 2px solid transparent; cursor: pointer; padding: 0; }
.pos-qk-dot.is-on { border-color: #fff; }
.pos-qk-grid { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 8px; }
.pos-qk-grid .qk-cell {
  min-height: clamp(56px, 9vh, 80px); border: 1px dashed #4b5563; border-radius: 6px; display: flex; align-items: center;
  justify-content: center; text-align: center; padding: 10px 8px; cursor: pointer; position: relative;
  font-size: 11px; font-weight: 800; color: #fff; background: #23262d; text-transform: uppercase; letter-spacing: .02em;
}
.pos-qk-grid .qk-cell.is-empty {
  color: transparent;
  background: rgba(255,255,255,.03);
  border-style: dashed;
  border-color: rgba(255,255,255,.12);
}
.pos-qk-grid .qk-cell.is-on { outline: 2px solid #3b82f6; }
.pos-qk-grid .qk-cell.is-folder .qk-label { border-bottom: 2px solid #e67e22; padding-bottom: 2px; }
.pos-qk-grid .qk-badge {
  position: absolute; top: 4px; right: 22px; background: #fff; color: #111; border-radius: 999px;
  font-size: 10px; font-weight: 800; min-width: 16px; height: 16px; line-height: 16px; padding: 0 4px;
}
.pos-qk-grid .qk-x { position: absolute; top: 4px; right: 4px; background: rgba(0,0,0,.45); border: 0; color: #fff; width: 18px; height: 18px; border-radius: 4px; line-height: 16px; font-size: 12px; cursor: pointer; z-index: 2; }
.pos-qk-grid .qk-label { display: block; max-width: 100%; overflow: hidden; text-overflow: ellipsis; }
.pos-card.pos-qk-folder .pos-card-name { border-bottom: 2px solid #e67e22; display: inline-block; padding-bottom: 2px; }
.pos-card .pos-qk-count {
  position: absolute; top: 6px; right: 8px; background: #fff; color: #111; border-radius: 999px;
  font-size: 10px; font-weight: 800; min-width: 16px; height: 16px; line-height: 16px; padding: 0 4px;
}
.pos-card.pos-qk-folder, .pos-card.pos-qk-product { position: relative; }
.pos-qk-mini { font-size: 13px; color: #9aa3b5; }
@media (max-width: 768px) {
  #pos_quick_keys_modal .modal-dialog.pos-qk-dialog {
    width: 94vw;
    height: 88vh;
    margin: 6vh auto;
  }
}
</style>
