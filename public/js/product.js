//This file contains all functions used products tab

$(document).ready(function() {
    $(document).on('ifChecked', 'input#enable_stock', function() {
        $('div#alert_quantity_div').show();
        $('div#quick_product_opening_stock_div').show();
        $('div#product_opening_stock_div').show();

        //Enable expiry selection
        if ($('#expiry_period_type').length) {
            $('#expiry_period_type').removeAttr('disabled');
        }

        if ($('#opening_stock_button').length) {
            $('#opening_stock_button').removeAttr('disabled');
        }
    });
    $(document).on('ifUnchecked', 'input#enable_stock', function() {
        $('div#alert_quantity_div').hide();
        $('div#quick_product_opening_stock_div').hide();
        $('div#product_opening_stock_div').hide();
        $('input#alert_quantity').val(0);

        //Disable expiry selection
        if ($('#expiry_period_type').length) {
            $('#expiry_period_type')
                .val('')
                .change();
            $('#expiry_period_type').attr('disabled', true);
        }
        if ($('#opening_stock_button').length) {
            $('#opening_stock_button').attr('disabled', true);
        }
    });

    //Start For product type single

    // Recalculate x Margin(%) from purchase + selling price (create + edit)
    window.update_profit_margin = function() {
        var $purchase = $('input#single_dpp');
        if (!$purchase.length) {
            $purchase = $('table.add-product-price-table input.dpp').first();
        }
        var $selling = $('input#single_dsp');
        if (!$selling.length) {
            $selling = $('table.add-product-price-table input.dsp').first();
        }
        var $margin = $('input#profit_percent');
        if (!$purchase.length || !$selling.length || !$margin.length) {
            return;
        }

        var purchase_price = __read_number($purchase);
        var selling_price = __read_number($selling);
        purchase_price = purchase_price == undefined ? 0 : purchase_price;
        selling_price = selling_price == undefined ? 0 : selling_price;

        if (purchase_price > 0) {
            var margin = __get_rate(purchase_price, selling_price);
            __write_number($margin, margin);
        } else {
            __write_number($margin, 0);
        }
    };

    function get_single_tax_rate() {
        var tax_rate = $('select#tax').find(':selected').data('rate');
        return tax_rate == undefined ? 0 : tax_rate;
    }

    // Purchase Exc. tax changed
    $(document).on('change blur keyup', 'input#single_dpp, table.add-product-price-table input.dpp', function(e) {
        var purchase_exc_tax = __read_number($(this));
        purchase_exc_tax = purchase_exc_tax == undefined ? 0 : purchase_exc_tax;

        var purchase_inc_tax = __add_percent(purchase_exc_tax, get_single_tax_rate());
        var $inc = $('input#single_dpp_inc_tax');
        if (!$inc.length) {
            $inc = $('table.add-product-price-table input.dpp_inc_tax').first();
        }
        __write_number($inc, purchase_inc_tax);
        window.update_profit_margin();
    });

    //If tax rate is changed
    $(document).on('change', 'select#tax', function() {
        if ($('select#type').val() == 'single') {
            var purchase_exc_tax = __read_number($('input#single_dpp'));
            purchase_exc_tax = purchase_exc_tax == undefined ? 0 : purchase_exc_tax;

            var tax_rate = get_single_tax_rate();
            var purchase_inc_tax = __add_percent(purchase_exc_tax, tax_rate);
            __write_number($('input#single_dpp_inc_tax'), purchase_inc_tax);

            var selling_price = __read_number($('input#single_dsp'));
            var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
            __write_number($('input#single_dsp_inc_tax'), selling_price_inc_tax);
            window.update_profit_margin();
        }
    });

    // Purchase Inc. tax changed
    $(document).on('change blur keyup', 'input#single_dpp_inc_tax, table.add-product-price-table input.dpp_inc_tax', function(e) {
        var purchase_inc_tax = __read_number($(this));
        purchase_inc_tax = purchase_inc_tax == undefined ? 0 : purchase_inc_tax;

        var purchase_exc_tax = __get_principle(purchase_inc_tax, get_single_tax_rate());
        var $exc = $('input#single_dpp');
        if (!$exc.length) {
            $exc = $('table.add-product-price-table input.dpp').first();
        }
        __write_number($exc, purchase_exc_tax);
        window.update_profit_margin();
    });

    // Margin field itself does not rewrite purchase/selling prices
    $(document).on('change', 'input#profit_percent', function(e) {
        // no-op
    });

    // Selling Exc. tax changed
    $(document).on('change blur keyup', 'input#single_dsp, table.add-product-price-table input.dsp', function(e) {
        var selling_price = __read_number($(this));
        var selling_price_inc_tax = __add_percent(selling_price, get_single_tax_rate());
        __write_number($('input#single_dsp_inc_tax'), selling_price_inc_tax);
        window.update_profit_margin();
    });

    // Selling Inc. tax changed
    $(document).on('change blur keyup', 'input#single_dsp_inc_tax', function(e) {
        var selling_price_inc_tax = __read_number($(this));
        var selling_price = __get_principle(selling_price_inc_tax, get_single_tax_rate());
        __write_number($('input#single_dsp'), selling_price);
        window.update_profit_margin();
    });

    // Recalculate margin when create/edit page loads
    setTimeout(function() {
        if ($('input#single_dpp').length || $('table.add-product-price-table input.dpp').length) {
            window.update_profit_margin();
        }
    }, 300);

    if ($('#product_add_form').length) {
        $('form#product_add_form').validate({
            rules: {
                    sku: {
                    remote: {
                        url: '/products/check_product_sku',
                        type: 'post',
                        dataType: 'json',
                        data: {
                            sku: function() {
                                return $('#sku').val();
                            },
                            product_id: function() {
                                if ($('#product_id').length > 0) {
                                    return $('#product_id').val();
                                } else {
                                    return '';
                                }
                            },
                            _token: function () {
                                return $('meta[name="csrf-token"]').attr('content')
                                    || $('input[name="_token"]').val()
                                    || '';
                            },
                        },
                    },
                },
                name: {
                    remote: {
                        url: '/products/check_product_name',
                        type: 'post',
                        data: {
                            name: function() {
                                return $('#name').val();
                            },
                            product_id: function() {
                                if ($('#product_id').length > 0) {
                                    return $('#product_id').val();
                                } else {
                                    return '';
                                }
                            },
                        },
                    },
                },
                expiry_period: {
                    required: {
                        depends: function(element) {
                            return (
                                $('#expiry_period_type')
                                    .val()
                                    .trim() != ''
                            );
                        },
                    },
                },
            },
            messages: {
                sku: {
                    remote: LANG.sku_already_exists || LANG.barcode_already_used || 'SKU / barcode already exists',
                },
                name: {
                    remote: LANG.name_already_exists,
                },
            },
            invalidHandler: function (event, validator) {
                if (validator.errorMap && validator.errorMap.sku) {
                    var code = $.trim($('#sku').val() || '');
                    showBarcodeTakenAlert(code);
                }
            },
        });
    }

    $(document).on('click', '.submit_product_form', function(e) {
        e.preventDefault();

        var is_valid_product_form = true;

        var variation_skus = [];

        var submit_type  = $(this).attr('value');

        $('#product_form_part').find('.input_sub_sku').each( function(){
            var element = $(this);
            var row_variation_id = '';
            if ($(this).closest('tr').find('.row_variation_id')) {
                row_variation_id = $(this).closest('tr').find('.row_variation_id').val();
            }

            variation_skus.push({sku: element.val(), variation_id: row_variation_id});
            
        });

        if (variation_skus.length > 0) {
            $.ajax({
                method: 'post',
                url: '/products/validate_variation_skus',
                data: { skus: variation_skus},
                success: function(result) {
                    if (result.success == true) {
                        $('#submit_type').val(submit_type);
                        if (!assertSkuUniqueThenSubmit()) {
                            return false;
                        }
                    } else {
                        toastr.error(__translate('skus_already_exists', {sku: result.sku}));
                        showBarcodeTakenAlert(result.sku);
                        return false;
                    }
                },
            });
        } else {
            $('#submit_type').val(submit_type);
            assertSkuUniqueThenSubmit();
        }
        
    });

    function barcodeTakenMessage(code) {
        return (LANG.barcode_already_used || 'This barcode/SKU is already used by another product: :code').replace(':code', code || '');
    }

    function showBarcodeTakenAlert(code) {
        var msg = barcodeTakenMessage(code);
        if (typeof toastr !== 'undefined') {
            toastr.error(msg, '', { timeOut: 8000, closeButton: true, progressBar: true });
        }
        window.alert(msg);
    }
    window.showBarcodeTakenAlert = showBarcodeTakenAlert;

    function skuCheckPayload(code) {
        return {
            sku: code,
            product_id: $('input[name="product_id"]').val() || $('#product_id').val() || '',
            _token: $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val() || ''
        };
    }

    function isSkuAvailableResponse(res) {
        if (res === true || res === 'true' || res === 1 || res === '1') {
            return true;
        }
        if (typeof res === 'string' && $.trim(res) === 'true') {
            return true;
        }
        return false;
    }

    function assertSkuUniqueThenSubmit() {
        var $form = $('form#product_add_form');
        var sku = $.trim($('#sku').val() || '');
        if (sku) {
            var skuOk = true;
            $.ajax({
                url: '/products/check_product_sku',
                method: 'POST',
                async: false,
                dataType: 'json',
                data: skuCheckPayload(sku)
            }).done(function (res) {
                skuOk = isSkuAvailableResponse(res);
            }).fail(function () {
                skuOk = false;
            });
            if (!skuOk) {
                showBarcodeTakenAlert(sku);
                return false;
            }
        }
        if ($form.valid()) {
            $form.submit();
            return true;
        }
        if ($form.validate().errorMap && $form.validate().errorMap.sku) {
            showBarcodeTakenAlert(sku);
        }
        return false;
    }

    $(document).on('blur', '#sku', function () {
        var code = $.trim($(this).val() || '');
        if (!code) {
            return;
        }
        $.ajax({
            url: '/products/check_product_sku',
            method: 'POST',
            dataType: 'json',
            data: skuCheckPayload(code)
        }).done(function (res) {
            if (!isSkuAvailableResponse(res)) {
                showBarcodeTakenAlert(code);
            }
        });
    });

    //Start for product type Variable
    window.update_variable_profit_margin = function(row) {
        var purchase_price = __read_number(row.find('input.variable_dpp'));
        var selling_price = __read_number(row.find('input.variable_dsp'));
        purchase_price = purchase_price == undefined ? 0 : purchase_price;
        selling_price = selling_price == undefined ? 0 : selling_price;

        if (purchase_price > 0) {
            __write_number(row.find('input.variable_profit_percent'), __get_rate(purchase_price, selling_price));
        } else {
            __write_number(row.find('input.variable_profit_percent'), 0);
        }
    };

    //If purchase price exc tax is changed
    $(document).on('change blur keyup', 'input.variable_dpp', function(e) {
        var tr_obj = $(this).closest('tr');

        var purchase_exc_tax = __read_number($(this));
        purchase_exc_tax = purchase_exc_tax == undefined ? 0 : purchase_exc_tax;

        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;

        var purchase_inc_tax = __add_percent(purchase_exc_tax, tax_rate);
        __write_number(tr_obj.find('input.variable_dpp_inc_tax'), purchase_inc_tax);
        window.update_variable_profit_margin(tr_obj);
    });

    //If purchase price inc tax is changed — purchase only, do not touch selling
    $(document).on('change blur keyup', 'input.variable_dpp_inc_tax', function(e) {
        var tr_obj = $(this).closest('tr');

        var purchase_inc_tax = __read_number($(this));
        purchase_inc_tax = purchase_inc_tax == undefined ? 0 : purchase_inc_tax;

        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;

        var purchase_exc_tax = __get_principle(purchase_inc_tax, tax_rate);
        __write_number(tr_obj.find('input.variable_dpp'), purchase_exc_tax);
        window.update_variable_profit_margin(tr_obj);
    });

    $(document).on('change', 'input.variable_profit_percent', function(e) {
        // no-op: purchase and selling stay independent
    });

    $(document).on('change blur keyup', 'input.variable_dsp', function(e) {
        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;

        var tr_obj = $(this).closest('tr');
        var selling_price = __read_number($(this));
        var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
        __write_number(tr_obj.find('input.variable_dsp_inc_tax'), selling_price_inc_tax);
        window.update_variable_profit_margin(tr_obj);
    });

    $(document).on('change blur keyup', 'input.variable_dsp_inc_tax', function(e) {
        var tr_obj = $(this).closest('tr');
        var selling_price_inc_tax = __read_number($(this));

        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;

        var selling_price = __get_principle(selling_price_inc_tax, tax_rate);
        __write_number(tr_obj.find('input.variable_dsp'), selling_price);
        window.update_variable_profit_margin(tr_obj);
    });

    // Auto-calculate margin on page load for variable products
    setTimeout(function() {
        $('input.variable_dpp').each(function() {
            window.update_variable_profit_margin($(this).closest('tr'));
        });
    }, 300);

    $(document).on('click', '.add_variation_value_row', function() {
        var variation_row_index = $(this)
            .closest('.variation_row')
            .find('.row_index')
            .val();
        var variation_value_row_index = $(this)
            .closest('table')
            .find('tr:last .variation_row_index')
            .val();

        if (
            $(this)
                .closest('.variation_row')
                .find('.row_edit').length >= 1
        ) {
            var row_type = 'edit';
        } else {
            var row_type = 'add';
        }

        var table = $(this).closest('table');

        $.ajax({
            method: 'GET',
            url: '/products/get_variation_value_row',
            data: {
                variation_row_index: variation_row_index,
                value_index: variation_value_row_index,
                row_type: row_type,
            },
            dataType: 'html',
            success: function(result) {
                if (result) {
                    table.append(result);
                    toggle_dsp_input();
                }
            },
        });
    });
    $(document).on('change', '.variation_template_values', function() {
        var tr_obj = $(this).closest('tr');
        var val = $(this).val();
        tr_obj.find('.variation_value_row').each(function(){
            if(val.includes($(this).attr('data-variation_value_id'))) {
                $(this).removeClass('hide');
                $(this).find('.is_variation_value_hidden').val(0);
            } else {
                $(this).addClass('hide');
                $(this).find('.is_variation_value_hidden').val(1);
            }
        })
    });
    $(document).on('change', '.variation_template', function() {
        tr_obj = $(this).closest('tr');

        if ($(this).val() !== '') {
            tr_obj.find('input.variation_name').val(
                $(this)
                    .find('option:selected')
                    .text()
            );

            var template_id = $(this).val();
            var row_index = $(this)
                .closest('tr')
                .find('.row_index')
                .val();
            $.ajax({
                method: 'POST',
                url: '/products/get_variation_template',
                dataType: 'json',
                data: { template_id: template_id, row_index: row_index },
                success: function(result) {
                    if (result) {
                        if(result.values.length > 0) {
                            tr_obj.find('.variation_template_values').select2();
                            tr_obj.find('.variation_template_values').empty();
                            tr_obj.find('.variation_template_values').select2({data: result.values, closeOnSelect: false});
                            tr_obj.find('.variation_template_values_div').removeClass('hide');
                            tr_obj.find('.variation_template_values').select2('open');
                        } else {
                            tr_obj.find('.variation_template_values_div').addClass('hide');
                        }
                        tr_obj
                            .find('table.variation_value_table')
                            .find('tbody')
                            .html(result.html);

                        toggle_dsp_input();
                    }
                },
            });
        }
    });

    $(document).on('click','.delete_complete_row', function(){
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(willDelete => {
            if (willDelete) {
                $(this)
                .closest('.variation_row')
                .remove();
            }
        });
    });

    $(document).on('click', '.remove_variation_value_row', function() {
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(willDelete => {
            if (willDelete) {
                var count = $(this)
                    .closest('table')
                    .find('.remove_variation_value_row').length;
                if (count === 1) {
                    $(this)
                        .closest('.variation_row')
                        .remove();
                } else {
                    $(this)
                        .closest('tr')
                        .remove();
                }
            }
        });
    });

    //If tax rate is changed
    $(document).on('change', 'select#tax', function() {
        if ($('select#type').val() == 'variable') {
            var tax_rate = $('select#tax')
                .find(':selected')
                .data('rate');
            tax_rate = tax_rate == undefined ? 0 : tax_rate;

            $('table.variation_value_table > tbody').each(function() {
                $(this)
                    .find('tr')
                    .each(function() {
                        var purchase_exc_tax = __read_number($(this).find('input.variable_dpp'));
                        purchase_exc_tax = purchase_exc_tax == undefined ? 0 : purchase_exc_tax;

                        var purchase_inc_tax = __add_percent(purchase_exc_tax, tax_rate);
                        __write_number(
                            $(this).find('input.variable_dpp_inc_tax'),
                            purchase_inc_tax
                        );

                        var selling_price = __read_number($(this).find('input.variable_dsp'));
                        var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
                        __write_number(
                            $(this).find('input.variable_dsp_inc_tax'),
                            selling_price_inc_tax
                        );
                    });
            });
        }
    });
    //End for product type Variable
    $(document).on('change', '#tax_type', function(e) {
        toggle_dsp_input();
    });
    toggle_dsp_input();

    $(document).on('change', '#expiry_period_type', function(e) {
        if ($(this).val()) {
            $('input#expiry_period').prop('disabled', false);
        } else {
            $('input#expiry_period').val('');
            $('input#expiry_period').prop('disabled', true);
        }
    });

    $(document).on('click', 'a.view-product', function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('href'),
            dataType: 'html',
            success: function(result) {
                $('#view_product_modal')
                    .html(result)
                    .modal('show');
                __currency_convert_recursively($('#view_product_modal'));
            },
        });
    });
    var img_fileinput_setting = {
        showUpload: false,
        showPreview: true,
        showCancel: false,
        browseLabel: LANG.file_browse_label,
        removeLabel: LANG.remove,
        browseClass: 'btn btn-primary btn-sm',
        removeClass: 'btn btn-default btn-sm',
        previewSettings: {
            image: {
                width: '140px',
                height: '140px',
                'max-width': '140px',
                'max-height': '140px',
            },
        },
        previewFileIconSettings: {
            image: '<i class="fa fa-file-image-o"></i>',
        },
    };
    $('#upload_image').fileinput(img_fileinput_setting);

    function fixProductImagePreview() {
        $('.product-image-upload .file-preview-frame .kv-file-content').css({
            width: '140px',
            height: '140px',
            overflow: 'hidden',
            padding: 0,
            margin: 0,
        });
        $('.product-image-upload .file-preview-frame img.file-preview-image').css({
            width: '140px',
            height: '140px',
            maxWidth: '140px',
            maxHeight: '140px',
            objectFit: 'cover',
            objectPosition: 'center center',
            display: 'block',
            margin: 0,
        });
    }
    $('#upload_image')
        .on('fileloaded filebatchselected fileselect', function () {
            setTimeout(fixProductImagePreview, 50);
        })
        .on('filecleared fileclear', function () {
            /* no-op */
        });
    setTimeout(fixProductImagePreview, 100);

    if ($('textarea#product_description').length > 0) {
        tinymce.init({
            selector: 'textarea#product_description',
            height:250
        });
    }
});

