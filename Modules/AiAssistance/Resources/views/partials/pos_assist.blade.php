@if(auth()->check() && \Illuminate\Support\Facades\Route::has('ai.intelligence.pos-hints'))
<script>
(function () {
    if (typeof jQuery === 'undefined') return;
    var $ = jQuery;
    var hintsUrl = @json(route('ai.intelligence.pos-hints'));
    var addonsUrl = @json(route('ai.intelligence.addons'));
    var token = @json(csrf_token());

    function toast(msg, type) {
        if (typeof toastr !== 'undefined') {
            toastr[type || 'warning'](msg);
        } else {
            alert(msg);
        }
    }

    function loadHints(cid) {
        if (!cid) return;
        $.get(hintsUrl, { contact_id: cid }, function (res) {
            if (res.credit && res.credit.flag) {
                toast(res.credit.message, 'warning');
            }
            (res.reorder || []).forEach(function (r) {
                toast(r.message, 'info');
            });
        }).fail(function () {});
    }

    $(document).on('select2:select change', 'select#customer_id', function () {
        loadHints($(this).val());
    });

    $(document).ajaxSuccess(function (_e, _xhr, settings) {
        if (!settings.url || String(settings.url).indexOf('get_product_row') === -1) return;
        var m = String(settings.url).match(/get_product_row\/(\d+)/);
        if (!m) return;
        $.get(addonsUrl, { variation_id: m[1] }, function (res) {
            (res.addons || []).slice(0, 3).forEach(function (a) {
                toast('Customers who buy this also buy: ' + a.name + '. Click search to add.', 'info');
            });
        }).fail(function () {});
    });
})();
</script>
@endif
