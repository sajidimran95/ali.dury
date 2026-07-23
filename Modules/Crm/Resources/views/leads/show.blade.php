@extends('layouts.app')
@section('title', $lead->name)
@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold">
        {{ $lead->name }}
        <small class="tw-text-base tw-text-gray-600">@lang('crm::lang.lead')</small>
    </h1>
</section>
<section class="content">
    <div class="box box-solid">
        <div class="box-header with-border">
            <div class="btn-group">
                @if(auth()->user()->can('crm.lead.update') || auth()->user()->can('crm.access') || auth()->user()->can('superadmin'))
                    <a href="{{ route('crm.leads.edit', $lead->id) }}" class="btn btn-primary btn-sm">@lang('crm::lang.edit')</a>
                    <form action="{{ route('crm.leads.convert', $lead->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('@lang('crm::lang.convert_to_customer')?');">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">@lang('crm::lang.convert_to_customer')</button>
                    </form>
                @endif
                <a href="{{ route('crm.leads.index') }}" class="btn btn-default btn-sm">@lang('crm::lang.leads')</a>
            </div>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr><th>@lang('crm::lang.name')</th><td>{{ $lead->name }}</td></tr>
                        <tr><th>@lang('business.business_name')</th><td>{{ $lead->supplier_business_name }}</td></tr>
                        <tr><th>@lang('crm::lang.mobile')</th><td>{{ $lead->mobile }}</td></tr>
                        <tr><th>@lang('crm::lang.email')</th><td>{{ $lead->email }}</td></tr>
                        <tr><th>@lang('crm::lang.source')</th><td>{{ $sources[$lead->crm_source] ?? $lead->crm_source }}</td></tr>
                        <tr><th>@lang('crm::lang.life_stage')</th><td>{{ $life_stages[$lead->crm_life_stage] ?? $lead->crm_life_stage }}</td></tr>
                        <tr>
                            <th>@lang('crm::lang.assigned_to')</th>
                            <td>{{ $lead->leadUsers->map(fn($u) => $u->user_full_name)->implode(', ') ?: __('crm::lang.unassigned') }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr><th>@lang('business.address')</th><td>{{ $lead->address_line_1 }} {{ $lead->city }} {{ $lead->state }}</td></tr>
                        <tr><th>@lang('lang_v1.contact_id')</th><td>{{ $lead->contact_id }}</td></tr>
                    </table>
                    <div class="btn-group" style="margin-top:8px;">
                        @if(auth()->user()->can('crm.schedule') || auth()->user()->can('crm.access'))
                            <a href="{{ route('crm.schedules.index', ['contact_id' => $lead->id]) }}" class="btn btn-default btn-sm">
                                <i class="fa fa-calendar-check"></i> @lang('crm::lang.add_follow_up')
                            </a>
                        @endif
                        @if(auth()->user()->can('crm.call_log') || auth()->user()->can('crm.access'))
                            <a href="{{ route('crm.call-logs.index', ['contact_id' => $lead->id]) }}" class="btn btn-default btn-sm">
                                <i class="fa fa-phone"></i> @lang('crm::lang.add_call_log')
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fas fa-paperclip"></i> @lang('lang_v1.documents_and_notes')
                    </h3>
                </div>
                <div class="box-body">
                    <input type="hidden" name="notable_id" id="notable_id" value="{{ $lead->id }}">
                    <input type="hidden" name="notable_type" id="notable_type" value="App\Contact">
                    <div class="document_note_body"></div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fas fa-stream"></i> @lang('crm::lang.activity_timeline')
                    </h3>
                </div>
                <div class="box-body" style="max-height:560px;overflow-y:auto;">
                    @forelse($timeline as $item)
                        @php
                            $icon = match($item['type']) {
                                'note' => 'fa-sticky-note text-info',
                                'call' => 'fa-phone text-success',
                                'follow_up' => 'fa-calendar-check text-warning',
                                default => 'fa-user-plus text-primary',
                            };
                            $badge = match($item['type']) {
                                'note' => __('crm::lang.note'),
                                'call' => __('crm::lang.call_log'),
                                'follow_up' => __('crm::lang.follow_up'),
                                default => __('crm::lang.lead'),
                            };
                        @endphp
                        <div style="display:flex;gap:12px;padding:12px 0;border-bottom:1px solid #eef2f7;">
                            <div style="width:28px;text-align:center;padding-top:2px;">
                                <i class="fa {{ $icon }}"></i>
                            </div>
                            <div style="flex:1;">
                                <div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap;">
                                    <strong>{{ $item['title'] }}</strong>
                                    <span class="label label-default">{{ $badge }}</span>
                                </div>
                                @if(!empty($item['body']))
                                    <div class="text-muted" style="margin-top:4px;">{!! nl2br(e(strip_tags($item['body']))) !!}</div>
                                @endif
                                <div style="font-size:12px;color:#6b7280;margin-top:6px;">
                                    @if(!empty($item['at']))
                                        {{ @format_datetime($item['at']) }}
                                    @endif
                                    @if(!empty($item['by']))
                                        · {{ $item['by'] }}
                                    @endif
                                    @if(!empty($item['meta']))
                                        · {{ $item['meta'] }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center">@lang('crm::lang.no_activity')</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('javascript')
@include('documents_and_notes.document_and_note_js')
@endsection