function toggle_dsp_input() {
    var tax_type = $('#tax_type').val();
    if (tax_type == 'inclusive') {
        $('.dsp_label').each(function() {
            $(this).text(LANG.inc_tax);
        });
        $('#single_dsp').addClass('hide');
        $('#single_dsp_inc_tax').removeClass('hide');

        $('.add-product-price-table')
            .find('.variable_dsp_inc_tax')
            .each(function() {
                $(this).removeClass('hide');
            });
        $('.add-product-price-table')
            .find('.variable_dsp')
            .each(function() {
                $(this).addClass('hide');
            });
    } else if (tax_type == 'exclusive') {
        $('.dsp_label').each(function() {
            $(this).text(LANG.exc_tax);
        });
        $('#single_dsp').removeClass('hide');
        $('#single_dsp_inc_tax').addClass('hide');

        $('.add-product-price-table')
            .find('.variable_dsp_inc_tax')
            .each(function() {
                $(this).addClass('hide');
            });
        $('.add-product-price-table')
            .find('.variable_dsp')
            .each(function() {
                $(this).removeClass('hide');
            });
    }
}

function get_product_details(rowData) {
    var div = $('<div/>')
        .addClass('loading')
        .text('Loading...');

    $.ajax({
        url: '/products/' + rowData.id,
        dataType: 'html',
        success: function(data) {
            div.html(data).removeClass('loading');
        },
    });

    return div;
}

