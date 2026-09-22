@extends('layouts.app')
@section('title', __('aiassistance::lang.ai_assistant'))
@section('content')
<section class="content" style="padding-top:20px;">
    <div style="max-width:960px;margin:0 auto;">
        @include('aiassistance::partials.panel', ['is_modal' => false])
    </div>
</section>
@endsection

@section('javascript')
@include('aiassistance::partials.chat_js')
<script>
$(function () {
    $('.ai-tab-link').on('click', function (e) {
        e.preventDefault();
        var tab = $(this).data('tab');
        if (!tab) return;
        $('.ai-tab-link').removeClass('active');
        $(this).addClass('active');
        $('.ai-tab-pane').hide();
        $('#ai-pane-' + tab).show();
        if (window.history && history.replaceState) {
            history.replaceState(null, '', '{{ url('ai') }}?tab=' + tab);
        }
        if (tab === 'chat' && window.initAiChat) window.initAiChat();
    });
    $(document).on('click', '.japs-intel-chip', function (e) {
        e.preventDefault();
        var url = $(this).attr('data-intel-url');
        var $box = $('#ai-intel-report');
        if (!url || !$box.length) return;
        $('.japs-intel-chip').removeClass('active');
        $(this).addClass('active');
        $box.html('<p class="text-muted">Loading report…</p>');
        $.ajax({ url: url, dataType: 'html', timeout: 90000, cache: false })
            .done(function (html) { $box.html(html || '<p class="text-muted">No rows.</p>'); })
            .fail(function (xhr) {
                $box.html('<p class="text-danger">Could not load this report' + (xhr && xhr.status ? ' (' + xhr.status + ')' : '') + '.</p>');
            });
    });
});
</script>
@endsection
