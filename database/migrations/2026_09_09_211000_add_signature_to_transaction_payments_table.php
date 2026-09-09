<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('transaction_payments', 'signature')) {
            Schema::table('transaction_payments', function (Blueprint $table) {
                $table->string('signature', 255)->nullable()->after('note');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('transaction_payments', 'signature')) {
            Schema::table('transaction_payments', function (Blueprint $table) {
                $table->dropColumn('signature');
            });
        }
    }
};
