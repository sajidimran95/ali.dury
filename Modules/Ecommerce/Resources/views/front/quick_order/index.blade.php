@extends('ecommerce::front.layouts.store')
@section('title', 'Quick Order')
@section('content')
@php $exampleSku = $exampleSku ?? 'SKU'; @endphp
<div class="ecom-acc max-w-[1100px] mx-auto px-4 py-10">
    @include('ecommerce::front.account.partials.mobile_topbar', ['title' => 'Quick Order'])
    <p class="text-[11px] uppercase tracking-[0.18em] text-[#8b95b8] font-bold mb-2 ecom-acc-desktop-only">Wholesale</p>
    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-2 ecom-acc-desktop-only">Quick Order</h1>
    <p class="text-sm text-[#8b95b8] mb-8 max-w-2xl ecom-acc-desktop-only">Search by SKU, barcode, or product name. Paste lines as <code class="text-xs bg-[#0c1226] border border-[#252d4d] px-1.5 py-0.5 rounded text-white">{{ $exampleSku }}, 12</code> or fill the table.</p>
    <p class="text-sm text-[#8b95b8] mb-4 px-1 ecom-acc-mobile-only">Search by SKU, barcode, or name. Paste as <code class="text-xs bg-[#0c1226] border border-[#252d4d] px-1.5 py-0.5 rounded text-white">SKU, qty</code>.</p>

    <form method="post" action="{{ route('ecommerce.quick_order.submit', absolute: false) }}" class="space-y-6" id="qoForm">
        @csrf

        <div class="wn-panel overflow-hidden">
            <div class="px-4 py-3 border-b border-[#252d4d] flex items-center justify-between gap-3">
                <h2 class="font-extrabold text-sm uppercase tracking-wide">Order lines</h2>
                <button type="button" id="addRowBtn" class="text-sm font-bold text-[#f23488]">+ Add row</button>
            </div>
            <div class="p-4 overflow-x-auto">
                <table class="w-full text-sm" id="qoTable">
                    <thead>
                        <tr class="text-left text-[#8b95b8] text-xs uppercase tracking-wide">
                            <th class="pb-2 pr-2 font-bold">SKU / product</th>
                            <th class="pb-2 pr-2 font-bold w-32">Qty</th>
                            <th class="pb-2 w-10"></th>
                        </tr>
                    </thead>
                    <tbody id="qoBody">
                        @for($i = 0; $i < 8; $i++)
                        <tr class="qo-row border-t border-[#252d4d]">
                            <td class="py-2 pr-2 relative">
                                <input name="rows[{{ $i }}][sku]" value="{{ old('rows.'.$i.'.sku') }}" class="wn-field qo-sku" placeholder="{{ $exampleSku }}" autocomplete="off">
                                <div class="qo-suggest" hidden></div>
                                <div class="qo-hit text-[11px] text-[#8b95b8] mt-1 min-h-[1.1em]"></div>
                            </td>
                            <td class="py-2 pr-2">
                                <input type="number" name="rows[{{ $i }}][qty]" value="{{ old('rows.'.$i.'.qty', 1) }}" min="1" step="1" class="wn-field">
                            </td>
                            <td class="py-2">
                                <button type="button" class="qo-remove text-[#ff6b8a] text-xs font-semibold">✕</button>
                            </td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>

        <div class="wn-panel p-4">
            <label class="font-extrabold text-sm uppercase tracking-wide block mb-2">Or paste list</label>
            <textarea name="bulk" rows="5" class="wn-field font-mono text-xs" placeholder="{{ $exampleSku }}, 12">{{ old('bulk') }}</textarea>
            <p class="text-xs text-[#8b95b8] mt-2">One product per line. Formats: <strong class="text-white">SKU, qty</strong> or <strong class="text-white">SKU qty</strong>.</p>
        </div>

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="wn-quick" style="height:48px;padding:0 22px;border:0;border-radius:8px;cursor:pointer">Add to cart</button>
            <a href="{{ route('ecommerce.cart') }}" class="btn-outline">View cart</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<style>
