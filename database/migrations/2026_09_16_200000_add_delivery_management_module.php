<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('transactions', 'delivery_status')) {
                $table->string('delivery_status', 32)->nullable()->after('delivery_person')->index();
            }
            if (! Schema::hasColumn('transactions', 'shipping_latitude')) {
                $table->decimal('shipping_latitude', 10, 7)->nullable()->after('shipping_address');
            }
            if (! Schema::hasColumn('transactions', 'shipping_longitude')) {
                $table->decimal('shipping_longitude', 10, 7)->nullable()->after('shipping_latitude');
            }
        });

        if (! Schema::hasTable('driver_delivery_routes')) {
            Schema::create('driver_delivery_routes', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('business_id')->index();
                $table->unsignedInteger('delivery_user_id')->index();
                $table->unsignedInteger('business_location_id')->nullable()->index();
                $table->date('route_date')->index();
                $table->string('status', 32)->default('planned');
                $table->unsignedInteger('total_orders')->default(0);
                $table->unsignedInteger('total_distance')->default(0);
                $table->unsignedInteger('estimated_duration')->default(0);
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->string('start_name')->nullable();
                $table->string('start_address')->nullable();
                $table->decimal('start_latitude', 10, 7)->nullable();
                $table->decimal('start_longitude', 10, 7)->nullable();
                $table->timestamps();

                $table->index(['business_id', 'route_date']);
                $table->index(['delivery_user_id', 'route_date']);
            });
        }

        if (! Schema::hasTable('delivery_route_orders')) {
            Schema::create('delivery_route_orders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('delivery_route_id')->index();
                $table->unsignedInteger('transaction_id')->index();
                $table->unsignedInteger('stop_no')->default(1);
                $table->unsignedInteger('distance_from_previous')->default(0);
                $table->unsignedInteger('estimated_duration_from_previous')->default(0);
                $table->string('status', 32)->default('pending');
                $table->timestamp('arrived_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->string('fail_reason')->nullable();
                $table->text('delivery_notes')->nullable();
                $table->string('ship_to_name')->nullable();
                $table->string('ship_to_phone')->nullable();
                $table->string('ship_to_address')->nullable();
                $table->string('ship_to_city')->nullable();
                $table->string('ship_to_state')->nullable();
                $table->string('ship_to_zip')->nullable();
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->timestamps();

                $table->foreign('delivery_route_id')
                    ->references('id')
                    ->on('driver_delivery_routes')
                    ->cascadeOnDelete();
                $table->unique(['delivery_route_id', 'transaction_id']);
            });
        }

        if (! Schema::hasTable('delivery_areas')) {
            Schema::create('delivery_areas', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('business_id')->index();
                $table->string('state')->nullable();
                $table->string('state_code', 8)->nullable();
                $table->string('city')->nullable();
                $table->string('zip_code', 16)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        $this->seedPermissionsAndRoles();
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_route_orders');
        Schema::dropIfExists('driver_delivery_routes');
        Schema::dropIfExists('delivery_areas');

        Schema::table('transactions', function (Blueprint $table) {
            foreach (['shipping_longitude', 'shipping_latitude', 'delivery_status'] as $col) {
                if (Schema::hasColumn('transactions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        foreach (['delivery.manage', 'delivery.driver.view', 'delivery.driver.edit'] as $name) {
            Permission::where('name', $name)->delete();
        }
    }

    private function seedPermissionsAndRoles(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $perms = ['delivery.manage', 'delivery.driver.view', 'delivery.driver.edit'];
        foreach ($perms as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $businessIds = DB::table('business')->pluck('id');
        foreach ($businessIds as $bid) {
            $deliveryName = 'Delivery#'.$bid;
            $role = Role::where('name', $deliveryName)->where('guard_name', 'web')->first();
            if (! $role) {
                $role = Role::create([
                    'name' => $deliveryName,
                    'guard_name' => 'web',
                    'business_id' => $bid,
                    'is_default' => 0,
                ]);
            }
            $role->givePermissionTo(['delivery.driver.view', 'delivery.driver.edit']);

            $admin = Role::where('name', 'Admin#'.$bid)->where('guard_name', 'web')->first();
            if ($admin) {
                $admin->givePermissionTo($perms);
            }
        }

        // Also grant manage to any Admin* role without business suffix pattern misses
        foreach (Role::where('name', 'like', 'Admin%')->get() as $adminRole) {
            $adminRole->givePermissionTo($perms);
        }
    }
};
