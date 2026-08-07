{{-- Shared JAPS error layout — self-contained (inline CSS) so 500 still looks right --}}
@php
    $status = (int) ($status ?? 500);
    $title = $title ?? 'Something went wrong';
    $message = $message ?? 'An unexpected error occurred. Please try again in a moment.';
    $hint = $hint ?? null;
    $appName = config('app.name', 'JAPS POS');
    try {
        $bizName = session('business.name');
        if (! empty($bizName)) {
            $appName = $bizName;
        }
    } catch (\Throwable $e) {
        // session may be unavailable during hard failures
    }
    $homeUrl = url('/');
    try {
        if (auth()->check()) {
            $homeUrl = url('/home');
        }
    } catch (\Throwable $e) {
        //
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0a0a0c">
    <title>{{ $status }} · {{ $title }} · {{ $appName }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Syne:wght@700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #07070a;
            --bg-2: #121218;
            --text: #f4f4f7;
            --muted: #a1a1b5;
            --accent: #9b6bff;
            --accent-2: #5b8def;
            --line: rgba(255, 255, 255, 0.08);
            --btn: #f4f4f7;
            --btn-text: #0a0a0c;
            --glow: rgba(155, 107, 255, 0.28);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            min-height: 100%;
            height: 100%;
        }

        body {
            font-family: 'Outfit', system-ui, sans-serif;
            color: var(--text);
            background: var(--bg);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        .error-page {
            min-height: 100%;
            min-height: 100dvh;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: clamp(1.25rem, 4vw, 3rem);
            isolation: isolate;
        }

        /* Atmosphere — full-bleed color planes (not flat black) */
        .error-page::before {
            content: '';
            position: fixed;
            inset: 0;
            z-index: -2;
            background:
                radial-gradient(ellipse 80% 55% at 15% 10%, rgba(155, 107, 255, 0.22), transparent 55%),
                radial-gradient(ellipse 70% 50% at 90% 85%, rgba(91, 141, 239, 0.16), transparent 50%),
                radial-gradient(ellipse 50% 40% at 50% 50%, rgba(30, 30, 40, 0.9), transparent 70%),
                linear-gradient(165deg, #0a0a0f 0%, #07070a 45%, #0d0d14 100%);
        }

        .error-page::after {
            content: '';
            position: fixed;
            inset: 0;
            z-index: -1;
            opacity: 0.35;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.05'/%3E%3C/svg%3E");
            pointer-events: none;
        }

        .error-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(50px);
            opacity: 0.45;
            pointer-events: none;
            z-index: 0;
            animation: orbDrift 14s ease-in-out infinite alternate;
        }

        .error-orb--a {
            width: min(40vw, 280px);
            height: min(40vw, 280px);
            background: var(--glow);
            top: 8%;
            right: 8%;
        }

        .error-orb--b {
            width: min(35vw, 220px);
            height: min(35vw, 220px);
            background: rgba(91, 141, 239, 0.25);
            bottom: 12%;
            left: 6%;
            animation-delay: -4s;
            animation-duration: 18s;
        }

        @keyframes orbDrift {
            from { transform: translate(0, 0) scale(1); }
            to { transform: translate(-18px, 22px) scale(1.08); }
        }

        .error-brand {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: clamp(1.5rem, 4vw, 2.5rem);
            animation: riseIn 0.7s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        .error-brand img {
            height: clamp(36px, 6vw, 48px);
            width: auto;
            max-width: min(280px, 70vw);
            object-fit: contain;
            filter: drop-shadow(0 8px 24px rgba(0, 0, 0, 0.45));
        }

        .error-brand__text {
            font-family: 'Syne', 'Outfit', sans-serif;
            font-weight: 800;
            font-size: clamp(1.1rem, 3vw, 1.35rem);
            letter-spacing: 0.06em;
            text-transform: uppercase;
            background: linear-gradient(120deg, #c4a5ff 0%, #9b6bff 45%, #5b8def 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .error-shell {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 560px;
            text-align: center;
            animation: riseIn 0.85s cubic-bezier(0.22, 1, 0.36, 1) 0.08s both;
        }

        .error-code {
            font-family: 'Syne', 'Outfit', sans-serif;
            font-weight: 800;
            font-size: clamp(5.5rem, 18vw, 8.5rem);
            line-height: 0.9;
            letter-spacing: -0.04em;
            margin-bottom: 0.35rem;
            background: linear-gradient(180deg, rgba(255,255,255,0.95) 0%, rgba(255,255,255,0.12) 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            user-select: none;
            position: relative;
        }

        .error-code::after {
            content: attr(data-code);
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, var(--accent), var(--accent-2));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            opacity: 0.15;
            filter: blur(1px);
            transform: translateY(4px);
            z-index: -1;
        }

        .error-title {
            font-family: 'Syne', 'Outfit', sans-serif;
            font-weight: 700;
            font-size: clamp(1.35rem, 3.5vw, 1.85rem);
            letter-spacing: -0.02em;
            margin: 0.35rem 0 0.75rem;
            color: var(--text);
        }

        .error-message {
            font-size: clamp(0.95rem, 2.2vw, 1.05rem);
            line-height: 1.55;
            color: var(--muted);
            max-width: 38ch;
            margin: 0 auto 1.25rem;
            font-weight: 400;
        }

        .error-hint {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.8125rem;
            color: rgba(196, 165, 255, 0.9);
            background: rgba(155, 107, 255, 0.1);
            border: 1px solid rgba(155, 107, 255, 0.22);
            border-radius: 999px;
            padding: 0.4rem 0.9rem;
            margin-bottom: 1.75rem;
            animation: riseIn 1s cubic-bezier(0.22, 1, 0.36, 1) 0.18s both;
        }

        .error-hint svg {
            width: 14px;
            height: 14px;
            flex-shrink: 0;
            opacity: 0.9;
        }

        .error-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            justify-content: center;
            align-items: center;
            animation: riseIn 1s cubic-bezier(0.22, 1, 0.36, 1) 0.22s both;
        }

        .error-btn {
            appearance: none;
            border: 0;
            cursor: pointer;
            font-family: inherit;
            font-weight: 600;
            font-size: 0.9375rem;
            border-radius: 10px;
            padding: 0.8rem 1.35rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease, color 0.18s ease, border-color 0.18s ease;
        }

        .error-btn:active {
            transform: scale(0.98);
        }

        .error-btn--primary {
            background: var(--btn);
            color: var(--btn-text);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
        }

        .error-btn--primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 36px rgba(155, 107, 255, 0.25);
        }

        .error-btn--ghost {
            background: transparent;
            color: var(--text);
            border: 1px solid var(--line);
        }

        .error-btn--ghost:hover {
            border-color: rgba(255, 255, 255, 0.22);
            background: rgba(255, 255, 255, 0.04);
            transform: translateY(-1px);
        }

        .error-btn svg {
            width: 16px;
            height: 16px;
        }

        .error-foot {
            position: relative;
            z-index: 2;
            margin-top: clamp(2rem, 6vw, 3.5rem);
            font-size: 0.75rem;
            letter-spacing: 0.04em;
            color: rgba(161, 161, 181, 0.65);
            animation: riseIn 1.05s cubic-bezier(0.22, 1, 0.36, 1) 0.3s both;
        }

        .error-foot a {
            color: rgba(196, 165, 255, 0.85);
            text-decoration: none;
        }

        .error-foot a:hover {
            text-decoration: underline;
        }

        @keyframes riseIn {
            from {
                opacity: 0;
                transform: translateY(18px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .error-orb,
            .error-brand,
            .error-shell,
            .error-hint,
            .error-actions,
            .error-foot {
                animation: none !important;
            }
        }

        @media (max-width: 480px) {
            .error-actions {
                flex-direction: column;
                width: 100%;
            }
            .error-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
    @stack('head')
</head>
<body>
    <div class="error-page">
        <span class="error-orb error-orb--a" aria-hidden="true"></span>
        <span class="error-orb error-orb--b" aria-hidden="true"></span>

        <div class="error-brand">
            @if (file_exists(public_path('img/logo-small.png')))
                <img src="{{ asset('img/logo-small.png') }}" alt="{{ $appName }}" onerror="this.style.display='none';this.nextElementSibling.style.display='block';">
                <span class="error-brand__text" style="display:none">{{ $appName }}</span>
            @else
                <span class="error-brand__text">{{ $appName }}</span>
            @endif
        </div>

        <main class="error-shell" role="main">
            <div class="error-code" data-code="{{ $status }}" aria-hidden="true">{{ $status }}</div>
            <h1 class="error-title">{{ $title }}</h1>
            <p class="error-message">{{ $message }}</p>

            @if (! empty($hint))
                <div class="error-hint">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>
                    </svg>
                    <span>{{ $hint }}</span>
                </div>
            @endif

            <div class="error-actions">
                <a class="error-btn error-btn--primary" href="{{ $homeUrl }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V20a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V9.5"/>
                    </svg>
                    Go home
                </a>
                <button type="button" class="error-btn error-btn--ghost" onclick="if (history.length > 1) { history.back(); } else { window.location.href = {{ json_encode($homeUrl) }}; }">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>
                    </svg>
                    Go back
                </button>
                @if (! empty($extra_action))
                    {!! $extra_action !!}
                @endif
            </div>
        </main>

        <footer class="error-foot">
            {{ $appName }} · Error {{ $status }}
        </footer>
    </div>
</body>
</html>
