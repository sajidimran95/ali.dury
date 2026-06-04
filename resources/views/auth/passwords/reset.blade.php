@extends('layouts.auth-shell')

@section('title', __('lang_v1.reset_password'))

@section('auth-brand')
    @include('layouts.partials.auth-brand', [
        'brand_subtitle' => __('lang_v1.reset_password'),
        'brand_items' => [
            [
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>',
                'text' => __('lang_v1.password'),
            ],
            [
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>',
                'text' => __('business.confirm_password'),
            ],
        ],
    ])
@endsection

@section('auth-title')
    @lang('lang_v1.reset_password')
@endsection

@section('auth-content')
    <form method="POST" action="{{ route('password.update') }}">
        {{ csrf_field() }}
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="form-group has-feedback {{ $errors->has('email') ? ' has-error' : '' }}">
            <label class="tw-dw-form-control">
                <div class="tw-dw-label">
                    <span class="tw-text-xs md:tw-text-sm tw-font-medium tw-text-slate-700">@lang('lang_v1.email_address')</span>
                </div>
                <input id="email" type="email" class="japs-auth-input" name="email" value="{{ $email ?? old('email') }}" required autofocus placeholder="@lang('lang_v1.email_address')">
                @if ($errors->has('email'))
                    <span class="help-block"><strong>{{ $errors->first('email') }}</strong></span>
                @endif
            </label>
        </div>

        <div class="form-group has-feedback {{ $errors->has('password') ? ' has-error' : '' }}">
            <label class="tw-dw-form-control" style="position:relative;">
                <div class="tw-dw-label">
                    <span class="tw-text-xs md:tw-text-sm tw-font-medium tw-text-slate-700">@lang('lang_v1.password')</span>
                </div>
                <input id="password" type="password" class="japs-auth-input japs-auth-input--password" name="password" required placeholder="@lang('lang_v1.password')">
                <button type="button" class="japs-auth-toggle-pw" data-target="#password" tabindex="-1" aria-label="Toggle password">
                    <svg xmlns="http://www.w3.org/2000/svg" class="tw-w-5 tw-h-5" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/></svg>
                </button>
                @if ($errors->has('password'))
                    <span class="help-block"><strong>{{ $errors->first('password') }}</strong></span>
                @endif
            </label>
        </div>

        <div class="form-group has-feedback {{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
            <label class="tw-dw-form-control" style="position:relative;">
                <div class="tw-dw-label">
                    <span class="tw-text-xs md:tw-text-sm tw-font-medium tw-text-slate-700">@lang('business.confirm_password')</span>
                </div>
                <input id="password_confirmation" type="password" class="japs-auth-input japs-auth-input--password" name="password_confirmation" required placeholder="@lang('business.confirm_password')">
                <button type="button" class="japs-auth-toggle-pw" data-target="#password_confirmation" tabindex="-1" aria-label="Toggle password">
                    <svg xmlns="http://www.w3.org/2000/svg" class="tw-w-5 tw-h-5" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/></svg>
                </button>
                @if ($errors->has('password_confirmation'))
                    <span class="help-block"><strong>{{ $errors->first('password_confirmation') }}</strong></span>
                @endif
            </label>
        </div>

        <button type="submit" class="japs-auth-btn tw-mt-2">
            @lang('lang_v1.reset_password')
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
            $('.japs-auth-toggle-pw').on('click', function(e) {
                e.preventDefault();
                var $input = $($(this).data('target'));
                var type = $input.attr('type') === 'password' ? 'text' : 'password';
                $input.attr('type', type);
            });
        });
    </script>
@endsection
