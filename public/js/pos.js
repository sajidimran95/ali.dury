var global_brand_id = null;
var global_p_category_id = null;
var global_is_clear_local_storage = false;
var posCartPriceRefreshEnabled = false;

function pos_play_success_sound() {
    var audio = document.getElementById('success-audio');
    if (audio) {
        try { audio.currentTime = 0; audio.play(); } catch (e) {}
    }
}

function pos_sync_empty_state() {
    var hasRows = $('#pos_table tbody .product_row').length > 0;
    $('#pos_table').toggleClass('pos-has-rows', hasRows);
    $('#add_pos_sell_form, #edit_pos_sell_form').toggleClass('pos-has-rows', hasRows);
}

/**
 * Keep newest cart activity at the top (after empty-state placeholder if present).
 * Used by create + edit POS whenever a line is added or re-scanned.
 */
function pos_line_edit_row($tr) {
    $tr = $($tr);
    if (!$tr.length) {
        return $();
    }
    var idx = $tr.attr('data-row_index');
    if (typeof idx !== 'undefined') {
        var $byIdx = $('table#pos_table tbody tr.pos-line-edit-row[data-row_index="' + idx + '"]');
        if ($byIdx.length) {
            return $byIdx.first();
        }
    }
    var $next = $tr.next('tr.pos-line-edit-row');
    if ($next.length && (typeof idx === 'undefined' || String($next.attr('data-row_index')) === String(idx))) {
        return $next;
    }
    return $();
}

function pos_attach_line_edit_after_product($product) {
    $product = $($product);
    if (!$product.length || !$product.hasClass('product_row')) {
        return $();
    }
    var $edit = pos_line_edit_row($product);
    if (!$edit.length) {
        return $();
    }
    var p = $product.get(0);
    var e = $edit.get(0);
    if (p && e && p.nextElementSibling !== e && p.parentNode) {
        p.parentNode.insertBefore(e, p.nextSibling);
    }
    return $edit;
}

function pos_reattach_all_line_edits() {
    $('table#pos_table tbody tr.product_row').each(function() {
        pos_attach_line_edit_after_product($(this));
    });
}

function pos_close_line_edit($tr) {
    $tr = $($tr);
    var $edit = $tr && $tr.length ? pos_line_edit_row($tr) : $();
    if ($edit.length) {
        $edit.removeClass('is-open');
    }
    if ($tr.length) {
        $tr.removeClass('is-editing');
    }
}

function pos_close_all_line_edits() {
    $('#pos_table tr.pos-line-edit-row').removeClass('is-open');
    $('#pos_table tr.product_row').removeClass('is-editing is-selected');
}

function pos_close_suspend_modal_to_top(thenFn) {
    var $modal = $('#confirmSuspendModal');
    var finish = function () {
        $modal.removeClass('is-sliding-top');
        $modal.find('.modal-dialog').off('transitionend.posParkSlide');
        $modal.modal('hide');
        if (typeof thenFn === 'function') {
            thenFn();
        }
    };
    if (!$modal.length || (!$modal.hasClass('in') && !$modal.hasClass('show'))) {
        $modal.modal('hide');
        if (typeof thenFn === 'function') {
            thenFn();
        }
        return;
    }
    $modal.addClass('is-sliding-top');
    var $dialog = $modal.find('.modal-dialog').first();
    var done = false;
    var once = function () {
        if (done) {
            return;
        }
        done = true;
        finish();
    };
    $dialog.off('transitionend.posParkSlide').on('transitionend.posParkSlide', function (e) {
        if (e.target !== this) {
            return;
        }
        once();
    });
    setTimeout(once, 500);
}

function pos_open_line_edit($tr) {
    $tr = $($tr);
    if (!$tr.length) {
        return;
    }
    pos_attach_line_edit_after_product($tr);
    var $edit = pos_line_edit_row($tr);
    var $panel = pos_row_edit_modal($tr);
    pos_close_all_line_edits();
    if ($panel.length) {
        pos_sync_row_edit_modal_from_cart($panel, $tr);
    }
    $tr.addClass('is-editing');
    $edit.addClass('is-open');
    var $first = $edit.find('input, select, textarea').filter(':visible').first();
    if ($first.length) {
        $first.focus().select();
    }
}

function pos_toggle_line_edit($tr) {
    $tr = $($tr);
    var $edit = pos_line_edit_row($tr);
    if ($edit.hasClass('is-open')) {
        pos_close_line_edit($tr);
        return;
    }
    pos_open_line_edit($tr);
}

function pos_bring_product_row_to_top($row) {
    if (!$row || !$row.length) {
        return $row;
    }
    pos_close_all_line_edits();
    $row = $($row);
    var $product = $row.filter ? $row.filter('tr.product_row').first() : $();
    if (!$product.length && $row.hasClass && $row.hasClass('product_row')) {
        $product = $row;
    }
    if (!$product.length && $row.hasClass && $row.hasClass('pos-cart-line')) {
        $product = $row.find('tr.product_row').first();
    }
    if (!$product.length) {
        return $row;
    }
    var $line = $product.closest('tbody.pos-cart-line');
    var $emptyBody = $('#pos_table > tbody.pos-cart-empty-body');
    if ($line.length) {
        if ($emptyBody.length) {
            $emptyBody.after($line);
        } else {
            var $thead = $('#pos_table > thead');
            if ($thead.length) {
                $thead.after($line);
            } else {
                $('#pos_table').prepend($line);
            }
        }
        pos_attach_line_edit_after_product($product);
        return $product;
    }
    var idx = $product.attr('data-row_index');
    var $edit = $row.filter ? $row.filter('tr.pos-line-edit-row') : $();
    if (!$edit.length && typeof idx !== 'undefined') {
        $edit = $('table#pos_table tr.pos-line-edit-row[data-row_index="' + idx + '"]');
    }
    var $tbody = $('table#pos_table tbody').first();
    var $empty = $tbody.children('tr.pos-empty-state-row').first();
    if ($empty.length) {
        $empty.after($product);
    } else {
        $tbody.prepend($product);
    }
    if ($edit.length) {
        pos_attach_line_edit_after_product($product);
    }
    return $product;
}

/**
 * Line-edit modals may be moved to body for stacking. Resolve row/modal fields so
 * price/tax/discount still work and form serialize includes them after close.
 */
function pos_row_edit_modal($row) {
    var idx = $row.attr('data-row_index');
    if (typeof idx !== 'undefined') {
        var $byId = $('#row_edit_product_price_modal_' + idx);
        if ($byId.length) {
            return $byId;
        }
    }
    return $row.find('.row_edit_product_price_model');
}

function pos_row_field($row, selector) {
    var $found = $row.find(selector);
    if ($found.length) {
        return $found;
    }
    return pos_row_edit_modal($row).find(selector);
}

function pos_product_row_from_el($el) {
    var $tr = $el.closest('tr.product_row');
    if ($tr.length) {
        return $tr;
    }
    var $editTr = $el.closest('tr.pos-line-edit-row');
    if ($editTr.length) {
        var eidx = $editTr.attr('data-row_index');
        if (typeof eidx !== 'undefined') {
            return $('table#pos_table tbody tr.product_row[data-row_index="' + eidx + '"]');
        }
    }
    var $modal = $el.closest('.row_edit_product_price_model');
    if (!$modal.length) {
        return $();
    }
    var id = $modal.attr('id') || '';
    var match = id.match(/row_edit_product_price_modal_(\d+)/);
    if (match) {
        return $('table#pos_table tbody tr.product_row[data-row_index="' + match[1] + '"]');
    }
    return $();
}

function pos_remember_row_edit_modal_parent($modal) {
    if (!$modal.data('original-parent')) {
        $modal.data('original-parent', $modal.parent());
    }
}

/**
 * Put cart-item price/discount popup back inside its product row so unit_price
 * is included in form POST. Missing unit_price causes "Something went wrong".
 */
function pos_restore_row_edit_modal($modal) {
    $modal = $($modal);
    if (!$modal.length) {
        return;
    }

    var $parent = $modal.data('original-parent');
    if ($parent && $parent.length && $parent.closest('body').length && $modal.parent()[0] !== $parent[0]) {
        $modal.appendTo($parent);
        return;
    }

    // Fallback if original parent was lost (still on body / outside form)
    if (!$modal.closest('tr.product_row, tr.pos-line-edit-row').length) {
        var id = $modal.attr('id') || '';
        var match = id.match(/row_edit_product_price_modal_(\d+)/);
        if (match) {
            var $editTr = $('table#pos_table tbody tr.pos-line-edit-row[data-row_index="' + match[1] + '"]');
            if ($editTr.length) {
                $modal.appendTo($editTr.children('td').first());
                return;
            }
            var $tr = $('table#pos_table tbody tr.product_row[data-row_index="' + match[1] + '"]');
            if ($tr.length) {
                var $nameCell = $tr.children('td.serial_no').length
                    ? $tr.children('td').eq(1)
                    : $tr.children('td').first();
                $modal.appendTo($nameCell);
            }
        }
    }
}

function pos_restore_all_row_edit_modals() {
    $('.row_edit_product_price_model').each(function() {
        pos_restore_row_edit_modal($(this));
    });
}

/** Load current cart line values into the price-edit popup (avoids stale catalog price). */
function pos_sync_row_edit_modal_from_cart($modal, $tr) {
    $modal = $($modal);
    $tr = $($tr);
    if (!$modal.length || !$tr.length) {
        return;
    }

    var inc_tax = __read_number($tr.find('input.pos_unit_price_inc_tax'));
    var tax_rate = pos_row_field($tr, 'select.tax_id').find(':selected').data('rate') || 0;
    var discounted = typeof __get_principle === 'function'
        ? __get_principle(inc_tax, tax_rate)
        : inc_tax;
    var unit_price = typeof get_unit_price_from_discounted_unit_price === 'function'
        ? get_unit_price_from_discounted_unit_price($tr, discounted)
        : discounted;

    __write_number($modal.find('input.pos_unit_price'), unit_price);

    var discType = pos_row_field($tr, 'select.row_discount_type').val();
    if (discType) {
        $modal.find('select.row_discount_type').val(discType);
    }
    __write_number($modal.find('input.row_discount_amount'), __read_number(pos_row_field($tr, 'input.row_discount_amount')));

    var taxId = pos_row_field($tr, 'select.tax_id').val();
    if (taxId !== undefined && taxId !== null) {
        $modal.find('select.tax_id').val(taxId);
    }

    var note = pos_row_field($tr, 'textarea[name*="sell_line_note"]').val();
    if (note !== undefined) {
        $modal.find('textarea[name*="sell_line_note"]').val(note);
    }

    var qty = __read_number($tr.find('input.pos_quantity'));
    if (!isNaN(qty)) {
        __write_number($modal.find('input.pos-line-edit-qty'), qty);
    }
}

/** Put line-edit fields back in the form and keep MSP from blocking line discounts. */
function pos_prepare_form_for_submit() {
    if (typeof pos_restore_all_row_edit_modals === 'function') {
        pos_restore_all_row_edit_modals();
    }
    $('table#pos_table tbody tr.product_row').each(function () {
        var $row = $(this);
        var discAmt = __read_number(pos_row_field($row, 'input.row_discount_amount'));
        var $inc = $row.find('input.pos_unit_price_inc_tax');
        if (!$inc.length) {
            return;
        }
        // MSP belongs on pre-discount unit price. After a line discount, inc-tax
        // is intentionally lower — don't fail finalize because of that.
        if (discAmt && !isNaN(discAmt) && discAmt > 0) {
            $inc.removeAttr('data-rule-min-value');
            try {
                if ($inc.rules) {
                    $inc.rules('remove', 'min-value');
                }
            } catch (err) {}
        }
    });
}
function pos_mark_row_manual_price($tr) {
    $tr = $($tr);
    if (!$tr.length) {
        return;
    }
    $tr.attr('data-pos-manual-price', '1').data('pos-manual-price', 1);
}

function pos_row_has_manual_price($tr) {
    $tr = $($tr);
    if (!$tr.length) {
        return false;
    }
    var flagged = $tr.data('pos-manual-price');
    return flagged === 1 || flagged === '1' || flagged === true || String($tr.attr('data-pos-manual-price') || '') === '1';
}

/** Remember that this cart line discount was typed by the cashier. */
function pos_mark_row_manual_discount($tr) {
    $tr = $($tr);
    if (!$tr.length) {
        return;
    }
    $tr.attr('data-pos-manual-discount', '1').data('pos-manual-discount', 1);
}

function pos_clear_row_manual_discount($tr) {
    $tr = $($tr);
    if (!$tr.length) {
        return;
    }
    $tr.removeAttr('data-pos-manual-discount').removeData('pos-manual-discount');
}

function pos_row_has_manual_discount($tr) {
    $tr = $($tr);
    if (!$tr.length) {
        return false;
    }
    var flagged = $tr.data('pos-manual-discount');
    return flagged === 1 || flagged === '1' || flagged === true || String($tr.attr('data-pos-manual-discount') || '') === '1';
}

/**
 * Catalog / list unit price (ex-tax) for a cart line.
 * Used to turn a lowered typed price into a line discount.
 */
function pos_row_list_price($tr) {
    $tr = $($tr);
    var listed = parseFloat($tr.attr('data-pos-list-price'));
    if (!isNaN(listed) && listed > 0) {
        return listed;
    }
    var base = parseFloat($tr.find('input.hidden_base_unit_sell_price').val());
    var mult = parseFloat($tr.find('input.base_unit_multiplier').val());
    if (isNaN(mult) || mult <= 0) {
        mult = 1;
    }
    if (!isNaN(base) && base > 0) {
        return base * mult;
    }
    var current = __read_number(pos_row_field($tr, 'input.pos_unit_price'));
    return !isNaN(current) && current > 0 ? current : 0;
}

function pos_set_row_list_price($tr, price) {
    $tr = $($tr);
    if (!$tr.length) {
        return;
    }
    var n = parseFloat(price);
    if (isNaN(n) || n < 0) {
        return;
    }
    $tr.attr('data-pos-list-price', n);
}

function pos_ensure_row_list_price($tr) {
    $tr = $($tr);
    if (!$tr.length) {
        return;
    }
    if (!$tr.attr('data-pos-list-price')) {
        pos_set_row_list_price($tr, pos_row_list_price($tr));
    }
}

/**
 * Show current line total, and crossed-out original when unit price was reduced.
 */
function pos_update_line_price_compare_display($tr) {
    $tr = $($tr);
    if (!$tr.length) {
        return;
    }
    var $orig = $tr.find('.pos-line-orig-price');
    if (!$orig.length) {
        return;
    }

    pos_ensure_row_list_price($tr);
    var qty = __read_number($tr.find('input.pos_quantity'));
    if (isNaN(qty) || qty <= 0) {
        qty = 1;
    }

    var currentTotal = __read_number($tr.find('input.pos_line_total'));
    if (isNaN(currentTotal)) {
        currentTotal = 0;
    }

    var listEx = pos_row_list_price($tr);
    var tax_rate = 0;
    var $tax = pos_row_field($tr, 'select.tax_id');
    if ($tax.length) {
        tax_rate = parseFloat($tax.find(':selected').data('rate')) || 0;
    }
    var listInc = typeof __add_percent === 'function' ? __add_percent(listEx, tax_rate) : listEx;
    var originalTotal = qty * listInc;

    // Markdown / discount: show list line total struck through under the new total
    var eps = 0.009;
    if (originalTotal > currentTotal + eps) {
        $orig
            .text(__currency_trans_from_en(originalTotal, true))
            .removeClass('hide')
            .attr('aria-hidden', 'false');
        $tr.addClass('has-price-change');
    } else {
        $orig.text('').addClass('hide').attr('aria-hidden', 'true');
        $tr.removeClass('has-price-change');
    }
}

/**
 * Lightspeed-style price edit:
 * - typed sell price < list  → keep list as unit_price, auto % line discount
 * - typed sell price >= list → set unit_price to typed, clear line discount
 *
 * @param {jQuery} $tr
 * @param {number} typedExTax  Desired sell price excluding tax
 * @returns {boolean} true when discount/price fields were rewritten
 */
function pos_apply_typed_sell_price($tr, typedExTax) {
    $tr = $($tr);
    typedExTax = parseFloat(typedExTax);
    if (!$tr.length || isNaN(typedExTax) || typedExTax < 0) {
        return false;
    }

    pos_ensure_row_list_price($tr);
    var list = pos_row_list_price($tr);
    if (!list || list <= 0) {
        return false;
    }

    var $unit = pos_row_field($tr, 'input.pos_unit_price');
    var $type = pos_row_field($tr, 'select.row_discount_type');
    var $amount = pos_row_field($tr, 'input.row_discount_amount');
    var eps = 0.0001;

    window.__posPriceLogicLock = true;
    try {
        if (typedExTax < list - eps) {
            // Markdown → keep list price, convert to % discount
            __write_number($unit, list);
            var pct = ((list - typedExTax) / list) * 100;
            if ($type.length) {
                $type.val('percentage').data('prev-discount-type', 'percentage');
            }
            if ($amount.length) {
                __write_number($amount, Math.round(pct * 100) / 100);
            }
            pos_mark_row_manual_discount($tr);
            $tr.removeAttr('data-pos-manual-price').removeData('pos-manual-price');
        } else {
            // Same or higher → raise/set unit price, clear line discount
            __write_number($unit, typedExTax);
            if ($type.length) {
                $type.val('fixed').data('prev-discount-type', 'fixed');
            }
            if ($amount.length) {
                __write_number($amount, 0);
            }
            pos_clear_row_manual_discount($tr);
            if (typedExTax > list + eps) {
                pos_mark_row_manual_price($tr);
                pos_set_row_list_price($tr, typedExTax);
            }
        }
    } finally {
        window.__posPriceLogicLock = false;
    }
    return true;
}

function pos_recalc_row_after_price_edit($tr) {
    $tr = $($tr);
    if (!$tr.length) {
        return;
    }
    var discounted_unit_price = calculate_discounted_unit_price($tr);
    var tax_rate = pos_row_field($tr, 'select.tax_id').find(':selected').data('rate') || 0;
    var quantity = __read_number($tr.find('input.pos_quantity'));
    var unit_price_inc_tax = __add_percent(discounted_unit_price, tax_rate);
    var line_total = quantity * unit_price_inc_tax;

    __write_number($tr.find('input.pos_unit_price_inc_tax'), unit_price_inc_tax);
    __write_number($tr.find('input.pos_line_total'), line_total, false);
    $tr.find('span.pos_line_total_text').text(__currency_trans_from_en(line_total, true));
    $tr.find('span.pos_line_unit_price_text').text(__currency_trans_from_en(unit_price_inc_tax, true));
    if (typeof pos_update_line_price_compare_display === 'function') {
        pos_update_line_price_compare_display($tr);
    }
    if (typeof pos_each_row === 'function') {
        pos_each_row($tr);
    }
    if (typeof pos_total_row === 'function') {
        pos_total_row();
    }
    if (typeof round_row_to_iraqi_dinnar === 'function') {
        round_row_to_iraqi_dinnar($tr);
    }
}

/** Apply popup fields to the cart row and recalculate totals. */
function pos_apply_row_edit_modal($modal, $tr, markManual) {
    $modal = $($modal);
    if (!$tr || !$tr.length) {
        $tr = pos_product_row_from_el($modal);
    }
    pos_restore_row_edit_modal($modal);
    if (!$tr.length) {
        return;
    }

    var $panelQty = $modal.find('input.pos-line-edit-qty');
    if ($panelQty.length) {
        var panelQty = __read_number($panelQty);
        var $rowQty = $tr.find('input.pos_quantity');
        if ($rowQty.length && !isNaN(panelQty)) {
            __write_number($rowQty, panelQty);
            $rowQty.trigger('change');
        }
    }

    if (markManual) {
        pos_mark_row_manual_price($tr);
        var discAmt = __read_number(pos_row_field($tr, 'input.row_discount_amount'));
        if (discAmt) {
            pos_mark_row_manual_discount($tr);
        }
    }
    pos_each_row($tr);
    pos_total_row();
}

/**
 * Serialize POS form including unit_price / line discount from item popups.
 * Popups may be temporarily on body for Bootstrap stacking.
 */
function pos_serialize_sell_form($form) {
    $form = $form && $form.length ? $form : (typeof pos_form_obj !== 'undefined' ? pos_form_obj : $());
    pos_restore_all_row_edit_modals();
    var data = $form.serialize() || '';

    // Safety net: still-orphaned line popups (unit_price only lives here on POS)
    $('.row_edit_product_price_model').each(function() {
        var $modal = $(this);
        if (!$form.length || !$form.has($modal).length) {
            var extra = $modal.find('input, select, textarea').serialize();
            if (extra) {
                data += (data ? '&' : '') + extra;
            }
        }
    });

    return data;
}

