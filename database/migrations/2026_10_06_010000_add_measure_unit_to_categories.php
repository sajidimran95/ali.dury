<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('categories') || Schema::hasColumn('categories', 'measure_unit_enabled')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('measure_unit_enabled')->default(0)->after('show_in_pos');
            $table->string('measure_unit_name', 30)->nullable()->after('measure_unit_enabled');
            $table->unsignedInteger('measure_unit_id')->nullable()->after('measure_unit_name');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('categories') && Schema::hasColumn('categories', 'measure_unit_enabled')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn(['measure_unit_enabled', 'measure_unit_name', 'measure_unit_id']);
            });
        }
    }
};
