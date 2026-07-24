var global_brand_id = null;
var global_p_category_id = null;
var global_is_clear_local_storage = false;

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
            if (data.supplier_business_name) template += data.supplier_business_name + '<br>';
            template += data.text + '<br>' + LANG.mobile + ': ' + data.mobile;
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
            var defId = String($('#default_customer_id').val() || '');
            if (!data.id || String(data.id) === defId) {
                return 'Search customers';
            }
            return data.text || data.name || 'Search customers';
        };
    }

    $('select#customer_id').select2(posCustomerSelect2Opts);

    // Keep placeholder styling when default/walk-in is selected
    if (isPosSaleTheme) {
        $('select#customer_id').on('change select2:select', function () {
            var defId = String($('#default_customer_id').val() || '');
            var val = String($(this).val() || '');
            var $rendered = $(this).next('.select2-container').find('.select2-selection__rendered');
            if (!val || val === defId) {
                $rendered
                    .text('Search customers')
                    .attr('title', 'Search customers')
                    .addClass('pos-cust-placeholder-text');
            } else {
                $rendered.removeClass('pos-cust-placeholder-text');
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
        posCustPickerSearch('');
    }

    function posCustPickerClose() {
        $('#pos_customer_picker').removeClass('is-open').attr('aria-hidden', 'true');
        $('.pos-customer-bar').removeClass('is-picking');
    }

    function posCustPickerSearch(term) {
        var $list = $('#pos_customer_picker_list');
        if (!$list.length) return;
        $list.html('<div class="pos-customer-picker-loading">Searching…</div>');
        $('#pos_customer_picker_add').attr('data-name', term || '');
        var label = term ? ("+ Add '" + term + "' as a new customer") : '+ Add as a new customer';
        $('#pos_customer_picker_add .pos-cust-add-label').text(label);

        $.ajax({
            url: '/contacts/customers',
            dataType: 'json',
            data: { q: term || '' },
            success: function (data) {
                if (!data || !data.length) {
                    $list.html('<div class="pos-customer-picker-empty">No customers found</div>');
                    return;
                }
                var html = '';
                data.forEach(function (row) {
                    var displayName = row.supplier_business_name || row.name || row.text || '';
                    var initials = posCustomerInitials(displayName);
                    var color = posCustomerAvatarColor(displayName);
                    var subParts = [];
                    if (row.contact_id) subParts.push(row.contact_id);
                    if (row.mobile) subParts.push(row.mobile);
                    var sub = subParts.join(' | ');
                    html +=
                        '<button type="button" class="pos-customer-picker-item" data-id="' + row.id + '" ' +
                        'data-text="' + $('<div>').text(row.text || displayName).html() + '" ' +
                        'data-name="' + $('<div>').text(displayName).html() + '" ' +
                        'data-pay_term_number="' + (row.pay_term_number || '') + '" ' +
                        'data-pay_term_type="' + (row.pay_term_type || '') + '" ' +
                        'data-balance="' + (row.balance || '') + '">' +
                            '<div class="pos-cust-avatar" style="background:' + color + '">' + initials + '</div>' +
                            '<div class="pos-cust-meta">' +
                                '<div class="pos-cust-name">' + $('<div>').text(displayName).html() +
                                ' <span class="pos-cust-tag">All customers</span></div>' +
                                (sub ? '<div class="pos-cust-sub">' + $('<div>').text(sub).html() + '</div>' : '') +
                            '</div>' +
                        '</button>';
                });
                $list.html(html);
            },
            error: function () {
                $list.html('<div class="pos-customer-picker-empty">Could not load customers</div>');
            }
        });
    }

    // when a real customer is picked, show their name (not "Search customers")
    function posCustPickerSelect($btn) {
        var id = $btn.data('id');
        var text = $btn.attr('data-text') || $btn.data('name') || '';
        if (!id) return;
        if ($('#customer_id option[value="' + id + '"]').length === 0) {
            $('#customer_id').append($('<option>', { value: id, text: text }));
        }
        $('#customer_id').val(String(id)).trigger('change');
        var $rendered = $('#customer_id').next('.select2-container').find('.select2-selection__rendered');
        var defId = String($('#default_customer_id').val() || '');
        if (String(id) === defId) {
            $rendered.text('Search customers').attr('title', 'Search customers').addClass('pos-cust-placeholder-text');
        } else {
            $rendered.text(text).attr('title', text).removeClass('pos-cust-placeholder-text').css('color', '#f2f2f2');
        }
        var payNum = $btn.attr('data-pay_term_number');
        var payType = $btn.attr('data-pay_term_type');
        if (payNum) { $('input#pay_term_number').val(payNum); } else { $('input#pay_term_number').val(''); }
        if (payType) {
            $('#add_sell_form select[name="pay_term_type"]').val(payType);
            $('#edit_sell_form select[name="pay_term_type"]').val(payType);
        } else {
            $('#add_sell_form select[name="pay_term_type"]').val('');
            $('#edit_sell_form select[name="pay_term_type"]').val('');
        }
        var balance = $btn.attr('data-balance');
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
        posCustPickerClose();
    }

    if (isPosSaleTheme) {
        // Block Select2 popup; open in-cart picker instead (open only — no toggle race)
        $('#customer_id').on('select2:opening', function (e) {
            e.preventDefault();
            posCustPickerOpen();
        });

        $(document).on('click', '.pos-customer-bar .select2-container, .pos-customer-bar .input-group-addon', function (e) {
            e.preventDefault();
            e.stopPropagation();
            posCustPickerOpen();
        });

        $(document).on('input', '#pos_customer_picker_input', function () {
            var term = $(this).val();
            clearTimeout(posCustPickerTimer);
            posCustPickerTimer = setTimeout(function () { posCustPickerSearch(term); }, 200);
        });

        $(document).on('click', '.pos-customer-picker-item', function (e) {
            e.preventDefault();
            e.stopPropagation();
            posCustPickerSelect($(this));
        });

        $(document).on('click', '#pos_customer_picker_add.add_new_customer', function (e) {
            e.stopPropagation();
            // existing add_new_customer handler opens modal; just close picker
            posCustPickerClose();
        });

        $(document).on('click', '#pos_customer_picker', function (e) {
            e.stopPropagation();
        });

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

    if ($('#search_product').length) {
        //Add Product
        $('#search_product')
            .autocomplete({
                delay: 1000,
                source: function(request, response) {
                    var price_group = '';
                    var search_fields = [];
                    $('.search_fields:checked').each(function(i){
                      search_fields[i] = $(this).val();
                    });

                    if ($('#price_group').length > 0) {
                        price_group = $('#price_group').val();
                    }
                    
                    //If default price group present
                    if ($('#default_price_group').length > 0 && price_group === '') {
                        price_group = $('#default_price_group').val();
                    }

                    //If types of service selected give more priority
                    if ($('#types_of_service_price_group').length > 0 && 
                        $('#types_of_service_price_group').val()) {
                        price_group = $('#types_of_service_price_group').val();
                    }
                    
                    var customer_id = $('select#customer_id').val();
                    var is_direct_sell = false;
                    if ($('input[name="is_direct_sale"]').length > 0 &&
                        $('input[name="is_direct_sale"]').val() == 1) {
                        is_direct_sell = true;
                    }
                    
                    var disable_qty_alert = false;
                    if ($('#disable_qty_alert').length) {
                        disable_qty_alert = true;
                    }
                    
                    var is_sales_order = $('#sale_type').length && $('#sale_type').val() == 'sales_order' ? true : false;
                    
                    var is_draft = false;
                    if($('#status') && ($('#status').val()=='quotation' || 
                    $('#status').val()=='draft')) {
                        is_draft = true;
                    }
                    
                    var is_serial_no = false;
                    if ($('input[name="is_serial_no"]').length > 0 &&
                        $('input[name="is_serial_no"]').val() == 1) {
                        is_serial_no = true;
                    }
                    
                    $.getJSON(
                        '/products/list',
                        {
                            price_group: price_group,
                            location_id: $('input#location_id').val(),
                            term: request.term,
                            not_for_selling: 0,
                            search_fields: search_fields,
                            auto_add_single: true,
                            product_row: $('input#product_row_count').val(),
                            customer_id: customer_id,
                            is_direct_sell: is_direct_sell,
                            is_serial_no: is_serial_no,
                            is_sales_order: is_sales_order,
                            disable_qty_alert: disable_qty_alert,
                            is_draft: is_draft
                        },
                        function(data) {
                            // Check if auto-add is enabled (single product found)
                            if (data.auto_add && data.row_data) {
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
                                } else {
                                    toastr.error(data.row_data.msg);
                                }
                                // Return a special marker to indicate auto-add was handled
                                response([{auto_added: true}]);
                            } else {
                                // Normal autocomplete response
                                response(data.products || data);
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
    }

    //Update line total and check for quantity not greater than max quantity
    $('table#pos_table tbody').on('change', 'input.pos_quantity', function() {
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
    $('table#pos_table tbody').on('change', 'input.pos_unit_price', function() {
        var unit_price = __read_number($(this));
        var tr = $(this).parents('tr');

        //calculate discounted unit price
        var discounted_unit_price = calculate_discounted_unit_price(tr);

        var tax_rate = tr
            .find('select.tax_id')
            .find(':selected')
            .data('rate');
        var quantity = __read_number(tr.find('input.pos_quantity'));

        var unit_price_inc_tax = __add_percent(discounted_unit_price, tax_rate);
        var line_total = quantity * unit_price_inc_tax;

        __write_number(tr.find('input.pos_unit_price_inc_tax'), unit_price_inc_tax);
        __write_number(tr.find('input.pos_line_total'), line_total);
        tr.find('span.pos_line_total_text').text(__currency_trans_from_en(line_total, true));
        pos_each_row(tr);
        pos_total_row();
        round_row_to_iraqi_dinnar(tr);
    });

    //If change in tax rate then update unit price according to it.
    $('table#pos_table tbody').on('change', 'select.tax_id', function() {
        var tr = $(this).parents('tr');

        var tax_rate = tr
            .find('select.tax_id')
            .find(':selected')
            .data('rate');
        var unit_price_inc_tax = __read_number(tr.find('input.pos_unit_price_inc_tax'));

        var discounted_unit_price = __get_principle(unit_price_inc_tax, tax_rate);
        var unit_price = get_unit_price_from_discounted_unit_price(tr, discounted_unit_price);
        __write_number(tr.find('input.pos_unit_price'), unit_price);
        pos_each_row(tr);
    });

    //If change in unit price including tax, update unit price
    $('table#pos_table tbody').on('change', 'input.pos_unit_price_inc_tax', function() {
        var unit_price_inc_tax = __read_number($(this));

        if (iraqi_selling_price_adjustment) {
            unit_price_inc_tax = round_to_iraqi_dinnar(unit_price_inc_tax);
            __write_number($(this), unit_price_inc_tax);
        }

        var tr = $(this).parents('tr');

        var tax_rate = tr
            .find('select.tax_id')
            .find(':selected')
            .data('rate');
        var quantity = __read_number(tr.find('input.pos_quantity'));

        var line_total = quantity * unit_price_inc_tax;
        var discounted_unit_price = __get_principle(unit_price_inc_tax, tax_rate);
        var unit_price = get_unit_price_from_discounted_unit_price(tr, discounted_unit_price);

        __write_number(tr.find('input.pos_unit_price'), unit_price);
        __write_number(tr.find('input.pos_line_total'), line_total, false);
        tr.find('span.pos_line_total_text').text(__currency_trans_from_en(line_total, true));

        pos_each_row(tr);
        pos_total_row();
    });

    //Change max quantity rule if lot number changes
    $('table#pos_table tbody').on('change', 'select.lot_number', function() {
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

    //Change in row discount type or discount amount
    $('table#pos_table tbody').on(
        'change',
        'select.row_discount_type, input.row_discount_amount',
        function() {
            var tr = $(this).parents('tr');

            //calculate discounted unit price
            var discounted_unit_price = calculate_discounted_unit_price(tr);

            var tax_rate = tr
                .find('select.tax_id')
                .find(':selected')
                .data('rate');
            var quantity = __read_number(tr.find('input.pos_quantity'));

            var unit_price_inc_tax = __add_percent(discounted_unit_price, tax_rate);
            var line_total = quantity * unit_price_inc_tax;

            __write_number(tr.find('input.pos_unit_price_inc_tax'), unit_price_inc_tax);
            __write_number(tr.find('input.pos_line_total'), line_total, false);
            tr.find('span.pos_line_total_text').text(__currency_trans_from_en(line_total, true));
            pos_each_row(tr);
            pos_total_row();
            round_row_to_iraqi_dinnar(tr);
        }
    );

    //Remove row on click on remove row
    $('table#pos_table tbody').on('click', '.pos_remove_row', function() {
        $(this)
            .parents('tr')
            .remove();
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
        return pos_form_obj.attr('action');
    }

    function pos_serialize_as_create(status) {
        var data = pos_form_obj.serialize();
        // Drop method override so we POST create, not PUT update
        data = data.replace(/(^|&)_method=[^&]*/g, '').replace(/^&/, '');
        data = data + '&status=' + status;
        return data;
    }

    //Save invoice as draft
    $('button#pos-draft').click(function() {
        //Check if product is present or not.
        if ($('table#pos_table tbody').find('.product_row').length <= 0) {
            toastr.warning(LANG.no_products_added);
            return false;
        }

        var is_valid = isValidPosForm();
        if (is_valid != true) {
            return;
        }

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
                    if (typeof loadPosDraftsPanel === 'function') {
                        loadPosDraftsPanel();
                    }
                } else {
                    toastr.error(result.msg);
                }
            },
        });
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

    //Finalize invoice, open payment modal
    $(document).on('click', 'button.pos-finalize, button.pos-pay-cta', function(e) {
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
        $paymentModal.modal('show');
        return false;
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
        pos_form_obj.submit();
        return false;
    });

    $('#modal_payment').one('shown.bs.modal', function() {
        $('#modal_payment')
            .find('input')
            .filter(':visible:first')
            .focus()
            .select();
        if ($('form#edit_pos_sell_form').length == 0) {
            $(this).find('#method_0').change();
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
        
            payment_method_dropdown.val(pay_method);
            payment_method_dropdown.change();
        if (pay_method == 'card') {
            $('div#card_details_modal').modal('show');
        } else {
            pos_form_obj.submit();
        }
    });

    $('div#card_details_modal').on('shown.bs.modal', function(e) {
        $('input#card_number').focus();
    });

    // Keep suspend modal on body so Save/Close clicks work (not clipped by cart panel)
    if ($('#confirmSuspendModal').length) {
        $('#confirmSuspendModal').appendTo('body');
    }

    $('div#confirmSuspendModal').on('shown.bs.modal', function(e) {
        $(this)
            .find('textarea')
            .focus();
    });

    //on save card details
    $('button#pos-save-card').click(function() {
        $('input#card_number_0').val($('#card_number').val());
        $('input#card_holder_name_0').val($('#card_holder_name').val());
        $('input#card_transaction_number_0').val($('#card_transaction_number').val());
        $('select#card_type_0').val($('#card_type').val());
        $('input#card_month_0').val($('#card_month').val());
        $('input#card_year_0').val($('#card_year').val());
        $('input#card_security_0').val($('#card_security').val());

        $('div#card_details_modal').modal('hide');
        pos_form_obj.submit();
    });

    $(document).off('click.posSuspend', 'button#pos-suspend').on('click.posSuspend', 'button#pos-suspend', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('input#is_suspend').val(1);
        $('div#confirmSuspendModal').modal('hide');
        // Submit after modal hide starts; keep is_suspend=1 until serialize in submitHandler
        setTimeout(function() {
            pos_form_obj.submit();
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

    $('button#add-payment-row').click(function() {
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

                    var total_payable = __read_number($('input#final_total_input'));
                    var total_paying = __read_number($('input#total_paying_input'));
                    var b_due = total_payable - total_paying;
                    $(appended)
                        .find('input.payment-amount')
                        .focus();
                    $(appended)
                        .find('input.payment-amount')
                        .last()
                        .val(__currency_trans_from_en(b_due, false))
                        .change()
                        .select();
                    __select2($(appended).find('.select2'));
                    $(appended).find('#method_' + row_index).change();
                    $('#payment_row_index').val(parseInt(row_index) + 1);
                }
            },
        });
    });

    $(document).on('click', '.remove_payment_row', function() {
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(willDelete => {
            if (willDelete) {
                $(this)
                    .closest('.payment_row')
                    .remove();
                calculate_balance_due();
            }
        });
    });

    pos_form_validator = pos_form_obj.validate({
        submitHandler: function(form) {
            // var total_payble = __read_number($('input#final_total_input'));
            // var total_paying = __read_number($('input#total_paying_input'));
            var cnf = true;

            // Suspend / park: skip balance-due confirm
            if ($('input#is_suspend').val() == '1') {
                cnf = true;
            } else if ($('input#in_balance_due').val() >= 0.5) {
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

                var data = $(form).serialize();
                data = data + '&status=final';
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

                            reset_pos_form();

                            //Check if enabled or not
                            if (result.receipt && result.receipt.is_enabled) {
                                pos_print(result.receipt);
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

    $(document).on('change', '.payment-amount', function() {
        calculate_balance_due();
    });

    //Update discount
    $('button#posEditDiscountModalUpdate').click(function() {

        //if discount amount is not valid return false
        if (!$("#discount_amount_modal").valid()) {
            return false;
        }
        //Close modal
        $('div#posEditDiscountModal').modal('hide');

        //Update values
        $('input#discount_type').val($('select#discount_type_modal').val());
        __write_number($('input#discount_amount'), __read_number($('input#discount_amount_modal')));

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

    //Shipping
    $('button#posShippingModalUpdate').click(function() {
        //Close modal
        $('div#posShippingModal').modal('hide');

        //update shipping details
        $('input#shipping_details').val($('#shipping_details_modal').val());

        $('input#shipping_address').val($('#shipping_address_modal').val());
        $('input#shipping_status').val($('#shipping_status_modal').val());
        $('input#delivered_to').val($('#delivered_to_modal').val());
        $('input#delivery_person').val($('#delivery_person_modal').val());

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
            // $('.select2-selection__rendered').css('padding-right', '150px');
    });

    $(document).on('shown.bs.modal', '.row_edit_product_price_model', function() {
        $('.row_edit_product_price_model')
            .find('input')
            .filter(':visible:first')
            .focus()
            .select();
    });

    //Update Order tax
    $('button#posEditOrderTaxModalUpdate').click(function() {
        //Close modal
        $('div#posEditOrderTaxModal').modal('hide');

        var tax_obj = $('select#order_tax_modal');
        var tax_id = tax_obj.val();
        var tax_rate = tax_obj.find(':selected').data('rate');

        $('input#tax_rate_id').val(tax_id);

        __write_number($('input#tax_calculation_amount'), tax_rate);
        pos_total_row();
    });

    $(document).on('click', '.add_new_customer', function() {
        $('#customer_id').select2('close');
        var name = $(this).data('name');
        $('.contact_modal')
            .find('input#name')
            .val(name);
        $('.contact_modal')
            .find('select#contact_type')
            .val('customer')
            .closest('div.contact_type_div')
            .addClass('hide');
        $('.contact_modal').modal('show');
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

    $('.contact_modal').on('hidden.bs.modal', function() {
        $('form#quick_add_contact')
            .find('button[type="submit"]')
            .removeAttr('disabled');
        $('form#quick_add_contact')[0].reset();
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
    //Datetime picker
    $('#transaction_date').datetimepicker({
        format: moment_date_format + ' ' + moment_time_format,
        ignoreReadonly: true,
    });

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

    //Show product list — default: All Categories active + all products
    if ($('#pos_defer_product_list').length && $('#pos_defer_product_list').val() == '1') {
        // Categories-first UI: wait for category click / search
    } else {
        if (typeof pos_set_active_category_tile === 'function') {
            pos_set_active_category_tile(global_p_category_id || 'all');
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
    $('.main-category').on('click', function(e) {
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
            if ($('#pos_table tbody tr').length > 0) {
                $('#pos_table tbody tr:last')
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
    });

    //Quick add product
    $(document).on('click', 'button.pos_add_quick_product', function() {
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

        var sp_element = tr.find('input.pos_unit_price');
        __write_number(sp_element, unit_sp);

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
    $.ajax({
        method: 'GET',
        url: '/sells/pos/get-recent-transactions',
        data: { status: status , transaction_sub_type: transaction_sub_type},
        dataType: 'html',
        success: function(result) {
            element_obj.html(result);
            __currency_convert_recursively(element_obj);
        },
    });
}

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
            var $badge = $('#pos_drafts_count');
            $badge.text(count).toggleClass('tw-hidden', count === 0);
            $('#pos_drafts_header_toggle').toggleClass('pos-drafts-header-btn--has-items', count > 0);
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
    }

    $('#pos_drafts_header_toggle').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var $panel = $('#pos_drafts_panel');
        if ($panel.hasClass('pos-drafts-dropdown--open')) {
            closePosDraftsDropdown();
        } else {
            openPosDraftsDropdown();
        }
    });

    $('#pos_drafts_close').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        closePosDraftsDropdown();
    });

    $(document).on('click', function(e) {
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

    $('#pos_drafts_refresh').on('click', function(e) {
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
    });
});

//variation_id is null when weighing_scale_barcode is used.

/**
 * Common function to insert product row into POS table
 * @param {object} result - The result object containing html_content and other data
 */
function pos_insert_product_row(result) {
    pos_play_success_sound();
    var product_row = $('input#product_row_count').val();
    $('table#pos_table tbody')
        .append(result.html_content)
        .find('input.pos_quantity');
    pos_sync_empty_state();
    //increment row count
    $('input#product_row_count').val(parseInt(product_row) + 1);
    var this_row = $('table#pos_table tbody')
        .find('tr')
        .last();
    pos_each_row(this_row);

    //For initial discount if present
    var line_total = __read_number(this_row.find('input.pos_line_total'));
    this_row.find('span.pos_line_total_text').text(line_total);

    pos_total_row();

    //Check if multipler is present then multiply it when a new row is added.
    if(__getUnitMultiplier(this_row) > 1){
        this_row.find('select.sub_unit').trigger('change');
    }

    if (result.enable_sr_no == '1') {
        var new_row = $('table#pos_table tbody')
            .find('tr')
            .last();
        new_row.find('.row_edit_product_price_model').modal('show');
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
        $('table#pos_table tbody')
            .find('tr')
            .last()
            .find('td:first')
            .append(result.html_modifier);
    }

    //scroll bottom of items list
    $(".pos_product_div").animate({ scrollTop: $('.pos_product_div').prop("scrollHeight")}, 1000);
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
            $('#pos_table tbody')
                .find('tr')
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

function pos_product_row(variation_id = null, purchase_line_id = null, weighing_scale_barcode = null, quantity = 1) {

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
        $('#pos_table tbody')
            .find('tr')
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

                if (
                    row_v_id == variation_id &&
                    enable_sr_no !== '1' &&
                    !modifiers_exist &&
                    !is_added
                ) {
                    add_via_ajax = false;
                    is_added = true;

                    //Increment product quantity
                    qty_element = $(this).find('.pos_quantity');
                    var qty = __read_number(qty_element);
                    __write_number(qty_element, qty + 1);
                    qty_element.change();

                    round_row_to_iraqi_dinnar($(this));

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
                is_draft: is_draft
            },
            dataType: 'json',
            success: function(result) {
                if (result.success) {
                    pos_insert_product_row(result);
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

//Update values for each row
function pos_each_row(row_obj) {
    var unit_price = __read_number(row_obj.find('input.pos_unit_price'));

    var discounted_unit_price = calculate_discounted_unit_price(row_obj);
    var tax_rate = row_obj
        .find('select.tax_id')
        .find(':selected')
        .data('rate');

    var unit_price_inc_tax =
        discounted_unit_price + __calculate_amount('percentage', tax_rate, discounted_unit_price);
    __write_number(row_obj.find('input.pos_unit_price_inc_tax'), unit_price_inc_tax);

    var discount = __read_number(row_obj.find('input.row_discount_amount'));

    if (discount > 0) {
        var qty = __read_number(row_obj.find('input.pos_quantity'));
        var line_total = qty * unit_price_inc_tax;
        __write_number(row_obj.find('input.pos_line_total'), line_total);
    }

    //var unit_price_inc_tax = __read_number(row_obj.find('input.pos_unit_price_inc_tax'));

    __write_number(row_obj.find('input.item_tax'), unit_price_inc_tax - discounted_unit_price);
}

function pos_total_row() {
    var total_quantity = 0;
    var price_total = get_subtotal();
    $('table#pos_table tbody tr').each(function() {
        total_quantity = total_quantity + __read_number($(this).find('input.pos_quantity'));
    });

    //updating shipping charges
    $('span#shipping_charges_amount').text(
        __currency_trans_from_en(__read_number($('input#shipping_charges_modal')), false)
    );

    $('span.total_quantity').each(function() {
        $(this).html(__number_f(total_quantity));
    });

    //$('span.unit_price_total').html(unit_price_total);
    $('span.price_total').html(__currency_trans_from_en(price_total, false));
    calculate_billing_details(price_total);

    if (
        $('input[name="is_serial_no"]').length > 0 &&
        $('input[name="is_serial_no"]').val() == 1
    ) {
        update_serial_no();
    }
    // store on any update
    saveFormDataToLocalStorage();

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

        $('#packing_charge_text').text(__currency_trans_from_en(packing_charge, false));
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

    //Check if edit form then don't update price.
    if ($('form#edit_pos_sell_form').length == 0 && $('form#edit_sell_form').length == 0) {
        __write_number($('.payment-amount').first(), total_payable_rounded);
    }

    $(document).trigger('invoice_total_calculated');

    calculate_balance_due();
}

function pos_discount(total_amount) {
    var calculation_type = $('#discount_type').val();
    var calculation_amount = __read_number($('#discount_amount'));

    var discount = __calculate_amount(calculation_type, calculation_amount, total_amount);

    $('span#total_discount').text(__currency_trans_from_en(discount, false));

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

    $('span#order_tax').text(__currency_trans_from_en(order_tax, false));

    return order_tax;
}

function calculate_balance_due() {
    var total_payable = __read_number($('#final_total_input'));
    var total_paying = 0;
    $('#payment_rows_div')
        .find('.payment-amount')
        .each(function() {
            if (parseFloat($(this).val())) {
                total_paying += __read_number($(this));
            }
        });
    var bal_due = total_payable - total_paying;
    var change_return = 0;

    //change_return
    if (bal_due < 0 || Math.abs(bal_due) < 0.05) {
        __write_number($('input#change_return'), bal_due * -1);
        $('span.change_return_span').text(__currency_trans_from_en(bal_due * -1, true));
        change_return = bal_due * -1;
        bal_due = 0;
    } else {
        __write_number($('input#change_return'), 0);
        $('span.change_return_span').text(__currency_trans_from_en(0, true));
        change_return = 0;
        
    }

    if (change_return !== 0) {
        $('#change_return_payment_data').removeClass('hide');
    } else {
        $('#change_return_payment_data').addClass('hide');
    }

    __write_number($('input#total_paying_input'), total_paying);
    $('span.total_paying').text(__currency_trans_from_en(total_paying, true));

    __write_number($('input#in_balance_due'), bal_due);
    $('span.balance_due').text(__currency_trans_from_en(bal_due, true));

    __highlight(bal_due * -1, $('span.balance_due'));
    __highlight(change_return * -1, $('span.change_return_span'));
    // store payment details
    saveFormDataToLocalStorage();
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
            var href = row.getAttribute('data-href') || '';
            var updateUrl = href.replace(/\/edit(\?.*)?$/, '$1');
            if (!updateUrl || updateUrl === href) {
                updateUrl = (window.base_path || '') + '/pos/' + tid;
            }
            // Keep query-less update URL
            updateUrl = updateUrl.split('?')[0];
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

        // Close retrieve panel
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

function reset_pos_form(){

	//If on edit page then redirect to Add POS page
	if($('form#edit_pos_sell_form').length > 0){
		setTimeout(function() {
			window.location = $("input#pos_redirect_url").val();
		}, 4000);
		return true;
	}
	
    //reset all repair defects tags
    if ($("#repair_defects").length > 0) {
        tagify_repair_defects.removeAllTags();
    }

	if(pos_form_obj[0]){
		pos_form_obj[0].reset();
	}
	if(sell_form[0]){
		sell_form[0].reset();
	}
	set_default_customer();
	set_location();

	$('tr.product_row').remove();
	$('span.total_quantity, span.price_total, span#total_discount, span#order_tax, span#total_payable, span#shipping_charges_amount, span#loyalty_amount_display').text(0);
	$('span.total_payable_span', 'span.total_paying', 'span.balance_due').text(0);

	$('#modal_payment').find('.remove_payment_row').each( function(){
		$(this).closest('.payment_row').remove();
	});

    if ($('#is_credit_sale').length) {
        $('#is_credit_sale').val(0);
    }

	//Reset discount
	__write_number($('input#discount_amount'), $('input#discount_amount').data('default'));
	$('input#discount_type').val($('input#discount_type').data('default'));

	//Reset tax rate
	$('input#tax_rate_id').val($('input#tax_rate_id').data('default'));
	__write_number($('input#tax_calculation_amount'), $('input#tax_calculation_amount').data('default'));

	$('select.payment_types_dropdown').val('cash').trigger('change');
	$('#price_group').trigger('change');

	//Reset shipping
	__write_number($('input#shipping_charges'), $('input#shipping_charges').data('default'));
	$('input#shipping_details').val($('input#shipping_details').data('default'));
    $('input#shipping_address, input#shipping_status, input#delivered_to').val('');
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

    //repair module extra  fields reset
    if ($('#repair_device_id').length > 0) {
        $('#repair_device_id').val('').trigger('change');
    }

    //Status is hidden in sales order
    if ($('#status').length > 0 && $('#status').is(":visible")) {
        $('#status').val('').trigger('change');
    }
    if ($('#transaction_date').length > 0) {
        $('#transaction_date').data("DateTimePicker").date(moment());
    }
    if ($('.paid_on').length > 0) {
        $('.paid_on').data("DateTimePicker").date(moment());
    }
    if ($('#commission_agent').length > 0) {
        $('#commission_agent').val('').trigger('change');
    } 

    //reset contact due
    $('.contact_due_text').find('span').text('');
    $('.contact_due_text').addClass('hide');

    $(document).trigger('sell_form_reset');

    // Set global_is_clear_local_storage to true to clear local storage
    global_is_clear_local_storage = true;
    saveFormDataToLocalStorage();
}

function set_default_customer() {
    var default_customer_id = $('#default_customer_id').val();
    var default_customer_name = $('#default_customer_name').val();
    var default_customer_balance = $('#default_customer_balance').val();
    var default_customer_address = $('#default_customer_address').val();
    var exists = default_customer_id ? $('select#customer_id option[value=' + default_customer_id + ']').length : 0;
    if (exists == 0 && default_customer_id) {
        $('select#customer_id').append(
            $('<option>', { value: default_customer_id, text: default_customer_name })
        );
    }
    $('#advance_balance_text').text(__currency_trans_from_en(default_customer_balance), true);
    $('#advance_balance').val(default_customer_balance);
    $('#shipping_address_modal').val(default_customer_address);
    if (default_customer_address) {
        $('#shipping_address').val(default_customer_address);
    }
    $('select#customer_id')
        .val(default_customer_id)
        .trigger('change');

    // POS theme: show "Search customers" instead of Walk-In name (value stays walk-in)
    if ($('body').hasClass('pos-sale-theme')) {
        setTimeout(function () {
            var $rendered = $('#customer_id').next('.select2-container').find('.select2-selection__rendered');
            $rendered
                .text('Search customers')
                .attr('title', 'Search customers')
                .addClass('pos-cust-placeholder-text');
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

function pos_print(receipt) {
    //If printer type then connect with websocket
    if (receipt.print_type == 'printer') {
        var content = receipt;
        content.type = 'print-receipt';

        //Check if ready or not, then print.
        if (socket != null && socket.readyState == 1) {
            socket.send(JSON.stringify(content));
        } else {
            initializeSocket();
            setTimeout(function() {
                socket.send(JSON.stringify(content));
            }, 700);
        }

    } else if (receipt.html_content != '') {
        var title = document.title;
        if (typeof receipt.print_title != 'undefined') {
            document.title = receipt.print_title;
        }

        //If printer type browser then print content
        $('#receipt_section').html(receipt.html_content);
        __currency_convert_recursively($('#receipt_section'));
        __print_receipt('receipt_section');

        setTimeout(function() {
            document.title = title;
        }, 1200);
    }
}

function calculate_discounted_unit_price(row) {
    var this_unit_price = __read_number(row.find('input.pos_unit_price'));
    var row_discounted_unit_price = this_unit_price;
    var row_discount_type = row.find('select.row_discount_type').val();
    var row_discount_amount = __read_number(row.find('input.row_discount_amount'));
    if (row_discount_amount) {
        if (row_discount_type == 'fixed') {
            row_discounted_unit_price = this_unit_price - row_discount_amount;
        } else {
            row_discounted_unit_price = __substract_percent(this_unit_price, row_discount_amount);
        }
    }

    return row_discounted_unit_price;
}

function get_unit_price_from_discounted_unit_price(row, discounted_unit_price) {
    var this_unit_price = discounted_unit_price;
    var row_discount_type = row.find('select.row_discount_type').val();
    var row_discount_amount = __read_number(row.find('input.row_discount_amount'));
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
$('table#pos_table tbody').on('change', 'input.pos_line_total', function() {

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
    $.ajax({
        url: $(this).attr('href') + "?check_location=true",
        dataType: 'json',
        success: function(result) {
            if (result.success == 1) {
                //Check if enabled or not
                if (result.receipt.is_enabled) {
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

$(document).on('change', '.payment_types_dropdown', function(e) {
    var default_accounts = $('select#select_location_id').length ? 
                $('select#select_location_id')
                .find(':selected')
                .data('default_payment_accounts') : $('#location_id').data('default_payment_accounts');
    var payment_type = $(this).val();
    var payment_row = $(this).closest('.payment_row');
    if (payment_type && payment_type != 'advance') {
        var default_account = default_accounts && default_accounts[payment_type]['account'] ? 
            default_accounts[payment_type]['account'] : '';
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
        amount_element.rules('add', {
            'max-value': max_value,
            messages: {
                'max-value': msg,
            },
        });
        if (account_dropdown) {
            account_dropdown.prop('disabled', true);
            account_dropdown.closest('.form-group').addClass('hide');
        }
    } else {
        amount_element.rules("remove", "max-value");
        if (account_dropdown) {
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
$(document).on('click', '#pos-receive-customer-payment', function() {
    var customer_id = $('select#customer_id').val();
    if (!customer_id) {
        return;
    }
    $.ajax({
        url: '/payments/pay-contact-due/' + customer_id + '?type=sell',
        dataType: 'html',
        success: function(result) {
            $('#pos_pay_contact_due_modal').html(result);
            var $modal = $('#pos_pay_contact_due_modal');
            var $form = $modal.find('#pay_contact_due_form');

            // Inject from_pos flag
            $form.append('<input type="hidden" name="from_pos" value="1">');

            // Init UI same as app.js
            __currency_convert_recursively($modal);
            $modal.find('.select2').select2();
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
    $.ajax({
        method: 'get',
        url: /get-contact-due/ + id,
        dataType: 'text',
        success: function(result) {
            if (result != '') {
                $('.contact_due_text').find('span').text(result);
                $('.contact_due_text').removeClass('hide');
            } else {
                $('.contact_due_text').find('span').text('');
                $('.contact_due_text').addClass('hide');
            }
        },
    });
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

    let form = $('form#add_pos_sell_form'); // Select the form by ID
    // Check if the form exists in the DOM
    if (form.length === 0) {
        console.error("Error: Form #add_pos_sell_form not found.");
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
