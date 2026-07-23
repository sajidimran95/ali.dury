<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Wholesale Account') — {{ $ecom_settings->store_name ?? 'Store' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { brand: '#e85d04', ink: '#1a1a1a', slatebar: '#1f1f1f' },
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
        body { font-family: Inter, system-ui, sans-serif; }
        .input-w {
            width: 100%; border: 1px solid #d4d4d4; border-radius: 8px; padding: .75rem .9rem; font-size: .875rem;
            background: #fff; outline: none;
        }
        .input-w:focus { border-color: #e85d04; box-shadow: 0 0 0 3px rgba(232,93,4,.15); }
        .btn-brand { background:#e85d04;color:#fff;font-weight:600;border-radius:8px;padding:.75rem 1.5rem; }
        .btn-brand:hover { background:#c44d03; }
        .step-dot { width:10px;height:10px;border-radius:999px;background:#555; }
        .step-dot.active { background:#e85d04; }
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
