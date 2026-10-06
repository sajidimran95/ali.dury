<?php

namespace Database\Seeders;

use App\Business;
use App\Contact;
use App\User;
use App\Utils\BusinessUtil;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Default Sale / Delivery / Customer portal logins for every fresh migrate/seed.
 *
 * Sale:     username sales     / 12345678  → Sales Representative#{business}
 * Delivery: username delivery  / 12345678  → Delivery#{business}
 * Customer: mobile 012548963145 / 12345678 → customer app contact
 */
class DefaultPortalCredentialsSeeder extends Seeder
{
    public const PASSWORD = '12345678';

    public const SALES_USERNAME = 'sales';

    public const SALES_EMAIL = 'sales@japspos.com';

    public const DELIVERY_USERNAME = 'delivery';

    public const DELIVERY_EMAIL = 'delivery@gmail.com';

    public const CUSTOMER_EMAIL = 'customer@example.com';

    public const CUSTOMER_MOBILE = '012548963145';

    public function run(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasTable('contacts') || ! Schema::hasTable('business')) {
            return;
        }

        $business = Business::query()->orderBy('id')->first();
        if (empty($business)) {
            return;
        }

        $businessId = (int) $business->id;
        $password = Hash::make(self::PASSWORD);
        $createdBy = (int) ($business->owner_id ?: User::where('business_id', $businessId)->value('id') ?: 1);

        $this->ensureSalesUser($businessId, $password);
        $this->ensureDeliveryUser($businessId, $password);
        $this->ensureCustomerContact($businessId, $password, $createdBy);
        $this->ensureRolesAndAssignments($businessId);
    }

    protected function ensureSalesUser(int $businessId, string $password): void
    {
        $user = User::withTrashed()
            ->where(function ($q) {
                $q->where('email', self::SALES_EMAIL)
                    ->orWhere('username', self::SALES_USERNAME);
            })
            ->first();

        $payload = [
            'user_type' => 'user',
            'surname' => 'Sales',
            'first_name' => 'Sales',
            'last_name' => 'Rep',
            'username' => self::SALES_USERNAME,
            'email' => self::SALES_EMAIL,
            'password' => $password,
            'business_id' => $businessId,
            'allow_login' => 1,
            'status' => 'active',
            'deleted_at' => null,
        ];

        if (empty($user)) {
            User::create($payload);
        } else {
            $user->fill($payload);
            $user->save();
        }
    }

    protected function ensureDeliveryUser(int $businessId, string $password): void
    {
        $user = User::withTrashed()
            ->where(function ($q) {
                $q->where('email', self::DELIVERY_EMAIL)
                    ->orWhere('username', self::DELIVERY_USERNAME);
            })
            ->first();

        $payload = [
            'user_type' => 'user',
            'surname' => 'Delivery',
            'first_name' => 'Delivery',
            'last_name' => 'Man',
            'username' => self::DELIVERY_USERNAME,
            'email' => self::DELIVERY_EMAIL,
            'password' => $password,
            'business_id' => $businessId,
            'allow_login' => 1,
            'status' => 'active',
            'deleted_at' => null,
        ];

        if (empty($user)) {
            User::create($payload);
        } else {
            $user->fill($payload);
            $user->save();
        }
    }

    protected function ensureCustomerContact(int $businessId, string $password, int $createdBy): void
    {
        $contact = Contact::withTrashed()
            ->where('business_id', $businessId)
            ->where(function ($q) {
                $q->where('email', self::CUSTOMER_EMAIL)
                    ->orWhere('mobile', self::CUSTOMER_MOBILE);
            })
            ->first();

        $payload = [
            'business_id' => $businessId,
            'type' => 'customer',
            'name' => 'Customer',
            'first_name' => 'Customer',
            'email' => self::CUSTOMER_EMAIL,
            'mobile' => self::CUSTOMER_MOBILE,
            'password' => $password,
            'created_by' => $createdBy,
            'deleted_at' => null,
        ];

        if (Schema::hasColumn('contacts', 'allow_customer_app')) {
            $payload['allow_customer_app'] = 1;
        }

        if (empty($contact)) {
            $businessUtil = new BusinessUtil();
            $refCount = $businessUtil->setAndGetReferenceCount('contacts', $businessId);
            $payload['contact_id'] = $businessUtil->generateReferenceNumber('contacts', $refCount, $businessId);
            Contact::create($payload);
        } else {
            $contact->fill($payload);
            $contact->save();
        }
    }

    protected function ensureRolesAndAssignments(int $businessId): void
    {
        $businessUtil = new BusinessUtil();
        $salesRepRole = $businessUtil->createDefaultSalesRole($businessId, true);

        foreach (['delivery.driver.view', 'delivery.driver.edit'] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $deliveryRoleName = 'Delivery#'.$businessId;
        $deliveryRole = Role::where('name', $deliveryRoleName)
            ->where('guard_name', 'web')
            ->where('business_id', $businessId)
            ->first();

        if (empty($deliveryRole)) {
            $deliveryRole = Role::create([
                'name' => $deliveryRoleName,
                'business_id' => $businessId,
                'guard_name' => 'web',
            ]);
        }

        $deliveryRole->syncPermissions(['delivery.driver.view', 'delivery.driver.edit']);

        $salesman = User::where('email', self::SALES_EMAIL)->where('business_id', $businessId)->first();
        if ($salesman) {
            $salesman->syncRoles([$salesRepRole->name]);
        }

        $deliveryman = User::where('email', self::DELIVERY_EMAIL)->where('business_id', $businessId)->first();
        if ($deliveryman) {
            $deliveryman->syncRoles([$deliveryRole->name]);
        }
    }
}
