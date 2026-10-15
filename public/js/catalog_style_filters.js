$(document).ready(function () {
    var $root = $('#catalog_style_filters');
    if (!$root.length) {
        return;
    }

    var dateId = $root.data('date-id');
    var tableSelector = $root.data('table');
    var $form = $('#' + $root.data('form'));
    var $legacyDate = $('#' + dateId);
    var $startInput = $root.find('.csf-start');
    var $endInput = $root.find('.csf-end');
    var ymd = 'YYYY-MM-DD';
    var today = moment().startOf('day');
    var fyStart = typeof financial_year !== 'undefined' && financial_year.start && typeof financial_year.start.isValid === 'function' && financial_year.start.isValid()
        ? financial_year.start.clone().startOf('day')
        : today.clone().startOf('year');
    while (fyStart.isAfter(today)) {
        fyStart.subtract(1, 'year');
    }
    while (fyStart.clone().add(1, 'year').isSameOrBefore(today)) {
        fyStart.add(1, 'year');
    }
    var fqStart = fyStart.clone().add(Math.floor(today.diff(fyStart, 'months') / 3) * 3, 'months');
    var weekStart = today.clone().startOf('week');
    var monthStart = today.clone().startOf('month');
    var span = function (start, length, unit) {
        return [start.clone(), start.clone().add(length, unit).subtract(1, 'day')];
    };
    var presets = [
        ['all', 'All'],
        ['today', 'Today', [today.clone(), today.clone()]],
        ['this_week', 'This Week', span(weekStart, 1, 'week')],
        ['this_week_to_date', 'This Week-to-date', [weekStart.clone(), today.clone()]],
        ['this_month', 'This Month', span(monthStart, 1, 'month')],
        ['this_month_to_date', 'This Month-to-date', [monthStart.clone(), today.clone()]],
        ['this_quarter', 'This Fiscal Quarter', span(fqStart, 3, 'months')],
        ['this_quarter_to_date', 'This Fiscal Quarter-to-date', [fqStart.clone(), today.clone()]],
        ['this_year', 'This Fiscal Year', span(fyStart, 1, 'year')],
        ['this_year_to_date', 'This Fiscal Year-to-date', [fyStart.clone(), today.clone()]],
        ['yesterday', 'Yesterday', [today.clone().subtract(1, 'day'), today.clone().subtract(1, 'day')]],
        ['last_week', 'Last Week', span(weekStart.clone().subtract(1, 'week'), 1, 'week')],
        ['last_month', 'Last Month', span(monthStart.clone().subtract(1, 'month'), 1, 'month')],
        ['last_quarter', 'Last Fiscal Quarter', span(fqStart.clone().subtract(3, 'months'), 3, 'months')],
        ['last_year', 'Last Fiscal Year', span(fyStart.clone().subtract(1, 'year'), 1, 'year')],
        ['next_week', 'Next Week', span(weekStart.clone().add(1, 'week'), 1, 'week')],
        ['next_4_weeks', 'Next 4 Weeks', span(weekStart.clone().add(1, 'week'), 4, 'weeks')],
        ['next_month', 'Next Month', span(monthStart.clone().add(1, 'month'), 1, 'month')],
        ['next_quarter', 'Next Fiscal Quarter', span(fqStart.clone().add(3, 'months'), 3, 'months')],
        ['next_year', 'Next Fiscal Year', span(fyStart.clone().add(1, 'year'), 1, 'year')],
        ['custom', 'Custom'],
    ];
    var presetRange = function (key) {
        var found = $.grep(presets, function (preset) {
            return preset[0] === key;
        })[0];
        return found ? found[2] : null;
    };
    var matchPreset = function (start, end) {
        var found = $.grep(presets, function (preset) {
            return (
                preset[2] &&
                preset[2][0].format(ymd) === start.format(ymd) &&
                preset[2][1].format(ymd) === end.format(ymd)
            );
        })[0];
        return found ? found[0] : 'custom';
    };

    var skipReload = false;
    var reloadReport = function () {
        if (skipReload) {
            return;
        }
        if (typeof updateSalesRepresentativeReport === 'function' && $('#sr_sales_report').length) {
            updateSalesRepresentativeReport();
        }
        if (typeof sell_payment_report !== 'undefined' && sell_payment_report && $('#sell_payment_report_table').length) {
            sell_payment_report.ajax.reload();
        }
    };

    var syncLegacyDate = function (start, end, all) {
        var picker = $legacyDate.data('daterangepicker');
        if (all) {
            $legacyDate.val('');
            $startInput.val('');
            $endInput.val('');
            if (picker) {
                picker.setStartDate(moment('2000-01-01'));
                picker.setEndDate(moment().add(10, 'years'));
            }
            return;
        }
        $startInput.val(start.format(ymd));
        $endInput.val(end.format(ymd));
        $legacyDate.val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
        if (picker) {
            picker.setStartDate(start);
            picker.setEndDate(end);
        }
    };

    $('.rd-preset').each(function () {
        var $select = $(this);
        if ($select.children().length) {
            return;
        }
        $.each(presets, function (i, preset) {
            $select.append($('<option>').val(preset[0]).text(preset[1]));
        });
    });

    var showRange = function (start, end, preset, all) {
        if (all) {
            $('.rd-from, .rd-to').val('');
            $('.rd-preset').val('all');
            syncLegacyDate(null, null, true);
            return;
        }
        syncLegacyDate(start, end, false);
        $('.rd-from').val(start.format(moment_date_format));
        $('.rd-to').val(end.format(moment_date_format));
        $('.rd-from, .rd-to').each(function () {
            var picker = $(this).data('daterangepicker');
            if (picker) {
                var date = $(this).hasClass('rd-from') ? start : end;
                picker.setStartDate(date);
                picker.setEndDate(date);
            }
        });
        $('.rd-preset').val(preset || matchPreset(start, end));
    };

    var currentStart = function () {
        return moment($startInput.val(), ymd);
    };
    var currentEnd = function () {
        return moment($endInput.val(), ymd);
    };
    var setDate = function (isFrom, date) {
        var start = isFrom ? date : currentStart();
        var end = isFrom ? currentEnd() : date;
        if (!start.isValid()) {
            start = date.clone();
        }
        if (!end.isValid()) {
            end = date.clone();
        }
        if (start.isAfter(end) && isFrom) {
            end = start.clone();
        } else if (start.isAfter(end)) {
            start = end.clone();
        }
        showRange(start, end);
        reloadReport();
        refreshChoices();
    };

    $('.rd-from, .rd-to').each(function () {
        var isFrom = $(this).hasClass('rd-from');
        $(this).daterangepicker(
            {
                singleDatePicker: true,
                showDropdowns: true,
                autoApply: true,
                autoUpdateInput: false,
                startDate: isFrom ? monthStart : today,
                locale: { format: moment_date_format },
            },
            function (date) {
                setDate(isFrom, date.clone().startOf('day'));
            }
        );
    });
    $('.rd-open').on('click', function () {
        $(this).siblings('input').data('daterangepicker').show();
    });

    $('.rd-preset').on('change', function () {
        var key = $(this).val();
        var inModal = $(this).closest('.modal').length > 0;
        if (key === 'custom') {
            $('.rd-preset').val('custom');
            return;
        }
        if (key === 'all') {
            showRange(null, null, 'all', true);
        } else {
            var range = presetRange(key);
            showRange(range[0], range[1], key);
        }
        if (!inModal) {
            reloadReport();
        }
        refreshChoices();
    });

    $form.on('submit', function (e) {
        e.preventDefault();
        reloadReport();
    });
    $('#csf_refresh').on('click', function () {
        reloadReport();
    });

    var applySort = function () {
        if (!tableSelector || !$.fn.DataTable.isDataTable(tableSelector)) {
            return;
        }
        var key = $('.rd-sort').val() || $('[name="sort_by"]').val() || 'default';
        var dir = $('[name="sort"]:checked').val() || 'desc';
        var map = {
            default: [0, 'desc'],
            date: [0, dir],
            num: [1, dir],
            name: [2, dir],
            item: [0, dir],
            amount: [5, dir],
        };
        var order = map[key] || map.default;
        $(tableSelector).DataTable().order([order]).draw();
    };
    $('.rd-sort, [name="sort_by"]').on('change', function () {
        var value = $(this).val();
        $('.rd-sort, [name="sort_by"]').val(value);
        if (!$(this).closest('.modal').length) {
            applySort();
        }
    });

    var syncBasis = function (value) {
        $('.rd-basis').prop('checked', false).filter('[value="' + value + '"]').prop('checked', true);
        $('.rd-basis-modal').prop('checked', false).filter('[value="' + value + '"]').prop('checked', true);
    };
    $('.rd-basis').on('change', function () {
        syncBasis($(this).val());
        reloadReport();
    });
    $('.rd-basis-modal').on('change', function () {
        syncBasis($(this).val());
    });

    $('#toggle_filters').on('click', function (e) {
        e.preventDefault();
        var $chips = $('#filter_chips').toggleClass('hide');
        $(this).text($chips.hasClass('hide') ? 'Show Filters' : 'Hide Filters');
    });

    var $modal = $('#modify_report_modal');
    $modal.find('.select2').each(function () {
        var $el = $(this);
        if ($el.data('select2')) {
            $el.select2('destroy');
        }
        $el.select2({ dropdownParent: $modal, width: '100%' });
    });

    $modal.on('click', '.mr-filter-list .list-group-item', function () {
        $(this).addClass('active').siblings().removeClass('active');
        $('#mr_choices tr[data-key="' + $(this).data('key') + '"]')
            .addClass('info')
            .siblings()
            .removeClass('info');
    });
    $('#mr_filter_search').on('keyup', function () {
        var term = $(this).val().toLowerCase();
        $modal.find('.mr-filter-list .list-group-item').each(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(term) !== -1);
        });
    });
    $('.mr-search').on('keyup', function () {
        var term = $(this).val().toLowerCase();
        $($(this).data('list'))
            .find('.checkbox')
            .each(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(term) !== -1);
            });
    });
    $modal.on('change', '.mr-column', function () {
        if (!tableSelector || !$.fn.DataTable.isDataTable(tableSelector)) {
            return;
        }
        $(tableSelector)
            .DataTable()
            .column($(this).val())
            .visible($(this).is(':checked'));
    });

    var snapshot = null;
    var takeSnapshot = function () {
        snapshot = $modal.find('select, input').serialize();
    };
    var refreshChoices = function () {
        var chips = [];
        $modal.find('.tab-pane[data-key]').each(function () {
            var $pane = $(this);
            var key = $pane.data('key');
            var all = $pane.data('all') || 'All';
            var value = all;
            var active = false;
            if (key === 'date') {
                var preset = $('.rd-preset').first().find('option:selected').text();
                var from = $('.rd-from').first().val();
                var to = $('.rd-to').first().val();
                value = from ? from + ' - ' + to : preset;
                active = true;
            } else {
                var $select = $pane.find('select').first();
                if ($select.length && $select.val()) {
                    value = $.trim($select.find('option:selected').text());
                    active = true;
                }
            }
            var $row = $('#mr_choices tr[data-key="' + key + '"]');
            $row.find('.mr-value').text(value);
            $row.toggleClass('hide', !active && key !== 'date');
            if (active && key !== 'date') {
                chips.push(
                    '<span class="tw-rounded tw-bg-gray-100 tw-px-2 tw-py-1">' +
                        $row.find('td:first').text() +
                        ': <strong>' +
                        $('<div>').text(value).html() +
                        '</strong></span>'
                );
            }
        });
        $('#filter_chips').html(chips.join(''));
    };

    $('#mr_remove_selected').on('click', function () {
        var $row = $('#mr_choices tr.info');
        var key = $row.data('key');
        if (!key || key === 'date') {
            return;
        }
        var $pane = $('#mr_f_' + key);
        $pane.find('select').val('').trigger('change');
        refreshChoices();
    });
    $('#mr_revert').on('click', function () {
        if (!snapshot) {
            return;
        }
        $modal.find('select, input').each(function () {
            this.checked = false;
        });
        $.each(snapshot.split('&'), function () {});
        location.reload();
    });
    $('#mr_ok').on('click', function () {
        $modal.modal('hide');
        applySort();
        reloadReport();
        refreshChoices();
    });

    $form.on('change', 'select', function () {
        refreshChoices();
    });

    skipReload = true;
    showRange(monthStart, today, 'this_month_to_date');
    skipReload = false;
    takeSnapshot();
    refreshChoices();
    reloadReport();
});
