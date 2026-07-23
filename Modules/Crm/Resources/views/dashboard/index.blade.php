@extends('layouts.app')
@section('title', __('crm::lang.dashboard'))
@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold">@lang('crm::lang.dashboard')</h1>
</section>
<section class="content">
    <div class="row">
        @foreach([
            ['total_leads', $total_leads, 'crm.leads.index'],
            ['total_customers', $total_customers, null],
            ['due_follow_ups', $due_follow_ups, 'crm.schedules.index'],
            ['open_follow_ups', $open_follow_ups, 'crm.schedules.index'],
        ] as [$label, $value, $route])
            <div class="col-md-3 col-sm-6">
                <div class="info-box" style="min-height:90px;">
                    <div class="info-box-content" style="margin-left:0;padding:15px;">
                        <span class="info-box-text">@lang('crm::lang.'.$label)</span>
                        <span class="info-box-number">
                            @if($route)
                                <a href="{{ route($route) }}">{{ $value }}</a>
                            @else
                                <a href="{{ action([\App\Http\Controllers\ContactController::class, 'index'], ['type' => 'customer']) }}">{{ $value }}</a>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        @endforeach
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
