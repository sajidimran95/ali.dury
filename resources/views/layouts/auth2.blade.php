<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $__icons = app_pwa_icons();
    @endphp
    <meta name="theme-color" content="{{ $__icons['theme'] }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="{{ $__icons['title'] }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ $__icons['icon192'] }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ $__icons['icon512'] }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ $__icons['apple'] }}">
    <title>@yield('title') - {{ config('app.name', 'POS') }}</title>
    @include('layouts.partials.css')
    @include('layouts.partials.extracss_auth')
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    @if (config('constants.enable_recaptcha'))
        <script src="https://www.google.com/recaptcha/api.js"></script>
    @endif
</head>

<body class="pace-done japs-auth-body">
    @inject('request', 'Illuminate\Http\Request')
    @if (session('status') && session('status.success'))
        <input type="hidden" id="status_span" data-status="{{ session('status.success') }}"
            data-msg="{{ session('status.msg') }}">
    @endif

    @yield('content')

    @include('layouts.partials.javascripts')
    <script src="{{ asset('js/login.js?v=' . $asset_v) }}"></script>
    @yield('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            if ($('.select2_register').length) {
                $('.select2_register').select2();
            }
        });
    </script>
</body>

</html>
