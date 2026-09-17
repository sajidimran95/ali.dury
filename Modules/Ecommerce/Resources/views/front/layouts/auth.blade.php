<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Wholesale Account') — {{ $ecom_settings->store_name ?? 'Store' }}</title>
    <link rel="stylesheet" href="{{ asset('fonts/greencloud/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/greencloud-store.css') }}">
    <link rel="stylesheet" href="{{ asset('css/greencloud-utilities.css') }}">
    <link rel="stylesheet" href="{{ asset('css/greencloud-theme.css') }}">
    <style>
        body { font-family: Poppins, system-ui, sans-serif; }
        .input-w {
            width: 100%; border: 1px solid #d4d4d4; border-radius: 8px; padding: .75rem .9rem; font-size: .875rem;
            background: #fff; outline: none;
        }
        .input-w:focus { border-color: #4CAF79; box-shadow: 0 0 0 3px rgba(76,175,121,.15); }
        .btn-brand { background:#4CAF79;color:#fff;font-weight:600;border-radius:8px;padding:.75rem 1.5rem; }
        .btn-brand:hover { background:#2E6A49; }
        .step-dot { width:10px;height:10px;border-radius:999px;background:#555; }
        .step-dot.active { background:#4CAF79; }
        .step-panel { display:none; }
        .step-panel.active { display:block; }
    </style>
    @stack('head')
</head>
<body class="antialiased bg-white text-ink">
@if(session('error'))
    <div class="bg-rose-50 text-rose-800 text-sm text-center py-2">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="bg-rose-50 text-rose-800 text-sm text-center py-2">{{ $errors->first() }}</div>
@endif
@yield('content')
@stack('scripts')
</body>
</html>
