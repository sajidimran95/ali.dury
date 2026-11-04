<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Manual "Bill To" name typed at POS (used by the japspos2 user only).
 * Printed on the invoice instead of the selected customer's details.
 */
return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('transactions', 'invoice_bill_name')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->string('invoice_bill_name', 191)->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('transactions', 'invoice_bill_name')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('invoice_bill_name');
            });
        }
    }
};
