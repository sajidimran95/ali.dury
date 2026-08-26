<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Sales Representative: expense create + own expense list.
     * Same permission style as createDefaultSalesRole() in BusinessUtil.
     */
    public function up(): void
    {
        $permissions = [
            'view_own_expense',
            'expense.add',
        ];

        foreach ($permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }

        // Clear Spatie cache so new permissions are picked up
        if (app()->bound('cache')) {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }

        $roles = Role::query()
            ->where('guard_name', 'web')
            ->where(function ($q) {
                $q->where('name', 'like', 'Sales Representative#%')
                    ->orWhere('name', 'like', 'Sales#%');
            })
            ->get();

        foreach ($roles as $role) {
            // Skip non sales-rep roles that happen to match "Sales#..." patterns loosely
            $name = (string) $role->name;
            if (
                stripos($name, 'Sales Representative#') !== 0
                && ! preg_match('/^Sales#\d+$/', $name)
            ) {
                continue;
            }

            foreach ($permissions as $permission) {
                if (! $role->hasPermissionTo($permission)) {
                    $role->givePermissionTo($permission);
                }
            }
        }
    }

    public function down(): void
    {
        $permissions = [
            'view_own_expense',
            'expense.add',
        ];

        $roles = Role::query()
            ->where('guard_name', 'web')
            ->where(function ($q) {
                $q->where('name', 'like', 'Sales Representative#%')
                    ->orWhere('name', 'like', 'Sales#%');
            })
            ->get();

        foreach ($roles as $role) {
            $name = (string) $role->name;
            if (
                stripos($name, 'Sales Representative#') !== 0
                && ! preg_match('/^Sales#\d+$/', $name)
            ) {
                continue;
            }

            foreach ($permissions as $permission) {
                if ($role->hasPermissionTo($permission)) {
                    $role->revokePermissionTo($permission);
                }
            }
        }
    }
};