//Quick add unit
$(document).on('submit', 'form#quick_add_unit_form', function(e) {
    e.preventDefault();
    var form = $(this);
    var data = form.serialize();

    $.ajax({
        method: 'POST',
        url: $(this).attr('action'),
        dataType: 'json',
        data: data,
        beforeSend: function(xhr) {
            __disable_submit_button(form.find('button[type="submit"]'));
        },
        success: function(result) {
            if (result.success == true) {
                var newOption = new Option(result.data.short_name, result.data.id, true, true);
                // Append it to the select
                $('#unit_id')
                    .append(newOption)
                    .trigger('change');
                $('div.view_modal').modal('hide');
                toastr.success(result.msg);
            } else {
                toastr.error(result.msg);
            }
        },
    });
});

//Quick add brand
$(document).on('submit', 'form#quick_add_brand_form', function(e) {
    e.preventDefault();
    var form = $(this);
    var data = form.serialize();

    $.ajax({
        method: 'POST',
        url: $(this).attr('action'),
        dataType: 'json',
        data: data,
        beforeSend: function(xhr) {
            __disable_submit_button(form.find('button[type="submit"]'));
        },
        success: function(result) {
            if (result.success == true) {
                var newOption = new Option(result.data.name, result.data.id, true, true);
                // Append it to the select
                $('#brand_id')
                    .append(newOption)
                    .trigger('change');
                $('div.view_modal').modal('hide');
                toastr.success(result.msg);
            } else {
                toastr.error(result.msg);
            }
        },
    });
});

