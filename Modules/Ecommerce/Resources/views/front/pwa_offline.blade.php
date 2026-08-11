<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="{{ $theme_color ?? '#e85d04' }}">
    <title>Offline — {{ $app_name ?? 'Store' }}</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, sans-serif;
            background: {{ $background_color ?? '#fff' }};
            color: #1a1a1a;
            padding: 24px;
            text-align: center;
        }
        h1 { font-size: 1.25rem; margin: 0 0 8px; }
        p { margin: 0; color: #666; font-size: .9375rem; }
        a {
            display: inline-block; margin-top: 20px; padding: 12px 20px;
            background: {{ $theme_color ?? '#e85d04' }}; color: #fff;
            text-decoration: none; border-radius: 8px; font-weight: 600;
        }
    </style>
</head>
<body>
    <div>
        <h1>You’re offline</h1>
        <p>Check your connection, then try again.</p>
        <a href="{{ url('/') }}">Back to store</a>
    </div>
</body>
</html>
