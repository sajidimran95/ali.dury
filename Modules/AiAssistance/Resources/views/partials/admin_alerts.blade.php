@php
    $alertBusinessId = (int) session('user.business_id');
    $canViewAlerts = \Modules\AiAssistance\Services\AdminAlertService::tableReady()
        && \Modules\AiAssistance\Services\AdminAlertService::canView(auth()->user(), $alertBusinessId);
@endphp
@if($canViewAlerts)
<div class="ai-alerts" id="ai-admin-alerts"
     data-url="{{ route('ai.alerts.index') }}"
     data-read-url="{{ route('ai.alerts.read') }}">
    <div class="ai-alerts__head">
        <span class="ai-alerts__title"><i class="fa fa-bell"></i> Admin alerts</span>
        <span class="ai-alerts__count" id="ai-alerts-unread" hidden>0</span>
        <div class="ai-alerts__tools">
            <label class="ai-alerts__voice" title="Read new alerts aloud">
                <input type="checkbox" id="ai-alerts-voice"> <i class="fa fa-volume-up"></i> Voice
            </label>
            <button type="button" class="ai-alerts__btn" id="ai-alerts-read">Mark all read</button>
        </div>
    </div>
    <div class="ai-alerts__list" id="ai-alerts-list">
        <p class="ai-alerts__empty">Loading alerts…</p>
    </div>
</div>
@endif
