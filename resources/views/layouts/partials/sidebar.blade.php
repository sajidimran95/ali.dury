<!-- Left sidebar — Salezy light -->
<aside class="side-bar tw-relative tw-hidden tw-h-full lg:tw-flex lg:tw-flex-col tw-shrink-0">

<script>document.currentScript.parentElement.classList.toggle('zoho-sidebar-collapsed', localStorage.getItem('upos_sidebar_collapse') === 'true');</script>

    <!-- Sidebar Search -->
    <div class="zoho-sidebar-search tw-px-3 tw-pt-3 tw-pb-2 tw-shrink-0">
        <div class="tw-flex tw-items-center tw-gap-2 tw-px-3 tw-py-2 tw-rounded-lg">
            <svg class="tw-size-4 tw-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="text" id="sidebar-search" placeholder="Search menu ( / )"
                class="tw-grow tw-min-w-0 tw-bg-transparent tw-outline-none tw-border-none tw-text-sm tw-font-normal"
                autocomplete="off" />
            <button id="sidebar-search-clear" type="button" aria-label="Clear search"
                class="tw-hidden tw-shrink-0">
                <svg class="tw-size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 6l-12 12M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    {!! Menu::render('admin-sidebar-menu', 'adminltecustom') !!}

    <p id="sidebar-no-results" class="tw-hidden tw-px-4 tw-py-3 tw-text-xs tw-text-center" style="color:#69738c">
        No menu items found.
    </p>

    <div class="zoho-sidebar-footer">
        <div id="japs-sidebar-ai-slot" class="japs-sidebar-ai-slot"></div>

        @php
            $sidebar_user = Auth::user();
            $sidebar_user_initial = strtoupper(substr($sidebar_user->first_name ?? 'U', 0, 1));
            $sidebar_user_name = trim(($sidebar_user->first_name ?? '') . ' ' . ($sidebar_user->last_name ?? ''));
            $sidebar_user_role = method_exists($sidebar_user, 'getRoleNames')
                ? (string) ($sidebar_user->getRoleNames()->first() ?? '')
                : '';
            if ($sidebar_user_role !== '' && str_contains($sidebar_user_role, '#')) {
                $sidebar_user_role = explode('#', $sidebar_user_role)[0];
            }
            if ($sidebar_user_role === '') {
                $sidebar_user_role = __('lang_v1.profile');
            }
        @endphp

        <div class="zoho-sidebar-footer__links">
            <a href="{{ action([\App\Http\Controllers\Auth\LoginController::class, 'logout']) }}"
               class="zoho-sidebar-footer__link zoho-sidebar-footer__link--logout"
               title="@lang('lang_v1.sign_out')">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                <span class="zoho-sidebar-footer__link-label">@lang('lang_v1.sign_out')</span>
            </a>
        </div>

        <div class="zoho-sidebar-user">
            <a href="{{ action([\App\Http\Controllers\UserController::class, 'getProfile']) }}"
               class="zoho-sidebar-user__profile"
               title="{{ $sidebar_user_name }}">
                <span class="zoho-sidebar-user__avatar">{{ $sidebar_user_initial }}</span>
                <span class="zoho-sidebar-user__meta">
                    <span class="zoho-sidebar-user__name">{{ $sidebar_user_name }}</span>
                    <span class="zoho-sidebar-user__role">{{ $sidebar_user_role }}</span>
                </span>
            </a>
            <button type="button" class="side-bar-collapse zoho-sidebar-footer__toggle" aria-label="Toggle sidebar">
                <svg class="zoho-sidebar-footer__icon zoho-sidebar-footer__icon--collapse" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z"/><path d="M9 4v16"/><path d="M14 10l-2 2l2 2"/></svg>
                <svg class="zoho-sidebar-footer__icon zoho-sidebar-footer__icon--expand" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z"/><path d="M15 4v16"/><path d="M10 10l2 2l-2 2"/></svg>
                <span class="tw-sr-only">Collapse sidebar</span>
            </button>
        </div>
    </div>
</aside>
