<div class="modal-dialog modal-lg" role="document">
  <div class="modal-content">
  @php
    $form_id = 'contact_add_form';
    if(isset($quick_add)){
      $form_id = 'quick_add_contact';
    }

    if(isset($store_action)) {
      $url = $store_action;
      $type = 'lead';
      $customer_groups = [];
    } else {
      $url = action([\App\Http\Controllers\ContactController::class, 'store']);
      $type = isset($selected_type) ? $selected_type : '';
      $sources = $sources ?? [];
      $life_stages = $life_stages ?? [];
    }
  @endphp
    {!! Form::open(['url' => $url, 'method' => 'post', 'id' => $form_id ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang('contact.add_contact')</h4>
    </div>

    <div class="modal-body">
        <div class="row">            
            <div class="col-md-4 contact_type_div">
                <div class="form-group">
                    {!! Form::label('type', __('contact.contact_type') . ':*' ) !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-user"></i>
                        </span>
                        {!! Form::select('type', $types, $type , ['class' => 'form-control', 'id' => 'contact_type','placeholder' => __('messages.please_select'), 'required']); !!}
                    </div>
                </div>
            </div>
            <div class="col-md-4 mt-15">
                <label class="radio-inline">
                    <input type="radio" name="contact_type_radio" id="inlineRadio1" value="individual">
                    @lang('lang_v1.individual')
                </label>
                <label class="radio-inline">
                    <input type="radio" name="contact_type_radio" id="inlineRadio2" value="business">
                    @lang('business.business')
                </label>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('contact_id', __('lang_v1.contact_id') . ':') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-id-badge"></i>
                        </span>
                        {!! Form::text('contact_id', null, ['class' => 'form-control','placeholder' => __('lang_v1.contact_id')]); !!}
                    </div>
                    <p class="help-block">
                        @lang('lang_v1.leave_empty_to_autogenerate')
                    </p>
                </div>
            </div>
            <div class="col-md-4 customer_fields">
                <div class="form-group">
                  {!! Form::label('customer_group_id', __('lang_v1.customer_group') . ':') !!}
                  @php
                    $msa_common = session('business.common_settings') ?? [];
                  @endphp
                  <div class="input-group">
                      <span class="input-group-addon">
                          <i class="fa fa-users"></i>
                      </span>
                      {!! Form::select('customer_group_id', $customer_groups, '', [
                          'class' => 'form-control',
                          'id' => 'customer_group_id',
                          'data-msa-retail-group' => $msa_common['msa_retail_customer_group_id'] ?? '',
                          'data-msa-wholesale-group' => $msa_common['msa_wholesale_customer_group_id'] ?? '',
                      ]); !!}
                  </div>
                </div>
            </div>
            <div class="clearfix customer_fields"></div>
            <div class="col-md-4 business" style="display: none;">
                <div class="form-group">
                    {!! Form::label('supplier_business_name', __('business.business_name') . ':') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-briefcase"></i>
                        </span>
                        {!! Form::text('supplier_business_name', null, ['class' => 'form-control', 'placeholder' => __('business.business_name')]); !!}
                    </div>
                </div>
            </div>

            <div class="clearfix"></div>

            <div class="col-md-3 individual" style="display: none;">
                <div class="form-group">
                    {!! Form::label('prefix', __( 'business.prefix' ) . ':') !!}
                    {!! Form::text('prefix', null, ['class' => 'form-control', 'placeholder' => __( 'business.prefix_placeholder' ) ]); !!}
                </div>
            </div>
            <div class="col-md-3 individual" style="display: none;">
                <div class="form-group">
                    {!! Form::label('first_name', __( 'business.first_name' ) . ':*') !!}
                    {!! Form::text('first_name', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'business.first_name' ) ]); !!}
                </div>
            </div>
            <div class="col-md-3 individual" style="display: none;">
                <div class="form-group">
                    {!! Form::label('middle_name', __( 'lang_v1.middle_name' ) . ':') !!}
                    {!! Form::text('middle_name', null, ['class' => 'form-control', 'placeholder' => __( 'lang_v1.middle_name' ) ]); !!}
                </div>
            </div>
            <div class="col-md-3 individual" style="display: none;">
                <div class="form-group">
                    {!! Form::label('last_name', __( 'business.last_name' ) . ':') !!}
                    {!! Form::text('last_name', null, ['class' => 'form-control', 'placeholder' => __( 'business.last_name' ) ]); !!}
                </div>
            </div>
            <div class="clearfix"></div>
        
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('mobile', __('contact.mobile') . ':*') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-mobile"></i>
                        </span>
                        {!! Form::text('mobile', null, ['class' => 'form-control', 'required', 'placeholder' => __('contact.mobile')]); !!}
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('alternate_number', __('contact.alternate_contact_number') . ':') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-phone"></i>
                        </span>
                        {!! Form::text('alternate_number', null, ['class' => 'form-control', 'placeholder' => __('contact.alternate_contact_number')]); !!}
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('landline', __('contact.landline') . ':') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-phone"></i>
                        </span>
                        {!! Form::text('landline', null, ['class' => 'form-control', 'placeholder' => __('contact.landline')]); !!}
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('email', __('business.email') . ':') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-envelope"></i>
                        </span>
                        {!! Form::email('email', null, ['class' => 'form-control','placeholder' => __('business.email')]); !!}
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
            <div class="col-sm-4 individual" style="display: none;">
                <div class="form-group">
                    {!! Form::label('dob', __('lang_v1.dob') . ':') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </span>
                        
                        {!! Form::text('dob', null, ['class' => 'form-control dob-date-picker','placeholder' => __('lang_v1.dob'), 'readonly']); !!}
                    </div>
                </div>
            </div>

            {{-- Lead-only fields: hidden in HTML for customer/supplier so modal doesn't jump after JS --}}
            <div class="col-md-4 lead_additional_div" @if(($type ?? '') !== 'lead') style="display: none;" @endif>
              <div class="form-group">
                  {!! Form::label('crm_source', __('lang_v1.source') . ':' ) !!}
                  <div class="input-group">
                      <span class="input-group-addon">
                          <i class="fas fa fa-search"></i>
                      </span>
                      {!! Form::select('crm_source', $sources, null , ['class' => 'form-control', 'id' => 'crm_source','placeholder' => __('messages.please_select')]); !!}
                  </div>
              </div>
            </div>
            
            <div class="col-md-4 lead_additional_div" @if(($type ?? '') !== 'lead') style="display: none;" @endif>
              <div class="form-group">
                  {!! Form::label('crm_life_stage', __('lang_v1.life_stage') . ':' ) !!}
                  <div class="input-group">
                      <span class="input-group-addon">
                          <i class="fas fa fa-life-ring"></i>
                      </span>
                      {!! Form::select('crm_life_stage', $life_stages, null , ['class' => 'form-control', 'id' => 'crm_life_stage','placeholder' => __('messages.please_select')]); !!}
                  </div>
              </div>
            </div>

            <!-- User in create leads -->
            <div class="col-md-6 lead_additional_div" @if(($type ?? '') !== 'lead') style="display: none;" @endif>
                  <div class="form-group">
                      {!! Form::label('user_id', __('lang_v1.assigned_to') . ':*' ) !!}
                      <div class="input-group">
                          <span class="input-group-addon">
                              <i class="fa fa-user"></i>
                          </span>
                          {!! Form::select('user_id[]', $users ?? [], null , ['class' => 'form-control select2', 'id' => 'user_id', 'multiple', 'required', 'style' => 'width: 100%;']); !!}
                      </div>
                  </div>
            </div>

            <!-- User in create customer & supplier -->
            @if(config('constants.enable_contact_assign') && $type !== 'lead')
                <div class="col-md-6">
                      <div class="form-group">
                          {!! Form::label('assigned_to_users', __('lang_v1.assigned_to') . ':' ) !!}
                          <div class="input-group">
                              <span class="input-group-addon">
                                  <i class="fa fa-user"></i>
                              </span>
                              {!! Form::select('assigned_to_users[]', $users ?? [], null , ['class' => 'form-control select2', 'id' => 'assigned_to_users', 'multiple', 'style' => 'width: 100%;']); !!}
                          </div>
                      </div>
                </div>
            @endif

            <div class="clearfix"></div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white tw-dw-btn-sm center-block more_btn" data-target="#more_div">@lang('lang_v1.more_info') <i class="fa fa-chevron-down"></i></button>
            </div>

            <div id="more_div" class="hide">
                {!! Form::hidden('position', null, ['id' => 'position']); !!}
                <div class="col-md-12"><hr/></div>

                <div class="col-md-4">
                    <div class="form-group">
                      {!! Form::label('tax_number', __('contact.tax_no') . ':') !!}
                        <div class="input-group">
                          <span class="input-group-addon">
                              <i class="fa fa-info"></i>
                          </span>
                          {!! Form::text('tax_number', null, ['class' => 'form-control', 'placeholder' => __('contact.tax_no')]); !!}
                        </div>
                    </div>
                </div>

                <div class="col-md-4 opening_balance">
                  <div class="form-group">
                      {!! Form::label('opening_balance', __('lang_v1.opening_balance') . ':') !!}
                      <div class="input-group">
                          <span class="input-group-addon">
                              <i class="fas fa-money-bill-alt"></i>
                          </span>
                          {!! Form::text('opening_balance', 0, ['class' => 'form-control input_number']); !!}
                      </div>
                  </div>
                </div>

                <div class="col-md-4 pay_term">
                  <div class="form-group">
                    <div class="multi-input">
                      {!! Form::label('pay_term_number', __('contact.pay_term') . ':') !!} @show_tooltip(__('tooltip.pay_term'))
                      <br/>
                      {!! Form::number('pay_term_number', null, ['class' => 'form-control width-40 pull-left', 'placeholder' => __('contact.pay_term')]); !!}

                      {!! Form::select('pay_term_type', ['months' => __('lang_v1.months'), 'days' => __('lang_v1.days')], '', ['class' => 'form-control width-60 pull-left','placeholder' => __('messages.please_select')]); !!}
                    </div>
                  </div>
                </div>
                <div class="clearfix"></div>
                @php
                  $common_settings = session()->get('business.common_settings');
                  $default_credit_limit = !empty($common_settings['default_credit_limit']) ? $common_settings['default_credit_limit'] : null;
                @endphp
                <div class="col-md-4 customer_fields">
                  <div class="form-group">
                      {!! Form::label('credit_limit', __('lang_v1.credit_limit') . ':') !!}
                      <div class="input-group">
                          <span class="input-group-addon">
                              <i class="fas fa-money-bill-alt"></i>
                          </span>
                          {!! Form::text('credit_limit', $default_credit_limit ?? null, ['class' => 'form-control input_number']); !!}
                      </div>
                      <p class="help-block">@lang('lang_v1.credit_limit_help')</p>
                  </div>
                </div>
                

                <div class="col-md-12"><hr/></div>
                <div class="clearfix"></div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('address_line_1', __('lang_v1.address_line_1') . ':') !!}
                        {!! Form::text('address_line_1', null, ['class' => 'form-control', 'placeholder' => __('lang_v1.address_line_1'), 'rows' => 3]); !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('address_line_2', __('lang_v1.address_line_2') . ':') !!}
                        {!! Form::text('address_line_2', null, ['class' => 'form-control', 'placeholder' => __('lang_v1.address_line_2'), 'rows' => 3]); !!}
                    </div>
                </div>
                <div class="clearfix"></div>
              <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('city', __('business.city') . ':') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-map-marker"></i>
                        </span>
                        {!! Form::text('city', null, ['class' => 'form-control', 'placeholder' => __('business.city')]); !!}
                    </div>
                </div>
              </div>
          <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('state', __('business.state') . ':') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-map-marker"></i>
                    </span>
                    {!! Form::text('state', null, ['class' => 'form-control', 'placeholder' => __('business.state')]); !!}
                </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('country', __('business.country') . ':') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-globe"></i>
                    </span>
                    {!! Form::text('country', null, ['class' => 'form-control', 'placeholder' => __('business.country')]); !!}
                </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('zip_code', __('business.zip_code') . ':') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-map-marker"></i>
                    </span>
                    {!! Form::text('zip_code', null, ['class' => 'form-control', 
                    'placeholder' => __('business.zip_code_placeholder')]); !!}
                </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('land_mark', __('business.land_mark') . ':') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-map-marker"></i>
                    </span>
                    {!! Form::text('land_mark', null, ['class' => 'form-control', 'placeholder' => __('business.land_mark')]); !!}
                </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('street_name', __('business.street_name') . ':') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-map-marker"></i>
                    </span>
                    {!! Form::text('street_name', null, ['class' => 'form-control', 'placeholder' => __('business.street_name')]); !!}
                </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('building_number', __('business.building_number') . ':') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-map-marker"></i>
                    </span>
                    {!! Form::text('building_number', null, ['class' => 'form-control', 'placeholder' => __('business.building_number')]); !!}
                </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('additional_number', __('business.additional_number_secondary') . ':') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-map-marker"></i>
                    </span>
                    {!! Form::text('additional_number', null, ['class' => 'form-control', 'placeholder' => __('business.additional_number')]); !!}
                </div>
            </div>
          </div>

          <div class="clearfix"></div>
          <div class="col-md-12">
            <hr/>
          </div>
          @php
            $custom_labels = json_decode(session('business.custom_labels'), true);
            $contact_cf_details = !empty($custom_labels['contact_cf_details']) ? $custom_labels['contact_cf_details'] : [];
            $contact_custom_field1 = !empty($custom_labels['contact']['custom_field_1']) ? $custom_labels['contact']['custom_field_1'] : __('lang_v1.contact_custom_field1');
            $contact_custom_field2 = !empty($custom_labels['contact']['custom_field_2']) ? $custom_labels['contact']['custom_field_2'] : __('lang_v1.contact_custom_field2');
            $contact_custom_field3 = !empty($custom_labels['contact']['custom_field_3']) ? $custom_labels['contact']['custom_field_3'] : __('lang_v1.contact_custom_field3');
            $contact_custom_field4 = !empty($custom_labels['contact']['custom_field_4']) ? $custom_labels['contact']['custom_field_4'] : __('lang_v1.contact_custom_field4');
            $contact_custom_field5 = !empty($custom_labels['contact']['custom_field_5']) ? $custom_labels['contact']['custom_field_5'] : __('lang_v1.custom_field', ['number' => 5]);
            $contact_custom_field6 = !empty($custom_labels['contact']['custom_field_6']) ? $custom_labels['contact']['custom_field_6'] : __('lang_v1.custom_field', ['number' => 6]);
            $contact_custom_field7 = !empty($custom_labels['contact']['custom_field_7']) ? $custom_labels['contact']['custom_field_7'] : __('lang_v1.custom_field', ['number' => 7]);
            $contact_custom_field8 = !empty($custom_labels['contact']['custom_field_8']) ? $custom_labels['contact']['custom_field_8'] : __('lang_v1.custom_field', ['number' => 8]);
            $contact_custom_field9 = !empty($custom_labels['contact']['custom_field_9']) ? $custom_labels['contact']['custom_field_9'] : __('lang_v1.custom_field', ['number' => 9]);
            $contact_custom_field10 = !empty($custom_labels['contact']['custom_field_10']) ? $custom_labels['contact']['custom_field_10'] : __('lang_v1.custom_field', ['number' => 10]);

            $cf1_type = !empty($contact_cf_details[1]['type']) ? $contact_cf_details[1]['type'] : 'text';
            $cf1_dropdown = !empty($contact_cf_details[1]['dropdown_options']) ? preg_split("/\r\n|\n|\r/", $contact_cf_details[1]['dropdown_options']) : [];
            $cf3_type = !empty($contact_cf_details[3]['type']) ? $contact_cf_details[3]['type'] : 'text';
            $cf3_dropdown = !empty($contact_cf_details[3]['dropdown_options']) ? preg_split("/\r\n|\n|\r/", $contact_cf_details[3]['dropdown_options']) : [];
          @endphp
          <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('custom_field1', $contact_custom_field1 . ':') !!}
                @if($cf1_type === 'dropdown')
                    <select name="custom_field1" id="custom_field1" class="form-control">
                        <option value="">{{ $contact_custom_field1 }}</option>
                        @foreach($cf1_dropdown as $option)
                            @if(trim($option) !== '')
                                <option value="{{ trim($option) }}">{{ trim($option) }}</option>
                            @endif
                        @endforeach
                    </select>
                @else
                    {!! Form::text('custom_field1', null, ['class' => 'form-control', 'id' => 'custom_field1',
                        'placeholder' => $contact_custom_field1]); !!}
                @endif
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('custom_field2', $contact_custom_field2 . ':') !!}
                {!! Form::text('custom_field2', null, ['class' => 'form-control',
                    'placeholder' => $contact_custom_field2]); !!}
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('custom_field3', $contact_custom_field3 . ':') !!}
                @if($cf3_type === 'dropdown')
                    <select name="custom_field3" id="custom_field3" class="form-control">
                        <option value="">{{ $contact_custom_field3 }}</option>
                        @foreach($cf3_dropdown as $option)
                            @if(trim($option) !== '')
                                <option value="{{ trim($option) }}">{{ trim($option) }}</option>
                            @endif
                        @endforeach
                    </select>
                @else
                    {!! Form::text('custom_field3', null, ['class' => 'form-control', 'id' => 'custom_field3',
                        'placeholder' => $contact_custom_field3]); !!}
                @endif
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('custom_field4', $contact_custom_field4 . ':') !!}
                {!! Form::text('custom_field4', null, ['class' => 'form-control', 
                    'placeholder' => $contact_custom_field4]); !!}
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('custom_field5', $contact_custom_field5 . ':') !!}
                {!! Form::text('custom_field5', null, ['class' => 'form-control', 
                    'placeholder' => $contact_custom_field5]); !!}
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('custom_field6', $contact_custom_field6 . ':') !!}
                {!! Form::text('custom_field6', null, ['class' => 'form-control', 
                    'placeholder' => $contact_custom_field6]); !!}
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('custom_field7', $contact_custom_field7 . ':') !!}
                {!! Form::text('custom_field7', null, ['class' => 'form-control', 
                    'placeholder' => $contact_custom_field7]); !!}
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('custom_field8', $contact_custom_field8 . ':') !!}
                {!! Form::text('custom_field8', null, ['class' => 'form-control', 
                    'placeholder' => $contact_custom_field8]); !!}
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('custom_field9', $contact_custom_field9 . ':') !!}
                {!! Form::text('custom_field9', null, ['class' => 'form-control', 
                    'placeholder' => $contact_custom_field9]); !!}
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('custom_field10', $contact_custom_field10 . ':') !!}
                {!! Form::text('custom_field10', null, ['class' => 'form-control', 
                    'placeholder' => $contact_custom_field10]); !!}
            </div>
          </div>
          <div class="col-md-12 shipping_addr_div"><hr></div>
          <div class="col-md-8 col-md-offset-2 shipping_addr_div mb-10" >
              <strong>{{__('lang_v1.shipping_address')}}</strong><br>
              {!! Form::text('shipping_address', null, ['class' => 'form-control', 
                    'placeholder' => __('lang_v1.search_address'), 'id' => 'shipping_address']); !!}
            <div class="mb-10" id="map"></div>
          </div>
          @php
                $shipping_custom_label_1 = !empty($custom_labels['shipping']['custom_field_1']) ? $custom_labels['shipping']['custom_field_1'] : '';

                $shipping_custom_label_2 = !empty($custom_labels['shipping']['custom_field_2']) ? $custom_labels['shipping']['custom_field_2'] : '';

                $shipping_custom_label_3 = !empty($custom_labels['shipping']['custom_field_3']) ? $custom_labels['shipping']['custom_field_3'] : '';

                $shipping_custom_label_4 = !empty($custom_labels['shipping']['custom_field_4']) ? $custom_labels['shipping']['custom_field_4'] : '';

                $shipping_custom_label_5 = !empty($custom_labels['shipping']['custom_field_5']) ? $custom_labels['shipping']['custom_field_5'] : '';
            @endphp

            @if(!empty($custom_labels['shipping']['is_custom_field_1_contact_default']) && !empty($shipping_custom_label_1))
                @php
                    $label_1 = $shipping_custom_label_1 . ':';
                @endphp

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('shipping_custom_field_1', $label_1 ) !!}
                        {!! Form::text('shipping_custom_field_details[shipping_custom_field_1]', null, ['class' => 'form-control','placeholder' => $shipping_custom_label_1]); !!}
                    </div>
                </div>
            @endif
            @if(!empty($custom_labels['shipping']['is_custom_field_2_contact_default']) && !empty($shipping_custom_label_2))
                @php
                    $label_2 = $shipping_custom_label_2 . ':';
                @endphp

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('shipping_custom_field_2', $label_2 ) !!}
                        {!! Form::text('shipping_custom_field_details[shipping_custom_field_2]', null, ['class' => 'form-control','placeholder' => $shipping_custom_label_2]); !!}
                    </div>
                </div>
            @endif
            @if(!empty($custom_labels['shipping']['is_custom_field_3_contact_default']) && !empty($shipping_custom_label_3))
                @php
                    $label_3 = $shipping_custom_label_3 . ':';
                @endphp

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('shipping_custom_field_3', $label_3 ) !!}
                        {!! Form::text('shipping_custom_field_details[shipping_custom_field_3]', null, ['class' => 'form-control','placeholder' => $shipping_custom_label_3]); !!}
                    </div>
                </div>
            @endif
            @if(!empty($custom_labels['shipping']['is_custom_field_4_contact_default']) && !empty($shipping_custom_label_4))
                @php
                    $label_4 = $shipping_custom_label_4 . ':';
                @endphp

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('shipping_custom_field_4', $label_4 ) !!}
                        {!! Form::text('shipping_custom_field_details[shipping_custom_field_4]', null, ['class' => 'form-control','placeholder' => $shipping_custom_label_4]); !!}
                    </div>
                </div>
            @endif
            @if(!empty($custom_labels['shipping']['is_custom_field_5_contact_default']) && !empty($shipping_custom_label_5))
                @php
                    $label_5 = $shipping_custom_label_5 . ':';
                @endphp

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('shipping_custom_field_5', $label_5 ) !!}
                        {!! Form::text('shipping_custom_field_details[shipping_custom_field_5]', null, ['class' => 'form-control','placeholder' => $shipping_custom_label_5]); !!}
                    </div>
                </div>
            @endif
            @if(!empty($common_settings['is_enabled_export']))
                <div class="col-md-12 mb-12">
                    <div class="form-check">
                        <input type="checkbox" name="is_export" class="form-check-input" id="is_customer_export">
                        <label class="form-check-label" for="is_customer_export">@lang('lang_v1.is_export')</label>
                    </div>
                </div>
                @php
                    $i = 1;
                @endphp
                @for($i; $i <= 6 ; $i++)
                    <div class="col-md-4 export_div" style="display: none;">
                        <div class="form-group">
                            {!! Form::label('export_custom_field_'.$i, __('lang_v1.export_custom_field'.$i).':' ) !!}
                            {!! Form::text('export_custom_field_'.$i, null, ['class' => 'form-control','placeholder' => __('lang_v1.export_custom_field'.$i)]); !!}
                        </div>
                    </div>
                @endfor
            @endif
            </div>
        </div>
        @include('layouts.partials.module_form_part')
    </div>
    
    <div class="modal-footer">
      <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white">@lang( 'messages.save' )</button>
      <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}
  
  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
<script type="text/javascript">
(function () {
    function bindMsaCot($) {
        var $group = $('#customer_group_id');
        var $cot = $('#custom_field1');
        if (!$group.length || !$cot.length) {
            return;
        }
        $group.off('change.msaCot').on('change.msaCot', function () {
            if ($.trim($cot.val() || '') !== '') {
                return;
            }
            var id = String($(this).val() || '');
            var retail = String($(this).data('msa-retail-group') || '');
            var wholesale = String($(this).data('msa-wholesale-group') || '');
            if (id && retail && id === retail) {
                $cot.val('RETAIL');
            } else if (id && wholesale && id === wholesale) {
                $cot.val('DISTRIBUTOR');
            }
        });
    }
    // This modal is often included mid-body (e.g. POS) before footer vendor/jQuery loads
    function whenJQuery(fn) {
        if (typeof jQuery !== 'undefined') {
            jQuery(fn);
            return;
        }
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof jQuery !== 'undefined') {
                jQuery(fn);
            }
        });
    }
    whenJQuery(bindMsaCot);
})();
</script>