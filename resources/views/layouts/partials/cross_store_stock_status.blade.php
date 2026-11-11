@if(config('cross_store_sync.enabled'))
<button type="button"
    id="cross_store_stock_status_btn"
    class="zoho-topbar__btn zoho-topbar__btn--ghost tw-hidden sm:tw-inline-flex"
    title="Cross-store stock match status"
    data-status-url="{{ action([\App\Http\Controllers\ProductController::class, 'crossStoreStockStatus']) }}"
    data-reconcile-url="{{ action([\App\Http\Controllers\ProductController::class, 'crossStoreReconcileStock']) }}"
    style="gap:6px; min-width:auto;">
    <span id="cross_store_stock_status_dot"
        style="width:8px;height:8px;border-radius:999px;background:#9ca3af;display:inline-block;"></span>
    <span id="cross_store_stock_status_text" class="tw-text-xs tw-font-semibold">Stock sync…</span>
</button>
<script>
(function () {
    function paint(status) {
        var dot = document.getElementById('cross_store_stock_status_dot');
        var text = document.getElementById('cross_store_stock_status_text');
        var btn = document.getElementById('cross_store_stock_status_btn');
        if (!dot || !text || !btn) return;

        if (!status || !status.enabled) {
            dot.style.background = '#9ca3af';
            text.textContent = 'Sync off';
            btn.title = 'Cross-store sync disabled';
            return;
        }
        if (!status.success) {
            dot.style.background = '#f59e0b';
            text.textContent = status.message || 'Peer down';
            btn.title = status.message || 'Peer unreachable';
            return;
        }
        if (status.matched) {
            dot.style.background = '#22c55e';
            text.textContent = 'All matched';
            btn.title = 'All stock matched with peer (' + (status.common || 0) + ' items)';
        } else {
            dot.style.background = '#ef4444';
            text.textContent = (status.mismatches || 0) + ' mismatch';
            btn.title = 'Click to match stock now';
        }
    }

    function loadStatus(fresh) {
        var btn = document.getElementById('cross_store_stock_status_btn');
        if (!btn) return;
        var url = btn.getAttribute('data-status-url') + (fresh ? '?fresh=1' : '');
        fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(paint)
            .catch(function () {
                paint({ enabled: true, success: false, message: 'Status failed' });
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.getElementById('cross_store_stock_status_btn');
        if (!btn) return;
        loadStatus(false);
        setInterval(function () { loadStatus(false); }, 60000);

        btn.addEventListener('click', function () {
            var reconcileUrl = btn.getAttribute('data-reconcile-url');
            btn.disabled = true;
            var text = document.getElementById('cross_store_stock_status_text');
            if (text) text.textContent = 'Matching…';
            fetch(reconcileUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: '{}'
            })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                if (window.toastr) {
                    if (result.success) toastr.success(result.msg || 'Matched');
                    else toastr.error(result.msg || 'Match failed');
                }
                loadStatus(true);
            })
            .catch(function () {
                if (window.toastr) toastr.error('Match failed');
                loadStatus(true);
            })
            .finally(function () { btn.disabled = false; });
        });
    });
})();
</script>
@endif