//Quick add sub category (needs a parent category selected first)
$(document).on('click', 'button.quick_add_sub_category', function(e) {
    e.preventDefault();
    var parent_id = $('#category_id').val();
    if (!parent_id) {
        toastr.error(LANG.please_select_category || 'Please select a category first');
        $('#category_id').select2('open');
        return;
    }
    var $container = $('.view_modal').first();
    if (!$container.length || $container.hasClass('in') || $container.data('loading')) {
        return;
    }
    $container.data('loading', true);
    $.ajax({
        url: $(this).data('href') + '&parent_id=' + encodeURIComponent(parent_id),
        dataType: 'html',
        success: function(result) {
            if (!$container.parent().is('body')) {
                $container.appendTo('body');
            }
            $container.html(result).modal('show');
        },
        complete: function() {
            $container.data('loading', false);
        },
    });
});

//Quick add child category (needs a sub category selected first)
$(document).on('click', 'button.quick_add_child_category', function(e) {
    e.preventDefault();
    var parent_id = $('#sub_category_id').val();
    if (!parent_id) {
        toastr.error(LANG.please_select_sub_category || 'Please select a sub category first');
        $('#sub_category_id').select2('open');
        return;
    }
    var $container = $('.view_modal').first();
    if (!$container.length || $container.hasClass('in') || $container.data('loading')) {
        return;
    }
    $container.data('loading', true);
    $.ajax({
        url: $(this).data('href') + '&parent_id=' + encodeURIComponent(parent_id),
        dataType: 'html',
        success: function(result) {
            if (!$container.parent().is('body')) {
                $container.appendTo('body');
            }
            $container.html(result).modal('show');
        },
        complete: function() {
            $container.data('loading', false);
        },
    });
});

