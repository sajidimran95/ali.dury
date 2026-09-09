<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sale_zero_sale_reasons')) {
            Schema::create('sale_zero_sale_reasons', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 191);
                $table->string('slug', 64)->unique();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(1);
                $table->timestamps();
            });
        }

        $now = now();
        $reasons = [
            ['name' => 'call back', 'slug' => 'call_back', 'sort_order' => 1],
            ['name' => 'does not want order', 'slug' => 'does_not_want_order', 'sort_order' => 2],
            ['name' => 'gps reset', 'slug' => 'gps_reset', 'sort_order' => 3],
            ['name' => 'marchandising', 'slug' => 'marchandising', 'sort_order' => 4],
            ['name' => 'note', 'slug' => 'note', 'sort_order' => 5],
            ['name' => 'owner/manager not in', 'slug' => 'owner_manager_not_in', 'sort_order' => 6],
            ['name' => 'refused service', 'slug' => 'refused_service', 'sort_order' => 7],
            ['name' => 'will order next time', 'slug' => 'will_order_next_time', 'sort_order' => 8],
        ];
        foreach ($reasons as $row) {
            $exists = DB::table('sale_zero_sale_reasons')->where('slug', $row['slug'])->exists();
            if (! $exists) {
                DB::table('sale_zero_sale_reasons')->insert([
                    'name' => $row['name'],
                    'slug' => $row['slug'],
                    'sort_order' => $row['sort_order'],
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        if (! Schema::hasTable('sale_zero_sales')) {
            Schema::create('sale_zero_sales', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('business_id');
                $table->unsignedInteger('contact_id');
                $table->unsignedInteger('reason_id');
                $table->unsignedInteger('created_by');
                $table->unsignedInteger('location_id')->nullable();
                $table->text('comment')->nullable();
                $table->timestamps();

                $table->index(['business_id', 'contact_id']);
                $table->index('created_by');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_zero_sales');
        Schema::dropIfExists('sale_zero_sale_reasons');
    }
};
