{{-- Browser text-to-speech for Insights (and reusable for chat replies) --}}
<script>
(function () {
    if (window.japsAiVoice) return;

    var labels = {
        listen: @json(__('aiassistance::lang.insights_listen')),
        stop: @json(__('aiassistance::lang.insights_stop')),
        unsupported: @json(__('aiassistance::lang.insights_voice_unsupported'))
    };

    function canSpeak() {
        return typeof window.speechSynthesis !== 'undefined'
            && typeof window.SpeechSynthesisUtterance !== 'undefined';
    }

    function stop() {
        if (!canSpeak()) return;
        try { window.speechSynthesis.cancel(); } catch (e) {}
        setBtnState(false);
    }

    function setBtnState(speaking) {
        var $btn = $('#ai-insights-speak-btn');
        if (!$btn.length) return;
        $btn.attr('data-speaking', speaking ? '1' : '0');
        $btn.attr('aria-pressed', speaking ? 'true' : 'false');
        $btn.find('.ai-insights-speak-label').text(speaking ? labels.stop : labels.listen);
        $btn.find('i').attr('class', speaking ? 'fa fa-stop' : 'fa fa-volume-up');
    }

    function speak(text, opts) {
        opts = opts || {};
        if (!text || !String(text).trim()) return;
        if (!canSpeak()) {
            if (typeof toastr !== 'undefined') toastr.warning(labels.unsupported);
            else alert(labels.unsupported);
            return;
        }
        stop();
        var u = new SpeechSynthesisUtterance(String(text).trim());
        u.rate = opts.rate || 1;
        u.pitch = opts.pitch || 1;
        u.lang = opts.lang || (document.documentElement.lang || 'en-US');
        u.onend = function () { setBtnState(false); };
        u.onerror = function () { setBtnState(false); };
        setBtnState(true);
        window.speechSynthesis.speak(u);
    }

    function insightsScript() {
        var el = document.getElementById('ai-insights-spoken-json');
        if (el) {
            try { return JSON.parse(el.textContent || '""'); } catch (e) {}
        }
        var hidden = document.getElementById('ai-insights-spoken-script');
        return hidden ? (hidden.textContent || '').trim() : '';
    }

    function toggleInsights() {
        var $btn = $('#ai-insights-speak-btn');
        if ($btn.attr('data-speaking') === '1') {
            stop();
            return;
        }
        speak(insightsScript());
    }

    window.japsAiVoice = {
        speak: speak,
        stop: stop,
        toggleInsights: toggleInsights,
        canSpeak: canSpeak
    };

    $(document)
        .off('click.aiVoice', '#ai-insights-speak-btn, .ai-insights-speak-btn')
        .on('click.aiVoice', '#ai-insights-speak-btn, .ai-insights-speak-btn', function (e) {
            e.preventDefault();
            toggleInsights();
        });
})();
</script>
