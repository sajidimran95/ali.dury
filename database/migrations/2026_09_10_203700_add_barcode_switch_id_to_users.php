<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'barcode_switch_id')) {
                $table->string('barcode_switch_id', 40)->nullable()->after('barcode_login');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'barcode_switch_id')) {
                $table->dropColumn('barcode_switch_id');
            }
        });
    }
};
