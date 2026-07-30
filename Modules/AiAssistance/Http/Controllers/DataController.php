<?php

namespace Modules\AiAssistance\Http\Controllers;

use App\Utils\ModuleUtil;
use Menu;
use Modules\AiAssistance\Entities\AiAssistanceSetting;

class DataController
{
    public function modifyAdminMenu()
    {
        if (! auth()->check()) {
            return;
        }

        $moduleUtil = app(ModuleUtil::class);
        if (! $moduleUtil->isModuleInstalled('AiAssistance')) {
            return;
        }

        $business_id = session()->get('user.business_id');
        if (empty($business_id)) {
            return;
        }

        $settings = AiAssistanceSetting::forBusiness((int) $business_id);
        if (! $settings->is_enabled) {
            return;
        }

        if (! auth()->user()->can('ai.access') && ! auth()->user()->can('ai.chat') && ! auth()->user()->can('ai.settings')) {
            return;
        }

        Menu::modify('admin-sidebar-menu', function ($menu) {
            $menu->url(
                route('ai.dashboard'),
                __('aiassistance::lang.ai_assistant'),
                [
                    'icon' => '<svg aria-hidden="true" class="tw-size-5 tw-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8V4H8"/><rect width="16" height="12" x="4" y="8" rx="2"/><path d="M2 14h2"/><path d="M20 14h2"/><path d="M15 13v2"/><path d="M9 13v2"/></svg>',
                    'active' => request()->segment(1) == 'ai',
                ]
            )->order(21);
        });
    }

    public function user_permissions()
    {
        return [
            ['value' => 'ai.access', 'label' => __('aiassistance::lang.permission_access'), 'default' => false],
            ['value' => 'ai.chat', 'label' => __('aiassistance::lang.permission_chat'), 'default' => false],
            ['value' => 'ai.settings', 'label' => __('aiassistance::lang.permission_settings'), 'default' => false],
        ];
    }

    public function get_additional_script()
    {
        return [
            'additional_views' => [
                'aiassistance::partials.fab',
            ],
        ];
    }
}
