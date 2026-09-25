<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ai_admin_alerts')) {
            Schema::create('ai_admin_alerts', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('business_id')->index();
                $table->string('type', 30)->index();
                $table->string('title', 255);
                $table->text('message')->nullable();
                $table->string('url', 500)->nullable();
                $table->json('meta')->nullable();
                $table->unsignedInteger('created_by')->nullable();
                $table->boolean('emailed')->default(0);
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
                $table->index(['business_id', 'read_at']);
            });
        }

        if (Schema::hasTable('ai_assistance_settings') && ! Schema::hasColumn('ai_assistance_settings', 'alert_email_enabled')) {
            Schema::table('ai_assistance_settings', function (Blueprint $table) {
                $table->boolean('alert_email_enabled')->default(1);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_admin_alerts');
        if (Schema::hasTable('ai_assistance_settings') && Schema::hasColumn('ai_assistance_settings', 'alert_email_enabled')) {
            Schema::table('ai_assistance_settings', function (Blueprint $table) {
                $table->dropColumn('alert_email_enabled');
            });
        }
    }
};