$(document).ready(function() {
    pos_sync_empty_state();
    customer_set = false;
    //Prevent enter key function except texarea
    $('form').on('keyup keypress', function(e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13 && e.target.tagName != 'TEXTAREA') {
            e.preventDefault();
            return false;
        }
    });

    //For edit pos form
    if ($('form#edit_pos_sell_form').length > 0) {
        pos_total_row();
        pos_form_obj = $('form#edit_pos_sell_form');
    } else {
        pos_form_obj = $('form#add_pos_sell_form');
    }
    if ($('form#edit_pos_sell_form').length > 0 || $('form#add_pos_sell_form').length > 0) {
        initialize_printer();
    }

    $('select#select_location_id').change(function() {
        reset_pos_form();

        var default_price_group = $(this).find(':selected').data('default_price_group')
        if (default_price_group) {
            if($("#price_group option[value='" + default_price_group + "']").length > 0) {
                $("#price_group").val(default_price_group);
                $("#price_group").change();
            }
        }

        //Set default invoice scheme for location
        if ($('#invoice_scheme_id').length) {
            if($('input[name="is_direct_sale"]').length > 0){
                //default scheme for sale screen
                var invoice_scheme_id = $(this).find(':selected').data('default_sale_invoice_scheme_id');
            } else {
                var invoice_scheme_id =  $(this).find(':selected').data('default_invoice_scheme_id');
            }
            
            $("#invoice_scheme_id").val(invoice_scheme_id).change();
        }

        //Set default invoice layout for location
        if ($('#invoice_layout_id').length) {
            let invoice_layout_id = $(this).find(':selected').data('default_invoice_layout_id');
            $("#invoice_layout_id").val(invoice_layout_id).change();
        }
        
        //Set default price group
        if ($('#default_price_group').length) {
            var dpg = default_price_group ?
            default_price_group : 0;
            $('#default_price_group').val(dpg);
        }

        set_payment_type_dropdown();

        if ($('#types_of_service_id').length && $('#types_of_service_id').val()) {
            $('#types_of_service_id').change();
        }
    });

    //get customer
    var isPosSaleTheme = $('body').hasClass('pos-sale-theme');
    var posCustomerSelect2Opts = {
        ajax: {
            url: '/contacts/customers',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return { q: params.term, page: params.page };
            },
            processResults: function(data) {
                return { results: data };
            },
        },
        templateResult: function (data) {
            if (!data.id && !data.text) return data.text;
            var template = '';
            var businessName = (data.supplier_business_name || '').toString().trim();
            var personName = (data.contact_person || data.first_name || '').toString().trim();
            // Prefer company/business name first; fall back to person name
            var primary = businessName || personName || (data.text || '');
            template += primary + '<br>';
            if (personName && businessName && businessName !== personName) {
                template += personName + '<br>';
            }
            template += LANG.mobile + ': ' + data.mobile;
            if (typeof(data.total_rp) != 'undefined') {
                var rp = data.total_rp ? data.total_rp : 0;
                template += "<br><i class='fa fa-gift text-success'></i> " + rp;
            }
            return template;
        },
        minimumInputLength: 1,
        language: {
            inputTooShort: function (args) {
                return LANG.please_enter + args.minimum + LANG.or_more_characters;
            },
            noResults: function() {
                var name = '';
                try { name = $('#customer_id').data('select2').dropdown.$search.val() || ''; } catch (err) {}
                return (
                    '<button type="button" data-name="' + name +
                    '" class="btn btn-link add_new_customer"><i class="fa fa-plus-circle fa-lg" aria-hidden="true"></i>&nbsp; ' +
                    __translate('add_name_as_new_customer', { name: name }) + '</button>'
                );
            },
        },
        escapeMarkup: function(markup) { return markup; },
        width: '100%',
    };

    // POS: select2 holds value only — search UI is in-cart panel (no floating popup)
    if (isPosSaleTheme) {
        delete posCustomerSelect2Opts.ajax;
        posCustomerSelect2Opts.minimumResultsForSearch = Infinity;
        posCustomerSelect2Opts.minimumInputLength = 0;
        posCustomerSelect2Opts.placeholder = 'Search customers';
        posCustomerSelect2Opts.templateSelection = function (data) {
            if (!data.id) {
                return $('#default_customer_name').val() || 'Walk-In Customer';
            }
            var label = (data.text || data.name || '').toString().trim();
            // If select2 only has the id as text, fall back to selected option text / default
            if (!label || label === String(data.id)) {
                label = $('#customer_id option:selected').text()
                    || $('#default_customer_name').val()
                    || ('Customer #' + data.id);
            }
            // Prefer name without trailing (CO000x) for bar display
            label = label.replace(/\s*\((?:CO|C)?\d+\)\s*$/i, '').trim() || label;
            return label;
        };
    }

    $('select#customer_id').select2(posCustomerSelect2Opts);

    $('select#customer_id').on('change', function () {
        if (typeof pos_schedule_cart_price_refresh === 'function') {
            pos_schedule_cart_price_refresh();
        }
    });

    // Keep walk-in / selected customer name visible
    if (isPosSaleTheme) {
        $('select#customer_id').on('change select2:select', function () {
            var $opt = $(this).find('option:selected');
            var text = ($opt.text() || '').trim();
            var val = String($(this).val() || '');
            // Only fall back to Walk-In when nothing is selected
            if (!val) {
                text = $('#default_customer_name').val() || 'Walk-In Customer';
            } else if (!text || text === val) {
                // Prefer option text; if missing keep current rendered label
                var cur = $(this)
                    .next('.select2-container')
                    .find('.select2-selection__rendered')
                    .text();
                text = (cur || text || ('Customer #' + val)).toString().trim();
            }
            text = text.replace(/\s*\((?:CO|C)?\d+\)\s*$/i, '').trim() || text;
            var $rendered = $(this).next('.select2-container').find('.select2-selection__rendered');
            $rendered
                .text(text)
                .attr('title', text)
                .removeClass('pos-cust-placeholder-text')
                .css('color', '#f2f2f2');
            if (typeof posCustSyncResetBtn === 'function') {
                posCustSyncResetBtn();
            }
        });
    }

    function posCustomerInitials(name) {
        name = (name || '').trim();
        if (!name) return '?';
        var parts = name.replace(/[^a-zA-Z0-9\s]/g, ' ').trim().split(/\s+/).filter(Boolean);
        if (parts.length >= 2) return (parts[0].charAt(0) + parts[1].charAt(0)).toUpperCase();
        return name.substring(0, 2).toUpperCase();
    }

    function posCustomerAvatarColor(name) {
        var colors = ['#c9a227', '#e67e22', '#8e44ad', '#2980b9', '#16a085', '#c0392b', '#7f8c8d', '#2c3e50'];
        var n = 0;
        name = name || '';
        for (var i = 0; i < name.length; i++) n += name.charCodeAt(i);
        return colors[n % colors.length];
    }

    var posCustPickerTimer = null;

    function posCustPickerOpen() {
        if (!isPosSaleTheme) return;
        var $picker = $('#pos_customer_picker');
        if (!$picker.length) return;
        $picker.addClass('is-open').attr('aria-hidden', 'false');
        $('.pos-customer-bar').addClass('is-picking');
        $('#pos_customer_picker_input').val('').focus();
        $('#pos_customer_picker_add').attr('data-name', '');
        $('#pos_customer_picker_add .pos-cust-add-label').text('Add as a new customer');
        posCustPickerRenderPinned();
    }

    function posCustPickerClose() {
        $('#pos_customer_picker').removeClass('is-open').attr('aria-hidden', 'true');
        $('.pos-customer-bar').removeClass('is-picking');
    }

    /** Walk-In always available so user can switch away from / back to default. */
    function posCustPickerRenderPinned() {
        var $list = $('#pos_customer_picker_list');
        if (!$list.length) return;
        var defId = String($('#default_customer_id').val() || '').trim();
        var defName = ($('#default_customer_name').val() || 'Walk-In Customer').toString().trim();
        var curId = String($('#customer_id').val() || '').trim();
        var html = '';
        if (defId && /^\d+$/.test(defId)) {
            var isCurrent = curId === defId;
            html +=
                '<button type="button" class="pos-customer-picker-item pos-customer-picker-item--walkin' +
                (isCurrent ? ' is-current' : '') +
                '" data-id="' +
                defId +
                '" data-text="' +
                $('<div>').text(defName).html() +
                '" data-name="' +
                $('<div>').text(defName).html() +
                '" data-pay_term_number="" data-pay_term_type="" data-balance="' +
                ($('#default_customer_balance').val() || '') +
                '" data-price_calculation_type="" data-selling_price_group_id="">' +
                '<div class="pos-cust-avatar" style="background:#64748b">WI</div>' +
                '<div class="pos-cust-meta">' +
                '<div class="pos-cust-name">' +
                $('<div>').text(defName).html() +
                '</div>' +
                '<div class="pos-cust-sub">Default walk-in customer</div>' +
                '</div>' +
                (isCurrent ? '<span class="pos-cust-current">Current</span>' : '') +
                '</button>';
        }
        html += '<div class="pos-customer-picker-empty">Type a name or phone to find other customers</div>';
        $list.html(html);
    }

    function posCustPickerSearch(term) {
        var $list = $('#pos_customer_picker_list');
        if (!$list.length) return;
        term = $.trim(term || '');
        $('#pos_customer_picker_add').attr('data-name', term);
        var label = term ? ("Add '" + term + "' as a new customer") : 'Add as a new customer';
        $('#pos_customer_picker_add .pos-cust-add-label').text(label);

        if (term.length < 1) {
            posCustPickerRenderPinned();
            return;
        }

        $list.html('<div class="pos-customer-picker-loading">Searching…</div>');

        $.ajax({
            url: '/contacts/customers',
            dataType: 'json',
            data: { q: term },
            success: function (data) {
                var curId = String($('#customer_id').val() || '').trim();
                var defId = String($('#default_customer_id').val() || '').trim();
                var defName = ($('#default_customer_name').val() || 'Walk-In Customer').toString().trim();
                var html = '';

                // Always keep Walk-In at the top so you can switch away from / back to it
                if (defId && /^\d+$/.test(defId)) {
                    var walkCurrent = curId === defId;
                    html +=
                        '<button type="button" class="pos-customer-picker-item pos-customer-picker-item--walkin' +
                        (walkCurrent ? ' is-current' : '') +
                        '" data-id="' +
                        defId +
                        '" data-text="' +
                        $('<div>').text(defName).html() +
                        '" data-name="' +
                        $('<div>').text(defName).html() +
                        '" data-pay_term_number="" data-pay_term_type="" data-balance="' +
                        ($('#default_customer_balance').val() || '') +
                        '" data-price_calculation_type="" data-selling_price_group_id="">' +
                        '<div class="pos-cust-avatar" style="background:#64748b">WI</div>' +
                        '<div class="pos-cust-meta">' +
                        '<div class="pos-cust-name">' +
                        $('<div>').text(defName).html() +
                        '</div>' +
                        '<div class="pos-cust-sub">Default walk-in customer</div>' +
                        '</div>' +
                        (walkCurrent ? '<span class="pos-cust-current">Current</span>' : '') +
                        '</button>';
                }

                if (!data || !data.length) {
                    html += '<div class="pos-customer-picker-empty">No other customers found</div>';
                    $list.html(html);
                    return;
                }

                data.forEach(function (row) {
                    // Skip duplicate walk-in row from API
                    if (defId && String(row.id) === defId) {
                        return;
                    }
                    // Prefer company/business name; fall back to contact/person name
                    var businessName = (row.supplier_business_name || '').toString().trim();
                    var personName = (row.contact_person || row.first_name || '').toString().trim();
                    var displayName = businessName || personName || (row.name || '').toString().trim();
                    if (!displayName) {
                        displayName = (row.text || '').toString().replace(/\s*\([^)]*\)\s*$/, '').trim();
                    }
                    if (!displayName) {
                        displayName = (row.contact_id || ('Customer #' + row.id)).toString();
                    }
                    var selectionText = displayName;
                    var initials = posCustomerInitials(displayName);
                    var color = posCustomerAvatarColor(displayName);
                    var code = (row.contact_id || '').toString().trim();
                    var subParts = [];
                    // Secondary: show the other name + code when available
                    if (personName && businessName && personName !== businessName) {
                        var secondaryName = displayName === businessName ? personName : businessName;
                        subParts.push(code ? (secondaryName + ' - ' + code) : secondaryName);
                    } else if (code) {
                        subParts.push(code);
                    }
                    if (row.mobile) subParts.push(row.mobile);
                    var sub = subParts.join(' | ');
                    var isCurrent = String(row.id) === curId;
                    html +=
                        '<button type="button" class="pos-customer-picker-item' +
                        (isCurrent ? ' is-current' : '') +
                        '" data-id="' +
                        row.id +
                        '" ' +
                        'data-text="' +
                        $('<div>').text(selectionText).html() +
                        '" ' +
                        'data-name="' +
                        $('<div>').text(displayName).html() +
                        '" ' +
                        'data-pay_term_number="' +
                        (row.pay_term_number || '') +
                        '" ' +
                        'data-pay_term_type="' +
                        (row.pay_term_type || '') +
                        '" ' +
                        'data-balance="' +
                        (row.balance || '') +
                        '" ' +
                        'data-price_calculation_type="' +
                        (row.price_calculation_type || '') +
                        '" ' +
                        'data-selling_price_group_id="' +
                        (row.selling_price_group_id || '') +
                        '" ' +
                        'data-address="' +
                        $('<div>').text([row.shipping_address, row.address_line_1, row.address_line_2, row.city, row.state, row.zip_code, row.country].filter(Boolean).join(', ')).html() +
                        '">' +
                        '<div class="pos-cust-avatar" style="background:' +
                        color +
                        '">' +
                        initials +
                        '</div>' +
                        '<div class="pos-cust-meta">' +
                        '<div class="pos-cust-name">' +
                        $('<div>').text(displayName).html() +
                        '</div>' +
                        (sub ? '<div class="pos-cust-sub">' + $('<div>').text(sub).html() + '</div>' : '') +
                        '</div>' +
                        (isCurrent ? '<span class="pos-cust-current">Current</span>' : '') +
                        '</button>';
                });
                $list.html(html);
            },
            error: function () {
                $list.html('<div class="pos-customer-picker-empty">Could not load customers</div>');
            },
        });
    }

    function posCustSetDisplayName(text) {
        text = (text || '').toString().trim();
        text = text.replace(/\s*\((?:CO|C)?\d+\)\s*$/i, '').trim() || text;
        var $rendered = $('#customer_id').next('.select2-container').find('.select2-selection__rendered');
        $rendered
            .text(text)
            .attr('title', text)
            .removeClass('pos-cust-placeholder-text')
            .css('color', '#f2f2f2');
        posCustSyncResetBtn();
    }

    function posCustSyncResetBtn() {
        var cur = String($('#customer_id').val() || '').trim();
        var def = String($('#default_customer_id').val() || '').trim();
        var $btn = $('#pos_customer_reset_walkin');
        if (!$btn.length) return;
        if (cur && def && cur !== def) {
            $btn.removeClass('hide').attr('title', 'Reset to Walk-In Customer');
        } else {
            $btn.addClass('hide');
        }
    }

    // when a real customer is picked, show their name (walk-in keeps its name)
    function posCustPickerSelect($btn) {
        // Always use attribute — jQuery .data() can cache/coerce incorrectly
        var id = String($btn.attr('data-id') || '');
        var text = ($btn.attr('data-name') || $btn.attr('data-text') || '').trim();
        // Must be numeric contacts.id (not contact code like CO0001)
        if (!id || !/^\d+$/.test(id)) {
            if (typeof toastr !== 'undefined') {
                toastr.error('Invalid customer selected. Please try again.');
            }
            return;
        }
        if (!text || /^\(?[A-Z]*0*\d+\)?$/.test(text) || text === id) {
            var $nameEl = $btn.find('.pos-cust-name');
            if ($nameEl.length) {
                text = $.trim($nameEl.text()) || text;
            }
        }
        if (!text) {
            text = 'Customer #' + id;
        }

        // Replace options so Select2 cannot stick on Walk-In label
        var $sel = $('#customer_id');
        $sel.empty().append($('<option>', { value: id, text: text, selected: true }));
        $sel.val(id).trigger('change');
        posCustSetDisplayName(text);

        var payNum = $btn.attr('data-pay_term_number');
        var payType = $btn.attr('data-pay_term_type');
        if (payNum) {
            $('input#pay_term_number').val(payNum);
        } else {
            $('input#pay_term_number').val('');
        }
        if (payType) {
            $('#add_sell_form select[name="pay_term_type"]').val(payType);
            $('#edit_sell_form select[name="pay_term_type"]').val(payType);
        } else {
            $('#add_sell_form select[name="pay_term_type"]').val('');
            $('#edit_sell_form select[name="pay_term_type"]').val('');
        }
        var balance = $btn.attr('data-balance');
        window.__posCustAddress = $btn.attr('data-address') || '';
        if (typeof update_shipping_address === 'function') {
            update_shipping_address({ id: id, name: text, balance: balance });
        }
        if ($('#advance_balance_text').length) {
            $('#advance_balance_text').text(__currency_trans_from_en(balance), true);
            $('#advance_balance').val(balance);
        }
        if ($('.contact_due_text').length && typeof get_contact_due === 'function') {
            get_contact_due(id);
        }

        // Apply customer group / selling price group (same as Select2 select path)
        var priceType = $btn.attr('data-price_calculation_type') || '';
        var spg = $btn.attr('data-selling_price_group_id') || '';
        if (priceType === 'selling_price_group' && spg && $('#price_group').length) {
            $('#price_group').val(spg).trigger('change');
        } else if ($('#default_selling_price_group').length) {
            $('#price_group').val($('#default_selling_price_group').val() || 0).trigger('change');
        } else if ($('#price_group').length && String($('#default_customer_id').val()) === id) {
            $('#price_group').val(0).trigger('change');
        }
        if (typeof pos_schedule_cart_price_refresh === 'function') {
            pos_schedule_cart_price_refresh();
        }

        posCustPickerClose();
    }

    function posCustResetToWalkIn() {
        var defId = String($('#default_customer_id').val() || '').trim();
        var defName = ($('#default_customer_name').val() || 'Walk-In Customer').toString().trim();
        if (!defId) {
            return;
        }
        var $fake = $('<button>')
            .attr('data-id', defId)
            .attr('data-name', defName)
            .attr('data-text', defName)
            .attr('data-balance', $('#default_customer_balance').val() || '')
            .attr('data-pay_term_number', '')
            .attr('data-pay_term_type', '')
            .attr('data-price_calculation_type', '')
            .attr('data-selling_price_group_id', '');
        posCustPickerSelect($fake);
    }

    if (isPosSaleTheme) {
        function posCustPickerToggle() {
            if ($('#pos_customer_picker').hasClass('is-open')) {
                posCustPickerClose();
            } else {
                posCustPickerOpen();
            }
        }

        // Block Select2 popup; header click toggles the in-cart picker
        $('#customer_id').on('select2:opening', function (e) {
            e.preventDefault();
        });

        $(document).on('click', '.pos-customer-bar .select2-container, .pos-customer-bar .input-group-addon', function (e) {
            if ($(e.target).closest('#pos_customer_reset_walkin').length) {
                return;
            }
            e.preventDefault();
            e.stopPropagation();
            posCustPickerToggle();
        });

        $(document).on('click', '#pos_customer_reset_walkin', function (e) {
            e.preventDefault();
            e.stopPropagation();
            posCustResetToWalkIn();
        });

        $(document).on('input', '#pos_customer_picker_input', function () {
            var term = $(this).val();
            clearTimeout(posCustPickerTimer);
            posCustPickerTimer = setTimeout(function () {
                posCustPickerSearch(term);
            }, 200);
        });

        $(document).on('click', '.pos-customer-picker-item', function (e) {
            e.preventDefault();
            e.stopPropagation();
            posCustPickerSelect($(this));
        });

        $(document).on('click', '#pos_customer_picker_add.add_new_customer', function (e) {
            e.preventDefault();
            e.stopPropagation();
            posCustPickerClose();
        });

        $(document).on('click', '#pos_customer_picker', function (e) {
            e.stopPropagation();
        });

        // Outside click still closes (header toggle + outside click both work)
        $(document).on('click', function (e) {
            if ($(e.target).closest('.pos-customer-bar').length) return;
            posCustPickerClose();
        });

        $(document).on('keydown', function (e) {
            if (e.key === 'Escape') posCustPickerClose();
        });
    }

    $('#customer_id').on('select2:select', function(e) {
        var data = e.params.data;
        if (data.pay_term_number) {
            $('input#pay_term_number').val(data.pay_term_number);
        } else {
            $('input#pay_term_number').val('');
        }

        if (data.pay_term_type) {
            $('#add_sell_form select[name="pay_term_type"]').val(data.pay_term_type);
            $('#edit_sell_form select[name="pay_term_type"]').val(data.pay_term_type);
        } else {
            $('#add_sell_form select[name="pay_term_type"]').val('');
            $('#edit_sell_form select[name="pay_term_type"]').val('');
        }
        
        update_shipping_address(data);
        $('#advance_balance_text').text(__currency_trans_from_en(data.balance), true);
        $('#advance_balance').val(data.balance);

        if (data.price_calculation_type == 'selling_price_group') {
            $('#price_group').val(data.selling_price_group_id);
            $('#price_group').change();
        }

        // Re-apply group prices and line discounts for current cart
        if (typeof pos_schedule_cart_price_refresh === 'function') {
            pos_schedule_cart_price_refresh();
        }
        //  else {
        //     $('#price_group').val(0);
        //     $('#price_group').change();
        // }
        if ($('.contact_due_text').length) {
            get_contact_due(data.id);
            // store on customer change
            saveFormDataToLocalStorage();
        }

    });

    set_default_customer();
    setTimeout(function () {
        posCartPriceRefreshEnabled = true;
    }, 700);

    if ($('#search_product').length) {
        // ------------------------------------------------------------
        // Scan / Search mode + barcode scan optimization
        // Scan: exact SKU only → auto-enter (no SKU-2 sibling list)
        // Search: name / partial SKU autocomplete
        // Scanners type fast and usually end with Enter; clearing the field
        // prevents double scans concatenating codes.
        // ------------------------------------------------------------
        var posScan = {
            lastKeyAt: 0,
            rapidChars: 0,
            idleTimer: null,
            busy: false,
            queue: [],
            mode: 'scan',
        };

        function posGetSearchMode() {
            try {
                var m = localStorage.getItem('pos_product_search_mode');
                if (m === 'search' || m === 'scan') {
                    return m;
                }
            } catch (e) {}
            return 'scan';
        }

        function posIsScanMode() {
            return posGetSearchMode() === 'scan';
        }

        function posApplySearchMode(mode, focusInput) {
            mode = mode === 'search' ? 'search' : 'scan';
            posScan.mode = mode;
            try {
                localStorage.setItem('pos_product_search_mode', mode);
            } catch (e) {}

            $('.pos-search-mode-btn').removeClass('is-active');
            $('.pos-search-mode-btn[data-pos-search-mode="' + mode + '"]').addClass('is-active');
            $('.pos-product-search-wrap').attr('data-pos-mode', mode);
            $('body').toggleClass('pos-entry-mode-scan', mode === 'scan')
                .toggleClass('pos-entry-mode-search', mode === 'search');

            var $inp = $('#search_product');
            if ($inp.length) {
                var ph = mode === 'scan'
                    ? ($inp.data('placeholder-scan') || 'Enter SKU')
                    : ($inp.data('placeholder-search') || $inp.attr('placeholder'));
                $inp.attr('placeholder', ph);
                try {
                    // Keep minLength low so exact SKU still auto-adds via autocomplete
                    // (same path Search uses when a single match is found)
                    $inp.autocomplete('option', 'minLength', 2);
                    if (mode === 'scan') {
                        $inp.autocomplete('close');
                    }
                } catch (e2) {}
                if (focusInput !== false && !$('#__is_mobile').length) {
                    $inp.focus();
                }
            }
        }

        $(document).on('click', '.pos-search-mode-btn', function (e) {
            e.preventDefault();
            posApplySearchMode($(this).data('pos-search-mode') || 'scan', true);
            $('#search_product').val('');
        });

        /**
         * If the same code was scanned twice without a clear, fix:
         * "ECOM-004ECOM-004" → { code: "ECOM-004", times: 2 }
         */
        function posSanitizeBarcode(term) {
            term = (term || '').toString().replace(/[\x00-\x1F\x7F]/g, '').replace(/[\r\n\t]+/g, '').trim();
            if (/^\][A-Za-z0-9]{2}/.test(term)) {
                term = term.substring(3);
            }
            return term;
        }

        function posNormalizeScanTerm(term) {
            term = posSanitizeBarcode(term);
            if (!term) {
                return { code: '', times: 1 };
            }

            var best = { code: term, times: 1 };
            // Prefer longer unit (full SKU repeated) over short chunks
            for (var partLen = Math.floor(term.length / 2); partLen >= 3; partLen--) {
                if (term.length % partLen !== 0) {
                    continue;
                }
                var unit = term.substring(0, partLen);
                var reps = term.length / partLen;
                if (reps < 2) {
                    continue;
                }
                var ok = true;
                for (var r = 1; r < reps; r++) {
                    if (term.substring(r * partLen, (r + 1) * partLen) !== unit) {
                        ok = false;
                        break;
                    }
                }
                if (ok) {
                    best = { code: unit, times: reps };
                    break;
                }
            }
            return best;
        }

        function posGetSearchParams(term, forceMode) {
            var price_group = '';
            var search_fields = [];
            var mode = forceMode || posGetSearchMode();
            if (mode === 'scan') {
                search_fields = ['sku'];
            } else {
                $('.search_fields:checked').each(function (i) {
                    search_fields[i] = $(this).val();
                });
            }
            if ($('#price_group').length > 0) {
                price_group = $('#price_group').val();
            }
            if ($('#default_price_group').length > 0 && price_group === '') {
                price_group = $('#default_price_group').val();
            }
            if ($('#types_of_service_price_group').length > 0 && $('#types_of_service_price_group').val()) {
                price_group = $('#types_of_service_price_group').val();
            }
            var customer_id = $('select#customer_id').val();
            var is_direct_sell = false;
            if ($('input[name="is_direct_sale"]').length > 0 && $('input[name="is_direct_sale"]').val() == 1) {
                is_direct_sell = true;
            }
            var disable_qty_alert = $('#disable_qty_alert').length ? true : false;
            var is_sales_order = $('#sale_type').length && $('#sale_type').val() == 'sales_order' ? true : false;
            var is_draft = false;
            if ($('#status').length && ($('#status').val() == 'quotation' || $('#status').val() == 'draft')) {
                is_draft = true;
            }
            var is_serial_no = false;
            if ($('input[name="is_serial_no"]').length > 0 && $('input[name="is_serial_no"]').val() == 1) {
                is_serial_no = true;
            }
            return {
                price_group: price_group,
                location_id: $('input#location_id').val(),
                term: term,
                not_for_selling: 0,
                search_fields: search_fields,
                // Scan: auto-add exact match. Search: suggestions only — cashier clicks to add.
                auto_add_single: mode === 'scan',
                search_mode: mode,
                product_row: $('input#product_row_count').val(),
                customer_id: customer_id,
                is_direct_sell: is_direct_sell,
                is_serial_no: is_serial_no,
                is_sales_order: is_sales_order,
                disable_qty_alert: disable_qty_alert,
                is_draft: is_draft,
            };
        }

        function posScanFinish() {
            posScan.busy = false;
            if (posScan.queue.length) {
                var next = posScan.queue.shift();
                posProcessBarcodeTerm(next.code, next.times);
            } else if (!$('#__is_mobile').length) {
                $('#search_product').focus();
            }
        }

        function posAddVariationQty(variation_id, qty, sub_unit_id) {
            qty = parseInt(qty, 10) || 1;
            if (qty < 1) {
                qty = 1;
            }
            // Prefer qty-aware row addition
            if (typeof pos_product_row === 'function') {
                pos_product_row(variation_id, null, null, qty, sub_unit_id || null);
            }
        }

        /**
         * Process a barcode/SKU immediately (clear field first so next scan cannot append).
         */
        function posProcessBarcodeTerm(rawTerm, forcedTimes, opts) {
            opts = opts || {};
            var silentIfMiss = opts.silentIfMiss === true;
            var parsed = posNormalizeScanTerm(rawTerm);
            if (!parsed.code) {
                return;
            }
            var times = forcedTimes || parsed.times || 1;

            if (posScan.busy) {
                posScan.queue.push({ code: parsed.code, times: times });
                return;
            }
            posScan.busy = true;

            var $inp = $('#search_product');
            // Clear immediately so the next scan starts clean
            $inp.val('');
            try {
                var ac = $inp.data('ui-autocomplete');
                if (ac) {
                    if (ac.xhr && typeof ac.xhr.abort === 'function') {
                        ac.xhr.abort();
                    }
                    ac.term = null;
                    ac.selectedItem = null;
                    if (typeof ac.close === 'function') {
                        ac.close();
                    }
                    ac.cache = {};
                }
            } catch (e) {}

            if ($('input#location_id').val() == '') {
                toastr.warning(LANG.select_location);
                posScanFinish();
                return;
            }

            // Scanner path always uses scan mode so leading-zero SKU variants match
            $.getJSON('/products/list', posGetSearchParams(parsed.code, 'scan'))
                .done(function (data) {
                    try {
                        if (data && data.auto_add && data.row_data) {
                            if (data.row_data.success) {
                                // row may already be fully built — insert then bump qty if multi-scan
                                if (typeof pos_add_product_row_from_data === 'function') {
                                    pos_add_product_row_from_data(data.row_data);
                                }
                                // Multi-scan of same code: bump remaining qty
                                if (times > 1 && data.row_data.variation_id) {
                                    var extra = times - 1;
                                    var $row = $('#pos_table tbody tr.product_row').filter(function () {
                                        return String($(this).find('.row_variation_id').val()) === String(data.row_data.variation_id);
                                    }).first();
                                    if ($row.length) {
                                        var $qty = $row.find('.pos_quantity');
                                        var q = __read_number($qty);
                                        __write_number($qty, q + extra);
                                        $qty.change();
                                        pos_bring_product_row_to_top($row);
                                    } else {
                                        posAddVariationQty(data.row_data.variation_id, extra, data.row_data.matched_sub_unit_id || null);
                                    }
                                }
                            } else {
                                toastr.error(data.row_data.msg || LANG.no_products_found);
                            }
                        } else {
                            // No auto_add envelope — handle product list
                            var products = (data && data.products) ? data.products : ($.isArray(data) ? data : []);
                            if (products.length === 1 && products[0].variation_id) {
                                posAddVariationQty(products[0].variation_id, times, products[0].matched_sub_unit_id || null);
                            } else if (products.length === 0) {
                                if (silentIfMiss) {
                                    $inp.val(parsed.code);
                                } else {
                                    toastr.error(LANG.no_products_found);
                                }
                            } else {
                                var scanned = String(parsed.code || '').trim().toLowerCase();
                                var exact = null;
                                for (var i = 0; i < products.length; i++) {
                                    var p = products[i];
                                    var sub = String(p.sub_sku || '').trim().toLowerCase();
                                    // HTML entities may come from server escaping
                                    try {
                                        sub = $('<textarea/>').html(sub).text();
                                        if (p.sku) {
                                            p.sku = $('<textarea/>').html(String(p.sku)).text();
                                        }
                                    } catch (eDec) {}
                                    var sku = String(p.sku || '').trim().toLowerCase();
                                    if (sub === scanned || (sku && sku === scanned)) {
                                        exact = p;
                                        break;
                                    }
                                }
                                if (exact && exact.variation_id) {
                                    posAddVariationQty(exact.variation_id, times, exact.matched_sub_unit_id || null);
                                } else if (posIsScanMode()) {
                                    if (silentIfMiss) {
                                        $inp.val(parsed.code);
                                    } else {
                                        toastr.error(LANG.no_products_found);
                                    }
                                } else {
                                    $inp.val(parsed.code);
                                    try {
                                        $inp.autocomplete('search', parsed.code);
                                    } catch (e3) {}
                                }
                            }
                        }
                    } finally {
                        posScanFinish();
                    }
                })
                .fail(function () {
                    toastr.error(LANG.something_went_wrong || 'Search failed');
                    posScanFinish();
                });
        }

        // Enter from scanner: process now (do not wait for autocomplete delay)
        $('#search_product').on('keydown.pos_scan', function (e) {
            var now = Date.now();
            var gap = posScan.lastKeyAt ? (now - posScan.lastKeyAt) : 999;
            posScan.lastKeyAt = now;

            // Tab → qty still handled elsewhere; skip
            if (e.which === 9) {
                return;
            }

            if (e.which === 13) {
                e.preventDefault();
                e.stopImmediatePropagation();
                clearTimeout(posScan.idleTimer);
                posScan.rapidChars = 0;
                var val = $(this).val();
                // Search tab: never auto-add on Enter — show/refresh suggestions only
                if (!posIsScanMode()) {
                    if (val && String(val).trim().length >= 2) {
                        try {
                            $(this).autocomplete('search', String(val).trim());
                        } catch (err) {}
                    }
                    return false;
                }
                if (val && String(val).trim().length > 0) {
                    posProcessBarcodeTerm(val);
                }
                return false;
            }

            // Track rapid keystroke bursts (scanner); human typing is slower
            if (gap > 0 && gap < 45) {
                posScan.rapidChars += 1;
            } else if (e.which !== 16 && e.which !== 17 && e.which !== 18) {
                posScan.rapidChars = 1;
            }

            clearTimeout(posScan.idleTimer);
            // Scanners that do not send Enter: flush after brief silence during a rapid burst
            posScan.idleTimer = setTimeout(function () {
                if (!posIsScanMode()) {
                    posScan.rapidChars = 0;
                    return;
                }
                var $s = $('#search_product');
                var val = $s.val();
                if (!val || String(val).trim().length < 8) {
                    posScan.rapidChars = 0;
                    return;
                }
                if (posScan.rapidChars >= 6) {
                    posScan.rapidChars = 0;
                    // Incomplete bluetooth/PWA scans: try quietly; Enter still shows "not found"
                    posProcessBarcodeTerm(val, null, { silentIfMiss: true });
                }
            }, 350);
        });

        //Add Product
        $('#search_product')
            .autocomplete({
                // Short delay: scanners finish in <50ms; long delays invite double-scan concat
                delay: 180,
                source: function(request, response) {
                    if (posScan.busy) {
                        response([]);
                        return;
                    }

                    // Fix accidental double barcode before searching autocomplete path
                    var term = request.term;
                    var norm = posNormalizeScanTerm(term);
                    if (norm.times > 1) {
                        term = norm.code;
                        request.term = term;
                        $('#search_product').val(term);
                    }
                    var params = posGetSearchParams(term);
                    
                    $.getJSON(
                        '/products/list',
                        params,
                        function(data) {
                            // Check if auto-add is enabled (single product found) — Scan mode only
                            if (data && data.auto_add && data.row_data && posIsScanMode()) {
                                // Automatically add the product row
                                if (data.row_data.success) {
                                    $('#search_product').val('');
                                    // Clear autocomplete cache to prevent stale data issues to avoid same product getting added again and again if search term is same as previous one 
                                    var ac = $('#search_product').data('ui-autocomplete');
                                    if (ac) {
                                        ac.cache = {};
                                        ac.term = '';
                                    }
                                    pos_add_product_row_from_data(data.row_data);
                                    if (norm.times > 1 && data.row_data.variation_id) {
                                        var $row = $('#pos_table tbody tr.product_row').filter(function () {
                                            return String($(this).find('.row_variation_id').val()) === String(data.row_data.variation_id);
                                        }).first();
                                        if ($row.length) {
                                            var $qty = $row.find('.pos_quantity');
                                            var q = __read_number($qty);
                                            __write_number($qty, q + (norm.times - 1));
                                            $qty.change();
                                            pos_bring_product_row_to_top($row);
                                        }
                                    }
                                } else {
                                    toastr.error(data.row_data.msg);
                                }
                                // Return a special marker to indicate auto-add was handled
                                response([{auto_added: true}]);
                            } else {
                                // Scan mode: never show multi-SKU sibling lists
                                if (posIsScanMode()) {
                                    response([]);
                                    return;
                                }
                                // Search mode: always show suggestion list (never auto-add)
                                var list = (data && data.products) ? data.products : ($.isArray(data) ? data : []);
                                response(list);
                            }
                        }
                    );
                },
                minLength: 2,
                response: function(event, ui) {
                    // Skip if auto-add already handled the product
                    if (ui.content.length == 1 && ui.content[0].auto_added) {
                        return;
                    }

                    // Search mode: show suggestion list only — cashier clicks to add
                    if (!posIsScanMode()) {
                        if (ui.content.length == 0) {
                            toastr.error(LANG.no_products_found);
                            if (!$('#__is_mobile').length) {
                                $('input#search_product').select();
                            }
                        }
                        return;
                    }
                    
                    if (ui.content.length == 1) {
                        ui.item = ui.content[0];

                        var is_overselling_allowed = false;
                        if($('input#is_overselling_allowed').length) {
                            is_overselling_allowed = true;
                        }
                        var for_so = false;
                        if ($('#sale_type').length && $('#sale_type').val() == 'sales_order') {
                            for_so = true;
                        }

                        if ((ui.item.enable_stock == 1 && ui.item.qty_available > 0) || 
                                (ui.item.enable_stock == 0) || is_overselling_allowed || for_so) {
                            $(this)
                                .data('ui-autocomplete')
                                ._trigger('select', 'autocompleteselect', ui);
                            $(this).autocomplete('close');
                        }
                    } else if (ui.content.length == 0) {
                        // Scan: stay quiet until Enter / scanner flushes the code
                        // (partial SKU while typing would otherwise spam "not found")
                        if (posIsScanMode()) {
                            return;
                        }
                        toastr.error(LANG.no_products_found);
                        if (!$('#__is_mobile').length) {
                            $('input#search_product').select();
                        }
                    }
                },
                focus: function(event, ui) {
                    if (ui.item.qty_available <= 0) {
                        return false;
                    }
                },
                select: function(event, ui) {
                    var searched_term = $(this).val();
                    var is_overselling_allowed = false;
                    if($('input#is_overselling_allowed').length) {
                        is_overselling_allowed = true;
                    }
                    var for_so = false;
                    if ($('#sale_type').length && $('#sale_type').val() == 'sales_order') {
                        for_so = true;
                    }

                    var is_draft=false;
                    if($('#status') && ($('#status').val()=='quotation' || 
                    $('#status').val()=='draft')) {
                        var is_draft=true;
                    }

                    if (ui.item.enable_stock != 1 || ui.item.qty_available > 0 || is_overselling_allowed || for_so || is_draft) {
                        $(this).val(null);

                        //Pre select lot number only if the searched term is same as the lot number
                        var purchase_line_id = ui.item.purchase_line_id && searched_term == ui.item.lot_number ? ui.item.purchase_line_id : null;
                        pos_product_row(ui.item.variation_id, purchase_line_id);
                    } else {
                        alert(LANG.out_of_stock);
                    }
                },
            })
            .autocomplete('instance')._renderItem = function(ul, item) {
                // Skip rendering if this is the auto_added marker
                if (item.auto_added) {
                    return $('<li style="display:none;">').appendTo(ul);
                }
                
                var is_overselling_allowed = false;
                if($('input#is_overselling_allowed').length) {
                    is_overselling_allowed = true;
                }

                var for_so = false;
                if ($('#sale_type').length && $('#sale_type').val() == 'sales_order') {
                    for_so = true;
                }
                var is_draft=false;
                
                if($('#status') && ($('#status').val()=='quotation' || 
                $('#status').val()=='draft')) {
                    var is_draft=true;
                }

            if (item.enable_stock == 1 && item.qty_available <= 0 && !is_overselling_allowed && !for_so && !is_draft) {
                var string = '<li class="ui-state-disabled">' + item.name;
                if (item.type == 'variable') {
                    string += '-' + item.variation;
                }
                var selling_price = item.selling_price;
                if (item.variation_group_price) {
                    selling_price = item.variation_group_price;
                }
                string +=
                    ' (' +
                    item.sub_sku +
                    ')' +
                    '<br> Price: ' +
                    __currency_trans_from_en(selling_price, false, false, __currency_precision, true) +
                    ' (Out of stock) </li>';
                return $(string).appendTo(ul);
            } else {
                var string = '<div>' + item.name;
                if (item.type == 'variable') {
                    string += '-' + item.variation;
                }

                var selling_price = item.selling_price;
                if (item.variation_group_price) {
                    selling_price = item.variation_group_price;
                }

                string += ' (' + item.sub_sku + ')' + '<br> Price: ' + __currency_trans_from_en(selling_price, false, false, __currency_precision, true);
                if (item.enable_stock == 1) {
                    var qty_available = __currency_trans_from_en(item.qty_available, false, false, __currency_precision, true);
                    string += ' - ' + qty_available + item.unit;
                }
                string += '</div>';

                return $('<li>')
                    .append(string)
                    .appendTo(ul);
            }
        };

        // Restore last Scan/Search mode (default Scan)
        posApplySearchMode(posGetSearchMode(), false);
    }

    //Update line total and check for quantity not greater than max quantity
    $('table#pos_table').on('change', 'input.pos_quantity', function() {
        pos_play_success_sound();
        // comment line becouse it validate form at increment and decrement item
        // if (sell_form_validator) {
        //     sell_form.valid();
        // }
        if (pos_form_validator) {
            pos_form_validator.element($(this));
        }
        // var max_qty = parseFloat($(this).data('rule-max'));
        var entered_qty = __read_number($(this));
        

        var tr = $(this).parents('tr');

        var unit_price_inc_tax = __read_number(tr.find('input.pos_unit_price_inc_tax'));
        var line_total = entered_qty * unit_price_inc_tax;

        __write_number(tr.find('input.pos_line_total'), line_total, false);
        tr.find('span.pos_line_total_text').text(__currency_trans_from_en(line_total, true));
        tr.find('span.pos_line_unit_price_text').text(__currency_trans_from_en(unit_price_inc_tax, true));
        if (typeof pos_update_line_price_compare_display === 'function') {
            pos_update_line_price_compare_display(tr);
        }

        //Change modifier quantity
        tr.find('.modifier_qty_text').each( function(){
            $(this).text(__currency_trans_from_en(entered_qty, false));
        });
        tr.find('.modifiers_quantity').each( function(){
            $(this).val(entered_qty);
        });

        pos_total_row();

        adjustComboQty(tr);
    });

    //If change in unit price update price including tax and line total
    // Use document so fields still work when line-edit modal is moved to body
    $(document).on('input change', 'input.pos_unit_price', function(e) {
        if (window.__posPriceLogicLock) {
            return;
        }
        var tr = pos_product_row_from_el($(this));
        if (!tr.length) {
            return;
        }
        if (e.type === 'input') {
            return;
        }
        var typed = __read_number($(this));
        // Lower than list → auto line discount; higher → keep as unit price raise
        pos_apply_typed_sell_price(tr, typed);
        pos_recalc_row_after_price_edit(tr);
    });

    //If change in tax rate then update unit price according to it.
    $(document).on('change', 'select.tax_id', function() {
        if (window.__posPriceLogicLock) {
            return;
        }
        var tr = pos_product_row_from_el($(this));
        if (!tr.length) {
            return;
        }

        var tax_rate = pos_row_field(tr, 'select.tax_id')
            .find(':selected')
            .data('rate');
        var unit_price_inc_tax = __read_number(tr.find('input.pos_unit_price_inc_tax'));

        var discounted_unit_price = __get_principle(unit_price_inc_tax, tax_rate);
        var unit_price = get_unit_price_from_discounted_unit_price(tr, discounted_unit_price);
        window.__posPriceLogicLock = true;
        try {
            __write_number(pos_row_field(tr, 'input.pos_unit_price'), unit_price);
        } finally {
            window.__posPriceLogicLock = false;
        }
        pos_each_row(tr);
    });

    //If change in unit price including tax, update unit price
    $('table#pos_table').on('change', 'input.pos_unit_price_inc_tax', function() {
        if (window.__posPriceLogicLock) {
            return;
        }
        var unit_price_inc_tax = __read_number($(this));

        if (iraqi_selling_price_adjustment) {
            unit_price_inc_tax = round_to_iraqi_dinnar(unit_price_inc_tax);
            __write_number($(this), unit_price_inc_tax);
        }

        var tr = $(this).parents('tr');
        var tax_rate = pos_row_field(tr, 'select.tax_id')
            .find(':selected')
            .data('rate') || 0;
        // Convert typed sell price (inc tax) → ex-tax target, then apply list/discount logic
        var typedExTax = tax_rate ? __get_principle(unit_price_inc_tax, tax_rate) : unit_price_inc_tax;
        pos_apply_typed_sell_price(tr, typedExTax);
        pos_recalc_row_after_price_edit(tr);
    });

    //Change max quantity rule if lot number changes
    $('table#pos_table').on('change', 'select.lot_number', function() {
        var qty_element = $(this)
            .closest('tr')
            .find('input.pos_quantity');

        var tr = $(this).closest('tr');
        var multiplier = 1;
        var unit_name = '';
        var sub_unit_length = tr.find('select.sub_unit').length;
        if (sub_unit_length > 0) {
            var select = tr.find('select.sub_unit');
            multiplier = parseFloat(select.find(':selected').data('multiplier'));
            unit_name = select.find(':selected').data('unit_name');
        }
        var allow_overselling = qty_element.data('allow-overselling');
        if ($(this).val() && !allow_overselling) {
            var lot_qty = $('option:selected', $(this)).data('qty_available');
            var max_err_msg = $('option:selected', $(this)).data('msg-max');

            if (sub_unit_length > 0) {
                lot_qty = lot_qty / multiplier;
                var lot_qty_formated = __number_f(lot_qty, false);
                max_err_msg = __translate('lot_max_qty_error', {
                    max_val: lot_qty_formated,
                    unit_name: unit_name,
                });
            }

            qty_element.attr('data-rule-max-value', lot_qty);
            qty_element.attr('data-msg-max-value', max_err_msg);

            qty_element.rules('add', {
                'max-value': lot_qty,
                messages: {
                    'max-value': max_err_msg,
                },
            });
        } else {
            var default_qty = qty_element.data('qty_available');
            var default_err_msg = qty_element.data('msg_max_default');
            if (sub_unit_length > 0) {
                default_qty = default_qty / multiplier;
                var lot_qty_formated = __number_f(default_qty, false);
                default_err_msg = __translate('pos_max_qty_error', {
                    max_val: lot_qty_formated,
                    unit_name: unit_name,
                });
            }

            qty_element.attr('data-rule-max-value', default_qty);
            qty_element.attr('data-msg-max-value', default_err_msg);

            qty_element.rules('add', {
                'max-value': default_qty,
                messages: {
                    'max-value': default_err_msg,
                },
            });
        }
        qty_element.trigger('change');
    });

    // Remember previous discount type so % ↔ fixed amount can convert correctly
    $(document).on('focus', 'select.row_discount_type', function () {
        $(this).data('prev-discount-type', $(this).val());
    });

    //Change in row discount type or discount amount
    $(document).on(
        'input change',
        'select.row_discount_type, input.row_discount_amount',
        function(e) {
            if (window.__posPriceLogicLock) {
                return;
            }
            var tr = pos_product_row_from_el($(this));
            if (!tr.length) {
                return;
            }
            // Keep cashier-typed line discount when customer changes
            // (auto discounts still override when a rule exists)
            pos_mark_row_manual_discount(tr);
            if (e.type === 'input') {
                return;
            }

            // When switching % ↔ fixed, convert the amount so sell price stays the same
            if ($(this).is('select.row_discount_type')) {
                var prevType = $(this).data('prev-discount-type');
                var nextType = $(this).val();
                var $amount = pos_row_field(tr, 'input.row_discount_amount');
                var unitPrice = __read_number(pos_row_field(tr, 'input.pos_unit_price'));
                var amount = __read_number($amount);
                if (
                    prevType &&
                    nextType &&
                    prevType !== nextType &&
                    $amount.length &&
                    unitPrice > 0 &&
                    amount &&
                    !isNaN(amount)
                ) {
                    window.__posPriceLogicLock = true;
                    try {
                        if (prevType === 'percentage' && nextType === 'fixed') {
                            // 59.84% of 4.98 → $2.98
                            __write_number($amount, Math.round(unitPrice * (amount / 100) * 100) / 100);
                        } else if (prevType === 'fixed' && nextType === 'percentage') {
                            // $2.98 of 4.98 → 59.84%
                            __write_number($amount, Math.round((amount / unitPrice) * 10000) / 100);
                        }
                    } finally {
                        window.__posPriceLogicLock = false;
                    }
                }
                $(this).data('prev-discount-type', nextType);
            }

            //calculate discounted unit price
            var discounted_unit_price = calculate_discounted_unit_price(tr);

            var tax_rate = pos_row_field(tr, 'select.tax_id')
                .find(':selected')
                .data('rate');
            var quantity = __read_number(tr.find('input.pos_quantity'));

            var unit_price_inc_tax = __add_percent(discounted_unit_price, tax_rate);
            var line_total = quantity * unit_price_inc_tax;

            __write_number(tr.find('input.pos_unit_price_inc_tax'), unit_price_inc_tax);
            __write_number(tr.find('input.pos_line_total'), line_total, false);
            tr.find('span.pos_line_total_text').text(__currency_trans_from_en(line_total, true));
            tr.find('span.pos_line_unit_price_text').text(__currency_trans_from_en(unit_price_inc_tax, true));
            pos_each_row(tr);
            pos_total_row();
            round_row_to_iraqi_dinnar(tr);
        }
    );

    //Remove row on click on remove row
    $('table#pos_table').on('click', '.pos_remove_row', function() {
        var $tr = $(this).closest('tr.product_row');
        var $line = $tr.closest('tbody.pos-cart-line');
        var idx = $tr.attr('data-row_index');
        pos_line_edit_row($tr).remove();
        if (typeof idx !== 'undefined') {
            $('#row_edit_product_price_modal_' + idx).closest('tr.pos-line-edit-row').remove();
            $('#row_edit_product_price_modal_' + idx).remove();
        }
        if ($line.length) {
            $line.remove();
        } else {
            $tr.remove();
        }
        pos_total_row();
        pos_sync_empty_state();
    });

    //Cancel the invoice (delegated so both mobile + desktop Cancel buttons fire the same handler)
    $(document).on('click', 'button.js-pos-cancel', function() {
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(confirm => {
            if (confirm) {
                reset_pos_form();
            }
        });
    });

    // Always create draft/quote on the create endpoint (not update of a retrieved parked sale)
    function pos_create_url_for_draft_or_quote() {
        var $addForm = $('#add_pos_sell_form');
        if ($addForm.length && $addForm.data('original-action')) {
            return $addForm.data('original-action');
        }
        if ($addForm.length) {
            return $addForm.attr('action');
        }
        return (window.base_path || '') + '/pos';
    }

    function pos_resolve_contact_pk() {
        var raw = String($('#customer_id').val() || '').trim();
        if (/^\d+$/.test(raw)) {
            return raw;
        }
        var def = String($('#default_customer_id').val() || '').trim();
        if (/^\d+$/.test(def)) {
            return def;
        }
        return '';
    }

    function pos_append_contact_id(data) {
        var contactId = pos_resolve_contact_pk();
        if (!contactId) {
            return data;
        }
        data = data.replace(/(^|&)contact_id=[^&]*/g, '');
        return data + (data ? '&' : '') + 'contact_id=' + encodeURIComponent(contactId);
    }

    function pos_go_to_create_with_drafts() {
        window.location = (window.base_path || '') + '/pos/create?open_drafts=1';
    }

    function pos_serialize_as_create(status) {
        var data = pos_serialize_sell_form(pos_form_obj);
        // Drop method override so we POST create, not PUT update
        data = data.replace(/(^|&)_method=[^&]*/g, '').replace(/^&/, '');
        data = data.replace(/(^|&)is_suspend=[^&]*/g, '');
        data = data + '&status=' + status + '&is_suspend=0';
        return pos_append_contact_id(data);
    }

    //Save invoice as draft (previous system behavior)
    $(document).off('click.posDraftSave', '#pos-draft').on('click.posDraftSave', '#pos-draft', function(e) {
        e.preventDefault();
        e.stopPropagation();

        //Check if product is present or not.
        if ($('table#pos_table tbody').find('.product_row').length <= 0) {
            toastr.warning(LANG.no_products_added);
            return false;
        }

        var is_valid = isValidPosForm();
        if (is_valid != true) {
            return;
        }

        var $editForm = $('form#edit_pos_sell_form');
        // Editing a parked/suspended sale: convert THIS sale to draft, then show drafts list
        if ($editForm.length) {
            disable_pos_form_actions();
            var editData = pos_serialize_sell_form($editForm);
            editData = editData.replace(/(^|&)is_suspend=[^&]*/g, '');
            editData = editData + '&status=draft&is_suspend=0';
            editData = pos_append_contact_id(editData);

            $.ajax({
                method: 'POST',
                url: $editForm.attr('action'),
                data: editData,
                dataType: 'json',
                success: function(result) {
                    enable_pos_form_actions();
                    if (result.success == 1) {
                        toastr.success(result.msg || 'Saved as draft');
                        pos_go_to_create_with_drafts();
                    } else {
                        toastr.error(result.msg || 'Unable to save draft');
                    }
                },
                error: function(xhr) {
                    enable_pos_form_actions();
                    var msg = (xhr.responseJSON && xhr.responseJSON.msg) ? xhr.responseJSON.msg : (LANG.something_went_wrong || 'Something went wrong');
                    toastr.error(msg);
                }
            });
            return false;
        }

        // Create POS: POST a new draft
        var data = pos_serialize_as_create('draft');
        var url = pos_create_url_for_draft_or_quote();

        disable_pos_form_actions();
        $.ajax({
            method: 'POST',
            url: url,
            data: data,
            dataType: 'json',
            success: function(result) {
                enable_pos_form_actions();
                if (result.success == 1) {
                    reset_pos_form();
                    toastr.success(result.msg);
                    if (typeof openPosDraftsDropdown === 'function') {
                        openPosDraftsDropdown();
                    } else if (typeof loadPosDraftsPanel === 'function') {
                        loadPosDraftsPanel('draft');
                    }
                } else {
                    toastr.error(result.msg);
                }
            },
            error: function(xhr) {
                enable_pos_form_actions();
                var msg = (xhr.responseJSON && xhr.responseJSON.msg) ? xhr.responseJSON.msg : (LANG.something_went_wrong || 'Something went wrong');
                toastr.error(msg);
            }
        });
        return false;
    });

    //Save as Quotation only (no auto invoice print)
    $('button#pos-quotation').click(function() {
        //Check if product is present or not.
        if ($('table#pos_table tbody').find('.product_row').length <= 0) {
            toastr.warning(LANG.no_products_added);
            return false;
        }

        var is_valid = isValidPosForm();
        if (is_valid != true) {
            return;
        }

        var data = pos_serialize_as_create('quotation');
        var url = pos_create_url_for_draft_or_quote();

        disable_pos_form_actions();
        $.ajax({
            method: 'POST',
            url: url,
            data: data,
            dataType: 'json',
            success: function(result) {
                enable_pos_form_actions();
                if (result.success == 1) {
                    reset_pos_form();
                    toastr.success(result.msg);
                    // Stay on POS — do not open invoice/receipt print
                } else {
                    toastr.error(result.msg);
                }
            },
        });
    });

    //Finalize invoice, open payment modal (Multiple / Pay — not express Cash/Card/Credit)
    $(document).on('click', 'button.pos-finalize, button.pos-pay-cta', function(e) {
        // Pay Cash / Card / Credit / Suspend use pos-express-finalize — do not open Multiple popup
        if ($(this).hasClass('pos-express-finalize')) {
            return;
        }

        e.preventDefault();
        e.stopPropagation();

        //Check if product is present or not.
        if ($('table#pos_table tbody').find('.product_row').length <= 0) {
            toastr.warning(LANG.no_products_added);
            return false;
        }

        if ($('#reward_point_enabled').length) {
            var validate_rp = isValidatRewardPoint();
            if (!validate_rp['is_valid']) {
                toastr.error(validate_rp['msg']);
                return false;
            }
        }

        var $paymentModal = $('#modal_payment');
        // Keep modal on body so it is not clipped / blocked by cart panel stacking
        if ($paymentModal.length && $paymentModal.parent()[0] !== document.body) {
            $paymentModal.appendTo('body');
        }
        // Submit button is outside the form after move — bind it to the POS form
        var formId = pos_form_obj && pos_form_obj.attr('id');
        if (formId) {
            $paymentModal.find('#pos-save').attr('form', formId);
        }
        // Recalc full cart BEFORE show so 2nd open is not stuck on previous amount
        if (typeof pos_total_row === 'function') {
            pos_total_row();
        }
        if (typeof pos_sync_payment_modal_amount === 'function') {
            pos_sync_payment_modal_amount(false);
        }
        $paymentModal.modal('show');
        // Ensure footer actions never look/stay stuck disabled when modal opens
        $paymentModal.find('.modal-footer button, #pos-save, #modal-payment-credit-sale')
            .prop('disabled', false)
            .removeAttr('disabled')
            .removeClass('disabled is-disabled');
        if (typeof enable_pos_form_actions === 'function') {
            enable_pos_form_actions();
        }
        return false;
    });

    $(document).on('shown.bs.modal', '#modal_payment', function () {
        var $m = $(this);
        $m.find('.modal-footer button, #pos-save, #modal-payment-credit-sale')
            .prop('disabled', false)
            .removeAttr('disabled')
            .removeClass('disabled is-disabled');
        if (typeof enable_pos_form_actions === 'function') {
            enable_pos_form_actions();
        }
    });

    // Finalize Payment: always submit POS form (modal may be outside the form)
    $(document).off('click.posSave', '#pos-save').on('click.posSave', '#pos-save', function(e) {
        e.preventDefault();
        e.stopPropagation();
        if ($('table#pos_table tbody').find('.product_row').length <= 0) {
            toastr.warning(LANG.no_products_added);
            return false;
        }
        if (typeof pos_form_obj === 'undefined' || !pos_form_obj.length) {
            return false;
        }
        if (typeof pos_prepare_form_for_submit === 'function') {
            pos_prepare_form_for_submit();
        }
        pos_form_obj.submit();
        return false;
    });

    /**
 * Mark the correct payment method card from hidden select values.
 * Only real payment lines inside #payment_rows_div (not change-return).
 */
