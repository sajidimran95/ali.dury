@extends('layouts.app')
@section('title', __('team_schedule.shifts'))

@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        @lang('team_schedule.shifts')
    </h1>
</section>

<section class="content">
    @component('components.filters', ['title' => __('report.filters')])
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('shift_date_filter', __('report.date_range') . ':') !!}
                {!! Form::text('shift_date_filter', null, ['class' => 'form-control', 'readonly', 'id' => 'shift_date_filter']) !!}
            </div>
        </div>
        @if($can_view_all)
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('shift_user_id', __('team_schedule.filter_employee') . ':') !!}
                    {!! Form::select('shift_user_id', $users, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%',
                        'placeholder' => __('team_schedule.all_employees'),
                        'id' => 'shift_user_id',
                    ]) !!}
                </div>
            </div>
        @endif
        @if($can_manage)
            <div class="col-md-3">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-primary btn-block" id="ts_add_shift_btn">
                        <i class="fa fa-plus"></i> @lang('team_schedule.add_shift')
                    </button>
                </div>
            </div>
        @endif
    @endcomponent

    <div class="box box-primary">
        <div class="box-body">
            <table class="table table-bordered table-striped" id="shifts_table">
                <thead>
                    <tr>
                        <th style="width:36px;"></th>
                        <th>@lang('team_schedule.employee')</th>
                        <th>@lang('team_schedule.starts_at')</th>
                        <th>@lang('team_schedule.ends_at')</th>
                        <th>@lang('team_schedule.title')</th>
                        <th>@lang('team_schedule.location')</th>
                        @if($can_manage)
                            <th>@lang('messages.action')</th>
                        @endif
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</section>

@if($can_manage)
<div class="modal fade" id="ts_shift_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="ts_shift_modal_title">@lang('team_schedule.add_shift')</h4>
            </div>
            <form id="ts_shift_form" onsubmit="return false;">
                <div class="modal-body">
                    <input type="hidden" id="ts_shift_id" value="">
                    <div class="form-group">
                        <label>@lang('team_schedule.employee') *</label>
                        {!! Form::select('user_id', $users, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'ts_shift_user_id', 'required']) !!}
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('team_schedule.starts_at') *</label>
                                <input type="text" class="form-control ts-datetime" id="ts_shift_starts" required autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('team_schedule.ends_at') *</label>
                                <input type="text" class="form-control ts-datetime" id="ts_shift_ends" required autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>@lang('team_schedule.location')</label>
                        {!! Form::select('location_id', $locations, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'ts_shift_location_id', 'placeholder' => __('messages.please_select')]) !!}
                    </div>
                    <div class="form-group">
                        <label>@lang('team_schedule.title')</label>
                        <input type="text" class="form-control" id="ts_shift_title">
                    </div>
                    <div class="form-group">
                        <label>@lang('team_schedule.notes')</label>
                        <textarea class="form-control" id="ts_shift_notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                    <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@section('css')
<style>
.att-color-dot { display:inline-block; width:12px; height:12px; border-radius:50%; box-shadow:0 0 0 1px rgba(0,0,0,.12); }
</style>
@endsection

