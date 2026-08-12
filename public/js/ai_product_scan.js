/* JapsAI — standalone "scan to add products" (Products page).
 * Reads a supplier invoice/price list/catalog photo or PDF and shows which lines are
 * already in the product catalog vs missing, so missing ones can be created straight
 * from what was detected. Unlike ai_invoice.js (Add Purchase), this never touches a
 * purchase — it only reads the document and creates products via the existing
 * quick-add flow:
 *   - /purchases/get_products    (product/variation search — read-only, no purchase needed)
 *   - /products/quick_add        (create-product modal, existing flow)
 */
(function () {
    'use strict';

    if (window.__aiProductScanBound) {
        return;
    }
    window.__aiProductScanBound = true;

    var state = {
        lines: [], // {name, sku, unit_price, status: 'searching'|'exists'|'missing'|'added'}
        originalFile: null,
        pendingQuickAddIdx: null,
    };

    function t(key, fallback) {
        return (window.AI_PRODUCT_SCAN_LANG && window.AI_PRODUCT_SCAN_LANG[key]) || fallback;
    }

    function escapeHtml(str) {
        return $('<div>').text(str == null ? '' : String(str)).html();
    }

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').first().val();
    }

    // ---- modal lifecycle -------------------------------------------------

    function resetModal() {
        state.lines = [];
        state.originalFile = null;
        state.pendingQuickAddIdx = null;
        $('#ai_scan_file').val('');
        $('#ai_scan_preview_img').attr('src', '').hide();
        $('#ai_scan_preview_pdf').hide();
        $('#ai_scan_review_body').empty();
        $('#ai_scan_review_table').hide();
        $('#ai_scan_select_all').prop('checked', true);
        $('#ai_scan_status').text('');
        $('#ai_scan_extract_btn').prop('disabled', true).show();
    }

    function openModal() {
        resetModal();
        $('#ai_product_scan_modal').modal('show');
    }

    $(document).on('click', '.open_ai_product_scan_modal', function (e) {
        e.preventDefault();
        openModal();
    });

    $(document).on('change', '#ai_scan_file', function () {
        var file = this.files && this.files[0];
        $('#ai_scan_extract_btn').prop('disabled', !file);
        if (!file) {
            return;
        }
        state.originalFile = file;
        if (file.type === 'application/pdf') {
            $('#ai_scan_preview_img').hide();
            $('#ai_scan_preview_pdf_name').text(file.name);
            $('#ai_scan_preview_pdf').show();
        } else {
            $('#ai_scan_preview_pdf').hide();
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#ai_scan_preview_img').attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        }
    });

    // ---- extraction --------------------------------------------------

    $(document).on('click', '#ai_scan_extract_btn', function () {
        var file = state.originalFile;
        if (!file) {
            return;
        }
        var fd = new FormData();
        fd.append('invoice_file', file);
        fd.append('_token', csrfToken());

        $('#ai_scan_status').text(t('scan_products_reading', 'JapsAI is reading the document...'));
        $('#ai_scan_extract_btn').prop('disabled', true);

        $.ajax({
            url: window.AI_PRODUCT_SCAN_EXTRACT_URL,
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            timeout: 120000,
        })
            .done(function (res) {
                if (res && res.success) {
                    renderExtraction(res.data);
                } else {
                    toastr.error((res && res.msg) || 'Could not read document.');
                }
            })
            .fail(function (xhr, status) {
                var msg = (xhr.responseJSON && xhr.responseJSON.msg)
                    || (status === 'timeout' ? 'Request timed out. Try a smaller file.' : 'Could not read document.');
                toastr.error(msg);
            })
            .always(function () {
                $('#ai_scan_status').text('');
                $('#ai_scan_extract_btn').prop('disabled', false);
            });
    });

    function renderExtraction(data) {
        data = data || {};
        state.lines = (data.lines || []).map(function (l) {
            return {
                name: l.name,
                sku: l.sku,
                unit_price: typeof l.unit_price === 'number' ? l.unit_price : 0,
                status: 'searching',
            };
        });

        var $body = $('#ai_scan_review_body').empty();
        if (!state.lines.length) {
            toastr.warning(t('scan_products_no_lines', 'No items detected in this document.'));
            $('#ai_scan_review_table').hide();
            return;
        }

        state.lines.forEach(function (line, idx) {
            $body.append(buildReviewRow(line));
            searchMatchForLine(idx);
        });
        $('#ai_scan_review_table').show();
        $('#ai_scan_extract_btn').hide();
    }

    function buildReviewRow(line) {
        return $(
            '<tr>' +
                '<td><input type="checkbox" class="ai_line_include" checked></td>' +
                '<td class="ai-line-name">' + escapeHtml(line.name) +
                    (line.sku ? ' <small class="text-muted">[' + escapeHtml(line.sku) + ']</small>' : '') +
                '</td>' +
                '<td><input type="number" step="any" min="0" class="form-control input-sm ai_line_price" value="' + line.unit_price + '"></td>' +
                '<td class="ai-line-match">' +
                    '<span class="text-muted"><i class="fa fa-spinner fa-spin"></i></span>' +
                '</td>' +
            '</tr>'
        );
    }

    function rowFor(idx) {
        return $('#ai_scan_review_body tr').eq(idx);
    }

    // ---- product matching --------------------------------------------
    // Any candidate at all means the product already exists in the catalog — unlike the
    // purchase-invoice flow, we don't need a single unambiguous variation match here,
    // just a yes/no on "is this already something I stock".

    function searchMatchForLine(idx) {
        var line = state.lines[idx];
        var term = line.sku || line.name;
        if (!term) {
            renderMatchCell(idx, []);
            return;
        }
        $.getJSON('/purchases/get_products', { term: term })
            .done(function (res) {
                renderMatchCell(idx, (res && res.length) ? res : []);
            })
            .fail(function () {
                renderMatchCell(idx, []);
            });
    }

    function renderMatchCell(idx, candidates) {
        var line = state.lines[idx];

        if (candidates.length > 0) {
            line.status = 'exists';
            rowFor(idx).find('.ai-line-match').html('<span class="label label-success"><i class="fa fa-check"></i> '
                + escapeHtml(t('scan_product_exists', 'Already in your catalog')) + '</span>');
            return;
        }

        line.status = 'missing';
        renderMissingAction(idx);
    }

    // Missing-line action button, shown only while its checkbox is checked — unchecking
    // a line means "not interested in this one", so hide the invitation to create it.
    function renderMissingAction(idx) {
        var line = state.lines[idx];
        if (!line || line.status !== 'missing') {
            return;
        }
        var $cell = rowFor(idx).find('.ai-line-match');
        if (!isLineIncluded(idx)) {
            $cell.html('<span class="text-muted">' + escapeHtml(t('scan_product_skipped', 'Skipped')) + '</span>');
            return;
        }
        var $btn = $('<button type="button" class="btn btn-primary btn-xs ai_line_quick_add"><i class="fa fa-plus"></i> '
            + escapeHtml('Add new product') + '</button>');
        $cell.empty().append($btn);
    }

    function isLineIncluded(idx) {
        return rowFor(idx).find('.ai_line_include').is(':checked');
    }

    // "Select all" header checkbox toggles every line's own checkbox.
    $(document).on('change', '#ai_scan_select_all', function () {
        $('#ai_scan_review_body .ai_line_include').prop('checked', $(this).is(':checked')).trigger('change');
    });

    // Keep the header checkbox in sync (and re-render the missing-line action) whenever
    // a line is toggled individually.
    $(document).on('change', '#ai_scan_review_body .ai_line_include', function () {
        var idx = $(this).closest('tr').index();
        renderMissingAction(idx);
        var $all = $('#ai_scan_review_body .ai_line_include');
        $('#ai_scan_select_all').prop('checked', $all.length > 0 && $all.length === $all.filter(':checked').length);
    });

    $(document).on('click', '.ai_line_quick_add', function () {
        var idx = $(this).closest('tr').index();
        state.pendingQuickAddIdx = idx;
        var line = state.lines[idx];

        // Read whatever price the user may have edited in the review table before
        // carrying it into the quick-add form.
        var priceVal = parseFloat(rowFor(idx).find('.ai_line_price').val());
        if (!isNaN(priceVal) && priceVal >= 0) {
            line.unit_price = priceVal;
        }

        var container = $('.quick_add_product_modal');
        var qs = 'product_name=' + encodeURIComponent(line.name || '');
        if (line.sku) {
            qs += '&sku=' + encodeURIComponent(line.sku);
        }
        if (line.unit_price > 0) {
            qs += '&purchase_price=' + encodeURIComponent(line.unit_price);
        }

        $.ajax({
            url: '/products/quick_add?' + qs,
            dataType: 'html',
            success: function (result) {
                $(container).html(result).modal('show');
                if (line.unit_price > 0) {
                    $('#single_dpp').trigger('change');
                }
            },
        });
    });

    // Existing quick-add-product modal (resources/views/product/partials/quick_add_product.blade.php)
    // fires this on successful save regardless of what page it was opened from.
    $(document).on('quickProductAdded', function () {
        if (state.pendingQuickAddIdx === null) {
            return;
        }
        var idx = state.pendingQuickAddIdx;
        state.pendingQuickAddIdx = null;
        var line = state.lines[idx];
        if (!line) {
            return;
        }
        line.status = 'added';
        rowFor(idx).find('.ai-line-match').html('<span class="label label-success"><i class="fa fa-check"></i> '
            + escapeHtml(t('scan_product_added', 'Added')) + '</span>');
    });
})();