function pos_sync_pay_method_cards($scope) {
    var $root = $scope && $scope.length ? $scope : $('#modal_payment');
    var $rows = $root.find('#payment_rows_div .payment_row');
    if (!$rows.length) {
        $rows = $('#payment_rows_div').find('.payment_row');
    }
    $rows.each(function () {
        var $row = $(this);
        var $select = $row.find('select.payment_types_dropdown').first();
        if (!$select.length) {
            $select = $row.find('.payment_types_dropdown').filter('select, input').first();
        }
        var method = $select.length ? $select.val() : null;
        if (!method) {
            method =
                $row.find('.pos-pay-method-card.is-active').data('method') ||
                $row.find('.pos-pay-method-card').first().data('method');
        }
        if (!method) {
            return;
        }
        $row.find('.pos-pay-method-card').removeClass('is-active').attr('aria-pressed', 'false');
        $row.find('.pos-pay-method-card[data-method="' + method + '"]')
            .addClass('is-active')
            .attr('aria-pressed', 'true');
    });
}

/**
 * Write payment amount (always unlock non-advance so updates actually apply).
 * options.systemWrite = true → do not mark as user-locked (auto remainder/full fill)
 * options.clearLock = true → force unlock user lock (fresh full pay on single empty row)
 */
function pos_write_payment_amount($field, value, options) {
    options = options || {};
    if (!$field || !$field.length) {
        return;
    }
    var $row = $field.closest('.payment_row');
    var method =
        ($row.find('select.payment_types_dropdown').first().val() ||
            $row.find('.payment_types_dropdown').first().val() ||
            '') + '';
    if (method === 'advance') {
        return;
    }
    $field.prop('readonly', false).removeAttr('readonly').prop('disabled', false).removeAttr('disabled');
    $field.data('pos-writing', 1);
    __write_number($field, value);
    if (options.clearLock) {
        $field.removeAttr('data-pos-amt-locked');
    }
    // system writes are not "user locks" (except keepLock for re-write of same partial)
    if (options.systemWrite && !options.keepLock) {
        // leave lock as-is for earlier partial rows; for current field only clear if clearLock
    }
    try {
        $field.trigger('change');
    } catch (err) {}
    setTimeout(function () {
        $field.removeData('pos-writing');
    }, 0);
}

/**
 * Apply current cart payable to payment amount fields.
 * - Unlocked single row → full payable (default)
 * - User-locked amount (e.g. typed 10 of 20) → KEEP it so Add Row + balance due work
 * - 2+ rows → keep earlier; set LAST to remaining
 */
function pos_apply_payable_to_payment_rows(total_payable) {
    var precision = typeof __currency_precision !== 'undefined' ? __currency_precision : 2;
    var factor = Math.pow(10, precision);
    total_payable = Math.round((parseFloat(total_payable) || 0) * factor) / factor;
    if (isNaN(total_payable) || total_payable < 0) {
        total_payable = 0;
    }

    var $amounts = $('#payment_rows_div').find('input.payment-amount');
    var n = $amounts.length;
    if (!n) {
        return;
    }

    // ---- Single payment row ----
    if (n === 1) {
        var $first = $amounts.eq(0);
        var cur = __read_number($first);
        if (isNaN(cur)) {
            cur = 0;
        }
        cur = Math.round(cur * factor) / factor;
        var locked = $first.attr('data-pos-amt-locked') === '1';

        if (locked && cur > 0.009) {
            // User set a fixed amount (e.g. 10) — never overwrite to full bill
            if (cur > total_payable + 0.009) {
                // Cart total got smaller than locked amount — clamp down
                pos_write_payment_amount($first, total_payable, { systemWrite: true, keepLock: true });
                $first.attr('data-pos-amt-locked', '1');
            }
            // Keep partial; balance due + Add Payment Row use remaining
            return;
        }

        // Default / not locked: keep amount in sync with bill total
        pos_write_payment_amount($first, total_payable, { systemWrite: true, clearLock: true });
        return;
    }

    // ---- Multi rows: sum earlier (0..n-2); do not overwrite them ----
    var sum_prev = 0;
    for (var i = 0; i < n - 1; i++) {
        var a = __read_number($amounts.eq(i));
        if (!isNaN(a) && a > 0) {
            sum_prev += a;
        }
        // Treat earlier rows as locked once multi-pay exists
        $amounts.eq(i).attr('data-pos-amt-locked', '1');
    }
    sum_prev = Math.round(sum_prev * factor) / factor;

    if (sum_prev <= 0) {
        pos_write_payment_amount($amounts.eq(0), total_payable, { systemWrite: true, clearLock: true });
        for (var j = 1; j < n; j++) {
            pos_write_payment_amount($amounts.eq(j), 0, { systemWrite: true, clearLock: true });
        }
        return;
    }

    // Remainder on last row only (2nd when 2 rows) — always system-managed
    var rem = Math.round((total_payable - sum_prev) * factor) / factor;
    if (isNaN(rem) || rem < 0) {
        rem = 0;
    }
    pos_write_payment_amount($amounts.eq(n - 1), rem, { systemWrite: true, clearLock: true });
}

/**
 * Sync payment amounts to current cart total payable.
 * Create: 1 row → full; multi-row → keep row 1+ earlier, update last row only.
 * Edit: fill when empty / put remaining on zero rows.
 * Does NOT remove payment rows or reset selected methods.
 */
function pos_sync_payment_modal_amount(cleanExtraRows) {
    // cleanExtraRows kept for API compat — only strip on explicit true + create
    cleanExtraRows = cleanExtraRows === true;
    var is_edit =
        $('form#edit_pos_sell_form').length > 0 || $('form#edit_sell_form').length > 0;

    if (cleanExtraRows && !is_edit) {
        $('#payment_rows_div')
            .find('.remove_payment_row')
            .each(function () {
                var $col = $(this).closest('#payment_rows_div > .col-md-12');
                if ($col.length) {
                    $col.remove();
                } else {
                    $(this).closest('.payment_row').remove();
                }
            });
        if ($('#payment_row_index').length) {
            var remaining_rows = $('#payment_rows_div').find('.payment_row').length;
            $('#payment_row_index').val(Math.max(1, remaining_rows));
        }
    }

    var total_payable = __read_number($('input#final_total_input'));
    if (isNaN(total_payable)) {
        total_payable = 0;
    }

    var $amounts = $('#payment_rows_div').find('input.payment-amount');
    if (!$amounts.length) {
        if (typeof calculate_balance_due === 'function') {
            calculate_balance_due();
        }
        return;
    }

    if (is_edit) {
        var total_paying = 0;
        $amounts.each(function () {
            var amt = __read_number($(this));
            if (!isNaN(amt) && amt > 0) {
                total_paying += amt;
            }
        });
        if (total_paying <= 0.009) {
            pos_write_payment_amount($amounts.first(), total_payable);
        } else {
            var still_due = total_payable - total_paying;
            if (still_due > 0.009) {
                // Prefer empty rows, else last row residual
                var applied = false;
                $amounts.each(function () {
                    if (still_due <= 0.009) {
                        return false;
                    }
                    var cur = __read_number($(this));
                    if (isNaN(cur) || cur <= 0.009) {
                        pos_write_payment_amount($(this), still_due);
                        still_due = 0;
                        applied = true;
                        return false;
                    }
                });
                if (!applied && $amounts.length > 1) {
                    var sum_except_last = 0;
                    $amounts.each(function (idx) {
                        if (idx === $amounts.length - 1) {
                            return;
                        }
                        var a = __read_number($(this));
                        if (!isNaN(a) && a > 0) {
                            sum_except_last += a;
                        }
                    });
                    var last_val = total_payable - sum_except_last;
                    if (last_val < 0) {
                        last_val = 0;
                    }
                    pos_write_payment_amount($amounts.last(), last_val);
                }
            } else if (still_due < -0.009) {
                pos_apply_payable_to_payment_rows(total_payable);
            }
        }
    } else {
        // Create: 1 row full; multi = keep 1st amounts, update last only
        pos_apply_payable_to_payment_rows(total_payable);
    }

    if (typeof calculate_balance_due === 'function') {
        calculate_balance_due();
    }
}

/**
 * Full Pay modal refresh after cart changes / reopen.
 * Recalc totals from ALL cart lines, sync amount, keep methods visible.
 */
function pos_refresh_payment_modal() {
    // 1) Recalculate from every product currently in cart
    if (typeof pos_total_row === 'function') {
        pos_total_row();
    }

    // 2) Order-details panel (left) — force from live main POS values
    $('#modal_payment .payment_od_discount').text($('span#total_discount').first().text() || '0');
    if (typeof update_payment_od_item_discount === 'function') {
        update_payment_od_item_discount();
    }
    $('#modal_payment .payment_od_tax').text($('span#order_tax').first().text() || '0');
    $('#modal_payment .payment_od_shipping').text($('span#shipping_charges_amount').first().text() || '0');
    var packTxt = $('#packing_charge_text').text();
    if (packTxt && $.trim(packTxt) !== '' && $.trim(packTxt) !== '0') {
        $('#modal_payment .payment_od_packing').text(packTxt);
        $('#modal_payment .payment_od_packing_wrap').removeClass('hide');
    } else {
        $('#modal_payment .payment_od_packing').text('0');
    }

    // 3) Amounts = current payable (do not strip rows / do not reset methods)
    if (typeof pos_sync_payment_modal_amount === 'function') {
        pos_sync_payment_modal_amount(false);
    }

    // 4) Re-show payment-type detail fields for the already-selected method(s)
    $('#payment_rows_div')
        .find('select.payment_types_dropdown')
        .each(function () {
            try {
                $(this).trigger('change');
            } catch (err) {}
        });

    // 5) Highlight method cards last (after change handlers)
    if (typeof pos_sync_pay_method_cards === 'function') {
        pos_sync_pay_method_cards($('#modal_payment'));
    }
    if (typeof pos_update_add_payment_row_btn === 'function') {
        pos_update_add_payment_row_btn();
    }
}

