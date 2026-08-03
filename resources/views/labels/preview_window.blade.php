<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('barcode.preview') }} — {{ __('barcode.print_labels') }}</title>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            background: #f3f4f6;
            font-family: Arial, Helvetica, sans-serif;
        }

        /* On-screen preview toolbar (hidden when printing) */
        #label_preview_toolbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            padding: 12px 18px;
            background: #111827;
            color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        #label_preview_toolbar .toolbar-title {
            font-size: 16px;
            font-weight: 700;
            margin: 0;
        }

        #label_preview_toolbar .toolbar-hint {
            margin: 2px 0 0;
            font-size: 12px;
            color: #d1d5db;
            font-weight: 400;
        }

        #label_preview_toolbar .toolbar-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        #label_preview_toolbar .btn-print {
            appearance: none;
            border: 0;
            border-radius: 8px;
            background: #16a34a;
            color: #fff;
            font-weight: 700;
            font-size: 14px;
            padding: 10px 20px;
            cursor: pointer;
        }

        #label_preview_toolbar .btn-print:hover {
            background: #15803d;
        }

        #label_preview_toolbar .btn-close {
            appearance: none;
            border: 1px solid #4b5563;
            border-radius: 8px;
            background: transparent;
            color: #fff;
            font-weight: 600;
            font-size: 14px;
            padding: 10px 16px;
            cursor: pointer;
        }

        #label_preview_toolbar .btn-close:hover {
            background: #1f2937;
        }

        #label_preview_content {
            padding: 20px 16px 40px;
            background: #f3f4f6;
        }

        @media print {
            html, body {
                background: #fff !important;
            }

            #label_preview_toolbar {
                display: none !important;
            }

            #label_preview_content {
                padding: 0 !important;
                background: #fff !important;
            }
        }
    </style>
</head>
<body>
    <div id="label_preview_toolbar" class="no-print">
        <div>
            <p class="toolbar-title">{{ __('barcode.preview') }}</p>
            <p class="toolbar-hint">{{ __('barcode.preview_before_print_hint') }}</p>
        </div>
        <div class="toolbar-actions">
            <button type="button" class="btn-close" onclick="window.close()">{{ __('messages.close') }}</button>
            <button type="button" class="btn-print" id="print_labels_btn" onclick="window.print()">
                {{ __('barcode.print_labels') }}
            </button>
        </div>
    </div>

    <div id="label_preview_content">
        {!! $content !!}
    </div>
</body>
</html>
