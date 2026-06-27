<!-- Left sidebar — Zoho Inventory style -->
<aside class="side-bar tw-relative tw-hidden tw-h-full lg:tw-flex lg:tw-flex-col tw-shrink-0">

<script>document.currentScript.parentElement.classList.toggle('zoho-sidebar-collapsed', localStorage.getItem('upos_sidebar_collapse') === 'true');</script>

    <!-- Sidebar Search -->
    <div class="tw-px-3 tw-pt-3 tw-pb-2 tw-shrink-0">
        <div class="tw-flex tw-items-center tw-gap-2 tw-px-3 tw-py-1.5 tw-rounded-md tw-bg-white tw-border tw-border-[#e4e7ed]">
            <svg class="tw-size-4 tw-shrink-0 tw-text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="text" id="sidebar-search" placeholder="Search menu ( / )"
                class="tw-grow tw-min-w-0 tw-bg-transparent tw-outline-none tw-border-none tw-text-sm tw-font-normal tw-text-gray-600 placeholder:tw-text-gray-400"
                autocomplete="off" />
            <button id="sidebar-search-clear" type="button" aria-label="Clear search"
                class="tw-hidden tw-shrink-0 tw-text-gray-400 hover:tw-text-gray-600">
                <svg class="tw-size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 6l-12 12M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    {!! Menu::render('admin-sidebar-menu', 'adminltecustom') !!}

    <p id="sidebar-no-results" class="tw-hidden tw-px-4 tw-py-3 tw-text-xs tw-text-gray-400 tw-text-center">
        No menu items found.
    </p>

    <div class="zoho-sidebar-footer tw-shrink-0 tw-mt-auto">
        <button type="button" class="side-bar-collapse zoho-sidebar-footer__toggle" aria-label="Toggle sidebar">
            <svg class="zoho-sidebar-footer__icon zoho-sidebar-footer__icon--collapse" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z"/><path d="M9 4v16"/><path d="M14 10l-2 2l2 2"/></svg>
            <svg class="zoho-sidebar-footer__icon zoho-sidebar-footer__icon--expand" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z"/><path d="M15 4v16"/><path d="M10 10l2 2l-2 2"/></svg>
        </button>
    </div>
</aside>
