<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="{{ $theme_color }}">
    <title>{{ $app_name }} — Offline</title>
    <style>
        :root {
            --bg: {{ $background_color }};
            --fg: #f5f5f5;
            --accent: #3b8ddd;
            --muted: #9aa3b2;
        }
        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            min-height: 100%;
            min-height: 100dvh;
            background: var(--bg);
            color: var(--fg);
            font-family: "Segoe UI", system-ui, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: max(24px, env(safe-area-inset-top)) max(24px, env(safe-area-inset-right)) max(24px, env(safe-area-inset-bottom)) max(24px, env(safe-area-inset-left));
            text-align: center;
        }
        .card { max-width: 420px; }
        .mark {
            width: 72px;
            height: 72px;
            margin: 0 auto 20px;
            border-radius: 18px;
            background: linear-gradient(145deg, #2a2a2a, #141414);
            border: 1px solid #333;
            display: grid;
            place-items: center;
            font-size: 28px;
            font-weight: 700;
            color: var(--accent);
        }
        h1 { font-size: 1.35rem; margin: 0 0 8px; font-weight: 650; }
        p { margin: 0 0 22px; color: var(--muted); line-height: 1.5; font-size: 0.95rem; }
        button {
            appearance: none;
            border: 0;
            border-radius: 10px;
            background: var(--accent);
            color: #fff;
            font-weight: 600;
            font-size: 0.95rem;
            padding: 12px 22px;
            cursor: pointer;
        }
        button:active { transform: scale(0.98); }
    </style>
</head>
<body>
    <div class="card">
        <div class="mark">POS</div>
        <h1>{{ $app_name }}</h1>
        <p>You appear to be offline. Check your connection, then try again to open the POS terminal.</p>
        <button type="button" onclick="location.reload()">Try again</button>
    </div>
</body>
</html>
