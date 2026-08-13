<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ecommerce checkout was saving type=sell (final invoices).
     * Those should be sales orders, not POS/direct sales.
     */
    public function up(): void
    {
        if (! Schema::hasTable('transactions') || ! Schema::hasColumn('transactions', 'source')) {
            return;
        }

        $deliveredIds = collect();
        if (Schema::hasTable('ecom_order_meta')) {
            $deliveredIds = DB::table('ecom_order_meta')
                ->where('fulfillment_status', 'delivered')
                ->pluck('transaction_id');
        }

        if ($deliveredIds->isNotEmpty()) {
            DB::table('transactions')
                ->where('source', 'ecommerce')
                ->where('type', 'sell')
                ->whereIn('id', $deliveredIds)
                ->update([
                    'type' => 'sales_order',
                    'status' => 'completed',
                    'updated_at' => now(),
                ]);
        }

        DB::table('transactions')
            ->where('source', 'ecommerce')
            ->where('type', 'sell')
            ->update([
                'type' => 'sales_order',
                'status' => 'ordered',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('transactions') || ! Schema::hasColumn('transactions', 'source')) {
            return;
        }

        DB::table('transactions')
            ->where('source', 'ecommerce')
            ->where('type', 'sales_order')
            ->update([
                'type' => 'sell',
                'status' => 'final',
                'updated_at' => now(),
            ]);
    }
};
