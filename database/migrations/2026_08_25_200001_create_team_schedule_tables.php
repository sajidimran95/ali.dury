<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_member_colors', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('business_id');
            $table->unsignedInteger('user_id');
            $table->string('color', 7);
            $table->timestamps();

            $table->unique(['business_id', 'user_id']);
            $table->index('business_id');
        });

        Schema::create('team_shifts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('business_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('location_id')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('title')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'starts_at']);
            $table->index(['business_id', 'user_id']);
        });

        Schema::create('time_off_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('business_id');
            $table->unsignedInteger('user_id');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('type', 32)->default('vacation');
            $table->string('status', 20)->default('pending');
            $table->text('reason')->nullable();
            $table->unsignedInteger('reviewed_by')->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'status']);
            $table->index(['business_id', 'user_id']);
            $table->index(['business_id', 'starts_at']);
        });

        Schema::table('employee_attendances', function (Blueprint $table) {
            $table->decimal('clock_latitude', 10, 7)->nullable()->after('notes');
            $table->decimal('clock_longitude', 10, 7)->nullable()->after('clock_latitude');
            $table->string('clock_source', 20)->nullable()->after('clock_longitude');
        });

        Schema::table('business_locations', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('zip_code');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->unsignedInteger('clock_radius_meters')->nullable()->after('longitude');
        });
    }

    public function down(): void
    {
        Schema::table('business_locations', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'clock_radius_meters']);
        });

        Schema::table('employee_attendances', function (Blueprint $table) {
            $table->dropColumn(['clock_latitude', 'clock_longitude', 'clock_source']);
        });

        Schema::dropIfExists('time_off_requests');
        Schema::dropIfExists('team_shifts');
        Schema::dropIfExists('team_member_colors');
    }
};
