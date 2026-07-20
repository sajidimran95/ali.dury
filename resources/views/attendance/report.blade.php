@extends('layouts.app')
@section('title', __('attendance.attendance_report'))

@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        @lang('attendance.attendance_report')
        <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">
            @if($can_view_all)
                @lang('attendance.all_employees')
            @else
                @lang('attendance.my_attendance')
            @endif
        </small>
    </h1>
</section>

<section class="content">
    @component('components.filters', ['title' => __('report.filters')])
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('attendance_date_filter', __('report.date_range') . ':') !!}
                {!! Form::text('attendance_date_filter', null, ['class' => 'form-control', 'readonly', 'id' => 'attendance_date_filter']) !!}
            </div>
        </div>
        @if($can_view_all)
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('attendance_user_id', __('attendance.filter_employee') . ':') !!}
                    {!! Form::select('attendance_user_id', $users, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%',
                        'placeholder' => __('attendance.all_employees'),
                        'id' => 'attendance_user_id',
                    ]) !!}
                </div>
            </div>
        @endif
    @endcomponent

    @component('components.widget', ['class' => 'box-primary'])
        <table class="table table-bordered table-striped ajax_view" id="attendance_table">
            <thead>
                <tr>
                    @if($can_view_all)
                        <th>@lang('attendance.employee')</th>
                    @endif
                    <th>@lang('attendance.clock_in_time')</th>
                    <th>@lang('attendance.clock_out_time')</th>
                    <th>@lang('attendance.break_time')</th>
                    <th>@lang('attendance.worked_time')</th>
                    <th>@lang('attendance.status')</th>
                </tr>
            </thead>
        </table>
    @endcomponent
</section>
@endsection

@section('javascript')
@php $attendance_sort_col = $can_view_all ? 1 : 0; @endphp
<script type="text/javascript">
    $(document).ready(function () {
        dateRangeSettings.startDate = moment().startOf('month');
        dateRangeSettings.endDate = moment().endOf('month');
        $('#attendance_date_filter').daterangepicker(dateRangeSettings, function (start, end) {
            $('#attendance_date_filter').val(
                start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format)
            );
            attendance_table.ajax.reload();
        });
        $('#attendance_date_filter').val(
            moment().startOf('month').format(moment_date_format) +
            ' ~ ' +
            moment().endOf('month').format(moment_date_format)
        );
        $('#attendance_date_filter').on('cancel.daterangepicker', function (ev, picker) {
            $('#attendance_date_filter').val('');
            attendance_table.ajax.reload();
        });

        @if($can_view_all)
            $('#attendance_user_id').change(function () {
                attendance_table.ajax.reload();
            });
        @endif

        attendance_table = $('#attendance_table').DataTable({
            processing: true,
            serverSide: true,
            aaSorting: [[{{ $attendance_sort_col }}, 'desc']],
            ajax: {
                url: '{{ action([\App\Http\Controllers\AttendanceController::class, "report"]) }}',
                data: function (d) {
                    var range = $('#attendance_date_filter').data('daterangepicker');
                    if (range && $('#attendance_date_filter').val()) {
                        d.start_date = range.startDate.format('YYYY-MM-DD');
                        d.end_date = range.endDate.format('YYYY-MM-DD');
                    }
                    @if($can_view_all)
                        d.user_id = $('#attendance_user_id').val();
                    @endif
                }
            },
            columns: [
                @if($can_view_all)
                    { data: 'employee', name: 'user.first_name' },
                @endif
                { data: 'clock_in_at', name: 'clock_in_at' },
                { data: 'clock_out_at', name: 'clock_out_at' },
                { data: 'break_time', orderable: false, searchable: false },
                { data: 'worked_time', orderable: false, searchable: false },
                { data: 'status', name: 'status' }
            ]
        });
    });
</script>
@endsection
