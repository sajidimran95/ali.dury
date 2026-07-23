<?php

namespace Modules\Crm\Http\Controllers;

use App\System;
use App\Utils\ModuleUtil;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class InstallController extends Controller
{
    public function __construct(protected ModuleUtil $moduleUtil)
    {
    }

    public function index()
    {
        if (! auth()->user()->can('manage_modules')) {
            abort(403, 'Unauthorized action.');
        }

        $version = System::getProperty('crm_version');
        $is_installed = ! empty($version);

        return view('crm::install.index', compact('is_installed', 'version'));
    }

    public function install(Request $request)
    {
        if (! auth()->user()->can('manage_modules')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();
            Artisan::call('module:migrate', ['module' => 'Crm', '--force' => true]);
            System::addProperty('crm_version', config('crm.module_version', '1.0.0'));
            $this->seedDefaultTaxonomies();
            DB::commit();

            $output = [
                'success' => 1,
                'msg' => __('crm::lang.crm_installed'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
            ];
        }

        return redirect()->back()->with('status', $output);
    }

    public function update()
    {
        if (! auth()->user()->can('manage_modules')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            Artisan::call('module:migrate', ['module' => 'Crm', '--force' => true]);
            System::addProperty('crm_version', config('crm.module_version', '1.0.0'));

            return [
                'success' => 1,
                'msg' => __('crm::lang.crm_updated'),
            ];
        } catch (\Exception $e) {
            return [
                'success' => 0,
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function uninstall()
    {
        if (! auth()->user()->can('manage_modules')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            System::removeProperty('crm_version');

            return [
                'success' => 1,
                'msg' => 'CRM module uninstalled',
            ];
        } catch (\Exception $e) {
            return [
                'success' => 0,
                'msg' => $e->getMessage(),
            ];
        }
    }

    protected function seedDefaultTaxonomies(): void
    {
        $business_ids = \App\Business::pluck('id');
        $default_sources = ['Website', 'Referral', 'Walk-in', 'Phone', 'Social Media'];
        $default_stages = ['New', 'Contacted', 'Qualified', 'Proposal', 'Won', 'Lost'];

        foreach ($business_ids as $business_id) {
            foreach ($default_sources as $name) {
                \App\Category::firstOrCreate(
                    [
                        'business_id' => $business_id,
                        'category_type' => 'crm_source',
                        'name' => $name,
                        'parent_id' => 0,
                    ],
                    ['created_by' => 1]
                );
            }
            foreach ($default_stages as $name) {
                \App\Category::firstOrCreate(
                    [
                        'business_id' => $business_id,
                        'category_type' => 'crm_life_stage',
                        'name' => $name,
                        'parent_id' => 0,
                    ],
                    ['created_by' => 1]
                );
            }
        }
    }
}
