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
            @include('aiassistance::partials.japsai_icon', ['size' => 18])
        </span>
        <span class="ai-ask-fab__text">
            <span class="ai-ask-fab__title">@lang('aiassistance::lang.boost_with_ai')</span>
            <span class="ai-ask-fab__desc">@lang('aiassistance::lang.boost_with_ai_desc')</span>
        </span>
    </button>
    <div class="ai-modal-backdrop" id="ai-modal-backdrop"></div>
    <div class="ai-modal" id="ai-modal">
        <div id="ai-modal-content"></div>
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

        var panelXhr = null;
        var intelXhr = null;

        function loadIntelReport(url, btn) {
            var $box = $('#ai-modal #ai-intel-report');
            if (!$box.length) {
                $box = $('#ai-intel-report');
            }
            if (!$box.length) {
                return;
            }
            if (!url) {
                $box.html('<p class="text-danger">Report URL missing.</p>');
                return;
            }
            if (btn) {
                $('#ai-intel-chips .japs-intel-chip').removeClass('active');
                $(btn).addClass('active');
            }
            $box.html('<p class="text-muted" style="padding:12px 0;">Loading report…</p>');
            if (intelXhr && typeof intelXhr.abort === 'function') {
                try { intelXhr.abort(); } catch (e) {}
            }
            intelXhr = $.ajax({
                url: url,
                method: 'GET',
                dataType: 'html',
                timeout: 90000,
                cache: false
            })
                .done(function (html) {
                    $box.html(html || '<p class="text-muted">No rows for this report.</p>');
                    var pane = document.querySelector('#ai-modal .ai-panel__body');
                    if (pane) pane.scrollTop = 0;
                    if ($box[0]) $box[0].scrollTop = 0;
                })
                .fail(function (xhr, status) {
                    if (status === 'abort') return;
                    $box.html('<p class="text-danger">Could not load this report' + (xhr && xhr.status ? ' (' + xhr.status + ')' : '') + '.</p>');
                })
                .always(function () {
                    intelXhr = null;
                });
        }
        window.japsLoadIntelReport = loadIntelReport;

        function openAiModal(tab, section) {
            var $modal = $('#ai-modal');
            var $backdrop = $('#ai-modal-backdrop');
            var $content = $('#ai-modal-content');

            // Drop any stale overlay state
            $modal.addClass('open').css({ display: 'flex', 'pointer-events': 'auto' });
            $backdrop.addClass('open').css({ display: 'block' });
            $('body').addClass('ai-modal-open');
            $('html').addClass('ai-modal-open');

            $content.html(
                '<div style="padding:40px;text-align:center;color:#6b7280;">' +
                    '<div class="japsai-icon" style="margin:0 auto 10px;width:28px;height:28px;color:#7c3aed;">…</div>' +
                    'Loading JapsAI…</div>'
            );

            if (panelXhr && typeof panelXhr.abort === 'function') {
                try { panelXhr.abort(); } catch (e) {}
            }

            panelXhr = $.ajax({
                url: '{{ route('ai.panel') }}',
                data: { tab: tab || 'chat' },
                timeout: 25000
            })
                .done(function (html) {
                    $content.html(html);
                    bindAiModalTabs();
                    if (window.initAiChat) {
                        window.initAiChat();
                    }
                })
                .fail(function (xhr, status) {
                    if (status === 'abort') return;
                    $content.html(
                        '<div style="padding:24px;text-align:center;">' +
                            '<p class="text-danger" style="margin-bottom:12px;">Unable to load JapsAI. Please try again.</p>' +
                            '<button type="button" class="btn btn-primary btn-sm" id="ai-retry-load">Retry</button> ' +
                            '<button type="button" class="btn btn-default btn-sm" id="ai-fail-close">Close</button>' +
                        '</div>'
                    );
                    $('#ai-retry-load').on('click', function () { openAiModal(tab || 'chat'); });
                    $('#ai-fail-close').on('click', closeAiModal);
                })
                .always(function () {
                    panelXhr = null;
                });
        }
        function closeAiModal() {
            if (panelXhr && typeof panelXhr.abort === 'function') {
                try { panelXhr.abort(); } catch (e) {}
                panelXhr = null;
            }
            $('#ai-modal').removeClass('open').css('display', '');
            $('#ai-modal-backdrop').removeClass('open').css('display', '');
            $('body').removeClass('ai-modal-open');
            $('html').removeClass('ai-modal-open');
            if (window.japsAiVoice) {
                window.japsAiVoice.stop();
            }
            // Clear any leftover full-screen blockers that left the UI “dead”
            $('.ai-modal-backdrop').removeClass('open');
        }
        function bindAiModalTabs() {
            $('#ai-modal .ai-tab-link').off('click.aiTab').on('click.aiTab', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var tab = $(this).data('tab');
                if (!tab) return;
                $('#ai-modal .ai-tab-link').removeClass('active');
                $(this).addClass('active');
                $('#ai-modal .ai-tab-pane').hide();
                $('#ai-modal #ai-pane-' + tab).show();
                if (tab === 'chat' && window.initAiChat) {
                    window.initAiChat();
                }
                if (tab !== 'insights' && window.japsAiVoice) {
                    window.japsAiVoice.stop();
                }
            });
            $('#ai-modal-close').off('click.aiClose').on('click.aiClose', closeAiModal);
        }
        // Re-bind once with namespaced handlers so duplicate includes are safe
        $(document)
            .off('click.aiFab', '#ai-ask-fab-btn')
            .on('click.aiFab', '#ai-ask-fab-btn', function (e) {
                e.preventDefault();
                openAiModal('chat');
            })
            .off('click.aiBackdrop', '#ai-modal-backdrop')
            .on('click.aiBackdrop', '#ai-modal-backdrop', closeAiModal)
            .off('keydown.aiEsc')
            .on('keydown.aiEsc', function (e) {
                if (e.key === 'Escape' && $('#ai-modal').hasClass('open')) {
                    closeAiModal();
                }
            })
            // Always on document — survives panel reloads; not blocked by chat .ai-chip handlers
            .off('click.japsIntel', '.japs-intel-chip')
            .on('click.japsIntel', '.japs-intel-chip', function (e) {
                e.preventDefault();
                e.stopPropagation();
                loadIntelReport($(this).attr('data-intel-url'), this);
            });
    })();
    </script>
    @include('aiassistance::partials.voice_js')
    @include('aiassistance::partials.chat_js')
@endif
