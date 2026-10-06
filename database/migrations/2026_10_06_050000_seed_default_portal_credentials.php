<?php

use Database\Seeders\DefaultPortalCredentialsSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Ensures Sale / Delivery / Customer default logins exist after migrate
 * (git pull + php artisan migrate on any environment).
 */
return new class extends Migration
{
    public function up(): void
    {
        (new DefaultPortalCredentialsSeeder)->run();
    }

    public function down(): void
    {
        // Keep default portal accounts on rollback.
    }
};
