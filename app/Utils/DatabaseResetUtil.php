<?php

namespace App\Utils;

use App\Business;
use App\BusinessLocation;
use App\Contact;
use App\InvoiceLayout;
use App\InvoiceScheme;
use App\NotificationTemplate;
use App\Services\Msa\MsaSetupService;
use App\Unit;
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
     * Tables that are never truncated (global / structural / permanent config).
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
        // Keep current invoice & printer configuration for the business
        'invoice_schemes',
        'invoice_layouts',
        'printers',
    ];

    /**
     * Config tables scoped by business_id: delete only other businesses, keep business #1.
     */
    protected array $businessScopedPreserveTables = [
        'invoice_schemes',
        'invoice_layouts',
        'printers',
    ];

    /**
     * Reset operational data; keep business #1, location #1, admin, business settings, and invoice settings.
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

        // Snapshot invoice picks on location (so selected layout/scheme stay after reset)
        $locationInvoiceSettings = $this->snapshotLocationInvoiceSettings($location);

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

            // Remove other-business rows from preserved config tables (business #1 stays)
            $this->purgeOtherBusinessConfigRows(self::PRESERVED_BUSINESS_ID);

            $this->purgeOtherBusinessesAndUsers($adminUserId);
            $this->purgeRolesAndPermissionsForBusiness(self::PRESERVED_BUSINESS_ID, $adminUserId);
            $this->ensureLocationPermission(self::PRESERVED_LOCATION_ID);

            // Roles, Walk-In, unit, notifications — do NOT recreate invoice defaults / overwrite business settings
            $this->seedFreshPosRequiredResources(self::PRESERVED_BUSINESS_ID, $adminUserId);

            // Re-apply previously selected invoice scheme / layout on location #1
            $this->restoreLocationInvoiceSettings(
                self::PRESERVED_BUSINESS_ID,
                self::PRESERVED_LOCATION_ID,
                $locationInvoiceSettings
            );

            $moduleUtil = new ModuleUtil();
            if (config('app.env') != 'demo') {
                $business = Business::find(self::PRESERVED_BUSINESS_ID);
                $moduleUtil->getModuleData('after_business_created', ['business' => $business]);
            }

            // Recreate MSA Tobacco structure only (no demo products / stock / sales).
            $this->restoreMsaTobaccoSetup(self::PRESERVED_BUSINESS_ID);

            // Safety: wipe any operational leftovers that setup hooks may have re-seeded.
            $this->ensureOperationalDataCleared();

            // Location may have been touched by hooks — re-assert invoice picks
            $this->restoreLocationInvoiceSettings(
                self::PRESERVED_BUSINESS_ID,
                self::PRESERVED_LOCATION_ID,
                $locationInvoiceSettings
            );
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        Artisan::call('cache:clear');
        Artisan::call('view:clear');
    }

    /**
     * Capture location's selected invoice scheme / layout IDs.
     */
    protected function snapshotLocationInvoiceSettings(BusinessLocation $location): array
    {
        $attrs = $location->getAttributes();

        return [
            'invoice_scheme_id' => $attrs['invoice_scheme_id'] ?? null,
            'invoice_layout_id' => $attrs['invoice_layout_id'] ?? null,
            'sale_invoice_layout_id' => $attrs['sale_invoice_layout_id'] ?? null,
            'sale_invoice_scheme_id' => $attrs['sale_invoice_scheme_id'] ?? null,
        ];
    }

    /**
     * Delete config rows belonging to other businesses; keep rows for preserved business.
     */
    protected function purgeOtherBusinessConfigRows(int $businessId): void
    {
        foreach ($this->businessScopedPreserveTables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'business_id')) {
                continue;
            }
            DB::table($table)->where('business_id', '!=', $businessId)->delete();
        }
    }

    /**
     * Minimal resources needed for POS after wipe — without overwriting invoice/business settings.
     */
    protected function seedFreshPosRequiredResources(int $businessId, int $userId): void
    {
        $user = User::find($userId);
        if (empty($user)) {
            throw new \RuntimeException('Admin user not found while seeding fresh POS resources.');
        }

        // Admin role
        $adminRoleName = 'Admin#'.$businessId;
        $role = Role::where('name', $adminRoleName)->where('guard_name', 'web')->first();
        if (empty($role)) {
            $role = Role::create([
                'name' => $adminRoleName,
                'business_id' => $businessId,
                'guard_name' => 'web',
                'is_default' => 1,
            ]);
        }
        if (! $user->hasRole($role->name)) {
            $user->assignRole($role->name);
        }

        // Cashier role
        $cashierRoleName = 'Cashier#'.$businessId;
        $cashierRole = Role::where('name', $cashierRoleName)->where('guard_name', 'web')->first();
        if (empty($cashierRole)) {
            $cashierRole = Role::create([
                'name' => $cashierRoleName,
                'business_id' => $businessId,
                'guard_name' => 'web',
            ]);
            $cashierRole->syncPermissions([
                'sell.view',
                'sell.create',
                'sell.update',
                'sell.delete',
                'access_all_locations',
                'view_cash_register',
                'close_cash_register',
            ]);
        }

        // Walk-In Customer
        $walkIn = Contact::where('business_id', $businessId)
            ->where('is_default', 1)
            ->where('type', 'customer')
            ->first();

        if (empty($walkIn)) {
            $businessUtil = new BusinessUtil();
            $ref_count = $businessUtil->setAndGetReferenceCount('contacts', $businessId);
            $contact_id = $businessUtil->generateReferenceNumber('contacts', $ref_count, $businessId);

            Contact::create([
                'business_id' => $businessId,
                'type' => 'customer',
                'name' => 'Walk-In Customer',
                'created_by' => $userId,
                'is_default' => 1,
                'contact_id' => $contact_id,
                'credit_limit' => null,
            ]);
        }

        // Invoice scheme / layout: keep existing. Only create fallback if none remain.
        if (! InvoiceScheme::where('business_id', $businessId)->exists()) {
            InvoiceScheme::create([
                'name' => 'Default',
                'scheme_type' => 'blank',
                'prefix' => '',
                'start_number' => 1,
                'total_digits' => 4,
                'is_default' => 1,
                'business_id' => $businessId,
            ]);
        }

        if (! InvoiceLayout::where('business_id', $businessId)->exists()) {
            InvoiceLayout::create(InvoiceLayoutDefaults::attributes($businessId, true));
        }

        // Default unit if none left
        if (! Unit::where('business_id', $businessId)->exists()) {
            Unit::create([
                'business_id' => $businessId,
                'actual_name' => 'Pieces',
                'short_name' => 'Pc(s)',
                'allow_decimal' => 0,
                'created_by' => $userId,
            ]);
        }

        // Notification templates only if table empty for this business
        if (! NotificationTemplate::where('business_id', $businessId)->exists()) {
            $notification_templates = NotificationTemplate::defaultNotificationTemplates($businessId);
            foreach ($notification_templates as $notification_template) {
                NotificationTemplate::create($notification_template);
            }
        }
    }

    /**
     * Keep the same invoice scheme/layout that was selected before reset.
     * Falls back to is_default only if previous IDs no longer exist.
     */
    protected function restoreLocationInvoiceSettings(int $businessId, int $locationId, array $previous): void
    {
        $schemeId = $previous['invoice_scheme_id'] ?? null;
        $layoutId = $previous['invoice_layout_id'] ?? null;
        $saleLayoutId = $previous['sale_invoice_layout_id'] ?? null;
        $saleSchemeId = $previous['sale_invoice_scheme_id'] ?? null;

        if (empty($schemeId) || ! InvoiceScheme::where('business_id', $businessId)->where('id', $schemeId)->exists()) {
            $schemeId = optional(
                InvoiceScheme::where('business_id', $businessId)->where('is_default', 1)->first()
            )->id
                ?? optional(InvoiceScheme::where('business_id', $businessId)->orderBy('id')->first())->id;
        }

        if (empty($layoutId) || ! InvoiceLayout::where('business_id', $businessId)->where('id', $layoutId)->exists()) {
            $layoutId = optional(
                InvoiceLayout::where('business_id', $businessId)->where('is_default', 1)->first()
            )->id
                ?? optional(InvoiceLayout::where('business_id', $businessId)->orderBy('id')->first())->id;
        }

        if (empty($saleLayoutId) || ! InvoiceLayout::where('business_id', $businessId)->where('id', $saleLayoutId)->exists()) {
            $saleLayoutId = $layoutId;
        }

        $update = [];
        if (Schema::hasColumn('business_locations', 'invoice_scheme_id') && ! empty($schemeId)) {
            $update['invoice_scheme_id'] = $schemeId;
        }
        if (Schema::hasColumn('business_locations', 'invoice_layout_id') && ! empty($layoutId)) {
            $update['invoice_layout_id'] = $layoutId;
        }
        if (Schema::hasColumn('business_locations', 'sale_invoice_layout_id') && ! empty($saleLayoutId)) {
            $update['sale_invoice_layout_id'] = $saleLayoutId;
        }
        if (Schema::hasColumn('business_locations', 'sale_invoice_scheme_id')) {
            if (empty($saleSchemeId) || ! InvoiceScheme::where('business_id', $businessId)->where('id', $saleSchemeId)->exists()) {
                $saleSchemeId = $schemeId;
            }
            if (! empty($saleSchemeId)) {
                $update['sale_invoice_scheme_id'] = $saleSchemeId;
            }
        }

        if (! empty($update)) {
            BusinessLocation::where('id', $locationId)->update($update);
        }
    }

    /**
     * Restore MSA Tobacco categories, price/customer groups, tax rates, and labels only.
     * Does not recreate demo products, opening stock, or sample transactions.
     */
    protected function restoreMsaTobaccoSetup(int $businessId): void
    {
        $business = Business::find($businessId);
        if (empty($business)) {
            return;
        }

        app(MsaSetupService::class)->setupForBusiness($business, false);
    }

    /**
     * Guarantee a blank POS: no products, stock, or transaction history after reset.
     */
    protected function ensureOperationalDataCleared(): void
    {
        $tables = [
            'transaction_sell_lines_purchase_lines',
            'transaction_sell_lines',
            'purchase_lines',
            'stock_adjustment_lines',
            'transaction_payments',
            'cash_register_transactions',
            'account_transactions',
            'transactions',
            'cash_registers',
            'variation_location_details',
            'variation_group_prices',
            'discount_variations',
            'product_variations',
            'variations',
            'product_locations',
            'product_racks',
            'res_product_modifier_sets',
            'products',
            'stock_adjustments',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        // Keep only business location #1.
        if (Schema::hasTable('business_locations')) {
            DB::table('business_locations')->where('id', '!=', self::PRESERVED_LOCATION_ID)->delete();
        }
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
}