.qo-suggest {
    position: absolute; left: 0; right: 8px; top: 46px; z-index: 20;
    background: #0c1226; border: 1px solid #2a3358; border-radius: 8px;
    max-height: 220px; overflow: auto; box-shadow: 0 12px 32px rgba(0,0,0,.4);
}
.qo-suggest button {
    display: block; width: 100%; text-align: left; padding: 8px 10px;
    background: transparent; border: 0; color: #eef2ff; cursor: pointer; font-size: 12px;
}
.qo-suggest button:hover, .qo-suggest button.is-active { background: #1a2140; }
.qo-suggest small { display: block; color: #8b95b8; font-size: 11px; }
</style>
<script>
(function () {
    const body = document.getElementById('qoBody');
    const addBtn = document.getElementById('addRowBtn');
    const suggestUrl = @json(route('ecommerce.quick_order.suggest', [], false));
    if (!body || !addBtn) return;
    let idx = body.querySelectorAll('.qo-row').length;

    function hideSuggest(row) {
        const box = row.querySelector('.qo-suggest');
        if (box) { box.hidden = true; box.innerHTML = ''; }
    }

    function bindRow(row) {
        row.querySelector('.qo-remove')?.addEventListener('click', () => {
            if (body.querySelectorAll('.qo-row').length <= 1) {
                row.querySelectorAll('input').forEach(i => { if (i.type === 'number') i.value = 1; else i.value = ''; });
                const hit = row.querySelector('.qo-hit');
                if (hit) hit.textContent = '';
                hideSuggest(row);
                return;
            }
            row.remove();
        });

        const skuInput = row.querySelector('.qo-sku');
        const box = row.querySelector('.qo-suggest');
        const hit = row.querySelector('.qo-hit');
        if (!skuInput || !box) return;
        let timer = null;
        skuInput.addEventListener('input', () => {
            const q = skuInput.value.trim();
            if (hit) hit.textContent = '';
            clearTimeout(timer);
            if (q.length < 2) { hideSuggest(row); return; }
            timer = setTimeout(async () => {
                try {
                    const res = await fetch(suggestUrl + '?q=' + encodeURIComponent(q), {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (!res.ok) return;
                    const data = await res.json();
                    const items = data.items || [];
                    if (!items.length) {
                        box.hidden = false;
                        box.innerHTML = '<div style="padding:8px 10px;color:#8b95b8;font-size:12px">No matches</div>';
                        return;
                    }
                    box.hidden = false;
                    box.innerHTML = '';
                    items.forEach((item) => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        const strong = document.createElement('strong');
                        strong.textContent = item.sku || '';
                        const small = document.createElement('small');
                        small.textContent = item.name || '';
                        btn.appendChild(strong);
                        btn.appendChild(small);
                        btn.addEventListener('click', () => {
                            skuInput.value = item.sku || '';
                            if (hit) hit.textContent = item.name || '';
                            hideSuggest(row);
                        });
                        box.appendChild(btn);
                    });
                } catch (e) {}
            }, 220);
        });
        skuInput.addEventListener('blur', () => setTimeout(() => hideSuggest(row), 180));
    }

    body.querySelectorAll('.qo-row').forEach(bindRow);

    addBtn.addEventListener('click', () => {
        const tr = document.createElement('tr');
        tr.className = 'qo-row border-t border-[#252d4d]';
        tr.innerHTML = `
            <td class="py-2 pr-2 relative">
                <input name="rows[${idx}][sku]" class="wn-field qo-sku" placeholder="SKU" autocomplete="off">
                <div class="qo-suggest" hidden></div>
                <div class="qo-hit text-[11px] text-[#8b95b8] mt-1 min-h-[1.1em]"></div>
            </td>
            <td class="py-2 pr-2"><input type="number" name="rows[${idx}][qty]" value="1" min="1" step="1" class="wn-field"></td>
            <td class="py-2"><button type="button" class="qo-remove text-[#ff6b8a] text-xs font-semibold">✕</button></td>`;
        body.appendChild(tr);
        bindRow(tr);
        idx++;
        tr.querySelector('.qo-sku')?.focus();
    });
})();
</script>
@endpush
