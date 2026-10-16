<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_delivery_routes', function (Blueprint $table) {
            if (! Schema::hasColumn('driver_delivery_routes', 'driver_latitude')) {
                $table->decimal('driver_latitude', 10, 7)->nullable()->after('start_longitude');
                $table->decimal('driver_longitude', 10, 7)->nullable()->after('driver_latitude');
                $table->unsignedInteger('driver_accuracy')->nullable()->after('driver_longitude');
                $table->timestamp('driver_location_at')->nullable()->after('driver_accuracy');
            }
        });
    }

    public function down(): void
    {
        Schema::table('driver_delivery_routes', function (Blueprint $table) {
            foreach (['driver_location_at', 'driver_accuracy', 'driver_longitude', 'driver_latitude'] as $col) {
                if (Schema::hasColumn('driver_delivery_routes', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
