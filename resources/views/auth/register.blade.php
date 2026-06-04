@extends('layouts.auth-shell')

@section('title', __('lang_v1.register'))

@section('auth-title')
    @lang('business.register')
@endsection

@section('auth-subtitle')
    @lang('business.register_and_get_started_in_minutes')
@endsection

@section('auth-content')
    <p class="tw-text-slate-600 tw-text-center tw-mb-6">@lang('business.register_and_get_started_in_minutes')</p>
    <a href="{{ route('business.getRegister') }}@if (!empty(request()->lang)){{ '?lang=' . request()->lang }}@endif" class="japs-auth-btn tw-inline-flex tw-items-center tw-justify-center tw-no-underline" style="color:#fff;">
        @lang('business.register_now')
    </a>
@endsection

@section('auth-footer')
    <p class="tw-text-center tw-text-sm tw-text-slate-500 tw-mb-0">
        @lang('business.already_registered')
        <a href="{{ route('login') }}" class="tw-font-semibold tw-text-sky-700 hover:tw-underline">@lang('business.sign_in')</a>
    </p>
@endsection
