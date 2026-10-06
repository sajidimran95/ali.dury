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
        $password = Hash::make('12345678');
        $businessId = 1;

        // Salesman
        $salesman = DB::table('users')->where('email', 'sales@japspos.com')->first();
        if (!$salesman) {
            DB::table('users')->insert([
                'user_type' => 'user',
                'first_name' => 'Sales',
                'username' => 'sales',
                'email' => 'sales@japspos.com',
                'password' => $password,
                'business_id' => $businessId,
                'allow_login' => 1,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('users')->where('id', $salesman->id)->update([
                'password' => $password,
                'business_id' => $businessId,
                'deleted_at' => null,
                'allow_login' => 1,
                'status' => 'active',
            ]);
        }

        // Deliveryman
        $delivery = DB::table('users')->where('email', 'delivery@gmail.com')->first();
        if (!$delivery) {
            DB::table('users')->insert([
                'user_type' => 'user',
                'surname' => 'Delivery',
                'first_name' => 'Delivery',
                'last_name' => 'Man',
                'username' => 'delivery',
                'email' => 'delivery@gmail.com',
                'password' => $password,
                'business_id' => $businessId,
                'allow_login' => 1,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('users')->where('id', $delivery->id)->update([
                'password' => $password,
                'business_id' => $businessId,
                'deleted_at' => null,
                'allow_login' => 1,
                'status' => 'active',
            ]);
        }
    }

    public function down(): void
    {
    }
};
