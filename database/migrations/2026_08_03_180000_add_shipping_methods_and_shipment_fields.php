<?php

use App\Business;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Shipping methods master + sale shipment detail columns.
     * Other environments: git pull && php artisan migrate
     */
    public function up(): void
    {
        if (! Schema::hasTable('shipping_methods')) {
            Schema::create('shipping_methods', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('business_id')->index();
                $table->string('name');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(1)->index();
                $table->unsignedInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['business_id', 'name']);
            });
        }

        Schema::table('transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('transactions', 'shipping_method')) {
                $table->string('shipping_method')->nullable()->after('shipping_details');
            }
            if (! Schema::hasColumn('transactions', 'shipping_date')) {
                $table->dateTime('shipping_date')->nullable()->index()->after('shipping_status');
            }
            if (! Schema::hasColumn('transactions', 'shipping_tracking_no')) {
                $table->string('shipping_tracking_no')->nullable()->after('shipping_date');
            }
            if (! Schema::hasColumn('transactions', 'no_of_boxes')) {
                $table->unsignedInteger('no_of_boxes')->nullable()->after('shipping_tracking_no');
            }
        });

        // Permissions for managing shipping methods
        foreach (['shipping_method.view', 'shipping_method.create', 'shipping_method.update', 'shipping_method.delete'] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Attach to every Admin#business role if present
        $roles = Role::where('name', 'like', 'Admin#%')->where('guard_name', 'web')->get();
        foreach ($roles as $role) {
            $role->givePermissionTo([
                'shipping_method.view',
                'shipping_method.create',
                'shipping_method.update',
                'shipping_method.delete',
            ]);
        }

        // Default methods per business
        $defaults = [
            'Pickup',
            'Local Delivery',
            'UPS',
            'FedEx',
            'USPS',
            'Freight',
            'DHL',
            'Amazon Logistics',
            'OnTrac',
            'Same Day Delivery',
            'Next Day Air',
            'Ground Shipping',
            'Customer Pickup',
            'Will Call',
            'Courier',
            'Other',
        ];

        if (Schema::hasTable('business')) {
            $now = now();
            $businessIds = DB::table('business')->pluck('id');
            foreach ($businessIds as $businessId) {
                $ownerId = DB::table('users')->where('business_id', $businessId)->orderBy('id')->value('id');
                foreach ($defaults as $name) {
                    $exists = DB::table('shipping_methods')
                        ->where('business_id', $businessId)
                        ->where('name', $name)
                        ->exists();
                    if (! $exists) {
                        DB::table('shipping_methods')->insert([
                            'business_id' => $businessId,
                            'name' => $name,
                            'description' => null,
                            'is_active' => 1,
                            'created_by' => $ownerId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            foreach (['shipping_method', 'shipping_date', 'shipping_tracking_no', 'no_of_boxes'] as $col) {
                if (Schema::hasColumn('transactions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::dropIfExists('shipping_methods');

        Permission::whereIn('name', [
            'shipping_method.view',
            'shipping_method.create',
            'shipping_method.update',
            'shipping_method.delete',
        ])->delete();
    }
};
