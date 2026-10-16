<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('transaction_sell_lines')) {
            return;
        }
        if (! Schema::hasColumn('transaction_sell_lines', 'qty_shipped')) {
            Schema::table('transaction_sell_lines', function (Blueprint $table) {
                $after = Schema::hasColumn('transaction_sell_lines', 'qty_packed') ? 'qty_packed' : 'quantity';
                $table->decimal('qty_shipped', 22, 4)->default(0)->after($after);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('transaction_sell_lines') && Schema::hasColumn('transaction_sell_lines', 'qty_shipped')) {
            Schema::table('transaction_sell_lines', function (Blueprint $table) {
                $table->dropColumn('qty_shipped');
            });
        }
    }
};
