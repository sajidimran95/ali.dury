<?php

namespace App\Utils;

use App\Business;
use App\BusinessLocation;
use App\InvoiceLayout;
use App\InvoiceScheme;
use App\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseResetUtil
{
    public const PRESERVED_BUSINESS_ID = 1;

    public const PRESERVED_LOCATION_ID = 1;

    /**
     * Tables that are never truncated (global / structural).
     */
    protected array $skipTables = [
        'migrations',
        'currencies',
        'barcodes',
        'permissions',
        'system',
        'business',
        'business_locations',
        'users',
    ];

    /**
     * Reset operational data; keep business #1, location #1, and the business owner (admin).
     */
    public function resetToFreshPos(): void
    {
        $business = Business::find(self::PRESERVED_BUSINESS_ID);
        if (empty($business)) {
            throw new \RuntimeException('Business with id '.self::PRESERVED_BUSINESS_ID.' was not found.');
        }

        $location = BusinessLocation::find(self::PRESERVED_LOCATION_ID);
        if (empty($location) || (int) $location->business_id !== self::PRESERVED_BUSINESS_ID) {
            throw new \RuntimeException('Business location with id '.self::PRESERVED_LOCATION_ID.' was not found for this business.');
        }

        $adminUser = User::withTrashed()->find($business->owner_id);
        if (empty($adminUser)) {
            $adminUser = User::withTrashed()
                ->where('business_id', self::PRESERVED_BUSINESS_ID)
                ->orderBy('id')
                ->first();
        }

        if (empty($adminUser)) {
            throw new \RuntimeException('Admin user (business owner) could not be found.');
        }

        $adminUserId = (int) $adminUser->id;

        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach ($this->getApplicationTables() as $table) {
                if (in_array($table, $this->skipTables, true)) {
                    continue;
                }

                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                }
            }

            $this->purgeOtherBusinessesAndUsers($adminUserId);
            $this->purgeRolesAndPermissionsForBusiness(self::PRESERVED_BUSINESS_ID, $adminUserId);
            $this->ensureLocationPermission(self::PRESERVED_LOCATION_ID);

            $businessUtil = new BusinessUtil();
            $businessUtil->newBusinessDefaultResources(self::PRESERVED_BUSINESS_ID, $adminUserId);

            $this->linkDefaultInvoiceSettingsToLocation(self::PRESERVED_BUSINESS_ID, self::PRESERVED_LOCATION_ID);

            $moduleUtil = new ModuleUtil();
            if (config('app.env') != 'demo') {
                $business = Business::find(self::PRESERVED_BUSINESS_ID);
                $moduleUtil->getModuleData('after_business_created', ['business' => $business]);
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        Artisan::call('cache:clear');
        Artisan::call('view:clear');
    }

    protected function getApplicationTables(): array
    {
        $database = DB::getDatabaseName();

        $rows = DB::select(
            'SELECT table_name AS name FROM information_schema.tables WHERE table_schema = ? AND table_type = ?',
            [$database, 'BASE TABLE']
        );

        return collect($rows)->pluck('name')->sort()->values()->all();
    }

    protected function purgeOtherBusinessesAndUsers(int $adminUserId): void
    {
        DB::table('business')->where('id', '!=', self::PRESERVED_BUSINESS_ID)->delete();
        DB::table('business_locations')->where('id', '!=', self::PRESERVED_LOCATION_ID)->delete();

        User::withTrashed()
            ->where('id', '!=', $adminUserId)
            ->forceDelete();

        $adminUser = User::withTrashed()->find($adminUserId);
        if ($adminUser) {
            $adminUser->business_id = self::PRESERVED_BUSINESS_ID;
            $adminUser->deleted_at = null;
            $adminUser->save();

            Business::where('id', self::PRESERVED_BUSINESS_ID)->update(['owner_id' => $adminUserId]);
        }
    }

    protected function purgeRolesAndPermissionsForBusiness(int $businessId, int $adminUserId): void
    {
        $roleIds = Role::where('business_id', $businessId)->pluck('id');

        if ($roleIds->isNotEmpty()) {
            DB::table('role_has_permissions')->whereIn('role_id', $roleIds)->delete();
            DB::table('model_has_roles')
                ->where('model_type', User::class)
                ->whereIn('role_id', $roleIds)
                ->delete();
            Role::whereIn('id', $roleIds)->delete();
        }

        DB::table('model_has_permissions')
            ->where('model_type', User::class)
            ->where('model_id', '!=', $adminUserId)
            ->delete();

        DB::table('model_has_roles')
            ->where('model_type', User::class)
            ->where('model_id', '!=', $adminUserId)
            ->delete();
    }

    protected function ensureLocationPermission(int $locationId): void
    {
        Permission::firstOrCreate([
            'name' => 'location.'.$locationId,
            'guard_name' => 'web',
        ]);
    }

    protected function linkDefaultInvoiceSettingsToLocation(int $businessId, int $locationId): void
    {
        $scheme = InvoiceScheme::where('business_id', $businessId)->where('is_default', 1)->first();
        $layout = InvoiceLayout::where('business_id', $businessId)->where('is_default', 1)->first();

        if (empty($scheme) || empty($layout)) {
            return;
        }

        BusinessLocation::where('id', $locationId)->update([
            'invoice_scheme_id' => $scheme->id,
            'invoice_layout_id' => $layout->id,
            'sale_invoice_layout_id' => $layout->id,
        ]);
    }
}
