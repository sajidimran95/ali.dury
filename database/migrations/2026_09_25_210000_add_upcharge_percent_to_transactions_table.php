<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('transactions') && ! Schema::hasColumn('transactions', 'upcharge_percent')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->decimal('upcharge_percent', 8, 2)->default(0)->after('discount_amount');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('transactions') && Schema::hasColumn('transactions', 'upcharge_percent')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('upcharge_percent');
            });
        }
    }
};
