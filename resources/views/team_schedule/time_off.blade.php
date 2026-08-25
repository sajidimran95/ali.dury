@extends('layouts.app')
@section('title', __('team_schedule.time_off'))

@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        @lang('team_schedule.time_off')
    </h1>
</section>

<section class="content">
    @component('components.filters', ['title' => __('report.filters')])
        @if($can_manage)
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('to_user_id', __('team_schedule.filter_employee') . ':') !!}
                    {!! Form::select('to_user_id', $users, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%',
                        'placeholder' => __('team_schedule.all_employees'),
                        'id' => 'to_user_id',
                    ]) !!}
                </div>
            </div>
        @endif
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('to_status', __('team_schedule.status') . ':') !!}
                {!! Form::select('to_status', [
                    '' => __('messages.all'),
                    'pending' => __('team_schedule.status_pending'),
                    'approved' => __('team_schedule.status_approved'),
                    'rejected' => __('team_schedule.status_rejected'),
                    'cancelled' => __('team_schedule.status_cancelled'),
                ], 'pending', ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'to_status']) !!}
            </div>
        </div>
        @if($can_request)
            <div class="col-md-3">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-primary btn-block" id="to_request_btn">
                        <i class="fa fa-plus"></i> @lang('team_schedule.request_time_off')
                    </button>
                </div>
            </div>
        @endif
    @endcomponent

    <div class="box box-primary">
        <div class="box-body">
            <table class="table table-bordered table-striped" id="time_off_table">
                <thead>
                    <tr>
                        <th>@lang('team_schedule.employee')</th>
                        <th>@lang('team_schedule.type')</th>
                        <th>@lang('team_schedule.starts_at')</th>
                        <th>@lang('team_schedule.ends_at')</th>
                        <th>@lang('team_schedule.reason')</th>
                        <th>@lang('team_schedule.status')</th>
                        <th>@lang('messages.action')</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</section>

@if($can_request)
<div class="modal fade" id="to_request_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">@lang('team_schedule.request_time_off')</h4>
            </div>
            <form id="to_request_form" onsubmit="return false;">
                <div class="modal-body">
                    @if($can_manage)
                        <div class="form-group">
                            <label>@lang('team_schedule.employee')</label>
                            {!! Form::select('user_id', $users, auth()->id(), ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'to_req_user_id']) !!}
                        </div>
                    @endif
                    <div class="form-group">
                        <label>@lang('team_schedule.type') *</label>
                        {!! Form::select('type', $types, 'vacation', ['class' => 'form-control', 'id' => 'to_req_type', 'required']) !!}
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('team_schedule.starts_at') *</label>
                                <input type="text" class="form-control to-datetime" id="to_req_starts" required autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('team_schedule.ends_at') *</label>
                                <input type="text" class="form-control to-datetime" id="to_req_ends" required autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>@lang('team_schedule.reason')</label>
                        <textarea class="form-control" id="to_req_reason" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                    <button type="submit" class="btn btn-primary">@lang('messages.submit')</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if($can_manage)
<div class="modal fade" id="to_review_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="to_review_title">@lang('team_schedule.approve')</h4>
            </div>
            <form id="to_review_form" onsubmit="return false;">
                <div class="modal-body">
                    <input type="hidden" id="to_review_id">
                    <input type="hidden" id="to_review_status">
                    <div class="form-group">
                        <label>@lang('team_schedule.admin_note')</label>
                        <textarea class="form-control" id="to_review_note" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                    <button type="submit" class="btn btn-primary" id="to_review_submit">@lang('messages.save')</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@section('javascript')
<script>
$(document).ready(function () {
    var table = $('#time_off_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ action([\App\Http\Controllers\TimeOffController::class, "index"]) }}',
            data: function (d) {
                d.user_id = $('#to_user_id').val() || '';
                d.status = $('#to_status').val() || '';
            }
        },
        columns: [
            { data: 'employee', name: 'employee' },
            { data: 'type', name: 'type' },
            { data: 'starts_at', name: 'starts_at' },
            { data: 'ends_at', name: 'ends_at' },
            { data: 'reason', name: 'reason', defaultContent: '' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        aaSorting: [[2, 'desc']]
    });

    $('#to_user_id, #to_status').on('change', function () { table.ajax.reload(); });

    function initDt() {
        $('.to-datetime').datetimepicker({
            format: moment_date_format + ' ' + moment_time_format,
            ignoreReadonly: true
        });
    }

    $('#to_request_btn').on('click', function () {
        $('#to_req_starts, #to_req_ends, #to_req_reason').val('');
        $('#to_request_modal').modal('show');
        setTimeout(initDt, 200);
    });

    $('#to_request_form').on('submit', function () {
        var payload = {
            type: $('#to_req_type').val(),
            starts_at: $('#to_req_starts').val(),
            ends_at: $('#to_req_ends').val(),
            reason: $('#to_req_reason').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        if ($('#to_req_user_id').length) {
            payload.user_id = $('#to_req_user_id').val();
        }
        $.post('{{ action([\App\Http\Controllers\TimeOffController::class, "store"]) }}', payload).done(function (res) {
            if (res.success) {
                toastr.success(res.msg);
                if (res.conflict) { toastr.warning(@json(__('team_schedule.conflict_warning'))); }
                $('#to_request_modal').modal('hide');
                table.ajax.reload();
            } else {
                toastr.error(res.msg);
            }
        });
    });

    $(document).on('click', '.to-approve, .to-reject', function () {
        var status = $(this).hasClass('to-approve') ? 'approved' : 'rejected';
        $('#to_review_id').val($(this).data('id'));
        $('#to_review_status').val(status);
        $('#to_review_note').val('');
        $('#to_review_title').text(status === 'approved'
            ? @json(__('team_schedule.approve'))
            : @json(__('team_schedule.reject')));
        $('#to_review_modal').modal('show');
    });

    $('#to_review_form').on('submit', function () {
        var id = $('#to_review_id').val();
        $.ajax({
            url: '{{ url("team-schedule/time-off") }}/' + id + '/review',
            method: 'POST',
            data: {
                status: $('#to_review_status').val(),
                admin_note: $('#to_review_note').val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            }
        }).done(function (res) {
            if (res.success) {
                toastr.success(res.msg);
                $('#to_review_modal').modal('hide');
                table.ajax.reload();
            } else {
                toastr.error(res.msg);
            }
        });
    });

    $(document).on('click', '.to-cancel', function () {
        var id = $(this).data('id');
        swal({ title: LANG.sure, icon: 'warning', buttons: true }).then(function (ok) {
            if (!ok) return;
            $.ajax({
                url: '{{ url("team-schedule/time-off") }}/' + id + '/cancel',
                method: 'POST',
                data: { _token: $('meta[name="csrf-token"]').attr('content') }
            }).done(function (res) {
                if (res.success) { toastr.success(res.msg); table.ajax.reload(); }
                else { toastr.error(res.msg); }
            });
        });
    });
});
</script>
@endsection
