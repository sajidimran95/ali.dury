@extends('layouts.app')
@section('title', __('team_schedule.shared_calendar'))

@php $__force_fullcalendar = true; @endphp

@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        @lang('team_schedule.shared_calendar')
        <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">
            @lang('team_schedule.team_schedule')
        </small>
    </h1>
</section>

<section class="content">
    @component('components.filters', ['title' => __('report.filters')])
        @if($can_view_all)
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('ts_user_id', __('team_schedule.filter_employee') . ':') !!}
                    {!! Form::select('ts_user_id', $users, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%',
                        'placeholder' => __('team_schedule.all_employees'),
                        'id' => 'ts_user_id',
                    ]) !!}
                </div>
            </div>
        @endif
        <div class="col-md-9">
            <p class="text-muted" style="margin-top:28px;">@lang('team_schedule.calendar_hint')</p>
        </div>
    @endcomponent

    <div class="box box-solid">
        <div class="box-body" style="padding:10px 15px;">
            <div class="att-legend">
                <span class="att-legend-title">@lang('attendance.team_colors')</span>
                <div class="att-legend-items">
                    @forelse($user_colors as $uid => $color)
                        <span class="att-legend-item">
                            <span class="att-color-dot" style="background:{{ $color }}"></span>
                            <span>
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
            </div>
        </div>
    </div>

    <div class="box box-primary">
        <div class="box-body">
            <div id="team_schedule_calendar"></div>
        </div>
    </div>
</section>
@endsection

@section('css')
<style>
    .att-color-dot { display:inline-block; width:12px; height:12px; border-radius:50%; box-shadow:0 0 0 1px rgba(0,0,0,.12); }
    .att-legend { display:flex; flex-wrap:wrap; align-items:center; gap:10px 16px; }
    .att-legend-title { font-weight:600; font-size:13px; color:#334155; }
    .att-legend-items { display:flex; flex-wrap:wrap; gap:8px 14px; }
    .att-legend-item { display:inline-flex; align-items:center; gap:6px; font-size:12.5px; color:#475569; }
    #team_schedule_calendar { min-height:620px; }
    #team_schedule_calendar .fc-event.ts-fc-break { border-style:dashed !important; opacity:.92; }
    #team_schedule_calendar .fc-event.ts-fc-shift {
        border-width:2px !important;
        border-style:solid !important;
        background:transparent !important;
        font-weight:600;
    }
    #team_schedule_calendar .fc-event.ts-fc-timeoff {
        border-style:dotted !important;
    }
</style>
@endsection

@section('javascript')
<script>
$(document).ready(function () {
    var canViewAll = @json(!empty($can_view_all));
    var calendarEl = document.getElementById('team_schedule_calendar');
    if (!calendarEl || typeof FullCalendar === 'undefined') {
        return;
    }

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        height: 'auto',
        slotMinTime: '05:00:00',
        slotMaxTime: '24:00:00',
        nowIndicator: true,
        events: function (info, success, failure) {
            var data = { start: info.startStr, end: info.endStr };
            if (canViewAll && $('#ts_user_id').val()) {
                data.user_id = $('#ts_user_id').val();
            }
            $.ajax({
                url: '{{ action([\App\Http\Controllers\TeamScheduleController::class, "calendarEvents"]) }}',
                data: data,
                dataType: 'json'
            }).done(success).fail(function () {
                failure && failure();
                toastr.error(@json(__('attendance.calendar_unavailable')));
            });
        }
    });
    calendar.render();

    $('#ts_user_id').on('change', function () {
        calendar.refetchEvents();
    });
});
</script>
@endsection
