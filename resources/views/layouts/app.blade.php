@inject('request', 'Illuminate\Http\Request')

@if (
    $request->segment(1) == 'pos' &&
        ($request->segment(2) == 'create' || $request->segment(3) == 'edit' || $request->segment(2) == 'payment'))
    @php
        $pos_layout = true;
    @endphp
@else
    @php
        $pos_layout = false;
    @endphp
@endif

@php
    $dashboard_home = in_array($request->segment(1), ['home', 'sales-dashboard'], true);
    $home_ops = $request->segment(1) == 'home';
    // Premium canvas for all non-POS pages (dashboards + rest of app)
    $dashboard_premium = ! $pos_layout;
    // Every list table outside POS: one short line per column (css/report-tables.css)
    $list_tables = ! $pos_layout;
@endphp

@php
    $whitelist = ['127.0.0.1', '::1'];
@endphp

<!DOCTYPE html>
<html class="tw-bg-white tw-scroll-smooth" lang="{{ app()->getLocale() }}"
    dir="{{ in_array(session()->get('user.language', config('app.locale')), config('constants.langs_rtl')) ? 'rtl' : 'ltr' }}">
<head>
    <!-- Tell the browser to be responsive to screen width -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover"
        name="viewport">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $__pos_pwa = [];
        if ($pos_layout) {
            if (!isset($pos_settings) || !is_array($pos_settings)) {
                $__biz = session('business');
                $__raw = is_object($__biz) ? ($__biz->pos_settings ?? null) : null;
                $pos_settings = !empty($__raw) ? (json_decode($__raw, true) ?: []) : [];
            }
            $__pwa_on = !array_key_exists('enable_pwa', $pos_settings) || !empty($pos_settings['enable_pwa']);
            if ($__pwa_on) {
                $__biz_name = Session::get('business.name') ?? config('app.name');
                $__pos_pwa = [
                    'enabled' => true,
                    'app_name' => trim((string) ($pos_settings['pwa_app_name'] ?? '')) ?: $__biz_name,
                    'theme_color' => $pos_settings['pwa_theme_color'] ?? '#0f0f0f',
                    'background_color' => $pos_settings['pwa_background_color'] ?? '#0f0f0f',
                    'icon' => asset('pwa/icon-192.png'),
                    'apple_icon' => asset('pwa/apple-touch-icon.png'),
                ];
                if (!empty($pos_settings['pwa_icon']) && file_exists(public_path('uploads/pwa_icons/' . $pos_settings['pwa_icon']))) {
                    $__pos_pwa['icon'] = asset('uploads/pwa_icons/' . $pos_settings['pwa_icon']);
                    $__pos_pwa['apple_icon'] = $__pos_pwa['icon'];
                } elseif (function_exists('japs_logo_url')) {
                    // keep default icons
                }
            }
        }
    @endphp

    @if (!empty($__pos_pwa['enabled']))
        <meta name="theme-color" content="{{ $__pos_pwa['theme_color'] }}">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="{{ \Illuminate\Support\Str::limit($__pos_pwa['app_name'], 12, '') }}">
        <meta name="application-name" content="{{ $__pos_pwa['app_name'] }}">
        <link rel="manifest" href="{{ route('pos.pwa.manifest', ['business_id' => session('user.business_id')]) }}">
        <link rel="apple-touch-icon" href="{{ $__pos_pwa['apple_icon'] }}">
        <link rel="icon" type="image/png" sizes="192x192" href="{{ $__pos_pwa['icon'] }}">
    @endif
    
    <title>@yield('title') - {{ Session::get('business.name') }}</title>

    @include('layouts.partials.css')

    @include('layouts.partials.extracss')

