<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_barcodes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('business_id');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('variation_id')->nullable();
            $table->string('barcode');
            $table->string('type', 20)->default('alternate'); // alternate | pack
            $table->unsignedInteger('unit_id')->nullable(); // packaging unit when type=pack
            $table->string('label')->nullable(); // e.g. Vendor A, Box sticker
            $table->timestamps();

            $table->unique(['business_id', 'barcode']);
            $table->index(['product_id']);
            $table->index(['variation_id']);
            $table->index(['business_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_barcodes');
    }
};
