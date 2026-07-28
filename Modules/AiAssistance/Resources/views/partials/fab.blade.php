@php
    $show_ai_fab = false;
    try {
        if (auth()->check()
            && app(\App\Utils\ModuleUtil::class)->isModuleInstalled('AiAssistance')
            && (auth()->user()->can('ai.access') || auth()->user()->can('ai.chat'))
        ) {
            $bid = (int) session('user.business_id');
            if ($bid) {
                $ai_settings = \Modules\AiAssistance\Entities\AiAssistanceSetting::forBusiness($bid);
                $show_ai_fab = (bool) $ai_settings->is_enabled;
            }
        }
    } catch (\Throwable $e) {
        $show_ai_fab = false;
    }
@endphp

@if($show_ai_fab && empty($pos_layout))
    <button type="button" class="ai-ask-fab" id="ai-ask-fab-btn" title="@lang('aiassistance::lang.boost_with_ai')">
        <span class="ai-ask-fab__icon" aria-hidden="true">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 3l1.4 4.2L18 8.6l-4.2 1.4L12 14.2l-1.4-4.2L6.4 8.6l4.2-1.4L12 3z" fill="currentColor"/>
                <path d="M18.5 14.5l.7 2.1 2.1.7-2.1.7-.7 2.1-.7-2.1-2.1-.7 2.1-.7.7-2.1z" fill="currentColor" opacity=".85"/>
            </svg>
        </span>
        <span class="ai-ask-fab__text">
            <span class="ai-ask-fab__title">@lang('aiassistance::lang.boost_with_ai')</span>
            <span class="ai-ask-fab__desc">@lang('aiassistance::lang.boost_with_ai_desc')</span>
        </span>
    </button>
    <div class="ai-modal-backdrop" id="ai-modal-backdrop"></div>
    <div class="ai-modal" id="ai-modal">
        <div id="ai-modal-content" style="min-height:200px;"></div>
    </div>
    @include('aiassistance::partials.styles')
    <script>
    (function () {
        function mountAiUi() {
            var fab = document.getElementById('ai-ask-fab-btn');
            var slot = document.getElementById('japs-sidebar-ai-slot');
            if (fab && slot && fab.parentElement !== slot) {
                slot.appendChild(fab);
            }
            ['#ai-modal-backdrop', '#ai-modal'].forEach(function (sel) {
                var el = document.querySelector(sel);
                if (el && el.parentElement !== document.body) {
                    document.body.appendChild(el);
                }
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', mountAiUi);
        } else {
            mountAiUi();
        }
        setTimeout(mountAiUi, 0);

        function openAiModal(tab) {
            var $modal = $('#ai-modal');
            var $backdrop = $('#ai-modal-backdrop');
            var $content = $('#ai-modal-content');
            $content.html('<div style="padding:40px;text-align:center;color:#6b7280;">Loading…</div>');
            $modal.addClass('open');
            $backdrop.addClass('open');
            $.get('{{ route('ai.panel') }}', { tab: tab || 'insights' }, function (html) {
                $content.html(html);
                bindAiModalTabs();
                if (window.initAiChat) window.initAiChat();
            }).fail(function () {
                $content.html('<div style="padding:24px;" class="text-danger">Unable to load AI Assistant.</div>');
            });
        }
        function closeAiModal() {
            $('#ai-modal').removeClass('open');
            $('#ai-modal-backdrop').removeClass('open');
        }
        function bindAiModalTabs() {
            $('#ai-modal .ai-tab-link').off('click').on('click', function (e) {
                e.preventDefault();
                var tab = $(this).data('tab');
                $('#ai-modal .ai-tab-link').removeClass('active');
                $(this).addClass('active');
                $('#ai-modal .ai-tab-pane').hide();
                $('#ai-modal #ai-pane-' + tab).show();
                if (tab === 'chat' && window.initAiChat) window.initAiChat();
            });
            $('#ai-modal-close').off('click').on('click', closeAiModal);
        }
        $(document).on('click', '#ai-ask-fab-btn', function () { openAiModal('insights'); });
        $(document).on('click', '#ai-modal-backdrop', closeAiModal);
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape') closeAiModal();
        });
    })();
    </script>
    @include('aiassistance::partials.chat_js')
@endif
