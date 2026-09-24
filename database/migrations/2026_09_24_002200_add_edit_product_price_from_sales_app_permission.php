<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Allow changing line item price on Sale App orders only
     * (does not change inventory / product master prices).
     */
    public function up(): void
    {
        $name = 'edit_product_price_from_sales_app';

        $permission = Permission::firstOrCreate(
            ['name' => $name, 'guard_name' => 'web']
        );

        if (app()->bound('cache')) {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }

        // Admins + Sales Representatives (order-line override only; not product master).
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->where(function ($q) {
                $q->where('name', 'like', 'Admin#%')
                    ->orWhere('name', 'Admin')
                    ->orWhere('name', 'like', 'Sales Representative#%')
                    ->orWhere('name', 'like', 'Sales#%');
            })
            ->get();

        foreach ($roles as $role) {
            $roleName = (string) $role->name;
            $isAdmin = $roleName === 'Admin' || stripos($roleName, 'Admin#') === 0;
            $isSalesRep = stripos($roleName, 'Sales Representative#') === 0
                || (bool) preg_match('/^Sales#\d+$/', $roleName);
            if (! $isAdmin && ! $isSalesRep) {
                continue;
            }

            // Avoid hasPermissionTo() — it throws if permission is missing from cache.
            if (! $role->permissions()->where('permissions.name', $name)->exists()) {
                $role->givePermissionTo($permission);
            }
        }

        if (app()->bound('cache')) {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }
    }

    public function down(): void
    {
        $name = 'edit_product_price_from_sales_app';

        $permission = Permission::where('name', $name)->where('guard_name', 'web')->first();
        if (! $permission) {
            return;
        }

        $roles = Role::query()
            ->where('guard_name', 'web')
            ->where(function ($q) {
                $q->where('name', 'like', 'Admin#%')
                    ->orWhere('name', 'Admin')
                    ->orWhere('name', 'like', 'Sales Representative#%')
                    ->orWhere('name', 'like', 'Sales#%');
            })
            ->get();

        foreach ($roles as $role) {
            $roleName = (string) $role->name;
            $isAdmin = $roleName === 'Admin' || stripos($roleName, 'Admin#') === 0;
            $isSalesRep = stripos($roleName, 'Sales Representative#') === 0
                || (bool) preg_match('/^Sales#\d+$/', $roleName);
            if (! $isAdmin && ! $isSalesRep) {
                continue;
            }

            if ($role->permissions()->where('permissions.name', $name)->exists()) {
                $role->revokePermissionTo($permission);
            }
        }

        $permission->delete();

        if (app()->bound('cache')) {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }
    }
};
