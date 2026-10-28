<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=5">
    <title>{{ $title ?? 'Invoice' }} — {{ config('app.name') }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            background: #e8edf3;
            font-family: system-ui, -apple-system, sans-serif;
            color: #0b1220;
        }
        .ca-inv-bar {
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            gap: 8px;
            align-items: center;
            justify-content: space-between;
            padding: 10px 12px;
            padding-top: max(10px, env(safe-area-inset-top, 0px));
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
        }
        .ca-inv-bar a,
        .ca-inv-bar button {
            appearance: none;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #0b1220;
            border-radius: 10px;
            padding: 8px 12px;
            font-weight: 700;
            font-size: 13px;
            text-decoration: none;
            cursor: pointer;
        }
        .ca-inv-bar .primary {
            background: #dc2626;
            border-color: #dc2626;
            color: #fff;
        }
        .ca-inv-stage {
            padding: 12px;
            padding-bottom: calc(20px + env(safe-area-inset-bottom, 0px));
        }
        .ca-inv-wrap {
            max-width: 900px;
            margin: 0 auto;
            padding: 16px;
            background: #fff;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
        }
        @media (max-width: 767px) {
            .ca-inv-stage {
                padding: 8px;
                padding-bottom: calc(16px + env(safe-area-inset-bottom, 0px));
                overflow-x: hidden;
            }
            .ca-inv-scale-host {
                width: 100%;
                overflow: hidden;
            }
            .ca-inv-wrap {
                max-width: none;
                margin: 0;
                padding: 0;
                box-shadow: none;
                background: #fff;
                transform-origin: top left;
                width: 8.5in;
            }
            .ca-inv-wrap .usl-wrap {
                max-width: none !important;
                width: 100% !important;
            }
        }
        @media print {
            .ca-inv-bar { display: none !important; }
            body { background: #fff; }
            .ca-inv-stage { padding: 0 !important; overflow: visible !important; }
            .ca-inv-scale-host {
                width: auto !important;
                height: auto !important;
                overflow: visible !important;
            }
            .ca-inv-wrap {
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                max-width: none !important;
                width: auto !important;
                transform: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="ca-inv-bar">
        <a href="{{ route('customer.documents') }}">← Documents</a>
        <div style="display:flex;gap:8px;">
            <button type="button" class="primary" onclick="if (window.__qbPaginateInvoice) { window.__qbPaginateInvoice(); } window.print();">Print / PDF</button>
        </div>
    </div>
    <div class="ca-inv-stage">
        <div class="ca-inv-scale-host" id="caInvHost">
            <div class="ca-inv-wrap" id="receipt_section">
                {!! $html !!}
            </div>
        </div>
    </div>
    <script>
        (function () {
            var host = document.getElementById('caInvHost');
            var wrap = document.getElementById('receipt_section');
            if (!host || !wrap) return;

            function fitMobileInvoice() {
                if (window.matchMedia('(min-width: 768px)').matches) {
                    wrap.style.transform = '';
                    wrap.style.width = '';
                    host.style.height = '';
                    return;
                }
                var letterPx = 8.5 * 96;
                wrap.style.width = letterPx + 'px';
                wrap.style.transform = 'none';
                var available = host.clientWidth || (window.innerWidth - 16);
                var scale = Math.min(1, available / letterPx);
                if (scale <= 0) scale = 1;
                wrap.style.transformOrigin = 'top left';
                wrap.style.transform = 'scale(' + scale + ')';
                var fullH = wrap.scrollHeight || wrap.offsetHeight;
                host.style.height = Math.ceil(fullH * scale) + 'px';
            }

            fitMobileInvoice();
            window.addEventListener('resize', fitMobileInvoice);
            window.addEventListener('orientationchange', function () {
                setTimeout(fitMobileInvoice, 150);
            });
            wrap.querySelectorAll('img').forEach(function (img) {
                if (!img.complete) img.addEventListener('load', fitMobileInvoice);
            });
            setTimeout(fitMobileInvoice, 300);
        })();
    </script>
</body>
</html>
