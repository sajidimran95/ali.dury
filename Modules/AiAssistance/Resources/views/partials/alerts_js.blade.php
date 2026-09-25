{{-- Admin alerts: live list in Insights, polled every 15s while visible --}}
<script>
(function () {
    if (window.japsAiAlerts) return;

    var POLL_MS = 15000;
    var VOICE_KEY = 'japsai_alert_voice';
    var timer = null;
    var lastId = null;
    var busy = false;

    var icons = { po_cost: 'fa-money', invoice: 'fa-file-text-o', price_edit: 'fa-tag' };

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function box() { return $('#ai-admin-alerts'); }

    function voiceOn() {
        try { return localStorage.getItem(VOICE_KEY) !== '0'; } catch (e) { return true; }
    }

    function setBadges(n) {
        var $c = $('#ai-alerts-unread');
        $c.text(n).prop('hidden', !n);
        $('.ai-alert-badge').text(n > 99 ? '99+' : n).prop('hidden', !n);
    }

    function render(alerts) {
        var $list = $('#ai-alerts-list');
        if (!alerts.length) {
            $list.html('<p class="ai-alerts__empty">No admin alerts yet. PO cost changes, invoice issues and price edits will show here.</p>');
            return;
        }
        var html = alerts.slice(0, 8).map(function (a) {
            var tag = a.url ? 'a' : 'div';
            var href = a.url ? ' href="' + esc(a.url) + '"' : '';
            return '<' + tag + href + ' class="ai-alert ai-alert--' + esc(a.type) + (a.read ? '' : ' is-unread') + '">' +
                '<span class="ai-alert__icon"><i class="fa ' + (icons[a.type] || 'fa-bell') + '"></i></span>' +
                '<span class="ai-alert__body">' +
                    '<span class="ai-alert__title">' + esc(a.title) + '</span>' +
                    '<span class="ai-alert__msg">' + esc(a.message) + '</span>' +
                    '<span class="ai-alert__time" title="' + esc(a.at) + '">' + esc(a.ago) + '</span>' +
                '</span>' +
            '</' + tag + '>';
        }).join('');
        $list.html(html);
    }

    function announce(fresh) {
        if (!fresh.length) return;
        if (typeof toastr !== 'undefined') {
            fresh.slice(0, 3).forEach(function (a) { toastr.warning(a.title, 'JapsAI admin alert'); });
        }
        if (voiceOn() && window.japsAiVoice) {
            var text = 'New admin alert' + (fresh.length > 1 ? 's: ' + fresh.length + '. ' : ': ') +
                fresh.slice(0, 3).map(function (a) { return a.title; }).join('. ');
            window.japsAiVoice.speak(text);
        }
    }

    function speakUnreadOnOpen(alerts, unread) {
        if (!unread || !voiceOn() || !window.japsAiVoice || !box().is(':visible')) return;
        var titles = alerts.filter(function (a) { return !a.read; }).slice(0, 3)
            .map(function (a, i) { return (i + 1) + '. ' + a.title; });
        window.japsAiVoice.speak('You have ' + unread + ' unread admin alert' + (unread > 1 ? 's' : '') + '. ' + titles.join('. '));
    }

    function load() {
        var $b = box();
        if (!$b.length || busy) return;
        busy = true;
        $.ajax({ url: $b.data('url'), dataType: 'json', cache: false, timeout: 15000 })
            .done(function (res) {
                var alerts = (res && res.alerts) || [];
                render(alerts);
                setBadges(res.unread || 0);
                if (lastId !== null) {
                    announce(alerts.filter(function (a) { return a.id > lastId && !a.read; }));
                } else {
                    speakUnreadOnOpen(alerts, res.unread || 0);
                }
                lastId = res.latest_id || 0;
            })
            .always(function () { busy = false; });
    }

    function tick() {
        var $b = box();
        if (!$b.length) { stop(); return; }
        if (document.hidden) return;
        load();
    }

    function start() {
        var $b = box();
        if (!$b.length) return;
        $('#ai-alerts-voice').prop('checked', voiceOn());
        lastId = null;
        load();
        stop();
        timer = setInterval(tick, POLL_MS);
    }

    function stop() {
        if (timer) { clearInterval(timer); timer = null; }
    }

    $(document)
        .off('change.aiAlerts', '#ai-alerts-voice')
        .on('change.aiAlerts', '#ai-alerts-voice', function () {
            try { localStorage.setItem(VOICE_KEY, this.checked ? '1' : '0'); } catch (e) {}
        })
        .off('click.aiAlerts', '#ai-alerts-read')
        .on('click.aiAlerts', '#ai-alerts-read', function () {
            var $b = box();
            $.post($b.data('read-url'), { _token: $('meta[name="csrf-token"]').attr('content') })
                .done(function () {
                    $('#ai-alerts-list .ai-alert').removeClass('is-unread');
                    setBadges(0);
                });
        });

    window.japsAiAlerts = { start: start, stop: stop, refresh: load };

    $(function () { if (box().length) start(); });
})();
</script>
