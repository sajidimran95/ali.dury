$(document).ready(function () {
    var cfg = window.cgGroup || {};
    var changed = {};
    var membersTable = null;
    var productsTable = null;

    function showTab(tab) {
        $('.cg-tab').removeClass('is-active tw-dw-btn-primary tw-text-white').addClass('tw-dw-btn-outline');
        $('.cg-tab[data-tab="' + tab + '"]').addClass('is-active tw-dw-btn-primary tw-text-white').removeClass('tw-dw-btn-outline');
        $('.cg-panel').addClass('hide').hide();
        var $panel = $('.cg-panel[data-panel="' + tab + '"]');
        $panel.removeClass('hide').show();
        if (tab === 'prices') {
            initProductsTable();
            if (productsTable) {
                productsTable.columns.adjust().draw(false);
            }
        }
    }

    $(document).on('click', '.cg-tab', function (e) {
        e.preventDefault();
        e.stopPropagation();
        showTab($(this).attr('data-tab'));
    });

    membersTable = $('#cg_members_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: cfg.membersUrl,
        columns: [
            { data: 'display_name', name: 'name' },
            { data: 'mobile', name: 'mobile' },
            { data: 'action', orderable: false, searchable: false }
        ]
    });

    function initProductsTable() {
        if (productsTable || !$('#cg_products_table').length) {
            return;
        }
        productsTable = $('#cg_products_table').DataTable({
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
    }

    if ($.fn.select2 && $('#cg_customer_search').length) {
        $('#cg_customer_search').select2({
            ajax: {
                url: cfg.customersSearchUrl,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term, all_contact: 0 };
                },
                processResults: function (data) {
                    if (typeof data === 'string') {
                        try {
                            data = JSON.parse(data);
                        } catch (err) {
                            data = [];
                        }
                    }
                    var list = Array.isArray(data) ? data : (data.results || []);
                    return {
                        results: $.map(list, function (item) {
                            return {
                                id: item.id,
                                text: item.text || item.name || ('#' + item.id)
                            };
                        })
                    };
                }
            },
            placeholder: (typeof LANG !== 'undefined' && LANG.search) ? LANG.search : 'Search',
            minimumInputLength: 1,
            width: '100%'
        });
    }

    if ($.fn.select2 && $('#cg_product_search').length) {
        $('#cg_product_search').select2({
            ajax: {
                url: cfg.productSearchUrl,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    var list = Array.isArray(data) ? data : [];
                    return { results: list };
                }
            },
            placeholder: (typeof LANG !== 'undefined' && LANG.search) ? LANG.search : 'Search product',
            minimumInputLength: 1,
            width: '100%'
        });
    }

    $(document).on('click', '#cg_add_override', function (e) {
        e.preventDefault();
        var variationId = $('#cg_product_search').val();
        if (!variationId) {
            return;
        }
        $.ajax({
            method: 'POST',
            url: cfg.productOverrideUrl,
            dataType: 'json',
            data: { variation_id: variationId },
            success: function (result) {
                if (result.success) {
                    toastr.success(result.msg);
                    $('#cg_product_search').val(null).trigger('change');
                    if (productsTable) {
                        productsTable.ajax.reload();
                    } else {
                        initProductsTable();
                    }
                } else {
                    toastr.error(result.msg);
                }
            }
        });
    });

    $(document).on('click', '#cg_tag_customers', function (e) {
        e.preventDefault();
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
                    if (membersTable) {
                        membersTable.ajax.reload();
                    }
                } else {
                    toastr.error(result.msg);
                }
            }
        });
    });

    $(document).on('click', '.js-untag-customer', function (e) {
        e.preventDefault();
        var href = $(this).data('href');
        $.ajax({
            method: 'DELETE',
            url: href,
            dataType: 'json',
            success: function (result) {
                if (result.success) {
                    toastr.success(result.msg);
                    if (membersTable) {
                        membersTable.ajax.reload();
                    }
                } else {
                    toastr.error(result.msg);
                }
            }
        });
    });

    $(document).on('input change', '.js-group-price', function () {
        changed[$(this).data('variation-id')] = $(this).val();
    });

    $(document).on('click', '#cg_save_prices', function (e) {
        e.preventDefault();
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
                    if (productsTable) {
                        productsTable.ajax.reload(null, false);
                    }
                } else {
                    toastr.error(result.msg);
                }
            }
        });
    });

    $(document).on('submit', '#cg_discount_form', function (e) {
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

    $(document).on('click', '#cg_apply_all', function (e) {
        e.preventDefault();
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
                    if (productsTable) {
                        productsTable.ajax.reload(null, false);
                    }
                } else {
                    toastr.error(result.msg);
                }
            }
        });
    });
});
