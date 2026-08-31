$(function () {
    var cfg = window.cgGroup || {};
    var changed = {};

    $('.cg-tab').on('click', function () {
        var tab = $(this).data('tab');
        $('.cg-tab').removeClass('is-active');
        $(this).addClass('is-active');
        $('.cg-panel').addClass('hide');
        $('.cg-panel[data-panel="' + tab + '"]').removeClass('hide');
        if (tab === 'prices' && productsTable) {
            productsTable.columns.adjust();
        }
    });

    var membersTable = $('#cg_members_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: cfg.membersUrl,
        columns: [
            { data: 'display_name', name: 'name' },
            { data: 'mobile', name: 'mobile' },
            { data: 'action', orderable: false, searchable: false }
        ]
    });

    var productsTable = $('#cg_products_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: cfg.productsUrl,
        columns: [
            { data: 'product', name: 'p.name' },
            { data: 'sub_sku', name: 'variations.sub_sku' },
            { data: 'default_price', name: 'variations.sell_price_inc_tax' },
            { data: 'group_price_input', name: 'vgp.price_inc_tax', orderable: false, searchable: false }
        ]
    });

    if ($.fn.select2) {
        $('#cg_customer_search').select2({
            ajax: {
                url: cfg.customersSearchUrl,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    return { results: data };
                }
            },
            placeholder: LANG.search || 'Search',
            minimumInputLength: 1,
            width: '100%'
        });
    }

    $('#cg_tag_customers').on('click', function () {
        var ids = $('#cg_customer_search').val() || [];
        $.ajax({
            method: 'POST',
            url: cfg.membersUrl,
            dataType: 'json',
            data: { customer_ids: ids },
            success: function (result) {
                if (result.success) {
                    toastr.success(result.msg);
                    $('#cg_customer_search').val(null).trigger('change');
                    membersTable.ajax.reload();
                } else {
                    toastr.error(result.msg);
                }
            }
        });
    });

    $(document).on('click', '.js-untag-customer', function () {
        var href = $(this).data('href');
        $.ajax({
            method: 'DELETE',
            url: href,
            dataType: 'json',
            success: function (result) {
                if (result.success) {
                    toastr.success(result.msg);
                    membersTable.ajax.reload();
                } else {
                    toastr.error(result.msg);
                }
            }
        });
    });

    $(document).on('input change', '.js-group-price', function () {
        changed[$(this).data('variation-id')] = $(this).val();
    });

    $('#cg_save_prices').on('click', function () {
        var prices = [];
        $.each(changed, function (variationId, price) {
            prices.push({ variation_id: variationId, price: price });
        });
        if (!prices.length) {
            toastr.error(cfg.noPrices);
            return;
        }
        $.ajax({
            method: 'POST',
            url: cfg.productsUrl,
            dataType: 'json',
            data: { prices: prices },
            success: function (result) {
                if (result.success) {
                    toastr.success(result.msg);
                    changed = {};
                    productsTable.ajax.reload(null, false);
                } else {
                    toastr.error(result.msg);
                }
            }
        });
    });

    $('#cg_discount_form').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            method: 'POST',
            url: cfg.discountUrl,
            dataType: 'json',
            data: { amount: $('#cg_discount_amount').val() },
            success: function (result) {
                if (result.success) {
                    toastr.success(result.msg);
                    if (productsTable) {
                        productsTable.ajax.reload(null, false);
                    }
                } else {
                    toastr.error(result.msg);
                }
            }
        });
    });
        if (!window.confirm(cfg.applyAllConfirm)) {
            return;
        }
        $.ajax({
            method: 'POST',
            url: cfg.applyAllUrl,
            dataType: 'json',
            success: function (result) {
                if (result.success) {
                    toastr.success(result.msg);
                    productsTable.ajax.reload(null, false);
                } else {
                    toastr.error(result.msg);
                }
            }
        });
    });
});
