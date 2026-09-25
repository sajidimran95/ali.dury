$(document).ready(function() {
    $(document).on('change', '.purchase_quantity', function() {
        update_table_total($(this).closest('table'));
    });
    $(document).on('change', '.unit_price', function() {
        update_table_total($(this).closest('table'));
    });

    $(document).on('click', '.remove_stock_row', function() {
        var $table = $(this).closest('table');
        $(this).closest('tr').remove();
        update_table_total($table);
    });

    // Box / Case helpers on the opening stock screen (stock is always saved in the base unit)
    function osPackFactor(el) {
        return parseFloat($(el).closest('table.add_opening_stock_table').data('pack-factor')) || 0;
    }
    $(document).on('keyup change', 'table.add_opening_stock_table .os_pack_qty', function() {
        var f = osPackFactor(this);
        if (!(f > 0)) return;
        var $qty = $(this).closest('td').find('.purchase_quantity');
        __write_number($qty, (__read_number($(this)) || 0) * f);
        update_table_total($(this).closest('table'));
    });
    $(document).on('keyup change', 'table.add_opening_stock_table .purchase_quantity', function() {
        var f = osPackFactor(this);
        var $pack = $(this).closest('td').find('.os_pack_qty');
        if (!(f > 0) || !$pack.length || $pack.is(':focus')) return;
        __write_number($pack, (__read_number($(this)) || 0) / f);
    });
    $(document).on('keyup change', 'table.add_opening_stock_table .os_pack_price', function() {
        var f = osPackFactor(this);
        if (!(f > 0)) return;
        var $price = $(this).closest('td').find('.unit_price');
        __write_number($price, (__read_number($(this)) || 0) / f);
        update_table_total($(this).closest('table'));
    });
    $(document).on('keyup change', 'table.add_opening_stock_table .unit_price', function() {
        var f = osPackFactor(this);
        var $pack = $(this).closest('td').find('.os_pack_price');
        if (!(f > 0) || !$pack.length || $pack.is(':focus')) return;
        __write_number($pack, (__read_number($(this)) || 0) * f);
    });

    $('.os_exp_date').datepicker({
        autoclose: true,
        format: datepicker_date_format,
    });

    $(document).on('click', '.add_stock_row', function() {
        var tr = $(this).data('row-html');
        var key = parseInt($(this).data('sub-key'));
        tr = tr.replace(/\__subkey__/g, key);
        $(this).data('sub-key', key + 1);

        $(tr)
            .insertAfter($(this).closest('tr'))
            .find('.os_exp_date')
            .datepicker({
                autoclose: true,
                format: datepicker_date_format,
            });
            
            $(this).closest('tr').next('tr').find('.os_date').datetimepicker({
                format: moment_date_format + ' ' + moment_time_format,
                ignoreReadonly: true,
            });
    });

    $(document).on('click', '.add-opening-stock', function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).data('href'),
            dataType: 'html',
            success: function(result) {
                $('#opening_stock_modal')
                    .html(result)
                    .modal('show');
            },
        });
    });
});

//Re-initialize data picker on modal opening
 $('#opening_stock_modal').on('shown.bs.modal', function(e) {
    $('#opening_stock_modal .os_exp_date').datepicker({
        autoclose: true,
        format: datepicker_date_format,
    });
    $('#opening_stock_modal .os_date').datetimepicker({
        format: moment_date_format + ' ' + moment_time_format,
        ignoreReadonly: true,
        widgetPositioning: {
            horizontal: 'right',
            vertical: 'bottom'
        }
    });
 });

$(document).on('click', 'button#add_opening_stock_btn', function(e) {
    e.preventDefault();
    var btn = $(this);
    var data = $('form#add_opening_stock_form').serialize();

    $.ajax({
        method: 'POST',
        url: $('form#add_opening_stock_form').attr('action'),
        dataType: 'json',
        data: data,
        beforeSend: function(xhr) {
            __disable_submit_button(btn);
        },
        success: function(result) {
            if (result.success == true) {
                $('#opening_stock_modal').modal('hide');
                toastr.success(result.msg);
            } else {
                toastr.error(result.msg);
            }
        },
    });
    return false;
});

function update_table_total(table) {
    var total_subtotal = 0;
    table.find('tbody tr').each(function() {
        var qty = __read_number($(this).find('.purchase_quantity'));
        var unit_price = __read_number($(this).find('.unit_price'));
        var row_subtotal = qty * unit_price;
        $(this)
            .find('.row_subtotal_before_tax')
            .text(__number_f(row_subtotal));
        total_subtotal += row_subtotal;
    });
    table.find('tfoot tr #total_subtotal').text(__currency_trans_from_en(total_subtotal, true));
    table.find('tfoot tr #total_subtotal_hidden').val(total_subtotal);
}