//Quick add category / sub category / child category
$(document).on('submit', 'form#quick_add_category_form', function(e) {
    e.preventDefault();
    var form = $(this);

    $.ajax({
        method: 'POST',
        url: form.attr('action'),
        dataType: 'json',
        data: form.serialize(),
        beforeSend: function() {
            __disable_submit_button(form.find('button[type="submit"]'));
        },
        success: function(result) {
            if (result.success == true) {
                var cat = result.data;
                var parentId = String(cat.parent_id || '');
                if (parseInt(parentId, 10) > 0 && $('#child_category_id').length && parentId === String($('#sub_category_id').val() || '')) {
                    $('#child_category_id')
                        .append(new Option(cat.name, cat.id, true, true))
                        .trigger('change');
                } else if (parseInt(parentId, 10) > 0) {
                    $('#sub_category_id')
                        .append(new Option(cat.name, cat.id, true, true))
                        .trigger('change');
                } else {
                    $('#category_id')
                        .append(new Option(cat.name, cat.id, true, true))
                        .trigger('change');
                }
                $('div.view_modal').modal('hide');
                toastr.success(result.msg);
            } else {
                toastr.error(result.msg);
                form.find('button[type="submit"]').prop('disabled', false);
            }
        },
    });
});

$(document).on('click', 'button.apply-all', function(){
    var val = $(this).closest('.input-group').find('input').val();
    var target_class = $(this).data('target-class');
    $(this).closest('tbody').find('tr').each( function(){
        element =  $(this).find(target_class);
        element.val(val);
        element.change();
    });
});

// Variation image upload preview handler
$(document).on('change', '.variation-file-input', function() {
    var $input = $(this);
    var $container = $input.closest('.variation-image-upload');
    var $preview = $container.find('.variation-image-preview');
    var $badge = $container.find('.image-count-badge');
    var $btn = $container.find('.variation-upload-btn');
    var files = this.files;
    
    // Clear previous previews
    $preview.empty();
    
    if (files && files.length > 0) {
        // Show badge with count
        $badge.text(files.length).show();
        $btn.removeClass('tw-dw-btn-primary').addClass('tw-dw-btn-success');
        
        // Show preview container
        $preview.show();
        
        // Create preview for each file (max 4 shown)
        var maxPreview = Math.min(files.length, 4);
        for (var i = 0; i < maxPreview; i++) {
            var file = files[i];
            if (file.type.match('image.*')) {
                var reader = new FileReader();
                reader.onload = (function(file) {
                    return function(e) {
                        var $thumb = $('<div class="preview-thumb" style="display: inline-block; position: relative; margin: 2px;">' +
                            '<img src="' + e.target.result + '" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;">' +
                            '</div>');
                        $preview.append($thumb);
                    };
                })(file);
                reader.readAsDataURL(file);
            }
        }
        
        // Show +N if more than 4 files
        if (files.length > 4) {
            $preview.append('<span style="display: inline-block; padding: 10px; font-size: 12px; color: #666;">+' + (files.length - 4) + ' more</span>');
        }
    } else {
        // No files selected - reset
        $badge.hide();
        $btn.removeClass('tw-dw-btn-success').addClass('tw-dw-btn-primary');
        $preview.hide();
    }
});

