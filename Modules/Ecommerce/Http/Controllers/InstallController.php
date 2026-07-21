<?php

namespace Modules\Ecommerce\Http\Controllers;

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

        $version = System::getProperty('ecommerce_version');
        $is_installed = ! empty($version);

        return view('ecommerce::install.index', compact('is_installed', 'version'));
    }

    public function install(Request $request)
    {
        if (! auth()->user()->can('manage_modules')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();
            Artisan::call('module:migrate', ['module' => 'Ecommerce', '--force' => true]);
            System::addProperty('ecommerce_version', config('ecommerce.module_version', '1.0.0'));
            DB::commit();

            $output = [
                'success' => 1,
                'msg' => 'Ecommerce module installed successfully',
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
            Artisan::call('module:migrate', ['module' => 'Ecommerce', '--force' => true]);
            System::addProperty('ecommerce_version', config('ecommerce.module_version', '1.0.0'));

            return [
                'success' => 1,
                'msg' => 'Ecommerce module updated successfully',
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
            System::removeProperty('ecommerce_version');

            return [
                'success' => 1,
                'msg' => 'Ecommerce module uninstalled',
            ];
        } catch (\Exception $e) {
            return [
                'success' => 0,
                'msg' => $e->getMessage(),
            ];
        }
    }
}
