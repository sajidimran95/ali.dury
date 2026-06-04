@extends('layouts.auth-shell')

@section('title', config('app.name', 'Japs POS'))

@section('auth-brand')
    @include('layouts.partials.auth-brand', [
        'brand_subtitle' => env('APP_TITLE', __('lang_v1.welcome_back')),
    ])
@endsection

@section('auth-title')
    {{ config('app.name', 'Japs POS') }}
@endsection

@section('auth-subtitle')
    {{ env('APP_TITLE', '') }}
@endsection

@section('auth-content')
    <div class="tw-text-center tw-py-4">
        <p class="tw-text-slate-600 tw-mb-6">@lang('lang_v1.login_to_your') {{ config('app.name') }}</p>
        <a href="{{ route('login') }}" class="japs-auth-btn tw-inline-flex tw-items-center tw-justify-center tw-no-underline" style="color:#fff;">
            @lang('lang_v1.login')
        </a>
        @if (config('constants.allow_registration'))
            <a href="{{ route('business.getRegister') }}"
                class="tw-block tw-mt-4 tw-text-sm tw-font-semibold tw-text-sky-700 hover:tw-underline">
                {{ __('business.register_now') }}
            </a>
        @endif
    </div>
@endsection
