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
        $permission = 'edit_product_price_from_sales_app';

        Permission::findOrCreate($permission, 'web');

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
            $name = (string) $role->name;
            $isAdmin = $name === 'Admin' || stripos($name, 'Admin#') === 0;
            $isSalesRep = stripos($name, 'Sales Representative#') === 0
                || (bool) preg_match('/^Sales#\d+$/', $name);
            if (! $isAdmin && ! $isSalesRep) {
                continue;
            }
            if (! $role->hasPermissionTo($permission)) {
                $role->givePermissionTo($permission);
            }
        }
    }

    public function down(): void
    {
        $permission = 'edit_product_price_from_sales_app';

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
            $name = (string) $role->name;
            $isAdmin = $name === 'Admin' || stripos($name, 'Admin#') === 0;
            $isSalesRep = stripos($name, 'Sales Representative#') === 0
                || (bool) preg_match('/^Sales#\d+$/', $name);
            if (! $isAdmin && ! $isSalesRep) {
                continue;
            }
            if ($role->hasPermissionTo($permission)) {
                $role->revokePermissionTo($permission);
            }
        }

        Permission::where('name', $permission)->where('guard_name', 'web')->delete();

        if (app()->bound('cache')) {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }
    }
};
