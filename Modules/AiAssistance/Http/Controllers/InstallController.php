<?php

namespace Modules\AiAssistance\Http\Controllers;

use App\System;
use App\Utils\ModuleUtil;
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

        $version = System::getProperty('aiassistance_version');
        $is_installed = ! empty($version);

        return view('aiassistance::install.index', compact('is_installed', 'version'));
    }

    public function install()
    {
        if (! auth()->user()->can('manage_modules')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();
            Artisan::call('module:migrate', ['module' => 'AiAssistance', '--force' => true]);
            System::addProperty('aiassistance_version', config('aiassistance.module_version', '1.0.0'));
            DB::commit();

            return redirect()->back()->with('status', [
                'success' => 1,
                'msg' => 'AI Assistance module installed successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('status', [
                'success' => 0,
                'msg' => $e->getMessage(),
            ]);
        }
    }
}
