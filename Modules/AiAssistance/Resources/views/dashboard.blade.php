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
        $('.ai-tab-link').removeClass('active');
        $(this).addClass('active');
        $('.ai-tab-pane').hide();
        $('#ai-pane-' + tab).show();
        if (window.history && history.replaceState) {
            history.replaceState(null, '', '{{ url('ai') }}?tab=' + tab);
        }
        if (tab === 'chat' && window.initAiChat) window.initAiChat();
    });
});
</script>
@endsection
