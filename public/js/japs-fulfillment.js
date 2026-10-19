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

    /**
     * Ready for packing when at least one OPEN line is checked with a pick qty.
     * Unchecked lines = intentional backorder on the same order (not lost).
     * Fully-picked lines (no checkbox) are ignored.
     */
    function syncReady() {
        var hasWave = false;
        var invalidChecked = false;

        $('#ff_pick_form .ff-items tbody tr').each(function () {
            var $row = $(this);
            var $check = $row.find('.ff-pick-check');
            if (!$check.length) {
                return;
            }
            var meta = rowMeta($row);
            var now = pickNowVal($row);
            if (!$check.is(':checked')) {
                return;
            }
            if (meta.already <= 0 && now <= 0) {
                invalidChecked = true;
                return;
            }
            hasWave = true;
        });

        $('#ff_ready_pack').prop('disabled', !hasWave || invalidChecked);
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
        if (val > meta.due) {
            $(this).val(meta.due);
        }
        if (val < 0) {
            $(this).val(0);
        }
        if (val > 0 && !$row.find('.ff-pick-check').is(':checked')) {
            $row.find('.ff-pick-check').prop('checked', true);
        }
        syncSelectAll();
        syncReady();
    });

    $(document).on('change', '.ff-pick-check', function () {
        var $row = $(this).closest('tr');
        var meta = rowMeta($row);
        if (this.checked) {
            // Fill remaining due only when Pick now is empty — keep typed partial qty.
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
        if (rowMeta($row).already > 0 && $row.find('.ff-pick-check').length) {
            $row.find('.ff-pick-check').prop('checked', true);
        }
    });
    syncSelectAll();
    syncReady();
});