// Every time payment modal opens: refresh FULL current cart + keep methods
    $('#modal_payment').on('shown.bs.modal', function() {
        if (typeof update_payment_modal_customer === 'function') {
            update_payment_modal_customer();
        }
        var customer_id = $('select#customer_id').val();
        if (customer_id && typeof get_contact_due === 'function') {
            get_contact_due(customer_id);
        } else if (typeof set_payment_modal_prev_due === 'function') {
            set_payment_modal_prev_due(0, '');
        }

        if (typeof pos_refresh_payment_modal === 'function') {
            pos_refresh_payment_modal();
        }

        // After paint — force re-apply so 2nd open always has current cart amounts
        var $modal = $(this);
        setTimeout(function () {
            if (typeof pos_total_row === 'function') {
                pos_total_row();
            }
            // Apply payable again AFTER total recalc (1st alone / last when multi)
            var payable = __read_number($('input#final_total_input'));
            if (typeof pos_apply_payable_to_payment_rows === 'function') {
                pos_apply_payable_to_payment_rows(payable);
            } else if (typeof pos_sync_payment_modal_amount === 'function') {
                pos_sync_payment_modal_amount(false);
            }
            if (typeof calculate_balance_due === 'function') {
                calculate_balance_due();
            }
            if (typeof pos_sync_pay_method_cards === 'function') {
                pos_sync_pay_method_cards($modal);
            }
            // Focus the last amount (that's the one that changes on multi-row)
            var $amt = $modal.find('#payment_rows_div input.payment-amount');
            ($amt.length > 1 ? $amt.last() : $amt.first()).filter(':visible').first().focus().select();
        }, 80);
    });

    // On close: leave method + rows as-is; next open will resync amount from cart
    $('#modal_payment').on('hidden.bs.modal', function () {
        // no-op strip / amount reset — avoids wiping user method on 2nd open
        if (typeof calculate_balance_due === 'function') {
            calculate_balance_due();
        }
    });

    //Finalize without showing payment options
    $(document).on('click', 'button.pos-express-finalize', function() {

        //Check if product is present or not.
        if ($('table#pos_table tbody').find('.product_row').length <= 0) {
            toastr.warning(LANG.no_products_added);
            return false;
        }

        if ($('#reward_point_enabled').length) {
            var validate_rp = isValidatRewardPoint();
            if (!validate_rp['is_valid']) {
                toastr.error(validate_rp['msg']);
                return false;
            }
        }

        var pay_method = $(this).data('pay_method');

        //If pay method is credit sale submit form
        if (pay_method == 'credit_sale') {
            $('#is_credit_sale').val(1);
            if (typeof pos_prepare_form_for_submit === 'function') {
                pos_prepare_form_for_submit();
            }
            pos_form_obj.submit();
            return true;
        } else {
            if ($('#is_credit_sale').length) {
                $('#is_credit_sale').val(0);
            }
        }

        // Park / suspend: open note modal only (do not force payment method "suspend")
        if (pay_method == 'suspend') {
            if ($('#confirmSuspendModal').parent()[0] !== document.body) {
                $('#confirmSuspendModal').appendTo('body');
            }
            $('div#confirmSuspendModal').modal('show');
            return false;
        }

        //Check for remaining balance & add it in 1st payment row
        var total_payable = __read_number($('input#final_total_input'));
        var total_paying = __read_number($('input#total_paying_input'));
        if (total_payable > total_paying) {
            var bal_due = total_payable - total_paying;

            var first_row = $('#payment_rows_div')
                .find('.payment-amount')
                .first();
            var first_row_val = __read_number(first_row);
            first_row_val = first_row_val + bal_due;
            __write_number(first_row, first_row_val);
            first_row.trigger('change');
        }

        //Change payment method.
        var payment_method_dropdown = $('#payment_rows_div')
            .find('.payment_types_dropdown')
            .first();

        if (payment_method_dropdown.length) {
            payment_method_dropdown.val(pay_method);
            try {
                payment_method_dropdown.trigger('change');
            } catch (err) {
                console.warn('payment method change failed', err);
            }
        }

        // Card / cash / other express methods: finalize immediately (no card details modal)
        if (typeof pos_prepare_form_for_submit === 'function') {
            pos_prepare_form_for_submit();
        }
        pos_form_obj.submit();
    });

    // Discount / tax / shipping modals live in clipped .pos-form-actions — move to body so Update works
    ['#posEditDiscountModal', '#posEditOrderTaxModal', '#posShippingModal'].forEach(function (sel) {
        var $m = $(sel);
        if ($m.length && $m.parent()[0] !== document.body) {
            $m.appendTo('body');
        }
    });

    // When opening discount modal, always show current (reset) form values — not previous sale leftover
    $(document).off('show.bs.modal.posDiscSync', '#posEditDiscountModal').on('show.bs.modal.posDiscSync', '#posEditDiscountModal', function() {
        if ($('#discount_type_modal').length && $('#discount_type').length) {
            $('#discount_type_modal').val($('#discount_type').val() || 'percentage');
        }
        if ($('#discount_amount_modal').length && $('#discount_amount').length) {
            __write_number($('#discount_amount_modal'), __read_number($('#discount_amount')));
        }
    });

    // When opening tax modal, always show current form tax (cleared after each sale)
    $(document).off('show.bs.modal.posTaxSync', '#posEditOrderTaxModal').on('show.bs.modal.posTaxSync', '#posEditOrderTaxModal', function() {
        if ($('#order_tax_modal').length && $('#tax_rate_id').length) {
            $('#order_tax_modal').val($('#tax_rate_id').val() || '');
        }
    });

    // Keep suspend modal on body so Save/Close clicks work (not clipped by cart panel)
    if ($('#confirmSuspendModal').length) {
        $('#confirmSuspendModal').appendTo('body');
    }

    $('div#confirmSuspendModal').on('shown.bs.modal', function(e) {
        $(this).removeClass('is-sliding-top');
        $(this)
            .find('textarea')
            .focus();
    });

    $(document).off('click.posSuspend', 'button#pos-suspend').on('click.posSuspend', 'button#pos-suspend', function(e) {
        e.preventDefault();
        e.stopPropagation();

        if ($('table#pos_table tbody').find('.product_row').length <= 0) {
            toastr.warning(LANG.no_products_added);
            return false;
        }

        $('input#is_suspend').val(1);

        // Keep Suspend Sale open until save succeeds, then slide it to the top and close.
        setTimeout(function() {
            if (typeof pos_form_obj === 'undefined' || !pos_form_obj.length) {
                return;
            }
            disable_pos_form_actions();

            var data = pos_serialize_sell_form(pos_form_obj);
            data = data + '&status=final&is_suspend=1';

            // Ensure numeric contacts.id — never send contact codes like CO0001
            function posResolveContactPk() {
                var raw = String($('#customer_id').val() || '').trim();
                if (/^\d+$/.test(raw)) {
                    return raw;
                }
                var def = String($('#default_customer_id').val() || '').trim();
                if (/^\d+$/.test(def)) {
                    return def;
                }
                return '';
            }

            var contactId = posResolveContactPk();
            if (contactId) {
                var cname = $('#customer_id option:selected').text()
                    || $('#default_customer_name').val()
                    || 'Walk-In Customer';
                if ($('#customer_id option[value="' + contactId + '"]').length === 0) {
                    $('#customer_id').append($('<option>', { value: contactId, text: cname }));
                }
                $('#customer_id').val(contactId).trigger('change');
                data = data.replace(/(^|&)contact_id=[^&]*/g, '');
                data += (data ? '&' : '') + 'contact_id=' + encodeURIComponent(contactId);
            } else {
                enable_pos_form_actions();
                toastr.error('Please select a customer before parking');
                return;
            }

            var noteVal = $('#confirmSuspendModal textarea[name="additional_notes"]').val();
            if (typeof noteVal !== 'undefined') {
                data += '&additional_notes=' + encodeURIComponent(noteVal);
            }

            // Prefer create endpoint for new park; use current action for repark (retrieved sale)
            var parkUrl = pos_form_obj.attr('action');
            var retrievedId = pos_form_obj.attr('data-retrieved-id') || pos_form_obj.data('retrieved-id');
            if (retrievedId) {
                parkUrl = (window.base_path || '') + '/pos/' + retrievedId;
                if (data.indexOf('_method=') === -1) {
                    data += '&_method=PUT';
                }
            }

            $.ajax({
                method: 'POST',
                url: parkUrl,
                data: data,
                dataType: 'json',
                success: function(result) {
                    $('input#is_suspend').val(0);
                    if (result.success == 1 || result.success === true) {
                        var afterParkSaved = function () {
                            $('.row_edit_product_price_model').remove();
                            $('#pos_table tbody.pos-cart-line').remove();
                            $('table#pos_table tbody tr.pos-line-edit-row').remove();
                            $('table#pos_table tbody tr.product_row').remove();
                            $('input#product_row_count').val(0);
                            if (typeof pos_sync_empty_state === 'function') {
                                pos_sync_empty_state();
                            }

                            var $addForm = $('#add_pos_sell_form');
                            if ($addForm.length && $addForm.data('original-action')) {
                                $addForm.attr('action', $addForm.data('original-action'));
                                $addForm.find('input[name="_method"]').remove();
                                $addForm.removeAttr('data-retrieved-id');
                                $addForm.removeData('retrieved-id');
                            }

                            reset_pos_form();

                            if (typeof refreshPosSuspendedCount === 'function') {
                                refreshPosSuspendedCount();
                            }
                            if (typeof loadPosDraftsPanel === 'function' && $('#pos_drafts_panel').length) {
                                loadPosDraftsPanel('draft');
                            }
                        };
                        pos_close_suspend_modal_to_top(afterParkSaved);
                    } else {
                        toastr.error(result.msg || 'Unable to park sale');
                    }
                    enable_pos_form_actions();
                },
                error: function(xhr) {
                    $('input#is_suspend').val(0);
                    enable_pos_form_actions();
                    var msg = (xhr.responseJSON && (xhr.responseJSON.msg || xhr.responseJSON.message))
                        ? (xhr.responseJSON.msg || xhr.responseJSON.message)
                        : 'Unable to park sale. Please try again.';
                    toastr.error(msg);
                },
            });
        }, 50);
    });

    //fix select2 input issue on modal
    $('#modal_payment')
        .find('.select2')
        .each(function() {
            $(this).select2({
                dropdownParent: $('#modal_payment'),
            });
        });

    $('button#add-payment-row').off('click.posAddPayRow').on('click.posAddPayRow', function() {
        // Don't add another row when current amount(s) already cover total payable
        var total_payable = __read_number($('input#final_total_input'));
        if (isNaN(total_payable)) {
            total_payable = 0;
        }
        var total_paying = 0;
        $('#payment_rows_div')
            .find('.payment-amount')
            .each(function () {
                var amount = __read_number($(this));
                if (!isNaN(amount)) {
                    total_paying += amount;
                }
            });
        var remaining = total_payable - total_paying;
        // already paid in full (or overpaid) — no extra row needed
        if (remaining <= 0.009) {
            if (typeof toastr !== 'undefined') {
                toastr.warning(
                    (typeof LANG !== 'undefined' && LANG.amount_already_settled) ||
                        'Amount already equals or exceeds total payable. No extra payment row needed.'
                );
            }
            return false;
        }

        var row_index = $('#payment_row_index').val();
        var location_id = $('input#location_id').val();
        $.ajax({
            method: 'POST',
            url: '/sells/pos/get_payment_row',
            data: { row_index: row_index, location_id: location_id },
            dataType: 'html',
            success: function(result) {
                if (result) {
                    var appended = $('#payment_rows_div').append(result);

                    var total_payable2 = __read_number($('input#final_total_input'));
                    if (isNaN(total_payable2)) {
                        total_payable2 = 0;
                    }
                    // Sum existing rows only (exclude newly appended last amount)
                    var $amounts = $('#payment_rows_div').find('.payment-amount');
                    var total_paying2 = 0;
                    $amounts.each(function (idx) {
                        if (idx === $amounts.length - 1) {
                            return;
                        }
                        var amount = __read_number($(this));
                        if (!isNaN(amount)) {
                            total_paying2 += amount;
                        }
                    });
                    var b_due = total_payable2 - total_paying2;
                    if (isNaN(b_due) || b_due < 0) {
                        b_due = 0;
                    }
                    // If nothing left, remove the empty row we just added
                    if (b_due <= 0.009) {
                        var $lastRow = $amounts.last().closest('.col-md-12');
                        if (!$lastRow.length) {
                            $lastRow = $amounts.last().closest('.payment_row');
                        }
                        $lastRow.remove();
                        if (typeof toastr !== 'undefined') {
                            toastr.warning('Amount already equals or exceeds total payable.');
                        }
                        if (typeof calculate_balance_due === 'function') {
                            calculate_balance_due();
                        }
                        if (typeof pos_update_add_payment_row_btn === 'function') {
                            pos_update_add_payment_row_btn();
                        }
                        return;
                    }
                    var $newAmount = $amounts.last();
                    // Keep cashier amounts on earlier rows (e.g. first typed 10)
                    $amounts.slice(0, -1).each(function () {
                        $(this).attr('data-pos-amt-locked', '1');
                    });
                    pos_write_payment_amount($newAmount, b_due, { systemWrite: true, clearLock: true });
                    $newAmount.focus().select();
                    __select2($(appended).find('.select2'));
                    $(appended).find('#method_' + row_index).change();
                    $('#payment_row_index').val(parseInt(row_index) + 1);
                    calculate_balance_due();
                    if (typeof pos_update_add_payment_row_btn === 'function') {
                        pos_update_add_payment_row_btn();
                    }
                }
            },
        });
    });

    $(document).on('click', '.remove_payment_row', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var $row = $(this).closest('.payment_row');
        if (!$row.length) {
            $row = $(this).closest('.box');
        }
        var doRemovePaymentRow = function() {
            $row.remove();
            if (typeof calculate_balance_due === 'function') {
                calculate_balance_due();
            }
        };
        // Confirm (swal must stack above #modal_payment; fallback = remove directly)
        if (typeof swal === 'function') {
            swal({
                title: (typeof LANG !== 'undefined' && LANG.sure) ? LANG.sure : 'Are you sure?',
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then(function(willDelete) {
                if (willDelete) {
                    doRemovePaymentRow();
                }
            });
        } else {
            doRemovePaymentRow();
        }
    });

    pos_form_validator = pos_form_obj.validate({
        submitHandler: function(form) {
            // var total_payble = __read_number($('input#final_total_input'));
            // var total_paying = __read_number($('input#total_paying_input'));
            var cnf = true;

            if (typeof pos_prepare_form_for_submit === 'function') {
                pos_prepare_form_for_submit();
            }

            // Suspend / park: skip balance-due confirm
            // Credit sale: previous system — due sale, skip paid-amount confirm
            if ($('input#is_suspend').val() == '1' || $('input#is_credit_sale').val() == '1') {
                cnf = true;
            } else if (parseFloat($('input#in_balance_due').val()) >= 0.5) {
                //Ignore if the difference is less than 0.5
                cnf = confirm(LANG.paid_amount_is_less_than_payable);
            }

            var total_advance_payments = 0;
            $('#payment_rows_div').find('select.payment_types_dropdown').each( function(){
                if ($(this).val() == 'advance') {
                    total_advance_payments++
                };
            });

            if (total_advance_payments > 1) {
                alert(LANG.advance_payment_cannot_be_more_than_once);
                return false;
            }

            var is_msp_valid = true;
            //Validate minimum selling price if hidden
            $('.pos_unit_price_inc_tax').each( function(){
                if (!$(this).is(":visible") && $(this).data('rule-min-value')) {
                    var val = __read_number($(this));
                    var error_msg_td = $(this).closest('tr').find('.pos_line_total_text').closest('td');
                    if (val > $(this).data('rule-min-value')) {
                        is_msp_valid = false;
                        error_msg_td.append( '<label class="error">' + $(this).data('msg-min-value') + '</label>');
                    } else {
                        error_msg_td.find('label.error').remove();
                    }
                }
            });

            if (!is_msp_valid) {
                return false;
            }

            if (cnf) {
                disable_pos_form_actions();

                // Includes unit_price + item discount from cart popup (may be on body)
                var data = pos_serialize_sell_form($(form));
                data = data.replace(/(^|&)is_suspend=[^&]*/g, '');
                data = data + '&status=final&is_suspend=0';
                // Payment modal may live on body (stacking) — include its fields
                var $payModal = $('#modal_payment');
                if ($payModal.length && !$payModal.closest(form).length) {
                    var payData = $payModal.find('input, select, textarea').serialize();
                    if (payData) {
                        data += '&' + payData;
                    }
                }
                // Suspend modal may live outside the form (appended to body) — append flags manually
                var isSuspend = $('input#is_suspend').val() == '1';
                if (isSuspend) {
                    data = data.replace(/(^|&)is_suspend=[^&]*/g, '');
                    data += '&is_suspend=1';
                    var noteVal = $('#confirmSuspendModal textarea[name="additional_notes"]').val();
                    if (typeof noteVal !== 'undefined') {
                        data += '&additional_notes=' + encodeURIComponent(noteVal);
                    }
                }
                var url = $(form).attr('action');
                $.ajax({
                    method: 'POST',
                    url: url,
                    data: data,
                    dataType: 'json',
                    success: function(result) {
                        $('input#is_suspend').val(0);
                        if (result.success == 1) {
                            if (result.whatsapp_link) {
                                window.open(result.whatsapp_link);
                            }
                            $('#modal_payment').modal('hide');
                            $('#confirmSuspendModal').modal('hide');
                            toastr.success(result.msg);

                            // If retrieve rebound create→update, restore create form for next sale
                            var $addForm = $('#add_pos_sell_form');
                            if ($addForm.length && $addForm.data('original-action')) {
                                $addForm.attr('action', $addForm.data('original-action'));
                                $addForm.find('input[name="_method"]').remove();
                                $addForm.removeAttr('data-retrieved-id');
                            }

                            // Print invoice FIRST — edit page reset_pos_form() redirects immediately
                            // and would cancel the print dialog (retrieve parked → pay flow)
                            var doReset = function() {
                                reset_pos_form();
                                if (typeof refreshPosSuspendedCount === 'function') {
                                    refreshPosSuspendedCount();
                                }
                                if (typeof loadPosDraftsPanel === 'function' && $('#pos_drafts_panel').length) {
                                    loadPosDraftsPanel('draft');
                                }
                                // Silent stock refresh after sale (no grid reload)
                                if (typeof posFetchLiveStock === 'function') {
                                    setTimeout(posFetchLiveStock, 300);
                                }
                            };

                            // Park / suspend is not a sale — do not print
                            if (isSuspend) {
                                doReset();
                            } else if (result.receipt && result.receipt.is_enabled) {
                                pos_print(result.receipt);
                                if ($('form#edit_pos_sell_form').length > 0) {
                                    setTimeout(doReset, 1200);
                                } else {
                                    doReset();
                                }
                            } else {
                                doReset();
                            }
                        } else {
                            toastr.error(result.msg);
                        }

                        enable_pos_form_actions();
                    },
                    error: function() {
                        $('input#is_suspend').val(0);
                        enable_pos_form_actions();
                        toastr.error('Unable to save sale. Please try again.');
                    },
                });
            }
            return false;
        },
    });

    // Live recalculate when payment amounts change (typing + blur)
    // Lock field when cashier types so auto-sync won't reset (e.g. 10 of bill 20)
    $(document).on('input keyup', '#payment_rows_div .payment-amount', function () {
        var $el = $(this);
        if ($el.data('pos-writing')) {
            calculate_balance_due();
            return;
        }
        $el.attr('data-pos-amt-locked', '1');
        calculate_balance_due();
        if (typeof pos_update_add_payment_row_btn === 'function') {
            pos_update_add_payment_row_btn();
        }
    });
    $(document).on('change', '#payment_rows_div .payment-amount', function () {
        var $el = $(this);
        if (!$el.data('pos-writing')) {
            $el.attr('data-pos-amt-locked', '1');
        }
        calculate_balance_due();
        if (typeof pos_update_add_payment_row_btn === 'function') {
            pos_update_add_payment_row_btn();
        }
    });

    // Optional: keep other screens that use .payment-amount (sell form)
    $(document).on('change', '.payment-amount', function() {
        if ($(this).closest('#payment_rows_div').length) {
            return; // already handled above
        }
        if (typeof calculate_balance_due === 'function') {
            calculate_balance_due();
        }
    });

    //Update discount (delegated — modal may be moved to body)
    $(document).off('click.posDiscountUpdate', 'button#posEditDiscountModalUpdate').on('click.posDiscountUpdate', 'button#posEditDiscountModalUpdate', function(e) {
        e.preventDefault();

        // Do not call .valid() — modal is appended to body (outside POS form),
        // so jquery.validate has no form settings and throws.
        var $discountModalAmt = $('#discount_amount_modal');
        var discountAmt = __read_number($discountModalAmt);
        if ($discountModalAmt.length && (discountAmt === '' || isNaN(discountAmt))) {
            toastr.error(LANG.please_enter_discount_amount || 'Please enter a valid discount amount');
            return false;
        }
        var maxDiscount = parseFloat($discountModalAmt.data('max-discount'));
        if (!isNaN(maxDiscount) && $('select#discount_type_modal').val() == 'percentage' && discountAmt > maxDiscount) {
            toastr.error($discountModalAmt.data('max-discount-error_msg') || 'Discount exceeds maximum allowed');
            return false;
        }

        $('div#posEditDiscountModal').modal('hide');

        $('input#discount_type').val($('select#discount_type_modal').val());
        __write_number($('input#discount_amount'), discountAmt);

        if ($('#reward_point_enabled').length) {
            var reward_validation = isValidatRewardPoint();
            if (!reward_validation['is_valid']) {
                toastr.error(reward_validation['msg']);
                $('#rp_redeemed_modal').val(0);
                $('#rp_redeemed_modal').change();
            }
            updateRedeemedAmount();
        }

        pos_total_row();
    });

    //Shipping (delegated — modal moved to body)
    $(document).off('click.posShippingUpdate', 'button#posShippingModalUpdate').on('click.posShippingUpdate', 'button#posShippingModalUpdate', function() {
        //Close modal
        $('div#posShippingModal').modal('hide');

        //update shipping details
        $('input#shipping_details').val($('#shipping_details_modal').val());

        $('input#shipping_address').val($('#shipping_address_modal').val());
        $('input#shipping_status').val($('#shipping_status_modal').val());
        $('input#delivered_to').val($('#delivered_to_modal').val());
        $('input#delivery_person').val($('#delivery_person_modal').val());
        if ($('#shipping_method_modal').length) {
            $('input#shipping_method').val($('#shipping_method_modal').val());
        }
        if ($('#shipping_date_modal').length) {
            $('input#shipping_date').val($('#shipping_date_modal').val());
        }
        if ($('#shipping_tracking_no_modal').length) {
            $('input#shipping_tracking_no').val($('#shipping_tracking_no_modal').val());
        }
        if ($('#no_of_boxes_modal').length) {
            $('input#no_of_boxes').val($('#no_of_boxes_modal').val());
        }

        //Update shipping charges
        __write_number(
            $('input#shipping_charges'),
            __read_number($('input#shipping_charges_modal'))
        );

        //$('input#shipping_charges').val(__read_number($('input#shipping_charges_modal')));

        pos_total_row();
    });

    $('#posShippingModal').on('shown.bs.modal', function() {
        $('#posShippingModal')
            .find('#shipping_details_modal')
            .filter(':visible:first')
            .focus()
            .select();
        if ($('#posShippingModal .shipping-date-picker').length && !$('#posShippingModal .shipping-date-picker').data('datepicker')) {
            $('#posShippingModal .shipping-date-picker').datepicker({ autoclose: true, todayHighlight: true });
        }
        // Sync modal fields from current POS form values
        if ($('#shipping_method').val()) {
            $('#shipping_method_modal').val($('#shipping_method').val()).trigger('change');
        }
        if ($('#shipping_date').val()) {
            $('#shipping_date_modal').val($('#shipping_date').val());
        }
        if ($('#shipping_tracking_no').val()) {
            $('#shipping_tracking_no_modal').val($('#shipping_tracking_no').val());
        }
        if ($('#no_of_boxes').val()) {
            $('#no_of_boxes_modal').val($('#no_of_boxes').val());
        }
            // $('.select2-selection__rendered').css('padding-right', '150px');
    });

    $(document).on('click.posLineEditOpen', 'table#pos_table tbody tr.product_row', function(e) {
        if ($(e.target).closest('input, select, textarea, button, a, .pos_remove_row, .quantity-up, .quantity-down, .input-number, .pos-sub-unit-select').length) {
            return;
        }
        e.preventDefault();
        pos_toggle_line_edit($(this));
    });

    $(document).on('click.posLineEditName', '.pos-line-open-edit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        pos_toggle_line_edit(pos_product_row_from_el($(this)));
    });

    // Live-sync Details qty <-> cart row qty
    $(document).on('change.posLineEditQty input.posLineEditQty', 'input.pos-line-edit-qty', function() {
        var $modal = $(this).closest('.row_edit_product_price_model');
        var $tr = pos_product_row_from_el($modal);
        if (!$tr.length) {
            return;
        }
        var qty = __read_number($(this));
        var $rowQty = $tr.find('input.pos_quantity');
        if ($rowQty.length && !isNaN(qty)) {
            __write_number($rowQty, qty);
            $rowQty.trigger('change');
        }
    });

    $(document).on('change.posRowQtySync', 'table#pos_table tbody tr.product_row input.pos_quantity', function() {
        var $tr = $(this).closest('tr.product_row');
        var $modal = pos_row_edit_modal($tr);
        if (!$modal.length || !$tr.closest('tbody').find('tr.pos-line-edit-row.is-open').length) {
            return;
        }
        var $panelQty = $modal.find('input.pos-line-edit-qty');
        if ($panelQty.length) {
            __write_number($panelQty, __read_number($(this)));
        }
    });

    $(document).on('change.posDiscSuffix', '.pos-line-edit-panel select.row_discount_type', function() {
        var $modal = $(this).closest('.row_edit_product_price_model');
        var $suffix = $modal.find('.pos-line-discount-suffix');
        if (!$suffix.length) {
            return;
        }
        if ($(this).val() === 'fixed') {
            var sym = ($modal.find('.pos-line-details-prefix').first().text() || '').trim() || '$';
            $suffix.text(sym);
        } else {
            $suffix.text('%');
        }
    });

    $(document).on('click.posRowEditUpdate', '.pos-row-edit-price-update', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var $modal = $(this).closest('.row_edit_product_price_model');
        var tr = pos_product_row_from_el($modal);
        pos_apply_row_edit_modal($modal, tr, true);
        pos_close_line_edit(tr);
        return false;
    });

    $(document).on('click.posRowEditClose', '.pos-line-edit-panel [data-dismiss="modal"]', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var tr = pos_product_row_from_el($(this));
        var $modal = $(this).closest('.row_edit_product_price_model');
        pos_apply_row_edit_modal($modal, tr, false);
        pos_close_line_edit(tr);
        return false;
    });

    //Update Order tax (delegated — modal moved to body)
    $(document).off('click.posTaxUpdate', 'button#posEditOrderTaxModalUpdate').on('click.posTaxUpdate', 'button#posEditOrderTaxModalUpdate', function() {
        //Close modal
        $('div#posEditOrderTaxModal').modal('hide');

        var tax_obj = $('select#order_tax_modal');
        var tax_id = tax_obj.val();
        var tax_rate = tax_obj.find(':selected').data('rate');

        $('input#tax_rate_id').val(tax_id);

        __write_number($('input#tax_calculation_amount'), tax_rate);
        pos_total_row();
    });

    function posContactModal() {
        var $modal = $('#pos_contact_modal');
        if (!$modal.length) {
            $modal = $('form#quick_add_contact').closest('.contact_modal');
        }
        // Never use empty #global_contact_modal
        if ($modal.is('#global_contact_modal')) {
            $modal = $('form#quick_add_contact').closest('.contact_modal');
        }
        return $modal;
    }

    $(document).on('click', '.add_new_customer', function(e) {
        e.preventDefault();
        e.stopPropagation();

        try {
            $('#customer_id').select2('close');
        } catch (err) {}
        if (typeof posCustPickerClose === 'function') {
            posCustPickerClose();
        }

        var name = $(this).attr('data-name') || '';
        var $modal = posContactModal();
        if (!$modal.length) {
            return;
        }

        if (!$modal.parent().is('body')) {
            $modal.appendTo('body');
        }

        $modal.find('input#name').val(name);
        $modal.find('select#contact_type')
            .val('customer')
            .closest('div.contact_type_div')
            .addClass('hide');
        var $indiv = $modal.find('#inlineRadio1');
        if ($indiv.length) {
            if ($indiv.parent().hasClass('iradio_square-blue')) {
                $indiv.iCheck('check');
            } else {
                $indiv.prop('checked', true);
            }
            $modal.find('div.individual').show();
            $modal.find('div.contact_person').show();
            $modal.find('.contact_person_hint').hide();
            $modal.find('div.business').hide();
        }

        // Same click can hit the new backdrop and instantly close. Show next tick.
        setTimeout(function () {
            $modal.modal('show');
        }, 0);
    });
    $('form#quick_add_contact')
        .submit(function(e) {
            e.preventDefault();
        })
        .validate({
            rules: {
                contact_id: {
                    remote: {
                        url: '/contacts/check-contacts-id',
                        type: 'post',
                        data: {
                            contact_id: function() {
                                return $('#contact_id').val();
                            },
                            hidden_id: function() {
                                return $('#hidden_id').val() || '';
                            },
                        },
                    },
                },
                // tax_number remote validation removed - now handled with sweet alert
            },
            messages: {
                contact_id: {
                    required: LANG.contact_id_required,
                    remote: LANG.contact_id_already_exists,
                },
            },
            submitHandler: function(form) {
                checkTaxNumberAndSubmitQuick(form);
            },
        });

    function checkTaxNumberAndSubmitQuick(form) {
        // Check if tax_number field exists and has a value
        if ($('#tax_number').length && $('#tax_number').val().trim() !== '') {
            $.ajax({
                method: 'POST',
                url: base_path + '/contacts/check-tax-number',
                dataType: 'json',
                data: {
                    contact_id: $('#hidden_id').val(),
                    tax_number: $('#tax_number').val(),
                },
                success: function(result) {
                    if (result.is_tax_number_exists == true) {
                        swal({
                            title: LANG.sure,
                            text: result.msg,
                            icon: 'warning',
                            buttons: true,
                            dangerMode: true,
                        }).then(willContinue => {
                            if (willContinue) {
                                checkMobileAndSubmitQuick(form);
                            } else {
                                $('#tax_number').select();
                            }
                        });
                    } else {
                        checkMobileAndSubmitQuick(form);
                    }
                },
            });
        } else {
            // If no tax number, proceed to mobile check
            checkMobileAndSubmitQuick(form);
        }
    }

    function checkMobileAndSubmitQuick(form) {
        $.ajax({
            method: 'POST',
            url: base_path + '/check-mobile',
            dataType: 'json',
            data: {
                contact_id: function() {
                    return $('#hidden_id').val();
                },
                mobile_number: function() {
                    return $('#mobile').val();
                },
            },
            success: function(result) {
                if (result.is_mobile_exists == true) {
                    swal({
                        title: LANG.sure,
                        text: result.msg,
                        icon: 'warning',
                        buttons: true,
                        dangerMode: true,
                    }).then(willContinue => {
                        if (willContinue) {
                            submitQuickContactForm(form);
                        } else {
                            $('#mobile').select();
                        }
                    });
                    
                } else {
                    submitQuickContactForm(form);
                }
            },
        });
    }

    $(document).on('hidden.bs.modal', '#pos_contact_modal, .contact_modal', function() {
        if (this.id === 'global_contact_modal') {
            return;
        }
        var $form = $(this).find('form#quick_add_contact');
        if ($form.length) {
            $form.find('button[type="submit"]').removeAttr('disabled');
            if ($form[0]) {
                $form[0].reset();
            }
        }
        if (!$('.modal.in:visible, .modal.show:visible').not(this).length) {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css({ paddingRight: '', overflow: '' });
        }
    });

    //Updates for add sell
    $('select#discount_type, input#discount_amount, input#shipping_charges, \
        input#rp_redeemed_amount').change(function() {
        pos_total_row();
    });
    $('select#tax_rate_id').change(function() {
        var tax_rate = $(this)
            .find(':selected')
            .data('rate');
        __write_number($('input#tax_calculation_amount'), tax_rate);
        pos_total_row();
    });
    // Sale date is date-only on screen; real time is stamped server-side on finalize.
    if ($.fn.datepicker) {
        try {
            if ($('#transaction_date').data('DateTimePicker')) {
                $('#transaction_date').data('DateTimePicker').destroy();
            }
        } catch (err) {}
        $('#transaction_date').datepicker({
            autoclose: true,
            todayHighlight: true,
            enableOnReadonly: true,
            forceParse: false,
            format: typeof datepicker_date_format !== 'undefined' ? datepicker_date_format : 'mm/dd/yyyy',
        });
    } else {
        $('#transaction_date').datetimepicker({
            format: moment_date_format,
            ignoreReadonly: true,
        });
    }

    //Direct sell submit
    sell_form = $('form#add_sell_form');
    if ($('form#edit_sell_form').length) {
        sell_form = $('form#edit_sell_form');
        pos_total_row();
    }
    sell_form_validator = sell_form.validate({
        rules: {
            invoice_no: {
                remote: {
                    url: '/sell/check-invoice-number',
                    type: 'post',
                    data: {
                        invoice_no: function() {
                            return $('#invoice_no').val();
                        },
                        transaction_id: function() {
                            var id = '';
                            var editForm = $('form#edit_sell_form');
                            if (editForm.length) {
                                id = editForm.data('transaction-id');
                            }
                            return id || '';
                        }
                    }
                }
            },
        },
        messages: {
            invoice_no: {
                remote: LANG.invoice_number_already_exists,
            },
        },
    });

    $('button#submit-sell, button#save-and-print').click(function(e) {
        //Check if product is present or not.
        if ($('table#pos_table tbody').find('.product_row').length <= 0) {
            toastr.warning(LANG.no_products_added);
            return false;
        }

        var is_msp_valid = true;
        //Validate minimum selling price if hidden
        $('.pos_unit_price_inc_tax').each( function(){
            if (!$(this).is(":visible") && $(this).data('rule-min-value')) {
                var val = __read_number($(this));
                var error_msg_td = $(this).closest('tr').find('.pos_line_total_text').closest('td');
                if (val > $(this).data('rule-min-value')) {
                    is_msp_valid = false;
                    error_msg_td.append( '<label class="error">' + $(this).data('msg-min-value') + '</label>');
                } else {
                    error_msg_td.find('label.error').remove();
                }
            }
        });

        if (!is_msp_valid) {
            return false;
        }

        if ($(this).attr('id') == 'save-and-print') {
            $('#is_save_and_print').val(1);           
        } else {
            $('#is_save_and_print').val(0);
        }

        if ($('#reward_point_enabled').length) {
            var validate_rp = isValidatRewardPoint();
            if (!validate_rp['is_valid']) {
                toastr.error(validate_rp['msg']);
                return false;
            }
        }

        if ($('.enable_cash_denomination_for_payment_methods').length) {
            var payment_row = $('.enable_cash_denomination_for_payment_methods').closest('.payment_row');
            var is_valid = true;
            var payment_type = payment_row.find('.payment_types_dropdown').val();
            var denomination_for_payment_types = JSON.parse($('.enable_cash_denomination_for_payment_methods').val());
            if (denomination_for_payment_types.includes(payment_type) && payment_row.find('.is_strict').length && payment_row.find('.is_strict').val() === '1' ) {
                var payment_amount = __read_number(payment_row.find('.payment-amount'));
                var total_denomination = payment_row.find('input.denomination_total_amount').val();
                if (payment_amount != total_denomination ) {
                    is_valid = false;
                }
            }

            if (!is_valid) {
                payment_row.find('.cash_denomination_error').removeClass('hide');
                toastr.error(payment_row.find('.cash_denomination_error').text());
                e.preventDefault();
                return false;
            } else {
                payment_row.find('.cash_denomination_error').addClass('hide');
            }
        }

        if (sell_form.valid()) {
            window.onbeforeunload = null;
            $(this).attr('disabled', true);
            sell_form.submit();
        }
    });

    //REPAIR MODULE:check if repair module field is present send data to filter product
    var is_enabled_stock = null;
    if ($("#is_enabled_stock").length) {
        is_enabled_stock = $("#is_enabled_stock").val();
    }

    var device_model_id = null;
    if ($("#repair_model_id").length) {
        device_model_id = $("#repair_model_id").val();
    }

    //Show product list — start with All Categories selected
    function pos_initial_category_id() {
        return 'all';
    }

    if ($('#pos_defer_product_list').length && $('#pos_defer_product_list').val() == '1') {
        // Categories-first UI: wait for category click / search
        if (typeof pos_set_active_category_tile === 'function') {
            pos_set_active_category_tile('all');
        }
    } else {
        global_p_category_id = null;
        if (typeof pos_set_filter_chip === 'function') {
            pos_set_filter_chip('category', null);
        }
        if (typeof pos_set_active_category_tile === 'function') {
            pos_set_active_category_tile('all');
        } else {
            $('.pos-card-grid-categories .main-category[data-value="all"] .pos-card.cat').addClass('is-active');
        }
        get_product_suggestion_list(
            global_p_category_id,
            global_brand_id,
            $('input#location_id').val(),
            null,
            is_enabled_stock,
            device_model_id
        );
    }

    // Real-time stock: poll DB + update tiles without full page reload
    if (typeof startPosLiveStockPolling === 'function') {
        startPosLiveStockPolling();
    }
    
    $('select#select_location_id').on('change', function(e) {
        $('input#suggestion_page').val(1);
        var location_id = $('input#location_id').val();
        if (location_id != '' || location_id != undefined) {
            if ($('#pos_defer_product_list').length && $('#pos_defer_product_list').val() == '1' && !global_p_category_id) {
                // keep categories-first until a category is chosen
            } else {
                get_product_suggestion_list(
                    global_p_category_id,
                    global_brand_id,
                    $('input#location_id').val(),
                    null
                );
            }
        }
        get_featured_products();
    });

