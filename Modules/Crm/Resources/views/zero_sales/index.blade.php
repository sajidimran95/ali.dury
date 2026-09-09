@extends('layouts.app')
@section('title', __('crm::lang.zero_sales'))
@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold">@lang('crm::lang.zero_sales')</h1>
</section>
<section class="content">
    <div class="box box-solid">
        <div class="box-header with-border">
            <form method="GET" class="form-inline" style="display:flex;flex-wrap:wrap;gap:8px;align-items:center">
                <input type="search" name="q" value="{{ $q }}" class="form-control input-sm" placeholder="@lang('lang_v1.search')">
                <select name="reason_id" class="form-control input-sm" onchange="this.form.submit()">
                    <option value="">@lang('crm::lang.all_reasons')</option>
                    @foreach($reasons as $reason)
                        <option value="{{ $reason->id }}" @selected((string) $reason_id === (string) $reason->id)>{{ $reason->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-primary">@lang('lang_v1.search')</button>
            </form>
        </div>
        <div class="box-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>@lang('messages.date')</th>
                        <th>@lang('crm::lang.customer')</th>
                        <th>@lang('crm::lang.reason')</th>
                        <th>@lang('crm::lang.comment')</th>
                        <th>@lang('lang_v1.added_by')</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ optional($row->created_at)->format('m/d/Y g:i A') }}</td>
                        <td>
                            {{ optional($row->contact)->supplier_business_name ?: optional($row->contact)->name }}
                            @if(optional($row->contact)->contact_id)
                                <div class="text-muted">#{{ $row->contact->contact_id }}</div>
                            @endif
                        </td>
                        <td>{{ optional($row->reason)->name }}</td>
                        <td>{{ $row->comment }}</td>
                        <td>{{ trim((optional($row->createdBy)->first_name ?? '').' '.(optional($row->createdBy)->last_name ?? '')) ?: optional($row->createdBy)->username }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">@lang('crm::lang.no_zero_sales')</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
            {{ $rows->links() }}
        </div>
    </div>
</section>
@endsection
