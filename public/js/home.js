$(document).ready(function() {
    if ($('#dashboard_date_filter').length == 1) {
        dateRangeSettings.startDate = moment();
        dateRangeSettings.endDate = moment();
        $('#dashboard_date_filter').daterangepicker(dateRangeSettings, function(start, end) {
            $('#dashboard_date_filter span').html(
                start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format)
            );
            update_statistics(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
            if ($('#quotation_table').length && $('#dashboard_location').length) {
                quotation_datatable.ajax.reload();
            }
        });

        update_statistics(moment().format('YYYY-MM-DD'), moment().format('YYYY-MM-DD'));
    }

    $('#dashboard_location').change( function(e) {
        var start = $('#dashboard_date_filter')
                    .data('daterangepicker')
                    .startDate.format('YYYY-MM-DD');

        var end = $('#dashboard_date_filter')
                    .data('daterangepicker')
                    .endDate.format('YYYY-MM-DD');

        update_statistics(start, end);
    });

    // Stock alert datatables
    if ($('#stock_alert_table').length) {
    var stock_alert_table = $('#stock_alert_table').DataTable({
        processing: true,
        serverSide: true,
        ordering: false,
        searching: false,
        scrollY:        "300px",
        scrollX:        true,
        scrollCollapse: true,
        fixedHeader: false,
        dom: 'tirp',
        ajax: {
            "url": '/home/product-stock-alert',
            "data": function ( d ) {
                if ($('#stock_alert_location').length > 0) {
                    d.location_id = $('#stock_alert_location').val();
                }
            }
        },
        fnDrawCallback: function(oSettings) {
            __currency_convert_recursively($('#stock_alert_table'));
        },
    });

    $('#stock_alert_location').change( function(){
        stock_alert_table.ajax.reload();
    });
    }

    // Payment dues datatables
    if ($('#purchase_payment_dues_table').length) {
    purchase_payment_dues_table = $('#purchase_payment_dues_table').DataTable({
        processing: true,
        serverSide: true,
        ordering: false,
        searching: false,
        scrollY:        "300px",
        scrollX:        true,
        scrollCollapse: true,
        fixedHeader: false,
        dom: 'tirp',
        ajax: {
            "url": '/home/purchase-payment-dues',
            "data": function ( d ) {
                if ($('#purchase_payment_dues_location').length > 0) {
                    d.location_id = $('#purchase_payment_dues_location').val();
                }
            }
        },
        fnDrawCallback: function(oSettings) {
            __currency_convert_recursively($('#purchase_payment_dues_table'));
        },
    });

    $('#purchase_payment_dues_location').change( function(){
        purchase_payment_dues_table.ajax.reload();
    });
    }

    // Sales dues datatables
    if ($('#sales_payment_dues_table').length) {
    sales_payment_dues_table = $('#sales_payment_dues_table').DataTable({
        processing: true,
        serverSide: true,
        ordering: false,
        searching: false,
        scrollY:        "300px",
        scrollX:        true,
        scrollCollapse: true,
        fixedHeader: false,
        dom: 'tirp',
        ajax: {
            "url": '/home/sales-payment-dues',
            "data": function ( d ) {
                if ($('#sales_payment_dues_location').length > 0) {
                    d.location_id = $('#sales_payment_dues_location').val();
                }
            }
        },
        fnDrawCallback: function(oSettings) {
            __currency_convert_recursively($('#sales_payment_dues_table'));
        },
    });

    $('#sales_payment_dues_location').change( function(){
        sales_payment_dues_table.ajax.reload();
    });
    }

    // Stock expiry report table
    if ($('#stock_expiry_alert_table').length) {
    stock_expiry_alert_table = $('#stock_expiry_alert_table').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        scrollY:        "300px",
        scrollX:        true,
        scrollCollapse: true,
        fixedHeader: false,
        dom: 'tirp',
        ajax: {
            url: '/reports/stock-expiry',
            data: function(d) {
                d.exp_date_filter = $('#stock_expiry_alert_days').val();
            },
        },
        order: [[3, 'asc']],
        columns: [
            { data: 'product', name: 'p.name' },
            { data: 'location', name: 'l.name' },
            { data: 'stock_left', name: 'stock_left' },
            { data: 'exp_date', name: 'exp_date' },
        ],
        fnDrawCallback: function(oSettings) {
            __show_date_diff_for_human($('#stock_expiry_alert_table'));
            __currency_convert_recursively($('#stock_expiry_alert_table'));
        },
    });
    }

    if ($('#quotation_table').length) {
        quotation_datatable = $('#quotation_table').DataTable({
            processing: true,
            serverSide: true,
            fixedHeader:false,
            aaSorting: [[0, 'desc']],
            "ajax": {
                "url": '/sells/draft-dt?is_quotation=1',
                "data": function ( d ) {
                    if ($('#dashboard_location').length > 0) {
                        d.location_id = $('#dashboard_location').val();
                    }
                }
            },
            columnDefs: [ {
                "targets": 4,
                "orderable": false,
                "searchable": false
            } ],
            columns: [
                { data: 'transaction_date', name: 'transaction_date'  },
                { data: 'invoice_no', name: 'invoice_no'},
                { data: 'name', name: 'contacts.name'},
                { data: 'business_location', name: 'bl.name'},
                { data: 'action', name: 'action'}
            ]            
        });
    }
});

