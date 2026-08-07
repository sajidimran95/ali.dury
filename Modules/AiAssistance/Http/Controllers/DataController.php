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

        // Under Settings sidebar dropdown (not a top-level menu item)
        Menu::modify('admin-sidebar-menu', function ($menu) {
            $settingsTitle = __('business.settings');
            $menu->whereTitle($settingsTitle, function ($settingsMenu) {
                if (empty($settingsMenu)) {
                    return;
                }

                $settingsMenu->url(
                    route('ai.dashboard'),
                    __('aiassistance::lang.ai_assistant'),
                    [
                        'icon' => '',
                        'active' => request()->segment(1) == 'ai',
                    ]
                );
            });
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
