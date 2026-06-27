@inject('request', 'Illuminate\Http\Request')
<!-- Zoho-style top bar -->
<div class="zoho-topbar no-print">
    <div class="zoho-topbar__inner">
        {{-- Sidebar collapse --}}
        <div class="tw-flex tw-items-center tw-gap-2 tw-shrink-0">
            <button type="button"
                class="zoho-topbar__btn zoho-topbar__btn--icon side-bar-collapse"
                aria-label="Collapse sidebar">
                <svg class="tw-size-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none"><path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z"/><path d="M15 4v16"/><path d="M10 10l-2 2l2 2"/></svg>
            </button>
        </div>

        {{-- Right actions --}}
        <div class="zoho-topbar__actions">
            @if(Module::has('Superadmin'))
                @includeIf('superadmin::layouts.partials.active_subscription')
            @endif

            @if(!empty(session('previous_user_id')) && !empty(session('previous_username')))
                <a href="{{ route('sign-in-as-user', session('previous_user_id')) }}"
                    class="zoho-topbar__btn zoho-topbar__btn--ghost tw-text-red-300">
                    <i class="fas fa-undo"></i>
                    <span class="tw-hidden md:tw-inline">@lang('lang_v1.back_to_username', ['username' => session('previous_username')])</span>
                </a>
            @endif

            <span class="zoho-topbar__org" title="{{ Session::get('business.name') }}">
                {{ Session::get('business.name') }}
            </span>

            @if (Module::has('Essentials'))
                @includeIf('essentials::layouts.partials.header_part')
            @endif

            {{-- Quick create --}}
            <details class="tw-relative tw-inline-block">
                <summary class="zoho-topbar__btn zoho-topbar__btn--primary tw-cursor-pointer" title="Quick create">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                </summary>
                <ul class="tw-absolute tw-right-0 tw-z-50 tw-mt-1 tw-w-48 tw-bg-white tw-rounded-lg tw-shadow-lg tw-ring-1 tw-ring-gray-200 tw-py-1">
                    <li>
                        <a href="{{ route('calendar') }}"
                            class="tw-flex tw-items-center tw-gap-2 tw-px-3 tw-py-2 tw-text-sm tw-text-gray-700 hover:tw-bg-gray-50">
                            <svg class="tw-w-4 tw-h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="5" width="16" height="16" rx="2"/><path d="M16 3v4M8 3v4M4 11h16"/></svg>
                            @lang('lang_v1.calendar')
                        </a>
                    </li>
                    @if (Module::has('Essentials'))
                        <li>
                            <a href="#" data-href="{{ action([\Modules\Essentials\Http\Controllers\ToDoController::class, 'create']) }}"
                                data-container="#task_modal"
                                class="btn-modal tw-flex tw-items-center tw-gap-2 tw-px-3 tw-py-2 tw-text-sm tw-text-gray-700 hover:tw-bg-gray-50">
                                <svg class="tw-w-4 tw-h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3h18v18H3z"/><path d="m9 12 2 2 4-4"/></svg>
                                @lang('essentials::lang.add_to_do')
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()->hasRole('Admin#' . auth()->user()->business_id))
                        <li>
                            <a href="#" id="start_tour"
                                class="tw-flex tw-items-center tw-gap-2 tw-px-3 tw-py-2 tw-text-sm tw-text-gray-700 hover:tw-bg-gray-50">
                                @lang('lang_v1.application_tour')
                            </a>
                        </li>
                    @endif
                </ul>
            </details>

            <button id="btnCalculator" title="@lang('lang_v1.calculator')"
                data-content='@include('layouts.partials.calculator')' type="button"
                data-trigger="click" data-html="true" data-placement="bottom"
                class="zoho-topbar__btn zoho-topbar__btn--icon tw-hidden md:tw-inline-flex popover-default">
                <svg class="tw-size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 7h8M8 14h.01M12 14h.01M16 14h.01M8 17h.01M12 17h.01M16 17h.01"/></svg>
            </button>

            @if (in_array('pos_sale', $enabled_modules))
                @can('sell.create')
                    <a href="{{ action([\App\Http\Controllers\SellPosController::class, 'create']) }}"
                        class="zoho-topbar__btn zoho-topbar__btn--ghost tw-hidden sm:tw-inline-flex">
                        <svg class="tw-size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="4" y="4" width="6" height="6" rx="1"/><rect x="14" y="4" width="6" height="6" rx="1"/><rect x="4" y="14" width="6" height="6" rx="1"/><rect x="14" y="14" width="6" height="6" rx="1"/></svg>
                        @lang('sale.pos_sale')
                    </a>
                @endcan
            @endif

            @if (Module::has('Repair'))
                @includeIf('repair::layouts.partials.header')
            @endif

            @can('profit_loss_report.view')
                <button type="button" id="view_todays_profit" title="{{ __('home.todays_profit') }}"
                    data-toggle="tooltip" data-placement="bottom"
                    class="zoho-topbar__btn zoho-topbar__btn--icon tw-hidden sm:tw-inline-flex">
                    <svg class="tw-size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="3"/><rect x="3" y="6" width="18" height="12" rx="2"/></svg>
                </button>
            @endcan

            <div class="tw-relative tw-inline-flex tw-items-center">
                @include('layouts.partials.header-notifications')
            </div>

            <a href="{{ route('business.getBusinessSettings') }}"
                class="zoho-topbar__btn zoho-topbar__btn--icon" title="@lang('business.business_settings')">
                <svg class="tw-size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
            </a>

            <details class="tw-relative tw-inline-block">
                <summary class="zoho-topbar__btn zoho-topbar__btn--icon tw-cursor-pointer">
                    <svg class="tw-size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="4"/><path d="M6 20v-1a6 6 0 0 1 12 0v1"/></svg>
                </summary>
                <ul class="tw-absolute tw-right-0 tw-z-50 tw-mt-1 tw-w-52 tw-bg-white tw-rounded-lg tw-shadow-lg tw-ring-1 tw-ring-gray-200 tw-py-1">
                    <li class="tw-px-4 tw-py-2 tw-border-b tw-border-gray-100">
                        <p class="tw-text-xs tw-text-gray-500">@lang('lang_v1.signed_in_as')</p>
                        <p class="tw-text-sm tw-font-medium tw-text-gray-900 tw-truncate">{{ Auth::User()->first_name }} {{ Auth::User()->last_name }}</p>
                    </li>
                    <li>
                        <a href="{{ action([\App\Http\Controllers\UserController::class, 'getProfile']) }}"
                            class="tw-flex tw-items-center tw-gap-2 tw-px-3 tw-py-2 tw-text-sm tw-text-gray-700 hover:tw-bg-gray-50">
                            @lang('lang_v1.profile')
                        </a>
                    </li>
                    <li>
                        <a href="{{ action([\App\Http\Controllers\Auth\LoginController::class, 'logout']) }}"
                            class="tw-flex tw-items-center tw-gap-2 tw-px-3 tw-py-2 tw-text-sm tw-text-gray-700 hover:tw-bg-gray-50">
                            @lang('lang_v1.sign_out')
                        </a>
                    </li>
                </ul>
            </details>
        </div>
    </div>
</div>