function japsFormatDelta(pct) {
    var num = parseFloat(pct);
    if (isNaN(num)) {
        return { text: '—', cls: '' };
    }
    var arrow = num >= 0 ? '↑' : '↓';
    return {
        text: arrow + ' ' + Math.abs(num) + '%',
        cls: num >= 0 ? 'is-up' : 'is-down'
    };
}

function japsApplyDelta($el, pct) {
    if (!$el || !$el.length) {
        return;
    }
    var delta = japsFormatDelta(pct);
    $el.text(delta.text).removeClass('is-up is-down');
    if (delta.cls) {
        $el.addClass(delta.cls);
    }
}

function update_statistics(start, end) {
    var location_id = '';
    if ($('#dashboard_location').length > 0) {
        location_id = $('#dashboard_location').val();
    }
    var data = { start: start, end: end, location_id: location_id };
    //get purchase details
    var loader = '<i class="fas fa-sync fa-spin fa-fw margin-bottom"></i>';
    $('.total_purchase').html(loader);
    $('.purchase_due').html(loader);
    $('.total_sell').html(loader);
    $('.invoice_due').html(loader);
    $('.total_expense').html(loader);
    $('.total_purchase_return').html(loader);
    $('.total_sell_return').html(loader);
    $('.net').html(loader);
    $('.online_orders').html(loader);
    $.ajax({
        method: 'get',
        url: '/home/get-totals',
        dataType: 'json',
        data: data,
        success: function(data) {
            //purchase details
            $('.total_purchase').html(__currency_trans_from_en(data.total_purchase, true));
            $('.purchase_due').html(__currency_trans_from_en(data.purchase_due, true));

            //sell details
            $('.total_sell').html(__currency_trans_from_en(data.total_sell, true));
            $('.japs-chart-total-sell').html(__currency_trans_from_en(data.total_sell, true));
            $('.invoice_due').html(__currency_trans_from_en(data.invoice_due, true));
            //expense details
            $('.total_expense').html(__currency_trans_from_en(data.total_expense, true));
            var total_purchase_return = data.total_purchase_return - data.total_purchase_return_paid;
            $('.total_purchase_return').html(__currency_trans_from_en(total_purchase_return, true));
            var total_sell_return_due = data.total_sell_return - data.total_sell_return_paid;
            $('.total_sell_return').html(__currency_trans_from_en(total_sell_return_due, true));
            $('.total_sr').html(__currency_trans_from_en(data.total_sell_return, true));
            $('.total_srp').html(__currency_trans_from_en(data.total_sell_return_paid, true));
            $('.total_pr').html(__currency_trans_from_en(data.total_purchase_return, true));
            $('.total_prp').html(__currency_trans_from_en(data.total_purchase_return_paid, true));
            $('.net').html(__currency_trans_from_en(data.net, true));
            var onlineOrders = typeof data.online_orders !== 'undefined' ? data.online_orders : 0;
            $('.online_orders').html(onlineOrders);

            if (data.change_pct) {
                japsApplyDelta($('[data-kpi="total_sell"]'), data.change_pct.total_sell);
                japsApplyDelta($('[data-kpi="net"]'), data.change_pct.net);
                japsApplyDelta($('[data-kpi="invoice_due"]'), data.change_pct.invoice_due);
                japsApplyDelta($('[data-kpi="total_purchase"]'), data.change_pct.total_purchase);
                japsApplyDelta($('.japs-chart-sell-growth'), data.change_pct.total_sell);
            }

            $('.japs-overview-sales-total').text(__currency_trans_from_en(data.total_sell, true));
            $('.japs-overview-target-total').text(__currency_trans_from_en(data.invoice_due, true));
            window.japsSalesGrowthPct = data.change_pct && typeof data.change_pct.total_sell === 'number'
                ? data.change_pct.total_sell
                : null;

            // assign tooltip total_sell_return 
            var lang = $('#total_srp').data('value');
            if (lang) {
                var splitlang = String(lang).split('-');
                if (splitlang.length >= 2) {
                    var newContent = "<p class='mb-0 text-muted fs-10 mt-5'>" + splitlang[0] + ": <span class=''>" + __currency_trans_from_en(data.total_sell_return, true) + "</span><br>" + splitlang[1] + ": <span class=''>" + __currency_trans_from_en(data.total_sell_return_paid, true) + "</span></p>";
                    $('#total_srp').attr('data-content', newContent);
                }
            }
            // assign tooltip total_purchase_return 
            lang = $('#total_prp').data('value');
            if (lang) {
                splitlang = String(lang).split('-');
                if (splitlang.length >= 2) {
                    newContent = "<p class='mb-0 text-muted fs-10 mt-5'>" + splitlang[0] + ": <span class=''>" + __currency_trans_from_en(data.total_purchase_return, true) + "</span><br>" + splitlang[1] + ": <span class=''>" + __currency_trans_from_en(data.total_purchase_return_paid, true) + "</span></p>";
                    $('#total_prp').attr('data-content', newContent);
                }
            }

            // Sales Overview follows dashboard date filter
            if (typeof window.japsLoadPaymentMethodMix === 'function') {
                window.japsLoadPaymentMethodMix(start, end, location_id);
            }
            // Revenue Insights bars follow dashboard date filter
            if (typeof window.japsLoadRevenueInsights === 'function') {
                window.japsLoadRevenueInsights(start, end, location_id);
            }
        },
        error: function () {
            $('.total_purchase, .purchase_due, .total_sell, .invoice_due, .total_expense, .total_purchase_return, .total_sell_return, .net').html('—');
            $('.online_orders').html('0');
        },
    });
}