// --- Packaging UoM helper (Add/Edit Product) ---
(function () {
    var uomLock = false;

    function uomEnabled() {
        var $cb = $('#enable_packaging_unit');
        if (!$cb.length) return false;
        return $cb.is(':checked');
    }

    function uomFactor() {
        var f = __read_number($('#packaging_conversion_factor'));
        return f && f > 0 ? f : 0;
    }

    function uomPackName() {
        var v = $('#packaging_unit_name').val();
        if (v === '__custom__') {
            return ($('#packaging_unit_custom_name').val() || 'Pack').toString().trim() || 'Pack';
        }
        return (v || 'Box').toString().trim();
    }

    function uomBaseLabel() {
        if (!$('#unit_id').val()) return 'Pcs';
        var text = ($('#unit_id option:selected').text() || 'Pcs').toString();
        var m = text.match(/\(([^)]+)\)\s*$/);
        return m ? m[1] : text.split(' ')[0] || 'Pcs';
    }

    function uomUpdateLabels() {
        var pack = uomPackName();
        var base = uomBaseLabel();
        $('.js-pack-unit-label').text(pack);
        $('.js-base-unit-label').text(base);
    }

    function uomToggleFields() {
        if (uomEnabled()) {
            $('#packaging_uom_fields').removeClass('hide');
            $('.js-pack-price-wrap').removeClass('hide');
            uomUpdateLabels();
            uomSyncFromMainPrices();
        } else {
            $('#packaging_uom_fields').addClass('hide');
            $('.js-pack-price-wrap').addClass('hide');
        }
    }

    // Box price fields live in the main price table; the main (per piece) fields are the source of truth.
    function uomSellIsInc() {
        return $('#tax_type').val() === 'inclusive';
    }

    function uomTaxRate() {
        return typeof get_single_tax_rate === 'function'
            ? get_single_tax_rate()
            : ($('select#tax').find(':selected').data('rate') || 0);
    }

    function uomSyncSellFromMain() {
        if (!uomEnabled() || uomLock || $('#uom_sell_pack').is(':focus')) return;
        var factor = uomFactor();
        var sellBase = __read_number(uomSellIsInc() ? $('input#single_dsp_inc_tax') : $('input#single_dsp'));
        uomLock = true;
        __write_number($('#uom_sell_pack'), factor > 0 ? (sellBase || 0) * factor : 0);
        uomLock = false;
    }

    function uomSyncPurchaseFromMain() {
        if (!uomEnabled() || uomLock || $('#uom_purchase_pack').is(':focus')) return;
        var factor = uomFactor();
        var purchaseBase = __read_number($('input#single_dpp'));
        uomLock = true;
        __write_number($('#uom_purchase_pack'), factor > 0 ? (purchaseBase || 0) * factor : 0);
        uomLock = false;
    }

    function uomSyncFromMainPrices() {
        uomSyncSellFromMain();
        uomSyncPurchaseFromMain();
    }

    function uomFromSellPack() {
        if (!uomEnabled() || uomLock) return;
        var factor = uomFactor();
        if (!(factor > 0)) return;
        uomLock = true;
        var pack = __read_number($('#uom_sell_pack')) || 0;
        var base = pack / factor;
        if (uomSellIsInc()) {
            __write_number($('input#single_dsp_inc_tax'), base);
            __write_number($('input#single_dsp'), __get_principle(base, uomTaxRate()));
        } else {
            __write_number($('input#single_dsp'), base);
            __write_number($('input#single_dsp_inc_tax'), __add_percent(base, uomTaxRate()));
        }
        uomLock = false;
        if (typeof window.update_profit_margin === 'function') {
            window.update_profit_margin();
        }
    }

    function uomFromPurchasePack() {
        if (!uomEnabled() || uomLock) return;
        var factor = uomFactor();
        if (!(factor > 0)) return;
        uomLock = true;
        var pack = __read_number($('#uom_purchase_pack')) || 0;
        var base = pack / factor;
        __write_number($('input#single_dpp'), base);
        var tax_rate = $('select#tax').find(':selected').data('rate') || 0;
        __write_number($('input#single_dpp_inc_tax'), __add_percent(base, tax_rate));
        uomLock = false;
        if (typeof window.update_profit_margin === 'function') {
            window.update_profit_margin();
        }
    }

    $(document).ready(function () {
        if (!$('#product_packaging_uom').length) return;

        function uomVisibilityByType() {
            var type = $('#type').val() || 'single';
            if (type === 'single') {
                $('#product_packaging_uom').show();
            } else {
                $('#product_packaging_uom').hide();
            }
        }

        uomVisibilityByType();
        uomToggleFields();
        uomUpdateLabels();

        $(document).on('change', '#type', uomVisibilityByType);

        $(document).ajaxComplete(function (event, xhr, settings) {
            if (settings && settings.url && String(settings.url).indexOf('product_form_part') !== -1) {
                setTimeout(function () {
                    uomUpdateLabels();
                    uomToggleFields();
                }, 50);
            }
        });

        $(document).on('ifChecked', '#enable_packaging_unit', function () {
            uomToggleFields();
        });
        $(document).on('ifUnchecked', '#enable_packaging_unit', function () {
            uomToggleFields();
        });
        $(document).on('change', '#enable_packaging_unit', function () {
            uomToggleFields();
        });

        $(document).on('change', '#packaging_unit_name', function () {
            if ($(this).val() === '__custom__') {
                $('#packaging_unit_custom_name').removeClass('hide').focus();
            } else {
                $('#packaging_unit_custom_name').addClass('hide');
            }
            $('#packaging_unit_id').val('');
            uomUpdateLabels();
        });

        $(document).on('input change', '#packaging_unit_custom_name', function () {
            uomUpdateLabels();
        });

        $(document).on('change', '#unit_id', function () {
            uomUpdateLabels();
            $('#packaging_unit_id').val('');
        });

        $(document).on('change keyup', '#packaging_conversion_factor', function () {
            uomSyncFromMainPrices();
        });

        $(document).on('keyup change', '#uom_sell_pack', uomFromSellPack);
        $(document).on('keyup change', '#uom_purchase_pack', uomFromPurchasePack);
        $(document).on('blur', '#uom_sell_pack, #uom_purchase_pack', function () {
            setTimeout(uomSyncFromMainPrices, 0);
        });
        $(document).on('change keyup', '#profit_percent, input#single_dpp_inc_tax, input#single_dsp_inc_tax, select#tax, #tax_type', function () {
            setTimeout(uomSyncFromMainPrices, 0);
        });

        // Keep sell and purchase packaging prices fully independent
        // Also recalculate x Margin(%) whenever top PO / sale price changes
        $(document).on('change keyup blur', 'input#single_dsp, table.add-product-price-table input.dsp', function () {
            if (typeof window.update_profit_margin === 'function') {
                window.update_profit_margin();
            }
            if (!uomEnabled() || uomLock) return;
            uomSyncSellFromMain();
        });
        $(document).on('change keyup blur', 'input#single_dpp, table.add-product-price-table input.dpp', function () {
            if (typeof window.update_profit_margin === 'function') {
                window.update_profit_margin();
            }
            if (!uomEnabled() || uomLock) return;
            uomSyncPurchaseFromMain();
        });
        $(document).on('change keyup blur', 'input#single_dpp_inc_tax, table.add-product-price-table input.dpp_inc_tax', function () {
            if (typeof window.update_profit_margin === 'function') {
                window.update_profit_margin();
            }
        });
        $(document).on('change keyup blur', 'input#single_dsp_inc_tax', function () {
            if (typeof window.update_profit_margin === 'function') {
                window.update_profit_margin();
            }
        });

        $(document).on('submit', 'form.product_form, form#product_add_form, form#product_edit_form', function (e) {
            if (!uomEnabled()) return;
            var factor = uomFactor();
            if (!(factor > 0)) {
                toastr.error('Enter conversion factor (e.g. 1 Box = 10 Pcs)');
                e.preventDefault();
                return false;
            }
            var name = uomPackName();
            if (!name) {
                toastr.error('Enter packaging unit name');
                e.preventDefault();
                return false;
            }
        });
    });
})();

