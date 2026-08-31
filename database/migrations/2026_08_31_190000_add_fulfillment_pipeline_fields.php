<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('transactions', 'order_source')) {
                $table->string('order_source', 32)->nullable()->after('source')->index();
            }
            if (! Schema::hasColumn('transactions', 'fulfillment_stage')) {
                $table->string('fulfillment_stage', 24)->nullable()->after('shipping_status')->index();
            }
            if (! Schema::hasColumn('transactions', 'fulfillment_meta')) {
                $table->json('fulfillment_meta')->nullable()->after('fulfillment_stage');
            }
        });

        Schema::table('transaction_sell_lines', function (Blueprint $table) {
            if (! Schema::hasColumn('transaction_sell_lines', 'qty_picked')) {
                $table->decimal('qty_picked', 22, 4)->default(0)->after('quantity');
            }
            if (! Schema::hasColumn('transaction_sell_lines', 'qty_packed')) {
                $table->decimal('qty_packed', 22, 4)->default(0)->after('qty_picked');
            }
        });

        DB::table('transactions')
            ->whereIn('source', ['ecommerce', 'sale_app'])
            ->whereNull('order_source')
            ->update(['order_source' => DB::raw('source')]);

        DB::table('transactions')
            ->whereIn('order_source', ['ecommerce', 'sale_app'])
            ->whereNull('fulfillment_stage')
            ->where(function ($q) {
                $q->whereNull('shipping_status')->orWhere('shipping_status', 'ordered');
            })
            ->update(['fulfillment_stage' => 'picking', 'shipping_status' => 'ordered']);

        DB::table('transactions')
            ->whereIn('order_source', ['ecommerce', 'sale_app'])
            ->whereNull('fulfillment_stage')
            ->where('shipping_status', 'packed')
            ->update(['fulfillment_stage' => 'packing']);

        DB::table('transactions')
            ->whereIn('order_source', ['ecommerce', 'sale_app'])
            ->whereNull('fulfillment_stage')
            ->whereIn('shipping_status', ['shipped', 'delivered'])
            ->update(['fulfillment_stage' => 'shipped']);
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'fulfillment_meta')) {
                $table->dropColumn('fulfillment_meta');
            }
            if (Schema::hasColumn('transactions', 'fulfillment_stage')) {
                $table->dropColumn('fulfillment_stage');
            }
            if (Schema::hasColumn('transactions', 'order_source')) {
                $table->dropColumn('order_source');
            }
        });

        Schema::table('transaction_sell_lines', function (Blueprint $table) {
            if (Schema::hasColumn('transaction_sell_lines', 'qty_packed')) {
                $table->dropColumn('qty_packed');
            }
            if (Schema::hasColumn('transaction_sell_lines', 'qty_picked')) {
                $table->dropColumn('qty_picked');
            }
        });
    }
};