window.japsLoadRevenueInsights = function(start, end, locationId) {
    if (!$('.japs-dash-chart-card--revenue').length) {
        return;
    }
    // Monthly / Yearly ranges are fixed on the server (30 days / FY).
    // start/end kept for API compatibility; location still filters.
    $.get('/home/revenue-insights', {
        location_id: locationId || ''
    }, function(data) {
        if (!data || typeof Highcharts === 'undefined') {
            return;
        }
        window.japsRevenueInsightsData = data;
        window.japsApplyRevenueInsightsData(data);
    });
};

window.japsApplyRevenueInsightsData = function(data) {
    if (!data || typeof Highcharts === 'undefined') {
        return;
    }
    var charts = (Highcharts.charts || []).filter(function(chart) {
        return chart && chart.renderTo && $(chart.renderTo).closest('.japs-dash-chart-pane').length;
    });
    if (!charts.length) {
        // Charts not ready yet — retry shortly
        clearTimeout(window.japsRevenueApplyTimer);
        window.japsRevenueApplyTimer = setTimeout(function() {
            window.japsApplyRevenueInsightsData(data);
        }, 250);
        return;
    }

    function applySeries(chart, labels, values, isYearly) {
        if (!chart || !chart.series || !chart.series[0]) {
            return;
        }
        try {
            chart.xAxis[0].setCategories(labels || [], false);
            chart.series[0].setData((values || []).map(function(v) {
                return typeof v === 'number' ? v : parseFloat(v) || 0;
            }), false);
            chart.update({
                xAxis: {
                    labels: {
                        rotation: isYearly ? 0 : -45
                    }
                }
            }, false);
            chart.reflow();
            chart.redraw();
        } catch (e) {}
        if (typeof window.japsStyleRevenueChart === 'function') {
            window.japsStyleRevenueChart(chart);
        }
    }

    charts.forEach(function(chart) {
        var $pane = $(chart.renderTo).closest('.japs-dash-chart-pane');
        var period = $pane.data('period');
        if (period === 'fy') {
            applySeries(chart, data.monthly_labels || [], data.monthly_values || [], true);
        } else {
            applySeries(chart, data.daily_labels || [], data.daily_values || [], false);
        }
    });
};