@section('javascript')
<script>
$(document).ready(function () {
    var canManage = @json(!empty($can_manage));
    var start = moment().startOf('month');
    var end = moment().endOf('month');

    if ($('#shift_date_filter').length && typeof dateRangeSettings !== 'undefined') {
        $('#shift_date_filter').daterangepicker(dateRangeSettings, function (s, e) {
            start = s; end = e;
            table.ajax.reload();
        });
        $('#shift_date_filter').data('daterangepicker').setStartDate(start);
        $('#shift_date_filter').data('daterangepicker').setEndDate(end);
    }

    var cols = [
        { data: 'color_swatch', name: 'color_swatch', orderable: false, searchable: false },
        { data: 'employee', name: 'employee' },
        { data: 'starts_at', name: 'starts_at' },
        { data: 'ends_at', name: 'ends_at' },
        { data: 'title', name: 'title', defaultContent: '' },
        { data: 'location_name', name: 'location_name', orderable: false }
    ];
    if (canManage) {
        cols.push({ data: 'action', name: 'action', orderable: false, searchable: false });
    }

    var table = $('#shifts_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ action([\App\Http\Controllers\TeamScheduleController::class, "shifts"]) }}',
            data: function (d) {
                d.start_date = start.format('YYYY-MM-DD');
                d.end_date = end.format('YYYY-MM-DD');
                d.user_id = $('#shift_user_id').val() || '';
            }
        },
        columns: cols,
        aaSorting: [[2, 'desc']]
    });

    $('#shift_user_id').on('change', function () { table.ajax.reload(); });

    function initDt() {
        $('.ts-datetime').datetimepicker({
            format: moment_date_format + ' ' + moment_time_format,
            ignoreReadonly: true
        });
    }

    $('#ts_add_shift_btn').on('click', function () {
        $('#ts_shift_modal_title').text(@json(__('team_schedule.add_shift')));
        $('#ts_shift_id').val('');
        $('#ts_shift_user_id').val(null).trigger('change');
        $('#ts_shift_starts, #ts_shift_ends, #ts_shift_title, #ts_shift_notes').val('');
        $('#ts_shift_location_id').val(null).trigger('change');
        $('#ts_shift_modal').modal('show');
        setTimeout(initDt, 200);
    });

    $(document).on('click', '.ts-edit-shift', function () {
        var id = $(this).data('id');
        $.get('{{ url("team-schedule/shifts") }}/' + id).done(function (res) {
            if (!res.success) { toastr.error(res.msg); return; }
            var d = res.data;
            $('#ts_shift_modal_title').text(@json(__('team_schedule.edit_shift')));
            $('#ts_shift_id').val(d.id);
            $('#ts_shift_user_id').val(d.user_id).trigger('change');
            $('#ts_shift_starts').val(d.starts_at);
            $('#ts_shift_ends').val(d.ends_at);
            $('#ts_shift_title').val(d.title || '');
            $('#ts_shift_notes').val(d.notes || '');
            $('#ts_shift_location_id').val(d.location_id || null).trigger('change');
            $('#ts_shift_modal').modal('show');
            setTimeout(initDt, 200);
        });
    });

    $(document).on('click', '.ts-delete-shift', function () {
        var id = $(this).data('id');
        swal({ title: LANG.sure, text: @json(__('team_schedule.confirm_delete_shift')), icon: 'warning', buttons: true, dangerMode: true })
            .then(function (ok) {
                if (!ok) return;
                $.ajax({
                    url: '{{ url("team-schedule/shifts") }}/' + id,
                    method: 'DELETE',
                    data: { _token: $('meta[name="csrf-token"]').attr('content') }
                }).done(function (res) {
                    if (res.success) { toastr.success(res.msg); table.ajax.reload(); }
                    else { toastr.error(res.msg); }
                });
            });
    });

    $('#ts_shift_form').on('submit', function () {
        var id = $('#ts_shift_id').val();
        var payload = {
            user_id: $('#ts_shift_user_id').val(),
            starts_at: $('#ts_shift_starts').val(),
            ends_at: $('#ts_shift_ends').val(),
            location_id: $('#ts_shift_location_id').val() || null,
            title: $('#ts_shift_title').val(),
            notes: $('#ts_shift_notes').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        var url = '{{ url("team-schedule/shifts") }}';
        var method = 'POST';
        if (id) {
            url += '/' + id;
            method = 'PUT';
        }
        $.ajax({ url: url, method: method, data: payload }).done(function (res) {
            if (res.success) {
                toastr.success(res.msg);
                $('#ts_shift_modal').modal('hide');
                table.ajax.reload();
            } else {
                toastr.error(res.msg);
            }
        });
    });
});
</script>
@endsection
