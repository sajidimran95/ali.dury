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

        if (! auth()->user()->can('ai.access') && ! auth()->user()->can('ai.chat') && ! auth()->user()->can('ai.settings') && ! auth()->user()->can('purchase_n_sell_report.view')) {
            return;
        }

        Menu::modify('admin-sidebar-menu', function ($menu) {
            if (auth()->user()->can('ai.access') || auth()->user()->can('ai.chat') || auth()->user()->can('ai.settings')) {
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
                            'active' => request()->segment(1) == 'ai' && request()->segment(2) != 'intelligence',
                        ]
                    );
                });
            }

            if (\Illuminate\Support\Facades\Route::has('ai.intelligence.supplier-prices')) {
                $reportsTitle = __('report.reports');
                $menu->whereTitle($reportsTitle, function ($reportsMenu) {
                    if (empty($reportsMenu)) {
                        return;
                    }
                    $reportsMenu->url(
                        route('ai.intelligence.supplier-prices'),
                        'Cheaper supplier prices',
                        [
                            'icon' => '',
                            'active' => request()->segment(2) == 'intelligence' && request()->segment(3) == 'supplier-prices',
                        ]
                    );
                });
            }
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
                'aiassistance::partials.po_cost_alert',
                'aiassistance::partials.pos_assist',
            ],
        ];
    }
}
