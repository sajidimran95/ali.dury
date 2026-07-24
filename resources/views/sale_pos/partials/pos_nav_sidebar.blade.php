@php
    $outlet_name = optional($default_location ?? null)->name
        ?? (session('business.name') ?? 'Outlet');
    $admin_menu = \Menu::instance('admin-sidebar-menu');
    $admin_items = $admin_menu ? $admin_menu->getOrderedItems() : [];
@endphp
<aside class="pos-nav-sidebar no-print" aria-label="POS navigation">
    <div class="pos-nav-icons" id="pos_admin_sidebar_icons">
        @forelse($admin_items as $item)
            @php
                $icon_raw = (string) ($item->icon ?? '');
                if (strpos($icon_raw, '<svg') !== false || strpos($icon_raw, '<') === 0) {
                    $icon_html = $icon_raw;
                } elseif ($icon_raw !== '') {
                    $icon_html = '<i class="' . e($icon_raw) . '" aria-hidden="true"></i>';
                } else {
                    $icon_html = '';
                }
                $item_url = $item->getUrl();
                if ($item->hasChilds()) {
                    $first_child = collect($item->getChilds())->first();
                    if ($first_child) {
                        $item_url = $first_child->getUrl();
                    }
                }
                $is_active = $item->isActive() || $item->hasActiveOnChild();
            @endphp
            @if(!empty($icon_html))
                <a href="{{ $item_url }}"
                   class="pos-nav-icon @if($is_active) is-active @endif"
                   title="{{ $item->title }}">
                    {!! $icon_html !!}
                    <span class="pos-nav-icon-label">{{ $item->title }}</span>
                </a>
            @endif
        @empty
            <a href="{{ url('/home') }}" class="pos-nav-icon" title="Home"><i class="fas fa-home"></i></a>
            <a href="{{ action([\App\Http\Controllers\SellPosController::class, 'create']) }}" class="pos-nav-icon is-active" title="Sell"><i class="fas fa-shopping-cart"></i></a>
        @endforelse
        <button type="button" class="pos-nav-icon pos-nav-collapse" id="pos_nav_collapse_btn" title="Collapse menu">
            <i class="fas fa-chevron-left"></i>
        </button>
    </div>

    <div class="pos-nav-panel">
        <div class="pos-nav-register">
            <h2 class="pos-nav-register-title">Main Register</h2>
            <div class="pos-nav-register-meta">
                <span>{{ $outlet_name }}</span>
                @if (empty(optional($transaction ?? null)->location_id) && !empty($business_locations) && count($business_locations) > 1)
                    <button type="button" class="pos-nav-switch" id="pos_nav_switch_location">
                        Switch <i class="fas fa-chevron-down" style="font-size:9px;"></i>
                    </button>
                @endif
            </div>
        </div>
        <ul class="pos-nav-menu">
            <li>
                <a href="{{ action([\App\Http\Controllers\SellPosController::class, 'create']) }}" class="is-active">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Sale</span>
                </a>
            </li>
            @can('close_cash_register')
                <li>
                    <button type="button"
                        class="btn-modal"
                        id="pos_nav_close_register"
                        data-container=".close_register_modal"
                        data-href="{{ action([\App\Http\Controllers\CashRegisterController::class, 'getCloseRegister']) }}">
                        <i class="fas fa-cash-register"></i>
                        <span>Open / Close</span>
                    </button>
                </li>
            @endcan
            @if (!isset($pos_settings['hide_recent_trans']) || $pos_settings['hide_recent_trans'] == 0)
                <li>
                    <button type="button"
                        data-toggle="modal"
                        data-target="#recent_transactions_modal"
                        id="pos_nav_recent_transactions">
                        <i class="fas fa-history"></i>
                        <span>History</span>
                    </button>
                </li>
            @endif
            @can('view_cash_register')
                <li>
                    <button type="button"
                        class="btn-modal"
                        id="pos_nav_register_details"
                        data-container=".register_details_modal"
                        data-href="{{ action([\App\Http\Controllers\CashRegisterController::class, 'getRegisterDetails']) }}">
                        <i class="fas fa-wallet"></i>
                        <span>Cash memo</span>
                    </button>
                </li>
                <li>
                    @can('register_report.view')
                        <a href="{{ action([\App\Http\Controllers\ReportController::class, 'getRegisterReport']) }}">
                            <i class="fas fa-chart-line"></i>
                            <span>Status</span>
                        </a>
                    @else
                        <button type="button"
                            class="btn-modal"
                            data-container=".register_details_modal"
                            data-href="{{ action([\App\Http\Controllers\CashRegisterController::class, 'getRegisterDetails']) }}">
                            <i class="fas fa-chart-line"></i>
                            <span>Status</span>
                        </button>
                    @endcan
                </li>
            @endcan
            @can('account.access')
                <li>
                    <a href="{{ action([\App\Http\Controllers\AccountController::class, 'index']) }}">
                        <i class="fas fa-university"></i>
                        <span>Manage account</span>
                    </a>
                </li>
            @endcan
            @can('business_settings.access')
                <li>
                    <a href="{{ route('business.getBusinessSettings') }}">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
            @endcan
            @if (!Gate::check('disable_quotation') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                <li>
                    <a href="{{ action([\App\Http\Controllers\SellController::class, 'getQuotations']) }}">
                        <i class="fas fa-quote-right"></i>
                        <span>Quotes</span>
                    </a>
                </li>
            @endif
        </ul>
    </div>
</aside>
<script>
(function () {
    var switchBtn = document.getElementById('pos_nav_switch_location');
    if (switchBtn) {
        switchBtn.addEventListener('click', function () {
            var loc = document.getElementById('select_location_id');
            if (loc) {
                loc.focus();
                if (typeof jQuery !== 'undefined' && jQuery(loc).data('select2')) {
                    jQuery(loc).select2('open');
                }
            }
        });
    }
    var collapseBtn = document.getElementById('pos_nav_collapse_btn');
    if (collapseBtn) {
        collapseBtn.addEventListener('click', function () {
            document.body.classList.toggle('pos-nav-collapsed');
            var icon = collapseBtn.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-chevron-left');
                icon.classList.toggle('fa-chevron-right');
            }
        });
    }
})();
</script>
