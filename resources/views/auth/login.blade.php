@extends('layouts.auth-shell')
@section('title', __('lang_v1.login'))
@inject('request', 'Illuminate\Http\Request')

@php
    $username = old('username');
    $password = null;
    if (config('app.env') == 'demo') {
        $username = 'admin';
        $password = '123456';

        $demo_types = [
            'all_in_one' => 'admin',
            'super_market' => 'admin',
            'pharmacy' => 'admin-pharmacy',
            'electronics' => 'admin-electronics',
            'services' => 'admin-services',
            'restaurant' => 'admin-restaurant',
            'superadmin' => 'superadmin',
            'woocommerce' => 'woocommerce_user',
            'essentials' => 'admin-essentials',
            'manufacturing' => 'manufacturer-demo',
        ];

        if (!empty($_GET['demo_type']) && array_key_exists($_GET['demo_type'], $demo_types)) {
            $username = $demo_types[$_GET['demo_type']];
        }
    }
@endphp

@section('auth-before-card')
    @if (config('app.env') == 'demo')
        <div class="japs-auth-demo">
            @component('components.widget', [
                'class' => 'box-primary',
                'header' => '<h4 class="text-center">Demo Shops <small><i><br/>Demos are for example purpose only. <b>Click a button to login.</b></i></small></h4>',
            ])
                <a href="?demo_type=all_in_one" class="btn btn-app bg-olive demo-login" data-toggle="tooltip" data-admin="{{ $demo_types['all_in_one'] }}"><i class="fas fa-star"></i> All In One</a>
                <a href="?demo_type=pharmacy" class="btn bg-maroon btn-app demo-login" data-toggle="tooltip" data-admin="{{ $demo_types['pharmacy'] }}"><i class="fas fa-medkit"></i> Pharmacy</a>
                <a href="?demo_type=services" class="btn bg-orange btn-app demo-login" data-toggle="tooltip" data-admin="{{ $demo_types['services'] }}"><i class="fas fa-wrench"></i> Multi-Service</a>
                <a href="?demo_type=electronics" class="btn bg-purple btn-app demo-login" data-toggle="tooltip" data-admin="{{ $demo_types['electronics'] }}"><i class="fas fa-laptop"></i> Electronics</a>
                <a href="?demo_type=super_market" class="btn bg-navy btn-app demo-login" data-toggle="tooltip" data-admin="{{ $demo_types['super_market'] }}"><i class="fas fa-shopping-cart"></i> Super Market</a>
                <a href="?demo_type=restaurant" class="btn bg-red btn-app demo-login" data-toggle="tooltip" data-admin="{{ $demo_types['restaurant'] }}"><i class="fas fa-utensils"></i> Restaurant</a>
            @endcomponent
        </div>
    @endif
@endsection

@section('auth-title')
    @lang('lang_v1.welcome_back')
@endsection

@section('auth-subtitle')
    @lang('lang_v1.login_to_your') {{ config('app.name', 'Japs POS') }}
@endsection

