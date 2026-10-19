<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Account') — {{ $ecom_settings->store_name ?? 'Store' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/gs-auth.css') }}?v=1">
    @stack('head')
</head>
<body class="gc-store ecom-auth antialiased">
@php
    $store = $ecom_settings->store_name ?? 'Japs Wholesale';
    $authLogoUrl = null;
    $gsMediaFile = storage_path('app/ecom_gs_media.json');
    if (is_file($gsMediaFile)) {
        $gsMedia = json_decode(file_get_contents($gsMediaFile), true) ?: [];
        $authLogoUrl = $gsMedia['logo'] ?? null;
    }
@endphp
@if(session('error') || $errors->any())
    <div class="auth-flash">{{ session('error') ?: $errors->first() }}</div>
@endif
<div class="auth-v2">
    <div class="auth-v2-left auth-v2-left--center">
        <a href="{{ route('ecommerce.home') }}" class="auth-v2-logo">
            @if($authLogoUrl)
                <img src="{{ $authLogoUrl }}" alt="{{ $store }}">
            @else
                <span style="color:#fff;font-size:20px">{{ $store }}</span>
            @endif
        </a>
        @yield('auth_steps')
        <div class="auth-v2-panel-footer">
            <a href="{{ route('ecommerce.page', 'about-us') }}" class="auth-v2-panel-link">Terms and Conditions</a>
            <a href="{{ route('ecommerce.page', 'privacy-policy') }}" class="auth-v2-panel-link">Privacy Policy</a>
            <a href="{{ route('ecommerce.contact') }}" class="auth-v2-panel-link">Contact Us</a>
        </div>
    </div>
    <div class="auth-v2-content">
        @yield('content')
    </div>
</div>
@stack('scripts')
<script>
document.querySelectorAll('form[method="post"], form[method="POST"]').forEach(function (form) {
    form.addEventListener('submit', function () {
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (!meta) return;
        var input = form.querySelector('input[name="_token"]');
        if (input) input.value = meta.getAttribute('content');
    });
});
</script>
</body>
</html>
