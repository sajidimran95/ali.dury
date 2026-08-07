<?php

namespace Modules\Crm\Http\Controllers;

use App\Utils\ModuleUtil;
use Menu;

class DataController
{
    public function modifyAdminMenu()
    {
        if (! auth()->check()) {
            return;
        }

        $moduleUtil = app(ModuleUtil::class);
        if (! $moduleUtil->isModuleInstalled('Crm')) {
            return;
        }

        if (! auth()->user()->can('crm.access')
            && ! auth()->user()->can('crm.lead.view')
            && ! auth()->user()->can('customer.view')
            && ! auth()->user()->can('customer.view_own')
            && ! auth()->user()->can('superadmin')) {
            return;
        }

        Menu::modify('admin-sidebar-menu', function ($menu) {
            $menu->dropdown(
                __('crm::lang.crm'),
                function ($sub) {
                    if (auth()->user()->can('crm.access') || auth()->user()->can('superadmin')) {
                        $sub->url(
                            route('crm.dashboard'),
                            __('crm::lang.dashboard'),
                            ['icon' => 'fa fas fa-tachometer-alt', 'active' => request()->segment(1) == 'crm' && request()->segment(2) == 'dashboard']
                        );
                    }

                    if (auth()->user()->can('crm.lead.view') || auth()->user()->can('crm.access') || auth()->user()->can('superadmin')) {
                        $sub->url(
                            route('crm.leads.index'),
                            __('crm::lang.leads'),
                            ['icon' => 'fa fas fa-user-plus', 'active' => request()->segment(1) == 'crm' && request()->segment(2) == 'leads']
                        );
                        $sub->url(
                            route('crm.pipeline.index'),
                            __('crm::lang.pipeline'),
                            ['icon' => 'fa fas fa-columns', 'active' => request()->segment(1) == 'crm' && request()->segment(2) == 'pipeline']
                        );
                    }

                    if (auth()->user()->can('customer.view') || auth()->user()->can('customer.view_own') || auth()->user()->can('superadmin')) {
                        $customerAttrs = [
                            'icon' => 'fa fas fa-users',
                            'active' => request()->segment(1) == 'contacts' && request()->input('type') == 'customer',
                        ];
                        if (auth()->user()->can('customer.create')) {
                            $customerAttrs['add_modal'] = [
                                'href' => action([\App\Http\Controllers\ContactController::class, 'create'], ['type' => 'customer']),
                                'container' => '#global_contact_modal',
                            ];
                            $customerAttrs['add_label'] = __('contact.add_contact');
                        }
                        $sub->url(
                            action([\App\Http\Controllers\ContactController::class, 'index'], ['type' => 'customer']),
                            __('crm::lang.customers'),
                            $customerAttrs
                        );
                        $sub->url(
                            action([\App\Http\Controllers\CustomerGroupController::class, 'index']),
                            __('crm::lang.customer_groups'),
                            ['icon' => 'fa fas fa-users-cog', 'active' => request()->segment(1) == 'customer-group']
                        );
                        if (auth()->user()->can('customer.create')) {
                            $sub->url(
                                action([\App\Http\Controllers\ContactController::class, 'getImportContacts']),
                                __('lang_v1.import_contacts'),
                                ['icon' => 'fa fas fa-upload', 'active' => request()->segment(1) == 'contacts' && request()->segment(2) == 'import']
                            );
                        }
                    }

                    if (auth()->user()->can('crm.schedule') || auth()->user()->can('crm.access') || auth()->user()->can('superadmin')) {
                        $sub->url(
                            route('crm.schedules.index'),
                            __('crm::lang.follow_ups'),
                            ['icon' => 'fa fas fa-calendar-check', 'active' => request()->segment(1) == 'crm' && request()->segment(2) == 'schedules']
                        );
                    }

                    if (auth()->user()->can('crm.call_log') || auth()->user()->can('crm.access') || auth()->user()->can('superadmin')) {
                        $sub->url(
                            route('crm.call-logs.index'),
                            __('crm::lang.call_logs'),
                            ['icon' => 'fa fas fa-phone', 'active' => request()->segment(1) == 'crm' && request()->segment(2) == 'call-logs']
                        );
                    }

                    if (auth()->user()->can('crm.settings') || auth()->user()->can('crm.access') || auth()->user()->can('superadmin')) {
                        $sub->url(
                            route('crm.settings'),
                            __('crm::lang.settings'),
                            ['icon' => 'fa fas fa-cog', 'active' => request()->segment(1) == 'crm' && request()->segment(2) == 'settings']
                        );
                    }
                },
                ['icon' => '<svg aria-hidden="true" class="tw-size-5 tw-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>']
            )->order(50);
        });
    }

    public function user_permissions()
    {
        return [
            ['value' => 'crm.access', 'label' => __('crm::lang.access_crm'), 'default' => false],
            ['value' => 'crm.lead.view', 'label' => __('crm::lang.permission_lead_view'), 'default' => false],
            ['value' => 'crm.lead.create', 'label' => __('crm::lang.permission_lead_create'), 'default' => false],
            ['value' => 'crm.lead.update', 'label' => __('crm::lang.permission_lead_update'), 'default' => false],
            ['value' => 'crm.lead.delete', 'label' => __('crm::lang.permission_lead_delete'), 'default' => false],
            ['value' => 'crm.schedule', 'label' => __('crm::lang.permission_schedule'), 'default' => false],
            ['value' => 'crm.call_log', 'label' => __('crm::lang.permission_call_log'), 'default' => false],
            ['value' => 'crm.settings', 'label' => __('crm::lang.permission_settings'), 'default' => false],
        ];
    }

    public function addTaxonomies()
    {
        return [
            'crm_source' => [
                'taxonomy_label' => __('crm::lang.source'),
                'heading' => __('crm::lang.sources'),
                'sub_heading' => __('crm::lang.manage_sources'),
                'enable_taxonomy_code' => false,
                'enable_sub_taxonomy' => false,
            ],
            'crm_life_stage' => [
                'taxonomy_label' => __('crm::lang.life_stage'),
                'heading' => __('crm::lang.life_stages'),
                'sub_heading' => __('crm::lang.manage_life_stages'),
                'enable_taxonomy_code' => false,
                'enable_sub_taxonomy' => false,
            ],
        ];
    }

    /**
     * Persist CRM fields after contact save (from core ContactController).
     */
    public function after_contact_saved($data)
    {
        $contact = $data['contact'] ?? null;
        $input = $data['input'] ?? [];

        if (! $contact) {
            return;
        }

        $crmContact = \Modules\Crm\Entities\CrmContact::find($contact->id);
        if (! $crmContact) {
            return;
        }

        $changed = false;
        if (array_key_exists('crm_source', $input)) {
            $crmContact->crm_source = $input['crm_source'] ?: null;
            $changed = true;
        }
        if (array_key_exists('crm_life_stage', $input)) {
            $crmContact->crm_life_stage = $input['crm_life_stage'] ?: null;
            $changed = true;
        }
        if ($changed) {
            $crmContact->save();
        }

        if (! empty($input['user_id']) && is_array($input['user_id'])) {
            $crmContact->leadUsers()->sync($input['user_id']);
        }
    }
}
