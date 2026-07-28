@extends('layouts.app')
@section('title', __('crm::lang.dashboard'))
@section('content')
<section class="content crm-dash">
    <div class="crm-head">
        <div>
            <h1 class="crm-title">{{ __('home.welcome_message', ['name' => Session::get('user.first_name')]) }}</h1>
            <p class="crm-sub">@lang('crm::lang.dashboard') · {{ @format_date(\Carbon::now()) }}</p>
        </div>
        <div class="crm-actions">
            <a href="{{ route('crm.leads.create') }}" class="crm-btn crm-btn-primary">@lang('crm::lang.add_lead')</a>
            <a href="{{ route('crm.pipeline.index') }}" class="crm-btn">@lang('crm::lang.pipeline')</a>
        </div>
    </div>

    <div class="crm-kpi-grid">
        <a href="{{ route('crm.leads.index') }}" class="crm-kpi crm-kpi--blue">
            <div class="crm-kpi__body">
                <div class="crm-kpi__label">@lang('crm::lang.total_leads')</div>
                <div class="crm-kpi__value">{{ $total_leads }}</div>
            </div>
            <span class="crm-kpi__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </span>
        </a>

        <a href="{{ action([\App\Http\Controllers\ContactController::class, 'index'], ['type' => 'customer']) }}" class="crm-kpi crm-kpi--teal">
            <div class="crm-kpi__body">
                <div class="crm-kpi__label">@lang('crm::lang.total_customers')</div>
                <div class="crm-kpi__value">{{ $total_customers }}</div>
            </div>
            <span class="crm-kpi__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </span>
        </a>

        <a href="{{ route('crm.schedules.index') }}" class="crm-kpi crm-kpi--amber">
            <div class="crm-kpi__body">
                <div class="crm-kpi__label">@lang('crm::lang.due_follow_ups')</div>
                <div class="crm-kpi__value {{ $due_follow_ups > 0 ? 'is-warn' : '' }}">{{ $due_follow_ups }}</div>
            </div>
            <span class="crm-kpi__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </span>
        </a>

        <a href="{{ route('crm.schedules.index') }}" class="crm-kpi crm-kpi--violet">
            <div class="crm-kpi__body">
                <div class="crm-kpi__label">@lang('crm::lang.open_follow_ups')</div>
                <div class="crm-kpi__value">{{ $open_follow_ups }}</div>
            </div>
            <span class="crm-kpi__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </span>
        </a>
    </div>

    <div class="crm-grid">
        <div class="crm-card crm-card--wide">
            <div class="crm-card__header">
                <h3 class="crm-card__title">@lang('crm::lang.recent_leads')</h3>
                <a href="{{ route('crm.leads.index') }}" class="crm-link">@lang('crm::lang.view')</a>
            </div>
            <div class="crm-card__body crm-card__body--flush">
                <div class="table-responsive">
                    <table class="table crm-table">
                        <thead>
                            <tr>
                                <th>@lang('crm::lang.name')</th>
                                <th>@lang('crm::lang.mobile')</th>
                                <th>@lang('crm::lang.email')</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($recent_leads as $lead)
                            <tr>
                                <td>{{ $lead->name }}</td>
                                <td>{{ $lead->mobile }}</td>
                                <td>{{ $lead->email }}</td>
                                <td><a href="{{ route('crm.leads.show', $lead->id) }}">@lang('crm::lang.view')</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="crm-empty">—</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="crm-card">
            <div class="crm-card__header">
                <h3 class="crm-card__title">@lang('crm::lang.pipeline_summary')</h3>
                <a href="{{ route('crm.pipeline.index') }}" class="crm-link">@lang('crm::lang.pipeline')</a>
            </div>
            <div class="crm-card__body">
                @forelse($pipeline_summary as $row)
                    <div class="crm-pipeline-row">
                        <span class="crm-pipeline-row__name">{{ $row['name'] }}</span>
                        <span class="crm-pipeline-row__count">{{ $row['count'] }}</span>
                    </div>
                @empty
                    <div class="crm-empty">@lang('crm::lang.no_stage')</div>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection
