<div class="pos-tab-content">
    <h4>@lang('business.add_keyboard_shortcuts'):</h4>
    <p class="help-block">@lang('lang_v1.shortcut_help'); @lang('lang_v1.example'): <b>ctrl+shift+b</b>, <b>ctrl+h</b></p>
    <p class="help-block">
        <b>@lang('lang_v1.available_key_names_are'):</b>
        <br> shift, ctrl, alt, backspace, tab, enter, return, capslock, esc, escape, space, pageup, pagedown, end, home, <br>left, up, right, down, ins, del, and plus
    </p>
    <div class="row">
        <div class="col-sm-6">
            <table class="table table-striped">
                <tr>
                    <th>@lang('business.operations')</th>
                    <th>@lang('business.keyboard_shortcut')</th>
                </tr>
                <tr>
                    <td>{!! __('sale.express_finalize') !!}:</td>
                    <td>
                        {!! Form::text('shortcuts[pos][express_checkout]', 
                        !empty($shortcuts["pos"]["express_checkout"]) ? $shortcuts["pos"]["express_checkout"] : null, ['class' => 'form-control']); !!}
                    </td>
                </tr>
                <tr>
                    <td>@lang('sale.finalize'):</td>
                    <td>
                        {!! Form::text('shortcuts[pos][pay_n_ckeckout]', !empty($shortcuts["pos"]["pay_n_ckeckout"]) ? $shortcuts["pos"]["pay_n_ckeckout"] : null, ['class' => 'form-control']); !!}
                    </td>
                </tr>
                <tr>
                    <td>@lang('sale.draft'):</td>
                    <td>
                        {!! Form::text('shortcuts[pos][draft]', !empty($shortcuts["pos"]["draft"]) ? $shortcuts["pos"]["draft"] : null, ['class' => 'form-control']); !!}
                    </td>
                </tr>
                <tr>
                    <td>@lang('messages.cancel'):</td>
                    <td>
                        {!! Form::text('shortcuts[pos][cancel]', !empty($shortcuts["pos"]["cancel"]) ? $shortcuts["pos"]["cancel"] : null, ['class' => 'form-control']); !!}
                    </td>
                </tr>
                <tr>
                    <td>@lang('lang_v1.recent_product_quantity'):</td>
                    <td>
                        {!! Form::text('shortcuts[pos][recent_product_quantity]', !empty($shortcuts["pos"]["recent_product_quantity"]) ? $shortcuts["pos"]["recent_product_quantity"] : null, ['class' => 'form-control']); !!}
                    </td>
                </tr>
                <tr>
                    <td>@lang('lang_v1.weighing_scale'):</td>
                    <td>
                        {!! Form::text('shortcuts[pos][weighing_scale]', !empty($shortcuts["pos"]["weighing_scale"]) ? $shortcuts["pos"]["weighing_scale"] : null, ['class' => 'form-control']); !!}
                    </td>
                </tr>
            </table>
        </div>
        <div class="col-sm-6">
            <table class="table table-striped">
                <tr>
                    <th>@lang('business.operations')</th>
                    <th>@lang('business.keyboard_shortcut')</th>
                </tr>
                <tr>
                    <td>@lang('sale.edit_discount'):</td>
                    <td>
                        {!! Form::text('shortcuts[pos][edit_discount]', !empty($shortcuts["pos"]["edit_discount"]) ? $shortcuts["pos"]["edit_discount"] : null, ['class' => 'form-control']); !!}
                    </td>
                </tr>
                <tr>
                    <td>@lang('sale.edit_order_tax'):</td>
                    <td>
                        {!! Form::text('shortcuts[pos][edit_order_tax]', !empty($shortcuts["pos"]["edit_order_tax"]) ? $shortcuts["pos"]["edit_order_tax"] : null, ['class' => 'form-control']); !!}
                    </td>
                </tr>
                <tr>
                    <td>@lang('sale.add_payment_row'):</td>
                    <td>
                        {!! Form::text('shortcuts[pos][add_payment_row]', !empty($shortcuts["pos"]["add_payment_row"]) ? $shortcuts["pos"]["add_payment_row"] : null, ['class' => 'form-control']); !!}
                    </td>
                </tr>
                <tr>
                    <td>@lang('sale.finalize_payment'):</td>
                    <td>
                        {!! Form::text('shortcuts[pos][finalize_payment]', !empty($shortcuts["pos"]["finalize_payment"]) ? $shortcuts["pos"]["finalize_payment"] : null, ['class' => 'form-control']); !!}
                    </td>
                </tr>
                <tr>
                    <td>@lang('lang_v1.add_new_product'):</td>
                    <td>
                        {!! Form::text('shortcuts[pos][add_new_product]', !empty($shortcuts["pos"]["add_new_product"]) ? $shortcuts["pos"]["add_new_product"] : null, ['class' => 'form-control']); !!}
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <h4>@lang('lang_v1.pos_settings'):</h4>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                <br>
                  <label>
                    {!! Form::checkbox('pos_settings[disable_pay_checkout]', 1,  
                        $pos_settings['disable_pay_checkout'] , 
                    [ 'class' => 'input-icheck']); !!} {{ __( 'lang_v1.disable_pay_checkout' ) }}
                  </label>
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                <br>
                  <label>
                    {!! Form::checkbox('pos_settings[disable_draft]', 1,  
                        $pos_settings['disable_draft'] , 
                    [ 'class' => 'input-icheck']); !!} {{ __( 'lang_v1.disable_draft' ) }}
                  </label>
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                <br>
                  <label>
                    {!! Form::checkbox('pos_settings[disable_express_checkout]', 1,  
                        $pos_settings['disable_express_checkout'] , 
                    [ 'class' => 'input-icheck']); !!} {{ __( 'lang_v1.disable_express_checkout' ) }}
                  </label>
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                <br>
                  <label>
                    {!! Form::checkbox('pos_settings[hide_product_suggestion]', 1,  $pos_settings['hide_product_suggestion'] , 
                    [ 'class' => 'input-icheck']); !!} {{ __( 'lang_v1.hide_product_suggestion' ) }}
                  </label>
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                <br>
                  <label>
                    {!! Form::checkbox('pos_settings[hide_recent_trans]', 1,  $pos_settings['hide_recent_trans'] , 
                    [ 'class' => 'input-icheck']); !!} {{ __( 'lang_v1.hide_recent_trans' ) }}
                  </label>
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                <br>
                  <label>
                    {!! Form::checkbox('pos_settings[disable_discount]', 1,  $pos_settings['disable_discount'] , 
                    [ 'class' => 'input-icheck']); !!} {{ __( 'lang_v1.disable_discount' ) }}
                  </label>
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                <br>
                  <label>
                    {!! Form::checkbox('pos_settings[disable_order_tax]', 1,  $pos_settings['disable_order_tax'] , 
                    [ 'class' => 'input-icheck']); !!} {{ __( 'lang_v1.disable_order_tax' ) }}
                  </label>
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                <br>
                  <label>
                    {!! Form::checkbox('pos_settings[disable_line_discount]', 1,
                    !empty($pos_settings['disable_line_discount']) ? 1 : 0,
                    [ 'class' => 'input-icheck']); !!} {{ __( 'lang_v1.disable_line_discount' ) }}
                  </label>
                  @show_tooltip(__('lang_v1.disable_line_discount_help'))
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                <br>
                  <label>
                    {!! Form::checkbox('pos_settings[disable_line_tax]', 1,
                    !empty($pos_settings['disable_line_tax']) ? 1 : 0,
                    [ 'class' => 'input-icheck']); !!} {{ __( 'lang_v1.disable_line_tax' ) }}
                  </label>
                  @show_tooltip(__('lang_v1.disable_line_tax_help'))
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                <br>
                  <label>
                    {!! Form::checkbox('pos_settings[is_pos_subtotal_editable]', 1,  
                    empty($pos_settings['is_pos_subtotal_editable']) ? 0 : 1 , 
                    [ 'class' => 'input-icheck']); !!} {{ __( 'lang_v1.subtotal_editable' ) }}
                  </label>
                  @show_tooltip(__('lang_v1.subtotal_editable_help_text'))
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                <br>
                  <label>
                    {!! Form::checkbox('pos_settings[disable_suspend]', 1,  
                    empty($pos_settings['disable_suspend']) ? 0 : 1 , 
                    [ 'class' => 'input-icheck']); !!} {{ __( 'lang_v1.disable_suspend_sale' ) }}
                  </label>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <div class="checkbox">
                <br>
                  <label>
                    {!! Form::checkbox('pos_settings[enable_transaction_date]', 1,  
                    empty($pos_settings['enable_transaction_date']) ? 0 : 1 , 
                    [ 'class' => 'input-icheck']); !!} {{ __( 'lang_v1.enable_pos_transaction_date' ) }}
                  </label>
                </div>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="form-group">
                <div class="checkbox">
                <br>
                  <label>
                    {!! Form::checkbox('pos_settings[inline_service_staff]', 1,  
                    !empty($pos_settings['inline_service_staff']) ? true : false , 
                    [ 'class' => 'input-icheck']); !!} {{ __( 'lang_v1.enable_service_staff_in_product_line' ) }}
                  </label>
                  @show_tooltip(__('lang_v1.inline_service_staff_tooltip'))
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                <br>
                  <label>
                    {!! Form::checkbox('pos_settings[is_service_staff_required]', 1,  
                    empty($pos_settings['is_service_staff_required']) ? 0 : 1 , 
                    [ 'class' => 'input-icheck']); !!} {{ __( 'lang_v1.is_service_staff_required' ) }}
                  </label>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                <br>
                  <label>
                    {!! Form::checkbox('pos_settings[disable_credit_sale_button]', 1,  
                    empty($pos_settings['disable_credit_sale_button']) ? 0 : 1 , 
                    [ 'class' => 'input-icheck']); !!} {{ __( 'lang_v1.disable_credit_sale_button' ) }}
                  </label>
                  @show_tooltip(__('lang_v1.show_credit_sale_btn_help'))
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                <br>
                  <label>
                    {!! Form::checkbox('pos_settings[enable_weighing_scale]', 1,  
                    empty($pos_settings['enable_weighing_scale']) ? 0 : 1 , 
                    [ 'class' => 'input-icheck']); !!} {{ __( 'lang_v1.enable_weighing_scale' ) }}
                  </label>
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                <br>
                  <label>
                    {!! Form::checkbox('pos_settings[show_invoice_scheme]', 1,  
                       empty($pos_settings['show_invoice_scheme']) ? 0 : 1 , 
                    [ 'class' => 'input-icheck']); !!} {{ __( 'lang_v1.show_invoice_scheme' ) }}
                  </label>
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                <br>
                  <label>
                    {!! Form::checkbox('pos_settings[show_invoice_layout]', 1,  
                        !empty($pos_settings['show_invoice_layout']) , 
                    [ 'class' => 'input-icheck']); !!} {{ __( 'lang_v1.show_invoice_layout' ) }}
                  </label>
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                <br>
                  <label>
                    {!! Form::checkbox('pos_settings[print_on_suspend]', 1,  
                        !empty($pos_settings['print_on_suspend']) , 
                    [ 'class' => 'input-icheck']); !!} {{ __( 'lang_v1.print_on_suspend' ) }}
                  </label>
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                <br>
                  <label>
                    {!! Form::checkbox('pos_settings[show_pricing_on_product_sugesstion]', 1,  
                        !empty($pos_settings['show_pricing_on_product_sugesstion']) , 
                    [ 'class' => 'input-icheck']); !!} {{ __( 'lang_v1.show_pricing_on_product_sugesstion' ) }}
                  </label>
                </div>
            </div>
        </div>
    </div>

    <hr>
    <div class="row">
        <div class="col-sm-12">
            <h4>@lang('lang_v1.pos_pwa_settings')</h4>
            <p class="help-block">@lang('lang_v1.pos_pwa_settings_help')</p>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                <br>
                  <label>
                    {!! Form::checkbox('pos_settings[enable_pwa]', 1,
                        !array_key_exists('enable_pwa', $pos_settings) || !empty($pos_settings['enable_pwa']),
                    [ 'class' => 'input-icheck']); !!} {{ __('lang_v1.enable_pos_pwa') }}
                  </label>
                  @show_tooltip(__('lang_v1.enable_pos_pwa_tooltip'))
                </div>
            </div>
        </div>
        <div class="clearfix"></div>

        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('pwa_app_name', __('lang_v1.pwa_app_name') . ':') !!}
                {!! Form::text('pos_settings[pwa_app_name]', $pos_settings['pwa_app_name'] ?? null, [
                    'class' => 'form-control',
                    'id' => 'pwa_app_name',
                    'placeholder' => Session::get('business.name'),
                ]) !!}
                <p class="help-block">@lang('lang_v1.pwa_app_name_help')</p>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('pwa_short_name', __('lang_v1.pwa_short_name') . ':') !!}
                {!! Form::text('pos_settings[pwa_short_name]', $pos_settings['pwa_short_name'] ?? 'POS', [
                    'class' => 'form-control',
                    'id' => 'pwa_short_name',
                    'maxlength' => 12,
                ]) !!}
                <p class="help-block">@lang('lang_v1.pwa_short_name_help')</p>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('pwa_icon', __('lang_v1.pwa_icon') . ':') !!}
                {!! Form::file('pwa_icon', ['accept' => 'image/png,image/jpeg,image/webp', 'class' => 'form-control']) !!}
                <p class="help-block">@lang('lang_v1.pwa_icon_help')</p>
                @php
                    $pwa_icon_preview = null;
                    if (!empty($pos_settings['pwa_icon']) && file_exists(public_path('uploads/pwa_icons/' . $pos_settings['pwa_icon']))) {
                        $pwa_icon_preview = asset('uploads/pwa_icons/' . $pos_settings['pwa_icon']);
                    }
                @endphp
                @if ($pwa_icon_preview)
                    <img src="{{ $pwa_icon_preview }}" alt="PWA icon" style="max-height:64px;margin-top:8px;border-radius:12px;border:1px solid #ddd;">
                @endif
            </div>
        </div>

        <div class="col-sm-12">
            <div class="form-group">
                {!! Form::label('pwa_description', __('lang_v1.pwa_description') . ':') !!}
                {!! Form::textarea('pos_settings[pwa_description]', $pos_settings['pwa_description'] ?? null, [
                    'class' => 'form-control',
                    'id' => 'pwa_description',
                    'rows' => 2,
                    'placeholder' => __('lang_v1.pwa_description_placeholder'),
                ]) !!}
            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('pwa_theme_color', __('lang_v1.pwa_theme_color') . ':') !!}
                {!! Form::input('color', 'pos_settings[pwa_theme_color]', $pos_settings['pwa_theme_color'] ?? '#0f0f0f', [
                    'class' => 'form-control',
                    'id' => 'pwa_theme_color',
                    'style' => 'max-width:120px;height:40px;padding:2px;',
                ]) !!}
                <p class="help-block">@lang('lang_v1.pwa_theme_color_help')</p>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('pwa_background_color', __('lang_v1.pwa_background_color') . ':') !!}
                {!! Form::input('color', 'pos_settings[pwa_background_color]', $pos_settings['pwa_background_color'] ?? '#0f0f0f', [
                    'class' => 'form-control',
                    'id' => 'pwa_background_color',
                    'style' => 'max-width:120px;height:40px;padding:2px;',
                ]) !!}
                <p class="help-block">@lang('lang_v1.pwa_background_color_help')</p>
            </div>
        </div>

        <div class="col-sm-12">
            <div class="callout callout-info" style="margin-top:8px;">
                <p style="margin:0;"><strong>@lang('lang_v1.pos_pwa_how_to_install'):</strong></p>
                <ul style="margin:8px 0 0; padding-left:18px;">
                    <li>@lang('lang_v1.pos_pwa_install_chrome')</li>
                    <li>@lang('lang_v1.pos_pwa_install_edge')</li>
                    <li>@lang('lang_v1.pos_pwa_install_ios')</li>
                </ul>
                <p class="help-block" style="margin:10px 0 0;">
                    @lang('lang_v1.pos_pwa_https_note')
                    —
                    <a href="{{ url('/pos/create') }}" target="_blank" rel="noopener">{{ url('/pos/create') }}</a>
                </p>
            </div>
        </div>
    </div>
    <hr>
    @include('business.partials.settings_weighing_scale')
</div>