// Active filter is shown as a second line INSIDE the matching Category / Brand
    // button. The blade template already renders the row markup (hidden); this
    // function just toggles `.is-active` on it and writes the selected name in.
    function pos_set_filter_chip(kind, name) {
        var cls = (kind === 'category') ? '.pos-filter-badge-cat'
                : (kind === 'brand')    ? '.pos-filter-badge-brand'
                : null;
        if (!cls) return;
        var $rows = $(cls);
        if (!$rows.length) return;
        var $names = $rows.find('.pos-active-filter-name');
        if (!name) {
            $rows.removeClass('is-active');
            $names.text('');
            return;
        }
        $names.text(name);
        $rows.addClass('is-active');
    }

    // Re-trigger the staggered card entrance each time a drawer opens.
    // Using class toggle + forced reflow because CSS @keyframes only fire once per element.
    function pos_animate_drawer_cards($drawerSide) {
        var $grid = $drawerSide.find('.pos-card-grid').filter(':visible');
        $grid.each(function() {
            var $g = $(this);
            $g.removeClass('is-animating');
            void this.offsetWidth;
            $g.addClass('is-animating');
        });
    }

    $(document).on('click', '.pos-filter-chip-clear', function(e) {
        // Pill wraps the × in a <label for="...">; without these the click would
        // also toggle the drawer checkbox.
        e.preventDefault();
        e.stopPropagation();
        var kind = $(this).data('clear');
        if (kind === 'category') {
            global_p_category_id = null;
            pos_set_active_category_tile('all');
        } else if (kind === 'brand') {
            global_brand_id = null;
        }
        pos_set_filter_chip(kind, null);
        $('input#suggestion_page').val(1);
        get_product_suggestion_list(global_p_category_id, global_brand_id, $('input#location_id').val(), null);
        get_featured_products();
    });

    // Highlight the active category tile (All or a specific category)
    function pos_set_active_category_tile(value) {
        var $grid = $('.pos-card-grid-categories');
        if (!$grid.length) return;
        $grid.find('.pos-card.cat').removeClass('is-active');
        var sel = (value === null || value === undefined || value === '' || value === 'all')
            ? '.main-category[data-value="all"] .pos-card.cat'
            : '.main-category[data-value="' + value + '"] .pos-card.cat';
        $grid.find(sel).first().addClass('is-active');
    }
    window.pos_set_active_category_tile = pos_set_active_category_tile;

    // on click sub category in category drawer
    $('.product_category').on('click', function(e) {
        global_p_category_id = $(this).data('value');
        pos_set_filter_chip('category', $(this).data('name'));
        pos_set_active_category_tile(global_p_category_id);
        $('input#suggestion_page').val(1);
        var location_id = $('input#location_id').val();
        if (location_id != '' || location_id != undefined) {
            get_product_suggestion_list(
                global_p_category_id,
                global_brand_id,
                $('input#location_id').val(),
                null
            );
        }
        get_featured_products();
        $('.overlay-category').trigger('click');
    });

    // Category card tap:
    //   "All"              → clear filter, show all products
    //   No subcategories   → filter directly
    //   Has subcategories  → load parent products + subcategory pills
    $('.pos-card-grid-categories').on('click', '.main-category', function(e) {
        e.stopPropagation();

        var value = $(this).data('value');
        var name  = $(this).data('name');
        var $grid = $('.pos-card-grid-categories');

        // "All Categories" — clear filter and any open strip
        if (value === 'all') {
            global_p_category_id = null;
            pos_set_filter_chip('category', null);
            $grid.find('.pos-subcat-strip').remove();
            $grid.removeData('open-cat');
            pos_set_active_category_tile('all');
            $('input#suggestion_page').val(1);
            get_product_suggestion_list(null, global_brand_id, $('input#location_id').val(), null);
            get_featured_products();
            $('.overlay-category').trigger('click');
            return;
        }

        // Check for subcategory data stored in the hidden divs
        var $subcatSource = $('.all-sub-category[data-category-id="' + value + '"]');

        if (!$subcatSource.length) {
            // No subcategories — filter immediately and close
            global_p_category_id = value;
            pos_set_filter_chip('category', name);
            $grid.find('.pos-subcat-strip').remove();
            $grid.removeData('open-cat');
            pos_set_active_category_tile(value);
            $('input#suggestion_page').val(1);
            get_product_suggestion_list(global_p_category_id, global_brand_id, $('input#location_id').val(), null);
            get_featured_products();
            $('.overlay-category').trigger('click');
            return;
        }

        // Has subcategories — tapping same card again collapses the strip
        if ($grid.data('open-cat') == value) {
            $grid.find('.pos-subcat-strip').remove();
            $grid.removeData('open-cat');
            return;
        }

        // Mark active parent card
        $grid.data('open-cat', value);
        pos_set_active_category_tile(value);

        // Load parent category products immediately (don't wait for subcategory pill)
        global_p_category_id = value;
        pos_set_filter_chip('category', name);
        $('input#suggestion_page').val(1);
        get_product_suggestion_list(global_p_category_id, global_brand_id, $('input#location_id').val(), null);
        get_featured_products();

        // Find the last col in the same visual row (3 cols per row at md breakpoint)
        var $cols     = $grid.children('[class*="col-"]').not('.pos-subcat-strip');
        var idx       = $cols.index($(this).closest('[class*="col-"]'));
        var $lastInRow = $cols.eq(Math.min(Math.ceil((idx + 1) / 3) * 3 - 1, $cols.length - 1));

        // Build pills: first pill = "All [Name]" (selects parent), rest = subcategories
        var allLabel = $grid.data('label-all') || 'All';
        var pills = '<button type="button" class="pos-subcat-pill pos-subcat-pill--all" data-value="' + value + '" data-name="' + name + '">' + allLabel + ' ' + name + '</button>';
        $subcatSource.find('.product_category').each(function() {
            var scId   = $(this).data('value');
            var scName = $(this).data('name');
            if (scId && scName) {
                pills += '<button type="button" class="pos-subcat-pill" data-value="' + scId + '" data-name="' + scName + '">' + scName + '</button>';
            }
        });

        $grid.find('.pos-subcat-strip').remove();
        $('<div class="pos-subcat-strip"></div>').html(pills).insertAfter($lastInRow);
    });

    // Subcategory pill tap — delegated because pills are dynamically injected
    $(document).on('click', '.pos-subcat-pill', function(e) {
        e.stopPropagation();
        var value = $(this).data('value');
        var name  = $(this).data('name');

        global_p_category_id = value;
        pos_set_filter_chip('category', name);
        // Keep parent tile active when a subcategory is chosen
        var parentId = $(this).closest('.pos-card-grid-categories').data('open-cat') || value;
        if ($(this).hasClass('pos-subcat-pill--all')) {
            parentId = value;
        }
        pos_set_active_category_tile(parentId);
        $('input#suggestion_page').val(1);
        get_product_suggestion_list(global_p_category_id, global_brand_id, $('input#location_id').val(), null);
        get_featured_products();

        var $grid = $('.pos-card-grid-categories');
        $grid.find('.pos-subcat-strip').remove();
        $grid.removeData('open-cat');
        $('.overlay-category').trigger('click');
    });

    // on click brand in brand drawer
    $('.product_brand').on('click', function(e) {
        global_brand_id = $(this).data('value');
        pos_set_filter_chip('brand', $(this).data('name'));
        $('input#suggestion_page').val(1);
        var location_id = $('input#location_id').val();

        if (location_id != '' || location_id != undefined) {
            get_product_suggestion_list(
                global_p_category_id,
                global_brand_id,
                $('input#location_id').val(),
                null
            );
        }
        get_featured_products();
        $('.overlay-brand').trigger('click');
    });

    // close side bar

    $('.close-side-bar-category').on('click', function() {
        $('.overlay-category').trigger('click');
    });

    $('.close-side-bar-brand').on('click', function() {
        $('.overlay-brand').trigger('click');
    });

    // Each drawer opens → re-run card entrance stagger.
    // Category drawer also resets to the main view when it closes (so next open is clean).
    $('#my-drawer-4').on('change', function() {
        var $side = $(this).siblings('.tw-dw-drawer-side');
        if (this.checked) {
            pos_animate_drawer_cards($side);
        } else {
            // Drawer closed — collapse any open subcategory strip
            var $grid = $('.pos-card-grid-categories');
            $grid.find('.pos-subcat-strip').remove();
            $grid.removeData('open-cat');
            $grid.find('.pos-card.cat').removeClass('is-active');
        }
    });
    $('#my-drawer-brand').on('change', function() {
        if (this.checked) {
            pos_animate_drawer_cards($(this).siblings('.tw-dw-drawer-side'));
        }
    });

    // When the mobile product-suggestion Bootstrap modal closes, uncheck any DaisyUI
    // drawer toggles that may have been activated inside it (duplicate IDs mean the
    // label inside the modal targets the main-page drawer, leaving its overlay open).
    $(document).on('hidden.bs.modal', '#mobile_product_suggestion_modal', function() {
        // Uncheck both the main-sidebar drawers and the modal-sidebar drawers (suffix _modal)
        $('#my-drawer-4, #my-drawer-4_modal, #my-drawer-brand, #my-drawer-brand_modal').prop('checked', false);
        var $grid = $('.pos-card-grid-categories');
        $grid.find('.pos-subcat-strip').remove();
        $grid.removeData('open-cat');
        $grid.find('.pos-card.cat').removeClass('is-active');
    });




    $(document).on('click', 'div.product_box', function() {
        // Stock-out tiles are disabled on POS unless overselling is allowed.
        if ($(this).hasClass('product_out_of_stock') && !$('input#is_overselling_allowed').length) {
            toastr.error(LANG.out_of_stock);
            return;
        }

        //Check if location is not set then show error message.
        if ($('input#location_id').val() == '') {
            toastr.warning(LANG.select_location);
        } else {
            pos_product_row($(this).data('variation_id'));
        }
    });

    $(document).on('shown.bs.modal', '.row_description_modal', function() {
        $(this)
            .find('textarea')
            .first()
            .focus();
    });

    //Press enter on search product to jump into last quantty and vice-versa
    $('#search_product').keydown(function(e) {
        var key = e.which;
        if (key == 9) {
            // the tab key code
            e.preventDefault();
            if ($('#pos_table tbody tr.product_row').length > 0) {
                // Newest line is first after last scan
                $('#pos_table tbody tr.product_row:first')
                    .find('input.pos_quantity')
                    .focus()
                    .select();
            }
        }
    });
    $('#pos_table').on('keypress', 'input.pos_quantity', function(e) {
        var key = e.which;
        if (key == 13) {
            // the enter key code
            if (!$('#__is_mobile').length) {
                $('#search_product').focus();
            }
        }
    });

    $('#exchange_rate').change(function() {
        var curr_exchange_rate = 1;
        if ($(this).val()) {
            curr_exchange_rate = __read_number($(this));
        }
        var total_payable = __read_number($('input#final_total_input'));
        var shown_total = total_payable * curr_exchange_rate;
        $('span#total_payable').text(__currency_trans_from_en(shown_total, false));
    });

    $('select#price_group').change(function() {
        $('input#hidden_price_group').val($(this).val());
        if (typeof pos_schedule_cart_price_refresh === 'function') {
            pos_schedule_cart_price_refresh();
        }
    });

    //Quick add product
    $(document).on('click', 'button.pos_add_quick_product', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var url = $(this).data('href');
        var container = $(this).data('container');
        $.ajax({
            url: url + '?product_for=pos',
            dataType: 'html',
            success: function(result) {
                $(container)
                    .html(result)
                    .modal('show');
                $('.os_exp_date').datepicker({
                    autoclose: true,
                    format: 'dd-mm-yyyy',
                    clearBtn: true,
                });
            },
        });
    });

    // Keep barcode / product search focused after quick-add closes
    $(document).on('hidden.bs.modal', '.quick_add_product_modal', function() {
        var $search = $('input#search_product');
        if ($search.length && !$search.prop('disabled')) {
            setTimeout(function() {
                $search.focus().select();
            }, 50);
        }
    });

    $(document).on('change', 'form#quick_add_product_form input#single_dpp', function() {
        var unit_price = __read_number($(this));
        $('table#quick_product_opening_stock_table tbody tr').each(function() {
            var input = $(this).find('input.unit_price');
            __write_number(input, unit_price);
            input.change();
        });
    });

    $(document).on('quickProductAdded', function(e) {
        //Check if location is not set then show error message.
        if ($('input#location_id').val() == '') {
            toastr.warning(LANG.select_location);
        } else {
            pos_product_row(e.variation.id);
        }
        // Refresh product tiles so newly assigned image shows for selected category
        if (typeof get_product_suggestion_list === 'function' && $('div#product_list_body').length) {
            $('input#suggestion_page').val(1);
            get_product_suggestion_list(
                typeof global_p_category_id !== 'undefined' ? global_p_category_id : null,
                typeof global_brand_id !== 'undefined' ? global_brand_id : null,
                $('input#location_id').val(),
                null
            );
        }
    });

    $('div.view_modal').on('show.bs.modal', function() {
        __currency_convert_recursively($(this));
    });

    $('table#pos_table').on('change', 'select.sub_unit', function() {
        var tr = $(this).closest('tr');
        var base_unit_selling_price = tr.find('input.hidden_base_unit_sell_price').val();

        var selected_option = $(this).find(':selected');

        var multiplier = parseFloat(selected_option.data('multiplier'));

        var allow_decimal = parseInt(selected_option.data('allow_decimal'));

        tr.find('input.base_unit_multiplier').val(multiplier);

        var unit_sp = base_unit_selling_price * multiplier;

        var sp_element = pos_row_field(tr, 'input.pos_unit_price');
        pos_set_row_list_price(tr, unit_sp);
        window.__posPriceLogicLock = true;
        try {
            __write_number(sp_element, unit_sp);
        } finally {
            window.__posPriceLogicLock = false;
        }

        sp_element.change();

        var qty_element = tr.find('input.pos_quantity');
        var base_max_avlbl = qty_element.data('qty_available');
        var error_msg_line = 'pos_max_qty_error';

        if (tr.find('select.lot_number').length > 0) {
            var lot_select = tr.find('select.lot_number');
            if (lot_select.val()) {
                base_max_avlbl = lot_select.find(':selected').data('qty_available');
                error_msg_line = 'lot_max_qty_error';
            }
        }

        qty_element.attr('data-decimal', allow_decimal);
        var abs_digit = true;
        if (allow_decimal) {
            abs_digit = false;
        }
        qty_element.rules('add', {
            abs_digit: abs_digit,
        });

        if (base_max_avlbl) {
            var max_avlbl = parseFloat(base_max_avlbl) / multiplier;
            var formated_max_avlbl = __number_f(max_avlbl);
            var unit_name = selected_option.data('unit_name');
            var max_err_msg = __translate(error_msg_line, {
                max_val: formated_max_avlbl,
                unit_name: unit_name,
            });
            qty_element.attr('data-rule-max-value', max_avlbl);
            qty_element.attr('data-msg-max-value', max_err_msg);
            qty_element.rules('add', {
                'max-value': max_avlbl,
                messages: {
                    'max-value': max_err_msg,
                },
            });
            qty_element.trigger('change');
        }
        adjustComboQty(tr);
    });

    //Confirmation before page load.
    window.onbeforeunload = function() {
        if($('form#edit_pos_sell_form').length == 0){
            if($('table#pos_table tbody tr').length > 0) {
                return LANG.sure;
            } else {
                return null;
            }
        }
    }
    $(window).resize(function() {
        var win_height = $(window).height();
        div_height = __calculate_amount('percentage', 63, win_height);
        // $('div.pos_product_div').css('min-height', div_height + 'px');
        // $('div.pos_product_div').css('max-height', div_height + 'px');
    });

    //Used for weighing scale barcode
    $('#weighing_scale_modal').on('shown.bs.modal', function (e) {

        //Attach the scan event
        onScan.attachTo(document, {
            suffixKeyCodes: [13], // enter-key expected at the end of a scan
            reactToPaste: true, // Compatibility to built-in scanners in paste-mode (as opposed to keyboard-mode)
            onScan: function(sCode, iQty) {
                console.log('Scanned: ' + iQty + 'x ' + sCode); 
                $('input#weighing_scale_barcode').val(sCode);
                $('button#weighing_scale_submit').trigger('click');
            },
            onScanError: function(oDebug) {
                console.log(oDebug); 
            },
            minLength: 2
            // onKeyDetect: function(iKeyCode){ // output all potentially relevant key events - great for debugging!
            //     console.log('Pressed: ' + iKeyCode);
            // }
        });

        $('input#weighing_scale_barcode').focus();
    });

    $('#weighing_scale_modal').on('hide.bs.modal', function (e) {
        //Detach from the document once modal is closed.
        onScan.detachFrom(document);
    });

    $('button#weighing_scale_submit').click(function(){

        var price_group = '';
        if ($('#price_group').length > 0) {
            price_group = $('#price_group').val();
        }

        if($('#weighing_scale_barcode').val().length > 0){
            pos_product_row(null, null, $('#weighing_scale_barcode').val());
            $('#weighing_scale_modal').modal('hide');
            $('input#weighing_scale_barcode').val('');
        } else{
            $('input#weighing_scale_barcode').focus();
        }
    });

    $(document).on('click', '#show_featured_products', function(){
        var $root = $(this).closest('.pos-sidebar-root');
        var $box = $root.length ? $root.find('#featured_products_box') : $('#featured_products_box');
        var emptyHtml = ($root.length ? $root.find('#featured_empty_state_template') : $('#featured_empty_state_template')).html() || '';
        if ($.trim($box.html()) === '') {
            $box.html(emptyHtml);
        }
        if (!$box.is(':visible')) {
            $box.css('display', 'flex').hide().fadeIn();
        } else {
            $box.fadeOut();
        }
    });
    validate_discount_field();
    set_payment_type_dropdown();
    if ($('#__is_mobile').length) {
        $('.pos_form_totals').css('margin-bottom', $('.pos-form-actions').height() - 30);
    }

    setInterval(function () {
        if ($('span.curr_datetime').length) {
            $('span.curr_datetime').html(__current_datetime());
        }
    }, 60000);

    set_search_fields();
});

function set_payment_type_dropdown() {
    var payment_settings = $('#location_id').data('default_payment_accounts');
    payment_settings = payment_settings ? payment_settings : [];
    enabled_payment_types = [];
    for (var key in payment_settings) {
        if (payment_settings[key] && payment_settings[key]['is_enabled']) {
            enabled_payment_types.push(key);
        }
    }
    if (enabled_payment_types.length) {
        $(".payment_types_dropdown > option").each(function() {
            //skip if advance
            if ($(this).val() && $(this).val() != 'advance') {
                if (enabled_payment_types.indexOf($(this).val()) != -1) {
                    $(this).removeClass('hide');
                } else {
                    $(this).addClass('hide');
                }
            }
        });
    }
}

function get_featured_products() {
    // The Featured Products button is always visible; when there are no featured products
    // the box is populated with the empty-state template so the click still shows a message.
    var $box = $('#featured_products_box');
    if ($box.length === 0) {
        return;
    }
    var emptyHtml = $('#featured_empty_state_template').html() || '';
    var location_id = $('#location_id').val();
    if (location_id) {
        $.ajax({
            method: 'GET',
            url: '/sells/pos/get-featured-products/' + location_id,
            dataType: 'html',
            success: function(result) {
                $box.html(result ? result : emptyHtml);
            },
            error: function() {
                $box.html(emptyHtml);
            },
        });
    } else {
        $box.html(emptyHtml);
    }
}

var pos_product_list_loading = false;
var pos_product_list_exhausted = false;
var pos_live_stock_timer = null;
var pos_live_stock_requesting = false;

/** Cart qty by variation_id */
function posGetCartQtyByVariation() {
    var qtyMap = {};
    $('table#pos_table tbody tr.product_row').each(function () {
        var vid = String($(this).find('input.row_variation_id').val() || '');
        if (!vid) {
            return;
        }
        var qty = __read_number($(this).find('input.pos_quantity')) || 0;
        qtyMap[vid] = (qtyMap[vid] || 0) + qty;
    });
    return qtyMap;
}

/** Update tile stock text/class from data-base-qty minus cart qty */
function posSyncProductStockDisplay() {
    var qtyMap = posGetCartQtyByVariation();
    var allowOversell = $('input#is_overselling_allowed').length > 0;

    $('div.product_box[data-variation_id]').each(function () {
        var $box = $(this);
        if (String($box.data('enable-stock')) !== '1') {
            return;
        }
        var vid = String($box.data('variation_id'));
        var baseQty = parseFloat($box.attr('data-base-qty'));
        if (isNaN(baseQty)) {
            baseQty = parseFloat($box.data('base-qty')) || 0;
        }
        var remaining = baseQty - (qtyMap[vid] || 0);
        if (remaining < 0 && !allowOversell) {
            remaining = 0;
        }
        var unit = $box.data('unit') || $box.find('.product_stock').data('stock-label') || 'Pc(s)';
        var $stock = $box.find('.product_stock');
        if ($stock.length) {
            $stock.text(Math.round(remaining) + ' ' + unit + ' in stock');
        }
        if (remaining <= 0 && !allowOversell) {
            $box.addClass('product_out_of_stock tw-cursor-not-allowed').removeClass('tw-cursor-pointer');
        } else {
            $box.removeClass('product_out_of_stock tw-cursor-not-allowed').addClass('tw-cursor-pointer');
        }
    });
}

/** Apply server stock values onto tiles, then re-apply cart reservation */
function posApplyLiveStock(stocks) {
    if (!stocks) {
        return;
    }
    Object.keys(stocks).forEach(function (vid) {
        var info = stocks[vid];
        var $boxes = $('div.product_box[data-variation_id="' + vid + '"]');
        $boxes.each(function () {
            var $box = $(this);
            $box.attr('data-enable-stock', info.enable_stock ? 1 : 0);
            $box.data('enable-stock', info.enable_stock ? 1 : 0);
            $box.attr('data-base-qty', info.qty_available);
            $box.data('base-qty', info.qty_available);
        });
    });
    posSyncProductStockDisplay();
}

/** Fetch live stock for currently visible product tiles (silent — no loader / no grid reload) */
function posFetchLiveStock() {
    if (pos_live_stock_requesting) {
        return;
    }
    var location_id = $('input#location_id').val();
    if (!location_id) {
        return;
    }
    var ids = [];
    $('div.product_box[data-variation_id]').each(function () {
        var id = $(this).data('variation_id');
        if (id && ids.indexOf(id) === -1) {
            ids.push(id);
        }
    });
    if (!ids.length) {
        return;
    }
    pos_live_stock_requesting = true;
    var runSilent = function () {
        $.ajax({
            method: 'GET',
            url: '/sells/pos/get-live-stock',
            data: {
                location_id: location_id,
                variation_ids: ids,
            },
            dataType: 'json',
            global: false, // skip Pace top spinner / ajaxStart
            success: function (res) {
                if (res && res.stocks) {
                    posApplyLiveStock(res.stocks);
                }
            },
            complete: function () {
                pos_live_stock_requesting = false;
            },
        });
    };
    // Also hide from Pace XHR tracker (same spinner)
    if (typeof Pace !== 'undefined' && Pace && typeof Pace.ignore === 'function') {
        Pace.ignore(runSilent);
    } else {
        runSilent();
    }
}

function startPosLiveStockPolling() {
    if (pos_live_stock_timer) {
        clearInterval(pos_live_stock_timer);
    }
    // Quiet background sync — only updates stock numbers on tiles, never reloads grid
    posFetchLiveStock();
    pos_live_stock_timer = setInterval(function () {
        if (document.hidden) {
            return;
        }
        posFetchLiveStock();
    }, 8000);
    $(document).off('visibilitychange.posLiveStock').on('visibilitychange.posLiveStock', function () {
        if (!document.hidden) {
            posFetchLiveStock();
        }
    });
}

function get_product_suggestion_list(category_id, brand_id, location_id, url = null, is_enabled_stock = null, repair_model_id = null) {
    if($('div#product_list_body').length == 0) {
        return false;
    }

    var $items_container = $('div#product_list_items').length ? $('div#product_list_items') : $('div#product_list_body');
    var page = parseInt($('input#suggestion_page').val(), 10) || 1;

    if (page == 1) {
        pos_product_list_exhausted = false;
    }

    if (pos_product_list_loading) {
        return false;
    }

    if (page > 1 && pos_product_list_exhausted) {
        $('#suggestion_page_loader').removeClass('is-visible').hide();
        return false;
    }

    if (url == null) {
        url = '/sells/pos/get-product-suggestion';
    }

    pos_product_list_loading = true;
    $('#suggestion_page_loader').addClass('is-visible').show().css('display', 'block');

    if (page == 1) {
        $items_container.html('');
    }
    if ($items_container.find('input#no_products_found').length > 0) {
        pos_product_list_exhausted = true;
        pos_product_list_loading = false;
        $('#suggestion_page_loader').removeClass('is-visible').hide();
        return false;
    }
    $.ajax({
        method: 'GET',
        url: url,
        data: {
            category_id: category_id,
            brand_id: brand_id,
            location_id: location_id,
            page: page,
            is_enabled_stock: is_enabled_stock,
            repair_model_id: repair_model_id
        },
        dataType: 'html',
        success: function(result) {
            var html = result || '';
            $items_container.append(html);
            var $parsed = $('<div>').append($.parseHTML(html));
            var productCount = $parsed.find('.product_box').length;
            if (productCount === 0 || $parsed.find('#no_products_found').length > 0) {
                pos_product_list_exhausted = true;
            }
            pos_product_list_loading = false;
            $('#suggestion_page_loader').removeClass('is-visible').hide();
            if (typeof posSyncProductStockDisplay === 'function') {
                posSyncProductStockDisplay();
            }
        },
        error: function() {
            pos_product_list_loading = false;
            $('#suggestion_page_loader').removeClass('is-visible').hide();
            if (page == 1) {
                $items_container.html(
                    '<div class="col-md-12"><h4 class="text-center" style="color:#aaa;">Unable to load products. Please try again.</h4></div>'
                );
            } else {
                pos_product_list_exhausted = true;
            }
        },
        complete: function() {
            pos_product_list_loading = false;
            $('#suggestion_page_loader').removeClass('is-visible').hide();
        },
    });
}

//Get recent transactions
function get_recent_transactions(status, element_obj) {
    if (element_obj.length == 0) {
        return false;
    }
    var transaction_sub_type = $("#transaction_sub_type").val();
    var location_id = $('input#location_id').val() || $('#location_id').val() || '';
    $.ajax({
        method: 'GET',
        url: '/sells/pos/get-recent-transactions',
        data: {
            status: status,
            transaction_sub_type: transaction_sub_type || '',
            location_id: location_id,
            limit: 50,
        },
        dataType: 'html',
        success: function(result) {
            element_obj.html(result);
            __currency_convert_recursively(element_obj);
        },
        error: function() {
            element_obj.html('<p class="text-danger">Unable to load recent transactions.</p>');
        },
    });
}

function setPosHeaderCountBadge($badge, count) {
    if (!$badge || !$badge.length) {
        return;
    }
    var n = parseInt(count, 10) || 0;
    $badge.text(n).toggleClass('tw-hidden', n === 0);
}

function refreshPosSuspendedCount() {
    var $btn = $('#view_suspended_sales');
    var $badge = $('#pos_suspended_count');
    if (!$btn.length || !$badge.length) {
        return;
    }

    var href = $btn.data('href') || $btn.attr('data-href');
    if (!href) {
        return;
    }

    $.ajax({
        method: 'GET',
        url: href,
        dataType: 'html',
        success: function(result) {
            var count = $(result).find('.pos-retrieve-row').length;
            setPosHeaderCountBadge($badge, count);
            $btn.toggleClass('pos-tb-btn--has-items', count > 0);
        },
    });
}
window.refreshPosSuspendedCount = refreshPosSuspendedCount;

function loadPosDraftsPanel(status) {
    var $list = $('#pos_drafts_list');
    if (!$list.length) {
        return;
    }

    status = status || $list.data('status') || 'draft';
    $list.data('status', status);

    var transaction_sub_type = $('#transaction_sub_type').val();
    var location_id = $('#location_id').val();

    $.ajax({
        method: 'GET',
        url: '/sells/pos/get-recent-transactions',
        data: {
            status: status,
            transaction_sub_type: transaction_sub_type,
            location_id: location_id,
            layout: 'panel',
            limit: 20,
        },
        dataType: 'html',
        success: function(result) {
            $list.html(result);
            __currency_convert_recursively($list);

            var count = $list.find('.pos-draft-card').length;
            if (status === 'suspended') {
                setPosHeaderCountBadge($('#pos_suspended_count'), count);
                $('#view_suspended_sales').toggleClass('pos-tb-btn--has-items', count > 0);
            } else {
                setPosHeaderCountBadge($('#pos_drafts_count'), count);
                $('#pos_drafts_header_toggle').toggleClass('pos-drafts-header-btn--has-items', count > 0);
            }
        },
    });
}

function openPosDraftsDropdown() {
    var $panel = $('#pos_drafts_panel');
    if (!$panel.length) {
        return;
    }

    loadPosDraftsPanel('draft');
    $panel.addClass('pos-drafts-dropdown--open').attr('aria-hidden', 'false');
}

function closePosDraftsDropdown() {
    $('#pos_drafts_panel').removeClass('pos-drafts-dropdown--open').attr('aria-hidden', 'true');
}

$(document).ready(function() {
    if ($('#pos_drafts_panel').length) {
        loadPosDraftsPanel('draft');
        // After converting parked sale → draft on edit, land here with list open
        if (/(?:^|[?&])open_drafts=1(?:&|$)/.test(window.location.search || '')) {
            setTimeout(function() {
                openPosDraftsDropdown();
            }, 200);
            if (window.history && window.history.replaceState) {
                var cleanUrl = window.location.pathname + (window.location.hash || '');
                window.history.replaceState({}, document.title, cleanUrl);
            }
        }
    }
    refreshPosSuspendedCount();

    $(document).off('click.posDraftsToggle', '#pos_drafts_header_toggle')
        .on('click.posDraftsToggle', '#pos_drafts_header_toggle', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var $panel = $('#pos_drafts_panel');
        if (!$panel.length) {
            return;
        }
        if ($panel.hasClass('pos-drafts-dropdown--open')) {
            closePosDraftsDropdown();
        } else {
            openPosDraftsDropdown();
        }
    });

    $(document).off('click.posDraftsClose', '#pos_drafts_close')
        .on('click.posDraftsClose', '#pos_drafts_close', function(e) {
        e.preventDefault();
        e.stopPropagation();
        closePosDraftsDropdown();
    });

    $(document).off('click.posDraftsOutside').on('click.posDraftsOutside', function(e) {
        if (!$(e.target).closest('#pos_drafts_dropdown_wrap').length) {
            closePosDraftsDropdown();
        }
    });

    $(document).on('click', '.pos-drafts-panel__tab', function(e) {
        e.preventDefault();
        var status = $(this).data('status');
        $('.pos-drafts-panel__tab').removeClass('pos-drafts-panel__tab--active');
        $(this).addClass('pos-drafts-panel__tab--active');
        loadPosDraftsPanel(status);
    });

    $(document).off('click.posDraftsRefresh', '#pos_drafts_refresh')
        .on('click.posDraftsRefresh', '#pos_drafts_refresh', function(e) {
        e.preventDefault();
        e.stopPropagation();
        loadPosDraftsPanel();
    });

    $(document).on('click', '.pos-draft-card__btn--resume', function() {
        closePosDraftsDropdown();
    });

    $('#select_location_id, #location_id').on('change', function() {
        if ($('#pos_drafts_panel').length) {
            loadPosDraftsPanel();
        }
        refreshPosSuspendedCount();
    });
});

//variation_id is null when weighing_scale_barcode is used.

/**
 * Common function to insert product row into POS table
 * @param {object} result - The result object containing html_content and other data
 */
function pos_insert_product_row(result) {
    pos_play_success_sound();
    pos_close_all_line_edits();
    var product_row = $('input#product_row_count').val();
    // Newest scanned/selected line stays in the first cart row (create + edit)
    var $tmp = $('<table></table>').append($.parseHTML($.trim(result.html_content), document, true));
    var $lineBody = $tmp.children('tbody.pos-cart-line');
    if (!$lineBody.length) {
        $lineBody = $tmp.find('tbody.pos-cart-line');
    }
    if ($lineBody.length) {
        pos_bring_product_row_to_top($lineBody);
    } else {
        var $newRows = $tmp.find('tr');
        pos_bring_product_row_to_top($newRows);
    }
    pos_reattach_all_line_edits();
    pos_sync_empty_state();
    //increment row count
    $('input#product_row_count').val(parseInt(product_row) + 1);
    var this_row = $('#pos_table tr.product_row').first();
    pos_ensure_row_list_price(this_row);
    pos_each_row(this_row);

    //For initial discount if present
    var line_total = __read_number(this_row.find('input.pos_line_total'));
    this_row.find('span.pos_line_total_text').text(line_total);

    pos_total_row();

    //Check if multipler is present then multiply it when a new row is added.
    if(__getUnitMultiplier(this_row) > 1){
        this_row.find('select.sub_unit').trigger('change');
    }

    round_row_to_iraqi_dinnar(this_row);
    __currency_convert_recursively(this_row);

    if (!$('#__is_mobile').length) {
        $('input#search_product')
            .focus()
            .select();
    }

    //Used in restaurant module
    if (result.html_modifier) {
        this_row.find('td:first').append(result.html_modifier);
    }

    // Keep last-scanned line visible at top of cart
    $(".pos_product_div").animate({ scrollTop: 0 }, 250);
}

// Helper function to add product row from server data
function pos_add_product_row_from_data(result) {
    if (result.success) {
        var variation_id = result.variation_id;
        var add_new_row = true;
        
        // Check item addition method setting
        var item_addtn_method = 0;
        if ($('#item_addition_method').length) {
            item_addtn_method = $('#item_addition_method').val();
        }
        
        // If item_addtn_method != 0, check for duplicate products
        if (item_addtn_method != 0 && variation_id) {
            var is_added = false;
            
            // Search for variation id in each row of pos table
            $('#pos_table')
                .find('tr.product_row')
                .each(function() {
                    var row_v_id = $(this).find('.row_variation_id').val();
                    var enable_sr_no = $(this).find('.enable_sr_no').val();
                    var modifiers_exist = false;
                    if ($(this).find('input.modifiers_exist').length > 0) {
                        modifiers_exist = true;
                    }
                    
                    if (
                        row_v_id == variation_id &&
                        enable_sr_no !== '1' &&
                        !modifiers_exist &&
                        !is_added
                    ) {
                        add_new_row = false;
                        is_added = true;
                        
                        // Increment product quantity
                        var qty_element = $(this).find('.pos_quantity');
                        var qty = __read_number(qty_element);
                        __write_number(qty_element, qty + 1);
                        qty_element.change();
                        
                        round_row_to_iraqi_dinnar($(this));
                        // Re-scan existing line → move to first row
                        pos_bring_product_row_to_top($(this));
                        $(".pos_product_div").animate({ scrollTop: 0 }, 250);
                        
                        if (!$('#__is_mobile').length) {
                            $('input#search_product')
                                .focus()
                                .select();
                        }
                    }
            });
        }
        
        // Add new row if not a duplicate
        if (add_new_row) {
            pos_insert_product_row(result);
        }
    } else {
        toastr.error(result.msg);
        if (!$('#__is_mobile').length) {
            $('input#search_product')
                .focus()
                .select();
        }
    }
}

