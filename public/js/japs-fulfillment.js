$(function () {
    function rowMeta($row) {
        var already = parseFloat($row.attr('data-already'));
        var due = parseFloat($row.attr('data-due'));
        var ordered = parseFloat($row.attr('data-ordered'));
        return {
            already: isNaN(already) ? 0 : already,
            due: isNaN(due) ? 0 : due,
            ordered: isNaN(ordered) ? 0 : ordered
        };
    }

    function pickNowVal($row) {
        var raw = $row.find('.ff-qty-picked').val();
        if (raw === '' || raw === null || typeof raw === 'undefined') {
            return 0;
        }
        var val = parseFloat(raw);
        return isNaN(val) ? 0 : val;
    }

    function lineHasPick($row) {
        var meta = rowMeta($row);
        return meta.already > 0 || pickNowVal($row) > 0;
    }

    function syncReady() {
        var $rows = $('#ff_pick_form .ff-items tbody tr');
        var ok = $rows.length > 0;
        $rows.each(function () {
            var $row = $(this);
            if (!$row.find('.ff-pick-check').is(':checked')) {
                ok = false;
                return false;
            }
            // Already saved and/or pick-now > 0 (partial OK)
            if (!lineHasPick($row)) {
                ok = false;
                return false;
            }
        });
        $('#ff_ready_pack').prop('disabled', !ok);
    }

    function syncSelectAll() {
        var $checks = $('#ff_pick_form .ff-pick-check');
        var $all = $('#ff_pick_select_all');
        if (!$all.length || !$checks.length) {
            return;
        }
        var checked = $checks.filter(':checked').length;
        $all.prop('checked', checked > 0 && checked === $checks.length);
        $all.prop('indeterminate', checked > 0 && checked < $checks.length);
    }

    $(document).on('input change keyup blur', '.ff-qty-picked', function () {
        var $row = $(this).closest('tr');
        var meta = rowMeta($row);
        var val = pickNowVal($row);
        // Clamp to due only — never bump to full ordered qty
        if (val > meta.due) {
            $(this).val(meta.due);
        }
        if (val < 0) {
            $(this).val(0);
        }
        syncReady();
    });

    $(document).on('change', '.ff-pick-check', function () {
        var $row = $(this).closest('tr');
        var meta = rowMeta($row);
        if (this.checked) {
            // Fill only remaining DUE (e.g. already 1 of 2 → fill 1, not 2)
            if (pickNowVal($row) <= 0 && meta.due > 0) {
                $row.find('.ff-qty-picked').val(meta.due);
            }
        }
        syncSelectAll();
        syncReady();
    });

    $(document).on('change', '#ff_pick_select_all', function () {
        var on = this.checked;
        $('#ff_pick_form .ff-pick-check').each(function () {
            if (this.checked === on) {
                return;
            }
            $(this).prop('checked', on).trigger('change');
        });
        syncSelectAll();
    });

    // Pre-select rows that already have saved picks (do not change pick-now)
    $('#ff_pick_form .ff-items tbody tr').each(function () {
        var $row = $(this);
        if (rowMeta($row).already > 0) {
            $row.find('.ff-pick-check').prop('checked', true);
        }
    });
    syncSelectAll();
    syncReady();
});
