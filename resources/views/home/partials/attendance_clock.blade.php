{{-- Dashboard attendance clock widget --}}
@can('attendance.clock')
<div class="att-clock-card" id="att_clock_widget">
    <div class="att-clock-card__left">
        <div class="att-clock-card__icon" id="att_clock_icon">
            <i class="fas fa-user-clock"></i>
        </div>
        <div>
            <p class="att-clock-card__title">@lang('attendance.today_status')</p>
            <p class="att-clock-card__state" id="att_state_label">@lang('attendance.not_clocked_in')</p>
            <p class="att-clock-card__meta" id="att_meta_label"></p>
        </div>
    </div>

    <div class="att-clock-card__timers">
        <div>
            <span class="att-clock-card__timer-label">@lang('attendance.worked_today')</span>
            <span class="att-clock-card__timer" id="att_worked_timer">00:00:00</span>
        </div>
        <div>
            <span class="att-clock-card__timer-label">@lang('attendance.break_time')</span>
            <span class="att-clock-card__timer" id="att_break_timer">00:00:00</span>
        </div>
    </div>

    <div class="att-clock-card__actions" id="att_actions">
        <span class="att-live-timer hide" id="att_live_timer" aria-live="polite" title="@lang('attendance.worked_today')">
            <i class="fas fa-clock"></i>
            <span id="att_live_timer_value">00:00:00</span>
        </span>
        <button type="button" class="att-btn att-btn--in" id="att_btn_clock_in">
            <i class="fas fa-sign-in-alt"></i> @lang('attendance.clock_in')
        </button>
        <button type="button" class="att-btn att-btn--break hide" id="att_btn_start_break">
            <i class="fas fa-coffee"></i> @lang('attendance.start_break')
        </button>
        <button type="button" class="att-btn att-btn--resume hide" id="att_btn_end_break">
            <i class="fas fa-play"></i> @lang('attendance.end_break')
        </button>
        <button type="button" class="att-btn att-btn--out hide" id="att_btn_clock_out">
            <i class="fas fa-sign-out-alt"></i> @lang('attendance.clock_out')
        </button>
        @can('attendance.view')
            <a href="{{ action([\App\Http\Controllers\AttendanceController::class, 'report']) }}"
               class="att-btn att-btn--link"
               id="att_btn_view_report">
                <i class="fas fa-list"></i> @lang('attendance.view_report')
            </a>
        @endcan
    </div>
</div>
@endcan