function pos_product_row(variation_id = null, purchase_line_id = null, weighing_scale_barcode = null, quantity = 1, sub_unit_id = null) {

    //Get item addition method
    var item_addtn_method = 0;
    var add_via_ajax = true;

    if (variation_id != null && $('#item_addition_method').length) {
        item_addtn_method = $('#item_addition_method').val();
    }

    if (item_addtn_method == 0) {
        add_via_ajax = true;
    } else {
        var is_added = false;

        //Search for variation id in each row of pos table
        $('#pos_table')
            .find('tr.product_row')
            .each(function() {
                var row_v_id = $(this)
                    .find('.row_variation_id')
                    .val();
                var enable_sr_no = $(this)
                    .find('.enable_sr_no')
                    .val();
                var modifiers_exist = false;
                if ($(this).find('input.modifiers_exist').length > 0) {
                    modifiers_exist = true;
                }
                var row_sub_unit = $(this).find('select.sub_unit').val()
                    || $(this).find('input[name*="[sub_unit_id]"]').val()
                    || null;
                var unit_matches = !sub_unit_id || String(row_sub_unit || '') === String(sub_unit_id);

                if (
                    row_v_id == variation_id &&
                    enable_sr_no !== '1' &&
                    !modifiers_exist &&
                    unit_matches &&
                    !is_added
                ) {
                    add_via_ajax = false;
                    is_added = true;

                    // Increment product quantity (supports multi-qty barcode / repeated scan)
                    qty_element = $(this).find('.pos_quantity');
                    var qty = __read_number(qty_element);
                    var add_qty = parseFloat(quantity);
                    if (isNaN(add_qty) || add_qty < 1) {
                        add_qty = 1;
                    }
                    __write_number(qty_element, qty + add_qty);
                    qty_element.change();

                    round_row_to_iraqi_dinnar($(this));
                    // Last touched line stays first in the cart
                    pos_bring_product_row_to_top($(this));
                    $(".pos_product_div").animate({ scrollTop: 0 }, 250);

                    if (!$('#__is_mobile').length) {
                        $('input#search_product')
                            .focus()
                            .select();
                    }
                }
        });
    }

    if (add_via_ajax) {
        var product_row = $('input#product_row_count').val();
        var location_id = $('input#location_id').val();
        var customer_id = $('select#customer_id').val();
        var is_direct_sell = false;
        if (
            $('input[name="is_direct_sale"]').length > 0 &&
            $('input[name="is_direct_sale"]').val() == 1
        ) {
            is_direct_sell = true;
        }

        var disable_qty_alert = false;

        if ($('#disable_qty_alert').length) {
            disable_qty_alert = true;
        }

        var is_sales_order = $('#sale_type').length && $('#sale_type').val() == 'sales_order' ? true : false;

        var price_group = '';
        if ($('#price_group').length > 0) {
            price_group = parseInt($('#price_group').val());
        }

        //If default price group present
        if ($('#default_price_group').length > 0 && 
            price_group === '') {
            price_group = $('#default_price_group').val();
        }

        //If types of service selected give more priority
        if ($('#types_of_service_price_group').length > 0 && 
            $('#types_of_service_price_group').val()) {
            price_group = $('#types_of_service_price_group').val();
        }

        var is_draft=false;
        if($('#status') && ($('#status').val()=='quotation' || 
        $('#status').val()=='draft')) {
            is_draft=true;
        }

        var is_serial_no = false;

        if (
            $('input[name="is_serial_no"]').length > 0 &&
            $('input[name="is_serial_no"]').val() == 1
        ) {
            is_serial_no = true;
        }
        
        $.ajax({
            method: 'GET',
            url: '/sells/pos/get_product_row/' + variation_id + '/' + location_id,
            async: false,
            data: {
                product_row: product_row,
                customer_id: customer_id,
                is_direct_sell: is_direct_sell,
                is_serial_no: is_serial_no,
                price_group: price_group,
                purchase_line_id: purchase_line_id,
                weighing_scale_barcode: weighing_scale_barcode,
                quantity: quantity,
                is_sales_order: is_sales_order,
                disable_qty_alert: disable_qty_alert,
                is_draft: is_draft,
                sub_unit_id: sub_unit_id || null
            },
            dataType: 'json',
            success: function(result) {
                if (result.success) {
                    if (result.sku_inactivity && result.sku_inactivity.prompt) {
                        pos_handle_sku_inactivity(result.sku_inactivity, function () {
                            pos_insert_product_row(result);
                        });
                    } else {
                        pos_insert_product_row(result);
                    }
                } else {
                    toastr.error(result.msg);
                    if (!$('#__is_mobile').length) {
                        $('input#search_product')
                            .focus()
                            .select();
                    }
                }
            },
        });
    }
}

function pos_handle_sku_inactivity(info, onContinue) {
    var msg = (typeof LANG !== 'undefined' && LANG.sku_inactivity_prompt)
        ? LANG.sku_inactivity_prompt
        : 'SKU :sku (:name) has not been scanned in :days days. Retain it or move it to the archive?';
    msg = msg
        .replace(':sku', info.sku || '')
        .replace(':name', info.product_name || '')
        .replace(':days', info.days || 180);

    var retainLabel = (typeof LANG !== 'undefined' && LANG.retain_sku) ? LANG.retain_sku : 'Retain';
    var archiveLabel = (typeof LANG !== 'undefined' && LANG.archive_sku) ? LANG.archive_sku : 'Archive';

    swal({
        title: msg,
        icon: 'warning',
        buttons: {
            retain: { text: retainLabel, value: 'retain', className: 'bg-green' },
            archive: { text: archiveLabel, value: 'archive', className: 'bg-red' },
            cancel: true
        },
        dangerMode: true
    }).then(function (value) {
        if (value === 'retain') {
            $.ajax({
                method: 'POST',
                url: '/products/' + info.product_id + '/retain-sku',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        toastr.success(res.msg);
                    } else {
                        toastr.error(res.msg);
                    }
                    if (typeof onContinue === 'function') {
                        onContinue();
                    }
                },
                error: function () {
                    if (typeof onContinue === 'function') {
                        onContinue();
                    }
                }
            });
        } else if (value === 'archive') {
            $.ajax({
                method: 'POST',
                url: '/products/' + info.product_id + '/archive-sku',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        toastr.success(res.msg);
                    } else {
                        toastr.error(res.msg);
                        if (typeof onContinue === 'function') {
                            onContinue();
                        }
                    }
                },
                error: function () {
                    toastr.error('Something went wrong');
                }
            });
        } else if (typeof onContinue === 'function') {
            // Cancel: still allow the scanned line for this sale, clock already updated.
            onContinue();
        }
    });
}

//Update values for each row (incl. item discount from cart item popup)
function pos_each_row(row_obj) {
    var discounted_unit_price = calculate_discounted_unit_price(row_obj);
    var tax_rate = pos_row_field(row_obj, 'select.tax_id')
        .find(':selected')
        .data('rate');

    var unit_price_inc_tax =
        discounted_unit_price + __calculate_amount('percentage', tax_rate, discounted_unit_price);
    __write_number(row_obj.find('input.pos_unit_price_inc_tax'), unit_price_inc_tax);

    // Always refresh line total so item popup discount reflects immediately (even when cleared to 0)
    var qty = __read_number(row_obj.find('input.pos_quantity'));
    var line_total = qty * unit_price_inc_tax;
    __write_number(row_obj.find('input.pos_line_total'), line_total);
    row_obj.find('span.pos_line_total_text').text(__currency_trans_from_en(line_total, true));
    row_obj.find('span.pos_line_unit_price_text').text(__currency_trans_from_en(unit_price_inc_tax, true));
    if (typeof pos_update_line_price_compare_display === 'function') {
        pos_update_line_price_compare_display(row_obj);
    }

    __write_number(pos_row_field(row_obj, 'input.item_tax'), unit_price_inc_tax - discounted_unit_price);
}

function pos_total_row() {
    var total_quantity = 0;
    var price_total = get_subtotal();
    $('table#pos_table tbody tr').each(function() {
        total_quantity = total_quantity + __read_number($(this).find('input.pos_quantity'));
    });

    // Updating shipping charges display (use saved form field, fall back to modal)
    var shipping_charges_val = 0;
    if ($('input#shipping_charges').length) {
        shipping_charges_val = __read_number($('input#shipping_charges'));
    } else if ($('input#shipping_charges_modal').length) {
        shipping_charges_val = __read_number($('input#shipping_charges_modal'));
    }
    if (isNaN(shipping_charges_val)) {
        shipping_charges_val = 0;
    }
    $('span#shipping_charges_amount, span.payment_od_shipping').text(__currency_trans_from_en(shipping_charges_val, false));

    $('span.total_quantity').each(function() {
        $(this).html(__number_f(total_quantity));
    });

    //$('span.unit_price_total').html(unit_price_total);
    $('span.price_total').html(__currency_trans_from_en(price_total, false));
    update_payment_od_item_discount();
    calculate_billing_details(price_total);

    if (
        $('input[name="is_serial_no"]').length > 0 &&
        $('input[name="is_serial_no"]').val() == 1
    ) {
        update_serial_no();
    }
    // store on any update
    saveFormDataToLocalStorage();

    // Keep product-tile stock in sync with cart (no page reload)
    if (typeof posSyncProductStockDisplay === 'function') {
        posSyncProductStockDisplay();
    }
}

function get_subtotal() {
    var price_total = 0;

    $('table#pos_table tbody tr').each(function() {
        price_total = price_total + __read_number($(this).find('input.pos_line_total'));
    });

    //Go through the modifier prices.
    $('input.modifiers_price').each(function() {
        var modifier_price = __read_number($(this));
        var modifier_quantity = $(this).closest('.product_modifier').find('.modifiers_quantity').val();
        var modifier_subtotal = modifier_price * modifier_quantity;
        price_total = price_total + modifier_subtotal;
    });

    return price_total;
}

/** Sum of per-line (item) discounts across the cart (qty × unit discount). */
function get_item_discount_total() {
    var item_discount_total = 0;
    $('table#pos_table tbody tr').each(function() {
        var $row = $(this);
        if (!$row.find('input.pos_quantity').length) {
            return;
        }
        var unit_price = __read_number(pos_row_field($row, 'input.pos_unit_price'));
        if (isNaN(unit_price)) {
            unit_price = 0;
        }
        var qty = __read_number($row.find('input.pos_quantity'));
        if (isNaN(qty) || qty === 0) {
            return;
        }
        var row_discount_type = pos_row_field($row, 'select.row_discount_type').val();
        var row_discount_amount = __read_number(pos_row_field($row, 'input.row_discount_amount'));
        if (!row_discount_amount || isNaN(row_discount_amount)) {
            return;
        }
        var unit_discount = 0;
        if (row_discount_type == 'fixed') {
            unit_discount = row_discount_amount;
        } else {
            unit_discount = __calculate_amount('percentage', row_discount_amount, unit_price);
        }
        if (!isNaN(unit_discount) && unit_discount > 0) {
            item_discount_total += unit_discount * qty;
        }
    });
    return item_discount_total;
}

function update_payment_od_item_discount() {
    var item_disc = get_item_discount_total();
    var formatted = __currency_trans_from_en(item_disc, false);
    $('span.payment_od_item_discount').text(formatted);
    $('span#total_line_item_dc').text(formatted);
}

function calculate_billing_details(price_total) {
    var discount = pos_discount(price_total);
    if ($('#reward_point_enabled').length) {
        total_customer_reward = $('#rp_redeemed_amount').val();
        discount = parseFloat(discount) + parseFloat(total_customer_reward);

        // Discount cell shows pure discount (set by pos_discount()); loyalty cell shows reward amount separately
        if ($('input[name="is_direct_sale"]').length <= 0) {
            $('span#loyalty_amount_display').text(__currency_trans_from_en(total_customer_reward, false));
        }
    }

    var order_tax = pos_order_tax(price_total, discount);

    //Add shipping charges.
    var shipping_charges = __read_number($('input#shipping_charges'));

    var additional_expense = 0;
    //calculate additional expenses
    if ($('input#additional_expense_value_1').length > 0) {
        additional_expense += __read_number($('input#additional_expense_value_1'));
    }
    if ($('input#additional_expense_value_2').length > 0) {
        additional_expense += __read_number($('input#additional_expense_value_2'))
    }
    if ($('input#additional_expense_value_3').length > 0) {
        additional_expense += __read_number($('input#additional_expense_value_3'))
    }
    if ($('input#additional_expense_value_4').length > 0) {
        additional_expense += __read_number($('input#additional_expense_value_4'))
    }

    //Add packaging charge
    var packing_charge = 0;
    if ($('#types_of_service_id').length > 0 && 
            $('#types_of_service_id').val()) {
        packing_charge = __calculate_amount($('#packing_charge_type').val(), 
            __read_number($('input#packing_charge')), price_total);

        $('#packing_charge_text, span.payment_od_packing').text(__currency_trans_from_en(packing_charge, false));
        if (packing_charge > 0) {
            $('#modal_payment .payment_od_packing_wrap').removeClass('hide');
        }
    } else {
        $('span.payment_od_packing').text(__currency_trans_from_en(0, false));
    }

    var total_payable = price_total + order_tax - discount + shipping_charges + packing_charge + additional_expense;

    var rounding_multiple = $('#amount_rounding_method').val() ? parseFloat($('#amount_rounding_method').val()) : 0;
    var round_off_data = __round(total_payable, rounding_multiple);
    var total_payable_rounded = round_off_data.number;

    var round_off_amount = round_off_data.diff;
    if (round_off_amount != 0) {
        $('span#round_off_text').text(__currency_trans_from_en(round_off_amount, false));
    } else {
        $('span#round_off_text').text(0);
    }
    $('input#round_off_amount').val(round_off_amount);

    __write_number($('input#final_total_input'), total_payable_rounded);
    var curr_exchange_rate = 1;
    if ($('#exchange_rate').length > 0 && $('#exchange_rate').val()) {
        curr_exchange_rate = __read_number($('#exchange_rate'));
    }
    var shown_total = total_payable_rounded * curr_exchange_rate;
    $('span#total_payable').text(__currency_trans_from_en(shown_total, false));

    $('span.total_payable_span').text(__currency_trans_from_en(total_payable_rounded, true));

    // Create: 1 row = full payable; multi-row keep 1st/earlier, update last only.
    // Edit: fill first when empty/zero.
    var $payAmounts = $('#payment_rows_div').find('.payment-amount');
    if ($payAmounts.length) {
        var is_edit_form =
            $('form#edit_pos_sell_form').length > 0 || $('form#edit_sell_form').length > 0;
        if (!is_edit_form) {
            if (typeof pos_apply_payable_to_payment_rows === 'function') {
                pos_apply_payable_to_payment_rows(total_payable_rounded);
            } else if ($payAmounts.length === 1) {
                __write_number($payAmounts.first(), total_payable_rounded);
            }
        } else {
            var cur_pay = __read_number($payAmounts.first());
            if (isNaN(cur_pay) || cur_pay <= 0) {
                __write_number($payAmounts.first(), total_payable_rounded);
            }
        }
    }

    $(document).trigger('invoice_total_calculated');

    calculate_balance_due();
}

function pos_discount(total_amount) {
    var calculation_type = $('#discount_type').val();
    var calculation_amount = __read_number($('#discount_amount'));

    var discount = __calculate_amount(calculation_type, calculation_amount, total_amount);

    $('span#total_discount, span.payment_od_discount').text(__currency_trans_from_en(discount, false));

    return discount;
}

function pos_order_tax(price_total, discount) {
    var tax_rate_id = $('#tax_rate_id').val();
    var calculation_type = 'percentage';
    var calculation_amount = __read_number($('#tax_calculation_amount'));
    var total_amount = price_total - discount;

    if (tax_rate_id) {
        var order_tax = __calculate_amount(calculation_type, calculation_amount, total_amount);
    } else {
        var order_tax = 0;
    }

    $('span#order_tax, span.payment_od_tax').text(__currency_trans_from_en(order_tax, false));

    return order_tax;
}

function calculate_balance_due() {
    var total_payable = __read_number($('#final_total_input'));
    if (isNaN(total_payable)) {
        total_payable = 0;
    }

    var total_paying = 0;
    $('#payment_rows_div')
        .find('.payment-amount')
        .each(function() {
            // Always use unformatted number (handles "1,234.56" / "0.50");
            // previously parseFloat(val) skipped some valid amounts (e.g. ",50" style).
            var amount = __read_number($(this));
            if (!isNaN(amount) && amount !== 0) {
                total_paying += amount;
            }
        });

    // Round to business currency precision to avoid float noise (e.g. 13.199999)
    var precision = typeof __currency_precision !== 'undefined' ? __currency_precision : 2;
    var factor = Math.pow(10, precision);
    total_payable = Math.round(total_payable * factor) / factor;
    total_paying = Math.round(total_paying * factor) / factor;

    var difference = Math.round((total_payable - total_paying) * factor) / factor;
    var change_return = 0;
    // Remaining amount customer still owes (always >= 0 after applying tender)
    var bal_due = 0;

    if (difference > 0) {
        // Underpaid — show remaining balance due
        bal_due = difference;
        change_return = 0;
    } else if (difference < 0) {
        // Overpaid — change goes back to customer; sale balance is settled
        change_return = Math.abs(difference);
        bal_due = 0;
    } else {
        bal_due = 0;
        change_return = 0;
    }

    if (change_return > 0) {
        __write_number($('input#change_return'), change_return);
        $('span.change_return_span').text(__currency_trans_from_en(change_return, true));
        $('#change_return_payment_data').removeClass('hide');
    } else {
        __write_number($('input#change_return'), 0);
        $('span.change_return_span').text(__currency_trans_from_en(0, true));
        $('#change_return_payment_data').addClass('hide');
    }

    __write_number($('input#total_paying_input'), total_paying);
    $('span.total_paying').text(__currency_trans_from_en(total_paying, true));

    // Store raw numeric remaining due (for submit validation / localStorage)
    // Use plain val so comparison in_balance_due >= 0.5 is reliable
    $('input#in_balance_due').val(bal_due);
    $('span.balance_due').text(__currency_trans_from_en(bal_due, true));

    // Highlight: underpayment (due) = danger, fully paid / over = success
    var $bal = $('span.balance_due');
    $bal.removeClass('text-success text-danger');
    if (bal_due > 0) {
        $bal.addClass('text-danger');
    } else if (change_return > 0 || total_paying > 0) {
        $bal.addClass('text-success');
    }

    // Return amount always shown (incl. $0 / non-cash); highlight for overpay
    __highlight(change_return * -1, $('span.change_return_span'));
    $('span.change_return_span').addClass('text-danger');

    // store payment details
    if (typeof saveFormDataToLocalStorage === 'function') {
        saveFormDataToLocalStorage();
    }
    if (typeof pos_update_add_payment_row_btn === 'function') {
        pos_update_add_payment_row_btn();
    }
}

/** Enable/disable Add Payment Row when amount already covers total payable */
function pos_update_add_payment_row_btn() {
    var $btn = $('#add-payment-row');
    if (!$btn.length) {
        return;
    }
    var total_payable = __read_number($('input#final_total_input'));
    if (isNaN(total_payable)) {
        total_payable = 0;
    }
    var total_paying = 0;
    $('#payment_rows_div')
        .find('.payment-amount')
        .each(function () {
            var amount = __read_number($(this));
            if (!isNaN(amount)) {
                total_paying += amount;
            }
        });
    var remaining = total_payable - total_paying;
    if (remaining <= 0.009) {
        $btn.prop('disabled', true).addClass('is-disabled');
        $btn.attr('title', 'Amount already equals or exceeds total payable');
    } else {
        $btn.prop('disabled', false).removeClass('is-disabled');
        $btn.removeAttr('title');
    }
}

function isValidPosForm() {
    flag = true;
    $('span.error').remove();

    if ($('select#customer_id').val() == null) {
        flag = false;
        error = '<span class="error">' + LANG.required + '</span>';
        $(error).insertAfter($('select#customer_id').parent('div'));
    }

    if ($('tr.product_row').length == 0) {
        flag = false;
        error = '<span class="error">' + LANG.no_products + '</span>';
        $(error).insertAfter($('input#search_product').parent('div'));
    }

    return flag;
}

