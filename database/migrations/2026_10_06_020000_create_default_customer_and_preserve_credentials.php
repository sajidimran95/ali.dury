<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure customer contact with specified credentials exists
        $businessId = 1;
        $customerEmail = 'customer@example.com';
        $password = Hash::make('12345678');

        $contact = DB::table('contacts')->where('business_id', $businessId)->where('email', $customerEmail)->first();
        if (!$contact) {
            // Need to generate contact_id - use BusinessUtil
            $businessUtil = new \App\Utils\BusinessUtil();
            $ref_count = $businessUtil->setAndGetReferenceCount('contacts', $businessId);
            $contact_id = $businessUtil->generateReferenceNumber('contacts', $ref_count, $businessId);
            DB::table('contacts')->insert([
                'business_id' => $businessId,
                'type' => 'customer',
                'name' => 'Customer',
                'first_name' => 'Customer',
                'email' => $customerEmail,
                'password' => $password,
                'contact_id' => $contact_id,
                'created_by' => 1,
                'mobile' => '012548963145',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('contacts')->where('id', $contact->id)->update([
                'password' => $password,
                'deleted_at' => null,
            ]);
        }
    }

    public function down(): void
    {
        // Do nothing on rollback
    }
};
