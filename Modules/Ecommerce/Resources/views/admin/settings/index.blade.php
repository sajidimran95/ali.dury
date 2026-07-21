@extends('layouts.app')
@section('title', __('ecommerce::lang.settings'))
@section('content')
<section class="content-header"><h1>@lang('ecommerce::lang.settings')</h1></section>
<section class="content">
{!! Form::open(['url' => route('ecommerce.admin.settings.update'), 'method' => 'post']) !!}
<div class="box box-primary">
    <div class="box-body">
        <div class="form-group">
            <label>
                {!! Form::checkbox('is_enabled', 1, $settings->is_enabled, ['class' => 'input-icheck']) !!}
                @lang('ecommerce::lang.enable_ecommerce')
            </label>
            <p class="help-block">@lang('ecommerce::lang.enable_ecommerce_help')</p>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('store_name', __('ecommerce::lang.store_name')) !!}
                    {!! Form::text('store_name', $settings->store_name, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('location_id', __('ecommerce::lang.default_location')) !!}
                    {!! Form::select('location_id', $locations, $settings->location_id, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]) !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('contact_email', 'Contact Email') !!}
                    {!! Form::email('contact_email', $settings->contact_email, ['class' => 'form-control']) !!}
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4"><label>{!! Form::checkbox('enable_cod', 1, $settings->enable_cod) !!} Cash on Delivery</label></div>
            <div class="col-md-4"><label>{!! Form::checkbox('enable_stripe', 1, $settings->enable_stripe) !!} Stripe Ready</label></div>
            <div class="col-md-4"><label>{!! Form::checkbox('enable_paypal', 1, $settings->enable_paypal) !!} PayPal Ready</label></div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('stripe_key', 'Stripe Key') !!}
                    {!! Form::text('stripe_key', $settings->stripe_key, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('stripe_secret', 'Stripe Secret') !!}
                    {!! Form::text('stripe_secret', $settings->stripe_secret, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('paypal_client_id', 'PayPal Client ID') !!}
                    {!! Form::text('paypal_client_id', $settings->paypal_client_id, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('paypal_secret', 'PayPal Secret') !!}
                    {!! Form::text('paypal_secret', $settings->paypal_secret, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('seo_title', __('ecommerce::lang.seo_title')) !!}
                    {!! Form::text('seo_title', $settings->seo_title, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('seo_description', __('ecommerce::lang.seo_description')) !!}
                    {!! Form::textarea('seo_description', $settings->seo_description, ['class' => 'form-control', 'rows' => 2]) !!}
                </div>
            </div>
        </div>
        <p><a href="{{ route('ecommerce.admin.shipping_tax') }}">@lang('ecommerce::lang.shipping_tax')</a>
        · Storefront: <a href="{{ url('/shop') }}" target="_blank">/shop</a></p>
    </div>
    <div class="box-footer">
        <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white">@lang('messages.save')</button>
    </div>
</div>
{!! Form::close() !!}
</section>
@endsection
