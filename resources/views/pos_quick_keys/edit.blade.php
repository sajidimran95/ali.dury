@extends('layouts.app')
@section('title', 'Edit layout')

@section('content')
@php
    $slots = 30;
    $bySlot = [];
    foreach (($layout->keys ?? []) as $k) {
        $bySlot[(int) ($k['slot'] ?? 0)] = $k;
    }
    $colors = ['#3a3f4b','#e67e22','#2980b9','#e53935','#16a34a','#7c3aed','#db2777','#0f766e'];
@endphp
<style>
.qk-ed { max-width: 980px; margin: 0 auto; padding: 8px 8px 48px; }
.qk-ed__bar { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
.qk-ed h1 { font-size: 26px; font-weight: 800; margin: 0; }
.qk-ed__hint { color: #64748b; font-size: 13px; margin: 4px 0 18px; }
.qk-done { background: #2563eb; color: #fff; border: 0; border-radius: 8px; padding: 8px 18px; font-weight: 800; cursor: pointer; }
.qk-sec { background: #fff; border: 1px solid #e8edf5; border-radius: 14px; padding: 18px; margin-bottom: 14px; }
.qk-sec h3 { margin: 0 0 12px; font-size: 15px; font-weight: 800; }
.qk-input { width: 100%; max-width: 420px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px 12px; }
.qk-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 8px; }
.qk-cell { min-height: 72px; border: 1px dashed #cbd5e1; border-radius: 8px; background: #f8fafc; display: flex; align-items: center; justify-content: center; text-align: center; padding: 8px; cursor: pointer; position: relative; font-size: 12px; font-weight: 700; color: #fff; }
.qk-cell.is-empty { color: #94a3b8; background: #fff; }
.qk-cell.is-on { outline: 2px solid #2563eb; }
.qk-cell .qk-x { position: absolute; top: 2px; right: 4px; background: rgba(0,0,0,.35); border: 0; color: #fff; border-radius: 4px; width: 18px; height: 18px; line-height: 16px; cursor: pointer; font-size: 11px; }
.qk-search { width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px 12px; margin-bottom: 10px; }
.qk-catlist { max-height: 180px; overflow: auto; border: 1px solid #e2e8f0; border-radius: 8px; }
.qk-catlist button { display: block; width: 100%; text-align: left; border: 0; border-bottom: 1px solid #f1f5f9; background: #fff; padding: 10px 12px; cursor: pointer; font-weight: 600; }
.qk-catlist button:hover, .qk-catlist button.is-pick { background: #eff6ff; color: #1d4ed8; }
.qk-colors { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 10px; }
.qk-dot { width: 22px; height: 22px; border-radius: 6px; border: 2px solid transparent; cursor: pointer; }
.qk-dot.is-on { border-color: #111; }
</style>

<form method="POST" action="{{ route('pos.quick_keys.update', $layout->id) }}" id="qkForm">
    @csrf
    @method('PUT')
    <input type="hidden" name="keys" id="qkKeysJson" value="{{ e(json_encode(array_values($layout->keys ?? []))) }}">
    <div class="qk-ed">
        <div class="qk-ed__bar">
            <div>
                <h1>New layout</h1>
                <div class="qk-ed__hint">Rename, reposition and recolor keys, or organize your categories on the POS grid.</div>
            </div>
            <button type="submit" class="qk-done">Done</button>
        </div>

        <div class="qk-sec">
            <h3>General</h3>
            <label style="font-size:12px;font-weight:700;display:block;margin-bottom:6px;">Layout name</label>
            <input class="qk-input" type="text" name="name" value="{{ $layout->name }}">
            <label style="display:flex;gap:8px;align-items:center;margin-top:14px;font-size:13px;">
                <input type="checkbox" name="keep_folder_open" value="1" {{ $layout->keep_folder_open ? 'checked' : '' }}>
                Leave selected category open until end of the sale
            </label>
        </div>

        <div class="qk-sec">
            <h3>Add categories</h3>
            <p class="qk-ed__hint">Search for categories to add to your quick key layout. Click a category, then click an empty (or filled) slot. Drag to rearrange.</p>
            <input type="search" class="qk-search" id="qkSearch" placeholder="Search for categories" autocomplete="off">
            <div class="qk-catlist" id="qkCatList">
                @foreach($categories as $cat)
                    <button type="button" data-id="{{ $cat->id }}" data-name="{{ $cat->name }}">{{ $cat->name }}</button>
                @endforeach
            </div>
            <div class="qk-colors" id="qkColors">
                @foreach($colors as $c)
                    <button type="button" class="qk-dot {{ $c === '#3a3f4b' ? 'is-on' : '' }}" data-color="{{ $c }}" style="background:{{ $c }}"></button>
                @endforeach
            </div>
        </div>

        <div class="qk-sec">
            <h3>Layout grid</h3>
            <div class="qk-grid" id="qkGrid">
                @for($i = 0; $i < $slots; $i++)
                    @php $cell = $bySlot[$i] ?? null; @endphp
                    <div class="qk-cell {{ $cell ? '' : 'is-empty' }}" draggable="{{ $cell ? 'true' : 'false' }}" data-slot="{{ $i }}"
                        @if($cell) data-category-id="{{ $cell['category_id'] }}" data-label="{{ $cell['label'] ?? '' }}" data-color="{{ $cell['color'] ?? '#3a3f4b' }}" style="background: {{ $cell['color'] ?? '#3a3f4b' }}; border-style:solid;" @endif>
                        @if($cell)
                            <button type="button" class="qk-x" title="Remove">&times;</button>
                            {{ $cell['label'] ?: ($cell['category_id'] ?? '') }}
                        @endif
                    </div>
                @endfor
            </div>
        </div>
    </div>
</form>
@endsection

@section('javascript')
<script>
(function () {
    var picked = null;
    var color = '#3a3f4b';
    var dragFrom = null;

    function cells() { return document.querySelectorAll('#qkGrid .qk-cell'); }

    function readCell(el) {
        if (!el || !el.getAttribute('data-category-id')) return null;
        return {
            slot: parseInt(el.getAttribute('data-slot'), 10),
            category_id: parseInt(el.getAttribute('data-category-id'), 10),
            label: el.getAttribute('data-label') || el.textContent.replace('×','').trim(),
            color: el.getAttribute('data-color') || '#3a3f4b'
        };
    }

    function paint(el, data) {
        if (!data) {
            el.className = 'qk-cell is-empty';
            el.removeAttribute('data-category-id');
            el.removeAttribute('data-label');
            el.removeAttribute('data-color');
            el.style.background = '';
            el.style.borderStyle = '';
            el.draggable = false;
            el.innerHTML = '';
            return;
        }
        el.className = 'qk-cell';
        el.setAttribute('data-category-id', data.category_id);
        el.setAttribute('data-label', data.label || '');
        el.setAttribute('data-color', data.color || '#3a3f4b');
        el.style.background = data.color || '#3a3f4b';
        el.style.borderStyle = 'solid';
        el.draggable = true;
        el.innerHTML = '<button type="button" class="qk-x" title="Remove">&times;</button>' + (data.label || '');
    }

    function serialize() {
        var out = [];
        cells().forEach(function (el) {
            var d = readCell(el);
            if (d) out.push(d);
        });
        document.getElementById('qkKeysJson').value = JSON.stringify(out);
    }

    document.querySelectorAll('#qkCatList button').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#qkCatList button').forEach(function (b) { b.classList.remove('is-pick'); });
            btn.classList.add('is-pick');
            picked = { category_id: parseInt(btn.getAttribute('data-id'), 10), label: btn.getAttribute('data-name'), color: color };
        });
    });

    document.querySelectorAll('#qkColors .qk-dot').forEach(function (dot) {
        dot.addEventListener('click', function () {
            document.querySelectorAll('#qkColors .qk-dot').forEach(function (d) { d.classList.remove('is-on'); });
            dot.classList.add('is-on');
            color = dot.getAttribute('data-color');
            if (picked) picked.color = color;
        });
    });

    document.getElementById('qkSearch').addEventListener('input', function () {
        var q = this.value.toLowerCase();
        document.querySelectorAll('#qkCatList button').forEach(function (b) {
            b.style.display = b.textContent.toLowerCase().indexOf(q) >= 0 ? '' : 'none';
        });
    });

    cells().forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (e.target.classList.contains('qk-x')) {
                paint(el, null);
                serialize();
                return;
            }
            if (picked) {
                paint(el, { category_id: picked.category_id, label: picked.label, color: picked.color || color });
                serialize();
            }
        });
        el.addEventListener('dragstart', function () {
            dragFrom = el;
        });
        el.addEventListener('dragover', function (e) { e.preventDefault(); });
        el.addEventListener('drop', function (e) {
            e.preventDefault();
            if (!dragFrom || dragFrom === el) return;
            var a = readCell(dragFrom);
            var b = readCell(el);
            paint(el, a);
            paint(dragFrom, b);
            serialize();
            dragFrom = null;
        });
    });

    document.getElementById('qkForm').addEventListener('submit', serialize);
    serialize();
})();
</script>
@endsection