window.japsLoadPaymentMethodMix = function(start, end, locationId) {
    if (!$('#payment_method_mix_chart').length) {
        return;
    }

    $.get('/home/payment-method-mix', {
        start: start,
        end: end,
        location_id: locationId || ''
    }, function(data) {
        if (window.japsPaymentMixChart) {
            try {
                window.japsPaymentMixChart.destroy();
            } catch (e) {}
            window.japsPaymentMixChart = null;
        }

        var $center = $('#payment_method_mix_center');
        var $centerAmount = $center.find('.japs-dash-donut-center__amount');
        var $centerLabel = $center.find('.japs-dash-donut-center__label');
        var growthPct = typeof window.japsSalesGrowthPct === 'number' ? window.japsSalesGrowthPct : null;
        var topItem = (data.series || [])[0];
        var topPct = data.total > 0 && topItem ? Math.round((topItem.amount / data.total) * 1000) / 10 : 0;
        var centerPct = growthPct !== null ? (Math.round(Math.abs(growthPct) * 10) / 10) : topPct;
        var centerDefaultAmount = centerPct + '%';
        var centerDefaultLabel = $centerLabel.data('default-label') || 'Sales Growth';

        // Footer from dashboard filter totals; % is drawn inside the SVG gauge
        $('#payment_method_mix_empty').hide();
        $('.japs-dash-payment-mix').show();
        $center.hide();
        $('#payment_overview_footer').show();
        $('#payment_overview_progress').css('width', Math.min(100, Math.max(centerPct, 8)) + '%');
        var targetPct = Math.min(100, Math.max(topPct || centerPct, 12));
        $('#payment_overview_target_progress').css('width', Math.max(targetPct, 8) + '%');

        // Radial Gauge Chart (Salezy): progress-filled segmented semi-gauge
        var gaugeHost = document.getElementById('payment_method_mix_chart');
        function renderRoundedSegmentGauge() {
            var el = gaugeHost;
            if (!el) {
                return;
            }

            var w = Math.max(el.clientWidth || 320, 280);
            var h = Math.max(el.clientHeight || 280, 260);
            var cx = w / 2;
            // Keep gauge higher in the card (more top space used)
            var cy = h * 0.78;
            var rOuter = Math.min(w * 0.44, h * 0.7, 160);
            var band = Math.max(48, Math.round(rOuter * 0.34));
            var rInner = rOuter - band;
            var segmentCount = 18;
            var gapDeg = 2;
            var startDeg = -180;
            var endDeg = 0;
            var totalSweep = endDeg - startDeg;
            var segSweep = (totalSweep - gapDeg * segmentCount) / segmentCount;
            var corner = Math.min(band * 0.22, (segSweep * rOuter * Math.PI / 180) * 0.28, 9);
            var fillRatio = Math.min(1, Math.max(0, centerPct / 100));
            var filledCount = Math.max(0, Math.round(fillRatio * segmentCount));

            function pt(r, deg) {
                var rad = (deg * Math.PI) / 180;
                return {
                    x: cx + r * Math.cos(rad),
                    y: cy + r * Math.sin(rad)
                };
            }

            // Rounded radial blocks (Salezy): soft outer corners, flat inner edge
            function segmentPath(a0, a1) {
                var ang = Math.min((corner / rOuter) * (180 / Math.PI), (a1 - a0) / 2 - 0.05);
                if (!(ang > 0.12)) {
                    ang = 0;
                }
                var o0 = a0 + ang;
                var o1 = a1 - ang;
                var rMid = rOuter - (ang > 0 ? corner : 0);

                var pOS = pt(rOuter, o0);
                var pOE = pt(rOuter, o1);
                var pOR = pt(rMid, a1);
                var pIR = pt(rInner, a1);
                var pIL = pt(rInner, a0);
                var pOL = pt(rMid, a0);

                function arc(r, to, sweep) {
                    return ' A ' + r + ' ' + r + ' 0 0 ' + sweep + ' ' +
                        to.x.toFixed(2) + ' ' + to.y.toFixed(2);
                }

                var d = 'M ' + pOS.x.toFixed(2) + ' ' + pOS.y.toFixed(2);
                d += arc(rOuter, pOE, 1);
                if (ang > 0) {
                    d += arc(corner, pOR, 1);
                }
                d += ' L ' + pIR.x.toFixed(2) + ' ' + pIR.y.toFixed(2);
                d += arc(rInner, pIL, 0);
                d += ' L ' + pOL.x.toFixed(2) + ' ' + pOL.y.toFixed(2);
                if (ang > 0) {
                    d += arc(corner, pOS, 1);
                }
                d += ' Z';
                return d;
            }

            function fillColor(i, isActive) {
                if (!isActive) {
                    return '#e8eef6';
                }
                var t = filledCount <= 1 ? 0 : i / (filledCount - 1);
                var rr = Math.round(29 + (147 - 29) * t);
                var gg = Math.round(78 + (197 - 78) * t);
                var bb = Math.round(216 + (253 - 216) * t);
                return 'rgb(' + rr + ',' + gg + ',' + bb + ')';
            }

            var parts = '';
            for (var i = 0; i < segmentCount; i++) {
                var a0 = startDeg + gapDeg / 2 + i * (segSweep + gapDeg);
                var a1 = a0 + segSweep;
                parts += '<path d="' + segmentPath(a0, a1) + '" fill="' + fillColor(i, i < filledCount) + '" />';
            }

            // Center % text inside SVG so it never mismatches the gauge
            var labelY = cy - rInner * 0.22;
            var pctText = String(centerDefaultAmount).replace(/</g, '');
            var lblText = String(centerDefaultLabel).replace(/</g, '');
            parts +=
                '<text x="' + cx + '" y="' + (labelY - 6).toFixed(1) + '" text-anchor="middle" ' +
                'dominant-baseline="middle" fill="#0f172a" font-size="34" font-weight="800" ' +
                'font-family="Inter, Segoe UI, system-ui, sans-serif" letter-spacing="-0.04em">' +
                pctText + '</text>';
            parts +=
                '<text x="' + cx + '" y="' + (labelY + 22).toFixed(1) + '" text-anchor="middle" ' +
                'dominant-baseline="middle" fill="#94a3b8" font-size="12" font-weight="500" ' +
                'font-family="Inter, Segoe UI, system-ui, sans-serif">' +
                lblText + '</text>';

            el.innerHTML =
                '<svg class="japs-dash-gauge-svg" viewBox="0 0 ' + w + ' ' + h + '"' +
                ' width="100%" height="100%" preserveAspectRatio="xMidYMin meet"' +
                ' role="img" aria-label="Sales growth ' + pctText + '">' +
                parts +
                '</svg>';

            // Hide HTML center overlay — SVG text is the source of truth
            $center.hide();
        }

        renderRoundedSegmentGauge();
        window.japsPaymentMixChart = {
            destroy: function() {
                if (gaugeHost) {
                    gaugeHost.innerHTML = '';
                }
            },
            reflow: function() {
                renderRoundedSegmentGauge();
            }
        };

        (function bindPaymentMixHeaderMenu() {
            var $btn = $('#payment_method_mix_menu_btn');
            var $menu = $('#payment_overview_export_menu');
            if (!$btn.length) {
                return;
            }
            if (!$menu.length) {
                $menu = $(
                    '<div id="payment_overview_export_menu" class="japs-dash-chart-card__export-menu" hidden>' +
                        '<button type="button" data-export="print">Print chart</button>' +
                        '<button type="button" data-export="image/png">Download PNG image</button>' +
                        '<button type="button" data-export="image/jpeg">Download JPEG image</button>' +
                        '<button type="button" data-export="application/pdf">Download PDF document</button>' +
                        '<button type="button" data-export="image/svg+xml">Download SVG vector image</button>' +
                    '</div>'
                ).appendTo('.japs-dash-chart-card--payment .japs-dash-chart-card__header');
            }

            var closeMenu = function() {
                $menu.attr('hidden', true);
                $btn.attr('aria-expanded', 'false');
            };

            $btn.off('click.japsPayMixMenu').on('click.japsPayMixMenu', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var isOpen = !$menu.attr('hidden');
                if (isOpen) {
                    closeMenu();
                    return;
                }
                $menu.removeAttr('hidden');
                $btn.attr('aria-expanded', 'true');
            });

            $menu.off('click.japsPayMixMenu').on('click.japsPayMixMenu', 'button[data-export]', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var chart = window.japsPaymentMixChart;
                var type = $(this).data('export');
                closeMenu();
                if (!chart) {
                    return;
                }
                if (type === 'print') {
                    if (typeof chart.print === 'function') {
                        chart.print();
                    }
                    return;
                }
                if (typeof chart.exportChartLocal === 'function') {
                    chart.exportChartLocal({ type: type });
                } else if (typeof chart.exportChart === 'function') {
                    chart.exportChart({ type: type });
                }
            });

            $(document)
                .off('click.japsPayMixMenu')
                .on('click.japsPayMixMenu', function(e) {
                    if ($(e.target).closest('#payment_method_mix_menu_btn, #payment_overview_export_menu').length) {
                        return;
                    }
                    closeMenu();
                });
        })();
    });
};