@section('auth-content')
    <form method="POST" action="{{ route('login') }}" id="login-form">
        {{ csrf_field() }}
        <div class="form-group has-feedback {{ $errors->has('username') ? ' has-error' : '' }}">
            <label class="tw-dw-form-control">
                <div class="tw-dw-label">
                    <span class="tw-text-xs md:tw-text-sm tw-font-medium tw-text-slate-700">@lang('lang_v1.username')</span>
                </div>
                <input class="japs-auth-input" name="username" required autofocus placeholder="@lang('lang_v1.username')" id="username" type="text" value="{{ $username }}" />
                @if ($errors->has('username'))
                    <span class="help-block"><strong>{{ $errors->first('username') }}</strong></span>
                @endif
            </label>
        </div>

        <div class="form-group has-feedback {{ $errors->has('password') ? ' has-error' : '' }}">
            <label class="tw-dw-form-control" style="position:relative;">
                <div class="tw-dw-label tw-flex tw-justify-between tw-items-center">
                    <span class="tw-text-xs md:tw-text-sm tw-font-medium tw-text-slate-700">@lang('lang_v1.password')</span>
                    @if (config('app.env') != 'demo')
                        <a href="{{ route('password.request') }}" class="tw-text-xs tw-font-medium tw-text-sky-700 hover:tw-underline" tabindex="-1">@lang('lang_v1.forgot_your_password')</a>
                    @endif
                </div>
                <input class="japs-auth-input japs-auth-input--password" id="password" type="password" name="password" value="{{ $password }}" required placeholder="@lang('lang_v1.password')" />
                <button type="button" id="show_hide_icon" class="japs-auth-toggle-pw" style="position:absolute;right:10px;bottom:14px;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="tw-w-5 tw-h-5 tw-text-slate-500" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/></svg>
                </button>
                @if ($errors->has('password'))
                    <span class="help-block"><strong>{{ $errors->first('password') }}</strong></span>
                @endif
            </label>
        </div>

        <div class="tw-dw-form-control tw-mb-4">
            <label class="tw-dw-cursor-pointer tw-dw-label tw-self-start tw-gap-2">
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }} class="tw-dw-checkbox">
                <span class="tw-text-sm tw-font-medium tw-text-slate-600">@lang('lang_v1.remember_me')</span>
            </label>
        </div>

        @if (config('constants.enable_recaptcha'))
            <div class="form-group tw-mb-4">
                <div class="g-recaptcha" data-sitekey="{{ config('constants.google_recaptcha_key') }}"></div>
                @if ($errors->has('g-recaptcha-response'))
                    <span class="text-danger">{{ $errors->first('g-recaptcha-response') }}</span>
                @endif
            </div>
        @endif

        <button type="submit" class="japs-auth-btn tw-mt-2">@lang('lang_v1.login')</button>
    </form>
@endsection

@section('auth-footer')
    @if (!($request->segment(1) == 'business' && $request->segment(2) == 'register'))
        @if (config('constants.allow_registration'))
            <p class="tw-text-center tw-text-sm tw-text-slate-500 tw-mb-0">
                {{ __('business.not_yet_registered') }}
                <a href="{{ route('business.getRegister') }}@if (!empty(request()->lang)){{ '?lang=' . request()->lang }}@endif" class="tw-font-semibold tw-text-sky-700 hover:tw-underline">{{ __('business.register_now') }}</a>
            </p>
        @endif
    @endif
@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#show_hide_icon').off('click');
            $('.change_lang').click(function() {
                window.location = "{{ route('login') }}?lang=" + $(this).attr('value');
            });
            $('a.demo-login').click(function(e) {
                e.preventDefault();
                $('#username').val($(this).data('admin'));
                $('#password').val("{{ $password }}");
                $('form#login-form').submit();
            });
            $('#show_hide_icon').on('click', function(e) {
                e.preventDefault();
                const passwordInput = $('#password');
                if (passwordInput.attr('type') === 'password') {
                    passwordInput.attr('type', 'text');
                    $('#show_hide_icon').html('<svg xmlns="http://www.w3.org/2000/svg" class="tw-w-5 tw-h-5 tw-text-slate-500" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z"/><path d="M10.585 10.587a2 2 0 0 0 2.829 2.828"/><path d="M16.681 16.673a8.717 8.717 0 0 1 -4.681 1.327c-3.6 0 -6.6 -2 -9 -6c1.272 -2.12 2.712 -3.678 4.32 -4.674m2.86 -1.146a9.055 9.055 0 0 1 1.82 -.18c3.6 0 6.6 2 9 6c-.666 1.11 -1.379 2.067 -2.138 2.87"/><path d="M3 3l18 18"/></svg>');
                } else {
                    passwordInput.attr('type', 'password');
                    $('#show_hide_icon').html('<svg xmlns="http://www.w3.org/2000/svg" class="tw-w-5 tw-h-5 tw-text-slate-500" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/></svg>');
                }
            });
        });
    </script>
@endsection
