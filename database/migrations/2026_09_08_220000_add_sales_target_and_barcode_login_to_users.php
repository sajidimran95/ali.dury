<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'sales_target')) {
                $table->decimal('sales_target', 22, 4)->nullable()->after('cmmsn_percent');
            }
            if (! Schema::hasColumn('users', 'barcode_login')) {
                $table->boolean('barcode_login')->default(false)->after('allow_login');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'sales_target')) {
                $table->dropColumn('sales_target');
            }
            if (Schema::hasColumn('users', 'barcode_login')) {
                $table->dropColumn('barcode_login');
            }
        });
    }
};
