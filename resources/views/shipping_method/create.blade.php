<div class="modal-dialog" role="document">
  <div class="modal-content">
    {!! Form::open(['url' => action([\App\Http\Controllers\ShippingMethodController::class, 'store']), 'method' => 'post', 'id' => 'shipping_method_add_form']) !!}
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang('lang_v1.add_shipping_method')</h4>
    </div>
    <div class="modal-body">
      <div class="form-group">
        {!! Form::label('name', __('lang_v1.name') . ':*') !!}
        {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => __('lang_v1.name')]); !!}
      </div>
      <div class="form-group">
        {!! Form::label('description', __('lang_v1.description') . ':') !!}
        {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('lang_v1.description')]); !!}
      </div>
      <div class="form-group">
        <div class="checkbox">
          <label>
            {!! Form::checkbox('is_active', 1, true); !!} @lang('business.is_active')
          </label>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white">@lang('messages.save')</button>
      <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">@lang('messages.close')</button>
    </div>
    {!! Form::close() !!}
  </div>
</div>
