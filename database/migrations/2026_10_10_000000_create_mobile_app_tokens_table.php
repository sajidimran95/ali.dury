<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bearer tokens for the native Sales / Delivery / Customer apps (/api/v1/*).
 * One row per logged-in device. Only the SHA-256 hash of the token is stored.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mobile_app_tokens')) {
            return;
        }

        Schema::create('mobile_app_tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            // sale | delivery | customer
            $table->string('app', 20)->index();
            // App\User (sale, delivery) or App\Contact (customer)
            $table->string('tokenable_type', 100);
            $table->unsignedInteger('tokenable_id');
            $table->unsignedInteger('business_id')->nullable()->index();
            $table->string('name', 191)->nullable();
            $table->string('token', 64)->unique();
            // Per-device app state that the PWA keeps in the session (e.g. default location)
            $table->text('meta')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['tokenable_type', 'tokenable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_app_tokens');
    }
};
