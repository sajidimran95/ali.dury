$(document).ready(function () {
    var cfg = window.cgGroup || {};
    var changed = {};
    var membersTable = null;
    var productsTable = null;

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val() || '';
    }

    function updateCustomerCount(n) {
        if (typeof n === 'undefined' || n === null) return;
        $('#cg_customers_count').text(n);
    }

    function updateProductCount(n) {
        if (typeof n === 'undefined' || n === null) return;
        $('#cg_products_count').text(n);
    }

    function currentScope() {
        return $('input[name="cg_scope"]:checked').val() || 'all_inventory';
    }

    function scopeLabel(scope) {
        if (scope === 'category') {
            return cfg.scopeCategory || 'Category wise';
        }
        if (scope === 'product') {
            return cfg.scopeProduct || 'Product wise';
        }
        return cfg.scopeAll || 'All inventory';
    }

    function syncApplyButton() {
        var scope = currentScope();
        var label = cfg.btnAll || 'Apply % to all inventory';
        if (scope === 'category') {
            label = cfg.btnCategory || 'Apply % to category';
        } else if (scope === 'product') {
            label = cfg.btnProducts || 'Apply % to selected products';
        }
        $('#cg_apply_btn').text(label);
    }

    function syncActiveScope(scope) {
        scope = scope || currentScope();
        $('.cg-scope-opt').removeClass('is-active');
        $('.cg-scope-opt').has('input[value="' + scope + '"]').addClass('is-active');
        $('#cg_active_scope_text').text(scopeLabel(scope));
        syncApplyButton();
    }

    function showScopePanel(scope) {
        $('.cg-scope-panel').addClass('hide').hide();
        if (scope === 'category' || scope === 'product') {
            $('.cg-scope-panel[data-scope-panel="' + scope + '"]').removeClass('hide').show();
        }
        syncActiveScope(scope);
        syncPickColumn(scope);
        if (productsTable) {
            productsTable.ajax.reload(null, false);
        }
    }

    function syncPickColumn(scope) {
        var show = scope === 'product';
        $('.cg-pick-col').toggle(show);
        if (productsTable) {
            productsTable.column(0).visible(show);
        }
    }

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
        } else if (tab === 'customers' && membersTable) {
            membersTable.columns.adjust().draw(false);
        }
    }

    $(document).on('click', '.cg-tab', function (e) {
        e.preventDefault();
        e.stopPropagation();
        showTab($(this).attr('data-tab'));
    });

    $(document).on('change', 'input[name="cg_scope"]', function () {
        showScopePanel($(this).val());
    });
    showScopePanel(cfg.initialScope || currentScope());

    membersTable = $('#cg_members_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: { url: cfg.membersUrl, type: 'GET' },
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
            pageLength: 25,
            deferRender: true,
            ajax: {
                url: cfg.productsUrl,
                data: function (d) {
                    d.only_tagged = ($('input[name="cg_list_mode"]:checked').val() || 'tagged') === 'tagged' ? 1 : 0;
                    // When category scope is active, list uses same category filter
                    if (currentScope() === 'category') {
                        d.category_id = $('#cg_category_id').val() || '';
                        d.sub_category_id = $('#cg_sub_category_id').val() || '';
                    } else {
                        d.category_id = '';
                        d.sub_category_id = '';
                    }
                }
            },
            columns: [
                { data: 'select_col', name: 'select_col', orderable: false, searchable: false, className: 'text-center cg-pick-col', visible: currentScope() === 'product' },
                { data: 'product', name: 'p.name' },
                { data: 'sub_sku', name: 'variations.sub_sku' },
                { data: 'default_price', name: 'variations.sell_price_inc_tax' },
                { data: 'group_price_input', name: 'vgp.price_inc_tax', orderable: false, searchable: false }
            ],
            drawCallback: function () {
                $('#cg_products_table .cg-product-check').each(function () {
                    var id = parseInt($(this).val(), 10);
                    if ($(this).is(':checked') || selectedProductIds[id]) {
                        selectedProductIds[id] = true;
                        $(this).prop('checked', true);
                    }
                });
                syncSelectAllBox();
            }
        });
        syncPickColumn(currentScope());
    }

    var selectedProductIds = {};

    $(document).on('change', 'input[name="cg_list_mode"]', function () {
        $('.cg-list-filter-opt').removeClass('is-active');
        $(this).closest('.cg-list-filter-opt').addClass('is-active');
        if (productsTable) {
            productsTable.ajax.reload();
        }
    });

    function syncSelectAllBox() {
        var $checks = $('#cg_products_table .cg-product-check');
        var total = $checks.length;
        var checked = $checks.filter(':checked').length;
        $('#cg_select_all_products').prop('checked', total > 0 && checked === total);
    }

    $(document).on('change', '#cg_select_all_products', function () {
        var on = $(this).is(':checked');
        $('#cg_products_table .cg-product-check').each(function () {
            $(this).prop('checked', on);
            var id = parseInt($(this).val(), 10);
            if (on) {
                selectedProductIds[id] = true;
            } else {
                delete selectedProductIds[id];
            }
        });
    });

    $(document).on('change', '.cg-product-check', function () {
        var id = parseInt($(this).val(), 10);
        if ($(this).is(':checked')) {
            selectedProductIds[id] = true;
        } else {
            delete selectedProductIds[id];
        }
        syncSelectAllBox();
    });
    function loadSubCategories(categoryId) {
        var $wrap = $('#cg_sub_category_wrap');
        var $sub = $('#cg_sub_category_id');
        $sub.html('<option value="">' + ((typeof LANG !== 'undefined' && LANG.all) ? LANG.all : 'All') + '</option>');
        $sub.val('').trigger('change.select2');
        if (!categoryId) {
            $wrap.hide();
            return;
        }
        $.ajax({
            method: 'POST',
            url: cfg.subCategoriesUrl,
            dataType: 'html',
            data: { cat_id: categoryId, _token: csrfToken() },
            success: function (html) {
                var $tmp = $('<select>' + html + '</select>');
                var count = 0;
                $tmp.find('option').each(function () {
                    var v = $(this).attr('value');
                    if (v === '' || v === undefined) return;
                    $sub.append($(this).clone());
                    count++;
                });
                if (count > 0) {
                    $wrap.show();
                } else {
                    $wrap.hide();
                    $sub.val('');
                }
                if (productsTable && currentScope() === 'category') {
                    productsTable.ajax.reload();
                }
            },
            error: function () {
                $wrap.hide();
            }
        });
    }

    if ($.fn.select2) {
        $('#cg_category_id, #cg_sub_category_id').select2({ width: '100%' });
    }

    $(document).on('change', '#cg_category_id', function () {
        loadSubCategories($(this).val());
        if (productsTable && currentScope() === 'category') {
            productsTable.ajax.reload();
        }
    });

    $(document).on('change', '#cg_sub_category_id', function () {
        if (productsTable && currentScope() === 'category') {
            productsTable.ajax.reload();
        }
    });

    if ($.fn.select2 && $('#cg_customer_search').length) {
        $('#cg_customer_search').select2({
            ajax: {
                url: cfg.customersSearchUrl,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term || '' };
                },
                processResults: function (data) {
                    var list = Array.isArray(data) ? data : [];
                    return {
                        results: $.map(list, function (item) {
                            return { id: String(item.id), text: item.text || ('#' + item.id) };
                        })
                    };
                }
            },
            placeholder: (typeof LANG !== 'undefined' && LANG.search) ? LANG.search : 'Search customer',
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
                    return { q: params.term || '' };
                },
                processResults: function (data) {
                    var list = Array.isArray(data) ? data : [];
                    return {
                        results: $.map(list, function (item) {
                            return { id: String(item.id), text: item.text || ('#' + item.id) };
                        })
                    };
                }
            },
            placeholder: (typeof LANG !== 'undefined' && LANG.search) ? LANG.search : 'Search item',
            minimumInputLength: 1,
            width: '100%'
        });
    }

    function selectedIds($el) {
        var ids = [];
        if ($.fn.select2 && $el.data('select2')) {
            ids = $.map($el.select2('data') || [], function (item) {
                return parseInt(item.id, 10);
            });
        } else {
            ids = $el.val() || [];
        }
        if (!Array.isArray(ids)) {
            ids = ids ? [ids] : [];
        }
        return ids.map(function (v) { return parseInt(v, 10); }).filter(function (v) { return v > 0; });
    }

    function applyScope(extra) {
        var payload = $.extend({
            amount: $('#cg_discount_amount').val(),
            scope: currentScope(),
            _token: csrfToken()
        }, extra || {});

        $.ajax({
            method: 'POST',
            url: cfg.applyScopeUrl,
            dataType: 'json',
            data: payload,
            success: function (result) {
                if (result.success) {
                    toastr.success(result.msg);
                    updateProductCount(result.priced_count);
                    if (productsTable) {
                        productsTable.ajax.reload(null, false);
                    } else {
                        initProductsTable();
                    }
                } else {
                    toastr.error(result.msg);
                }
            },
            error: function () {
                toastr.error('Failed to apply group prices');
            }
        });
    }

    $(document).on('click', '#cg_apply_btn', function (e) {
        e.preventDefault();
        var scope = currentScope();
        if (scope === 'category') {
            var cat = $('#cg_category_id').val();
            if (!cat) {
                toastr.error(cfg.selectCategory || 'Select a category');
                return;
            }
            applyScope({
                scope: 'category',
                category_id: cat,
                sub_category_id: $('#cg_sub_category_id').val() || ''
            });
            return;
        }
        if (scope === 'product') {
            // Search select + multiple ticks from product list
            var ids = selectedIds($('#cg_product_search'));
            Object.keys(selectedProductIds).forEach(function (k) {
                var id = parseInt(k, 10);
                if (id > 0 && ids.indexOf(id) === -1) {
                    ids.push(id);
                }
            });
            if (!ids.length) {
                toastr.error(cfg.selectProducts || 'Select at least one product');
                return;
            }
            var data = {
                amount: $('#cg_discount_amount').val(),
                scope: 'product',
                _token: csrfToken()
            };
            for (var i = 0; i < ids.length; i++) {
                data['variation_ids[' + i + ']'] = ids[i];
            }
            $.ajax({
                method: 'POST',
                url: cfg.applyScopeUrl,
                dataType: 'json',
                data: data,
                success: function (result) {
                    if (result.success) {
                        toastr.success(result.msg);
                        updateProductCount(result.priced_count);
                        $('#cg_product_search').val(null).trigger('change');
                        selectedProductIds = {};
                        $('#cg_select_all_products').prop('checked', false);
                        if (productsTable) {
                            productsTable.ajax.reload(null, false);
                        } else {
                            initProductsTable();
                        }
                    } else {
                        toastr.error(result.msg);
                    }
                },
                error: function () {
                    toastr.error('Failed to apply group prices');
                }
            });
            return;
        }
        applyScope({ scope: 'all_inventory' });
    });

    $(document).on('click', '#cg_tag_customers', function (e) {
        e.preventDefault();
        var ids = selectedIds($('#cg_customer_search'));
        if (!ids.length) {
            toastr.error(cfg.selectCustomers || 'Select at least one customer.');
            return;
        }

        var data = { _token: csrfToken() };
        for (var i = 0; i < ids.length; i++) {
            data['customer_ids[' + i + ']'] = ids[i];
        }

        $.ajax({
            method: 'POST',
            url: cfg.membersUrl,
            dataType: 'json',
            data: data,
            success: function (result) {
                if (result.success) {
                    toastr.success(result.msg);
                    $('#cg_customer_search').val(null).trigger('change');
                    updateCustomerCount(result.customers_count);
                    if (membersTable) {
                        membersTable.ajax.reload(null, false);
                    }
                } else {
                    toastr.error(result.msg);
                }
            },
            error: function (xhr) {
                toastr.error((xhr.responseJSON && xhr.responseJSON.msg) || 'Failed to tag customers');
            }
        });
    });

    $(document).on('click', '.js-untag-customer', function (e) {
        e.preventDefault();
        $.ajax({
            method: 'DELETE',
            url: $(this).data('href'),
            dataType: 'json',
            data: { _token: csrfToken() },
            success: function (result) {
                if (result.success) {
                    toastr.success(result.msg);
                    updateCustomerCount(result.customers_count);
                    if (membersTable) {
                        membersTable.ajax.reload(null, false);
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
            data: { prices: prices, _token: csrfToken() },
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
});
