@extends('layouts.app')
@section('title', __('crm::lang.follow_ups'))
@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold">@lang('crm::lang.follow_ups')</h1>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-4">
            <div class="box box-solid">
                <div class="box-header with-border"><h3 class="box-title">@lang('crm::lang.add_follow_up')</h3></div>
                <div class="box-body">
                    <form method="POST" action="{{ route('crm.schedules.store') }}">
                        @csrf
                        <div class="form-group">
                            <label>@lang('crm::lang.contact')</label>
                            <select name="contact_id" class="form-control select2" required style="width:100%">
                                <option value="">@lang('messages.please_select')</option>
                                @foreach($contacts as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>@lang('crm::lang.title')</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('crm::lang.start_datetime')</label>
                            <input type="text" name="start_datetime" class="form-control" id="crm_schedule_datetime" placeholder="YYYY-MM-DD HH:MM">
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
                        <select name="status" class="form-control input-sm" onchange="this.form.submit()">
                            <option value="">@lang('crm::lang.all')</option>
                            <option value="open" @selected($status==='open')>@lang('crm::lang.open')</option>
                            <option value="completed" @selected($status==='completed')>@lang('crm::lang.completed')</option>
                        </select>
                    </form>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>@lang('crm::lang.title')</th>
                                <th>@lang('crm::lang.contact')</th>
                                <th>@lang('crm::lang.start_datetime')</th>
                                <th>@lang('crm::lang.status')</th>
                                <th>@lang('crm::lang.actions')</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($schedules as $s)
                            <tr>
                                <td>{{ $s->title }}</td>
                                <td>{{ optional($s->contact)->name }}</td>
                                <td>{{ $s->start_datetime }}</td>
                                <td>
                                    @if($s->status === 'completed')
                                        <span class="label label-success">@lang('crm::lang.completed')</span>
                                    @else
                                        <span class="label label-warning">@lang('crm::lang.open')</span>
                                    @endif
                                </td>
                                <td>
                                    @if($s->status !== 'completed')
                                        <form action="{{ route('crm.schedules.complete', $s->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button class="btn btn-xs btn-success">@lang('crm::lang.mark_completed')</button>
                                        </form>
                                    @endif
                                    <form action="{{ route('crm.schedules.destroy', $s->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete?');">
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
                    {{ $schedules->appends(request()->query())->links() }}
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
        $('#crm_schedule_datetime').datetimepicker({ format: moment_date_format + ' ' + moment_time_format });
    }
    if ($.fn.select2) {
        $('.select2').select2();
    }
});
</script>
@endsection
