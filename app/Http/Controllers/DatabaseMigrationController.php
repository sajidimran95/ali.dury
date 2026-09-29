<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DatabaseMigrationController extends Controller
{
    public function status()
    {
        $this->authorizeAccess();

        $connection = DB::connection();

        return response()->json(array_merge($this->migrationStatus(), [
            'database' => $connection->getDatabaseName(),
            'host' => config('database.connections.'.$connection->getName().'.host'),
        ]));
    }

    public function run(Request $request)
    {
        $this->authorizeAccess();

        if (! $request->boolean('confirm_backup')) {
            return response()->json(['success' => 0, 'msg' => __('lang_v1.db_update_confirm_required')]);
        }

        // Guards against two admins (or a double click) running migrations at the same time
        $lock = Cache::lock('system_database_migrate', 900);
        if (! $lock->get()) {
            return response()->json(['success' => 0, 'msg' => __('lang_v1.db_update_already_running')]);
        }

        try {
            @set_time_limit(0);
            @ignore_user_abort(true);

            $pending_before = $this->migrationStatus()['pending'];

            $exit_code = Artisan::call('migrate', ['--force' => true]);
            $output = trim(Artisan::output());

            foreach (['view:clear', 'cache:clear', 'config:clear', 'route:clear'] as $command) {
                try {
                    Artisan::call($command);
                } catch (\Throwable $e) {
                    \Log::warning('DB update: '.$command.' failed: '.$e->getMessage());
                }
            }

            \Log::info('Database update run by user #'.auth()->id().' ('.auth()->user()->username.')', [
                'exit_code' => $exit_code,
                'migrations' => $pending_before,
            ]);

            $status = $this->migrationStatus();

            return response()->json([
                'success' => $exit_code === 0 ? 1 : 0,
                'msg' => $exit_code === 0
                    ? __('lang_v1.db_update_success', ['count' => count($pending_before) - count($status['pending'])])
                    : __('lang_v1.db_update_failed'),
                'output' => $output,
                'status' => $status,
            ]);
        } catch (\Throwable $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            return response()->json([
                'success' => 0,
                'msg' => __('lang_v1.db_update_failed'),
                'output' => $e->getMessage(),
                'status' => $this->migrationStatus(),
            ]);
        } finally {
            $lock->release();
        }
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();
        if (! $user->can('superadmin') && ! $user->hasRole('Admin#'.$user->business_id)) {
            abort(403, 'Unauthorized action.');
        }
    }

    /**
     * Pending = migration files (app + modules) not yet recorded in the migrations table.
     */
    protected function migrationStatus(): array
    {
        $migrator = app('migrator');
        $files = $migrator->getMigrationFiles(array_merge([database_path('migrations')], $migrator->paths()));

        $repository = $migrator->getRepository();
        $ran = $repository->repositoryExists() ? $repository->getRan() : [];

        $pending = array_values(array_diff(array_keys($files), $ran));
        sort($pending);

        return [
            'pending' => $pending,
            'ran_count' => count($ran),
            'total_count' => count($files),
        ];
    }
}
