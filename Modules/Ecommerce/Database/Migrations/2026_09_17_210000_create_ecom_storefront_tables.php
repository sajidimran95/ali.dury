<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ecom_inquiries')) {
            Schema::create('ecom_inquiries', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('business_id');
                $table->string('name');
                $table->string('email');
                $table->string('phone')->nullable();
                $table->string('subject')->nullable();
                $table->text('message');
                $table->string('source')->default('contact');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ecom_subscribers')) {
            Schema::create('ecom_subscribers', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('business_id');
                $table->string('email');
                $table->string('source')->nullable();
                $table->timestamps();
                $table->unique(['business_id', 'email']);
            });
        }

        if (! Schema::hasTable('ecom_stock_alerts')) {
            Schema::create('ecom_stock_alerts', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('business_id');
                $table->unsignedInteger('product_id');
                $table->string('email');
                $table->timestamps();
                $table->unique(['product_id', 'email']);
            });
        }

        if (! Schema::hasTable('ecom_password_resets')) {
            Schema::create('ecom_password_resets', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('business_id');
                $table->string('email');
                $table->string('token', 64);
                $table->timestamp('expires_at');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ecom_password_resets');
        Schema::dropIfExists('ecom_stock_alerts');
        Schema::dropIfExists('ecom_subscribers');
        Schema::dropIfExists('ecom_inquiries');
    }
};
