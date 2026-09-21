<?php

use App\Business;
use App\NotificationTemplate;
use App\Services\CreditControlService;
use App\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('transactions', 'credit_shipment_hold')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->boolean('credit_shipment_hold')->default(0)->after('shipping_status');
            });
        }

        if (! Schema::hasColumn('products', 'last_scanned_at')) {
            Schema::table('products', function (Blueprint $table) {
                $table->timestamp('last_scanned_at')->nullable()->after('is_inactive');
            });
        }

        // Force monetary values to exactly 2 decimal places.
        if (Schema::hasColumn('business', 'currency_precision')) {
            DB::table('business')->update(['currency_precision' => 2]);
        }

        if (! Permission::where('name', CreditControlService::SETTLE_PERMISSION)->exists()) {
            Permission::create([
                'name' => CreditControlService::SETTLE_PERMISSION,
                'guard_name' => 'web',
            ]);
        }

        $this->assignSettlerPermissionByName();
        $this->seedCreditNotificationTemplates();
    }

    public function down(): void
    {
        if (Schema::hasColumn('transactions', 'credit_shipment_hold')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('credit_shipment_hold');
            });
        }

        if (Schema::hasColumn('products', 'last_scanned_at')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('last_scanned_at');
            });
        }

        $permission = Permission::where('name', CreditControlService::SETTLE_PERMISSION)->first();
        if ($permission) {
            $permission->delete();
        }

        NotificationTemplate::whereIn('template_for', ['credit_limit_exceeded', 'overdue_balance'])->delete();
    }

    protected function assignSettlerPermissionByName(): void
    {
        $permission = CreditControlService::SETTLE_PERMISSION;
        $users = User::all();

        foreach ($users as $user) {
            $service = app(CreditControlService::class);
            if ($service->userMatchesAuthorizedSettlerName($user)) {
                if (! $user->hasPermissionTo($permission)) {
                    $user->givePermissionTo($permission);
                }
            }
        }
    }

    protected function seedCreditNotificationTemplates(): void
    {
        $businessIds = Business::pluck('id');
        foreach ($businessIds as $businessId) {
            foreach (['credit_limit_exceeded', 'overdue_balance'] as $templateFor) {
                $exists = NotificationTemplate::where('business_id', $businessId)
                    ->where('template_for', $templateFor)
                    ->exists();
                if ($exists) {
                    continue;
                }

                if ($templateFor === 'credit_limit_exceeded') {
                    NotificationTemplate::create([
                        'business_id' => $businessId,
                        'template_for' => $templateFor,
                        'subject' => 'Credit limit exceeded, from {business_name}',
                        'email_body' => '<p>Dear {contact_name},</p><p>Your account has exceeded the prescribed credit limit of {credit_limit}. Your outstanding balance is {due_amount}. New orders may be placed, but shipment is on hold until the outstanding balance is fully cleared.</p><p>{business_logo}</p>',
                        'sms_body' => 'Dear {contact_name}, your credit limit ({credit_limit}) is exceeded. Outstanding: {due_amount}. Orders allowed; shipment held until balance is cleared. {business_name}',
                        'whatsapp_text' => 'Dear {contact_name}, your credit limit ({credit_limit}) is exceeded. Outstanding: {due_amount}. Orders allowed; shipment held until balance is cleared. {business_name}',
                        'auto_send' => 1,
                        'auto_send_sms' => 1,
                    ]);
                } else {
                    NotificationTemplate::create([
                        'business_id' => $businessId,
                        'template_for' => $templateFor,
                        'subject' => 'Overdue balance, from {business_name}',
                        'email_body' => '<p>Dear {contact_name},</p><p>You have an overdue outstanding balance of {due_amount}. New orders may be placed, but shipment is on hold until the outstanding balance is fully cleared.</p><p>{business_logo}</p>',
                        'sms_body' => 'Dear {contact_name}, you have an overdue balance of {due_amount}. Orders allowed; shipment held until balance is cleared. {business_name}',
                        'whatsapp_text' => 'Dear {contact_name}, you have an overdue balance of {due_amount}. Orders allowed; shipment held until balance is cleared. {business_name}',
                        'auto_send' => 1,
                        'auto_send_sms' => 1,
                    ]);
                }
            }
        }
    }
};
