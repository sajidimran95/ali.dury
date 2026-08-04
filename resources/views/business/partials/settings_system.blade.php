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