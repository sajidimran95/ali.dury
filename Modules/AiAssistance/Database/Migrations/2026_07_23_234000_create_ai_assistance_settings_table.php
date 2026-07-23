<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ai_assistance_settings')) {
            Schema::create('ai_assistance_settings', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('business_id')->unique();
                $table->boolean('is_enabled')->default(1);
                $table->text('openai_api_key')->nullable();
                $table->string('openai_model')->nullable();
                $table->timestamps();

                $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_assistance_settings');
    }
};