// --- Additional barcodes / SKUs (Add/Edit Product) ---
(function () {
    function syncPackUnitIds() {
        var packId = $('#packaging_unit_id').val() || '';
        $('#product_barcodes_list .product-barcode-row').each(function () {
            var $row = $(this);
            if ($row.find('.product-barcode-type').val() === 'pack') {
                $row.find('.product-barcode-unit-id').val(packId);
            }
        });
    }

    function togglePackFields($row) {
        var isPack = $row.find('.product-barcode-type').val() === 'pack';
        $row.find('.product-barcode-pack-unit').toggleClass('hide', !isPack);
        if (isPack) {
            syncPackUnitIds();
        } else {
            $row.find('.product-barcode-unit-id').val('');
        }
    }

    function updateEmptyState() {
        var hasRows = $('#product_barcodes_list .product-barcode-row').length > 0;
        $('#product_barcodes_empty').toggleClass('hide', hasRows);
    }

    function nextIndex() {
        var max = -1;
        $('#product_barcodes_list .product-barcode-row').each(function () {
            var i = parseInt($(this).attr('data-index'), 10);
            if (!isNaN(i) && i > max) max = i;
        });
        return max + 1;
    }

    function addBarcodeRow() {
        var tplEl = document.getElementById('product_barcode_row_template');
        var tpl = '';
        if (tplEl && tplEl.content) {
            var tmp = document.createElement('div');
            tmp.appendChild(tplEl.content.cloneNode(true));
            tpl = tmp.innerHTML;
        }
        if (!tpl) {
            tpl = $('#product_barcode_row_template_html').html() || '';
        }
        if (!tpl) return;
        var idx = nextIndex();
        var html = tpl.split('__INDEX__').join(String(idx));
        var $row = $(html);
        $row.attr('data-index', idx);
        $('#product_barcodes_list').append($row);
        togglePackFields($row);
        updateEmptyState();
        $row.find('.product-barcode-input').focus();
    }

    $(document).ready(function () {
        if (!$('#product_additional_barcodes').length) return;

        $('#btn_add_product_barcode').on('click', function (e) {
            e.preventDefault();
            addBarcodeRow();
        });

        $(document).on('click', '.btn-remove-product-barcode', function (e) {
            e.preventDefault();
            $(this).closest('.product-barcode-row').remove();
            updateEmptyState();
        });

        $(document).on('change', '.product-barcode-type', function () {
            togglePackFields($(this).closest('.product-barcode-row'));
        });

        $(document).on('change', '#enable_packaging_unit, #packaging_unit_id, #packaging_unit_name, #packaging_conversion_factor', function () {
            syncPackUnitIds();
        });

        $('#product_barcodes_list .product-barcode-row').each(function () {
            togglePackFields($(this));
        });
        updateEmptyState();

        function csrfToken() {
            return $('meta[name="csrf-token"]').attr('content')
                || $('input[name="_token"]').val()
                || '';
        }

        function productIdForBarcodeCheck() {
            return $('input[name="product_id"]').val()
                || $('#product_id').val()
                || '';
        }

        function barcodeUsedOnThisForm($input) {
            var code = $.trim($input.val()).toLowerCase();
            if (!code) {
                return false;
            }
            var sku = $.trim($('#sku').val() || $('input[name="sku"]').val() || '').toLowerCase();
            if (sku && sku === code) {
                return true;
            }
            var hits = 0;
            $('#product_barcodes_list .product-barcode-input').each(function () {
                if ($.trim($(this).val()).toLowerCase() === code) {
                    hits++;
                }
            });
            return hits > 1;
        }

        function alertDuplicateBarcode($input, msg) {
            if (typeof window.showBarcodeTakenAlert === 'function') {
                var code = $.trim($input.val() || '');
                window.showBarcodeTakenAlert(code);
            } else if (typeof toastr !== 'undefined') {
                toastr.error(msg);
            } else {
                window.alert(msg);
            }
            $input.addClass('input-error').css('border-color', '#dd4b39');
            $input.focus();
        }

        function checkBarcodeUnique($input) {
            var code = $.trim($input.val());
            if (!code) {
                $input.css('border-color', '');
                return;
            }
            if (barcodeUsedOnThisForm($input)) {
                alertDuplicateBarcode(
                    $input,
                    (LANG.barcode_duplicate_on_form || 'This barcode is already entered on this product: :code').replace(':code', code)
                );
                return false;
            }
            $.ajax({
                url: '/products/check_product_sku',
                method: 'POST',
                dataType: 'json',
                data: {
                    sku: code,
                    product_id: productIdForBarcodeCheck(),
                    _token: csrfToken()
                }
            }).done(function (res) {
                var ok = (res === true || res === 'true' || res === 1 || res === '1');
                if (!ok) {
                    alertDuplicateBarcode(
                        $input,
                        (LANG.barcode_already_used || 'This barcode/SKU is already used by another product: :code').replace(':code', code)
                    );
                } else {
                    $input.css('border-color', '');
                }
            });
            return true;
        }

        $(document).on('blur', '.product-barcode-input', function () {
            checkBarcodeUnique($(this));
        });

        $(document).on('submit', 'form.product_form, form#product_add_form, form#product_edit_form', function (e) {
            var blocked = false;
            var $form = $(this);
            $('#product_barcodes_list .product-barcode-input').each(function () {
                var $inp = $(this);
                var code = $.trim($inp.val());
                if (!code) {
                    return;
                }
                if (barcodeUsedOnThisForm($inp)) {
                    alertDuplicateBarcode(
                        $inp,
                        (LANG.barcode_duplicate_on_form || 'This barcode is already entered on this product: :code').replace(':code', code)
                    );
                    blocked = true;
                    return false;
                }
                $.ajax({
                    url: '/products/check_product_sku',
                    method: 'POST',
                    async: false,
                    data: {
                        sku: code,
                        product_id: productIdForBarcodeCheck(),
                        _token: csrfToken()
                    }
                }).done(function (res) {
                    var ok = (res === true || res === 'true' || res === 1 || res === '1');
                    if (!ok) {
                        alertDuplicateBarcode(
                            $inp,
                            (LANG.barcode_already_used || 'This barcode/SKU is already used by another product: :code').replace(':code', code)
                        );
                        blocked = true;
                    }
                }).fail(function () {
                    alertDuplicateBarcode($inp, LANG.something_went_wrong || 'Could not verify barcode');
                    blocked = true;
                });
                if (blocked) {
                    return false;
                }
            });
            if (blocked) {
                e.preventDefault();
                e.stopImmediatePropagation();
                $form.find('button[type="submit"], .submit_product_form').prop('disabled', false);
                return false;
            }
        });
    });
})();

