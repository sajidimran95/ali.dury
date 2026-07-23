@php
    $is_modal = !empty($is_modal);
@endphp
@include('aiassistance::partials.styles')

<div class="ai-shell">
    <div class="ai-panel">
        <div class="ai-panel__head">
            <div class="ai-panel__icon"><i class="fa fa-magic"></i></div>
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
            <a href="{{ $is_modal ? '#' : route('ai.dashboard', ['tab' => 'insights']) }}"
               class="ai-tab-link {{ $tab === 'insights' ? 'active' : '' }}"
               data-tab="insights">
                <i class="fa fa-magic"></i> @lang('aiassistance::lang.insights')
            </a>
            <a href="{{ $is_modal ? '#' : route('ai.dashboard', ['tab' => 'chat']) }}"
               class="ai-tab-link {{ $tab === 'chat' ? 'active' : '' }}"
               data-tab="chat">
                <i class="fa fa-comments"></i> @lang('aiassistance::lang.chat')
            </a>
        </div>

        <div class="ai-panel__body">
            <div class="ai-tab-pane" id="ai-pane-insights" style="{{ $tab === 'insights' ? '' : 'display:none;' }}">
                @include('aiassistance::partials.insights')
            </div>
            <div class="ai-tab-pane" id="ai-pane-chat" style="{{ $tab === 'chat' ? '' : 'display:none;' }}">
                @include('aiassistance::partials.chat')
            </div>
        </div>
    </div>
</div>
