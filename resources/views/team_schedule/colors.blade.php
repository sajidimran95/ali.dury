@extends('layouts.app')
@section('title', __('team_schedule.member_colors'))

@section('content')
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        @lang('team_schedule.member_colors')
    </h1>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-body">
            <form id="ts_colors_form" onsubmit="return false;">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>@lang('team_schedule.employee')</th>
                            <th style="width:120px;">@lang('attendance.team_colors')</th>
                            <th style="width:140px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            <tr>
                                <td>{{ $row['name'] }}</td>
                                <td>
                                    <input type="color"
                                           class="form-control ts-color-input"
                                           name="colors[{{ $row['user_id'] }}]"
                                           value="{{ $row['color'] }}"
                                           data-user-id="{{ $row['user_id'] }}"
                                           data-default="{{ \App\EmployeeAttendance::colorForUserId((int) $row['user_id']) }}"
                                           style="height:38px;padding:2px;">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-default btn-sm ts-reset-color"
                                            data-user-id="{{ $row['user_id'] }}">
                                        @lang('team_schedule.reset_color')
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> @lang('team_schedule.save_colors')
                </button>
            </form>
        </div>
    </div>
</section>
@endsection

@section('javascript')
<script>
$(document).ready(function () {
    var resets = {};

    $(document).on('click', '.ts-reset-color', function () {
        var uid = $(this).data('user-id');
        var $input = $('.ts-color-input[data-user-id="' + uid + '"]');
        $input.val($input.data('default'));
        resets[uid] = true;
    });

    $('.ts-color-input').on('change', function () {
        delete resets[$(this).data('user-id')];
    });

    $('#ts_colors_form').on('submit', function () {
        var colors = {};
        $('.ts-color-input').each(function () {
            var uid = $(this).data('user-id');
            colors[uid] = resets[uid] ? 'RESET' : $(this).val();
        });
        $.ajax({
            url: '{{ action([\App\Http\Controllers\TeamScheduleController::class, "saveColors"]) }}',
            method: 'POST',
            data: {
                colors: colors,
                _token: $('meta[name="csrf-token"]').attr('content')
            }
        }).done(function (res) {
            if (res.success) {
                toastr.success(res.msg);
                resets = {};
            } else {
                toastr.error(res.msg);
            }
        });
    });
});
</script>
@endsection
