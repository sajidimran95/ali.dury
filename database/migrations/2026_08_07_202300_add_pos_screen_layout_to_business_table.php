<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business', function (Blueprint $table) {
            if (! Schema::hasColumn('business', 'pos_screen_layout')) {
                // ltr = products left / cart right (default); rtl = swap
                $table->string('pos_screen_layout', 10)->default('ltr')->after('print_per_sale_printer_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('business', function (Blueprint $table) {
            if (Schema::hasColumn('business', 'pos_screen_layout')) {
                $table->dropColumn('pos_screen_layout');
            }
        });
    }
};
