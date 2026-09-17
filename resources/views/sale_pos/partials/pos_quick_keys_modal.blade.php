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
                            <input type="text" id="posQkName" class="form-control" placeholder="New layout">
                        </label>
                        <label class="pos-qk-check">
                            <input type="checkbox" id="posQkKeepOpen" checked>
                            Leave selected folder open until end of the sale
                        </label>
                    </section>
                    <section class="pos-qk-block">
                        <h2>Choose categories</h2>
                        <p>These are your existing POS categories. Click one to show it on the layout. Click again to remove it. Drag tiles to rearrange.</p>
                        <label class="pos-qk-field">Search
                            <input type="search" id="posQkSearch" class="form-control" placeholder="Filter categories" autocomplete="off">
                        </label>
                        <div id="posQkCats" class="pos-qk-cats"></div>
                        <div class="pos-qk-colors" id="posQkColors">
                            @foreach(['#3a3f4b','#e67e22','#2980b9','#e53935','#16a34a','#7c3aed','#db2777','#0f766e'] as $c)
                                <button type="button" class="pos-qk-dot{{ $c === '#3a3f4b' ? ' is-on' : '' }}" data-color="{{ $c }}" style="background:{{ $c }}"></button>
                            @endforeach
                        </div>
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

    var SLOTS = 30;
    var PALETTE = ['#3a3f4b','#e67e22','#2980b9','#e53935','#16a34a','#7c3aed','#db2777','#0f766e'];
    var data = { enabled: true, layouts: window.__posQkLayouts || [] };
    var picked = null;
    var color = '#3a3f4b';
    var lastCatId = null;
    var dragFrom = null;
    var cats = [];

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
        var lay = findLayout(id);
        if (lay) {
            openEditor(lay);
            return;
        }
        loadList().then(function () {
            var found = findLayout(id);
            if (found) openEditor(found);
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
        document.getElementById('posQkHint').textContent = 'Pick categories for this layout, then press Done.';
        wide(true);
    }
    function isCurrent(l) {
        return !!(l && (l.is_current === true || l.is_current === 1 || l.is_current === '1'));
    }
    function applyPosLayout() {
        var grid = document.querySelector('.pos-card-grid-categories');
        if (!grid) return;
        var allTile = grid.querySelector('.main-category[data-value="all"]');
        grid.querySelectorAll('.main-category-div.main-category').forEach(function (el) {
            if (el.getAttribute('data-value') !== 'all') el.remove();
        });
        var cats = window.__posQkCats || [];
        var byId = {};
        cats.forEach(function (c) { byId[String(c.id)] = c; });
        var current = (data.layouts || []).filter(isCurrent)[0];
        var rows = [];
        if (data.enabled && current && current.keys && current.keys.length) {
            current.keys.slice().sort(function (a, b) {
                return (parseInt(a.slot, 10) || 0) - (parseInt(b.slot, 10) || 0);
            }).forEach(function (k) {
                var c = byId[String(k.category_id)];
                if (c) rows.push({ id: c.id, name: k.label || c.name, color: k.color || '' });
            });
        } else {
            cats.forEach(function (c) { rows.push({ id: c.id, name: c.name, color: '' }); });
        }
        var html = '';
        rows.forEach(function (c) {
            var extra = '';
            if (c.color) {
                extra = ' pos-cat-tile--custom" style="--qk-color:' + c.color + '"';
            } else {
                extra = '"';
            }
            html += '<div class="col-md-2 col-xs-4 tw-mb-2 tw-cursor-pointer main-category-div main-category no-print" data-value="' + c.id + '" data-name="' + String(c.name).replace(/"/g, '') + '" data-parent="0" style="padding-left: 4px; padding-right: 4px;">' +
                '<div class="pos-card cat pos-cat-tile' + extra + '><h4 class="pos-card-name">' + c.name + '</h4></div></div>';
        });
        if (allTile) allTile.insertAdjacentHTML('afterend', html);
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
        if (typeof window.get_product_suggestion_list === 'function' && document.querySelector('input#location_id')) {
            var loc = document.querySelector('input#location_id').value;
            window.get_product_suggestion_list(null, window.global_brand_id || null, loc, null);
        }
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
            if (d) map[d.category_id] = d.color || colorForCat(d.category_id);
        });
        return map;
    }
    function setPaletteOn(hex) {
        color = hex || color;
        document.querySelectorAll('#posQkColors .pos-qk-dot').forEach(function (d) {
            d.classList.toggle('is-on', d.getAttribute('data-color') === color);
        });
    }
    function usedIds() {
        var ids = {};
        gridCells().forEach(function (el) {
            var d = readCell(el);
            if (d) ids[d.category_id] = true;
        });
        return ids;
    }
    function nextEmpty() {
        var cells = gridCells();
        for (var i = 0; i < cells.length; i++) {
            if (!readCell(cells[i])) return cells[i];
        }
        return null;
    }
    function cellForCategory(id) {
        var cells = gridCells();
        for (var i = 0; i < cells.length; i++) {
            var d = readCell(cells[i]);
            if (d && d.category_id === id) return cells[i];
        }
        return null;
    }
    function loadCats() {
        if (window.__posQkCats && window.__posQkCats.length) {
            cats = window.__posQkCats.map(function (c) {
                return { id: c.id, name: c.name };
            });
            paintCats(document.getElementById('posQkSearch') ? document.getElementById('posQkSearch').value : '');
            return Promise.resolve();
        }
        return fetch(base() + '/pos/quick-keys/categories', { credentials: 'same-origin', headers: jsonHeaders() })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                cats = j || [];
                paintCats('');
            });
    }
    function paintCats(q) {
        q = (q || '').toLowerCase();
        var el = document.getElementById('posQkCats');
        if (!el) return;
        var used = usedIds();
        var colors = usedColorMap();
        el.innerHTML = cats.filter(function (c) {
            return !q || (c.name || '').toLowerCase().indexOf(q) >= 0;
        }).map(function (c) {
            var on = !!used[c.id];
            var bg = on ? (colors[c.id] || colorForCat(c.id)) : '';
            var st = bg ? ' style="background:' + bg + ';color:#fff;border-left:4px solid #fff;"' : '';
            return '<button type="button" class="' + (on ? 'is-on' : '') + '" data-id="' + c.id + '" data-name="' + (c.name || '').replace(/"/g, '') + '"' + st + '>' +
                (on ? '<span class="pos-qk-tick">✓</span> ' : '') + c.name + '</button>';
        }).join('') || '<div class="pos-qk-mini">No categories in this business.</div>';
        el.querySelectorAll('button').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = parseInt(btn.getAttribute('data-id'), 10);
                var label = btn.getAttribute('data-name');
                lastCatId = id;
                var existing = cellForCategory(id);
                if (existing) {
                    var cur = readCell(existing);
                    setPaletteOn(cur && cur.color ? cur.color : colorForCat(id));
                    paintCell(existing, null);
                } else {
                    var slot = nextEmpty();
                    if (!slot) { status('Layout is full', true); return; }
                    var useColor = colorForCat(id);
                    setPaletteOn(useColor);
                    paintCell(slot, { category_id: id, label: label, color: useColor });
                }
                paintCats(document.getElementById('posQkSearch').value);
            });
        });
    }
    function gridCells() { return document.querySelectorAll('#posQkGrid .qk-cell'); }
    function readCell(el) {
        if (!el || !el.getAttribute('data-category-id')) return null;
        return {
            slot: parseInt(el.getAttribute('data-slot'), 10),
            category_id: parseInt(el.getAttribute('data-category-id'), 10),
            label: el.getAttribute('data-label') || '',
            color: el.getAttribute('data-color') || '#3a3f4b'
        };
    }
    function paintCell(el, d) {
        if (!d) {
            el.className = 'qk-cell is-empty';
            el.removeAttribute('data-category-id');
            el.removeAttribute('data-label');
            el.removeAttribute('data-color');
            el.style.background = '';
            el.draggable = false;
            el.innerHTML = '';
            return;
        }
        el.className = 'qk-cell';
        el.setAttribute('data-category-id', d.category_id);
        el.setAttribute('data-label', d.label || '');
        el.setAttribute('data-color', d.color || '#3a3f4b');
        el.style.background = d.color || '#3a3f4b';
        el.draggable = true;
        el.innerHTML = '<button type="button" class="qk-x">&times;</button>' + (d.label || '');
    }
    function bindGrid() {
        gridCells().forEach(function (el) {
            el.onclick = function (e) {
                if (e.target.classList.contains('qk-x')) {
                    paintCell(el, null);
                    paintCats(document.getElementById('posQkSearch').value);
                    return;
                }
                var d = readCell(el);
                if (d) {
                    lastCatId = d.category_id;
                    setPaletteOn(d.color || colorForCat(d.category_id));
                }
            };
            el.ondragstart = function () { dragFrom = el; };
            el.ondragover = function (e) { e.preventDefault(); };
            el.ondrop = function (e) {
                e.preventDefault();
                if (!dragFrom || dragFrom === el) return;
                var a = readCell(dragFrom), b = readCell(el);
                paintCell(el, a); paintCell(dragFrom, b); dragFrom = null;
            };
        });
    }
    function slotCount() {
        return Math.max(30, (cats.length || (window.__posQkCats || []).length || 0));
    }
    function keysForAllCategories() {
        var list = cats.length ? cats : (window.__posQkCats || []);
        return list.map(function (c, i) {
            return {
                slot: i,
                category_id: parseInt(c.id, 10),
                label: c.name || '',
                color: colorForCat(c.id)
            };
        });
    }
    function openEditor(layout) {
        document.getElementById('posQkEditId').value = layout.id;
        document.getElementById('posQkName').value = layout.name || '';
        document.getElementById('posQkKeepOpen').checked = layout.keep_folder_open !== false;
        var keys = (layout.keys || []).slice();
        if (!keys.length) {
            keys = keysForAllCategories();
        }
        var bySlot = {};
        keys.forEach(function (k) { bySlot[parseInt(k.slot, 10)] = k; });
        var grid = document.getElementById('posQkGrid');
        var html = '';
        var n = Math.max(slotCount(), keys.length);
        for (var i = 0; i < n; i++) html += '<div class="qk-cell is-empty" data-slot="' + i + '"></div>';
        grid.innerHTML = html;
        gridCells().forEach(function (el) {
            var d = bySlot[el.getAttribute('data-slot')];
            if (d) paintCell(el, d);
        });
        bindGrid();
        showEdit();
        document.getElementById('posQuickKeysTitle').textContent = layout.name || 'New layout';
        loadCats().then(function () {
            if (!(layout.keys || []).length) {
                keys = keysForAllCategories();
                bySlot = {};
                keys.forEach(function (k) { bySlot[parseInt(k.slot, 10)] = k; });
                n = Math.max(slotCount(), keys.length);
                html = '';
                for (var j = 0; j < n; j++) html += '<div class="qk-cell is-empty" data-slot="' + j + '"></div>';
                grid.innerHTML = html;
                gridCells().forEach(function (el) {
                    var d = bySlot[el.getAttribute('data-slot')];
                    if (d) paintCell(el, d);
                });
                bindGrid();
            }
            paintCats(document.getElementById('posQkSearch') ? document.getElementById('posQkSearch').value : '');
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
        gridCells().forEach(function (el) { var d = readCell(el); if (d) keys.push(d); });
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
    document.getElementById('posQkSearch').addEventListener('input', function () { paintCats(this.value); });
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
                        paintCats(document.getElementById('posQkSearch').value);
                    }
                }
            }
            if (picked) picked.color = color;
        });
    });

    if (typeof jQuery !== 'undefined') {
        jQuery(document).on('shown.bs.modal', '#pos_quick_keys_modal', function () {
            showList();
            loadList().then(function () { applyPosLayout(); });
            loadCats();
        });
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
  width: min(860px, 82vw);
  max-width: none;
  height: min(720px, 82vh);
  margin: max(24px, calc((100vh - min(720px, 82vh)) / 2)) auto;
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
.pos-qk-cats { max-height: 120px; overflow: auto; border: 1px solid #3a3f4b; border-radius: 8px; margin-bottom: 10px; background: #111318; }
.pos-qk-cats button { display: block; width: 100%; text-align: left; border: 0; border-bottom: 1px solid #2a2e36; background: transparent; color: #e5e7eb; padding: 10px 12px; cursor: pointer; }
.pos-qk-cats button.is-on { font-weight: 800; background: rgba(124,58,237,.28); }
.pos-qk-colors { display: flex; gap: 6px; flex-wrap: wrap; margin: 8px 0 14px; }
.pos-qk-dot { width: 22px; height: 22px; border-radius: 6px; border: 2px solid transparent; cursor: pointer; padding: 0; }
.pos-qk-dot.is-on { border-color: #fff; }
.pos-qk-grid { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 8px; }
.pos-qk-grid .qk-cell {
  min-height: clamp(48px, 8vh, 72px); border: 1px dashed #4b5563; border-radius: 4px; display: flex; align-items: center;
  justify-content: center; text-align: center; padding: 8px; cursor: pointer; position: relative;
  font-size: 12px; font-weight: 700; color: #fff; background: #23262d;
}
.pos-qk-grid .qk-cell.is-empty { color: transparent; background: transparent; }
.pos-qk-grid .qk-x { position: absolute; top: 4px; right: 4px; background: rgba(0,0,0,.45); border: 0; color: #fff; width: 18px; height: 18px; border-radius: 4px; line-height: 16px; font-size: 12px; cursor: pointer; }
.pos-qk-mini { font-size: 13px; color: #9aa3b5; }
@media (max-width: 768px) {
  #pos_quick_keys_modal .modal-dialog.pos-qk-dialog {
    width: 94vw;
    height: 88vh;
    margin: 6vh auto;
  }
}
</style>
