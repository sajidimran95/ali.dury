<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulk_price_updates', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('business_id');
            $table->unsignedInteger('created_by');
            $table->string('update_type')->comment('percentage or flat');
            $table->decimal('update_value', 22, 4)->comment('percentage or flat amount');
            $table->string('price_field')->comment('default_sell_price or sell_price_inc_tax');
            $table->text('filters')->nullable()->comment('JSON: category, brand, etc.');
            $table->integer('products_count')->default(0);
            $table->text('product_ids')->nullable()->comment('Comma-separated IDs');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });

        // Add permission
        $permission = \Spatie\Permission\Models\Permission::create([
            'name' => 'bulk_pricing.access',
            'guard_name' => 'web',
        ]);

        // Give admin role the permission
        $adminRole = \Spatie\Permission\Models\Role::where('name', 'Admin#1')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permission);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_price_updates');
        
        \Spatie\Permission\Models\Permission::where('name', 'bulk_pricing.access')->delete();
    }
};
