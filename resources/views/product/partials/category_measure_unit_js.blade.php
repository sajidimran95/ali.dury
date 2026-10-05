@php
  $category_unit_map = \App\Category::measureUnitMap((int) session('user.business_id'));
@endphp
<script>
$(function () {
    var unitMap = @json((object) $category_unit_map);
    var $unit = $('select#unit_id');
    if (!$unit.length) return;

    function selectedCategoryUnit() {
        var ids = [$('#child_category_id').val(), $('#sub_category_id').val(), $('#category_id').val()];
        for (var i = 0; i < ids.length; i++) {
            if (ids[i] && unitMap[ids[i]]) return unitMap[ids[i]];
        }
        return null;
    }

    function note() {
        var $n = $('.js-category-unit-note');
        if (!$n.length) {
            $n = $('<p class="help-block js-category-unit-note" style="margin:4px 0 0;color:#6d28d9;font-weight:600;"></p>');
            $unit.closest('.form-group').append($n);
        }
        return $n;
    }

    function firstOpeningQty() {
        return $('#product_opening_stock_table tbody tr:first .purchase_quantity');
    }

    function toggleAmount(cu) {
        var $wrap = $('#category_measure_amount_wrap');
        if (!$wrap.length) return;
        $wrap.toggleClass('hide', !cu);
        $('.js-category-unit-name').text(cu ? cu.name : '');
        var $amt = $('#category_measure_amount');
        if (cu && !$amt.prop('readonly') && !$amt.is(':focus')) {
            var $q = firstOpeningQty();
            if ($q.length) __write_number($amt, __read_number($q) || 0);
        }
    }

    $(document).on('keyup change', '#category_measure_amount', function () {
        if ($(this).prop('readonly')) return;
        if (!$('#enable_stock').is(':checked')) {
            $('#enable_stock').iCheck ? $('#enable_stock').iCheck('check') : $('#enable_stock').prop('checked', true).trigger('change');
        }
        var $q = firstOpeningQty();
        if (!$q.length) return;
        __write_number($q, __read_number($(this)) || 0);
        $q.trigger('change');
    });
    $(document).on('keyup change', '#product_opening_stock_table tbody tr:first .purchase_quantity', function () {
        var $amt = $('#category_measure_amount');
        if ($amt.length && !$amt.prop('readonly') && !$amt.is(':focus')) {
            __write_number($amt, __read_number($(this)) || 0);
        }
    });

    function applyCategoryUnit() {
        var cu = selectedCategoryUnit();
        $('#unit_id_locked').remove();
        toggleAmount(cu);

        if (!cu) {
            if ($unit.prop('disabled') && $unit.data('cat-locked')) {
                $unit.prop('disabled', false).data('cat-locked', false).trigger('change.select2');
            }
            $('.js-category-unit-note').remove();
            return;
        }

        if (!$unit.find('option[value="' + cu.unit_id + '"]').length) {
            $unit.append(new Option(cu.name + ' (' + cu.name + ')', cu.unit_id));
        }
        var changed = String($unit.val()) !== String(cu.unit_id);
        $unit.val(String(cu.unit_id));
        if (changed) $unit.trigger('change');
        $unit.prop('disabled', true).data('cat-locked', true).trigger('change.select2');
        $unit.after('<input type="hidden" name="unit_id" id="unit_id_locked" value="' + cu.unit_id + '">');
        note().html('<i class="fa fa-lock"></i> Stock counted in <strong>' + $('<span>').text(cu.name).html() + '</strong> (set by category)');
    }

    $(document).on('change', '#category_id, #sub_category_id, #child_category_id', function () {
        setTimeout(applyCategoryUnit, 30);
    });
    $(document).ajaxComplete(function (e, xhr, settings) {
        if (settings && settings.url && /sub-categories|get_sub_categories|child/i.test(settings.url)) {
            setTimeout(applyCategoryUnit, 60);
        }
    });
    setTimeout(applyCategoryUnit, 100);
});
</script>
