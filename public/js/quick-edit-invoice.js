(function ($) {
    function qeMoney(n) {
        n = parseFloat(n);
        if (isNaN(n)) {
            n = 0;
        }
        return n.toFixed(2);
    }

    function qeMoneyLabel(n) {
        var sym = $('#qe_invoice_form').attr('data-symbol') || '';
        return sym + qeMoney(n);
    }

    function qeRecalc() {
        var $form = $('#qe_invoice_form');
        if (!$form.length) {
            return;
        }
        var sub = 0;
        var qtyTotal = 0;
        $('#qe_lines tr').each(function () {
            var $tr = $(this);
            var qty = parseFloat($tr.find('.qe-qty').val()) || 0;
            qtyTotal += qty;
            var unit = parseFloat($tr.find('.qe-unit').val()) || 0;
            var dtype = $tr.find('.qe-dtype').val();
            var damt = parseFloat($tr.find('.qe-damount').val()) || 0;
            var rate = parseFloat($tr.find('.qe-tax-rate').val()) || 0;
            var after = unit;
            if (dtype === 'percentage') {
                after = unit * (1 - damt / 100);
            } else if (dtype === 'fixed') {
                after = unit - damt;
            }
            if (after < 0) {
                after = 0;
            }
            var line = after * (1 + rate) * qty;
            sub += line;
            $tr.find('.qe-line-total').text(qeMoneyLabel(line));
        });
        var odType = $('#qe_order_discount_type').val();
        var odAmt = parseFloat($('#qe_order_discount_amount').val()) || 0;
        var discount = odType === 'percentage' ? sub * (odAmt / 100) : odAmt;
        if (discount < 0) {
            discount = 0;
        }
        if (discount > sub) {
            discount = sub;
        }
        var taxPct = parseFloat($form.attr('data-tax-percent')) || 0;
        var tax = (sub - discount) * (taxPct / 100);
        var shipping = parseFloat($('#qe_shipping').val()) || 0;
        var extra = parseFloat($form.attr('data-extra')) || 0;
        var grand = sub - discount + tax + shipping + extra;
        var paid = parseFloat($form.attr('data-paid')) || 0;
        var qtyLabel = Math.abs(qtyTotal - Math.round(qtyTotal)) < 0.0001
            ? String(Math.round(qtyTotal))
            : String(Math.round(qtyTotal * 100) / 100);
        $('#qe_qty_total').text('QTY ' + qtyLabel);
        $('#qe_grand').text(qeMoneyLabel(grand));
        $('#qe_due').text(qeMoneyLabel(Math.max(0, grand - paid)));
    }

    function qeAddProduct(p) {
        var variationId = p.variation_id;
        var $existing = $('#qe_lines tr[data-variation-id="' + variationId + '"]');
        if ($existing.length) {
            var $qty = $existing.find('.qe-qty');
            var next = (parseFloat($qty.val()) || 0) + 1;
            $qty.val(String(next).replace(/\.00$/, ''));
            qeRecalc();
            return;
        }
        var $form = $('#qe_invoice_form');
        var tpl = document.getElementById('qe_row_template');
        if (!tpl || !tpl.content) {
            return;
        }
        var i = parseInt($form.attr('data-next'), 10) || 0;
        $form.attr('data-next', i + 1);
        var name = p.name || '';
        if (p.variation && String(p.variation).toUpperCase() !== 'DUMMY') {
            name += ' (' + p.variation + ')';
        }
        var sku = p.sub_sku || p.sku || '';
        var price = parseFloat(p.selling_price) || 0;
        var row = tpl.content.querySelector('tr').cloneNode(true);
        var $row = $(row);
        $row.attr('data-variation-id', variationId);
        $row.find('.qe-sku').text(sku);
        $row.find('.qe-name').text(name);
        $row.find('[data-name]').each(function () {
            $(this).attr('name', 'lines[' + i + '][' + $(this).attr('data-name') + ']');
        });
        $row.find('[data-name="product_id"]').val(p.product_id || '');
        $row.find('[data-name="variation_id"]').val(variationId);
        $row.find('.qe-unit').val(qeMoney(price));
        $('#qe_lines').append($row);
        qeRecalc();
    }

    function qeProductsFromResponse(res) {
        if ($.isArray(res)) {
            return res;
        }
        if (res && $.isArray(res.products)) {
            return res.products;
        }
        return [];
    }

    function qeShowPicks(products) {
        var $list = $('#qe_scan_picks');
        $list.empty();
        if (!products.length) {
            $list.hide();
            return;
        }
        products.slice(0, 8).forEach(function (p) {
            var label = (p.sub_sku || p.sku || '') + ' — ' + (p.name || '');
            var $li = $('<li></li>').text(label);
            $li.on('click', function () {
                qeAddProduct(p);
                $list.hide().empty();
                $('#qe_scan').val('').focus();
                $('#qe_scan_msg').text('');
            });
            $list.append($li);
        });
        $list.show();
    }

    function qeScan() {
        var term = $.trim($('#qe_scan').val() || '');
        var $msg = $('#qe_scan_msg');
        if (!term) {
            return;
        }
        var locationId = $('#qe_invoice_form').data('location-id');
        $msg.text('');
        $.ajax({
            url: '/products/list',
            dataType: 'json',
            data: {
                term: term,
                location_id: locationId,
                not_for_selling: 0,
                search_mode: 'scan',
                search_fields: ['sku']
            },
            success: function (res) {
                var products = qeProductsFromResponse(res);
                if (products.length === 1) {
                    qeAddProduct(products[0]);
                    $('#qe_scan').val('').focus();
                    $('#qe_scan_picks').hide().empty();
                    return;
                }
                if (products.length > 1) {
                    qeShowPicks(products);
                    return;
                }
                $.ajax({
                    url: '/products/list',
                    dataType: 'json',
                    data: {
                        term: term,
                        location_id: locationId,
                        not_for_selling: 0,
                        search_fields: ['name', 'sku']
                    },
                    success: function (searchRes) {
                        var found = qeProductsFromResponse(searchRes);
                        if (found.length === 1) {
                            qeAddProduct(found[0]);
                            $('#qe_scan').val('').focus();
                            $('#qe_scan_picks').hide().empty();
                        } else if (found.length > 1) {
                            qeShowPicks(found);
                        } else {
                            $msg.text('No item found for that code.');
                            $('#qe_scan_picks').hide().empty();
                        }
                    }
                });
            },
            error: function () {
                $msg.text('Could not search items.');
            }
        });
    }

    $(function () {
        if ($('#qe_invoice_form').length) {
            qeRecalc();
            $('#qe_scan').focus();
        }
    });

    $(document).on('keydown', '#qe_scan', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            qeScan();
        }
    });

    $(document).on('click', '#qe_lines .qe-plus, #qe_lines .qe-minus', function () {
        var $qty = $(this).closest('.qe-stepper').find('.qe-qty');
        var qty = parseFloat($qty.val()) || 0;
        qty += $(this).hasClass('qe-plus') ? 1 : -1;
        if (qty < 0) {
            qty = 0;
        }
        $qty.val(Number.isInteger(qty) ? String(qty) : qeMoney(qty));
        qeRecalc();
    });

    $(document).on('click', '#qe_lines .qe-remove', function () {
        $(this).closest('tr').remove();
        qeRecalc();
    });

    $(document).on('input change', '#qe_invoice_form .qe-qty, #qe_invoice_form .qe-unit, #qe_invoice_form .qe-dtype, #qe_invoice_form .qe-damount, #qe_order_discount_type, #qe_order_discount_amount, #qe_shipping', function () {
        qeRecalc();
    });

    $(document).on('submit', '#qe_invoice_form', function (e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $('#qe_save');
        $btn.prop('disabled', true);
        $.ajax({
            method: 'POST',
            url: $form.attr('action'),
            data: $form.serialize(),
            dataType: 'json',
            success: function (res) {
                if (res && res.success) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(res.msg || 'Saved');
                    }
                    window.location.reload();
                } else if (typeof toastr !== 'undefined') {
                    toastr.error((res && res.msg) || 'Could not save');
                }
            },
            error: function () {
                if (typeof toastr !== 'undefined') {
                    toastr.error('Could not save');
                }
            },
            complete: function () {
                $btn.prop('disabled', false);
            }
        });
    });
})(jQuery);
