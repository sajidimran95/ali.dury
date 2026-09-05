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

    //If purchase price exc tax is changed
    $(document).on('change', 'input#single_dpp', function(e) {
        var purchase_exc_tax = __read_number($('input#single_dpp'));
        purchase_exc_tax = purchase_exc_tax == undefined ? 0 : purchase_exc_tax;

        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;

        var purchase_inc_tax = __add_percent(purchase_exc_tax, tax_rate);
        __write_number($('input#single_dpp_inc_tax'), purchase_inc_tax);

        var profit_percent = __read_number($('#profit_percent'));
        var selling_price = __add_percent(purchase_exc_tax, profit_percent);
        __write_number($('input#single_dsp'), selling_price);

        var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
        __write_number($('input#single_dsp_inc_tax'), selling_price_inc_tax);
    });

    //If tax rate is changed
    $(document).on('change', 'select#tax', function() {
        if ($('select#type').val() == 'single') {
            var purchase_exc_tax = __read_number($('input#single_dpp'));
            purchase_exc_tax = purchase_exc_tax == undefined ? 0 : purchase_exc_tax;

            var tax_rate = $('select#tax')
                .find(':selected')
                .data('rate');
            tax_rate = tax_rate == undefined ? 0 : tax_rate;

            var purchase_inc_tax = __add_percent(purchase_exc_tax, tax_rate);
            __write_number($('input#single_dpp_inc_tax'), purchase_inc_tax);

            var selling_price = __read_number($('input#single_dsp'));
            var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
            __write_number($('input#single_dsp_inc_tax'), selling_price_inc_tax);
        }
    });

    //If purchase price inc tax is changed
    $(document).on('change', 'input#single_dpp_inc_tax', function(e) {
        var purchase_inc_tax = __read_number($('input#single_dpp_inc_tax'));
        purchase_inc_tax = purchase_inc_tax == undefined ? 0 : purchase_inc_tax;

        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;

        var purchase_exc_tax = __get_principle(purchase_inc_tax, tax_rate);
        __write_number($('input#single_dpp'), purchase_exc_tax);
        $('input#single_dpp').change();

        var profit_percent = __read_number($('#profit_percent'));
        profit_percent = profit_percent == undefined ? 0 : profit_percent;
        var selling_price = __add_percent(purchase_exc_tax, profit_percent);
        __write_number($('input#single_dsp'), selling_price);

        var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
        __write_number($('input#single_dsp_inc_tax'), selling_price_inc_tax);
    });

    $(document).on('change', 'input#profit_percent', function(e) {
        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;

        var purchase_inc_tax = __read_number($('input#single_dpp_inc_tax'));
        purchase_inc_tax = purchase_inc_tax == undefined ? 0 : purchase_inc_tax;

        var purchase_exc_tax = __read_number($('input#single_dpp'));
        purchase_exc_tax = purchase_exc_tax == undefined ? 0 : purchase_exc_tax;

        var profit_percent = __read_number($('input#profit_percent'));
        var selling_price = __add_percent(purchase_exc_tax, profit_percent);
        __write_number($('input#single_dsp'), selling_price);

        var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
        __write_number($('input#single_dsp_inc_tax'), selling_price_inc_tax);
    });

    $(document).on('change', 'input#single_dsp', function(e) {
        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;

        var selling_price = __read_number($('input#single_dsp'));
        var purchase_exc_tax = __read_number($('input#single_dpp'));
        var profit_percent = __read_number($('input#profit_percent'));

        //if purchase price not set
        if (purchase_exc_tax == 0) {
            profit_percent = 0;
        } else {
            profit_percent = __get_rate(purchase_exc_tax, selling_price);
        }

        __write_number($('input#profit_percent'), profit_percent);

        var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
        __write_number($('input#single_dsp_inc_tax'), selling_price_inc_tax);
    });

    $(document).on('change', 'input#single_dsp_inc_tax', function(e) {
        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;
        var selling_price_inc_tax = __read_number($('input#single_dsp_inc_tax'));

        var selling_price = __get_principle(selling_price_inc_tax, tax_rate);
        __write_number($('input#single_dsp'), selling_price);
        var purchase_exc_tax = __read_number($('input#single_dpp'));
        var profit_percent = __read_number($('input#profit_percent'));

        //if purchase price not set
        if (purchase_exc_tax == 0) {
            profit_percent = 0;
        } else {
            profit_percent = __get_rate(purchase_exc_tax, selling_price);
        }

        __write_number($('input#profit_percent'), profit_percent);
    });

    if ($('#product_add_form').length) {
        $('form#product_add_form').validate({
            rules: {
                sku: {
                    remote: {
                        url: '/products/check_product_sku',
                        type: 'post',
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
                    remote: LANG.sku_already_exists,
                },
                name: {
                    remote: LANG.name_already_exists,
                },
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
                        if ($('form#product_add_form').valid()) {
                            $('form#product_add_form').submit();
                        }
                    } else {
                        toastr.error(__translate('skus_already_exists', {sku: result.sku}));
                        return false;
                    }
                },
            });
        } else {
            $('#submit_type').val(submit_type);
            if ($('form#product_add_form').valid()) {
                $('form#product_add_form').submit();
            }
        }
        
    });
    //End for product type single

    //Start for product type Variable
    //If purchase price exc tax is changed
    $(document).on('change', 'input.variable_dpp', function(e) {
        var tr_obj = $(this).closest('tr');

        var purchase_exc_tax = __read_number($(this));
        purchase_exc_tax = purchase_exc_tax == undefined ? 0 : purchase_exc_tax;

        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;

        var purchase_inc_tax = __add_percent(purchase_exc_tax, tax_rate);
        __write_number(tr_obj.find('input.variable_dpp_inc_tax'), purchase_inc_tax);

        var profit_percent = __read_number(tr_obj.find('input.variable_profit_percent'));
        var selling_price = __add_percent(purchase_exc_tax, profit_percent);
        __write_number(tr_obj.find('input.variable_dsp'), selling_price);

        var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
        __write_number(tr_obj.find('input.variable_dsp_inc_tax'), selling_price_inc_tax);
    });

    //If purchase price inc tax is changed
    $(document).on('change', 'input.variable_dpp_inc_tax', function(e) {
        var tr_obj = $(this).closest('tr');

        var purchase_inc_tax = __read_number($(this));
        purchase_inc_tax = purchase_inc_tax == undefined ? 0 : purchase_inc_tax;

        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;

        var purchase_exc_tax = __get_principle(purchase_inc_tax, tax_rate);
        __write_number(tr_obj.find('input.variable_dpp'), purchase_exc_tax);

        var profit_percent = __read_number(tr_obj.find('input.variable_profit_percent'));
        var selling_price = __add_percent(purchase_exc_tax, profit_percent);
        __write_number(tr_obj.find('input.variable_dsp'), selling_price);

        var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
        __write_number(tr_obj.find('input.variable_dsp_inc_tax'), selling_price_inc_tax);
    });

    $(document).on('change', 'input.variable_profit_percent', function(e) {
        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;

        var tr_obj = $(this).closest('tr');
        var profit_percent = __read_number($(this));

        var purchase_exc_tax = __read_number(tr_obj.find('input.variable_dpp'));
        purchase_exc_tax = purchase_exc_tax == undefined ? 0 : purchase_exc_tax;

        var selling_price = __add_percent(purchase_exc_tax, profit_percent);
        __write_number(tr_obj.find('input.variable_dsp'), selling_price);

        var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
        __write_number(tr_obj.find('input.variable_dsp_inc_tax'), selling_price_inc_tax);
    });

    $(document).on('change', 'input.variable_dsp', function(e) {
        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;

        var tr_obj = $(this).closest('tr');
        var selling_price = __read_number($(this));
        var purchase_exc_tax = __read_number(tr_obj.find('input.variable_dpp'));

        var profit_percent = __read_number(tr_obj.find('input.variable_profit_percent'));

        //if purchase price not set
        if (purchase_exc_tax == 0) {
            profit_percent = 0;
        } else {
            profit_percent = __get_rate(purchase_exc_tax, selling_price);
        }

        __write_number(tr_obj.find('input.variable_profit_percent'), profit_percent);

        var selling_price_inc_tax = __add_percent(selling_price, tax_rate);
        __write_number(tr_obj.find('input.variable_dsp_inc_tax'), selling_price_inc_tax);
    });
    $(document).on('change', 'input.variable_dsp_inc_tax', function(e) {
        var tr_obj = $(this).closest('tr');
        var selling_price_inc_tax = __read_number($(this));

        var tax_rate = $('select#tax')
            .find(':selected')
            .data('rate');
        tax_rate = tax_rate == undefined ? 0 : tax_rate;

        var selling_price = __get_principle(selling_price_inc_tax, tax_rate);
        __write_number(tr_obj.find('input.variable_dsp'), selling_price);

        var purchase_exc_tax = __read_number(tr_obj.find('input.variable_dpp'));
        var profit_percent = __read_number(tr_obj.find('input.variable_profit_percent'));
        //if purchase price not set
        if (purchase_exc_tax == 0) {
            profit_percent = 0;
        } else {
            profit_percent = __get_rate(purchase_exc_tax, selling_price);
        }

        __write_number(tr_obj.find('input.variable_profit_percent'), profit_percent);
    });

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
            uomUpdateLabels();
            uomSyncFromMainPrices();
        } else {
            $('#packaging_uom_fields').addClass('hide');
        }
    }

    function uomSyncFromMainPrices() {
        if (!uomEnabled() || uomLock) return;
        uomLock = true;
        var factor = uomFactor();
        var sellBase = __read_number($('input#single_dsp'));
        var purchaseBase = __read_number($('input#single_dpp'));
        if (sellBase !== undefined && sellBase !== null) {
            __write_number($('#uom_sell_base'), sellBase);
            if (factor > 0) {
                __write_number($('#uom_sell_pack'), sellBase * factor);
            }
        }
        if (purchaseBase !== undefined && purchaseBase !== null) {
            __write_number($('#uom_purchase_base'), purchaseBase);
            if (factor > 0) {
                __write_number($('#uom_purchase_pack'), purchaseBase * factor);
            }
        }
        uomLock = false;
    }

    function uomFromSellBase() {
        if (!uomEnabled() || uomLock) return;
        uomLock = true;
        var factor = uomFactor();
        var base = __read_number($('#uom_sell_base'));
        base = base == undefined ? 0 : base;
        __write_number($('input#single_dsp'), base);
        var tax_rate = $('select#tax').find(':selected').data('rate') || 0;
        __write_number($('input#single_dsp_inc_tax'), __add_percent(base, tax_rate));
        if (factor > 0) {
            __write_number($('#uom_sell_pack'), base * factor);
        }
        var purchase_exc_tax = __read_number($('input#single_dpp')) || 0;
        if (purchase_exc_tax > 0) {
            __write_number($('#profit_percent'), __get_rate(purchase_exc_tax, base));
        }
        uomLock = false;
    }

    function uomFromSellPack() {
        if (!uomEnabled() || uomLock) return;
        var factor = uomFactor();
        if (!(factor > 0)) return;
        uomLock = true;
        var pack = __read_number($('#uom_sell_pack'));
        pack = pack == undefined ? 0 : pack;
        var base = pack / factor;
        __write_number($('#uom_sell_base'), base);
        __write_number($('input#single_dsp'), base);
        var tax_rate = $('select#tax').find(':selected').data('rate') || 0;
        __write_number($('input#single_dsp_inc_tax'), __add_percent(base, tax_rate));
        var purchase_exc_tax = __read_number($('input#single_dpp')) || 0;
        if (purchase_exc_tax > 0) {
            __write_number($('#profit_percent'), __get_rate(purchase_exc_tax, base));
        }
        uomLock = false;
    }

    function uomFromPurchaseBase() {
        if (!uomEnabled() || uomLock) return;
        uomLock = true;
        var factor = uomFactor();
        var base = __read_number($('#uom_purchase_base'));
        base = base == undefined ? 0 : base;
        __write_number($('input#single_dpp'), base);
        $('input#single_dpp').trigger('change');
        if (factor > 0) {
            __write_number($('#uom_purchase_pack'), base * factor);
        }
        var sellBase = __read_number($('input#single_dsp')) || 0;
        __write_number($('#uom_sell_base'), sellBase);
        if (factor > 0) {
            __write_number($('#uom_sell_pack'), sellBase * factor);
        }
        uomLock = false;
    }

    function uomFromPurchasePack() {
        if (!uomEnabled() || uomLock) return;
        var factor = uomFactor();
        if (!(factor > 0)) return;
        uomLock = true;
        var pack = __read_number($('#uom_purchase_pack'));
        pack = pack == undefined ? 0 : pack;
        var base = pack / factor;
        __write_number($('#uom_purchase_base'), base);
        __write_number($('input#single_dpp'), base);
        $('input#single_dpp').trigger('change');
        var sellBase = __read_number($('input#single_dsp')) || 0;
        __write_number($('#uom_sell_base'), sellBase);
        __write_number($('#uom_sell_pack'), sellBase * factor);
        uomLock = false;
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
                if (uomEnabled()) {
                    setTimeout(uomSyncFromMainPrices, 50);
                }
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

        $(document).on('change', '#packaging_conversion_factor', function () {
            if (!uomEnabled()) return;
            var factor = uomFactor();
            if (!(factor > 0)) return;
            var sellBase = __read_number($('#uom_sell_base'));
            if (sellBase == undefined || sellBase === null || sellBase === '') {
                sellBase = __read_number($('input#single_dsp')) || 0;
                __write_number($('#uom_sell_base'), sellBase);
            }
            var purchaseBase = __read_number($('#uom_purchase_base'));
            if (purchaseBase == undefined || purchaseBase === null || purchaseBase === '') {
                purchaseBase = __read_number($('input#single_dpp')) || 0;
                __write_number($('#uom_purchase_base'), purchaseBase);
            }
            uomLock = true;
            __write_number($('#uom_sell_pack'), (sellBase || 0) * factor);
            __write_number($('#uom_purchase_pack'), (purchaseBase || 0) * factor);
            uomLock = false;
        });

        $(document).on('change', '#uom_sell_base', uomFromSellBase);
        $(document).on('change', '#uom_sell_pack', uomFromSellPack);
        $(document).on('change', '#uom_purchase_base', uomFromPurchaseBase);
        $(document).on('change', '#uom_purchase_pack', uomFromPurchasePack);

        $(document).on('change', 'input#single_dsp, input#single_dpp', function () {
            if (!uomEnabled() || uomLock) return;
            uomSyncFromMainPrices();
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
            var sellBase = __read_number($('#uom_sell_base'));
            var purchaseBase = __read_number($('#uom_purchase_base'));
            if (sellBase != undefined && $('input#single_dsp').length) {
                __write_number($('input#single_dsp'), sellBase);
                var tax_rate = $('select#tax').find(':selected').data('rate') || 0;
                __write_number($('input#single_dsp_inc_tax'), __add_percent(sellBase, tax_rate));
            }
            if (purchaseBase != undefined && $('input#single_dpp').length) {
                __write_number($('input#single_dpp'), purchaseBase);
                var tax_rate2 = $('select#tax').find(':selected').data('rate') || 0;
                __write_number($('input#single_dpp_inc_tax'), __add_percent(purchaseBase, tax_rate2));
            }
        });
    });
})();
