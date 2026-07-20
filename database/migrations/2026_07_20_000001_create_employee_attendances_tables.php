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
        Schema::create('employee_attendances', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('business_id');
            $table->unsignedInteger('user_id');
            $table->dateTime('clock_in_at');
            $table->dateTime('clock_out_at')->nullable();
            $table->enum('status', ['clocked_in', 'on_break', 'clocked_out'])->default('clocked_in');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'user_id']);
            $table->index(['business_id', 'clock_in_at']);
            $table->index(['user_id', 'status']);
        });

        Schema::create('employee_attendance_breaks', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('attendance_id');
            $table->dateTime('break_start_at');
            $table->dateTime('break_end_at')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index('attendance_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_attendance_breaks');
        Schema::dropIfExists('employee_attendances');
    }
};
