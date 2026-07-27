@extends('layouts.app')
@section('title', __('crm::lang.dashboard'))
@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold">@lang('crm::lang.dashboard')</h1>
</section>
<section class="content crm-dash">
<style>
.crm-dash .crm-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
@media (max-width: 1199px) {
    .crm-dash .crm-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 575px) {
    .crm-dash .crm-kpi-grid { grid-template-columns: 1fr; }
}
.crm-dash .crm-kpi {
    --kpi-accent: #4f8fce;
    --kpi-soft: #eef6fd;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;
    min-height: 112px;
    background: #fff;
    border: 1px solid #e7eaf0;
    border-radius: 18px;
    padding: 22px 20px 18px;
    box-shadow: 0 2px 10px rgba(15, 20, 37, 0.045);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    text-decoration: none !important;
    color: inherit;
}
.crm-dash .crm-kpi::before {
    content: "";
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    border-radius: 18px 18px 0 0;
    background: var(--kpi-accent);
}
.crm-dash .crm-kpi:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(15, 20, 37, 0.07);
    color: inherit;
}
.crm-dash .crm-kpi--blue { --kpi-accent: #4f8fce; --kpi-soft: #eef6fd; }
.crm-dash .crm-kpi--teal { --kpi-accent: #4db39e; --kpi-soft: #ecf8f4; }
.crm-dash .crm-kpi--amber { --kpi-accent: #d5aa35; --kpi-soft: #fff8e6; }
.crm-dash .crm-kpi--violet { --kpi-accent: #7c72c9; --kpi-soft: #f5f0fc; }
.crm-dash .crm-kpi__body { flex: 1; min-width: 0; }
.crm-dash .crm-kpi__label {
    margin: 0 0 10px;
    font-size: 14px;
    font-weight: 500;
    color: #7a8495;
}
.crm-dash .crm-kpi__value {
    margin: 0;
    font-size: 26px;
    font-weight: 700;
    color: #151b2b;
    line-height: 1.15;
    letter-spacing: -0.03em;
}
.crm-dash .crm-kpi__value.is-warn { color: #b78616; }
.crm-dash .crm-kpi__icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: var(--kpi-soft);
    color: var(--kpi-accent);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.crm-dash .crm-kpi__icon svg { width: 22px; height: 22px; display: block; }
</style>

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

    <div class="row">
        <div class="col-md-7">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('crm::lang.recent_leads')</h3>
                    <div class="box-tools">
                        <a href="{{ route('crm.leads.create') }}" class="btn btn-sm btn-primary">@lang('crm::lang.add_lead')</a>
                    </div>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-bordered">
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
                            <tr><td colspan="4" class="text-center text-muted">—</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('crm::lang.pipeline_summary')</h3>
                    <div class="box-tools">
                        <a href="{{ route('crm.pipeline.index') }}" class="btn btn-sm btn-default">@lang('crm::lang.pipeline')</a>
                    </div>
                </div>
                <div class="box-body">
                    <ul class="list-group">
                        @forelse($pipeline_summary as $row)
                            <li class="list-group-item">
                                {{ $row['name'] }}
                                <span class="badge">{{ $row['count'] }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">@lang('crm::lang.no_stage')</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
