/**
 * Add/Edit Sale: raise every line's unit price by a % (e.g. to cover shipping)
 * without showing a separate charge on the invoice. Items added later get the same %.
 * Each row keeps its original price in data-upcharge-base so the increase can be removed.
 */
$(function() {
    var $bar = $('#sell_upcharge_bar');
    if (!$bar.length || !$('#pos_table').length) {
        return;
    }

    var $hidden = $('#upcharge_percent');
    var current = parseFloat($hidden.val()) || 0;

    function round2(n) {
        return Math.round((n + Number.EPSILON) * 100) / 100;
    }

    function rows() {
        return $('#pos_table tr.product_row');
    }

    function priceField($tr) {
        return typeof pos_row_field === 'function'
            ? pos_row_field($tr, 'input.pos_unit_price')
            : $tr.find('input.pos_unit_price');
    }

    function writeRowPrice($tr, price) {
        window.__posPriceLogicLock = true;
        try {
            __write_number(priceField($tr), price);
        } finally {
            window.__posPriceLogicLock = false;
        }
        if (typeof pos_mark_row_manual_price === 'function') {
            pos_mark_row_manual_price($tr);
        }
        pos_each_row($tr);
    }

    function rowBase($tr) {
        var base = $tr.attr('data-upcharge-base');
        if (base !== undefined && base !== '') {
            return parseFloat(base);
        }
        return __read_number(priceField($tr));
    }

    function applyToRow($tr, percent) {
        var base = rowBase($tr);
        $tr.attr('data-upcharge-base', base);
        writeRowPrice($tr, round2(base * (1 + percent / 100)));
    }

    function refreshUi() {
        $hidden.val(current > 0 ? current : 0);
        if (current > 0) {
            $('#upcharge_active_value').text(+current.toFixed(2));
            $('#upcharge_active_badge').show();
            $('#upcharge_remove').show();
        } else {
            $('#upcharge_active_badge').hide();
            $('#upcharge_remove').hide();
        }
    }

    function apply(percent) {
        percent = parseFloat(percent);
        if (!(percent > 0)) {
            toastr.error(LANG.upcharge_invalid || 'Enter a percentage greater than 0.');
            return;
        }
        rows().each(function() {
            applyToRow($(this), percent);
        });
        current = percent;
        pos_total_row();
        refreshUi();
    }

    function remove() {
        rows().each(function() {
            var $tr = $(this);
            var base = $tr.attr('data-upcharge-base');
            if (base !== undefined && base !== '') {
                writeRowPrice($tr, round2(parseFloat(base)));
                $tr.removeAttr('data-upcharge-base');
            }
        });
        current = 0;
        pos_total_row();
        refreshUi();
    }

    // Edit sale: saved prices already include the stored %, so recover each row's original price
    if (current > 0) {
        rows().each(function() {
            var $tr = $(this);
            var price = __read_number(priceField($tr));
            $tr.attr('data-upcharge-base', round2(price / (1 + current / 100)));
        });
    }

    // New rows get the active % automatically
    if (typeof window.pos_insert_product_row === 'function') {
        var originalInsert = window.pos_insert_product_row;
        window.pos_insert_product_row = function(result) {
            originalInsert.apply(this, arguments);
            if (current > 0) {
                var $tr = rows().first();
                if ($tr.length && $tr.attr('data-upcharge-base') === undefined) {
                    applyToRow($tr, current);
                    pos_total_row();
                }
            }
        };
    }

    // A hand-typed price becomes that row's new normal price
    $(document).on('change', 'input.pos_unit_price, input.pos_unit_price_inc_tax', function() {
        if (window.__posPriceLogicLock) {
            return;
        }
        var $tr = typeof pos_product_row_from_el === 'function' ? pos_product_row_from_el($(this)) : $(this).closest('tr');
        if ($tr.length) {
            $tr.removeAttr('data-upcharge-base');
        }
    });

    $bar.on('click', '.sell-upcharge-quick', function() {
        apply($(this).data('percent'));
    });
    $bar.on('click', '#upcharge_apply_custom', function() {
        apply($('#upcharge_custom_percent').val());
    });
    $bar.on('keydown', '#upcharge_custom_percent', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            apply($(this).val());
        }
    });
    $bar.on('click', '#upcharge_remove', function() {
        remove();
    });

    refreshUi();
});
