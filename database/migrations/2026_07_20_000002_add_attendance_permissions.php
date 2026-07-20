<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissions = [
            'attendance.clock',
            'attendance.view',
            'attendance.view_all',
        ];

        foreach ($permissions as $name) {
            if (! Permission::where('name', $name)->exists()) {
                Permission::create([
                    'name' => $name,
                    'guard_name' => 'web',
                ]);
            }
        }

        $roles = Role::all();
        foreach ($roles as $role) {
            $role->givePermissionTo(['attendance.clock', 'attendance.view']);

            // Admins can view everyone's attendance (role name pattern: Admin#{business_id})
            if (stripos($role->name, 'Admin#') === 0 || stripos($role->name, 'Admin') === 0) {
                $role->givePermissionTo('attendance.view_all');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissions = ['attendance.clock', 'attendance.view', 'attendance.view_all'];
        foreach ($permissions as $name) {
            $permission = Permission::where('name', $name)->first();
            if ($permission) {
                $permission->delete();
            }
        }
    }
};
