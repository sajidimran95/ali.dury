/* Home operations dashboard */
(function () {
    function dashRange() {
        var picker = $('#dashboard_date_filter').data('daterangepicker');
        if (picker) {
            return {
                start: picker.startDate.format('YYYY-MM-DD'),
                end: picker.endDate.format('YYYY-MM-DD')
            };
        }
        return {
            start: moment().subtract(6, 'days').format('YYYY-MM-DD'),
            end: moment().format('YYYY-MM-DD')
        };
    }

    function locationId() {
        return $('#dashboard_location').length ? ($('#dashboard_location').val() || '') : '';
    }

    function attentionIcon(key) {
        var map = {
            cart: 'fas fa-shopping-cart',
            truck: 'fas fa-truck',
            box: 'fas fa-boxes',
            alert: 'fas fa-exclamation-circle'
        };
        return map[key] || 'fas fa-circle';
    }

    function renderAttention(items) {
        var $wrap = $('#japs_ops_attention');
        if (!$wrap.length) {
            return;
        }
        if (!items || !items.length) {
            $wrap.html('<p class="japs-ops-empty">No items need attention</p>');
            return;
        }
        var html = items.map(function (item) {
            return '<a class="japs-ops-attention__row" href="' + (item.url || '#') + '">' +
                '<span class="japs-ops-attention__icon"><i class="' + attentionIcon(item.icon) + '"></i></span>' +
                '<span class="japs-ops-attention__label">' + $('<div>').text(item.label || '').html() + '</span>' +
                '<span class="japs-ops-attention__count">' + (item.count || 0) + '</span>' +
                '<i class="fas fa-chevron-right japs-ops-attention__chev"></i></a>';
        }).join('');
        $wrap.html(html);
    }

    function renderFulfillment(data) {
        if (!data) {
            return;
        }
        $('#ops_pick').text(data.picking || 0);
        $('#ops_pack').text(data.packing || 0);
        $('#ops_ship').text(data.shipment || 0);
        var by = data.by_source || {};
        ['pos', 'online', 'field'].forEach(function (src) {
            var row = by[src] || {};
            var $tr = $('tr[data-src="' + src + '"]');
            $tr.find('.c-pick').text(row.picking || 0);
            $tr.find('.c-pack').text(row.packing || 0);
            $tr.find('.c-ship').text(row.shipment || 0);
        });
    }

    function renderLowStock(rows) {
        var $tb = $('#japs_ops_low_stock tbody');
        if (!$tb.length) {
            return;
        }
        if (!rows || !rows.length) {
            $tb.html('<tr><td colspan="4" class="japs-ops-empty">—</td></tr>');
            return;
        }
        var html = rows.map(function (row) {
            var critical = parseFloat(row.stock) <= 0;
            return '<tr>' +
                '<td>' + $('<div>').text(row.product || '').html() + '</td>' +
                '<td>' + $('<div>').text(row.sku || '').html() + '</td>' +
                '<td class="' + (critical ? 'is-critical' : 'is-low') + '">' + (row.stock || 0) + '</td>' +
                '<td>' + (row.alert_quantity || 0) + '</td>' +
                '</tr>';
        }).join('');
        $tb.html(html);
    }

    function lineChart(el, labels, values, color, area) {
        if (!el || typeof Highcharts === 'undefined') {
            return;
        }
        Highcharts.chart(el, {
            chart: { type: area ? 'areaspline' : 'spline', backgroundColor: 'transparent', height: 220, spacing: [8, 8, 8, 4] },
            title: { text: null },
            credits: { enabled: false },
            legend: { enabled: false },
            xAxis: {
                categories: labels || [],
                lineColor: 'transparent',
                tickLength: 0,
                labels: { style: { color: '#64748b', fontSize: '11px' } }
            },
            yAxis: {
                title: { text: null },
                gridLineColor: '#eef2f7',
                min: 0,
                labels: { style: { color: '#94a3b8', fontSize: '11px' } }
            },
            tooltip: {
                backgroundColor: '#1e3a8a',
                borderWidth: 0,
                style: { color: '#fff', fontWeight: '600' }
            },
            plotOptions: {
                spline: { lineWidth: 2.5, marker: { radius: 3, lineWidth: 2, lineColor: '#fff' } },
                areaspline: {
                    fillOpacity: 0.18,
                    lineWidth: 2.5,
                    marker: { radius: 3, lineWidth: 2, lineColor: '#fff' }
                }
            },
            series: [{ name: ' ', data: values || [], color: color }]
        });
    }

    window.japsLoadHomeOps = function (start, end) {
        if (!$('.japs-ops').length) {
            return;
        }
        var range = start && end ? { start: start, end: end } : dashRange();
        $.ajax({
            url: '/home/ops-overview',
            method: 'GET',
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            data: {
                start: range.start,
                end: range.end,
                location_id: locationId()
            }
        }).done(function (data) {
            if (!data) {
                return;
            }
            var kpis = data.kpis || {};
            $('.awaiting_fulfillment').text(typeof kpis.awaiting_fulfillment === 'undefined' ? 0 : kpis.awaiting_fulfillment);
            $('.open_issues').text(typeof kpis.open_issues === 'undefined' ? 0 : kpis.open_issues);
            if (kpis.change_pct && typeof japsApplyDelta === 'function') {
                japsApplyDelta($('[data-kpi="awaiting_fulfillment"]'), kpis.change_pct.awaiting_fulfillment);
                japsApplyDelta($('[data-kpi="open_issues"]'), kpis.change_pct.open_issues, true);
            }
            if (kpis.prev_period_label) {
                $('.japs-ops-kpi__period').text('vs ' + kpis.prev_period_label);
            }
            var growth = (data.kpis && data.kpis.change_pct) ? data.kpis.change_pct : {};
            function chip($el, pct) {
                if (!$el.length || typeof pct === 'undefined' || pct === null) {
                    return;
                }
                var n = parseFloat(pct);
                if (isNaN(n)) {
                    return;
                }
                $el.text((n >= 0 ? '+' : '−') + ' ' + Math.abs(n).toFixed(1) + '%')
                    .toggleClass('is-up', n >= 0)
                    .toggleClass('is-down', n < 0);
            }
            chip($('#ops_orders_growth'), growth.orders);
            chip($('#ops_revenue_growth'), growth.revenue);
            renderAttention(data.attention);
            renderFulfillment(data.fulfillment);
            renderLowStock(data.low_stock);
            var trends = data.trends || {};
            function draw() {
                lineChart('japs_ops_orders_chart', trends.labels, trends.orders, '#2563eb', false);
                lineChart('japs_ops_revenue_chart', trends.labels, trends.revenue, '#3b82f6', true);
            }
            if (typeof Highcharts !== 'undefined') {
                draw();
            } else {
                setTimeout(draw, 400);
            }
        }).fail(function () {
            $('.awaiting_fulfillment, .open_issues').text('0');
        });
    };

    $(function () {
        if (!$('.japs-ops').length) {
            return;
        }

        var range = dashRange();
        window.japsLoadHomeOps(range.start, range.end);

        if ($('#recent_sales_table').length && $.fn.DataTable) {
            if ($.fn.DataTable.isDataTable('#recent_sales_table')) {
                $('#recent_sales_table').DataTable().destroy();
            }
            $('#recent_sales_table').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                paging: false,
                info: false,
                autoWidth: false,
                order: [[6, 'desc']],
                pageLength: 8,
                lengthChange: false,
                dom: 't',
                ajax: {
                    url: '/home/recent-orders',
                    data: function (d) {
                        d.location_id = locationId();
                        d.length = 8;
                    },
                    error: function () {
                        $('#recent_sales_table tbody').html(
                            '<tr><td colspan="7" class="japs-ops-empty">Could not load orders</td></tr>'
                        );
                    }
                },
                columns: [
                    { data: 'invoice_no', name: 'invoice_no' },
                    { data: 'business_location_badge', name: 'source', orderable: false },
                    { data: 'name', name: 'contacts.name' },
                    { data: 'final_total', name: 'final_total' },
                    { data: 'fulfillment_stage', name: 'shipping_status', orderable: false },
                    { data: 'payment_status', name: 'payment_status' },
                    { data: 'transaction_date', name: 'transaction_date' }
                ],
                fnDrawCallback: function () {
                    if (typeof __currency_convert_recursively === 'function') {
                        __currency_convert_recursively($('#recent_sales_table'));
                    }
                }
            });
            $('#dashboard_location').on('change', function () {
                $('#recent_sales_table').DataTable().ajax.reload();
            });
        }
    });
})();
