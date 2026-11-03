<div class="pos-tab-content">
     <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('theme_color', __('lang_v1.theme_color')); !!}
                {!! Form::select('theme_color', $theme_colors,   $business->theme_color, 
                    ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'style' => 'width: 100%;']); !!}
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                @php
                    $page_entries = [25 => 25, 50 => 50, 100 => 100, 200 => 200];
                @endphp
                {!! Form::label('default_datatable_page_entries', __('lang_v1.default_datatable_page_entries')); !!}
                {!! Form::select('common_settings[default_datatable_page_entries]', $page_entries, !empty($common_settings['default_datatable_page_entries']) ? $common_settings['default_datatable_page_entries'] : 25 , 
                    ['class' => 'form-control select2', 'style' => 'width: 100%;', 'id' => 'default_datatable_page_entries']); !!}
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                  <label>
                    {!! Form::checkbox('enable_tooltip', 1, $business->enable_tooltip , 
                    [ 'class' => 'input-icheck']); !!} {{ __( 'business.show_help_text' ) }}
                  </label>
                </div>
            </div>
        </div>
    </div>

    <div class="row" style="margin-top: 20px;">
        <div class="col-sm-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('lang_v1.clear_cache')</h3>
                </div>
                <div class="box-body">
                    <p class="text-muted">@lang('lang_v1.clear_cache_help')</p>
                    <button type="button" class="btn btn-primary" id="btn_clear_application_cache">
                        <i class="fas fa-sync-alt"></i> @lang('lang_v1.clear_cache')
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if(auth()->user()->can('superadmin') || auth()->user()->hasRole('Admin#'.auth()->user()->business_id))
    <div class="row" style="margin-top: 20px;">
        <div class="col-sm-12">
            <div class="box box-warning" id="db_update_box">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('lang_v1.db_update')</h3>
                </div>
                <div class="box-body">
                    <p class="text-muted">@lang('lang_v1.db_update_help')</p>
                    <p>
                        <strong>@lang('lang_v1.db_connected_to'):</strong> <code id="db_update_db_name">…</code>
                        &nbsp;|&nbsp; <strong>@lang('lang_v1.db_pending_updates'):</strong> <span id="db_update_pending_count" class="label label-default">…</span>
                        &nbsp;|&nbsp; <strong>@lang('lang_v1.db_applied_updates'):</strong> <span id="db_update_ran_count">…</span>
                        <button type="button" class="btn btn-default btn-xs" id="btn_db_update_refresh" style="margin-left:8px;">
                            <i class="fas fa-sync-alt"></i> @lang('lang_v1.refresh_status')
                        </button>
                    </p>

                    <div id="db_update_up_to_date" class="text-success hide">
                        <i class="fa fa-check-circle"></i> @lang('lang_v1.db_up_to_date')
                    </div>

                    <div id="db_update_pending_wrap" class="hide">
                        <strong>@lang('lang_v1.db_pending_list')</strong>
                        <ul id="db_update_pending_list" style="max-height:200px;overflow:auto;margin:6px 0 12px;padding-left:20px;"></ul>

                        <p class="text-warning" style="margin-bottom:4px;">
                            <i class="fa fa-exclamation-triangle"></i> <strong>@lang('lang_v1.db_backup_first')</strong>
                        </p>
                        <p class="text-muted"><small>@lang('lang_v1.db_backup_cpanel_help')</small></p>

                        <div class="checkbox">
                            <label>
                                <input type="checkbox" id="db_update_confirm_backup"> @lang('lang_v1.db_confirm_backup_label')
                            </label>
                        </div>

                        <button type="button" class="btn btn-warning" id="btn_db_update_run" disabled>
                            <i class="fa fa-database"></i> @lang('lang_v1.db_run_update')
                        </button>
                        <span id="db_update_running" class="text-muted hide" style="margin-left:10px;">
                            <i class="fa fa-spinner fa-spin"></i> @lang('lang_v1.db_update_running')
                        </span>
                    </div>

                    <div id="db_update_output_wrap" class="hide" style="margin-top:12px;">
                        <strong>@lang('lang_v1.db_update_output')</strong>
                        <pre id="db_update_output" style="max-height:300px;overflow:auto;background:#111827;color:#e5e7eb;border-radius:6px;padding:10px;white-space:pre-wrap;"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row" style="margin-top: 20px;" id="recycle_bin_section">
        <div class="col-sm-12">
            <div class="box box-warning">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-recycle"></i> @lang('lang_v1.recycle_bin')</h3>
                </div>
                <div class="box-body">
                    <p class="text-muted">@lang('lang_v1.recycle_bin_help')</p>
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#recycle_tab_products" data-toggle="tab" data-recycle-type="products">@lang('lang_v1.deleted_products') <span class="badge" id="recycle_count_products">0</span></a></li>
                        <li><a href="#recycle_tab_sales" data-toggle="tab" data-recycle-type="sales">@lang('lang_v1.deleted_sales') <span class="badge" id="recycle_count_sales">0</span></a></li>
                    </ul>
                    <div class="tab-content" style="padding-top: 12px;">
                        <div class="tab-pane active" id="recycle_tab_products">
                            <div class="text-right" style="margin-bottom: 8px;">
                                <button type="button" class="btn btn-sm btn-danger recycle-empty" data-type="products"><i class="fa fa-trash"></i> @lang('lang_v1.empty_deleted_products')</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="recycle_products_table" style="width:100%;">
                                    <thead>
                                        <tr>
                                            <th>@lang('sale.product')</th>
                                            <th>@lang('product.sku')</th>
                                            <th>@lang('lang_v1.used_in_invoices')</th>
                                            <th>@lang('lang_v1.deleted_on')</th>
                                            <th>@lang('lang_v1.deleted_by')</th>
                                            <th>@lang('messages.action')</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane" id="recycle_tab_sales">
                            <div class="text-right" style="margin-bottom: 8px;">
                                <button type="button" class="btn btn-sm btn-danger recycle-empty" data-type="sales"><i class="fa fa-trash"></i> @lang('lang_v1.empty_deleted_sales')</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="recycle_sales_table" style="width:100%;">
                                    <thead>
                                        <tr>
                                            <th>@lang('sale.invoice_no')</th>
                                            <th>@lang('lang_v1.type')</th>
                                            <th>@lang('contact.customer')</th>
                                            <th>@lang('sale.total_amount')</th>
                                            <th>@lang('messages.date')</th>
                                            <th>@lang('lang_v1.deleted_on')</th>
                                            <th>@lang('lang_v1.deleted_by')</th>
                                            <th>@lang('messages.action')</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <button type="button" class="btn btn-danger recycle-empty" data-type="all"><i class="fa fa-recycle"></i> @lang('lang_v1.empty_recycle_bin')</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row" style="margin-top: 20px;">
        <div class="col-sm-12">
            <div class="box box-danger">
                <div class="box-header with-border">
                    <h3 class="box-title">@lang('lang_v1.fresh_pos_reset')</h3>
                </div>
                <div class="box-body">
                    <p class="text-danger">@lang('lang_v1.fresh_pos_reset_help')</p>
                    <ul class="text-muted">
                        <li>@lang('lang_v1.fresh_pos_reset_keeps_location')</li>
                        <li>@lang('lang_v1.fresh_pos_reset_keeps_admin')</li>
                        <li>@lang('lang_v1.fresh_pos_reset_keeps_settings')</li>
                        <li>@lang('lang_v1.fresh_pos_reset_keeps_msa')</li>
                        <li>@lang('lang_v1.fresh_pos_reset_deletes_data')</li>
                    </ul>
                    <div class="form-group" style="max-width: 320px;">
                        <label for="fresh_pos_reset_phrase">@lang('lang_v1.fresh_pos_reset_type_reset')</label>
                        <input type="text" id="fresh_pos_reset_phrase" class="form-control" placeholder="RESET" autocomplete="off">
                    </div>
                    <button type="button" class="btn btn-danger" id="btn_reset_database_fresh_pos">
                        <i class="fa fa-refresh"></i> @lang('lang_v1.reset_database_fresh_pos')
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>