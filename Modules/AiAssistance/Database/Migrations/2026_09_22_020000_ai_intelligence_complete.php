<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ai_assistance_settings')) {
            Schema::table('ai_assistance_settings', function (Blueprint $table) {
                if (! Schema::hasColumn('ai_assistance_settings', 'match_tolerance_pct')) {
                    $table->decimal('match_tolerance_pct', 8, 2)->default(2);
                }
                if (! Schema::hasColumn('ai_assistance_settings', 'digest_enabled')) {
                    $table->boolean('digest_enabled')->default(0);
                }
                if (! Schema::hasColumn('ai_assistance_settings', 'digest_email')) {
                    $table->string('digest_email')->nullable();
                }
            });
        }

        if (! Schema::hasTable('ai_match_reviews')) {
            Schema::create('ai_match_reviews', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('business_id')->index();
                $table->unsignedInteger('po_id')->index();
                $table->unsignedInteger('po_line_id')->unique();
                $table->unsignedInteger('purchase_id')->nullable();
                $table->string('status', 20)->default('exception');
                $table->decimal('qty_var_pct', 10, 2)->default(0);
                $table->decimal('cost_var_pct', 10, 2)->default(0);
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_match_reviews');
    }
};
