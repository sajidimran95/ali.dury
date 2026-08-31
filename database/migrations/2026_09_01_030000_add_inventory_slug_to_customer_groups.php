<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_groups', function (Blueprint $table) {
            if (! Schema::hasColumn('customer_groups', 'inventory_slug')) {
                $table->string('inventory_slug', 32)->default('all_inventory')->after('amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customer_groups', function (Blueprint $table) {
            if (Schema::hasColumn('customer_groups', 'inventory_slug')) {
                $table->dropColumn('inventory_slug');
            }
        });
    }
};
