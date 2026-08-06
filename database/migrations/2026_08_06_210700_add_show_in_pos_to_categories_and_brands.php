<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('categories') && ! Schema::hasColumn('categories', 'show_in_pos')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->boolean('show_in_pos')->default(1)->after('category_type');
            });
        }

        if (Schema::hasTable('brands') && ! Schema::hasColumn('brands', 'show_in_pos')) {
            Schema::table('brands', function (Blueprint $table) {
                $table->boolean('show_in_pos')->default(1)->after('description');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('categories') && Schema::hasColumn('categories', 'show_in_pos')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('show_in_pos');
            });
        }

        if (Schema::hasTable('brands') && Schema::hasColumn('brands', 'show_in_pos')) {
            Schema::table('brands', function (Blueprint $table) {
                $table->dropColumn('show_in_pos');
            });
        }
    }
};
