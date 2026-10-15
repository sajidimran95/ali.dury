@extends('layouts.app')
@section('title', __('attendance.attendance_report'))

@php $__force_fullcalendar = true; @endphp

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
        <div class="col-md-3 att-filter-dates" id="att_list_date_wrap">
            <div class="form-group">
                {!! Form::label('attendance_date_filter', __('report.date_range') . ':') !!}
                {!! Form::text('attendance_date_filter', null, ['class' => 'form-control', 'readonly', 'id' => 'attendance_date_filter']) !!}
            </div>
        </div>
        @if($can_view_all)
            <div class="col-md-3">
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
        <div class="col-md-3">
            <div class="form-group">
                <label>@lang('attendance.view_mode')</label>
                <div class="btn-group btn-group-justified att-view-toggle" data-toggle="buttons" style="display:flex; gap:0;">
                    <label class="btn btn-default active" style="width:50%">
                        <input type="radio" name="att_view" value="calendar" checked autocomplete="off">
                        <i class="fa fa-calendar"></i> @lang('attendance.calendar_view')
                    </label>
                    <label class="btn btn-default" style="width:50%">
                        <input type="radio" name="att_view" value="list" autocomplete="off">
                        <i class="fa fa-list"></i> @lang('attendance.list_view')
                    </label>
                </div>
            </div>
        </div>
        @if(!empty($can_edit))
            <div class="col-md-3">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-primary btn-block" id="att_add_entry_btn">
                        <i class="fa fa-plus"></i> @lang('attendance.add_time_clock')
                    </button>
                </div>
            </div>
        @endif
    @endcomponent

    {{-- Color legend --}}
    <div class="box box-solid att-legend-box" id="att_legend_box">
        <div class="box-body" style="padding:10px 15px;">
            <div class="att-legend">
                <span class="att-legend-title">@lang('attendance.team_colors')</span>
                <div class="att-legend-items" id="att_legend_items">
                    @forelse($user_colors as $uid => $color)
                        <span class="att-legend-item" data-user-id="{{ $uid }}">
                            <span class="att-color-dot" style="background:{{ $color }}"></span>
                            <span class="att-legend-name">
                                @if($can_view_all && isset($users[$uid]))
                                    {{ $users[$uid] }}
                                @else
                                    {{ auth()->user()->user_full_name }}
                                @endif
                            </span>
                        </span>
                    @empty
                        <span class="text-muted">@lang('attendance.no_employees')</span>
                    @endforelse
                </div>
                <span class="att-legend-hint">
                    <span class="att-fc-break-sample"></span> @lang('attendance.break_blocks_hint')
                </span>
            </div>
        </div>
    </div>

    {{-- Calendar --}}
    <div class="box box-primary" id="att_calendar_panel">
        <div class="box-body">
            <div id="attendance_calendar"></div>
        </div>
    </div>

    {{-- List --}}
    <div id="att_list_panel" style="display:none;">
        @component('components.widget', ['class' => 'box-primary'])
            <table class="table table-bordered table-striped ajax_view" id="attendance_table" style="width:100%;">
                <thead>
                    <tr>
                        @if($can_view_all)
                            <th style="width:36px;"></th>
                            <th>@lang('attendance.employee')</th>
                        @endif
                        <th>@lang('attendance.clock_in_time')</th>
                        <th>@lang('attendance.clock_out_time')</th>
                        <th>@lang('attendance.break_time')</th>
                        <th>@lang('attendance.worked_time')</th>
                        <th>@lang('attendance.status')</th>
                        @if(!empty($can_edit))
                            <th>@lang('messages.action')</th>
                        @endif
                    </tr>
                </thead>
            </table>
        @endcomponent
    </div>
</section>

