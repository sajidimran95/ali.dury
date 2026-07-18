@inject('request', 'Illuminate\Http\Request')
@php
    $show_sign_in = $show_sign_in ?? (! in_array($request->segment(1), ['login', 'password'], true));
@endphp
<div class="japs-auth-topbar">
    <a href="{{ $home_url ?? url('/') }}" class="japs-auth-logo">
        <img src="{{ getAuthLogoUrl() }}" alt="{{ config('app.name') }}" />
    </a>
    <div class="tw-flex tw-items-center tw-gap-4">
        @if ($show_sign_in)
            <a href="{{ action([\App\Http\Controllers\Auth\LoginController::class, 'login']) }}@if (!empty(request()->lang)){{ '?lang=' . request()->lang }}@endif"
                class="tw-hidden sm:tw-inline-flex tw-items-center tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-white tw-rounded-full tw-border tw-border-white/40 hover:tw-bg-white/10 tw-transition-colors">
                {{ __('business.sign_in') }}
            </a>
        @endif
        @include('layouts.partials.language_btn')
    </div>
</div>
