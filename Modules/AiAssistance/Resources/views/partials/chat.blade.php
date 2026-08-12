@if(!$has_api_key)
    <div class="alert alert-warning" style="border-radius:12px;">
        @lang('aiassistance::lang.no_api_key')
        <a href="{{ route('ai.settings') }}">@lang('aiassistance::lang.settings')</a>
    </div>
@endif

<div class="ai-chat">
    <div class="ai-chat__msgs" id="ai-chat-messages">
        <div class="ai-msg bot">
            <div class="ai-avatar" title="JapsAI">@include('aiassistance::partials.japsai_icon', ['size' => 15])</div>
            <div class="ai-bubble">@lang('aiassistance::lang.chat_greeting')</div>
        </div>

        @foreach($chat_history as $msg)
            @if(($msg['role'] ?? '') === 'user')
                <div class="ai-msg user">
                    <div class="ai-avatar"><i class="fa fa-user"></i></div>
                    <div class="ai-bubble">{{ $msg['content'] }}</div>
                </div>
            @elseif(($msg['role'] ?? '') === 'assistant')
                @if(!empty($msg['tool']))
                    <div class="ai-tool"><i class="fa fa-check"></i> {{ $msg['tool'] }}</div>
                @endif
                <div class="ai-msg bot">
                    <div class="ai-avatar" title="JapsAI">@include('aiassistance::partials.japsai_icon', ['size' => 15])</div>
                    <div class="ai-bubble">{!! nl2br(e($msg['content'])) !!}</div>
                </div>
            @endif
        @endforeach
    </div>

    <div class="ai-chips" id="ai-chips">
        <button type="button" class="ai-chip" data-q="@lang('aiassistance::lang.chip_today_sales')">@lang('aiassistance::lang.chip_today_sales')</button>
        <button type="button" class="ai-chip" data-q="@lang('aiassistance::lang.chip_overview')">@lang('aiassistance::lang.chip_overview')</button>
        <button type="button" class="ai-chip" data-q="@lang('aiassistance::lang.chip_top_products')">@lang('aiassistance::lang.chip_top_products')</button>
        <button type="button" class="ai-chip" data-q="@lang('aiassistance::lang.chip_purchases')">@lang('aiassistance::lang.chip_purchases')</button>
        <button type="button" class="ai-chip" data-q="@lang('aiassistance::lang.chip_payments')">@lang('aiassistance::lang.chip_payments')</button>
        <button type="button" class="ai-chip" data-q="@lang('aiassistance::lang.chip_low_stock')">@lang('aiassistance::lang.chip_low_stock')</button>
        <button type="button" class="ai-chip" data-q="@lang('aiassistance::lang.chip_unpaid')">@lang('aiassistance::lang.chip_unpaid')</button>
        <button type="button" class="ai-chip" data-q="@lang('aiassistance::lang.chip_pipeline')">@lang('aiassistance::lang.chip_pipeline')</button>
    </div>

    <form id="ai-chat-form" class="ai-chat__footer">
        @csrf
        @can('purchase.create')
            <label class="ai-attach-btn" title="@lang('aiassistance::lang.invoice_attach_chat')" style="cursor:pointer;display:inline-flex;align-items:center;padding:0 6px;">
                <input type="file" id="ai-invoice-attach" accept="image/jpeg,image/jpg,image/png,image/webp,application/pdf,.pdf" style="display:none;">
                <i class="fa fa-paperclip"></i>
            </label>
        @endcan
        <input type="text" id="ai-chat-input" placeholder="@lang('aiassistance::lang.chat_placeholder')" autocomplete="off">
        <button type="submit" class="ai-send" title="@lang('aiassistance::lang.send')"><i class="fa fa-paper-plane"></i></button>
        <a href="{{ route('ai.dashboard') }}" class="ai-panel__link" title="@lang('aiassistance::lang.open_full')"><i class="fa fa-external-link"></i></a>
    </form>
</div>
