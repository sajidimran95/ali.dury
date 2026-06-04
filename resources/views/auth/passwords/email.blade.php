@extends('layouts.auth-shell')

@section('title', __('lang_v1.reset_password'))

@section('auth-brand')
    @include('layouts.partials.auth-brand', [
        'brand_subtitle' => __('lang_v1.send_password_reset_link'),
        'brand_items' => [
            [
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
                'text' => __('lang_v1.email_address'),
            ],
            [
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>',
                'text' => __('lang_v1.reset_password'),
            ],
        ],
    ])
@endsection

@section('auth-title')
    @lang('lang_v1.forgot_your_password')
@endsection

@section('auth-subtitle')
    @lang('lang_v1.send_password_reset_link')
@endsection

@section('auth-content')
    @if (session('status') && is_string(session('status')))
        <div class="japs-auth-alert" role="alert">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        {{ csrf_field() }}
        <div class="form-group has-feedback {{ $errors->has('email') ? ' has-error' : '' }}">
            <label class="tw-dw-form-control">
                <div class="tw-dw-label">
                    <span class="tw-text-xs md:tw-text-sm tw-font-medium tw-text-slate-700">@lang('lang_v1.email_address')</span>
                </div>
                <input id="email" type="email" class="japs-auth-input" name="email" value="{{ old('email') }}" required autofocus placeholder="@lang('lang_v1.email_address')">
                @if ($errors->has('email'))
                    <span class="help-block"><strong>{{ $errors->first('email') }}</strong></span>
                @endif
            </label>
        </div>

        <button type="submit" class="japs-auth-btn tw-mt-4">
            @lang('lang_v1.send_password_reset_link')
        </button>
    </form>
@endsection

@section('auth-footer')
    <p class="tw-text-center tw-text-sm tw-text-slate-500 tw-mb-0">
        <a href="{{ route('login') }}" class="tw-font-semibold tw-text-sky-700 hover:tw-underline">&larr; @lang('lang_v1.login')</a>
    </p>
@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $('.change_lang').click(function() {
                window.location = "{{ route('password.request') }}?lang=" + $(this).attr('value');
            });
        });
    </script>
@endsection