</head>
<body
    class="tw-font-sans tw-antialiased tw-text-gray-900 zoho-theme @if ($pos_layout) hold-transition lockscreen pos-sale-theme @php
        $__pos_screen_layout = 'ltr';
        // Prefer fresh controller model, then session business
        if (isset($business_details) && !empty($business_details->pos_screen_layout)) {
            $__pos_screen_layout = strtolower((string) $business_details->pos_screen_layout) === 'rtl' ? 'rtl' : 'ltr';
        } else {
            $__biz_sess = session('business');
            if (is_object($__biz_sess) && !empty($__biz_sess->pos_screen_layout)) {
                $__pos_screen_layout = strtolower((string) $__biz_sess->pos_screen_layout) === 'rtl' ? 'rtl' : 'ltr';
            }
        }
    @endphp pos-layout-{{ $__pos_screen_layout }} @elseif(!empty($home_ops)) hold-transition japs-dashboard-home japs-home-ops japs-dashboard-premium sidebar-mini @elseif(!empty($dashboard_home)) hold-transition japs-dashboard-home japs-dashboard-premium sidebar-mini @elseif(!empty($dashboard_premium)) hold-transition japs-dashboard-premium sidebar-mini @else hold-transition skin-@if (!empty(session('business.theme_color'))){{ session('business.theme_color') }}@else{{ 'blue-light' }} @endif sidebar-mini @endif @if (!empty($list_tables)) japs-list-tables @endif" >
    <div class="tw-flex thetop">
        <script type="text/javascript">
            if (localStorage.getItem("upos_sidebar_collapse") == 'true') {
                var body = document.getElementsByTagName("body")[0];
                body.className += " sidebar-collapse zoho-sidebar-collapsed";
            }
        </script>

        @if (in_array($_SERVER['REMOTE_ADDR'], $whitelist))
            <input type="hidden" id="__is_localhost" value="true">
        @endif

        <!-- Add currency related field-->
        <input type="hidden" id="__code" value="{{ session('currency')['code'] }}">
        <input type="hidden" id="__symbol" value="{{ session('currency')['symbol'] }}">
        <input type="hidden" id="__thousand" value="{{ session('currency')['thousand_separator'] }}">
        <input type="hidden" id="__decimal" value="{{ session('currency')['decimal_separator'] }}">
        <input type="hidden" id="__symbol_placement" value="{{ session('business.currency_symbol_placement') }}">
        <input type="hidden" id="__precision" value="{{ session('business.currency_precision', 2) }}">
        <input type="hidden" id="__quantity_precision" value="{{ session('business.quantity_precision', 2) }}">
        <!-- End of currency related field-->
        @can('view_export_buttons')
            <input type="hidden" id="view_export_buttons">
        @endcan
        @if (isMobile())
            <input type="hidden" id="__is_mobile">
        @endif
        @if (session('status'))
            <input type="hidden" id="status_span" data-status="{{ session('status.success') }}"
                data-msg="{{ session('status.msg') }}">
        @endif

        @if($request->segment(1) != 'customer-display' && !$pos_layout)
            @include('layouts.partials.header')
        @elseif($request->segment(1) != 'customer-display')
            @include('layouts.partials.header-pos')
        @endif

        <div class="zoho-app-body">
        @if (!$pos_layout && $request->segment(1) != 'customer-display')
            @include('layouts.partials.sidebar')
        @endif

        <main class="zoho-main-column tw-flex tw-flex-col tw-flex-1 tw-h-full tw-min-w-0">
            <!-- empty div for vuejs -->
            <div id="app">
                @yield('vue')
            </div>
            <div class="tw-flex-1 tw-overflow-y-auto tw-h-screen" id="scrollable-container">
                @yield('content')
                @if (!$pos_layout)
                
                    @include('layouts.partials.footer')
                @else
                    @include('layouts.partials.footer_pos')
                @endif
            </div>
            <div class='scrolltop no-print'>
                <div class='scroll icon'><i class="fas fa-angle-up"></i></div>
            </div>

            @if (config('constants.iraqi_selling_price_adjustment'))
                <input type="hidden" id="iraqi_selling_price_adjustment">
            @endif

            <!-- This will be printed -->
            <section class="invoice print_section" id="receipt_section">
            </section>
        </main>
        </div>{{-- /.zoho-app-body --}}

        @include('home.todays_profit_modal')
        <!-- /.content-wrapper -->



        <audio id="success-audio">
            <source src="{{ asset('/audio/success.ogg?v=' . $asset_v) }}" type="audio/ogg">
            <source src="{{ asset('/audio/success.mp3?v=' . $asset_v) }}" type="audio/mpeg">
        </audio>
        <audio id="error-audio">
            <source src="{{ asset('/audio/error.ogg?v=' . $asset_v) }}" type="audio/ogg">
            <source src="{{ asset('/audio/error.mp3?v=' . $asset_v) }}" type="audio/mpeg">
        </audio>
        <audio id="warning-audio">
            <source src="{{ asset('/audio/warning.ogg?v=' . $asset_v) }}" type="audio/ogg">
            <source src="{{ asset('/audio/warning.mp3?v=' . $asset_v) }}" type="audio/mpeg">
        </audio>

        @if (!empty($__additional_html))
            {!! $__additional_html !!}
        @endif

        @include('layouts.partials.javascripts')

        @if (!empty($list_tables))
            <script>
                (function () {
                    function isEntryTable(node) {
                        return node.closest && node.closest('#purchase_entry_table, #pos_table, .direct-sell-product-table, .table-pdf, .fc');
                    }
                    function flattenReportBreaks(root) {
                        var scope = root && root.querySelectorAll ? root : document;
                        var nodes = scope.querySelectorAll('br');
                        for (var i = 0; i < nodes.length; i++) {
                            var br = nodes[i];
                            if (!br.closest || !br.closest('table') || isEntryTable(br)) {
                                continue;
                            }
                            br.replaceWith(document.createTextNode(' '));
                        }
                    }
                    function titleLongCells(root) {
                        var scope = root && root.querySelectorAll ? root : document;
                        var cells = scope.querySelectorAll('th, td');
                        for (var i = 0; i < cells.length; i++) {
                            var cell = cells[i];
                            if (isEntryTable(cell)) {
                                continue;
                            }
                            if (cell.querySelector('.btn, .tw-dw-btn, .btn-group, .table-action-btns')) {
                                continue;
                            }
                            var text = (cell.textContent || '').replace(/\s+/g, ' ').trim();
                            if (text.length > 28) {
                                cell.setAttribute('title', text);
                            }
                        }
                    }
                    document.addEventListener('DOMContentLoaded', function () {
                        var content = document.querySelector('.content');
                        if (content) {
                            flattenReportBreaks(content);
                            titleLongCells(content);
                        }
                    });
                    if (window.jQuery) {
                        jQuery(document).on('draw.dt', function (e) {
                            flattenReportBreaks(e.target);
                            titleLongCells(e.target);
                        });
                    }
                })();
            </script>
        @endif

        {{-- Module JS --}}
        @include('layouts.module-assets')

        @if (!empty($__pos_pwa['enabled']))
            <script>
                window.__POS_PWA__ = {
                    enabled: true,
                    swUrl: @json(route('pos.pwa.sw')),
                    iosHint: @json(__('lang_v1.pos_pwa_ios_hint')),
                    installHint: @json(__('lang_v1.pos_pwa_install_hint'))
                };
            </script>
            <script src="{{ asset('js/pos-pwa.js?v=' . ($asset_v ?? time())) }}" defer></script>
        @endif

        <div class="modal fade view_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
        <div class="modal fade contact_modal" id="global_contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>

        @if (!empty($__additional_views) && is_array($__additional_views))
            @foreach ($__additional_views as $additional_view)
                @includeIf($additional_view)
            @endforeach
        @endif
        <div>

            <div class="overlay tw-hidden"></div>
        </div>
</body>
<style>
    @media print {
        #scrollable-container {
            overflow: visible !important;
            height: auto !important;
        }
        
        /* Hide side menu */
        .side-bar,
        .thetop > aside {
            display: none !important;
        }
    }
</style>
<style>
    .small-view-side-active {
        display: flex !important;
        flex-direction: column;
        z-index: 1080;
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
    }
    .overlay {
        width: 100vw;
        height: 100vh;
        background: rgba(15, 20, 37, 0.48);
        position: fixed;
        top: 0;
        left: 0;
        display: none;
        z-index: 1070;
        backdrop-filter: blur(2px);
    }

    .tw-dw-btn.tw-dw-btn-xs.tw-dw-btn-outline {
        width: max-content;
        margin: 2px;
    }

    #scrollable-container{
        position:relative;
    }
</style>

</html>
