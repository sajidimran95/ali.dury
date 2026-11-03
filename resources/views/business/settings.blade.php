@extends('layouts.app')
@section('title', __('business.business_settings'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('business.business_settings')</h1>
    <br>
    @include('layouts.partials.search_settings')
</section>

<!-- Main content -->
<section class="content business-settings-page">
{!! Form::open(['url' => action([\App\Http\Controllers\BusinessController::class, 'postBusinessSettings']), 'method' => 'post', 'id' => 'bussiness_edit_form',
           'files' => true ]) !!}
    <div class="row">
        <div class="col-xs-12">
       <!--  <pos-tab-container> -->
        {{-- <div class="col-xs-12 pos-tab-container"> --}}
        @component('components.widget', ['class' =>  'pos-tab-container'])
            <div class="col-lg-2 col-md-3 col-sm-12 col-xs-12 pos-tab-menu tw-rounded-lg">
                <div class="list-group">
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base  active">@lang('business.business')</a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('business.tax') @show_tooltip(__('tooltip.business_tax'))</a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('business.product')</a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('contact.contact')</a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('business.sale')</a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('sale.pos_sale')</a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('lang_v1.display_screen')</a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('purchase.purchases')</a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('lang_v1.payment')</a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('business.dashboard')</a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('business.system')</a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('lang_v1.prefixes')</a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('lang_v1.email_settings')</a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('lang_v1.sms_settings')</a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('lang_v1.reward_point_settings')</a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('lang_v1.modules')</a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">Ecommerce</a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('lang_v1.custom_labels')</a>
                </div>
            </div>
            <div class="col-lg-10 col-md-9 col-sm-12 col-xs-12 pos-tab">
                <!-- tab 1 start -->
                @include('business.partials.settings_business')
                <!-- tab 1 end -->
                <!-- tab 2 start -->
                @include('business.partials.settings_tax')
                <!-- tab 2 end -->
                <!-- tab 3 start -->
                @include('business.partials.settings_product')

                @include('business.partials.settings_contact')
                <!-- tab 3 end -->
                <!-- tab 4 start -->
                @include('business.partials.settings_sales')
                @include('business.partials.settings_pos')
                @include('business.partials.settings_display_pos')
                <!-- tab 4 end -->
                <!-- tab 5 start -->
                @include('business.partials.settings_purchase')

                @include('business.partials.settings_payment')
                <!-- tab 5 end -->
                <!-- tab 6 start -->
                @include('business.partials.settings_dashboard')
                <!-- tab 6 end -->
                <!-- tab 7 start -->
                @include('business.partials.settings_system')
                <!-- tab 7 end -->
                <!-- tab 8 start -->
                @include('business.partials.settings_prefixes')
                <!-- tab 8 end -->
                <!-- tab 9 start -->
                @include('business.partials.settings_email')
                <!-- tab 9 end -->
                <!-- tab 10 start -->
                @include('business.partials.settings_sms')
                <!-- tab 10 end -->
                <!-- tab 11 start -->
                @include('business.partials.settings_reward_point')
                <!-- tab 11 end -->
                <!-- tab 12 start -->
                @include('business.partials.settings_modules')
                <!-- tab 12 end -->
                @include('business.partials.settings_ecommerce')
                @include('business.partials.settings_custom_labels')
            </div>
        @endcomponent
        {{-- </div> --}}
        <!--  </pos-tab-container> -->
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12 text-center">
            <button class="tw-dw-btn tw-dw-btn-error tw-dw-btn-lg tw-text-white" type="submit">@lang('business.update_settings')</button>
        </div>
    </div>
{!! Form::close() !!}
</section>
<!-- /.content -->
@stop
@section('javascript')
<script type="text/javascript">
    __page_leave_confirmation('#bussiness_edit_form');
    $(document).on('ifToggled', '#use_superadmin_settings', function() {
        if ($('#use_superadmin_settings').is(':checked')) {
            $('#toggle_visibility').addClass('hide');
            $('.test_email_btn').addClass('hide');
        } else {
            $('#toggle_visibility').removeClass('hide');
            $('.test_email_btn').removeClass('hide');
        }
    });

    $(document).ready(function(){

    
        $('#test_email_btn').click( function() {
            var data = {
                mail_driver: $('#mail_driver').val(),
                mail_host: $('#mail_host').val(),
                mail_port: $('#mail_port').val(),
                mail_username: $('#mail_username').val(),
                mail_password: $('#mail_password').val(),
                mail_encryption: $('#mail_encryption').val(),
                mail_from_address: $('#mail_from_address').val(),
                mail_from_name: $('#mail_from_name').val(),
            };
            $.ajax({
                method: 'post',
                data: data,
                url: "{{ action([\App\Http\Controllers\BusinessController::class, 'testEmailConfiguration']) }}",
                dataType: 'json',
                success: function(result) {
                    if (result.success == true) {
                        swal({
                            text: result.msg,
                            icon: 'success'
                        });
                    } else {
                        swal({
                            text: result.msg,
                            icon: 'error'
                        });
                    }
                },
            });
        });

        $('#test_sms_btn').click( function() {
            var test_number = $('#test_number').val();
            if (test_number.trim() == '') {
                toastr.error('{{__("lang_v1.test_number_is_required")}}');
                $('#test_number').focus();

                return false;
            }

            var data = {
                url: $('#sms_settings_url').val(),
                send_to_param_name: $('#send_to_param_name').val(),
                msg_param_name: $('#msg_param_name').val(),
                request_method: $('#request_method').val(),
                param_1: $('#sms_settings_param_key1').val(),
                param_2: $('#sms_settings_param_key2').val(),
                param_3: $('#sms_settings_param_key3').val(),
                param_4: $('#sms_settings_param_key4').val(),
                param_5: $('#sms_settings_param_key5').val(),
                param_6: $('#sms_settings_param_key6').val(),
                param_7: $('#sms_settings_param_key7').val(),
                param_8: $('#sms_settings_param_key8').val(),
                param_9: $('#sms_settings_param_key9').val(),
                param_10: $('#sms_settings_param_key10').val(),

                param_val_1: $('#sms_settings_param_val1').val(),
                param_val_2: $('#sms_settings_param_val2').val(),
                param_val_3: $('#sms_settings_param_val3').val(),
                param_val_4: $('#sms_settings_param_val4').val(),
                param_val_5: $('#sms_settings_param_val5').val(),
                param_val_6: $('#sms_settings_param_val6').val(),
                param_val_7: $('#sms_settings_param_val7').val(),
                param_val_8: $('#sms_settings_param_val8').val(),
                param_val_9: $('#sms_settings_param_val9').val(),
                param_val_10: $('#sms_settings_param_val10').val(),
                test_number: test_number,

                header_1: $('#sms_settings_header_key1').val(),
                header_val_1: $('#sms_settings_header_val1').val(),
                header_2: $('#sms_settings_header_key2').val(),
                header_val_2: $('#sms_settings_header_val2').val(),
                header_3: $('#sms_settings_header_key3').val(),
                header_val_3: $('#sms_settings_header_val3').val(),
                data_parameter_type: $('#data_parameter_type').val(),
            };

            $.ajax({
                method: 'post',
                data: data,
                url: "{{ action([\App\Http\Controllers\BusinessController::class, 'testSmsConfiguration']) }}",
                dataType: 'json',
                success: function(result) {
                    if (result.success == true) {
                        swal({
                            text: result.msg,
                            icon: 'success'
                        });
                    } else {
                        swal({
                            text: result.msg,
                            icon: 'error'
                        });
                    }
                },
            });

        });

        $('select.custom_labels_products').change(function(){
            value = $(this).val();
            textarea = $(this).parents('div.custom_label_product_div').find('div.custom_label_product_dropdown');
            if(value == 'dropdown'){
                textarea.removeClass('hide');
            } else{
                textarea.addClass('hide');
            }
        })

        // Defer heavy widgets until Display Screen tab is opened (faster first paint)
        window.__businessSettingsDisplayReady = false;
        window.__initBusinessSettingsDisplayTab = function () {
            if (window.__businessSettingsDisplayReady) {
                return;
            }
            window.__businessSettingsDisplayReady = true;
            if (typeof tinymce !== 'undefined' && $('#display_screen_heading').length) {
                tinymce.init({
                    selector: 'textarea#display_screen_heading',
                    height: 250
                });
            }
            if (typeof $.fn.fileinput === 'function') {
                $('.carousel_image').each(function () {
                    if (!$(this).data('fileinput')) {
                        $(this).fileinput({
                            showUpload: true,
                            showPreview: true,
                            browseLabel: LANG.file_browse_label,
                            removeLabel: LANG.remove,
                        });
                    }
                });
            }
        };

        $('#btn_clear_application_cache').click(function() {
            var $btn = $(this);
            $.ajax({
                method: 'POST',
                url: '{{ route('business.clearCache') }}',
                dataType: 'json',
                data: {
                    _token: '{{ csrf_token() }}',
                },
                beforeSend: function() {
                    $btn.prop('disabled', true);
                },
                success: function(result) {
                    if (result.success == 1) {
                        toastr.success(result.msg);
                    } else {
                        toastr.error(result.msg || '@lang('messages.something_went_wrong')');
                    }
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON && xhr.responseJSON.message
                        ? xhr.responseJSON.message
                        : '@lang('messages.something_went_wrong')';
                    toastr.error(msg);
                },
                complete: function() {
                    $btn.prop('disabled', false);
                },
            });
        });

        if ($('#db_update_box').length) {
            var dbUpdateName = '';

            function dbUpdateRender(status) {
                var pending = status.pending || [];
                if (status.database) {
                    dbUpdateName = status.database;
                    $('#db_update_db_name').text(status.database + (status.host ? ' @ ' + status.host : ''));
                }
                $('#db_update_pending_count').text(pending.length)
                    .toggleClass('label-danger', pending.length > 0)
                    .toggleClass('label-success', pending.length === 0)
                    .removeClass('label-default');
                $('#db_update_ran_count').text(status.ran_count);
                var $list = $('#db_update_pending_list').empty();
                pending.forEach(function (m) {
                    $list.append($('<li>').append($('<code>').text(m)));
                });
                $('#db_update_pending_wrap').toggleClass('hide', pending.length === 0);
                $('#db_update_up_to_date').toggleClass('hide', pending.length !== 0);
                $('#db_update_confirm_backup').prop('checked', false);
                $('#btn_db_update_run').prop('disabled', true);
            }

            function dbUpdateLoad() {
                var $icon = $('#btn_db_update_refresh i').addClass('fa-spin');
                $.getJSON('{{ route('system.db_update.status') }}', dbUpdateRender)
                    .fail(function () { toastr.error('@lang('messages.something_went_wrong')'); })
                    .always(function () { $icon.removeClass('fa-spin'); });
            }

            dbUpdateLoad();
            $('#btn_db_update_refresh').click(dbUpdateLoad);

            $('#db_update_confirm_backup').change(function () {
                $('#btn_db_update_run').prop('disabled', !this.checked);
            });

            $('#btn_db_update_run').click(function () {
                if (!$('#db_update_confirm_backup').is(':checked')) {
                    toastr.error(@json(__('lang_v1.db_update_confirm_required')));
                    return;
                }
                var text = @json(__('lang_v1.db_update_confirm_text'))
                    .replace(':count', $('#db_update_pending_list li').length)
                    .replace(':db', dbUpdateName);

                swal({
                    title: @json(__('lang_v1.db_update_confirm_title')),
                    text: text,
                    icon: 'warning',
                    buttons: true,
                    dangerMode: true,
                }).then(function (ok) {
                    if (!ok) return;
                    var $btn = $('#btn_db_update_run').prop('disabled', true);
                    $('#db_update_running').removeClass('hide');
                    $.ajax({
                        method: 'POST',
                        url: '{{ route('system.db_update.run') }}',
                        dataType: 'json',
                        timeout: 0,
                        data: { _token: '{{ csrf_token() }}', confirm_backup: 1 },
                        success: function (result) {
                            result.success ? toastr.success(result.msg) : toastr.error(result.msg);
                            if (result.output) {
                                $('#db_update_output').text(result.output);
                                $('#db_update_output_wrap').removeClass('hide');
                            }
                            if (result.status) {
                                dbUpdateRender(result.status);
                            }
                        },
                        error: function (xhr) {
                            toastr.error(@json(__('lang_v1.db_update_failed')));
                            $('#db_update_output').text(xhr.responseText ? xhr.responseText.substring(0, 4000) : xhr.statusText);
                            $('#db_update_output_wrap').removeClass('hide');
                            $btn.prop('disabled', !$('#db_update_confirm_backup').is(':checked'));
                        },
                        complete: function () {
                            $('#db_update_running').addClass('hide');
                        },
                    });
                });
            });
        }

        // ---------------- Recycle Bin ----------------
        var recycleTables = {};
        var recycleUrls = {
            products: '{{ url('recycle-bin/products') }}',
            sales: '{{ url('recycle-bin/sales') }}',
        };

        function refreshRecycleCounts() {
            $.get('{{ route('recycle-bin.counts') }}', function(res) {
                $('#recycle_count_products').text(res.products);
                $('#recycle_count_sales').text(res.sales);
            });
        }

        function initRecycleTable(type) {
            if (recycleTables[type]) {
                recycleTables[type].columns.adjust();
                return;
            }
            var columns = type === 'products'
                ? [
                    { data: 'name', name: 'products.name' },
                    { data: 'sku', name: 'products.sku' },
                    { data: 'sales_count', name: 'sales_count', searchable: false },
                    { data: 'deleted_at', name: 'products.deleted_at', searchable: false },
                    { data: 'deleted_by_name', name: 'deleted_by_name', orderable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ]
                : [
                    { data: 'invoice_no', name: 'deleted_sales.invoice_no' },
                    { data: 'type', name: 'deleted_sales.type', searchable: false },
                    { data: 'customer', name: 'customer', orderable: false },
                    { data: 'final_total', name: 'deleted_sales.final_total', searchable: false },
                    { data: 'transaction_date', name: 'deleted_sales.transaction_date', searchable: false },
                    { data: 'created_at', name: 'deleted_sales.created_at', searchable: false },
                    { data: 'deleted_by_name', name: 'deleted_by_name', orderable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ];
            recycleTables[type] = $('#recycle_' + type + '_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: recycleUrls[type],
                order: [[type === 'products' ? 3 : 5, 'desc']],
                columns: columns,
                fnDrawCallback: function() {
                    __currency_convert_recursively($('#recycle_' + type + '_table'));
                },
            });
        }

        function reloadRecycle() {
            $.each(recycleTables, function(k, t) { t.ajax.reload(null, false); });
            refreshRecycleCounts();
        }

        function recycleAction(options) {
            swal({
                title: LANG.sure,
                text: options.text,
                icon: 'warning',
                buttons: true,
                dangerMode: options.danger,
            }).then(function(confirmed) {
                if (!confirmed) {
                    return;
                }
                $.ajax({
                    method: options.method,
                    url: options.url,
                    dataType: 'json',
                    data: $.extend({ _token: '{{ csrf_token() }}' }, options.data || {}),
                    success: function(result) {
                        if (result.success) {
                            toastr.success(result.msg);
                        } else {
                            toastr.error(result.msg);
                        }
                        reloadRecycle();
                    },
                    error: function() {
                        toastr.error('@lang('messages.something_went_wrong')');
                    },
                });
            });
        }

        if ($('#recycle_bin_section').length) {
            refreshRecycleCounts();
            // Tables are created lazily when the System tab / sub-tab becomes visible
            $('.pos-tab-menu .list-group-item').on('click', function() {
                setTimeout(function() {
                    if ($('#recycle_bin_section').is(':visible')) {
                        initRecycleTable($('#recycle_bin_section .nav-tabs li.active a').data('recycle-type'));
                    }
                }, 50);
            });
            $('#recycle_bin_section a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
                initRecycleTable($(e.target).data('recycle-type'));
            });
            if ($('#recycle_bin_section').is(':visible')) {
                initRecycleTable('products');
            }

            $(document).on('click', '#recycle_bin_section .recycle-restore', function() {
                var type = $(this).data('type'), id = $(this).data('id');
                recycleAction({
                    text: '@lang('lang_v1.recycle_confirm_restore')',
                    danger: false,
                    method: 'POST',
                    url: '{{ url('recycle-bin') }}/' + type + '/' + id + '/restore',
                });
            });

            $(document).on('click', '#recycle_bin_section .recycle-force-delete', function() {
                var type = $(this).data('type'), id = $(this).data('id');
                recycleAction({
                    text: '@lang('lang_v1.recycle_confirm_force_delete')',
                    danger: true,
                    method: 'DELETE',
                    url: '{{ url('recycle-bin') }}/' + type + '/' + id,
                });
            });

            $(document).on('click', '#recycle_bin_section .recycle-empty', function() {
                recycleAction({
                    text: '@lang('lang_v1.recycle_confirm_empty')',
                    danger: true,
                    method: 'POST',
                    url: '{{ route('recycle-bin.empty') }}',
                    data: { type: $(this).data('type') },
                });
            });
        }

        $('#btn_reset_database_fresh_pos').click(function() {
            var phrase = $('#fresh_pos_reset_phrase').val();
            if (phrase !== 'RESET') {
                swal('@lang('lang_v1.fresh_pos_reset_phrase_invalid')', '', 'error');
                return;
            }

            swal({
                title: LANG.sure,
                text: '@lang('lang_v1.fresh_pos_reset_confirm')',
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then(function(confirmed) {
                if (!confirmed) {
                    return;
                }

                $.ajax({
                    method: 'POST',
                    url: '{{ route('business.resetDatabase') }}',
                    dataType: 'json',
                    data: {
                        confirm_phrase: phrase,
                        _token: '{{ csrf_token() }}',
                    },
                    beforeSend: function() {
                        $('#btn_reset_database_fresh_pos').prop('disabled', true);
                    },
                    success: function(result) {
                        if (result.success == 1) {
                            swal({
                                title: result.msg,
                                icon: 'success',
                            }).then(function() {
                                window.location.href = '{{ action([\App\Http\Controllers\HomeController::class, 'index']) }}';
                            });
                        } else {
                            swal(result.msg, '', 'error');
                        }
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON && xhr.responseJSON.message
                            ? xhr.responseJSON.message
                            : '@lang('messages.something_went_wrong')';
                        swal(msg, '', 'error');
                    },
                    complete: function() {
                        $('#btn_reset_database_fresh_pos').prop('disabled', false);
                    },
                });
            });
        });
    });
</script>
@endsection