<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('transactions', 'crm_is_order_request')) {
                $table->boolean('crm_is_order_request')->default(0)->after('is_direct_sale');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'crm_is_order_request')) {
                $table->dropColumn('crm_is_order_request');
            }
        });
    }
};