function pos_load_retrieved_sale_into_cart(row) {
    if (!row || typeof jQuery === 'undefined') {
        return false;
    }

    var lines = [];
    try {
        lines = JSON.parse(row.getAttribute('data-lines') || '[]');
    } catch (e) {
        lines = [];
    }

    if (!lines.length) {
        if (typeof toastr !== 'undefined') {
            toastr.error('No items in this parked sale');
        }
        return false;
    }

    if (typeof pos_product_row !== 'function') {
        var href = row.getAttribute('data-href');
        if (href) {
            window.location.href = href;
        }
        return false;
    }

    var doLoad = function () {
        // Clear current cart lines
        $('#pos_table tbody.pos-cart-line').remove();
        $('table#pos_table tbody tr.pos-line-edit-row').remove();
        $('table#pos_table tbody tr.product_row').remove();
        $('input#product_row_count').val(0);
        if (typeof pos_sync_empty_state === 'function') {
            pos_sync_empty_state();
        }

        // Set customer
        var cid = row.getAttribute('data-contact-id');
        var cname = row.getAttribute('data-contact-name') || '';
        if (cid && $('#customer_id').length) {
            if ($('#customer_id').find('option[value="' + cid + '"]').length === 0) {
                $('#customer_id').append(new Option(cname, cid, true, true));
            }
            $('#customer_id').val(cid).trigger('change');
        }

        // Always add as new rows with given qty (not merge-increment)
        var prevMethod = $('#item_addition_method').val();
        $('#item_addition_method').val(0);

        for (var i = 0; i < lines.length; i++) {
            var line = lines[i];
            if (!line || !line.variation_id) {
                continue;
            }
            var qty = parseFloat(line.quantity);
            if (!qty || qty <= 0) {
                qty = 1;
            }
            pos_product_row(line.variation_id, null, null, qty);
        }

        if (typeof prevMethod !== 'undefined' && prevMethod !== null) {
            $('#item_addition_method').val(prevMethod);
        }

        // Bind create form to UPDATE this parked sale (Pay finishes same sale)
        var tid = row.getAttribute('data-transaction-id');
        var $form = $('#add_pos_sell_form');
        if ($form.length && tid) {
            if (!$form.data('original-action')) {
                $form.data('original-action', $form.attr('action'));
            }
            var updateUrl = (window.base_path || '') + '/pos/' + tid;
            $form.attr('action', updateUrl);
            if (!$form.find('input[name="_method"]').length) {
                $form.prepend('<input type="hidden" name="_method" value="PUT">');
            } else {
                $form.find('input[name="_method"]').val('PUT');
            }
            $form.attr('data-retrieved-id', tid);
        }

        if ($('input#is_suspend').length) {
            $('input#is_suspend').val(0);
        }

        if (typeof pos_total_row === 'function') {
            pos_total_row();
        }
        if (typeof pos_sync_empty_state === 'function') {
            pos_sync_empty_state();
        }

        // Close retrieve panel (Retrieve sale link)
        var panel = document.getElementById('pos_retrieve_panel');
        var link = document.getElementById('view_suspended_sales_link');
        if (panel) {
            panel.classList.remove('is-open');
            panel.setAttribute('aria-hidden', 'true');
        }
        if (link) {
            link.classList.remove('is-active');
            link.setAttribute('aria-expanded', 'false');
        }

        // Close Bootstrap modal when parked list was opened from header suspend icon
        if (typeof jQuery !== 'undefined') {
            var $viewModal = jQuery('div.view_modal');
            if ($viewModal.length) {
                $viewModal.modal('hide');
            }
        }

        if (typeof toastr !== 'undefined') {
            toastr.success('Parked sale added to cart');
        }
    };

    if ($('table#pos_table tbody tr.product_row').length > 0) {
        if (typeof swal === 'function') {
            swal({
                title: LANG.sure || 'Replace current cart?',
                text: 'Current cart items will be replaced with the parked sale.',
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then(function (ok) {
                if (ok) {
                    doLoad();
                }
            });
            return true;
        }
    }

    doLoad();
    return true;
}

/**
 * Read data-default attr reliably (jQuery .data() can leave empty defaults as undefined;
 * .val(undefined) does nothing and previous sale tax/discount would stick).
 */
function pos_attr_default($el, fallback) {
    if (!$el || !$el.length) {
        return fallback;
    }
    var raw = $el.attr('data-default');
    if (raw === undefined || raw === null || String(raw).trim() === '') {
        return fallback;
    }
    return raw;
}

/**
 * Clear order-level discount + tax after each finished sale (next sale starts clean).
 * Discount/tax modals are moved to body so form.reset() does not clear them.
 */
function pos_reset_order_discount_and_tax() {
    var discAmt = pos_attr_default($('input#discount_amount'), 0);
    var discType = pos_attr_default($('input#discount_type'), 'percentage');
    var taxId = pos_attr_default($('input#tax_rate_id'), '');
    var taxRate = taxId ? pos_attr_default($('input#tax_calculation_amount'), 0) : 0;

    // Hidden form fields used by total calculation / submit
    if ($('input#discount_type').length) {
        $('input#discount_type').val(discType);
    }
    if ($('input#discount_amount').length) {
        __write_number($('input#discount_amount'), discAmt);
    }
    if ($('input#tax_rate_id').length) {
        // Always set a real value (empty string clears previous sale tax)
        $('input#tax_rate_id').val(taxId === null || taxId === undefined ? '' : taxId);
    }
    if ($('input#tax_calculation_amount').length) {
        __write_number($('input#tax_calculation_amount'), taxRate);
    }

    // Modals (outside form after appendTo body)
    if ($('#discount_type_modal').length) {
        $('#discount_type_modal').val(discType);
    }
    if ($('#discount_amount_modal').length) {
        __write_number($('#discount_amount_modal'), discAmt);
    }
    if ($('#order_tax_modal').length) {
        $('#order_tax_modal').val(taxId === null || taxId === undefined ? '' : taxId);
    }

    // Display spans (totals recalculated next via pos_total_row)
    $('span#total_discount, span#order_tax').text(0);

    // Reward points (order-level)
    if ($('#rp_redeemed').length) {
        $('#rp_redeemed').val(0);
    }
    if ($('#rp_redeemed_amount').length) {
        $('#rp_redeemed_amount').val(0);
    }
    if ($('#rp_redeemed_modal').length) {
        $('#rp_redeemed_modal').val(0);
    }
    if ($('#rp_redeemed_amount_text').length) {
        $('#rp_redeemed_amount_text').text(__currency_trans_from_en(0, false));
    }
    $('span#loyalty_amount_display').text(0);
}

function reset_pos_form(){

	//If on edit page then redirect to Add POS page
	if($('form#edit_pos_sell_form').length > 0){
		var redirectUrl = $("input#pos_redirect_url").val() || ((window.base_path || '') + '/pos/create');
		window.location = redirectUrl;
		return true;
	}

    // Clear cart lines FIRST (before any optional widgets that may throw)
    $('.row_edit_product_price_model').remove();
    $('#pos_table tbody.pos-cart-line').remove();
    $('table#pos_table tbody tr.pos-line-edit-row').remove();
    $('table#pos_table tbody tr.product_row').remove();
    $('input#product_row_count').val(0);
    if (typeof pos_sync_empty_state === 'function') {
        pos_sync_empty_state();
    }
	
    //reset all repair defects tags
    if ($("#repair_defects").length > 0 && typeof tagify_repair_defects !== 'undefined' && tagify_repair_defects) {
        try { tagify_repair_defects.removeAllTags(); } catch (e) {}
    }

	if(typeof pos_form_obj !== 'undefined' && pos_form_obj[0]){
		pos_form_obj[0].reset();
	}
	if(typeof sell_form !== 'undefined' && sell_form && sell_form[0]){
		sell_form[0].reset();
	}
	set_default_customer();
	set_location();

	$('span.total_quantity, span.price_total, span#total_discount, span#order_tax, span#total_payable, span#shipping_charges_amount, span#loyalty_amount_display').text(0);
	$('span.total_payable_span, span.total_paying, span.balance_due, span.payment_customer_prev_due').text(__currency_trans_from_en(0, true));
	$('#payment_customer_prev_due').val(0);
	$('#in_balance_due').val(0);
    $('.pos_pay_cta_total').text(__currency_trans_from_en(0, true));

	$('#modal_payment').find('.remove_payment_row').each( function(){
		$(this).closest('.payment_row').remove();
	});
	// Clear cashier-typed locks so next sale starts with auto full amount
	$('#payment_rows_div').find('input.payment-amount').removeAttr('data-pos-amt-locked');

    if ($('#is_credit_sale').length) {
        $('#is_credit_sale').val(0);
    }

	// Reset order discount + tax (form fields AND modals — must not stick on next sale)
	pos_reset_order_discount_and_tax();

	$('select.payment_types_dropdown').val('cash').trigger('change');
	$('#price_group').trigger('change');

	//Reset shipping
	__write_number($('input#shipping_charges'), pos_attr_default($('input#shipping_charges'), 0));
	$('input#shipping_details').val(pos_attr_default($('input#shipping_details'), ''));
    $('input#shipping_address, input#shipping_status, input#delivered_to, input#delivery_person').val('');
    $('input#shipping_method, input#shipping_date, input#shipping_tracking_no, input#no_of_boxes').val('');
    if ($('#shipping_method_modal').length) {
        $('#shipping_method_modal').val('').trigger('change');
    }
    if ($('#shipping_date_modal').length) {
        $('#shipping_date_modal').val('');
    }
    if ($('#shipping_tracking_no_modal').length) {
        $('#shipping_tracking_no_modal').val('');
    }
    if ($('#no_of_boxes_modal').length) {
        $('#no_of_boxes_modal').val('');
    }
    if ($('#shipping_details_modal').length) {
        $('#shipping_details_modal').val('');
    }
    if ($('#shipping_charges_modal').length) {
        __write_number($('#shipping_charges_modal'), 0);
    }
    if ($('#shipping_address_modal').length) {
        $('#shipping_address_modal').val('');
    }
    if ($('#shipping_status_modal').length) {
        $('#shipping_status_modal').val('');
    }
    if ($('#delivered_to_modal').length) {
        $('#delivered_to_modal').val('');
    }
	if($('input#is_recurring').length > 0){
		$('input#is_recurring').iCheck('update');
	};
    if($('input#is_kitchen_order').length > 0){
		$('input#is_kitchen_order').iCheck('update');
	};
    if($('#invoice_layout_id').length > 0){
        $('#invoice_layout_id').trigger('change');
    };
    $('span#round_off_text').text(0);

    // Clear sale notes leftover from payment / park
    if ($('#sale_note').length) {
        $('#sale_note').val('');
    }
    if ($('#staff_note').length) {
        $('#staff_note').val('');
    }
    if ($('#confirmSuspendModal textarea[name="additional_notes"]').length) {
        $('#confirmSuspendModal textarea[name="additional_notes"]').val('');
    }

    //repair module extra  fields reset
    if ($('#repair_device_id').length > 0) {
        $('#repair_device_id').val('').trigger('change');
    }

    //Status is hidden in sales order
    if ($('#status').length > 0 && $('#status').is(":visible")) {
        $('#status').val('').trigger('change');
    }
    if ($('#transaction_date').length > 0) {
        if ($('#transaction_date').data('DateTimePicker')) {
            $('#transaction_date').data("DateTimePicker").date(moment());
        } else if ($('#transaction_date').data('datepicker')) {
            var dateFmt = typeof moment_date_format !== 'undefined' ? moment_date_format : 'MM/DD/YYYY';
            $('#transaction_date').datepicker('update', moment().toDate());
            $('#transaction_date').val(moment().format(dateFmt));
        }
    }
    if ($('.paid_on').length > 0) {
        $('.paid_on').each(function () {
            if ($(this).data('DateTimePicker')) {
                $(this).data('DateTimePicker').date(moment());
            }
        });
    }
    if ($('#commission_agent').length > 0) {
        $('#commission_agent').val('').trigger('change');
    } 

    // Reset contact due display, then reload for default/walk-in (set_default_customer ran earlier)
    $('.contact_due_text').find('span').text('');
    $('.contact_due_text').addClass('hide');
    var due_cid = $('select#customer_id').val() || $('#default_customer_id').val();
    if (due_cid && $('.contact_due_text').length && typeof get_contact_due === 'function') {
        get_contact_due(due_cid);
    }

    // Clear retrieved parked-sale binding
    var $addForm = $('#add_pos_sell_form');
    if ($addForm.length && $addForm.data('original-action')) {
        $addForm.attr('action', $addForm.data('original-action'));
        $addForm.find('input[name="_method"]').remove();
        $addForm.removeAttr('data-retrieved-id');
        $addForm.removeData('retrieved-id');
    }

    $(document).trigger('sell_form_reset');

    // Set global_is_clear_local_storage to true to clear local storage
    global_is_clear_local_storage = true;
    saveFormDataToLocalStorage();

    // Re-apply discount/tax defaults last (anything above must not leave previous sale values)
    pos_reset_order_discount_and_tax();

    if (typeof pos_total_row === 'function') {
        try { pos_total_row(); } catch (e) {}
    }

    // Silent live stock update only (no product grid reload / no loader)
    if (typeof posFetchLiveStock === 'function') {
        try { posFetchLiveStock(); } catch (e) {}
    }
}

function set_default_customer() {
    var default_customer_id = $('#default_customer_id').val();
    var default_customer_name = $('#default_customer_name').val();
    var default_customer_balance = $('#default_customer_balance').val();
    var default_customer_address = $('#default_customer_address').val();
    var exists = default_customer_id ? $('select#customer_id option[value="' + default_customer_id + '"]').length : 0;
    if (exists == 0 && default_customer_id) {
        $('select#customer_id').append(
            $('<option>', { value: default_customer_id, text: default_customer_name || 'Walk-In Customer' })
        );
    }
    $('#advance_balance_text').text(__currency_trans_from_en(default_customer_balance), true);
    $('#advance_balance').val(default_customer_balance);
    $('#shipping_address_modal').val(default_customer_address);
    if (default_customer_address) {
        $('#shipping_address').val(default_customer_address);
    }
    if (default_customer_id) {
        $('select#customer_id')
            .val(String(default_customer_id))
            .trigger('change');
        // select2:select does not fire for programmatic default — load walk-in prev due on first paint
        if ($('.contact_due_text').length && typeof get_contact_due === 'function') {
            get_contact_due(default_customer_id);
        }
    }

    // POS theme: keep walk-in / default customer name visible (selected)
    if ($('body').hasClass('pos-sale-theme') && default_customer_name) {
        setTimeout(function () {
            var $rendered = $('#customer_id').next('.select2-container').find('.select2-selection__rendered');
            $rendered
                .text(default_customer_name)
                .attr('title', default_customer_name)
                .removeClass('pos-cust-placeholder-text')
                .css('color', '#f2f2f2');
            if (typeof posCustSyncResetBtn === 'function') {
                posCustSyncResetBtn();
            }
        }, 0);
    }

    if ($('#default_selling_price_group').length) {
        $('#price_group').val($('#default_selling_price_group').val());
        $('#price_group').change();
    }

    //initialize tags input (tagify)
    if ($("textarea#repair_defects").length > 0 && !customer_set) {
        let suggestions = [];
        if ($("input#pos_repair_defects_suggestion").length > 0 && $("input#pos_repair_defects_suggestion").val().length > 2) {
            suggestions = JSON.parse($("input#pos_repair_defects_suggestion").val());    
        }
        let repair_defects = document.querySelector('textarea#repair_defects');
        tagify_repair_defects = new Tagify(repair_defects, {
                  whitelist: suggestions,
                  maxTags: 100,
                  dropdown: {
                    maxItems: 100,           // <- mixumum allowed rendered suggestions
                    classname: "tags-look", // <- custom classname for this dropdown, so it could be targeted
                    enabled: 0,             // <- show suggestions on focus
                    closeOnSelect: false    // <- do not hide the suggestions dropdown once an item has been selected
                  }
                });
    }

    customer_set = true;
}

//Set the location and initialize printer
function set_location() {
    if ($('select#select_location_id').length == 1) {
        $('input#location_id').val($('select#select_location_id').val());
        $('input#location_id').data(
            'receipt_printer_type',
            $('select#select_location_id')
                .find(':selected')
                .data('receipt_printer_type')
        );
        $('input#location_id').data(
            'default_payment_accounts',
            $('select#select_location_id')
                .find(':selected')
                .data('default_payment_accounts')
        );

        $('input#location_id').attr(
            'data-default_price_group',
            $('select#select_location_id')
                .find(':selected')
                .data('default_price_group')
        );
    }

    if ($('input#location_id').val()) {
        $('input#search_product')
            .prop('disabled', false);
        if (!$('#__is_mobile').length) {
            $('input#search_product').focus();
        }
    } else {
        $('input#search_product').prop('disabled', true);
    }

    initialize_printer();
}

function initialize_printer() {
    if ($('input#location_id').data('receipt_printer_type') == 'printer') {
        initializeSocket();
    }
}

$('body').on('click', 'label', function(e) {
    var field_id = $(this).attr('for');
    if (field_id) {
        if ($('#' + field_id).hasClass('select2')) {
            $('#' + field_id).select2('open');
            return false;
        }
    }
});

$('body').on('focus', 'select', function(e) {
    var field_id = $(this).attr('id');
    if (field_id) {
        if ($('#' + field_id).hasClass('select2')) {
            $('#' + field_id).select2('open');
            return false;
        }
    }
});

function round_row_to_iraqi_dinnar(row) {
    if (iraqi_selling_price_adjustment) {
        var element = row.find('input.pos_unit_price_inc_tax');
        var unit_price = round_to_iraqi_dinnar(__read_number(element));
        __write_number(element, unit_price);
        element.change();
    }
}

function pos_print_copies_count(receipt) {
    var fromReceipt = parseInt(receipt && receipt.print_copies, 10);
    if ([1, 2, 3].indexOf(fromReceipt) !== -1) {
        return fromReceipt;
    }
    var field = document.getElementById('pos_print_copies');
    if (field) {
        var fromField = parseInt(field.value, 10);
        if ([1, 2, 3].indexOf(fromField) !== -1) {
            return fromField;
        }
    }
    try {
        var fromStore = parseInt(localStorage.getItem('pos_print_copies') || '1', 10);
        if ([1, 2, 3].indexOf(fromStore) !== -1) {
            return fromStore;
        }
    } catch (e) {}
    return 1;
}

function pos_print_send_thermal(content, copies) {
    var remaining = Math.max(1, copies || 1);
    var sendOnce = function () {
        if (socket != null && socket.readyState == 1) {
            socket.send(JSON.stringify(content));
            remaining -= 1;
            if (remaining > 0) {
                setTimeout(sendOnce, 450);
            }
        } else {
            initializeSocket();
            setTimeout(function () {
                if (socket != null && socket.readyState == 1) {
                    sendOnce();
                } else {
                    try {
                        socket.send(JSON.stringify(content));
                    } catch (e) {}
                }
            }, 700);
        }
    };
    sendOnce();
}

function pos_print(receipt) {
    var copies = pos_print_copies_count(receipt);

    //If printer type then connect with websocket
    if (receipt.print_type == 'printer') {
        var content = receipt;
        content.type = 'print-receipt';
        pos_print_send_thermal(content, copies);
    } else if (receipt.html_content != '') {
        // Do not set document.title to invoice no (removes top browser print header mark)

        // Hide any open POS popup so it is not drawn over the invoice (print + print preview)
        try {
            $('#recent_transactions_modal').modal('hide');
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css({ 'padding-right': '', overflow: '' });
            $('#recent_transactions_modal').removeClass('in show').hide();
        } catch (e) {}

        // Browser: repeat HTML with page breaks for Double / Triple
        var html = receipt.html_content || '';
        if (copies > 1 && html) {
            var pieces = [];
            for (var i = 0; i < copies; i++) {
                var pageBreak = i < copies - 1
                    ? ' style="page-break-after:always;break-after:page;"'
                    : '';
                pieces.push('<div class="pos-receipt-copy"' + pageBreak + '>' + html + '</div>');
            }
            html = pieces.join('');
        }

        //If printer type browser then print content
        $('#receipt_section').html(html);
        __currency_convert_recursively($('#receipt_section'));
        // PDF path removes browser HTML date/URL stamps (Chrome cannot hide those on web pages)
        // Multi-copy uses HTML so each copy is a page; skip single-page PDF for N>1
        var pdfUrl = '';
        if (copies <= 1) {
            pdfUrl = receipt.print_pdf_url || '';
            if (!pdfUrl && receipt.transaction_id) {
                pdfUrl = (window.base_path || '') + '/sells/' + receipt.transaction_id + '/print-pdf';
            }
        }
        __print_receipt('receipt_section', pdfUrl);
    }
}

function calculate_discounted_unit_price(row) {
    var this_unit_price = __read_number(pos_row_field(row, 'input.pos_unit_price'));
    var row_discounted_unit_price = this_unit_price;
    var row_discount_type = pos_row_field(row, 'select.row_discount_type').val();
    var row_discount_amount = __read_number(pos_row_field(row, 'input.row_discount_amount'));
    if (row_discount_amount) {
        if (row_discount_type == 'fixed') {
            row_discounted_unit_price = this_unit_price - row_discount_amount;
        } else {
            row_discounted_unit_price = __substract_percent(this_unit_price, row_discount_amount);
        }
    }

    return row_discounted_unit_price;
}

/**
 * Re-resolve group prices and brand / category / product discounts for all cart lines
 * when customer or selling price group changes.
 */
var posCartPriceRefreshTimer = null;
function pos_schedule_cart_price_refresh() {
    if (!posCartPriceRefreshEnabled) {
        return;
    }
    if (posCartPriceRefreshTimer) {
        clearTimeout(posCartPriceRefreshTimer);
    }
    posCartPriceRefreshTimer = setTimeout(function () {
        posCartPriceRefreshTimer = null;
        if (typeof pos_refresh_line_discounts === 'function') {
            pos_refresh_line_discounts();
        }
    }, 80);
}

function pos_refresh_line_discounts() {
    var $rows = $('table#pos_table tbody tr.product_row');
    if (!$rows.length) {
        return;
    }

    var location_id = $('input#location_id').val() || $('select#select_location_id').val();
    if (!location_id) {
        return;
    }

    var variation_ids = [];
    $rows.each(function () {
        var vid = $(this).find('input.row_variation_id').first().val();
        if (vid) {
            variation_ids.push(vid);
        }
    });
    if (!variation_ids.length) {
        return;
    }

    var price_group = '';
    if ($('#price_group').length > 0) {
        price_group = $('#price_group').val();
    }
    if ($('#default_price_group').length > 0 && (price_group === '' || price_group === null || price_group === undefined)) {
        price_group = $('#default_price_group').val();
    }
    if ($('#types_of_service_price_group').length > 0 && $('#types_of_service_price_group').val()) {
        price_group = $('#types_of_service_price_group').val();
    }

    var customer_id = $('select#customer_id').val() || $('#default_customer_id').val() || '';

    $.ajax({
        method: 'POST',
        url: (window.base_path || '') + '/sells/pos/get-product-line-discounts',
        dataType: 'json',
        data: {
            location_id: location_id,
            customer_id: customer_id,
            price_group: price_group,
            variation_ids: variation_ids,
        },
        success: function (result) {
            if (!result || !result.success) {
                return;
            }

            $rows.each(function () {
                var $row = $(this);
                var vid = $row.find('input.row_variation_id').first().val();
                if (!vid) {
                    return;
                }

                var keepManual = typeof pos_row_has_manual_price === 'function'
                    ? pos_row_has_manual_price($row)
                    : !!$row.data('pos-manual-price');
                var keepManualDiscount = typeof pos_row_has_manual_discount === 'function'
                    ? pos_row_has_manual_discount($row)
                    : !!$row.data('pos-manual-discount');

                var price = result.prices ? result.prices[vid] : null;
                if (!keepManual && price && (price.unit_price || price.unit_price_inc_tax)) {
                    var $unit = pos_row_field($row, 'input.pos_unit_price');
                    var $inc = $row.find('input.pos_unit_price_inc_tax');
                    if ($unit.length && price.unit_price) {
                        window.__posPriceLogicLock = true;
                        try {
                            __write_number($unit, price.unit_price);
                        } finally {
                            window.__posPriceLogicLock = false;
                        }
                        pos_set_row_list_price($row, price.unit_price);
                        // Only refresh MSP floor when it already exists — never introduce a new
                        // min-value rule (that blocks line discounts on finalize).
                        if ($unit.is('[data-rule-min-value]')) {
                            $unit.attr('data-rule-min-value', price.unit_price);
                        }
                    }
                    if ($inc.length && price.unit_price_inc_tax) {
                        window.__posPriceLogicLock = true;
                        try {
                            __write_number($inc, price.unit_price_inc_tax);
                        } finally {
                            window.__posPriceLogicLock = false;
                        }
                        if ($inc.is('[data-rule-min-value]')) {
                            $inc.attr('data-rule-min-value', price.unit_price_inc_tax);
                        }
                    }
                }

                // Auto discounts override when a rule exists; otherwise keep cashier line discount
                var disc = result.discounts ? result.discounts[vid] : null;
                var $type = pos_row_field($row, 'select.row_discount_type');
                var $amount = pos_row_field($row, 'input.row_discount_amount');
                var $discId = $row.find('input.row_discount_id');
                if (!$discId.length) {
                    $discId = pos_row_field($row, 'input.row_discount_id');
                }

                if (disc) {
                    if ($type.length) {
                        $type.val(disc.discount_type);
                    }
                    if ($amount.length) {
                        __write_number($amount, disc.discount_amount);
                    }
                    if ($discId.length) {
                        $discId.val(disc.discount_id);
                    } else {
                        $row.append(
                            '<input type="hidden" name="products[' +
                                ($row.data('row_index') || '') +
                                '][discount_id]" class="row_discount_id" value="' +
                                disc.discount_id +
                                '">'
                        );
                    }
                    pos_clear_row_manual_discount($row);

                    // Update help text in row price modal if present
                    var $help = $row.find('.row_edit_product_price_model .help-block').first();
                    if (!$help.length) {
                        $help = pos_row_edit_modal($row).find('.help-block').first();
                    }
                    if ($help.length && disc.name) {
                        $help.html(
                            'Discount: <i>' +
                                $('<div>').text(disc.name).html() +
                                '</i>' +
                                (disc.formated_ends_at
                                    ? ' applied (Ends at: <i>' +
                                      $('<div>').text(disc.formated_ends_at).html() +
                                      '</i>)'
                                    : '')
                        );
                    }
                } else if (!keepManualDiscount) {
                    if ($type.length) {
                        $type.val('fixed');
                    }
                    if ($amount.length) {
                        __write_number($amount, 0);
                    }
                    if ($discId.length) {
                        $discId.val('');
                    }
                }

                // Recalc row totals
                if (typeof calculate_discounted_unit_price === 'function') {
                    var discounted_unit_price = calculate_discounted_unit_price($row);
                    var tax_rate = pos_row_field($row, 'select.tax_id').find(':selected').data('rate') || 0;
                    var quantity = __read_number($row.find('input.pos_quantity'));
                    var unit_price_inc_tax = __add_percent(discounted_unit_price, tax_rate);
                    var line_total = quantity * unit_price_inc_tax;
                    var $incTax = $row.find('input.pos_unit_price_inc_tax');
                    __write_number($incTax, unit_price_inc_tax);
                    __write_number($row.find('input.pos_line_total'), line_total, false);
                    $row.find('span.pos_line_total_text').text(__currency_trans_from_en(line_total, true));
                    $row.find('span.pos_line_unit_price_text').text(__currency_trans_from_en(unit_price_inc_tax, true));
                    // Line discount lowers inc-tax below catalog MSP; don't leave a stale min rule
                    var appliedDisc = __read_number(pos_row_field($row, 'input.row_discount_amount'));
                    if (appliedDisc && !isNaN(appliedDisc) && appliedDisc > 0 && $incTax.length) {
                        $incTax.removeAttr('data-rule-min-value');
                        try {
                            if ($incTax.rules) {
                                $incTax.rules('remove', 'min-value');
                            }
                        } catch (err) {}
                    }
                    if (typeof pos_each_row === 'function') {
                        pos_each_row($row);
                    }
                }
            });

            if (typeof pos_total_row === 'function') {
                pos_total_row();
            }
        },
    });
}

function get_unit_price_from_discounted_unit_price(row, discounted_unit_price) {
    var this_unit_price = discounted_unit_price;
    var row_discount_type = pos_row_field(row, 'select.row_discount_type').val();
    var row_discount_amount = __read_number(pos_row_field(row, 'input.row_discount_amount'));
    if (row_discount_amount) {
        if (row_discount_type == 'fixed') {
            this_unit_price = discounted_unit_price + row_discount_amount;
        } else {
            this_unit_price = __get_principle(discounted_unit_price, row_discount_amount, true);
        }
    }

    return this_unit_price;
}

//Update quantity if line subtotal changes
$('table#pos_table').on('change', 'input.pos_line_total', function() {

    var subtotal = __read_number($(this));
    var tr = $(this).parents('tr');
    var quantity_element = tr.find('input.pos_quantity');
    var unit_price_inc_tax = __read_number(tr.find('input.pos_unit_price_inc_tax'));
    var quantity = subtotal / unit_price_inc_tax;
    __write_number(quantity_element, quantity);

    __write_number($(this), subtotal, false);


    if (sell_form_validator) {
        sell_form_validator.element(quantity_element);
    }
    if (pos_form_validator) {
        pos_form_validator.element(quantity_element);
    }
    tr.find('span.pos_line_total_text').text(__currency_trans_from_en(subtotal, true));

    pos_total_row();
});

$('div#product_list_body').on('scroll', function() {
    var el = this;
    // Don't paginate when content fits (no real scroll) — prevents endless spinner
    if (el.scrollHeight <= el.clientHeight + 4) {
        return;
    }
    if (pos_product_list_loading || pos_product_list_exhausted) {
        return;
    }
    if ($(el).scrollTop() + $(el).innerHeight() < el.scrollHeight - 48) {
        return;
    }

    var page = parseInt($('#suggestion_page').val(), 10) || 1;
    page += 1;
    $('#suggestion_page').val(page);
    var location_id = $('input#location_id').val();
    var category_id = global_p_category_id;
    var brand_id = global_brand_id;

    var is_enabled_stock = null;
    if ($("#is_enabled_stock").length) {
        is_enabled_stock = $("#is_enabled_stock").val();
    }

    var device_model_id = null;
    if ($("#repair_model_id").length) {
        device_model_id = $("#repair_model_id").val();
    }

    get_product_suggestion_list(category_id, brand_id, location_id, null, is_enabled_stock, device_model_id);
});

$(document).on('ifChecked', '#is_recurring', function() {
    $('#recurringInvoiceModal').modal('show');
});

$(document).on('shown.bs.modal', '#recurringInvoiceModal', function() {
    $('input#recur_interval').focus();
});

$(document).on('click', '#select_all_service_staff', function() {
    var val = $('#res_waiter_id').val();
    $('#pos_table tbody')
        .find('select.order_line_service_staff')
        .each(function() {
            $(this)
                .val(val)
                .change();
        });
});

$(document).on('click', '.print-invoice-link', function(e) {
    e.preventDefault();
    e.stopPropagation();
    var printUrl = $(this).attr('href') + '?check_location=true';

    // Close Recent Transactions / purchase history / any open modal before invoice print so
    // popup does not sit on top of receipt (POS theme keeps modals display:flex !important)
    try {
        $('#recent_transactions_modal').modal('hide');
        $('#pos_customer_purchase_history_modal').modal('hide');
        $('.modal.in, .modal.show').not('#receipt_section').modal('hide');
    } catch (err) {}
    setTimeout(function() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css({ 'padding-right': '', overflow: '' });
        $('#recent_transactions_modal').removeClass('in show').attr('aria-hidden', 'true').hide();
        $('#pos_customer_purchase_history_modal').removeClass('in show').attr('aria-hidden', 'true').hide();
    }, 50);

    $.ajax({
        url: printUrl,
        dataType: 'json',
        success: function(result) {
            if (result.success == 1) {
                //Check if enabled or not
                if (result.receipt.is_enabled) {
                    // Ensure modal leftover is gone when receipt HTML is ready
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    $('#recent_transactions_modal').removeClass('in show').hide();
                    $('#pos_customer_purchase_history_modal').removeClass('in show').hide();
                    pos_print(result.receipt);
                }
            } else {
                toastr.error(result.msg);
            }

        },
    });
});

function getCustomerRewardPoints() {
    if ($('#reward_point_enabled').length <= 0) {
        return false;
    }
    var is_edit = $('form#edit_sell_form').length || 
    $('form#edit_pos_sell_form').length ? true : false;
    if (is_edit && !customer_set) {
        return false;
    }

    var customer_id = $('#customer_id').val();

    $.ajax({
        method: 'POST',
        url: '/sells/pos/get-reward-details',
        data: { 
            customer_id: customer_id
        },
        dataType: 'json',
        success: function(result) {
            $('#available_rp').text(result.points);
            $('#rp_redeemed_modal').data('max_points', result.points);
            updateRedeemedAmount();
            $('#rp_redeemed_amount').change()
        },
    });
}

function updateRedeemedAmount(argument) {
    var points = $('#rp_redeemed_modal').val().trim();
    points = points == '' ? 0 : parseInt(points);
    var amount_per_unit_point = parseFloat($('#rp_redeemed_modal').data('amount_per_unit_point'));
    var redeemed_amount = points * amount_per_unit_point;
    $('#rp_redeemed_amount_text, #loyalty_amount_display').text(__currency_trans_from_en(redeemed_amount, true));
    $('#rp_redeemed').val(points);
    $('#rp_redeemed_amount').val(redeemed_amount);
}

$(document).on('change', 'select#customer_id', function(){
    var default_customer_id = $('#default_customer_id').val();
    if ($(this).val() == default_customer_id) {
        //Disable reward points for walkin customers
        if ($('#rp_redeemed_modal').length) {
            $('#rp_redeemed_modal').val('');
            $('#rp_redeemed_modal').change();
            $('#rp_redeemed_modal').attr('disabled', true);
            $('#available_rp').text('');
            updateRedeemedAmount();
            pos_total_row();
        }
    } else {
        if ($('#rp_redeemed_modal').length) {
            $('#rp_redeemed_modal').removeAttr('disabled');
        }
        getCustomerRewardPoints();
    }

    get_sales_orders();
});

$(document).on('change', '#rp_redeemed_modal', function(){
    var points = $(this).val().trim();
    points = points == '' ? 0 : parseInt(points);
    var amount_per_unit_point = parseFloat($(this).data('amount_per_unit_point'));
    var redeemed_amount = points * amount_per_unit_point;
    $('#rp_redeemed_amount_text, #loyalty_amount_display').text(__currency_trans_from_en(redeemed_amount, true));
    var reward_validation = isValidatRewardPoint();
    if (!reward_validation['is_valid']) {
        toastr.error(reward_validation['msg']);
        $('#rp_redeemed_modal').select();
    }
});

$(document).on('change', '.direct_sell_rp_input', function(){
    updateRedeemedAmount();
    pos_total_row();
});

function isValidatRewardPoint() {
    var element = $('#rp_redeemed_modal');
    var points = element.val().trim();
    points = points == '' ? 0 : parseInt(points);

    var max_points = parseInt(element.data('max_points'));
    var is_valid = true;
    var msg = '';

    if (points == 0) {
        return {
            is_valid: is_valid,
            msg: msg
        }
    }

    var rp_name = $('input#rp_name').val();
    if (points > max_points) {
        is_valid = false;
        msg = __translate('max_rp_reached_error', {max_points: max_points, rp_name: rp_name});
    }

    var min_order_total_required = parseFloat(element.data('min_order_total'));

    var order_total = __read_number($('#final_total_input'));

    if (order_total < min_order_total_required) {
        is_valid = false;
        msg = __translate('min_order_total_error', {min_order: __currency_trans_from_en(min_order_total_required, true), rp_name: rp_name});
    }

    var output = {
        is_valid: is_valid,
        msg: msg,
    }

    return output;
}

function adjustComboQty(tr){
    if(tr.find('input.product_type').val() == 'combo'){
        var qty = __read_number(tr.find('input.pos_quantity'));
        var multiplier = __getUnitMultiplier(tr);

        tr.find('input.combo_product_qty').each(function(){
            $(this).val($(this).data('unit_quantity') * qty * multiplier);
        });
    }
}

$(document).on('change', '#types_of_service_id', function(){
    var types_of_service_id = $(this).val();
    var location_id = $('#location_id').val();

    if(types_of_service_id) {
        $.ajax({
            method: 'POST',
            url: '/sells/pos/get-types-of-service-details',
            data: { 
                types_of_service_id: types_of_service_id,
                location_id: location_id
            },
            dataType: 'json',
            success: function(result) {
                //reset form if price group is changed
                var prev_price_group = $('#types_of_service_price_group').val();
                if(result.price_group_id) {
                    $('#types_of_service_price_group').val(result.price_group_id);
                    $('#price_group_text').removeClass('hide');
                    $('#price_group_text span').text(result.price_group_name);
                } else {
                    $('#types_of_service_price_group').val('');
                    $('#price_group_text').addClass('hide');
                    $('#price_group_text span').text('');
                }
                $('#types_of_service_id').val(types_of_service_id);
                $('.types_of_service_modal').html(result.modal_html);
                
                if (prev_price_group != result.price_group_id) {
                    if ($('form#edit_pos_sell_form').length > 0) {
                        $('table#pos_table tbody').html('');
                        pos_total_row();
                    } else {
                        reset_pos_form();
                    }
                } else {
                    pos_total_row();
                }

                $('.types_of_service_modal').modal('show');
            },
        });
    } else {
        $('.types_of_service_modal').html('');
        $('#types_of_service_price_group').val('');
        $('#price_group_text').addClass('hide');
        $('#price_group_text span').text('');
        $('#packing_charge_text').text('');
        if ($('form#edit_pos_sell_form').length > 0) {
            $('table#pos_table tbody').html('');
            pos_total_row();
        } else {
            reset_pos_form();
        }
    }
});

$(document).on('change', 'input#packing_charge, #additional_expense_value_1, #additional_expense_value_2, \
        #additional_expense_value_3, #additional_expense_value_4', function() {
    pos_total_row();
});

$(document).on('click', '.service_modal_btn', function(e) {
    if ($('#types_of_service_id').val()) {
        $('.types_of_service_modal').modal('show');
    }
});

$(document).on('click', '.pos-pay-method-card:not(:disabled)', function (e) {
    e.preventDefault();
    var $btn = $(this);
    var method = $btn.data('method');
    if (!method) {
        return;
    }
    // Scope to the method-card grid first (more reliable than .payment_row alone)
    var $grid = $btn.closest('.pos-pay-method-cards');
    var $row = $btn.closest('.payment_row');
    if (!$row.length) {
        $row = $btn.closest('.box-body, .form-group').parent().closest('.col-md-12, .payment_row, .box');
    }
    var $select = $row.find('select.payment_types_dropdown').first();
    if (!$select.length) {
        $select = $btn.closest('.form-group').find('select.payment_types_dropdown, .payment_types_dropdown').first();
    }
    if ($grid.length) {
        $grid.find('.pos-pay-method-card').removeClass('is-active').attr('aria-pressed', 'false');
    } else if ($row.length) {
        $row.find('.pos-pay-method-card').removeClass('is-active').attr('aria-pressed', 'false');
    }
    $btn.addClass('is-active').attr('aria-pressed', 'true');

    // Existing credit sale flag (same as express Credit button)
    if (method === 'credit_sale') {
        if ($('#is_credit_sale').length) {
            $('#is_credit_sale').val(1);
        }
        return;
    }

    if ($('#is_credit_sale').length) {
        $('#is_credit_sale').val(0);
    }
    if ($select.length) {
        $select.val(method).trigger('change');
    }
});

$(document).on('change', '.payment_types_dropdown', function(e) {
    var default_accounts = $('select#select_location_id').length ? 
                $('select#select_location_id')
                .find(':selected')
                .data('default_payment_accounts') : $('#location_id').data('default_payment_accounts');
    var payment_type = $(this).val();
    var payment_row = $(this).closest('.payment_row');
    // Sync card UI selection (Pay modal card-wise methods)
    var $cardsRoot = payment_row.length
        ? payment_row
        : $(this).closest('.form-group');
    if ($cardsRoot.length && payment_type) {
        $cardsRoot.find('.pos-pay-method-card').removeClass('is-active').attr('aria-pressed', 'false');
        $cardsRoot
            .find('.pos-pay-method-card[data-method="' + payment_type + '"]')
            .addClass('is-active')
            .attr('aria-pressed', 'true');
    }
    if (payment_type && payment_type != 'advance') {
        var default_account = (default_accounts && default_accounts[payment_type] && default_accounts[payment_type]['account'])
            ? default_accounts[payment_type]['account']
            : '';
        var row_index = payment_row.find('.payment_row_index').val();

        var account_dropdown = payment_row.find('select#account_' + row_index);
        if (account_dropdown.length && default_accounts) {
            account_dropdown.val(default_account);
            account_dropdown.change();
        }
    }

    //Validate max amount and disable account if advance 
    amount_element = payment_row.find('.payment-amount');
    account_dropdown = payment_row.find('.account-dropdown');
    if (payment_type == 'advance') {
        max_value = $('#advance_balance').val();
        msg = $('#advance_balance').data('error-msg');
        if (amount_element.length && amount_element.rules) {
            try {
                amount_element.rules('add', {
                    'max-value': max_value,
                    messages: {
                        'max-value': msg,
                    },
                });
            } catch (err) {}
        }
        if (account_dropdown.length) {
            account_dropdown.prop('disabled', true);
            account_dropdown.closest('.form-group').addClass('hide');
        }
    } else {
        if (amount_element.length) {
            // Leaving Advance must unlock amount so total can be reapplied on next modal open
            amount_element.prop('readonly', false).removeAttr('readonly');
            if (amount_element.rules) {
                try {
                    amount_element.rules("remove", "max-value");
                } catch (err) {}
            }
        }
        if (account_dropdown.length) {
            account_dropdown.prop('disabled', false); 
            account_dropdown.closest('.form-group').removeClass('hide');
        }    
    }
});

$(document).on('show.bs.modal', '#recent_transactions_modal', function () {
    get_recent_transactions('final', $('div#tab_final'));
});
$(document).on('shown.bs.tab', 'a[href="#tab_quotation"]', function () {
    get_recent_transactions('quotation', $('div#tab_quotation'));
});
$(document).on('shown.bs.tab', 'a[href="#tab_draft"]', function () {
    get_recent_transactions('draft', $('div#tab_draft'));
});

function disable_pos_form_actions(){
    if (!window.navigator.onLine) {
        return false;
    }

    $('div.pos-processing').show();
    $('#pos-save').attr('disabled', 'true');
    $('div.pos-form-actions').find('button').attr('disabled', 'true');
}

function enable_pos_form_actions(){
    $('div.pos-processing').hide();
    $('#pos-save').removeAttr('disabled');
    $('div.pos-form-actions').find('button').removeAttr('disabled');
}

$(document).on('change', '#recur_interval_type', function() {
    if ($(this).val() == 'months') {
        $('.subscription_repeat_on_div').removeClass('hide');
    } else {
        $('.subscription_repeat_on_div').addClass('hide');
    }
});

function validate_discount_field() {
    discount_element = $('#discount_amount_modal');
    discount_type_element = $('#discount_type_modal');

    if ($('#add_sell_form').length || $('#edit_sell_form').length) {
        discount_element = $('#discount_amount');
        discount_type_element = $('#discount_type');
    }
    var max_value = parseFloat(discount_element.data('max-discount'));
    if (discount_element.val() != '' && !isNaN(max_value)) {
        if (discount_type_element.val() == 'fixed') {
            var subtotal = get_subtotal();
            //get max discount amount
            max_value = __calculate_amount('percentage', max_value, subtotal)
        }

        discount_element.rules('add', {
            'max-value': max_value,
            messages: {
                'max-value': discount_element.data('max-discount-error_msg'),
            },
        });
    } else {
        discount_element.rules("remove", "max-value");      
    }
    discount_element.trigger('change');
}

$(document).on('change', '#discount_type_modal, #discount_type', function() {
    validate_discount_field();
});

function update_shipping_address(data) {
    if ($('#shipping_address_div').length) {
        var shipping_address = '';
        if (data.supplier_business_name) {
            shipping_address += data.supplier_business_name;
        }
        if (data.name) {
            shipping_address += ',<br>' + data.name;
        }
        if (data.text) {
            shipping_address += ',<br>' + data.text;
        }
        shipping_address += ',<br>' + data.shipping_address ;
        $('#shipping_address_div').html(shipping_address);
    }
    if ($('#billing_address_div').length) {
        var address = [];
        if (data.supplier_business_name) {
            address.push(data.supplier_business_name);
        }
        if (data.name) {
            address.push('<br>' + data.name);
        }
        if (data.text) {
            address.push('<br>' + data.text);
        }
        if (data.address_line_1) {
            address.push('<br>' + data.address_line_1);
        }
        if (data.address_line_2) {
            address.push('<br>' + data.address_line_2);
        }
        if (data.city) {
            address.push('<br>' + data.city);
        }
        if (data.state) {
            address.push(data.state);
        }
        if (data.country) {
            address.push(data.country);
        }
        if (data.zip_code) {
            address.push('<br>' + data.zip_code);
        }
        var billing_address = address.join(', ');
        $('#billing_address_div').html(billing_address);
    }

    if ($('#shipping_custom_field_1').length) {
        let shipping_custom_field_1 = data.shipping_custom_field_details != null ? data.shipping_custom_field_details.shipping_custom_field_1 : '';
        $('#shipping_custom_field_1').val(shipping_custom_field_1);
    }

    if ($('#shipping_custom_field_2').length) {
        let shipping_custom_field_2 = data.shipping_custom_field_details != null ? data.shipping_custom_field_details.shipping_custom_field_2 : '';
        $('#shipping_custom_field_2').val(shipping_custom_field_2);
    }

    if ($('#shipping_custom_field_3').length) {
        let shipping_custom_field_3 = data.shipping_custom_field_details != null ? data.shipping_custom_field_details.shipping_custom_field_3 : '';
        $('#shipping_custom_field_3').val(shipping_custom_field_3);
    }

    if ($('#shipping_custom_field_4').length) {
        let shipping_custom_field_4 = data.shipping_custom_field_details != null ? data.shipping_custom_field_details.shipping_custom_field_4 : '';
        $('#shipping_custom_field_4').val(shipping_custom_field_4);
    }

    if ($('#shipping_custom_field_5').length) {
        let shipping_custom_field_5 = data.shipping_custom_field_details != null ? data.shipping_custom_field_details.shipping_custom_field_5 : '';
        $('#shipping_custom_field_5').val(shipping_custom_field_5);
    }
    
    //update export fields
    if (data.is_export) {
        $('#is_export').prop('checked', true);
        $('div.export_div').show();
        if ($('#export_custom_field_1').length) {
            $('#export_custom_field_1').val(data.export_custom_field_1);
        }
        if ($('#export_custom_field_2').length) {
            $('#export_custom_field_2').val(data.export_custom_field_2);
        }
        if ($('#export_custom_field_3').length) {
            $('#export_custom_field_3').val(data.export_custom_field_3);
        }
        if ($('#export_custom_field_4').length) {
            $('#export_custom_field_4').val(data.export_custom_field_4);
        }
        if ($('#export_custom_field_5').length) {
            $('#export_custom_field_5').val(data.export_custom_field_5);
        }
        if ($('#export_custom_field_6').length) {
            $('#export_custom_field_6').val(data.export_custom_field_6);
        }
    } else {
        $('#export_custom_field_1, #export_custom_field_2, #export_custom_field_3, #export_custom_field_4, #export_custom_field_5, #export_custom_field_6').val('');
        $('#is_export').prop('checked', false);
        $('div.export_div').hide();
    }
    
    $('#shipping_address_modal').val(data.shipping_address);
    $('#shipping_address').val(data.shipping_address);
}

function get_sales_orders() {
    if ($('#sales_order_ids').length) {
        if ($('#sale_type').val() === 'sales_order') {
            return false;
        }
        if ($('#sales_order_ids').hasClass('not_loaded')) {
            $('#sales_order_ids').removeClass('not_loaded');
            return false;
        }
        var customer_id = $('select#customer_id').val();
        var location_id = $('input#location_id').val();
        $.ajax({
            url: '/get-sales-orders/' + customer_id + '?location_id=' + location_id,
            dataType: 'json',
            success: function(data) {
                $('#sales_order_ids').select2('destroy').empty().select2({data: data});
                $('table#pos_table tbody').find('tr').each( function(){
                    if (typeof($(this).data('so_id')) !== 'undefined') {
                        $(this).remove();
                    }
                });
                pos_total_row();

                var convertId = $('#convert_sales_order_id').val();
                if (convertId && $('#sales_order_ids').length) {
                    var hasOption = false;
                    $.each(data || [], function (i, row) {
                        if (String(row.id) === String(convertId)) {
                            hasOption = true;
                            return false;
                        }
                    });
                    if (!hasOption) {
                        var label = $('#convert_sales_order_no').val() || convertId;
                        data = (data || []).concat([{ id: convertId, text: label }]);
                        $('#sales_order_ids').select2('destroy').empty().select2({data: data});
                    }
                    $('#sales_order_ids').val([String(convertId)]).trigger('change');
                    $('#sales_order_ids').trigger({
                        type: 'select2:select',
                        params: { data: { id: convertId } }
                    });
                    $('#convert_sales_order_id').val('');
                }
            },
        });
    }
}

$("#sales_order_ids").on("select2:select", function (e) {
    var sales_order_id = e.params.data.id;
    var product_row = $('input#product_row_count').val();
    var location_id = $('input#location_id').val();
    $.ajax({
        method: 'GET',
        url: '/get-sales-order-lines',
        async: false,
        data: {
            product_row: product_row,
            sales_order_id: sales_order_id,
            is_serial_no: true,

        },
        dataType: 'json',
        success: function(result) {
            if (result.html) {
                var html = result.html;
                $(html).find('tr').each(function(){
                    $('table#pos_table tbody')
                    .append($(this))
                    .find('input.pos_quantity');
                    
                    var this_row = $('table#pos_table tbody')
                        .find('tr')
                        .last();
                    pos_each_row(this_row);

                    product_row = parseInt(product_row) + 1;

                    //For initial discount if present
                    var line_total = __read_number(this_row.find('input.pos_line_total'));
                    this_row.find('span.pos_line_total_text').text(line_total);

                    //Check if multipler is present then multiply it when a new row is added.
                    if(__getUnitMultiplier(this_row) > 1){
                        this_row.find('select.sub_unit').trigger('change');
                    }

                    round_row_to_iraqi_dinnar(this_row);
                    __currency_convert_recursively(this_row);
                });

                set_so_values(result.sales_order);

                //increment row count
                $('input#product_row_count').val(product_row);
                
                pos_total_row();
            
            } else {
                toastr.error(result.msg);
                $('input#search_product')
                    .focus()
                    .select();
            }
        },
    });
});

function set_so_values(so) {
    $('textarea[name="sale_note"]').val(so.additional_notes);
    if ($('#shipping_details').is(':visible')) {
        $('#shipping_details').val(so.shipping_details);
    }
    $('#shipping_address').val(so.shipping_address);
    $('#delivered_to').val(so.delivered_to);
    $('#shipping_charges').val( __number_f(so.shipping_charges));
    $('#shipping_status').val(so.shipping_status);
    if ($('#shipping_custom_field_1').length) {
        $('#shipping_custom_field_1').val(so.shipping_custom_field_1);
    }
    if ($('#shipping_custom_field_2').length) {
        $('#shipping_custom_field_2').val(so.shipping_custom_field_2);
    }
    if ($('#shipping_custom_field_3').length) {
        $('#shipping_custom_field_3').val(so.shipping_custom_field_3);
    }
    if ($('#shipping_custom_field_4').length) {
        $('#shipping_custom_field_4').val(so.shipping_custom_field_4);
    }
    if ($('#shipping_custom_field_5').length) {
        $('#shipping_custom_field_5').val(so.shipping_custom_field_5);
    }
}

$("#sales_order_ids").on("select2:unselect", function (e) {
    var sales_order_id = e.params.data.id;
    $('table#pos_table tbody').find('tr').each( function(){
        if (typeof($(this).data('so_id')) !== 'undefined' 
            && $(this).data('so_id') == sales_order_id) {
            $(this).remove();
        pos_total_row();
        }
    });
});

$(document).on('click', '#add_expense', function(){
    $.ajax({
        url: '/expenses/create',
        data: { 
            location_id: $('#select_location_id').val()
        },
        dataType: 'html',
        success: function(result) {
            $('#expense_modal').html(result);
            $('#expense_modal').modal('show');
        },
    });
});

$(document).on('shown.bs.modal', '#expense_modal', function(){
    $('#expense_transaction_date').datetimepicker({
        format: moment_date_format + ' ' + moment_time_format,
        ignoreReadonly: true,
    });
    $('#expense_modal .paid_on').datetimepicker({
        format: moment_date_format + ' ' + moment_time_format,
        ignoreReadonly: true,
    });
    $(this).find('.select2').select2();
    $('#add_expense_modal_form').validate();
});

$(document).on('hidden.bs.modal', '#expense_modal', function(){
    $(this).html('');
});

$(document).on('submit', 'form#add_expense_modal_form', function(e) {
    e.preventDefault();
    var data = $(this).serialize();

    $.ajax({
        method: 'POST',
        url: $(this).attr('action'),
        dataType: 'json',
        data: data,
        success: function(result) {
            if (result.success == true) {
                $('#expense_modal').modal('hide');
                toastr.success(result.msg);
            } else {
                toastr.error(result.msg);
            }
        },
    });
});

// Receive Customer Payment from POS
// Customer purchase history (last sales) on POS
$(document).on('click', '#pos-customer-purchase-history', function (e) {
    e.preventDefault();
    var customer_id = $('select#customer_id').val();
    if (!customer_id) {
        toastr.warning(
            (typeof LANG !== 'undefined' && LANG.select_customer_for_history) ||
                'Select a customer to view purchase history.'
        );
        return;
    }

    var $modal = $('#pos_customer_purchase_history_modal');
    if (!$modal.length) {
        return;
    }

    var $loading = $modal.find('.pos-cust-history-loading');
    var $empty = $modal.find('.pos-cust-history-empty');
    var $wrap = $modal.find('.pos-cust-history-table-wrap');
    var $tbody = $modal.find('.pos-cust-history-table tbody');
    var $subtitle = $modal.find('.pos-cust-history-subtitle');

    $tbody.empty();
    $empty.addClass('hide');
    $wrap.addClass('hide');
    $loading.removeClass('hide');
    $subtitle.text('');

    var customerName =
        $('#customer_id option:selected').text() ||
        $('select#customer_id').find('option:selected').text() ||
        '';
    // Select2 may put name in rendered text
    if ((!customerName || customerName === 'Add a customer') && $('.pos-customer-bar .select2-selection__rendered').length) {
        customerName = $('.pos-customer-bar .select2-selection__rendered').text().trim();
    }
    $subtitle.text(customerName);

    $modal.modal('show');

    $.ajax({
        method: 'GET',
        url: '/contacts/purchase-history/' + customer_id,
        data: { limit: 15 },
        dataType: 'json',
    })
        .done(function (res) {
            $loading.addClass('hide');
            if (res.customer && res.customer.name) {
                var sub = res.customer.name;
                if (res.customer.mobile) {
                    sub += ' · ' + res.customer.mobile;
                }
                $subtitle.text(sub);
            }
            var rows = (res && res.history) || [];
            if (!rows.length) {
                $empty.removeClass('hide');
                return;
            }
            $wrap.removeClass('hide');
            rows.forEach(function (sale, idx) {
                var rowId = 'pos-hist-prod-' + sale.id + '-' + idx;
                var payClass = '';
                if (sale.payment_status === 'paid') {
                    payClass = 'label-success';
                } else if (sale.payment_status === 'partial') {
                    payClass = 'label-warning';
                } else if (sale.payment_status === 'due') {
                    payClass = 'label-danger';
                } else {
                    payClass = 'label-default';
                }
                var itemLabel =
                    (typeof LANG !== 'undefined' && LANG.items_count
                        ? String(LANG.items_count).replace(':count', sale.product_count)
                        : sale.product_count + ' item(s)');

                var productsHtml = '';
                if (sale.products && sale.products.length) {
                    productsHtml =
                        '<table class="table table-condensed table-striped" style="margin:0;background:transparent;">' +
                        '<thead><tr><th>Product</th><th>SKU</th><th class="text-right">Qty</th><th class="text-right">Total</th></tr></thead><tbody>';
                    sale.products.forEach(function (p) {
                        productsHtml +=
                            '<tr>' +
                            '<td>' +
                            $('<div>').text(p.name || '').html() +
                            '</td>' +
                            '<td>' +
                            $('<div>').text(p.sku || '—').html() +
                            '</td>' +
                            '<td class="text-right">' +
                            $('<div>').text(p.qty_formatted || p.qty || '').html() +
                            '</td>' +
                            '<td class="text-right">' +
                            $('<div>').text(p.line_total_formatted || '').html() +
                            '</td>' +
                            '</tr>';
                    });
                    productsHtml += '</tbody></table>';
                } else {
                    productsHtml = '<p class="text-muted" style="margin:0;">—</p>';
                }

                var invoiceHtml = '—';
                if (sale.invoice_no && sale.id) {
                    var printHref =
                        (window.base_path || '') + '/sells/' + sale.id + '/print';
                    invoiceHtml =
                        '<a href="' +
                        printHref +
                        '" class="print-invoice-link pos-cust-history-invoice" title="' +
                        ((typeof LANG !== 'undefined' && LANG.print_invoice) || 'Print Invoice') +
                        '">' +
                        '<i class="fa fa-print"></i> ' +
                        $('<div>').text(sale.invoice_no).html() +
                        '</a>';
                } else if (sale.invoice_no) {
                    invoiceHtml =
                        '<strong>' + $('<div>').text(sale.invoice_no).html() + '</strong>';
                }

                var tr =
                    '<tr class="pos-cust-history-row" data-target="#' +
                    rowId +
                    '">' +
                    '<td class="text-center"><i class="fa fa-chevron-right pos-hist-chevron"></i></td>' +
                    '<td>' +
                    invoiceHtml +
                    '</td>' +
                    '<td>' +
                    $('<div>').text(sale.transaction_date_formatted || '').html() +
                    (sale.location
                        ? '<br><small class="text-muted">' +
                          $('<div>').text(sale.location).html() +
                          '</small>'
                        : '') +
                    '</td>' +
                    '<td><span class="label ' +
                    payClass +
                    '">' +
                    $('<div>').text(sale.payment_status_label || sale.payment_status || '').html() +
                    '</span></td>' +
                    '<td class="text-right"><strong>' +
                    $('<div>').text(sale.final_total_formatted || '').html() +
                    '</strong></td>' +
                    '<td>' +
                    $('<div>').text(itemLabel).html() +
                    '</td>' +
                    '</tr>' +
                    '<tr id="' +
                    rowId +
                    '" class="pos-cust-history-detail hide">' +
                    '<td colspan="6" style="background:rgba(0,0,0,0.03);padding:8px 12px;">' +
                    productsHtml +
                    '</td></tr>';
                $tbody.append(tr);
            });
        })
        .fail(function (xhr) {
            $loading.addClass('hide');
            $empty.removeClass('hide');
            var msg =
                (xhr.responseJSON && xhr.responseJSON.message) ||
                (typeof LANG !== 'undefined' && LANG.something_went_wrong) ||
                'Something went wrong';
            toastr.error(msg);
        });
});

$(document).on('click', '#pos_customer_purchase_history_modal .pos-cust-history-row', function () {
    var target = $(this).data('target');
    var $detail = $(target);
    var $icon = $(this).find('.pos-hist-chevron');
    if ($detail.hasClass('hide')) {
        $detail.removeClass('hide');
        $icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
    } else {
        $detail.addClass('hide');
        $icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
    }
});

$(document).on('click', '#pos-receive-customer-payment', function() {
    var customer_id = $('select#customer_id').val();
    if (!customer_id) {
        if (typeof toastr !== 'undefined') {
            toastr.warning(typeof LANG !== 'undefined' && LANG.please_select_customer
                ? LANG.please_select_customer
                : 'Please select a customer');
        }
        return;
    }
    $.ajax({
        url: '/payments/pay-contact-due/' + customer_id + '?type=sell',
        dataType: 'html',
        success: function(result) {
            var $modal = $('#pos_pay_contact_due_modal');
            if ($modal.length && $modal.parent()[0] !== document.body) {
                $modal.appendTo('body');
            }
            $modal.html(result);
            var $form = $modal.find('#pay_contact_due_form');

            // Inject from_pos flag
            $form.append('<input type="hidden" name="from_pos" value="1">');

            // Init UI same as app.js
            __currency_convert_recursively($modal);
            $modal.find('.select2').select2({
                dropdownParent: $modal,
                width: '100%'
            });
            $modal.find('[name="paid_on"]').datetimepicker({
                format: moment_date_format + ' ' + moment_time_format,
                ignoreReadonly: true
            });
            $form.validate();

            // Direct handler fires before app.js document-delegated handler.
            // stopPropagation prevents app.js from doing a normal form POST.
            $form.on('submit', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Cash denomination validation (same as app.js)
                var $denomInput = $form.find('.enable_cash_denomination_for_payment_methods');
                if ($denomInput.length && $denomInput.val()) {
                    var payment_type = $form.find('.payment_types_dropdown').val();
                    var denomination_for_payment_types = JSON.parse($denomInput.val());
                    if (denomination_for_payment_types.includes(payment_type) && $form.find('.is_strict').length && $form.find('.is_strict').val() === '1') {
                        var payment_amount = __read_number($form.find('.payment_amount'));
                        var total_denomination = $form.find('input.denomination_total_amount').val();
                        if (payment_amount != total_denomination) {
                            $form.find('.cash_denomination_error').removeClass('hide');
                            return false;
                        }
                    }
                }
                $form.find('.cash_denomination_error').addClass('hide');

                // Use FormData to support file upload (document attachment)
                var formData = new FormData($form[0]);

                $.ajax({
                    method: 'POST',
                    url: $form.attr('action'),
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(result) {
                        if (result.success) {
                            $modal.modal('hide');
                            toastr.success(result.msg);
                            get_contact_due($('select#customer_id').val());
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            });

            $modal.modal('show');
        }
    });
});

$(document).on('hidden.bs.modal', '#pos_pay_contact_due_modal', function() {
    $(this).html('');
});

function get_contact_due(id) {
    if (!id) {
        set_payment_modal_prev_due(0, '');
        $('.contact_due_text').find('span').text('');
        $('.contact_due_text').addClass('hide');
        return;
    }

    var exclude_id = '';
    if ($('form#edit_pos_sell_form').length) {
        var action = ($('form#edit_pos_sell_form').attr('action') || '').replace(/\/$/, '');
        var parts = action.split('/');
        exclude_id = parts.length ? parts[parts.length - 1] : '';
        if (!/^\d+$/.test(String(exclude_id))) {
            exclude_id = $('[data-transaction_id]').first().attr('data-transaction_id')
                || $('[data-transaction_id]').first().data('transaction_id')
                || '';
        }
    }

    $.ajax({
        method: 'get',
        url: '/get-contact-due/' + id,
        data: exclude_id ? { exclude_transaction_id: exclude_id } : {},
        dataType: 'json',
        success: function(result) {
            // Support both new JSON payload and legacy plain-text response
            var formatted = '';
            var amount = 0;
            if (result && typeof result === 'object') {
                formatted = result.formatted || '';
                amount = parseFloat(result.amount);
                if (isNaN(amount)) {
                    amount = 0;
                }
            } else if (typeof result === 'string' && result !== '') {
                formatted = result;
                amount = __number_uf(result);
                if (isNaN(amount)) {
                    amount = 0;
                }
            }

            if (amount !== 0 && formatted !== '') {
                $('.contact_due_text').find('span').text(formatted);
                $('.contact_due_text').removeClass('hide');
            } else {
                $('.contact_due_text').find('span').text('');
                $('.contact_due_text').addClass('hide');
            }

            set_payment_modal_prev_due(amount, formatted);
        },
        error: function() {
            // Fallback: try text response (older backends)
            $.ajax({
                method: 'get',
                url: '/get-contact-due/' + id,
                dataType: 'text',
                success: function(result) {
                    if (result != '') {
                        $('.contact_due_text').find('span').text(result);
                        $('.contact_due_text').removeClass('hide');
                        set_payment_modal_prev_due(__number_uf(result), result);
                    } else {
                        $('.contact_due_text').find('span').text('');
                        $('.contact_due_text').addClass('hide');
                        set_payment_modal_prev_due(0, '');
                    }
                },
            });
        },
    });
}

/**
 * Fill customer name (+ mobile if known) on Pay modal top bar.
 */
function update_payment_modal_customer() {
    if (!$('#payment_modal_customer_name').length) {
        return;
    }
    var name = '';
    var extra = '';

    try {
        var s2 = $('#customer_id').select2('data');
        if (s2 && s2.length && s2[0]) {
            var d = s2[0];
            name = (d.text || d.name || '').toString().trim();
            if (d.mobile) {
                extra = String(d.mobile).trim();
            } else if (d.contact_id) {
                extra = String(d.contact_id).trim();
            }
        }
    } catch (e) {}

    if (!name) {
        name = ($('#customer_id option:selected').text() || '').toString().trim();
    }
    if (!name) {
        name = ($('#default_customer_name').val() || 'Walk-In Customer').toString().trim();
    }
    // Drop trailing (CO0001) style codes for cleaner display
    name = name.replace(/\s*\((?:CO|C)?\d+\)\s*$/i, '').trim() || name;

    $('#payment_modal_customer_name').text(name || '—');
    if (extra && extra !== name) {
        $('#payment_modal_customer_extra').text(' · ' + extra).show();
    } else {
        $('#payment_modal_customer_extra').text('').hide();
    }
}

function set_payment_modal_prev_due(amount, formatted) {
    amount = parseFloat(amount);
    if (isNaN(amount)) {
        amount = 0;
    }
    if (amount < 0) {
        amount = 0;
    }

    var display = formatted && formatted !== ''
        ? formatted
        : __currency_trans_from_en(amount, true);

    $('#payment_customer_prev_due').val(amount);
    var $span = $('span.payment_customer_prev_due');
    $span.text(display);
    $span.removeClass('text-success text-danger').addClass('text-warning');
}

function submitQuickContactForm(form) {
    var data = $(form).serialize();
    $.ajax({
        method: 'POST',
        url: $(form).attr('action'),
        dataType: 'json',
        data: data,
        beforeSend: function(xhr) {
            __disable_submit_button($(form).find('button[type="submit"]'));
        },
        success: function(result) {
            if (result.success == true) {
                var name = result.data.name;

                if (result.data.supplier_business_name) {
                    name += result.data.supplier_business_name;
                }
                
                $('select#customer_id').append(
                    $('<option>', { value: result.data.id, text: name })
                );
                $('select#customer_id')
                    .val(result.data.id)
                    .trigger('change');
                $('div.contact_modal').modal('hide');
                update_shipping_address(result.data)
                toastr.success(result.msg);
            } else {
                toastr.error(result.msg);
            }
        },
    });
}

$(document).on('click', '#send_for_sell_return', function(e) {
    var invoice_no = $('#send_for_sell_return_invoice_no').val();

    if (invoice_no) {
        $.ajax({
            method: 'get',
            url: /validate-invoice-to-return/ + encodeURI(invoice_no),
            dataType: 'json',
            success: function(result) {
                if (result.success == true) {
                    window.location = result.redirect_url ;
                } else {
                    toastr.error(result.msg);
                }
            },
        });
    }
})

    $(document).on('click', '#send_for_sercice_staff_replacement', function (e) {
        var invoice_no = $('#send_for_sell_service_staff_invoice_no').val();

        if (invoice_no) {
            $.ajax({
                method: 'get',
                url: /validate-invoice-to-service-staff-replacement/ + encodeURI(invoice_no),
                dataType: 'json',
                success: function (result) {
                    if (result.success == true) {
                        $('#service_staff_replacement').popover('hide');
                        $('#service_staff_modal').html(result.msg);
                        $('#service_staff_modal').modal('show');
                       
                    } else {
                        toastr.error(result.msg);
                    }
                },
            });
        }
    });

    $(document).on('shown.bs.modal', '#service_staff_modal', function () {
        $('#change_service_staff').validate();
    });


    $(document).on('submit', 'form#change_service_staff', function (e) {
        e.preventDefault();
        var data = $(this).serialize();

        $.ajax({
            method: 'POST',
            url: $(this).attr('action'),
            dataType: 'json',
            data: data,
            success: function (result) {
                if (result.success == true) {
                    $('#service_staff_modal').modal('hide');
                    toastr.success(result.msg);
                } else {
                    toastr.error(result.msg);
                }
            },
        });
    });

$(document).on('ifChanged', 'input[name="search_fields[]"]', function(event) {
    var search_fields = [];
    $('input[name="search_fields[]"]:checked').each(function() {
       search_fields.push($(this).val());
    });

    localStorage.setItem('pos_search_fields', search_fields);
});

function set_search_fields() {
    if ($('input[name="search_fields[]"]').length == 0) {
        return false;
    }

    var pos_search_fields = localStorage.getItem('pos_search_fields');

    if (pos_search_fields === null) {
        pos_search_fields = ['name', 'sku', 'lot'];
    }

    $('input[name="search_fields[]"]').each(function() {
        if (pos_search_fields.indexOf($(this).val()) >= 0) {
            $(this).iCheck('check');
        } else {
            $(this).iCheck('uncheck');
        }
    });
}

$(document).on('click', '#show_service_staff_availability', function(){
    loadServiceStaffAvailability();
})
$(document).on('click', '#refresh_service_staff_availability_status', function(){
    loadServiceStaffAvailability(false);
})
$(document).on('click', 'button.pause_resume_timer', function(e){
    $('.view_modal').find('.overlay').removeClass('hide');
    $.ajax({
        method: 'get',
        url: $(this).attr('data-href'),
        dataType: 'json',
        success: function(result) {
            loadServiceStaffAvailability(false);
        },
    });
})

$(document).on('click', '.mark_as_available', function(e){
    e.preventDefault()
    $('.view_modal').find('.overlay').removeClass('hide');
    $.ajax({
        method: 'get',
        url: $(this).attr('href'),
        dataType: 'json',
        success: function(result) {
            loadServiceStaffAvailability(false);
        },
    });
})
var service_staff_availability_interval = null;

function loadServiceStaffAvailability(show = true) {
    var location_id = $('[name="location_id"]').val();
    $.ajax({
        method: 'get',
        url: $('#show_service_staff_availability').attr('data-href'),
        dataType: 'html',
        data: {location_id: location_id},
        success: function(result) {
            $('.view_modal').html(result);
            if (show) {
                $('.view_modal').modal('show')

                //auto refresh service staff availabilty if modal is open
                service_staff_availability_interval = setInterval(function () {
                    loadServiceStaffAvailability(false);
                }, 60000);
            }
        },
    });
}

$(document).on('hidden.bs.modal', '.view_modal', function(){
    if (service_staff_availability_interval !== null) {
        clearInterval(service_staff_availability_interval);
    }
    service_staff_availability_interval = null;
});


$(document).on('change', '#res_waiter_id', function(e){
    var is_enable = $(this).find('option:selected').data('is_enable');

    if(is_enable){
        swal({
            text: LANG.enter_pin_here,
            buttons: true,
            dangerMode: true,
            content: {
                element: "input",
                attributes: {
                    placeholder: LANG.enter_pin_here,
                    type: "password",
                },
            },
        })
        .then((inputValue) => {
            if (inputValue !== null) {
                    $.ajax({
                        method: 'get',
                        url: '/modules/data/check-staff-pin',
                        dataType: 'json',
                        data: {
                        service_staff_pin: inputValue,
                        user_id : $("#res_waiter_id").val(),
                        },
                        success: (result) => {

                            if (result == false) {
                                toastr.error(LANG.authentication_failed);
                                $("#res_waiter_id").val('');
                            } else {
                                // AJAX request succeeded, resolve
                                toastr.success(LANG.authentication_successfull);
                            }
                        },
                    });
            } else {
                // Handle the "Cancel" action
                $("#res_waiter_id").val('');
            }
        });
        
    }
})

// update serial number of product item
function update_serial_no(){
    $('.product_row').each(function (index) {
        // Add the serial number to the first <td> of each row (index + 1 to start from 1)
        if ($(this).find('td:first').hasClass('serial_no')) {
            $(this).find('td:first').text(index + 1);
        }
    });
}


/**
 * Saves the serialized form data from #add_pos_sell_form into LocalStorage.
 * (Create POS only — edit page uses #edit_pos_sell_form and should not cache here.)
 */
function saveFormDataToLocalStorage() {


    // Check if global_is_clear_local_storage is true and reset it to false if so
    if(global_is_clear_local_storage){
        localStorage.setItem("pos_form_data_array", JSON.stringify([]));
        global_is_clear_local_storage = false;
        return false; // Exit the function early if global_is_clear_local_storage was true
    }

    // var storedArrayData = JSON.parse(localStorage.getItem("pos_form_data_array"));

    // console.log("All data afer clear:", storedArrayData);

    let form = $('form#add_pos_sell_form'); // Create POS only
    // Edit retrieved/parked sales use #edit_pos_sell_form — skip quietly
    if (form.length === 0) {
        return;
    }
    // Serialize form data into an array of objects: [{name: 'input_name', value: 'input_value'}, ...]
    let formArray = form.serializeArray();

    // Find if "price_total" already exists in the array
    let priceIndex = formArray.findIndex(item => item.name === "price_total");

    if (priceIndex !== -1) {
        // If exists, update the value
        formArray[priceIndex].value = get_subtotal();
    } else {
        // If not exists, push new entry
        formArray.push({ name: "price_total", value: get_subtotal() });
    }

    // Find if "order_tax" already exists in the array
    let textIndex = formArray.findIndex(item => item.name === "order_tax");

    if (priceIndex !== -1) {
        // If exists, update the value
        formArray[textIndex].value = $("#order_tax").text().trim();
    } else {
        // If not exists, push new entry
        formArray.push({ name: "order_tax", value: $("#order_tax").text().trim()});
    }

    // Find if "shipping_charges_amount" already exists in the array
    let shipping_charges_amount = formArray.findIndex(item => item.name === "shipping_charges_amount");

    if (priceIndex !== -1) {
        // If exists, update the value
        formArray[shipping_charges_amount].value = $("#shipping_charges_amount").text().trim();
    } else {
        // If not exists, push new entry
        formArray.push({ name: "shipping_charges_amount", value: $("#shipping_charges_amount").text().trim()});
    }

    // Find if "total_paying_input" already exists in the array
    let total_paying_input = formArray.findIndex(item => item.name === "total_paying_input");
    
    if (priceIndex !== -1) {
        // If exists, update the value
        formArray[total_paying_input].value = $("#total_paying_input").val();
    } else {
        // If not exists, push new entry
        formArray.push({ name: "total_paying_input", value: $("#total_paying_input").val()});
    }

    // Find if "change_return" already exists in the array
    let change_return = formArray.findIndex(item => item.name === "change_return");
    if (priceIndex !== -1) {
        // If exists, update the value
        formArray[change_return].value = $("#change_return").val();
    } else {
        // If not exists, push new entry
        formArray.push({ name: "change_return", value: $("#change_return").val()});
    }
     // Find if "in_balance_due" already exists in the array
     let in_balance_due = formArray.findIndex(item => item.name === "in_balance_due");
     if (priceIndex !== -1) {
         // If exists, update the value
         formArray[in_balance_due].value = $("#in_balance_due").val();
     } else {
         // If not exists, push new entry
         formArray.push({ name: "in_balance_due", value: $("#in_balance_due").val()});
     }
    // Store serialized data in LocalStorage as a JSON string
    localStorage.setItem("pos_form_data_array", JSON.stringify(formArray));

    // console.log("Form data successfully saved to LocalStorage.");
}

