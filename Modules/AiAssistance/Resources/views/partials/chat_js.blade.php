<script>
(function () {
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
        var $row = $('<div class="ai-msg bot"><div class="ai-avatar"><i class="fa fa-android"></i></div><div class="ai-bubble"></div></div>');
        $row.find('.ai-bubble').text(msg);
        $box.append($row);
    }

    window.initAiChat = function () {
        var $box = $('#ai-chat-messages');
        if (!$box.length) return;
        $box.scrollTop($box[0].scrollHeight);

        $('#ai-chat-form').off('submit.ai').on('submit.ai', function (e) {
            e.preventDefault();
            var $input = $('#ai-chat-input');
            var msg = $.trim($input.val());
            if (!msg) return;
            sendAiMessage(msg);
            $input.val('');
        });

        $('.ai-chip').off('click.ai').on('click.ai', function () {
            var q = $(this).data('q');
            $('.ai-chip').removeClass('active');
            $(this).addClass('active');
            $('#ai-chat-input').val(q);
            sendAiMessage(q);
        });
    };

    window.sendAiMessage = function (msg) {
        var $box = $('#ai-chat-messages');
        appendUser($box, msg);
        $box.scrollTop($box[0].scrollHeight);

        var $pending = $('<div class="ai-pending" style="margin:0 0 10px 38px;color:#6b7280;">…</div>');
        $box.append($pending);

        $.ajax({
            url: '{{ route('ai.chat.send') }}',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', message: msg },
            success: function (res) {
                $pending.remove();
                if (res.success) {
                    appendTool($box, res.tool);
                    appendBot($box, res.reply);
                } else {
                    toastr.error(res.msg || '{{ __('aiassistance::lang.chat_error') }}');
                }
                $box.scrollTop($box[0].scrollHeight);
            },
            error: function (xhr) {
                $pending.remove();
                toastr.error((xhr.responseJSON && xhr.responseJSON.msg) || '{{ __('aiassistance::lang.chat_error') }}');
            }
        });
    };

    $(function () { window.initAiChat(); });
})();
</script>
