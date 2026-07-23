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
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
