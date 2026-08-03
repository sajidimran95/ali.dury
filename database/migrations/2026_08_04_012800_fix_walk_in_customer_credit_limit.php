<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Walk-In customers were created with credit_limit = 0, which blocks Credit Sale
     * (due) with "Customer Credit Limit Exceeded $0.00". Null = no limit.
     */
    public function up(): void
    {
        if (! Schema::hasTable('contacts')) {
            return;
        }

        DB::table('contacts')
            ->where('is_default', 1)
            ->where(function ($q) {
                $q->where('credit_limit', 0)
                    ->orWhereNull('credit_limit');
            })
            ->update(['credit_limit' => null]);
    }

    public function down(): void
    {
        // No rollback — null is the correct “unlimited” value for walk-in
    }
};
