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

            if (typeof window.japsLoadPaymentMethodMix === 'function') {
                window.japsLoadPaymentMethodMix(start, end, location_id);
            }
        },
        error: function () {
            $('.total_purchase, .purchase_due, .total_sell, .invoice_due, .total_expense, .total_purchase_return, .total_sell_return, .net').html('—');
            $('.online_orders').html('0');
        },
    });
}

window.japsLoadPaymentMethodMix = function(start, end, locationId) {
    if (!$('#payment_method_mix_chart').length) {
        return;
    }

    $.get('/home/payment-method-mix', {
        start: start,
        end: end,
        location_id: locationId || ''
    }, function(data) {
        if (typeof Highcharts === 'undefined') {
            return;
        }

        if (window.japsPaymentMixChart) {
            window.japsPaymentMixChart.destroy();
            window.japsPaymentMixChart = null;
        }

        var $legend = $('#payment_method_mix_legend');
        var $center = $('#payment_method_mix_center');
        var $centerAmount = $center.find('.japs-dash-donut-center__amount');
        var $centerLabel = $center.find('.japs-dash-donut-center__label');
        $legend.empty();

        if (!data.has_data) {
            $('#payment_method_mix_empty').show();
            $('.japs-dash-payment-mix').hide();
            $center.hide();
            return;
        }

        var totalAmountText = __currency_trans_from_en(data.total || 0, true);
        var topItem = (data.series || [])[0];
        var topPct = data.total > 0 && topItem ? Math.round((topItem.amount / data.total) * 1000) / 10 : 0;
        var centerDefaultAmount = (Math.round(topPct * 10) / 10) + '%';
        var centerDefaultLabel = topItem ? topItem.label : ($centerLabel.data('default-label') || 'Total Sales');

        $('#payment_method_mix_empty').hide();
        $('.japs-dash-payment-mix').show();
        $centerAmount.text(centerDefaultAmount);
        $centerLabel.text(centerDefaultLabel);
        $center.show();
        $('#payment_overview_footer').show();
        $('.japs-overview-sales-total').text(totalAmountText);
        $('#payment_overview_progress').css('width', Math.min(100, Math.max(topPct, 8)) + '%');

        var colors = ['#3b82f6', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'];
        var pieData = (data.series || []).map(function(item, idx) {
            var color = colors[idx % colors.length];
            var pct = data.total > 0 ? Math.round((item.amount / data.total) * 100) : 0;
            $legend.append(
                '<li class="japs-dash-payment-legend__item">' +
                    '<span class="japs-dash-payment-legend__swatch" style="background:' + color + '"></span>' +
                    '<span class="japs-dash-payment-legend__label">' + $('<div>').text(item.label).html() + '</span>' +
                    '<span class="japs-dash-payment-legend__pct">' + pct + '%</span>' +
                '</li>'
            );
            return {
                name: item.label,
                y: item.amount,
                color: color
            };
        });

        window.japsPaymentMixChart = Highcharts.chart('payment_method_mix_chart', {
            chart: {
                type: 'pie',
                backgroundColor: 'transparent',
                height: 220,
                spacing: [8, 8, 8, 8],
                margin: [8, 8, 8, 8]
            },
            title: { text: null },
            subtitle: { text: null },
            credits: { enabled: false },
            legend: { enabled: false },
            exporting: {
                enabled: true,
                fallbackToExportServer: false,
                buttons: {
                    contextButton: {
                        enabled: false
                    }
                }
            },
            tooltip: {
                backgroundColor: '#111827',
                borderColor: '#111827',
                borderRadius: 8,
                borderWidth: 0,
                shadow: false,
                style: { color: '#fff', fontSize: '12px' },
                pointFormatter: function() {
                    var pct = data.total > 0 ? Math.round((this.y / data.total) * 100) : 0;
                    return '<b>' + this.name + '</b><br/>' +
                        __currency_trans_from_en(this.y, true) +
                        ' (' + pct + '%)';
                }
            },
            plotOptions: {
                pie: {
                    innerSize: '68%',
                    size: '88%',
                    center: ['50%', '50%'],
                    borderWidth: pieData.length > 1 ? 3 : 0,
                    borderColor: '#ffffff',
                    slicedOffset: 0,
                    dataLabels: { enabled: false },
                    showInLegend: false,
                    states: {
                        hover: { brightness: 0.04 }
                    },
                    point: {
                        events: {
                            mouseOver: function() {
                                var pct = data.total > 0 ? Math.round((this.y / data.total) * 1000) / 10 : 0;
                                $centerAmount.text(pct + '%');
                                $centerLabel.text(this.name);
                            },
                            mouseOut: function() {
                                $centerAmount.text(centerDefaultAmount);
                                $centerLabel.text(centerDefaultLabel);
                            }
                        }
                    }
                }
            },
            series: [{
                name: 'Payment',
                type: 'pie',
                data: pieData
            }]
        });

        // Align 3-line menu with Sales card header (top-right)
        (function bindPaymentMixHeaderMenu() {
            var $btn = $('#payment_method_mix_menu_btn');
            var $menu = $('#payment_method_mix_export_menu');
            if (!$btn.length) {
                return;
            }
            if (!$menu.length) {
                $menu = $(
                    '<div id="payment_method_mix_export_menu" class="japs-dash-chart-card__export-menu" hidden>' +
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
                    if ($(e.target).closest('#payment_method_mix_menu_btn, #payment_method_mix_export_menu').length) {
                        return;
                    }
                    closeMenu();
                });
        })();
    });
};
