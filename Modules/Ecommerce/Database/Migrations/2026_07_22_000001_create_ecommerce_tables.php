<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'sales_channel')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('sales_channel', 30)->default('pos_only')->after('not_for_selling');
                $table->string('ecom_slug')->nullable()->after('sales_channel');
                $table->text('ecom_short_description')->nullable();
                $table->longText('ecom_long_description')->nullable();
                $table->string('ecom_seo_title')->nullable();
                $table->text('ecom_seo_description')->nullable();
                $table->boolean('ecom_is_featured')->default(0);
                $table->boolean('ecom_is_new_arrival')->default(0);
                $table->decimal('ecom_online_price', 22, 4)->nullable();
                $table->decimal('ecom_discount_price', 22, 4)->nullable();
                $table->json('ecom_gallery')->nullable();
                $table->index(['business_id', 'sales_channel']);
                $table->index(['business_id', 'ecom_slug']);
            });
        }

        if (! Schema::hasColumn('contacts', 'password')) {
            Schema::table('contacts', function (Blueprint $table) {
                $table->string('password')->nullable()->after('email');
                $table->string('ecom_first_name')->nullable()->after('password');
                $table->string('ecom_last_name')->nullable()->after('ecom_first_name');
                $table->timestamp('email_verified_at')->nullable()->after('ecom_last_name');
                $table->rememberToken();
            });
        }

        if (! Schema::hasTable('password_resets_contacts')) {
            Schema::create('password_resets_contacts', function (Blueprint $table) {
                $table->string('email')->index();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        if (! Schema::hasTable('ecommerce_settings')) {
            Schema::create('ecommerce_settings', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('business_id')->unique();
                $table->boolean('is_enabled')->default(0);
                $table->string('store_name')->nullable();
                $table->string('store_logo')->nullable();
                $table->unsignedInteger('location_id')->nullable();
                $table->boolean('enable_cod')->default(1);
                $table->boolean('enable_stripe')->default(0);
                $table->boolean('enable_paypal')->default(0);
                $table->string('stripe_key')->nullable();
                $table->string('stripe_secret')->nullable();
                $table->string('paypal_client_id')->nullable();
                $table->string('paypal_secret')->nullable();
                $table->string('default_country')->default('United States');
                $table->string('seo_title')->nullable();
                $table->text('seo_description')->nullable();
                $table->text('contact_email')->nullable();
                $table->string('contact_phone')->nullable();
                $table->json('extra')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ecom_api_settings')) {
            Schema::create('ecom_api_settings', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('business_id');
                $table->string('api_token')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ecom_banners')) {
            Schema::create('ecom_banners', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('business_id');
                $table->string('title')->nullable();
                $table->string('subtitle')->nullable();
                $table->string('image')->nullable();
                $table->string('button_text')->nullable();
                $table->string('button_link')->nullable();
                $table->boolean('is_active')->default(1);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ecom_collections')) {
            Schema::create('ecom_collections', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('business_id');
                $table->string('name');
                $table->string('slug');
                $table->text('description')->nullable();
                $table->string('image')->nullable();
                $table->boolean('is_active')->default(1);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                $table->unique(['business_id', 'slug']);
            });
        }

        if (! Schema::hasTable('ecom_collection_products')) {
            Schema::create('ecom_collection_products', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('collection_id');
                $table->unsignedInteger('product_id');
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                $table->unique(['collection_id', 'product_id']);
            });
        }

        if (! Schema::hasTable('ecom_customer_addresses')) {
            Schema::create('ecom_customer_addresses', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('business_id');
                $table->unsignedInteger('contact_id');
                $table->string('first_name');
                $table->string('last_name');
                $table->string('company')->nullable();
                $table->string('address_line_1');
                $table->string('address_line_2')->nullable();
                $table->string('city');
                $table->string('state', 100);
                $table->string('zip_code', 20);
                $table->string('country')->default('United States');
                $table->string('phone')->nullable();
                $table->boolean('is_default')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ecom_carts')) {
            Schema::create('ecom_carts', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('business_id');
                $table->unsignedInteger('contact_id')->nullable();
                $table->string('session_id')->nullable()->index();
                $table->string('coupon_code')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ecom_cart_items')) {
            Schema::create('ecom_cart_items', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('cart_id');
                $table->unsignedInteger('product_id');
                $table->unsignedInteger('variation_id');
                $table->decimal('quantity', 22, 4)->default(1);
                $table->decimal('unit_price', 22, 4)->default(0);
                $table->timestamps();
                $table->unique(['cart_id', 'variation_id']);
            });
        }

        if (! Schema::hasTable('ecom_wishlists')) {
            Schema::create('ecom_wishlists', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('business_id');
                $table->unsignedInteger('contact_id');
                $table->unsignedInteger('product_id');
                $table->unsignedInteger('variation_id')->nullable();
                $table->timestamps();
                $table->unique(['contact_id', 'product_id', 'variation_id']);
            });
        }

        if (! Schema::hasTable('ecom_coupons')) {
            Schema::create('ecom_coupons', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('business_id');
                $table->string('code');
                $table->string('type')->default('fixed'); // fixed|percentage
                $table->decimal('value', 22, 4)->default(0);
                $table->decimal('min_order_amount', 22, 4)->nullable();
                $table->integer('usage_limit')->nullable();
                $table->integer('used_count')->default(0);
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->boolean('is_active')->default(1);
                $table->timestamps();
                $table->unique(['business_id', 'code']);
            });
        }

        if (! Schema::hasTable('ecom_coupon_usages')) {
            Schema::create('ecom_coupon_usages', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('coupon_id');
                $table->unsignedInteger('contact_id')->nullable();
                $table->unsignedInteger('transaction_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ecom_reviews')) {
            Schema::create('ecom_reviews', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('business_id');
                $table->unsignedInteger('product_id');
                $table->unsignedInteger('contact_id')->nullable();
                $table->string('author_name')->nullable();
                $table->unsignedTinyInteger('rating')->default(5);
                $table->text('review')->nullable();
                $table->json('images')->nullable();
                $table->string('status')->default('pending'); // pending|approved|rejected
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ecom_shipping_rules')) {
            Schema::create('ecom_shipping_rules', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('business_id');
                $table->string('name');
                $table->string('type')->default('flat'); // flat|free|weight|state
                $table->decimal('amount', 22, 4)->default(0);
                $table->decimal('min_order_amount', 22, 4)->nullable();
                $table->decimal('max_weight', 22, 4)->nullable();
                $table->json('states')->nullable();
                $table->boolean('is_active')->default(1);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ecom_tax_rules')) {
            Schema::create('ecom_tax_rules', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('business_id');
                $table->string('name');
                $table->string('level')->default('state'); // state|county|city
                $table->string('state', 100)->nullable();
                $table->string('county')->nullable();
                $table->string('city')->nullable();
                $table->decimal('rate', 8, 4)->default(0);
                $table->boolean('is_active')->default(1);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ecom_order_meta')) {
            Schema::create('ecom_order_meta', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('business_id');
                $table->unsignedInteger('transaction_id')->unique();
                $table->string('tracking_token')->unique();
                $table->string('fulfillment_status')->default('pending');
                $table->json('timeline')->nullable();
                $table->json('shipping_address')->nullable();
                $table->json('billing_address')->nullable();
                $table->unsignedInteger('coupon_id')->nullable();
                $table->string('payment_method')->nullable();
                $table->string('customer_email')->nullable();
                $table->string('customer_phone')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ecom_order_meta');
        Schema::dropIfExists('ecom_tax_rules');
        Schema::dropIfExists('ecom_shipping_rules');
        Schema::dropIfExists('ecom_reviews');
        Schema::dropIfExists('ecom_coupon_usages');
        Schema::dropIfExists('ecom_coupons');
        Schema::dropIfExists('ecom_wishlists');
        Schema::dropIfExists('ecom_cart_items');
        Schema::dropIfExists('ecom_carts');
        Schema::dropIfExists('ecom_customer_addresses');
        Schema::dropIfExists('ecom_collection_products');
        Schema::dropIfExists('ecom_collections');
        Schema::dropIfExists('ecom_banners');
        Schema::dropIfExists('ecom_api_settings');
        Schema::dropIfExists('ecommerce_settings');
    }
};
