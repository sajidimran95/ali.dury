<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            if (! Schema::hasColumn('contacts', 'crm_source')) {
                $table->string('crm_source')->nullable()->after('contact_status');
            }
            if (! Schema::hasColumn('contacts', 'crm_life_stage')) {
                $table->string('crm_life_stage')->nullable()->after('crm_source');
            }
        });

        if (! Schema::hasTable('crm_lead_users')) {
            Schema::create('crm_lead_users', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('contact_id');
                $table->unsignedInteger('user_id');
                $table->timestamps();

                $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->unique(['contact_id', 'user_id']);
            });
        }

        if (! Schema::hasTable('crm_schedules')) {
            Schema::create('crm_schedules', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('business_id');
                $table->unsignedInteger('contact_id');
                $table->unsignedInteger('created_by');
                $table->string('title');
                $table->string('status')->default('open'); // open | completed
                $table->dateTime('start_datetime')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
                $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
                $table->index(['business_id', 'status']);
            });
        }

        if (! Schema::hasTable('crm_call_logs')) {
            Schema::create('crm_call_logs', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('business_id');
                $table->unsignedInteger('contact_id');
                $table->unsignedInteger('created_by');
                $table->string('subject')->nullable();
                $table->integer('duration')->nullable();
                $table->text('description')->nullable();
                $table->dateTime('called_on')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
                $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
                $table->index(['business_id', 'contact_id']);
            });
        }

        Schema::table('business', function (Blueprint $table) {
            if (! Schema::hasColumn('business', 'crm_settings')) {
                $table->text('crm_settings')->nullable()->after('common_settings');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_call_logs');
        Schema::dropIfExists('crm_schedules');
        Schema::dropIfExists('crm_lead_users');

        Schema::table('contacts', function (Blueprint $table) {
            if (Schema::hasColumn('contacts', 'crm_life_stage')) {
                $table->dropColumn('crm_life_stage');
            }
            if (Schema::hasColumn('contacts', 'crm_source')) {
                $table->dropColumn('crm_source');
            }
        });

        Schema::table('business', function (Blueprint $table) {
            if (Schema::hasColumn('business', 'crm_settings')) {
                $table->dropColumn('crm_settings');
            }
        });
    }
};
