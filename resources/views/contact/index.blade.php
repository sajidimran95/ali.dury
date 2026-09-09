@extends('layouts.app')
@section('title', __('lang_v1.' . $type . 's'))
@php
    $api_key = env('GOOGLE_MAP_API_KEY');
@endphp
@section('css')
    <link rel="stylesheet" href="{{ asset('css/product-list-metrics.css?v=' . ($asset_v ?? '') . '&plm=8') }}">
    @if (!empty($api_key))
        @include('contact.partials.google_map_styles')
    @endif
@endsection
@section('content')
    @if ($type == 'customer')
        @include('contact.partials.customer_index')
    @else
    <!-- Content Header (Page header) -->
    <section class="content-header product-list-header">
        <div class="product-list-header__row">
            <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black product-list-header__title">
                @lang('lang_v1.' . $type . 's')
                <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">@lang('contact.manage_your_contact', ['contacts' => __('lang_v1.' . $type . 's')])</small>
            </h1>
            <div class="product-list-header__actions">
                @if (auth()->user()->can('supplier.create') || auth()->user()->can('supplier.view_own'))
                    <a class="product-list-header__btn product-list-header__btn--primary btn-modal"
                        data-href="{{ action([\App\Http\Controllers\ContactController::class, 'create'], ['type' => $type]) }}"
                        data-container="#global_contact_modal"
                        href="javascript:void(0);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 5l0 14" />
                            <path d="M5 12l14 0" />
                        </svg>
                        @lang('messages.add')
                    </a>
                @endif
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        @if (!empty($supplier_stats))
            @include('contact.partials.supplier_list_metrics')
        @endif
        @component('components.filters', ['title' => __('report.filters')])
            <div class="col-md-3">
                <div class="form-group">
                    <label>
                        {!! Form::checkbox('has_purchase_due', 1, false, ['class' => 'input-icheck', 'id' => 'has_purchase_due']) !!} <strong>@lang('report.purchase_due')</strong>
                    </label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>
                        {!! Form::checkbox('has_purchase_return', 1, false, ['class' => 'input-icheck', 'id' => 'has_purchase_return']) !!} <strong>@lang('lang_v1.purchase_return')</strong>
                    </label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>
                        {!! Form::checkbox('has_advance_balance', 1, false, ['class' => 'input-icheck', 'id' => 'has_advance_balance']) !!} <strong>@lang('lang_v1.advance_balance')</strong>
                    </label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>
                        {!! Form::checkbox('has_opening_balance', 1, false, ['class' => 'input-icheck', 'id' => 'has_opening_balance']) !!} <strong>@lang('lang_v1.opening_balance')</strong>
                    </label>
                </div>
            </div>

            @if (config('constants.enable_contact_assign') === true)
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('assigned_to', __('lang_v1.assigned_to') . ':') !!}
                        {!! Form::select('assigned_to', $users, null, ['class' => 'form-control select2', 'style' => 'width:100%']) !!}
                    </div>
                </div>
            @endif

            <div class="col-md-3">
                <div class="form-group">
                    <label for="status_filter">@lang('sale.status'):</label>
                    {!! Form::select(
                        'status_filter',
                        ['active' => __('business.is_active'), 'inactive' => __('lang_v1.inactive')],
                        null,
                        ['class' => 'form-control', 'id' => 'status_filter', 'placeholder' => __('lang_v1.none')],
                    ) !!}
                </div>
            </div>
        @endcomponent
        <input type="hidden" value="{{ $type }}" id="contact_type">
        @component('components.widget', [
            'class' => 'box-primary',
            'title' => __('contact.all_your_contact', ['contacts' => __('lang_v1.' . $type . 's')]),
        ])
            @if (auth()->user()->can('supplier.view') || auth()->user()->can('supplier.view_own'))
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="contact_table">
                        <thead>
                            <tr>
                                <th class="tw-w-full not-export">@lang('messages.action')</th>
                                <th>@lang('lang_v1.contact_id')</th>
                                <th>@lang('business.business_name')</th>
                                <th>@lang('contact.name')</th>
                                <th>@lang('business.email')</th>
                                <th>@lang('contact.tax_no')</th>
                                <th>@lang('contact.pay_term')</th>
                                <th>@lang('account.opening_balance')</th>
                                <th>@lang('lang_v1.advance_balance')</th>
                                <th>@lang('lang_v1.added_on')</th>
                                <th>@lang('business.address')</th>
                                <th>@lang('contact.mobile')</th>
                                <th>@lang('contact.total_purchase_due')</th>
                                <th>@lang('lang_v1.total_purchase_return_due')</th>
                                @php
                                    $custom_labels = json_decode(session('business.custom_labels'), true);
                                @endphp
                                <th>
                                    {{ $custom_labels['contact']['custom_field_1'] ?? __('lang_v1.contact_custom_field1') }}
                                </th>
                                <th>
                                    {{ $custom_labels['contact']['custom_field_2'] ?? __('lang_v1.contact_custom_field2') }}
                                </th>
                                <th>
                                    {{ $custom_labels['contact']['custom_field_3'] ?? __('lang_v1.contact_custom_field3') }}
                                </th>
                                <th>
                                    {{ $custom_labels['contact']['custom_field_4'] ?? __('lang_v1.contact_custom_field4') }}
                                </th>
                                <th>
                                    {{ $custom_labels['contact']['custom_field_5'] ?? __('lang_v1.custom_field', ['number' => 5]) }}
                                </th>
                                <th>
                                    {{ $custom_labels['contact']['custom_field_6'] ?? __('lang_v1.custom_field', ['number' => 6]) }}
                                </th>
                                <th>
                                    {{ $custom_labels['contact']['custom_field_7'] ?? __('lang_v1.custom_field', ['number' => 7]) }}
                                </th>
                                <th>
                                    {{ $custom_labels['contact']['custom_field_8'] ?? __('lang_v1.custom_field', ['number' => 8]) }}
                                </th>
                                <th>
                                    {{ $custom_labels['contact']['custom_field_9'] ?? __('lang_v1.custom_field', ['number' => 9]) }}
                                </th>
                                <th>
                                    {{ $custom_labels['contact']['custom_field_10'] ?? __('lang_v1.custom_field', ['number' => 10]) }}
                                </th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr class="bg-gray font-17 text-center footer-total">
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td colspan="6">
                                    <strong>
                                        @lang('sale.total'):
                                    </strong>
                                </td>
                                <td class="footer_contact_due"></td>
                                <td class="footer_contact_return_due"></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        @endcomponent

        <div class="modal fade pay_contact_due_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

    </section>
    @endif
    <!-- /.content -->
