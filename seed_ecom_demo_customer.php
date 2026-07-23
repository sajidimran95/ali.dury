<?php

/**
 * Creates / updates a demo wholesale customer for storefront login.
 * Run: php seed_ecom_demo_customer.php
 */

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Contact;
use Illuminate\Support\Facades\Hash;
use Modules\Ecommerce\Entities\EcomCustomerAddress;
use Modules\Ecommerce\Entities\EcommerceSetting;
use Modules\Ecommerce\Support\WholesaleAccount;

$businessId = (int) (EcommerceSetting::where('is_enabled', 1)->value('business_id')
    ?? App\Business::query()->value('id')
    ?? 1);

$email = 'demo@japswholesale.test';
$mobile = '5551234567';
$password = 'Demo1234';

$payload = [
    'business_id' => $businessId,
    'type' => 'customer',
    'name' => 'Demo Buyer',
    'supplier_business_name' => 'Demo Wholesale LLC',
    'ecom_first_name' => 'Demo',
    'ecom_last_name' => 'Buyer',
    'email' => $email,
    'mobile' => $mobile,
    'tax_number' => 'TAX-DEMO-001',
    'address_line_1' => '100 Demo Street',
    'city' => 'Dallas',
    'state' => 'TX',
    'zip_code' => '75001',
    'country' => 'United States',
    'password' => Hash::make($password),
    'created_by' => 1,
    'contact_status' => 'active',
    'custom_field6' => WholesaleAccount::APPROVED,
];

$contact = Contact::withTrashed()
    ->where('business_id', $businessId)
    ->where(function ($q) use ($email, $mobile) {
        $q->where('email', $email)->orWhere('mobile', $mobile);
    })
    ->first();

if ($contact) {
    if ($contact->trashed()) {
        $contact->restore();
    }
    $contact->fill($payload);
    $contact->save();
    $action = 'updated';
} else {
    $contact = Contact::create($payload);
    $action = 'created';
}

$hasAddress = EcomCustomerAddress::where('contact_id', $contact->id)->exists();
if (! $hasAddress) {
    EcomCustomerAddress::create([
        'business_id' => $businessId,
        'contact_id' => $contact->id,
        'first_name' => 'Demo',
        'last_name' => 'Buyer',
        'company' => 'Demo Wholesale LLC',
        'address_line_1' => '100 Demo Street',
        'city' => 'Dallas',
        'state' => 'TX',
        'zip_code' => '75001',
        'country' => 'United States',
        'phone' => $mobile,
        'is_default' => 1,
    ]);
}

echo "Demo customer {$action}\n";
echo "Business ID: {$businessId}\n";
echo "Contact ID: {$contact->id}\n";
echo "Email / login: {$email}\n";
echo "Mobile / login: {$mobile}\n";
echo "Password: {$password}\n";
echo "Status: approved\n";
