<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Add attendance.edit permission (manager/admin time-clock corrections).
     */
    public function up(): void
    {
        if (! Permission::where('name', 'attendance.edit')->exists()) {
            Permission::create([
                'name' => 'attendance.edit',
                'guard_name' => 'web',
            ]);
        }

        $roles = Role::all();
        foreach ($roles as $role) {
            // Super admin / business Admin roles can edit time clocks
            if (stripos($role->name, 'Admin#') === 0
                || stripos($role->name, 'Admin') === 0
                || stripos($role->name, 'Manager') === 0
                || stripos($role->name, 'Superadmin') === 0
            ) {
                $role->givePermissionTo('attendance.edit');
            }
        }
    }

    public function down(): void
    {
        $permission = Permission::where('name', 'attendance.edit')->first();
        if ($permission) {
            $permission->delete();
        }
    }
};
