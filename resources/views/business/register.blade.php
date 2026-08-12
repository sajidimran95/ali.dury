@extends('layouts.auth-shell')

@section('title', __('lang_v1.register'))

@section('auth-wide')
@endsection

@section('auth-brand')
    @include('layouts.partials.auth-brand', [
        'brand_subtitle' => __('business.register_and_get_started_in_minutes'),
        'brand_items' => [
            [
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
                'text' => __('business.business'),
            ],
            [
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
                'text' => __('business.register'),
            ],
        ],
    ])
@endsection

@section('auth-title')
    {{ config('app.name', 'Japs POS') }}
@endsection

@section('auth-subtitle')
    @lang('business.register_and_get_started_in_minutes')
@endsection

@section('auth-content')
    <div class="japs-auth-register-form">
        {!! Form::open([
            'url' => route('business.postRegister'),
            'method' => 'post',
            'id' => 'business_register_form',
            'files' => true,
        ]) !!}
        @include('business.partials.register_form', ['is_register' => true])
        {!! Form::hidden('package_id', $package_id) !!}
        {!! Form::close() !!}
    </div>
@endsection

@section('auth-footer')
    <p class="tw-text-center tw-text-sm tw-text-slate-500 tw-mb-0">
        @lang('business.already_registered')
        <a href="{{ route('login') }}" class="tw-font-semibold tw-text-sky-700 hover:tw-underline">@lang('business.sign_in')</a>
    </p>
@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $('.change_lang').click(function() {
                window.location = "{{ route('business.getRegister') }}?lang=" + $(this).attr('value');
            });
        });
    </script>
@endsection