@stop
@section('javascript')
    @if ($type == 'customer')
        <script>
            (function () {
                function wireCustomerUi() {
                    if (typeof contact_table === 'undefined' || !contact_table) {
                        return setTimeout(wireCustomerUi, 50);
                    }

                    // Keep a compact subset for optional collapse; default shows ALL columns
                    var rewardEnabled = $('#rp_col').length > 0;
                    var hideIdx = [1, 2, 4, 5, 6, 7, 8, 9, 10];
                    if (rewardEnabled) {
                        hideIdx.push(11);
                    }
                    var afterPrimary = rewardEnabled ? 16 : 15;
                    hideIdx.push(afterPrimary);
                    for (var c = 1; c <= 10; c++) {
                        hideIdx.push(afterPrimary + c);
                    }

                    // Columns are already visible by default — avoid a second draw/adjust
                    // that shifts the table layout after first load.

                    if (typeof __currency_convert_recursively === 'function') {
                        __currency_convert_recursively($('.customers-page').parent());
                    }

                    var $search = $('#customer_quick_search');
                    var searchTimer = null;
                    $search.on('keyup input', function () {
                        var val = $(this).val();
                        clearTimeout(searchTimer);
                        searchTimer = setTimeout(function () {
                            contact_table.search(val).draw();
                        }, 250);
                    });

                    $('#customer_filters_toggle').on('click', function () {
                        $('#customer_filters_panel').toggleClass('is-open');
                    });

                    // Toggle: All columns (default) <-> Compact view
                    var showingAll = true;
                    $('#customer_toggle_columns').addClass('active');
                    $('#customer_toggle_columns').on('click', function () {
                        showingAll = !showingAll;
                        if (showingAll) {
                            contact_table.columns().every(function () {
                                this.visible(true, false);
                            });
                        } else {
                            contact_table.columns().every(function () {
                                this.visible(hideIdx.indexOf(this.index()) === -1, false);
                            });
                            hideIdx.forEach(function (i) {
                                try { contact_table.column(i).visible(false, false); } catch (e) {}
                            });
                        }
                        contact_table.columns.adjust().draw(false);
                        $(this).toggleClass('active', showingAll);
                    });

                    $('#customer_export_btn').on('click', function () {
                        var $btn = $(this);
                        $btn.prop('disabled', true);
                        var oldLen = contact_table.page.len();
                        var wasHidden = [];
                        contact_table.columns().every(function () {
                            if (!this.visible()) {
                                wasHidden.push(this.index());
                                this.visible(true, false);
                            }
                        });

                        contact_table.one('draw', function () {
                            var csv = [];
                            var headers = [];
                            $('#contact_table thead th').each(function () {
                                headers.push('"' + $(this).text().replace(/"/g, '""').trim() + '"');
                            });
                            csv.push(headers.join(','));

                            contact_table.rows({ search: 'applied' }).every(function () {
                                var rowNode = this.node();
                                if (!rowNode) return;
                                var cols = [];
                                $(rowNode).find('td').each(function () {
                                    cols.push('"' + $(this).text().replace(/\s+/g, ' ').replace(/"/g, '""').trim() + '"');
                                });
                                csv.push(cols.join(','));
                            });

                            var blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
                            var link = document.createElement('a');
                            link.href = URL.createObjectURL(blob);
                            link.download = 'customers.csv';
                            link.click();
                            URL.revokeObjectURL(link.href);

                            wasHidden.forEach(function (i) {
                                try { contact_table.column(i).visible(false, false); } catch (e) {}
                            });
                            contact_table.page.len(oldLen).draw(false);
                            $btn.prop('disabled', false);
                        });

                        contact_table.page.len(-1).draw();
                    });

                    $('#contact_table tbody').on('click', 'tr', function (e) {
                        if ($(e.target).closest('a, button, .btn-group, .dropdown-menu, input, select, label').length) {
                            return;
                        }
                        var data = contact_table.row(this).data();
                        if (!data) return;
                        var id = data.id;
                        if (!id && data.action) {
                            var m = String(data.action).match(/contacts\/(\d+)/);
                            if (m) id = m[1];
                        }
                        if (!id) return;
                        window.__custOptId = String(id);
                        var name = data.supplier_business_name || data.name || data.text || '';
                        if (typeof name === 'object' && name !== null) name = name.display || '';
                        window.__custOptName = $('<div>').html(name).text().replace(/\s+/g, ' ').trim();
                        window.__posCustAddress = (data.address || data.shipping_address || '').toString().replace(/<[^>]+>/g, ' ').trim();
                        $('#posCustOptName').text(window.__custOptName || '');
                        $('#posCustOptMenu').show();
                        $('#posCustOptInvoices').attr('hidden', true);
                        var $m = $('#pos_customer_options_modal');
                        if ($m.length) {
                            $m.appendTo('body');
                            $m.modal('show');
                        }
                    });

                    contact_table.on('draw', function () {
                        var info = contact_table.page.info();
                        $('#customer_shown_count').text(info.recordsDisplay);
                        $('#customer_total_count').text(info.recordsTotal);
                        if (typeof __currency_convert_recursively === 'function') {
                            __currency_convert_recursively($('.customers-page'));
                        }
                    });
                }

                $(document).ready(wireCustomerUi);

                $(document).on('click', '#posCustOptMenu [data-cust-act]', function () {
                    if (typeof posCurrentCustomerId === 'function' && $('select#customer_id').length) {
                        return;
                    }
                    var act = $(this).data('cust-act');
                    var id = window.__custOptId;
                    if (!id) return;
                    if (act === 'order') {
                        window.location.href = '/pos/create?customer_id=' + id;
                        return;
                    }
                    if (act === 'payment') {
                        $('#pos_customer_options_modal').modal('hide');
                        var $a = $('<a class="pay_sale_due" href="/payments/pay-contact-due/' + id + '?type=sell"></a>');
                        $('body').append($a);
                        $a.trigger('click');
                        $a.remove();
                        return;
                    }
                    if (act === 'invoices' || act === 'credit') {
                        $('#posCustOptMenu').hide();
                        $('#posCustOptInvoices').removeAttr('hidden');
                        $('#posCustOptInvoiceHint').text(act === 'credit' ? 'Select an invoice to create a credit' : 'This customer invoices');
                        var $list = $('#posCustOptInvoiceList').html('<div class="pos-cust-opt-hint">Loading…</div>');
                        $.getJSON('/contacts/purchase-history/' + id, { limit: 25 }, function (res) {
                            var rows = (res && res.history) || [];
                            if (!rows.length) {
                                $list.html('<div class="pos-cust-opt-hint">No invoices for this customer.</div>');
                                return;
                            }
                            var html = '';
                            rows.forEach(function (sale) {
                                var label = (sale.invoice_no || '#' + sale.id);
                                html += '<button type="button" class="pos-cust-opt-inv" data-sale-id="' + sale.id + '" data-mode="' + (act === 'credit' ? 'credit' : 'view') + '"><span>' + label + '</span><i class="fas fa-chevron-right"></i></button>';
                            });
                            $list.html(html);
                        }).fail(function () {
                            $list.html('<div class="pos-cust-opt-hint">Could not load invoices.</div>');
                        });
                        return;
                    }
                    if (act === 'history') {
                        $('#pos_customer_options_modal').modal('hide');
                        window.location.href = '/contacts/' + id + '?view=ledger';
                        return;
                    }
                    if (act === 'details') {
                        $('#pos_customer_options_modal').modal('hide');
                        window.location.href = '/contacts/' + id;
                        return;
                    }
                    if (act === 'directions') {
                        var addr = window.__posCustAddress || '';
                        $('#pos_customer_options_modal').modal('hide');
                        if (!addr) {
                            if (typeof toastr !== 'undefined') toastr.warning('No address on this customer');
                            return;
                        }
                        window.open('https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(addr), '_blank');
                    }
                });
                $(document).on('click', '#posCustOptInvoiceList .pos-cust-opt-inv', function () {
                    if (typeof posCurrentCustomerId === 'function' && $('select#customer_id').length) {
                        return;
                    }
                    var saleId = $(this).data('sale-id');
                    var mode = $(this).data('mode');
                    $('#pos_customer_options_modal').modal('hide');
                    if (mode === 'credit') {
                        window.location.href = '/sell-return/add/' + saleId;
                        return;
                    }
                    window.location.href = '/sells/' + saleId;
                });
            })();
        </script>
    @else
        <script>
            $(document).ready(function () {
                if (typeof __currency_convert_recursively === 'function') {
                    __currency_convert_recursively($('#supplier_list_metrics'));
                }
            });
        </script>
    @endif
    @if (!empty($api_key))
        <script>
            function initAutocomplete() {
                var map = new google.maps.Map(document.getElementById('map'), {
                    center: {
                        lat: -33.8688,
                        lng: 151.2195
                    },
                    zoom: 10,
                    mapTypeId: 'roadmap'
                });

                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        initialLocation = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
                        map.setCenter(initialLocation);
                    });
                }

                var input = document.getElementById('shipping_address');
                var searchBox = new google.maps.places.SearchBox(input);
                map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

                map.addListener('bounds_changed', function() {
                    searchBox.setBounds(map.getBounds());
                });

                var markers = [];
                searchBox.addListener('places_changed', function() {
                    var places = searchBox.getPlaces();

                    if (places.length == 0) {
                        return;
                    }

                    markers.forEach(function(marker) {
                        marker.setMap(null);
                    });
                    markers = [];

                    var bounds = new google.maps.LatLngBounds();
                    places.forEach(function(place) {
                        if (!place.geometry) {
                            console.log("Returned place contains no geometry");
                            return;
                        }
                        var icon = {
                            url: place.icon,
                            size: new google.maps.Size(71, 71),
                            origin: new google.maps.Point(0, 0),
                            anchor: new google.maps.Point(17, 34),
                            scaledSize: new google.maps.Size(25, 25)
                        };

                        markers.push(new google.maps.Marker({
                            map: map,
                            icon: icon,
                            title: place.name,
                            position: place.geometry.location
                        }));

                        var lat_long = [place.geometry.location.lat(), place.geometry.location.lng()]
                        $('#position').val(lat_long);

                        if (place.geometry.viewport) {
                            bounds.union(place.geometry.viewport);
                        } else {
                            bounds.extend(place.geometry.location);
                        }
                    });
                    map.fitBounds(bounds);
                });
            }
        </script>
        <script src="https://maps.googleapis.com/maps/api/js?key={{ $api_key }}&libraries=places" async defer></script>
        <script type="text/javascript">
            $(document).on('shown.bs.modal', '#global_contact_modal', function(e) {
                initAutocomplete();
            });
        </script>
    @endif
@endsection
