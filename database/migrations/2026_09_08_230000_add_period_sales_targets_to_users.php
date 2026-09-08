<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'sales_target_daily')) {
                $table->decimal('sales_target_daily', 22, 4)->nullable()->after('sales_target');
            }
            if (! Schema::hasColumn('users', 'sales_target_weekly')) {
                $table->decimal('sales_target_weekly', 22, 4)->nullable()->after('sales_target_daily');
            }
            if (! Schema::hasColumn('users', 'sales_target_monthly')) {
                $table->decimal('sales_target_monthly', 22, 4)->nullable()->after('sales_target_weekly');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['sales_target_daily', 'sales_target_weekly', 'sales_target_monthly'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
