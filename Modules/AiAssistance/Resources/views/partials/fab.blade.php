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
    <button type="button" class="ai-ask-fab" id="ai-ask-fab-btn" title="@lang('aiassistance::lang.ask_ai')">
        <i class="fa fa-magic"></i>
        <span>@lang('aiassistance::lang.ask_ai')</span>
    </button>
    <div class="ai-modal-backdrop" id="ai-modal-backdrop"></div>
    <div class="ai-modal" id="ai-modal">
        <div id="ai-modal-content" style="min-height:200px;"></div>
    </div>
    @include('aiassistance::partials.styles')
    <script>
    (function () {
        // Mount on <body> so position:fixed anchors to the viewport bottom
        // (overflow/transform on layout wrappers otherwise pin it mid-page).
        function mountAiToBody() {
            ['#ai-ask-fab-btn', '#ai-modal-backdrop', '#ai-modal'].forEach(function (sel) {
                var el = document.querySelector(sel);
                if (el && el.parentElement !== document.body) {
                    document.body.appendChild(el);
                }
            });
        }

        function scrollRoot() {
            return document.getElementById('scrollable-container')
                || document.scrollingElement
                || document.documentElement;
        }

        function setAiFabVisible(visible) {
            var fab = document.getElementById('ai-ask-fab-btn');
            if (!fab) {
                return;
            }
            fab.classList.toggle('is-visible', !!visible);
        }

        // Show only when user has scrolled to the page bottom / footer area.
        function updateAiFabVisibility() {
            var root = scrollRoot();
            if (!root) {
                setAiFabVisible(false);
                return;
            }

            var scrollTop = root.scrollTop || 0;
            var clientHeight = root.clientHeight || 0;
            var scrollHeight = root.scrollHeight || 0;

            // Must be able to scroll, and must have scrolled down
            var canScroll = scrollHeight > (clientHeight + 60);
            if (!canScroll || scrollTop < 30) {
                setAiFabVisible(false);
                return;
            }

            // Near absolute bottom of the scroll area (footer lives here)
            var distanceFromBottom = scrollHeight - (scrollTop + clientHeight);
            var atBottom = distanceFromBottom <= 120;

            // Also accept when footer element is visible in the scroll viewport
            var footer = document.getElementById('japs-page-footer');
            if (!atBottom && footer && root.getBoundingClientRect) {
                var rootRect = root.getBoundingClientRect();
                var footRect = footer.getBoundingClientRect();
                atBottom = footRect.top < rootRect.bottom && footRect.bottom > rootRect.top;
            }

            setAiFabVisible(atBottom);
        }

        function bindScrollWatch() {
            var root = document.getElementById('scrollable-container');
            setAiFabVisible(false);

            if (root) {
                root.addEventListener('scroll', updateAiFabVisibility, { passive: true });
            }
            window.addEventListener('scroll', updateAiFabVisibility, { passive: true });
            window.addEventListener('resize', updateAiFabVisibility);

            // Re-check after late content (tables, charts) finishes laying out
            updateAiFabVisibility();
            setTimeout(updateAiFabVisibility, 300);
            setTimeout(updateAiFabVisibility, 1000);
        }

        function boot() {
            mountAiToBody();
            bindScrollWatch();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', boot);
        } else {
            boot();
        }

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
