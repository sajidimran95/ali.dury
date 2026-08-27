@can('attendance.clock')
<script type="text/javascript">
    $(function () {
        if (!$('#att_clock_widget').length) {
            return;
        }

        var attState = 'clocked_out';
        var clockInAt = null;
        var breakStartAt = null;
        var baseWorked = 0;
        var baseBreak = 0;

        function pad(n) { return n < 10 ? '0' + n : '' + n; }
        function formatSecs(total) {
            total = Math.max(0, Math.floor(total || 0));
            var h = Math.floor(total / 3600);
            var m = Math.floor((total % 3600) / 60);
            var s = total % 60;
            return pad(h) + ':' + pad(m) + ':' + pad(s);
        }

        function applyStatus(st) {
            if (!st) return;
            attState = st.state || 'clocked_out';
            clockInAt = st.clock_in_at ? new Date(st.clock_in_at) : null;
            breakStartAt = st.break_start_at ? new Date(st.break_start_at) : null;
            baseWorked = parseInt(st.worked_seconds, 10) || 0;
            baseBreak = parseInt(st.break_seconds, 10) || 0;
            window.__attLoadedAt = Date.now();

            var $widget = $('#att_clock_widget');
            $widget.removeClass('is-working is-break is-out');
            $('#att_btn_clock_in, #att_btn_start_break, #att_btn_end_break, #att_btn_clock_out, #att_live_timer').addClass('hide');

            if (attState === 'clocked_in') {
                $widget.addClass('is-working');
                $('#att_state_label').text(@json(__('attendance.working')));
                $('#att_btn_start_break, #att_btn_clock_out, #att_live_timer').removeClass('hide');
                if (clockInAt) {
                    $('#att_meta_label').text(@json(__('attendance.clocked_in_at')) + ': ' + clockInAt.toLocaleTimeString());
                }
            } else if (attState === 'on_break') {
                $widget.addClass('is-break');
                $('#att_state_label').text(@json(__('attendance.on_break')));
                $('#att_btn_end_break, #att_btn_clock_out, #att_live_timer').removeClass('hide');
                $('#att_meta_label').text(@json(__('attendance.on_break')));
            } else {
                $widget.addClass('is-out');
                $('#att_state_label').text(@json(__('attendance.not_clocked_in')));
                $('#att_btn_clock_in').removeClass('hide');
                $('#att_meta_label').text('');
            }

            updateTimers();
        }

        function updateTimers() {
            var now = Date.now();
            var worked = baseWorked;
            var brk = baseBreak;
            var loadedAt = window.__attLoadedAt || now;
            var elapsed = Math.max(0, Math.floor((now - loadedAt) / 1000));

            if (attState === 'clocked_in') {
                if (clockInAt && !isNaN(clockInAt.getTime())) {
                    worked = Math.max(0, Math.floor((now - clockInAt.getTime()) / 1000) - baseBreak);
                } else {
                    worked = baseWorked + elapsed;
                }
            } else if (attState === 'on_break') {
                brk = baseBreak + elapsed;
                worked = baseWorked;
            }

            var workedLabel = formatSecs(worked);
            var breakLabel = formatSecs(brk);
            $('#att_worked_timer').text(workedLabel);
            $('#att_break_timer').text(breakLabel);
            if (attState === 'on_break') {
                $('#att_live_timer_value').text(breakLabel);
            } else {
                $('#att_live_timer_value').text(workedLabel);
            }
        }

        function loadStatus() {
            $.ajax({
                url: @json(action([\App\Http\Controllers\AttendanceController::class, 'status'])),
                method: 'GET',
                dataType: 'json',
                success: function (resp) {
                    window.__attLoadedAt = Date.now();
                    applyStatus(resp);
                }
            });
        }

        function postAction(url, extra) {
            extra = extra || {};
            var data = Object.assign({ _token: @json(csrf_token()) }, extra);
            $.ajax({
                method: 'POST',
                url: url,
                data: data,
                dataType: 'json',
                success: function (result) {
                    if (typeof toastr !== 'undefined') {
                        if (result.success) {
                            toastr.success(result.msg);
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                    if (result && result.status) {
                        applyStatus(result.status);
                    } else {
                        loadStatus();
                    }
                },
                error: function () {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(@json(__('messages.something_went_wrong')));
                    }
                }
            });
        }

        function withGeoThen(url) {
            if (!navigator.geolocation) {
                postAction(url);
                return;
            }
            navigator.geolocation.getCurrentPosition(
                function (pos) {
                    postAction(url, {
                        latitude: pos.coords.latitude,
                        longitude: pos.coords.longitude
                    });
                },
                function () {
                    postAction(url);
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
            );
        }

        $(document).off('click.attClock').on('click.attClock', '#att_btn_clock_in', function (e) {
            e.preventDefault();
            withGeoThen(@json(action([\App\Http\Controllers\AttendanceController::class, 'clockIn'])));
        });
        $(document).on('click.attClock', '#att_btn_start_break', function (e) {
            e.preventDefault();
            postAction(@json(action([\App\Http\Controllers\AttendanceController::class, 'startBreak'])));
        });
        $(document).on('click.attClock', '#att_btn_end_break', function (e) {
            e.preventDefault();
            postAction(@json(action([\App\Http\Controllers\AttendanceController::class, 'endBreak'])));
        });
        $(document).on('click.attClock', '#att_btn_clock_out', function (e) {
            e.preventDefault();
            if (typeof swal === 'undefined') {
                withGeoThen(@json(action([\App\Http\Controllers\AttendanceController::class, 'clockOut'])));
                return;
            }
            swal({
                title: (typeof LANG !== 'undefined' && LANG.sure) ? LANG.sure : 'Are you sure?',
                icon: 'warning',
                buttons: true,
            }).then(function (confirmed) {
                if (confirmed) {
                    withGeoThen(@json(action([\App\Http\Controllers\AttendanceController::class, 'clockOut'])));
                }
            });
        });

        loadStatus();
        setInterval(updateTimers, 1000);
    });
</script>
@endcan
