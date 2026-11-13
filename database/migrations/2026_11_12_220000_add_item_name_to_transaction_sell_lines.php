<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optional per-order item display name. Does not change products.name.
 */
return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('transaction_sell_lines', 'item_name')) {
            Schema::table('transaction_sell_lines', function (Blueprint $table) {
                $table->string('item_name', 191)->nullable()->after('sell_line_note');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('transaction_sell_lines', 'item_name')) {
            Schema::table('transaction_sell_lines', function (Blueprint $table) {
                $table->dropColumn('item_name');
            });
        }
    }
};
