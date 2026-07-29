@extends('layouts.app')
@section('title', __('crm::lang.dashboard'))
@section('content')
@php
    $pipelineMax = 0;
    foreach ($pipeline_summary as $pRow) {
        $pipelineMax = max($pipelineMax, (int) ($pRow['count'] ?? 0));
    }
    $followHealth = $open_follow_ups > 0
        ? max(0, 100 - min(100, (int) round(($due_follow_ups / max(1, $open_follow_ups)) * 100)))
        : 100;
@endphp
<section class="content crm-dash">
    <div class="crm-hero">
        <div class="crm-hero__left">
            <p class="crm-hero__eyebrow">@lang('crm::lang.dashboard')</p>
            <h1 class="crm-hero__title">{{ __('home.welcome_message', ['name' => Session::get('user.first_name')]) }}</h1>
            <p class="crm-hero__sub">
                {{ Session::get('business.name') }} · {{ @format_date(\Carbon::now()) }}
            </p>
        </div>
        <div class="crm-hero__actions">
            <a href="{{ route('crm.pipeline.index') }}" class="crm-btn crm-btn-ghost">@lang('crm::lang.pipeline')</a>
            <a href="{{ route('crm.leads.create') }}" class="crm-btn crm-btn-primary">
                <span aria-hidden="true">+</span> @lang('crm::lang.add_lead')
            </a>
        </div>
    </div>

    <div class="crm-spotlight">
        <a href="{{ route('crm.leads.index') }}" class="crm-spot crm-spot--leads">
            <div class="crm-spot__label">@lang('crm::lang.total_leads')</div>
            <div class="crm-spot__value">{{ @num_format($total_leads) }}</div>
            <div class="crm-spot__meta">@lang('crm::lang.view') →</div>
        </a>
        <a href="{{ route('crm.schedules.index') }}" class="crm-spot crm-spot--due">
            <div class="crm-spot__head">
                <span class="crm-spot__label">@lang('crm::lang.due_follow_ups')</span>
                <strong class="crm-spot__value {{ $due_follow_ups > 0 ? 'is-warn' : '' }}">{{ @num_format($due_follow_ups) }}</strong>
            </div>
            <div class="crm-spot__track" aria-hidden="true">
                <span class="crm-spot__fill {{ $due_follow_ups > 0 ? 'is-warn' : 'is-ok' }}" style="width: {{ $followHealth }}%"></span>
            </div>
            <div class="crm-spot__meta">
                {{ @num_format($open_follow_ups) }} @lang('crm::lang.open_follow_ups')
            </div>
        </a>
    </div>

    <div class="crm-metric-row">
        <a href="{{ action([\App\Http\Controllers\ContactController::class, 'index'], ['type' => 'customer']) }}" class="crm-metric crm-metric--teal">
            <span class="crm-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </span>
            <div>
                <div class="crm-metric__label">@lang('crm::lang.total_customers')</div>
                <div class="crm-metric__value">{{ @num_format($total_customers) }}</div>
            </div>
        </a>
        <a href="{{ route('crm.schedules.index') }}" class="crm-metric crm-metric--violet">
            <span class="crm-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </span>
            <div>
                <div class="crm-metric__label">@lang('crm::lang.open_follow_ups')</div>
                <div class="crm-metric__value">{{ @num_format($open_follow_ups) }}</div>
            </div>
        </a>
        <a href="{{ route('crm.pipeline.index') }}" class="crm-metric crm-metric--blue">
            <span class="crm-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 16V8"/><path d="M12 16v-5"/><path d="M17 16V5"/></svg>
            </span>
            <div>
                <div class="crm-metric__label">@lang('crm::lang.pipeline')</div>
                <div class="crm-metric__value">{{ count($pipeline_summary) }}</div>
            </div>
        </a>
    </div>

    <div class="crm-bento">
        <div class="crm-panel crm-panel--leads">
            <div class="crm-panel__header">
                <div class="crm-panel__heading">
                    <span class="crm-panel__chip crm-panel__chip--blue">@lang('crm::lang.recent_leads')</span>
                    <h3 class="crm-panel__title">@lang('crm::lang.recent_leads')</h3>
                </div>
                <a href="{{ route('crm.leads.index') }}" class="crm-link">@lang('crm::lang.view') →</a>
            </div>
            <div class="crm-panel__body">
                @forelse($recent_leads as $lead)
                    <a href="{{ route('crm.leads.show', $lead->id) }}" class="crm-lead">
                        <div class="crm-lead__avatar" aria-hidden="true">
                            {{ mb_strtoupper(mb_substr($lead->name ?? '?', 0, 1)) }}
                        </div>
                        <div class="crm-lead__main">
                            <div class="crm-lead__name">{{ $lead->name }}</div>
                            <div class="crm-lead__meta">
                                {{ $lead->mobile ?: '—' }}
                                @if(!empty($lead->email)) · {{ $lead->email }} @endif
                            </div>
                        </div>
                        <span class="crm-lead__go" aria-hidden="true">→</span>
                    </a>
                @empty
                    <div class="crm-empty">—</div>
                @endforelse
            </div>
        </div>

        <div class="crm-panel crm-panel--pipe">
            <div class="crm-panel__header">
                <div class="crm-panel__heading">
                    <span class="crm-panel__chip crm-panel__chip--teal">@lang('crm::lang.pipeline')</span>
                    <h3 class="crm-panel__title">@lang('crm::lang.pipeline_summary')</h3>
                </div>
                <a href="{{ route('crm.pipeline.index') }}" class="crm-link">@lang('crm::lang.pipeline') →</a>
            </div>
            <div class="crm-panel__body">
                @forelse($pipeline_summary as $row)
                    @php
                        $bar = $pipelineMax > 0 ? min(100, round(((int) $row['count'] / $pipelineMax) * 100)) : 0;
                    @endphp
                    <div class="crm-pipe">
                        <div class="crm-pipe__top">
                            <span class="crm-pipe__name">{{ $row['name'] }}</span>
                            <span class="crm-pipe__count">{{ $row['count'] }}</span>
                        </div>
                        <div class="crm-bar" aria-hidden="true"><span style="width: {{ $bar }}%"></span></div>
                    </div>
                @empty
                    <div class="crm-empty">@lang('crm::lang.no_stage')</div>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection
