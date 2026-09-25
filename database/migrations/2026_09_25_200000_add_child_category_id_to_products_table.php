<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products') && ! Schema::hasColumn('products', 'child_category_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedInteger('child_category_id')->nullable()->after('sub_category_id');
                $table->index('child_category_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'child_category_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex(['child_category_id']);
                $table->dropColumn('child_category_id');
            });
        }
    }
};
