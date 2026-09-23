/* JapsAI — invoice-to-purchase extraction.
 * Reads a vendor invoice photo into the existing Add Purchase form (purchase.js /
 * purchase/create.blade.php) and lets the user review/edit before the normal
 * "Save" submit runs. This file never talks to the database directly — it only
 * drives the same endpoints a human filling the form would hit:
 *   - /purchases/get_products            (product search / matching)
 *   - /purchases/get_purchase_entry_row  (renders + appends a purchase row)
 *   - /products/quick_add                (create-product modal, existing flow)
 */
(function () {
    'use strict';

    if (window.__aiInvoiceBound) {
        return;
    }
    window.__aiInvoiceBound = true;

    var state = {
        lines: [], // {name, sku, quantity, unit_price, status: 'searching'|'matched'|'review', product_id, variation_id, text}
        originalFile: null,
        imageUrl: null,
        isPdf: false,
        pendingQuickAddIdx: null,
    };

    function t(key, fallback) {
        return (window.AI_INVOICE_LANG && window.AI_INVOICE_LANG[key]) || fallback;
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
        state.imageUrl = null;
        state.isPdf = false;
        state.pendingQuickAddIdx = null;
        $('#ai_invoice_file').val('');
        $('#ai_invoice_preview_img').attr('src', '').hide();
        $('#ai_invoice_preview_pdf').hide();
        $('#ai_invoice_header_block').hide();
        $('#ai_invoice_review_body').empty();
        $('#ai_invoice_review_table').hide();
        $('#ai_invoice_select_all').prop('checked', true);
        $('#ai_invoice_status').text('');
        $('#ai_invoice_extract_btn').prop('disabled', true).show();
        $('#ai_invoice_insert_btn').prop('disabled', true).hide();
    }

    function openModal() {
        resetModal();
        $('#ai_invoice_modal').modal('show');
    }
    window.openAiInvoiceModal = openModal;

    $(document).on('click', '.open_ai_invoice_modal', function (e) {
        e.preventDefault();
        openModal();
    });

    $(document).on('change', '#ai_invoice_file', function () {
        var file = this.files && this.files[0];
        $('#ai_invoice_extract_btn').prop('disabled', !file);
        if (!file) {
            return;
        }
        state.originalFile = file;
        if (file.type === 'application/pdf') {
            $('#ai_invoice_preview_img').hide();
            $('#ai_invoice_preview_pdf_name').text(file.name);
            $('#ai_invoice_preview_pdf').show();
        } else {
            $('#ai_invoice_preview_pdf').hide();
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#ai_invoice_preview_img').attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        }
    });

    // ---- extraction --------------------------------------------------

    $(document).on('click', '#ai_invoice_extract_btn', function () {
        var file = state.originalFile;
        if (!file) {
            return;
        }
        var fd = new FormData();
        fd.append('invoice_file', file);
        fd.append('_token', csrfToken());

        $('#ai_invoice_status').text(t('invoice_reading', 'JapsAI is reading the invoice...'));
        $('#ai_invoice_extract_btn').prop('disabled', true);

        $.ajax({
            url: window.AI_INVOICE_EXTRACT_URL,
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            timeout: 120000,
        })
            .done(function (res) {
                if (res && res.success) {
                    state.imageUrl = res.image_url || null;
                    state.isPdf = !!res.is_pdf;
                    renderExtraction(res.data);
                } else {
                    toastr.error((res && res.msg) || 'Could not read invoice.');
                }
            })
            .fail(function (xhr, status) {
                var msg = (xhr.responseJSON && xhr.responseJSON.msg)
                    || (status === 'timeout' ? 'Request timed out. Try a smaller file.' : 'Could not read invoice.');
                toastr.error(msg);
            })
            .always(function () {
                $('#ai_invoice_status').text('');
                $('#ai_invoice_extract_btn').prop('disabled', false);
            });
    });

    function renderExtraction(data) {
        data = data || {};
        $('#ai_invoice_header_supplier').text(data.supplier_name || t('invoice_not_detected', 'not detected'));
        $('#ai_invoice_header_ref').text(data.ref_no || t('invoice_not_detected', 'not detected'));
        $('#ai_invoice_header_date').text(data.invoice_date || t('invoice_not_detected', 'not detected'));
        $('#ai_invoice_header_block').show();

        if (data.ref_no) {
            $('#ref_no').val(data.ref_no);
        }
        if (data.supplier_name) {
            trySupplierMatch(data.supplier_name);
        }

        state.lines = (data.lines || []).map(function (l) {
            return {
                name: l.name,
                sku: l.sku,
                quantity: (typeof l.quantity === 'number' && l.quantity > 0) ? l.quantity : 1,
                unit_price: typeof l.unit_price === 'number' ? l.unit_price : 0,
                status: 'searching',
                product_id: null,
                variation_id: null,
                text: null,
            };
        });

        var $body = $('#ai_invoice_review_body').empty();
        state.lines.forEach(function (line, idx) {
            $body.append(buildReviewRow(line, idx));
            searchMatchForLine(idx);
        });
        $('#ai_invoice_review_table').toggle(state.lines.length > 0);
        $('#ai_invoice_insert_btn').show().prop('disabled', state.lines.length === 0);
        $('#ai_invoice_extract_btn').hide();
    }

    function buildReviewRow(line) {
        return $(
            '<tr>' +
                '<td><input type="checkbox" class="ai_line_include" checked></td>' +
                '<td class="ai-line-name">' + escapeHtml(line.name) +
                    (line.sku ? ' <small class="text-muted">[' + escapeHtml(line.sku) + ']</small>' : '') +
                '</td>' +
                '<td><input type="number" step="any" min="0" class="form-control input-sm ai_line_qty" value="' + line.quantity + '"></td>' +
                '<td><input type="number" step="any" min="0" class="form-control input-sm ai_line_price" value="' + line.unit_price + '"></td>' +
                '<td class="ai-line-match">' +
                    '<span class="text-muted"><i class="fa fa-spinner fa-spin"></i></span>' +
                '</td>' +
            '</tr>'
        );
    }

    function rowFor(idx) {
        return $('#ai_invoice_review_body tr').eq(idx);
    }

    // ---- product matching --------------------------------------------

    function flattenProductResults(res) {
        if (!res || !res.length) {
            return [];
        }
        return res.filter(function (r) {
            return r.variation_id && r.variation_id != 0;
        });
    }

    function searchMatchForLine(idx) {
        var line = state.lines[idx];
        var term = line.sku || line.name;
        if (!term) {
            renderMatchCell(idx, []);
            return;
        }
        $.getJSON('/purchases/get_products', { location_id: $('#location_id').val(), term: term })
            .done(function (res) {
                renderMatchCell(idx, flattenProductResults(res));
            })
            .fail(function () {
                renderMatchCell(idx, []);
            });
    }

    function renderMatchCell(idx, candidates) {
        var $cell = rowFor(idx).find('.ai-line-match');
        var line = state.lines[idx];

        if (candidates.length === 1) {
            line.status = 'matched';
            line.product_id = candidates[0].product_id;
            line.variation_id = candidates[0].variation_id;
            line.text = candidates[0].text;
            $cell.html('<span class="label label-success"><i class="fa fa-check"></i> ' + escapeHtml(candidates[0].text) + '</span>');
            return;
        }

        line.status = 'review';
        line.product_id = null;
        line.variation_id = null;

        var $wrap = $('<div class="ai-line-review"></div>');
        $wrap.append(
            '<div class="text-warning" style="margin-bottom:4px;"><i class="fa fa-exclamation-triangle"></i> '
            + t('invoice_line_no_match', 'No match — search or add product') + '</div>'
        );
        var $input = $('<input type="text" class="form-control input-sm ai_line_search" placeholder="Search product...">');
        var $addBtn = $('<button type="button" class="btn btn-link btn-xs ai_line_quick_add"><i class="fa fa-plus"></i> ' + escapeHtml('Add new product') + '</button>');
        $wrap.append($input).append($addBtn);
        $cell.empty().append($wrap);

        $input.autocomplete({
            source: function (request, response) {
                $.getJSON('/purchases/get_products', { location_id: $('#location_id').val(), term: request.term }, function (res) {
                    response(flattenProductResults(res));
                });
            },
            minLength: 2,
            select: function (event, ui) {
                event.preventDefault();
                $input.val(ui.item.text);
                var line = state.lines[idx];
                line.status = 'matched';
                line.product_id = ui.item.product_id;
                line.variation_id = ui.item.variation_id;
                line.text = ui.item.text;
                $wrap.prepend('<span class="label label-success"><i class="fa fa-check"></i> ' + escapeHtml(ui.item.text) + '</span><br>');
            },
        }).autocomplete('instance')._renderItem = function (ul, item) {
            return $('<li>').append('<div>' + escapeHtml(item.text) + '</div>').appendTo(ul);
        };
    }

    $(document).on('click', '.ai_line_quick_add', function () {
        var idx = $(this).closest('tr').index();
        state.pendingQuickAddIdx = idx;
        var line = state.lines[idx];
        var container = $('.quick_add_product_modal');

        // Carry the invoice's own SKU/barcode + unit price into the quick-add form so
        // the new product isn't retyped from scratch — user still reviews before saving.
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
                    // Refresh purchase inc-tax only; selling price stays independent.
                    $('#single_dpp').trigger('change');
                }
            },
        });
    });

    // Existing quick-add-product modal fires this on successful save (see
    // resources/views/product/partials/quick_add_product.blade.php). It also calls
    // get_purchase_entry_row() itself, so we just need to apply this line's qty/price
    // to the row it inserts and clear the review flag.
    $(document).on('quickProductAdded', function (e) {
        if (state.pendingQuickAddIdx === null) {
            return;
        }
        var idx = state.pendingQuickAddIdx;
        state.pendingQuickAddIdx = null;
        var line = state.lines[idx];
        if (!line) {
            return;
        }
        line.status = 'inserted';
        rowFor(idx).find('.ai-line-match').html('<span class="label label-success"><i class="fa fa-check"></i> Added</span>');
        setTimeout(function () {
            applyLineToRow(line, $('#purchase_entry_table tbody tr').last());
        }, 150);
    });

    // ---- supplier matching ---------------------------------------------

    function trySupplierMatch(name) {
        $.getJSON('/purchases/get_suppliers', { q: name }).done(function (res) {
            if (res && res.results && res.results.length === 1) {
                setSupplier(res.results[0].id, res.results[0].text);
            } else if (res && res.length === 1) {
                setSupplier(res[0].id, res[0].text);
            }
        });
    }

    function setSupplier(id, text) {
        var $select = $('#supplier_id');
        if (!$select.find('option[value="' + id + '"]').length) {
            $select.append(new Option(text, id, true, true));
        } else {
            $select.val(id);
        }
        $select.trigger('change');
    }

    // ---- inserting rows into the real purchase table --------------------

    function applyLineToRow(line, $row) {
        if (!$row || !$row.length) {
            return;
        }
        __write_number($row.find('input.purchase_unit_cost_without_discount'), line.unit_price, true);
        $row.find('input.purchase_unit_cost_without_discount').trigger('change');

        __write_number($row.find('input.purchase_quantity'), line.quantity, true);
        $row.find('input.purchase_quantity').trigger('change');
    }

    function insertMatchedLine(idx) {
        var line = state.lines[idx];
        if (!line || line.status !== 'matched' || !line.product_id) {
            return $.Deferred().resolve().promise();
        }

        var row_count = $('#row_count').val();
        var data = {
            product_id: line.product_id,
            variation_id: line.variation_id,
            row_count: row_count,
            location_id: $('#location_id').val(),
            supplier_id: $('#supplier_id').val(),
        };
        if ($('#is_purchase_order').length) {
            data.is_purchase_order = true;
        }

        return $.ajax({
            method: 'POST',
            url: '/purchases/get_purchase_entry_row',
            dataType: 'html',
            data: data,
        }).done(function (result) {
            append_purchase_lines(result, row_count);
            var $row = $('#purchase_entry_table tbody tr').last();
            applyLineToRow(line, $row);
            line.status = 'inserted';
        });
    }

    // Read the qty/price the user may have edited in the review table before confirming.
    function syncEditsFromReviewTable() {
        state.lines.forEach(function (line, idx) {
            var $row = rowFor(idx);
            var qty = parseFloat($row.find('.ai_line_qty').val());
            var price = parseFloat($row.find('.ai_line_price').val());
            if (!isNaN(qty) && qty > 0) {
                line.quantity = qty;
            }
            if (!isNaN(price) && price >= 0) {
                line.unit_price = price;
            }
        });
    }

    function attachOriginalDocument() {
        var input = document.getElementById('upload_document');
        if (!input || typeof window.DataTransfer === 'undefined') {
            return;
        }
        if (state.originalFile) {
            var dt = new DataTransfer();
            dt.items.add(state.originalFile);
            input.files = dt.files;
        } else if (state.imageUrl) {
            fetch(state.imageUrl).then(function (r) {
                return r.blob();
            }).then(function (blob) {
                var name = state.isPdf ? 'invoice.pdf' : 'invoice.jpg';
                var type = blob.type || (state.isPdf ? 'application/pdf' : 'image/jpeg');
                var file = new File([blob], name, { type: type });
                var dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
            }).catch(function () {});
        }
    }

    // "Select all" header checkbox toggles every line's own checkbox.
    $(document).on('change', '#ai_invoice_select_all', function () {
        $('#ai_invoice_review_body .ai_line_include').prop('checked', $(this).is(':checked'));
    });

    // Keep the header checkbox in sync when lines are toggled individually.
    $(document).on('change', '#ai_invoice_review_body .ai_line_include', function () {
        var $all = $('#ai_invoice_review_body .ai_line_include');
        $('#ai_invoice_select_all').prop('checked', $all.length > 0 && $all.length === $all.filter(':checked').length);
    });

    function isLineIncluded(idx) {
        return rowFor(idx).find('.ai_line_include').is(':checked');
    }

    $(document).on('click', '#ai_invoice_insert_btn', function () {
        syncEditsFromReviewTable();
        var $btn = $(this).prop('disabled', true);

        var matchedIdx = [];
        state.lines.forEach(function (line, idx) {
            if (line.status === 'matched' && isLineIncluded(idx)) {
                matchedIdx.push(idx);
            }
        });

        var chain = $.Deferred().resolve().promise();
        matchedIdx.forEach(function (idx) {
            chain = chain.then(function () {
                return insertMatchedLine(idx);
            });
        });

        chain.then(function () {
            attachOriginalDocument();
            $btn.prop('disabled', false);

            // Only nag about lines the user actually wants inserted — an unchecked
            // "needs review" line is one they've deliberately chosen to skip.
            var stillNeedsReview = state.lines.some(function (line, idx) {
                return line.status === 'review' && isLineIncluded(idx);
            });
            if (stillNeedsReview) {
                toastr.warning(t('invoice_needs_review', 'Some items need your review before you can insert them.'));
            } else {
                toastr.success('Invoice items added — review the purchase table, then Save.');
                $('#ai_invoice_modal').modal('hide');
            }
        });
    });

    // ---- deep link: ?ai_invoice_token=xxx (from JapsAI chat) ------------

    $(function () {
        var params = new URLSearchParams(window.location.search);
        var token = params.get('ai_invoice_token');
        if (token && window.AI_INVOICE_RESULT_URL_BASE) {
            openModal();
            $('#ai_invoice_extract_btn').hide();
            $('#ai_invoice_status').text(t('invoice_reading', 'Loading extracted invoice...'));
            $.getJSON(window.AI_INVOICE_RESULT_URL_BASE + '/' + encodeURIComponent(token))
                .done(function (res) {
                    if (res && res.success) {
                        state.imageUrl = res.image_url || null;
                        state.isPdf = !!res.is_pdf;
                        if (state.imageUrl && state.isPdf) {
                            $('#ai_invoice_preview_pdf_name').text('Uploaded invoice (PDF)');
                            $('#ai_invoice_preview_pdf').show();
                        } else if (state.imageUrl) {
                            $('#ai_invoice_preview_img').attr('src', state.imageUrl).show();
                        }
                        renderExtraction(res.data);
                    } else {
                        toastr.error((res && res.msg) || 'Could not load extracted invoice.');
                    }
                })
                .fail(function () {
                    toastr.error('Could not load extracted invoice.');
                })
                .always(function () {
                    $('#ai_invoice_status').text('');
                });
        } else if (params.get('open_ai_invoice') === '1') {
            openModal();
        }
    });
})();
