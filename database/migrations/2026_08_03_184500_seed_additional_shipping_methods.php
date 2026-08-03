<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Extra default carriers / methods (idempotent).
     * Other environments: git pull && php artisan migrate
     */
    public function up(): void
    {
        if (! Schema::hasTable('shipping_methods') || ! Schema::hasTable('business')) {
            return;
        }

        $methods = [
            'DHL',
            'Amazon Logistics',
            'OnTrac',
            'Same Day Delivery',
            'Next Day Air',
            'Ground Shipping',
            'Customer Pickup',
            'Will Call',
            'Courier',
        ];

        $now = now();
        $businessIds = DB::table('business')->pluck('id');

        foreach ($businessIds as $businessId) {
            $ownerId = DB::table('users')->where('business_id', $businessId)->orderBy('id')->value('id');
            foreach ($methods as $name) {
                $exists = DB::table('shipping_methods')
                    ->where('business_id', $businessId)
                    ->where('name', $name)
                    ->whereNull('deleted_at')
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

    public function down(): void
    {
        if (! Schema::hasTable('shipping_methods')) {
            return;
        }

        DB::table('shipping_methods')->whereIn('name', [
            'DHL',
            'Amazon Logistics',
            'OnTrac',
            'Same Day Delivery',
            'Next Day Air',
            'Ground Shipping',
            'Customer Pickup',
            'Will Call',
            'Courier',
        ])->delete();
    }
};
