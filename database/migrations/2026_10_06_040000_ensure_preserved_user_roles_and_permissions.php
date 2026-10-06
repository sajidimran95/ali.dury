<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $businessId = 1;
        $businessUtil = new \App\Utils\BusinessUtil();

        // Ensure Sales Representative role exists
        $salesRepRole = $businessUtil->createDefaultSalesRole($businessId, true);

        // Ensure Delivery role exists
        $deliveryRoleName = 'Delivery#'.$businessId;
        $deliveryRole = Role::where('name', $deliveryRoleName)->where('guard_name', 'web')->where('business_id', $businessId)->first();
        if (empty($deliveryRole)) {
            $deliveryRole = Role::create([
                'name' => $deliveryRoleName,
                'business_id' => $businessId,
                'guard_name' => 'web',
            ]);
            $deliveryRole->syncPermissions(['delivery.driver.view', 'delivery.driver.edit']);
        } else {
            $deliveryRole->syncPermissions(['delivery.driver.view', 'delivery.driver.edit']);
        }

        // Assign salesman
        $salesman = \App\User::where('email', 'sales@japspos.com')->where('business_id', $businessId)->first();
        if ($salesman && !$salesman->hasRole($salesRepRole->name)) {
            $salesman->assignRole($salesRepRole->name);
        }

        // Assign deliveryman
        $deliveryman = \App\User::where('email', 'delivery@gmail.com')->where('business_id', $businessId)->first();
        if ($deliveryman && !$deliveryman->hasRole($deliveryRole->name)) {
            $deliveryman->assignRole($deliveryRole->name);
        }
    }

    public function down(): void
    {
    }
};
