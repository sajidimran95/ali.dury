<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cross_store_stock_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('business_id');
            $table->unsignedInteger('product_id')->nullable();
            $table->unsignedInteger('variation_id')->nullable();
            $table->string('sku')->index();
            $table->string('sub_sku')->index();
            $table->decimal('qty_delta', 22, 4);
            $table->decimal('qty_after', 22, 4)->nullable();
            $table->string('source_site')->nullable();
            $table->string('event')->default('delta'); // delta|absolute
            $table->timestamps();

            $table->index(['variation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cross_store_stock_logs');
    }
};
