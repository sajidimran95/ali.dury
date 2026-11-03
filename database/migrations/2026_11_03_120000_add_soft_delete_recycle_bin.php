<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Recycle bin support:
 *  - products are soft deleted (deleted_at / deleted_by) so old invoices keep showing the product name.
 *  - deleted sales are archived in `deleted_sales` (full snapshot) so they can be restored from System Settings.
 */
return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('products', 'deleted_at')) {
            Schema::table('products', function (Blueprint $table) {
                $table->softDeletes();
                $table->unsignedInteger('deleted_by')->nullable();
                $table->index(['business_id', 'deleted_at']);
            });
        }

        if (! Schema::hasTable('deleted_sales')) {
            Schema::create('deleted_sales', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('business_id')->index();
                $table->unsignedInteger('transaction_id')->index();
                $table->string('type', 50)->nullable();
                $table->string('status', 50)->nullable();
                $table->string('invoice_no')->nullable();
                $table->unsignedInteger('contact_id')->nullable();
                $table->unsignedInteger('location_id')->nullable();
                $table->decimal('final_total', 22, 4)->default(0);
                $table->dateTime('transaction_date')->nullable();
                $table->longText('payload');
                $table->unsignedInteger('deleted_by')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('deleted_sales');

        if (Schema::hasColumn('products', 'deleted_at')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex(['business_id', 'deleted_at']);
                $table->dropColumn(['deleted_at', 'deleted_by']);
            });
        }
    }
};
