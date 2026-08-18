<?php

use App\Utils\BusinessUtil;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Ensure Sales Representative exists on every business (cloned sites included).
     */
    public function up(): void
    {
        if (! class_exists(BusinessUtil::class)) {
            return;
        }

        try {
            (new BusinessUtil())->ensureDefaultSalesRolesForAllBusinesses(false);
        } catch (\Throwable $e) {
            // Roles table may not exist yet on a fresh install; new businesses still get the role in BusinessUtil.
        }
    }

    public function down(): void
    {
        // Keep the system role.
    }
};