{{-- Edit / Create modal --}}
<div class="modal fade" id="att_edit_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="att_edit_modal_title">@lang('attendance.edit_time_clock')</h4>
            </div>
            <form id="att_edit_form" onsubmit="return false;">
                <div class="modal-body">
                    <input type="hidden" id="att_edit_id" value="">
                    @if($can_view_all)
                        <div class="form-group" id="att_employee_wrap">
                            <label for="att_edit_user_id">@lang('attendance.employee') *</label>
                            {!! Form::select('user_id', $users, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%',
                                'id' => 'att_edit_user_id',
                                'placeholder' => __('messages.please_select'),
                            ]) !!}
                        </div>
                    @else
                        <input type="hidden" id="att_edit_user_id" value="{{ auth()->user()->id }}">
                    @endif
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="att_edit_clock_in">@lang('attendance.clock_in_time') *</label>
                                <input type="text" class="form-control att-datetime" id="att_edit_clock_in" required autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="att_edit_clock_out">@lang('attendance.clock_out_time')</label>
                                <input type="text" class="form-control att-datetime" id="att_edit_clock_out" autocomplete="off" placeholder="@lang('attendance.still_open')">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="att_edit_notes">@lang('attendance.notes')</label>
                        <textarea class="form-control" id="att_edit_notes" rows="2"></textarea>
                    </div>

                    <div class="att-breaks-panel">
                        <div class="clearfix" style="margin-bottom:8px;">
                            <strong>@lang('attendance.breaks')</strong>
                            <button type="button" class="btn btn-default btn-xs pull-right" id="att_add_break_btn">
                                <i class="fa fa-plus"></i> @lang('attendance.add_break')
                            </button>
                        </div>
                        <div id="att_breaks_list"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger pull-left" id="att_delete_btn" style="display:none;">
                        <i class="fa fa-trash"></i> @lang('messages.delete')
                    </button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                    <button type="submit" class="btn btn-primary" id="att_save_btn">
                        <i class="fa fa-save"></i> @lang('messages.save')
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
    .att-color-dot {
        display: inline-block;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        vertical-align: middle;
        box-shadow: 0 0 0 1px rgba(0,0,0,.12);
    }
    .att-legend {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px 16px;
    }
    .att-legend-title {
        font-weight: 600;
        font-size: 13px;
        color: #334155;
        margin-right: 4px;
    }
    .att-legend-items {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 14px;
        flex: 1;
    }
    .att-legend-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12.5px;
        color: #475569;
    }
    .att-legend-hint {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: #64748b;
        margin-left: auto;
    }
    .att-fc-break-sample {
        display: inline-block;
        width: 18px;
        height: 10px;
        border-radius: 2px;
        background: repeating-linear-gradient(
            -45deg,
            #94a3b8,
            #94a3b8 3px,
            #e2e8f0 3px,
            #e2e8f0 6px
        );
        border: 1px solid #64748b;
    }
    #attendance_calendar {
        min-height: 620px;
    }
    #attendance_calendar .fc-event.att-fc-break {
        border-style: dashed !important;
        opacity: 0.92;
    }
    #attendance_calendar .fc-event.att-fc-work {
        border-width: 0 0 0 3px;
        font-weight: 600;
    }
    #attendance_calendar .fc-time-grid-event .fc-title {
        font-size: 12px;
    }
    .att-view-toggle .btn.active {
        background: #2563eb;
        color: #fff;
        border-color: #2563eb;
    }
    .att-break-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 8px;
        padding: 8px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        align-items: flex-end;
    }
    .att-break-row .form-group {
        margin-bottom: 0;
        flex: 1 1 160px;
    }
    .att-break-row .att-break-remove {
        flex: 0 0 auto;
    }
    .pl-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.3;
        white-space: nowrap;
    }
    .pl-status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
        flex-shrink: 0;
    }
    .pl-status--ok {
        color: #067647;
        background: #ecfdf3;
    }
    .pl-status--low {
        color: #b54708;
        background: #fffaeb;
    }
    .pl-status--inactive {
        color: #475569;
        background: #f1f5f9;
    }
    #att_list_panel .dataTables_wrapper,
    #att_list_panel table.dataTable {
        width: 100% !important;
    }
    #att_list_panel table.dataTable thead th,
    #att_list_panel table.dataTable tbody td {
        white-space: nowrap;
        vertical-align: middle !important;
    }
    #att_list_panel table.dataTable thead th:first-child,
    #att_list_panel table.dataTable tbody td:first-child {
        width: 40px !important;
        max-width: 48px !important;
        text-align: center;
    }
</style>
@endsection

