<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Composite index for dashboard / list filters that query by
     * business + type + status + date together.
     */
    public function up(): void
    {
        $index = 'transactions_biz_type_status_date_index';
        $exists = collect(DB::select("SHOW INDEX FROM transactions WHERE Key_name = ?", [$index]))->isNotEmpty();

        if (! $exists) {
            Schema::table('transactions', function (Blueprint $table) use ($index) {
                $table->index(
                    ['business_id', 'type', 'status', 'transaction_date'],
                    $index
                );
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $index = 'transactions_biz_type_status_date_index';
        $exists = collect(DB::select("SHOW INDEX FROM transactions WHERE Key_name = ?", [$index]))->isNotEmpty();

        if ($exists) {
            Schema::table('transactions', function (Blueprint $table) use ($index) {
                $table->dropIndex($index);
            });
        }
    }
};
