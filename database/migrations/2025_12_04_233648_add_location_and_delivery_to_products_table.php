<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Location fields
            $table->string('location_address')->nullable()->after('images');
            $table->string('location_city')->nullable()->after('location_address');
            $table->string('location_state')->nullable()->after('location_city');
            $table->string('location_country')->default('USA')->after('location_state');
            $table->string('location_zip')->nullable()->after('location_country');
            $table->decimal('location_latitude', 10, 8)->nullable()->after('location_zip');
            $table->decimal('location_longitude', 11, 8)->nullable()->after('location_latitude');

            // Delivery options
            $table->boolean('delivery_available')->default(false)->after('location_longitude');
            $table->decimal('delivery_fee', 8, 2)->nullable()->after('delivery_available');
            $table->integer('delivery_radius_km')->nullable()->after('delivery_fee')->comment('Delivery radius in kilometers');
            $table->boolean('pickup_available')->default(true)->after('delivery_radius_km');

            // Additional rental info
            $table->enum('product_condition', ['new', 'like_new', 'good', 'fair', 'worn'])->default('good')->after('pickup_available');
            $table->decimal('security_deposit', 10, 2)->nullable()->after('product_condition');
            $table->integer('min_rental_days')->default(1)->after('security_deposit');
            $table->integer('max_rental_days')->nullable()->after('min_rental_days');

            // Pricing tiers
            $table->decimal('price_per_week', 10, 2)->nullable()->after('max_rental_days');
            $table->decimal('price_per_month', 10, 2)->nullable()->after('price_per_week');

            $table->index(['location_city', 'location_state']);
            $table->index(['delivery_available', 'pickup_available']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'location_address',
                'location_city',
                'location_state',
                'location_country',
                'location_zip',
                'location_latitude',
                'location_longitude',
                'delivery_available',
                'delivery_fee',
                'delivery_radius_km',
                'pickup_available',
                'product_condition',
                'security_deposit',
                'min_rental_days',
                'max_rental_days',
                'price_per_week',
                'price_per_month',
            ]);
        });
    }
};