@section('javascript')
@php $attendance_sort_col = $can_view_all ? 2 : 0; @endphp
<script type="text/javascript">
(function () {
    var canEdit = @json(!empty($can_edit));
    var canViewAll = @json(!empty($can_view_all));
    var reportUrl = @json(action([\App\Http\Controllers\AttendanceController::class, 'report']));
    var calendarUrl = @json(action([\App\Http\Controllers\AttendanceController::class, 'calendarEvents']));
    var storeUrl = @json(action([\App\Http\Controllers\AttendanceController::class, 'store']));
    var attendance_table = null;
    var currentView = 'calendar';
    var dtFormat = (typeof moment_date_format !== 'undefined' ? moment_date_format : 'MM/DD/YYYY')
        + ' ' + (typeof moment_time_format !== 'undefined' ? moment_time_format : 'HH:mm');

    function listRange() {
        var range = $('#attendance_date_filter').data('daterangepicker');
        if (range && $('#attendance_date_filter').val()) {
            return {
                start_date: range.startDate.format('YYYY-MM-DD'),
                end_date: range.endDate.format('YYYY-MM-DD')
            };
        }
        return {};
    }

    function selectedUserId() {
        return canViewAll ? ($('#attendance_user_id').val() || '') : '';
    }

    function calendarDataParams() {
        var d = {};
        if (selectedUserId()) {
            d.user_id = selectedUserId();
        }
        return d;
    }

    function reloadCalendar() {
        var cal = $('#attendance_calendar');
        if (cal.data('fullCalendar') || cal.fullCalendar) {
            try {
                cal.fullCalendar('refetchEvents');
            } catch (e) {}
        }
    }

    function initAttendanceTable() {
        if (attendance_table) {
            attendance_table.columns.adjust();
            attendance_table.ajax.reload();
            return;
        }

        var columns = [];
        if (canViewAll) {
            columns.push({
                data: 'color_swatch',
                name: 'color_swatch',
                orderable: false,
                searchable: false,
                width: '40px',
                className: 'text-center',
            });
            columns.push({ data: 'employee', name: 'user.first_name' });
        }
        columns.push({ data: 'clock_in_at', name: 'clock_in_at' });
        columns.push({ data: 'clock_out_at', name: 'clock_out_at' });
        columns.push({ data: 'break_time', orderable: false, searchable: false });
        columns.push({ data: 'worked_time', orderable: false, searchable: false });
        columns.push({ data: 'status', name: 'status' });
        if (canEdit) {
            columns.push({ data: 'action', orderable: false, searchable: false });
        }

        attendance_table = $('#attendance_table').DataTable({
            processing: true,
            serverSide: true,
            fixedHeader: false,
            aaSorting: [[{{ $attendance_sort_col }}, 'desc']],
            ajax: {
                url: reportUrl,
                data: function (d) {
                    var r = listRange();
                    d.start_date = r.start_date || '';
                    d.end_date = r.end_date || '';
                    if (selectedUserId()) {
                        d.user_id = selectedUserId();
                    }
                }
            },
            columns: columns
        });
    }

    function setView(mode) {
        currentView = mode;
        if (mode === 'calendar') {
            $('#att_calendar_panel, #att_legend_box').show();
            $('#att_list_panel').hide();
            $('#att_list_date_wrap').hide();
            reloadCalendar();
            setTimeout(function () {
                try { $('#attendance_calendar').fullCalendar('render'); } catch (e) {}
            }, 50);
        } else {
            $('#att_calendar_panel').hide();
            $('#att_list_panel, #att_list_date_wrap, #att_legend_box').show();
            setTimeout(initAttendanceTable, 50);
        }
    }

    function initDatetimePickers(ctx) {
        var $root = ctx ? $(ctx) : $(document);
        $root.find('.att-datetime').each(function () {
            var $el = $(this);
            if ($el.data('DateTimePicker')) {
                return;
            }
            if ($.fn.datetimepicker) {
                $el.datetimepicker({
                    format: dtFormat,
                    ignoreReadonly: true,
                    sideBySide: true
                });
            }
        });
    }

    function breakRowHtml(data) {
        data = data || {};
        return '' +
            '<div class="att-break-row">' +
            '  <div class="form-group">' +
            '    <label>@lang('attendance.break_start')</label>' +
            '    <input type="text" class="form-control att-datetime att-break-start" value="' + __escape_html(data.break_start_at || '') + '" autocomplete="off">' +
            '  </div>' +
            '  <div class="form-group">' +
            '    <label>@lang('attendance.break_end')</label>' +
            '    <input type="text" class="form-control att-datetime att-break-end" value="' + __escape_html(data.break_end_at || '') + '" autocomplete="off">' +
            '  </div>' +
            '  <div class="form-group">' +
            '    <label>@lang('attendance.break_reason')</label>' +
            '    <input type="text" class="form-control att-break-reason" value="' + __escape_html(data.reason || '') + '">' +
            '  </div>' +
            '  <button type="button" class="btn btn-link text-danger att-break-remove" title="@lang('messages.delete')"><i class="fa fa-times"></i></button>' +
            '</div>';
    }

    function __escape_html(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function collectBreaks() {
        var breaks = [];
        $('#att_breaks_list .att-break-row').each(function () {
            var start = $(this).find('.att-break-start').val();
            if (!start) return;
            breaks.push({
                break_start_at: start,
                break_end_at: $(this).find('.att-break-end').val() || null,
                reason: $(this).find('.att-break-reason').val() || null
            });
        });
        return breaks;
    }

    function openCreateModal() {
        $('#att_edit_modal_title').text(@json(__('attendance.add_time_clock')));
        $('#att_edit_id').val('');
        $('#att_edit_clock_in').val('');
        $('#att_edit_clock_out').val('');
        $('#att_edit_notes').val('');
        $('#att_breaks_list').empty();
        $('#att_delete_btn').hide();
        if (canViewAll) {
            $('#att_employee_wrap').show();
            $('#att_edit_user_id').val(null).trigger('change');
            $('#att_edit_user_id').prop('disabled', false);
        }
        $('#att_edit_modal').modal('show');
        setTimeout(function () { initDatetimePickers('#att_edit_modal'); }, 200);
    }

    function openEditModal(id) {
        $.ajax({
            url: base_path + '/attendance/' + id,
            method: 'GET',
            dataType: 'json'
        }).done(function (res) {
            if (!res.success || !res.data) {
                toastr.error(res.msg || LANG.something_went_wrong);
                return;
            }
            var d = res.data;
            $('#att_edit_modal_title').text(@json(__('attendance.edit_time_clock')));
            $('#att_edit_id').val(d.id);
            $('#att_edit_clock_in').val(d.clock_in_at || '');
            $('#att_edit_clock_out').val(d.clock_out_at || '');
            $('#att_edit_notes').val(d.notes || '');
            if (canViewAll) {
                $('#att_employee_wrap').show();
                $('#att_edit_user_id').val(d.user_id).trigger('change');
                $('#att_edit_user_id').prop('disabled', true);
            }
            $('#att_breaks_list').empty();
            (d.breaks || []).forEach(function (b) {
                $('#att_breaks_list').append(breakRowHtml(b));
            });
            $('#att_delete_btn').show();
            $('#att_edit_modal').modal('show');
            setTimeout(function () { initDatetimePickers('#att_edit_modal'); }, 200);
        }).fail(function () {
            toastr.error(LANG.something_went_wrong);
        });
    }

    function saveEntry() {
        var id = $('#att_edit_id').val();
        var payload = {
            clock_in_at: $('#att_edit_clock_in').val(),
            clock_out_at: $('#att_edit_clock_out').val() || null,
            notes: $('#att_edit_notes').val() || null,
            breaks: collectBreaks(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        if (!payload.clock_in_at) {
            toastr.error(@json(__('attendance.clock_in_required')));
            return;
        }

        var url, method;
        if (id) {
            url = base_path + '/attendance/' + id;
            method = 'PUT';
        } else {
            url = storeUrl;
            method = 'POST';
            payload.user_id = $('#att_edit_user_id').val();
            if (!payload.user_id) {
                toastr.error(@json(__('attendance.employee_required')));
                return;
            }
        }

        $.ajax({
            url: url,
            method: method,
            data: payload,
            dataType: 'json'
        }).done(function (res) {
            if (res.success) {
                toastr.success(res.msg);
                $('#att_edit_modal').modal('hide');
                if (attendance_table) attendance_table.ajax.reload(null, false);
                reloadCalendar();
            } else {
                toastr.error(res.msg || LANG.something_went_wrong);
            }
        }).fail(function (xhr) {
            var msg = (xhr.responseJSON && (xhr.responseJSON.msg || xhr.responseJSON.message)) || LANG.something_went_wrong;
            toastr.error(msg);
        });
    }

    function deleteEntry() {
        var id = $('#att_edit_id').val();
        if (!id) return;

        swal({
            title: LANG.sure,
            text: @json(__('attendance.confirm_delete')),
            icon: 'warning',
            buttons: true,
            dangerMode: true
        }).then(function (will) {
            if (!will) return;
            $.ajax({
                url: base_path + '/attendance/' + id,
                method: 'DELETE',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                dataType: 'json'
            }).done(function (res) {
                if (res.success) {
                    toastr.success(res.msg);
                    $('#att_edit_modal').modal('hide');
                    if (attendance_table) attendance_table.ajax.reload(null, false);
                    reloadCalendar();
                } else {
                    toastr.error(res.msg || LANG.something_went_wrong);
                }
            }).fail(function () {
                toastr.error(LANG.something_went_wrong);
            });
        });
    }

    $(document).ready(function () {
        // Date range for list (calendar uses its own nav)
        dateRangeSettings.startDate = moment().subtract(5, 'months').startOf('month');
        dateRangeSettings.endDate = moment().endOf('month');
        $('#attendance_date_filter').daterangepicker(dateRangeSettings, function (start, end) {
            $('#attendance_date_filter').val(
                start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format)
            );
            if (attendance_table) attendance_table.ajax.reload();
        });
        $('#attendance_date_filter').val(
            moment().subtract(5, 'months').startOf('month').format(moment_date_format) +
            ' ~ ' +
            moment().endOf('month').format(moment_date_format)
        );
        $('#attendance_date_filter').on('cancel.daterangepicker', function () {
            $('#attendance_date_filter').val('');
            if (attendance_table) attendance_table.ajax.reload();
        });

        if (canViewAll) {
            $('#attendance_user_id').change(function () {
                if (attendance_table) attendance_table.ajax.reload();
                reloadCalendar();
            });
        }

        // View toggle
        $('input[name="att_view"]').on('change', function () {
            setView($(this).val());
        });
        // hide list date filter by default (calendar)
        $('#att_list_date_wrap').hide();

        // FullCalendar
        if ($.fn.fullCalendar) {
            $('#attendance_calendar').fullCalendar({
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay,listWeek'
                },
                defaultView: 'agendaWeek',
                contentHeight: 'auto',
                allDaySlot: false,
                nowIndicator: true,
                eventLimit: true,
                slotEventOverlap: true,
                navLinks: true,
                eventSources: [
                    {
                        url: calendarUrl,
                        type: 'GET',
                        data: function () {
                            return calendarDataParams();
                        },
                        error: function () {
                            toastr.error(LANG.something_went_wrong || 'Calendar load failed');
                        }
                    }
                ],
                eventClick: function (event) {
                    if (!canEdit || !event.attendance_id) {
                        return;
                    }
                    openEditModal(event.attendance_id);
                },
                eventRender: function (event, element) {
                    var title = event.title || '';
                    if (event.extendedProps && event.extendedProps.worked && event.type === 'work') {
                        element.attr('title', title + ' · ' + event.extendedProps.worked);
                    } else {
                        element.attr('title', title);
                    }
                    if (canEdit) {
                        element.css('cursor', 'pointer');
                    }
                }
            });
            setTimeout(function () {
                try { $('#attendance_calendar').fullCalendar('render'); } catch (e) {}
            }, 0);
            $(window).on('resize.attCal', function () {
                try { $('#attendance_calendar').fullCalendar('render'); } catch (e) {}
            });
        } else {
            $('#att_calendar_panel .box-body').html(
                '<p class="text-muted text-center" style="padding:40px 0;">@lang('attendance.calendar_unavailable')</p>'
            );
            setView('list');
            $('input[name="att_view"][value="list"]').closest('label').addClass('active');
            $('input[name="att_view"][value="calendar"]').closest('label').removeClass('active');
        }

        // Edit actions
        $(document).on('click', '.att-edit-btn', function () {
            openEditModal($(this).data('id'));
        });
        $('#att_add_entry_btn').on('click', openCreateModal);
        $('#att_add_break_btn').on('click', function () {
            $('#att_breaks_list').append(breakRowHtml({}));
            initDatetimePickers('#att_breaks_list');
        });
        $(document).on('click', '.att-break-remove', function () {
            $(this).closest('.att-break-row').remove();
        });
        $('#att_edit_form').on('submit', function (e) {
            e.preventDefault();
            saveEntry();
        });
        $('#att_delete_btn').on('click', deleteEntry);

        // Laravel method spoofing for PUT via ajax is not needed when using method: 'PUT' with jQuery + CSRF
        // Ensure _method if needed:
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    });
})();
</script>
@endsection
