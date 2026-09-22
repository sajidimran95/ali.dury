<script>
(function () {
    var JAPSAI_ICON = '<svg class="japsai-icon" viewBox="0 0 24 24" width="15" height="15" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">'
        + '<path d="M12 2.5l1.65 5.05L18.7 9.2l-5.05 1.65L12 15.9l-1.65-5.05L5.3 9.2l5.05-1.65L12 2.5z" fill="currentColor"/>'
        + '<path d="M18.2 14l.85 2.55 2.55.85-2.55.85L18.2 20.8l-.85-2.55-2.55-.85 2.55-.85.85-2.55z" fill="currentColor" opacity="0.9"/>'
        + '<path d="M5.2 14.8l.55 1.65 1.65.55-1.65.55L5.2 19.2l-.55-1.65-1.65-.55 1.65-.55.55-1.65z" fill="currentColor" opacity="0.75"/>'
        + '</svg>';

    // Global once — survives modal reloads; no “stuck disabled” after reopen only.
    if (window.__japsAiChatBound) {
        return;
    }
    window.__japsAiChatBound = true;

    var chatXhr = null;
    var sending = false;

    function $chatBox() {
        return $('#ai-chat-messages');
    }
    function $form() {
        return $('#ai-chat-form');
    }
    function setBusy(on) {
        sending = !!on;
        var $f = $form();
        if (!$f.length) return;
        $f.find('#ai-chat-input, .ai-send, .ai-chip').prop('disabled', !!on);
        $f.find('.ai-send').toggleClass('is-busy', !!on);
        // Chips sit outside form
        $('#ai-chips .ai-chip').prop('disabled', !!on);
    }

    function appendUser($box, msg) {
        var $row = $('<div class="ai-msg user"><div class="ai-avatar"><i class="fa fa-user"></i></div><div class="ai-bubble"></div></div>');
        $row.find('.ai-bubble').text(msg);
        $box.append($row);
    }
    function appendTool($box, tool) {
        if (!tool) return;
        $box.append($('<div class="ai-tool"><i class="fa fa-check"></i> </div>').append(document.createTextNode(tool)));
    }
    function appendBot($box, msg) {
        var $row = $('<div class="ai-msg bot"><div class="ai-avatar" title="JapsAI">' + JAPSAI_ICON + '</div><div class="ai-bubble"></div></div>');
        $row.find('.ai-bubble').html($('<div>').text(msg || '').html().replace(/\n/g, '<br>'));
        $box.append($row);
    }

    window.initAiChat = function () {
        // Always clear stuck busy state when chat pane mounts / refocuses
        setBusy(false);
        var $box = $chatBox();
        if ($box.length) {
            $box.scrollTop($box[0].scrollHeight);
        }
        var $input = $('#ai-chat-input');
        if ($input.length && !$input.prop('disabled')) {
            // Soft focus only when chat tab is visible
            var $pane = $('#ai-pane-chat');
            if (!$pane.length || $pane.is(':visible')) {
                setTimeout(function () {
                    try { $input.trigger('focus'); } catch (e) {}
                }, 50);
            }
        }
    };

    window.sendAiMessage = function (msg) {
        msg = $.trim(msg || '');
        if (!msg) return;

        var $box = $chatBox();
        if (!$box.length) {
            toastr.error('{{ __('aiassistance::lang.chat_error') }}');
            return;
        }

        // Prevent double-send freeze (looks “disabled” until reopen)
        if (sending) {
            return;
        }

        appendUser($box, msg);
        $box.scrollTop($box[0].scrollHeight);
        $('#ai-chat-input').val('');

        var $pending = $('<div class="ai-pending" style="margin:0 0 10px 38px;color:#6b7280;">JapsAI is thinking…</div>');
        $box.append($pending);
        setBusy(true);

        var reqToken = (window.__japsAiReqToken = (window.__japsAiReqToken || 0) + 1);

        chatXhr = $.ajax({
            url: '{{ route('ai.chat.send') }}',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', message: msg },
            timeout: 90000,
        })
            .done(function (res) {
                if (reqToken !== window.__japsAiReqToken) return;
                if (res && res.success) {
                    appendTool($box, res.tool);
                    appendBot($box, res.reply);
                } else {
                    toastr.error((res && res.msg) || '{{ __('aiassistance::lang.chat_error') }}');
                    appendBot($box, (res && res.msg) || '{{ __('aiassistance::lang.chat_error') }}');
                }
            })
            .fail(function (xhr, status) {
                if (reqToken !== window.__japsAiReqToken) return;
                if (status === 'abort') {
                    return;
                }
                var err =
                    (xhr.responseJSON && xhr.responseJSON.msg) ||
                    (status === 'timeout'
                        ? 'Request timed out. Please try again.'
                        : '{{ __('aiassistance::lang.chat_error') }}');
                toastr.error(err);
                appendBot($box, err);
            })
            .always(function () {
                if (reqToken !== window.__japsAiReqToken) return;
                $pending.remove();
                setBusy(false);
                chatXhr = null;
                if ($box.length && $box[0]) {
                    $box.scrollTop($box[0].scrollHeight);
                }
                // Return focus so input never feels "dead"
                setTimeout(function () {
                    var $input = $('#ai-chat-input');
                    if ($input.length) {
                        $input.prop('disabled', false).trigger('focus');
                    }
                    $('#ai-chat-form .ai-send, #ai-chips .ai-chip').prop('disabled', false);
                }, 0);
            });
    };

    function appendBotHtml($box, html) {
        var $row = $('<div class="ai-msg bot"><div class="ai-avatar" title="JapsAI">' + JAPSAI_ICON + '</div><div class="ai-bubble"></div></div>');
        $row.find('.ai-bubble').html(html);
        $box.append($row);
    }

    window.sendAiInvoiceFile = function (file) {
        if (!file || sending) return;
        var $box = $chatBox();
        if (!$box.length) return;

        appendUser($box, '📎 ' + (file.name || 'invoice image'));
        $box.scrollTop($box[0].scrollHeight);

        var $pending = $('<div class="ai-pending" style="margin:0 0 10px 38px;color:#6b7280;">{{ __('aiassistance::lang.invoice_reading') }}</div>');
        $box.append($pending);
        setBusy(true);

        var fd = new FormData();
        fd.append('invoice_file', file);
        fd.append('_token', '{{ csrf_token() }}');

        $.ajax({
            url: '{{ route('ai.invoice.extract') }}',
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            timeout: 120000,
        })
            .done(function (res) {
                if (res && res.success) {
                    var d = res.data || {};
                    var count = (d.lines || []).length;
                    var preview = res.preview || null;
                    var html = 'Read ' + count + ' item(s)'
                        + (d.supplier_name ? ' from ' + $('<div>').text(d.supplier_name).html() : '')
                        + (d.ref_no ? ', ref ' + $('<div>').text(d.ref_no).html() : '')
                        + (d.total ? ', total ' + d.total : '') + '.';

                    if (preview) {
                        html += '<br><br><strong>Ready to confirm</strong>';
                        html += '<br>✓ ' + (preview.matched_count || 0) + ' item(s) OK in product list';
                        if (preview.missing_count > 0) {
                            html += '<br>⚠ ' + preview.missing_count + ' item(s) not in list — will auto-add on confirm:';
                            html += '<ul style="margin:4px 0 0 18px;padding:0;">';
                            (preview.missing || []).slice(0, 12).forEach(function (row) {
                                var lab = (row.sku ? row.sku + ' — ' : '') + (row.name || '');
                                html += '<li>' + $('<div>').text(lab).html()
                                    + (row.unit_price != null ? ' @ ' + row.unit_price : '')
                                    + '</li>';
                            });
                            if (preview.missing_count > 12) {
                                html += '<li>…and ' + (preview.missing_count - 12) + ' more</li>';
                            }
                            html += '</ul>';
                        } else {
                            html += '<br>✓ All invoice items found in product list';
                        }

                        var sup = preview.supplier || {};
                        if (sup.will_create) {
                            html += '<br>⚠ Supplier <strong>' + $('<div>').text(sup.name || '').html()
                                + '</strong> not in list — will auto-add on confirm';
                        } else if (sup.found) {
                            html += '<br>✓ Supplier OK: ' + $('<div>').text(sup.name || '').html();
                        } else {
                            html += '<br>✓ No supplier name on invoice (will use default if needed)';
                        }

                        if (preview.existing_po) {
                            html += '<br>✓ Existing PO <strong>' + $('<div>').text(preview.existing_po.ref_no || '').html()
                                + '</strong> will be updated with invoice amounts';
                        } else {
                            html += '<br>✓ New draft PO will be created (not sent to vendor)';
                        }

                        html += '<br><br><button type="button" class="tw-dw-btn tw-dw-btn-sm tw-dw-btn-primary tw-text-white ai-invoice-confirm-btn"'
                            + ' data-token="' + $('<div>').text(res.token || '').html() + '"'
                            + ' style="margin-top:4px;"><i class="fa fa-check"></i> Confirm — add missing &amp; create/update PO</button>';
                        html += ' <span class="text-muted" style="font-size:12px;">Nothing is saved until you confirm.</span>';
                    }

                    appendBotHtml($box, html);
                } else {
                    toastr.error((res && res.msg) || '{{ __('aiassistance::lang.chat_error') }}');
                    appendBot($box, (res && res.msg) || '{{ __('aiassistance::lang.chat_error') }}');
                }
            })
            .fail(function (xhr, status) {
                var err = (xhr.responseJSON && xhr.responseJSON.msg)
                    || (status === 'timeout' ? 'Request timed out. Try a smaller image.' : '{{ __('aiassistance::lang.chat_error') }}');
                toastr.error(err);
                appendBot($box, err);
            })
            .always(function () {
                $pending.remove();
                setBusy(false);
                if ($box.length && $box[0]) {
                    $box.scrollTop($box[0].scrollHeight);
                }
            });
    };

    window.confirmAiInvoice = function (token, $btn) {
        var $box = $('#ai-chat-box');
        if (!$box.length || !token || sending) return;

        if ($btn && $btn.length) {
            $btn.prop('disabled', true).text('Working…');
        }
        var $pending = $('<div class="ai-pending" style="margin:0 0 10px 38px;color:#6b7280;">Adding missing items/supplier and creating PO…</div>');
        $box.append($pending);
        setBusy(true);

        $.ajax({
            url: '{{ route('ai.invoice.confirm') }}',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', token: token },
            timeout: 120000,
        })
            .done(function (res) {
                if (!(res && res.success)) {
                    var err = (res && res.msg) || '{{ __('aiassistance::lang.chat_error') }}';
                    toastr.error(err);
                    appendBot($box, err);
                    if ($btn && $btn.length) {
                        $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Confirm — add missing &amp; create/update PO');
                    }
                    return;
                }

                var draft = res.draft_po || {};
                var html = '';

                if (draft.created_products_count) {
                    html += '✓ Added ' + draft.created_products_count + ' product(s) to the list';
                    var cps = draft.created_products || [];
                    if (cps.length) {
                        html += '<ul style="margin:4px 0 0 18px;padding:0;">';
                        cps.slice(0, 10).forEach(function (p) {
                            html += '<li>' + $('<div>').text((p.sku ? p.sku + ' — ' : '') + (p.name || '')).html() + '</li>';
                        });
                        html += '</ul>';
                    }
                    html += '<br>';
                }
                if (draft.created_supplier) {
                    html += '✓ Supplier added: <strong>'
                        + $('<div>').text(draft.created_supplier.name || '').html() + '</strong><br>';
                }

                function costAlertHtml(changes) {
                    if (!changes || !changes.length) return '';
                    var t = '<br><strong>Admin alert — PO cost updated on product list</strong> (sell price unchanged):';
                    t += '<table class="table table-condensed" style="margin:6px 0;font-size:12px;"><thead><tr>'
                        + '<th>Item</th><th>SKU</th><th>Old</th><th>New</th><th>PO</th></tr></thead><tbody>';
                    changes.slice(0, 20).forEach(function (row) {
                        t += '<tr><td>' + $('<div>').text(row.product || '').html() + '</td>'
                            + '<td>' + $('<div>').text(row.sku || '').html() + '</td>'
                            + '<td>' + Number(row.old_cost || 0).toFixed(2) + '</td>'
                            + '<td><strong>' + Number(row.new_cost || 0).toFixed(2) + '</strong></td>'
                            + '<td>' + $('<div>').text(row.po_ref || '').html() + '</td></tr>';
                    });
                    t += '</tbody></table>';
                    if (typeof toastr !== 'undefined') {
                        toastr.warning(changes.length + ' item(s) PO cost updated on the product.');
                    }
                    return t;
                }

                if (draft.matched_existing && draft.edit_url) {
                    html += '<strong>PO ' + $('<div>').text(draft.ref_no || '').html()
                        + '</strong> updated from invoice'
                        + (draft.updated_count ? ' (' + draft.updated_count + ' line change(s))' : '')
                        + '. Not sent to vendor.';
                    html += costAlertHtml(draft.cost_changes);
                    html += '<br><a href="' + draft.edit_url + '" class="tw-dw-btn tw-dw-btn-sm tw-dw-btn-primary tw-text-white" style="margin-top:6px;display:inline-block;"><i class="fa fa-file-text-o"></i> Open PO</a>';
                } else if (draft.created && draft.edit_url) {
                    html += '<strong>Draft PO ' + $('<div>').text(draft.ref_no || '').html()
                        + '</strong> created with invoice prices ('
                        + (draft.matched || 0) + ' line(s)). Not sent to vendor.';
                    html += costAlertHtml(draft.cost_changes);
                    html += '<br><a href="' + draft.edit_url + '" class="tw-dw-btn tw-dw-btn-sm tw-dw-btn-primary tw-text-white" style="margin-top:6px;display:inline-block;"><i class="fa fa-file-text-o"></i> Open draft PO</a>';
                } else {
                    html += $('<div>').text(draft.msg || 'Done.').html();
                }

                appendBotHtml($box, html);
                if (typeof toastr !== 'undefined') {
                    toastr.success('Confirmed — list updated and PO ready.');
                }
            })
            .fail(function (xhr) {
                var err = (xhr.responseJSON && xhr.responseJSON.msg) || '{{ __('aiassistance::lang.chat_error') }}';
                toastr.error(err);
                appendBot($box, err);
                if ($btn && $btn.length) {
                    $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Confirm — add missing &amp; create/update PO');
                }
            })
            .always(function () {
                $pending.remove();
                setBusy(false);
                if ($box.length && $box[0]) {
                    $box.scrollTop($box[0].scrollHeight);
                }
            });
    };

    // Delegated events — work even if modal HTML is replaced many times
    $(document)
        .off('change.ai', '#ai-invoice-attach')
        .on('change.ai', '#ai-invoice-attach', function () {
            var file = this.files && this.files[0];
            if (file) {
                window.sendAiInvoiceFile(file);
            }
            $(this).val('');
        })
        .off('click.aiConfirm', '.ai-invoice-confirm-btn')
        .on('click.aiConfirm', '.ai-invoice-confirm-btn', function (e) {
            e.preventDefault();
            var token = $(this).data('token');
            window.confirmAiInvoice(token, $(this));
        })
        .off('submit.ai', '#ai-chat-form')
        .on('submit.ai', '#ai-chat-form', function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (sending) return false;
            var msg = $.trim($('#ai-chat-input').val() || '');
            if (!msg) return false;
            window.sendAiMessage(msg);
            return false;
        })
        .off('click.ai', '#ai-chips .ai-chip, .ai-chip')
        .off('click.ai', '#ai-chips .ai-chip')
        .on('click.ai', '#ai-chips .ai-chip', function (e) {
            e.preventDefault();
            if (sending) return;
            var q = $(this).data('q');
            if (!q) return;
            $('#ai-chips .ai-chip').removeClass('active');
            $(this).addClass('active');
            window.sendAiMessage(q);
        })
        .off('keydown.ai', '#ai-chat-input')
        .on('keydown.ai', '#ai-chat-input', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (sending) return;
                var msg = $.trim($(this).val() || '');
                if (msg) window.sendAiMessage(msg);
            }
        });
})();
</script>
