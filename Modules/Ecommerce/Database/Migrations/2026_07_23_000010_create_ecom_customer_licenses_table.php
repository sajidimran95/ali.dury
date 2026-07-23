<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ecom_customer_licenses')) {
            Schema::create('ecom_customer_licenses', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('business_id');
                $table->unsignedInteger('contact_id')->index();
                $table->string('license_type', 50); // business, resale, tobacco, id, other
                $table->string('label')->nullable();
                $table->string('certificate_number')->nullable();
                $table->date('expires_at')->nullable();
                $table->string('file_path')->nullable();
                $table->string('original_name')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ecom_customer_licenses');
    }
};
