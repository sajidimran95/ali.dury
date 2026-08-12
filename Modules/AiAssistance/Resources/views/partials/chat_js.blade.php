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
                    var summary = 'Read ' + count + ' item(s)'
                        + (d.supplier_name ? ' from ' + $('<div>').text(d.supplier_name).html() : '')
                        + (d.total ? ', total ' + d.total : '') + '.';
                    var link = '{{ url('/purchases/create') }}?ai_invoice_token=' + encodeURIComponent(res.token);
                    appendBotHtml($box, summary + '<br><a href="' + link + '" class="tw-dw-btn tw-dw-btn-sm tw-dw-btn-primary tw-text-white" style="margin-top:6px;display:inline-block;"><i class="fa fa-check"></i> {{ __('aiassistance::lang.invoice_review_link') }}</a>');
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
        .on('click.ai', '#ai-chips .ai-chip, .ai-chip', function (e) {
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
