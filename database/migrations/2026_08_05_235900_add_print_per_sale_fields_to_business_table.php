<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Print Per Sale preferences as real columns (not JSON).
     */
    public function up(): void
    {
        Schema::table('business', function (Blueprint $table) {
            if (! Schema::hasColumn('business', 'print_per_sale_copies')) {
                $table->unsignedTinyInteger('print_per_sale_copies')->default(1)->after('pos_settings');
            }
            if (! Schema::hasColumn('business', 'print_per_sale_printer_id')) {
                $table->unsignedInteger('print_per_sale_printer_id')->nullable()->after('print_per_sale_copies');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business', function (Blueprint $table) {
            if (Schema::hasColumn('business', 'print_per_sale_printer_id')) {
                $table->dropColumn('print_per_sale_printer_id');
            }
            if (Schema::hasColumn('business', 'print_per_sale_copies')) {
                $table->dropColumn('print_per_sale_copies');
            }
        });
    }
};
