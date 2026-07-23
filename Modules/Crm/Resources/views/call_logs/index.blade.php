@extends('layouts.app')
@section('title', __('crm::lang.call_logs'))
@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold">@lang('crm::lang.call_logs')</h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-4">
            <div class="box box-solid">
                <div class="box-header with-border"><h3 class="box-title">@lang('crm::lang.add_call_log')</h3></div>
                <div class="box-body">
                    <form method="POST" action="{{ route('crm.call-logs.store') }}">
                        @csrf
                        <div class="form-group">
                            <label>@lang('crm::lang.contact')</label>
                            <select name="contact_id" class="form-control select2" required style="width:100%">
                                <option value="">@lang('messages.please_select')</option>
                                @foreach($contacts as $id => $name)
                                    <option value="{{ $id }}" @selected($contact_id == $id)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>@lang('crm::lang.subject')</label>
                            <input type="text" name="subject" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>@lang('crm::lang.duration')</label>
                            <input type="number" name="duration" class="form-control" min="0">
                        </div>
                        <div class="form-group">
                            <label>@lang('crm::lang.called_on')</label>
                            <input type="text" name="called_on" class="form-control" id="crm_called_on">
                        </div>
                        <div class="form-group">
                            <label>@lang('crm::lang.description')</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">@lang('crm::lang.save')</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <form method="get" class="form-inline">
                        <select name="contact_id" class="form-control input-sm" onchange="this.form.submit()">
                            <option value="">@lang('crm::lang.all')</option>
                            @foreach($contacts as $id => $name)
                                <option value="{{ $id }}" @selected($contact_id == $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>@lang('crm::lang.subject')</th>
                                <th>@lang('crm::lang.contact')</th>
                                <th>@lang('crm::lang.called_on')</th>
                                <th>@lang('crm::lang.duration')</th>
                                <th>@lang('crm::lang.actions')</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($call_logs as $log)
                            <tr>
                                <td>{{ $log->subject }}</td>
                                <td>{{ optional($log->contact)->name }}</td>
                                <td>{{ $log->called_on }}</td>
                                <td>{{ $log->duration }}</td>
                                <td>
                                    <form action="{{ route('crm.call-logs.destroy', $log->id) }}" method="POST" onsubmit="return confirm('Delete?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-xs btn-danger">@lang('crm::lang.delete')</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">—</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                    {{ $call_logs->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@section('javascript')
<script>
$(function () {
    if ($.fn.datetimepicker) {
        $('#crm_called_on').datetimepicker({ format: moment_date_format + ' ' + moment_time_format });
    }
    if ($.fn.select2) {
        $('.select2').select2();
    }
});
</script>
@endsection
