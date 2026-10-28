<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        (new \App\Utils\Util())->repairActivityLogPrimaryKey();
    }

    public function down(): void
    {
    }
};
