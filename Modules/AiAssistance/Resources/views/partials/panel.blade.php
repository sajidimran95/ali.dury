@php
    $is_modal = !empty($is_modal);
    $intel_html = $intel_html ?? '';
    $panelBid = (int) session('user.business_id');
    $alertUnread = \Modules\AiAssistance\Services\AdminAlertService::canView(auth()->user(), $panelBid)
        ? app(\Modules\AiAssistance\Services\AdminAlertService::class)->unreadCount($panelBid)
        : 0;
@endphp
@include('aiassistance::partials.styles')

<div class="ai-shell">
    <div class="ai-panel">
        <div class="ai-panel__head">
            <div class="ai-panel__icon" title="JapsAI">@include('aiassistance::partials.japsai_icon', ['size' => 20])</div>
            <div style="min-width:0;">
                <h2 class="ai-panel__title">@lang('aiassistance::lang.ai_assistant')</h2>
                <p class="ai-panel__sub">@lang('aiassistance::lang.subtitle')</p>
            </div>
            <div class="ai-panel__actions">
                @if(auth()->user()->can('ai.settings') || auth()->user()->can('ai.access'))
                    <a href="{{ route('ai.settings') }}" class="ai-panel__link" title="@lang('aiassistance::lang.settings')"><i class="fa fa-cog"></i></a>
                @endif
                @if($is_modal)
                    <button type="button" class="ai-panel__close" id="ai-modal-close" aria-label="Close"><i class="fa fa-times"></i></button>
                @endif
            </div>
        </div>

        <div class="ai-tabs">
            <button type="button" class="ai-tab-link {{ $tab === 'insights' ? 'active' : '' }}" data-tab="insights">
                <i class="fa fa-magic"></i> @lang('aiassistance::lang.insights')
                <span class="ai-alert-badge" title="Unread admin alerts" @if(!$alertUnread) hidden @endif>{{ $alertUnread > 99 ? '99+' : $alertUnread }}</span>
            </button>
            <button type="button" class="ai-tab-link {{ $tab === 'chat' ? 'active' : '' }}" data-tab="chat">
                <i class="fa fa-comments"></i> @lang('aiassistance::lang.chat')
            </button>
            @if(\Illuminate\Support\Facades\Route::has('ai.intelligence.index'))
            <button type="button" class="ai-tab-link {{ $tab === 'intel' ? 'active' : '' }}" data-tab="intel">
                <i class="fa fa-line-chart"></i> Intelligence
            </button>
            @endif
        </div>

        <div class="ai-panel__body">
            <div class="ai-tab-pane" id="ai-pane-insights" style="{{ $tab === 'insights' ? '' : 'display:none;' }}">
                @include('aiassistance::partials.insights')
            </div>
            <div class="ai-tab-pane" id="ai-pane-chat" style="{{ $tab === 'chat' ? '' : 'display:none;' }}">
                @include('aiassistance::partials.chat')
            </div>
            <div class="ai-tab-pane" id="ai-pane-intel" style="{{ $tab === 'intel' ? '' : 'display:none;' }}">
                @include('aiassistance::partials.intel_menu')
            </div>
        </div>
    </div>
</div>
