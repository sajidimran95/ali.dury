@extends('layouts.app')
@section('title', __('crm::lang.dashboard'))
@section('content')
@php
    $pipelineMax = 0;
    $pipelineTotal = 0;
    foreach ($pipeline_summary as $pRow) {
        $cnt = (int) ($pRow['count'] ?? 0);
        $pipelineMax = max($pipelineMax, $cnt);
        $pipelineTotal += $cnt;
    }
    $followHealth = $open_follow_ups > 0
        ? max(0, 100 - min(100, (int) round(($due_follow_ups / max(1, $open_follow_ups)) * 100)))
        : 100;
@endphp
<section class="content inv-dash">
    {{-- Hero pulse (same layout language as inventory dashboard) --}}
    <div class="inv-pulse">
        <div class="inv-pulse__glow" aria-hidden="true"></div>
        <div class="inv-pulse__top">
            <div class="inv-pulse__intro">
                <p class="inv-pulse__eyebrow">@lang('crm::lang.dashboard')</p>
                <h1 class="inv-pulse__title">{{ __('home.welcome_message', ['name' => Session::get('user.first_name')]) }}</h1>
                <p class="inv-pulse__sub">
                    {{ Session::get('business.name') }} · {{ @format_date(\Carbon::now()) }}
                </p>
            </div>
            <div class="inv-pulse__actions">
                <a href="{{ route('crm.pipeline.index') }}" class="inv-btn inv-btn-ghost">
                    @lang('crm::lang.pipeline')
                </a>
                <a href="{{ route('crm.leads.create') }}" class="inv-btn inv-btn-primary">
                    <span aria-hidden="true">+</span> @lang('crm::lang.add_lead')
                </a>
            </div>
        </div>

        <div class="inv-pulse__spotlight">
            <a href="{{ route('crm.leads.index') }}" class="inv-spotlight">
                <div class="inv-spotlight__label">@lang('crm::lang.total_leads')</div>
                <div class="inv-spotlight__value">{{ @num_format($total_leads) }}</div>
                <div class="inv-spotlight__meta">
                    @lang('crm::lang.pipeline'): <strong>{{ @num_format($pipelineTotal) }}</strong>
                    · @lang('crm::lang.view') →
                </div>
            </a>

            <a href="{{ route('crm.schedules.index') }}" class="inv-health">
                <div class="inv-health__head">
                    <span>@lang('crm::lang.due_follow_ups')</span>
                    <strong class="{{ $due_follow_ups > 0 ? 'is-warn' : '' }}">{{ @num_format($due_follow_ups) }}</strong>
                </div>
                <div class="inv-health__track" aria-hidden="true">
                    <span class="inv-health__fill {{ $due_follow_ups > 0 ? 'is-warn' : 'is-ok' }}" style="width: {{ $followHealth }}%"></span>
                </div>
                <div class="inv-health__hint">
                    {{ @num_format($open_follow_ups) }} @lang('crm::lang.open_follow_ups')
                </div>
            </a>
        </div>
    </div>

    {{-- Quick metrics --}}
    <div class="inv-metric-row">
        <a href="{{ action([\App\Http\Controllers\ContactController::class, 'index'], ['type' => 'customer']) }}" class="inv-metric inv-metric--teal">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('crm::lang.total_customers')</div>
                <div class="inv-metric__value">{{ @num_format($total_customers) }}</div>
                <div class="inv-metric__hint">@lang('crm::lang.customers')</div>
            </div>
        </a>

        <a href="{{ route('crm.schedules.index') }}" class="inv-metric inv-metric--amber">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('crm::lang.open_follow_ups')</div>
                <div class="inv-metric__value {{ $due_follow_ups > 0 ? 'is-warn' : '' }}">{{ @num_format($open_follow_ups) }}</div>
                <div class="inv-metric__hint">{{ @num_format($due_follow_ups) }} @lang('crm::lang.due_follow_ups')</div>
            </div>
        </a>

        <a href="{{ route('crm.pipeline.index') }}" class="inv-metric inv-metric--blue">
            <span class="inv-metric__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 16V8"/><path d="M12 16v-5"/><path d="M17 16V5"/></svg>
            </span>
            <div>
                <div class="inv-metric__label">@lang('crm::lang.pipeline')</div>
                <div class="inv-metric__value">{{ @num_format(count($pipeline_summary)) }}</div>
                <div class="inv-metric__hint">{{ @num_format($pipelineTotal) }} @lang('crm::lang.leads')</div>
            </div>
        </a>
    </div>

    {{-- Insight bento --}}
    <div class="inv-bento inv-bento--two">
        <div class="inv-panel inv-panel--alert">
            <div class="inv-panel__header">
                <div class="inv-panel__heading">
                    <span class="inv-panel__chip inv-panel__chip--warn">@lang('crm::lang.leads')</span>
                    <h3 class="inv-panel__title">@lang('crm::lang.recent_leads')</h3>
                </div>
                <span class="inv-badge {{ $total_leads > 0 ? 'inv-badge--warn' : '' }}">{{ @num_format($total_leads) }}</span>
            </div>
            <div class="inv-panel__body">
                @forelse($recent_leads as $lead)
                    <a href="{{ route('crm.leads.show', $lead->id) }}" class="inv-row">
                        <div class="inv-row__rank inv-row__rank--warn" aria-hidden="true">
                            {{ mb_strtoupper(mb_substr($lead->name ?? '?', 0, 1)) }}
                        </div>
                        <div class="inv-row__main">
                            <div class="inv-row__name">{{ $lead->name }}</div>
                            <div class="inv-row__meta">
                                {{ $lead->mobile ?: '—' }}
                                @if(!empty($lead->email)) · {{ $lead->email }} @endif
                            </div>
                        </div>
                        <div class="inv-row__side">
                            <div class="inv-row__value">→</div>
                            <div class="inv-row__meta">@lang('crm::lang.view')</div>
                        </div>
                    </a>
                @empty
                    <div class="inv-empty">—</div>
                @endforelse
            </div>
            <div class="inv-panel__footer">
                <a href="{{ route('crm.leads.index') }}" class="inv-panel__link">
                    @lang('crm::lang.leads')
                    <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <div class="inv-panel inv-panel--cats">
            <div class="inv-panel__header">
                <div class="inv-panel__heading">
                    <span class="inv-panel__chip inv-panel__chip--teal">@lang('crm::lang.pipeline')</span>
                    <h3 class="inv-panel__title">@lang('crm::lang.pipeline_summary')</h3>
                </div>
            </div>
            <div class="inv-panel__body">
                @forelse($pipeline_summary as $row)
                    @php
                        $bar = $pipelineMax > 0 ? min(100, round(((int) $row['count'] / $pipelineMax) * 100)) : 0;
                    @endphp
                    <div class="inv-cat">
                        <div class="inv-cat__top">
                            <div class="inv-cat__name">{{ $row['name'] }}</div>
                            <div class="inv-cat__value">{{ @num_format($row['count']) }}</div>
                        </div>
                        <div class="inv-bar inv-bar--teal" aria-hidden="true"><span style="width: {{ $bar }}%"></span></div>
                        <div class="inv-cat__meta">@lang('crm::lang.leads')</div>
                    </div>
                @empty
                    <div class="inv-empty">@lang('crm::lang.no_stage')</div>
                @endforelse
            </div>
            <div class="inv-panel__footer">
                <a href="{{ route('crm.pipeline.index') }}" class="inv-panel__link">
                    @lang('crm::lang.pipeline')
                    <svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
