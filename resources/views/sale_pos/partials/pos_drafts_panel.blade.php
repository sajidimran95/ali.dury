@php
    $pos_settings = $pos_settings ?? (session('business.pos_settings') ? json_decode(session('business.pos_settings'), true) : []);
    $is_pos_create = request()->segment(1) == 'pos' && request()->segment(2) == 'create';
    $is_pos_edit = request()->segment(1) == 'pos' && request()->segment(3) == 'edit';
    $show_drafts_panel = ($is_pos_create || $is_pos_edit)
        && (empty($pos_settings['disable_draft']) || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
        && (! Gate::check('disable_draft') || auth()->user()->can('superadmin') || auth()->user()->can('admin'));
    $show_suspend_tab = empty($pos_settings['disable_suspend'])
        && (! Gate::check('disable_suspend_sale') || auth()->user()->can('superadmin') || auth()->user()->can('admin'));
@endphp

@if ($show_drafts_panel)
    <div class="pos-drafts-dropdown-wrap tw-relative tw-inline-flex" id="pos_drafts_dropdown_wrap">
        <button type="button" id="pos_drafts_header_toggle" title="@lang('lang_v1.pos_saved_drafts')"
            class="tw-shadow-[rgba(17,_17,_26,_0.1)_0px_0px_16px] tw-bg-white hover:tw-bg-white/60 tw-cursor-pointer tw-border-2 tw-flex tw-items-center tw-justify-center tw-rounded-md md:tw-w-10 tw-w-auto tw-h-10 tw-text-gray-600 active:tw-scale-95 tw-transition-transform pull-right pos-drafts-header-btn">
            <strong class="!tw-m-3 tw-relative tw-inline-flex tw-items-center tw-justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file-text tw-text-[#646EE4] tw-inline-block" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M14 3v4a1 1 0 0 0 1 1h4"/>
                    <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/>
                    <path d="M9 9l1 0"/>
                    <path d="M9 13l6 0"/>
                    <path d="M9 17l6 0"/>
                </svg>
                <span class="pos-drafts-header-badge tw-hidden" id="pos_drafts_count">0</span>
                <span class="tw-inline md:tw-hidden tw-ml-1">@lang('lang_v1.pos_saved_drafts')</span>
            </strong>
        </button>

        <div class="pos-drafts-dropdown" id="pos_drafts_panel" aria-hidden="true">
            <div class="pos-drafts-dropdown__header">
                <span class="pos-drafts-dropdown__title">@lang('lang_v1.pos_saved_drafts')</span>
                <div class="pos-drafts-dropdown__header-actions">
                    <button type="button" class="pos-drafts-panel__refresh" id="pos_drafts_refresh" title="@lang('lang_v1.refresh')">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <button type="button" class="pos-drafts-dropdown__close" id="pos_drafts_close" title="@lang('messages.close')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <ul class="pos-drafts-panel__tabs" role="tablist">
                <li role="presentation" class="active">
                    <a href="#pos_drafts_tab_drafts" data-status="draft" class="pos-drafts-panel__tab pos-drafts-panel__tab--active" role="tab">
                        @lang('sale.draft')
                    </a>
                </li>
                @if ($show_suspend_tab)
                    <li role="presentation">
                        <a href="#pos_drafts_tab_suspended" data-status="suspended" class="pos-drafts-panel__tab" role="tab">
                            @lang('lang_v1.suspend')
                        </a>
                    </li>
                @endif
            </ul>
            <div class="pos-drafts-panel__list-wrap">
                <div id="pos_drafts_list" class="pos-drafts-panel__list" data-status="draft">
                    <div class="pos-drafts-panel__loading">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