// --- Quick add supplier (Add/Edit Product) ---
(function () {
    var waitingForSupplier = false;

    $(document).on('click', '.product-quick-add-supplier', function () {
        var $modal = $('div.contact_modal').first();
        if (!$modal.length) {
            $modal = $('<div class="modal fade contact_modal" tabindex="-1" role="dialog"></div>').appendTo('body');
        }
        $modal.load($(this).data('href'), function () {
            waitingForSupplier = true;
            var $type = $modal.find('select#contact_type');
            if ($type.length) {
                $type.val('supplier').trigger('change');
            }
            $modal.modal('show');
        });
    });

    $(document).on('hidden.bs.modal', 'div.contact_modal', function (e) {
        if (e.target === this) {
            setTimeout(function () { waitingForSupplier = false; }, 300);
        }
    });

    $(document).on('contact:created', function (e, contact) {
        var $sel = $('#product_supplier_id');
        if (!waitingForSupplier || !contact || !contact.id || !$sel.length) return;
        waitingForSupplier = false;
        var name = contact.name || contact.supplier_business_name || contact.contact_id || ('#' + contact.id);
        if (contact.supplier_business_name && contact.name && contact.supplier_business_name !== contact.name) {
            name += ' - ' + contact.supplier_business_name;
        }
        if (contact.contact_id) {
            name += ' (' + contact.contact_id + ')';
        }
        if (!$sel.find('option[value="' + contact.id + '"]').length) {
            $sel.append(new Option(name, contact.id, true, true));
        }
        $sel.val(String(contact.id)).trigger('change');
    });
})();
