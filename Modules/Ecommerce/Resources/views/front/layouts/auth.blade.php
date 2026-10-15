<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Wholesale Account') — {{ $ecom_settings->store_name ?? 'Store' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/gc-storefront.css') }}?v={{ config('app.asset_version', '1') }}&gc=23">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { brand: '#4caf79', ink: '#1a1a1a', slatebar: '#1a3d2b' },
                    fontFamily: { sans: ['Poppins', 'system-ui', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
        body { font-family: Poppins, system-ui, sans-serif; background: #f4f4f4; color: #1a1a1a; }
        .input-w {
            width: 100%; border: 1px solid #e6ebe8; border-radius: 8px; padding: .75rem .9rem; font-size: .875rem;
            background: #fff; color: #111; outline: none;
        }
        .input-w:focus { border-color: #4caf79; box-shadow: 0 0 0 3px rgba(76,175,121,.18); }
        .step-dot { width:10px;height:10px;border-radius:999px;background:#ccc; }
        .step-dot.active { background:#4caf79; }
        .step-panel { display:none; }
        .step-panel.active { display:block; }
    </style>
    @stack('head')
</head>
<body class="gc-store ecom-auth antialiased bg-[#f4f4f4] text-[#1a1a1a]">
@if(session('error'))
    <div class="bg-rose-50 text-rose-800 text-sm text-center py-2">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="bg-rose-50 text-rose-800 text-sm text-center py-2">{{ $errors->first() }}</div>
@endif
@yield('content')
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
