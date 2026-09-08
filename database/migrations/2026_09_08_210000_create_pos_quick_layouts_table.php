<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pos_quick_layouts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('business_id');
            $table->unsignedInteger('user_id');
            $table->string('name');
            $table->json('keys')->nullable();
            $table->boolean('keep_folder_open')->default(true);
            $table->boolean('is_current')->default(false);
            $table->timestamps();

            $table->index(['business_id', 'user_id']);
        });

        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'pos_quick_keys_enabled')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('pos_quick_keys_enabled')->default(1);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('pos_quick_layouts');

        if (Schema::hasColumn('users', 'pos_quick_keys_enabled')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('pos_quick_keys_enabled');
            });
        }
    }
};
