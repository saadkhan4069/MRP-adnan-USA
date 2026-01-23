<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('woocommerce_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('api_setting_id')->nullable();
            $table->string('platform_order_id'); // Original order ID from platform
            $table->string('order_number')->unique();
            $table->string('status')->default('pending'); // pending, processing, completed, cancelled, refunded
            $table->string('currency', 10)->default('PKR');
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_total', 15, 2)->default(0);
            $table->decimal('shipping_total', 15, 2)->default(0);
            $table->decimal('tax_total', 15, 2)->default(0);
            
            // Customer Info
            $table->unsignedBigInteger('customer_id')->nullable(); // Link to customers table
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_first_name')->nullable();
            $table->string('customer_last_name')->nullable();
            
            // Billing Address
            $table->text('billing_address')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_state')->nullable();
            $table->string('billing_postcode')->nullable();
            $table->string('billing_country')->nullable();
            
            // Shipping Address
            $table->text('shipping_address')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_state')->nullable();
            $table->string('shipping_postcode')->nullable();
            $table->string('shipping_country')->nullable();
            
            // Payment Info
            $table->string('payment_method')->nullable();
            $table->string('payment_method_title')->nullable();
            $table->string('transaction_id')->nullable();
            
            // Dates
            $table->timestamp('order_date')->nullable();
            $table->timestamp('date_created')->nullable();
            $table->timestamp('date_modified')->nullable();
            $table->timestamp('date_completed')->nullable();
            $table->timestamp('date_paid')->nullable();
            
            // Additional Data
            $table->text('customer_note')->nullable();
            $table->text('order_notes')->nullable();
            $table->json('line_items')->nullable(); // Store order items as JSON
            $table->json('meta_data')->nullable(); // Store additional metadata
            $table->json('raw_data')->nullable(); // Store complete API response
            
            $table->boolean('is_synced')->default(false);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            
            $table->foreign('api_setting_id')->references('id')->on('woocommerce_api_settings')->onDelete('set null');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->index('platform_order_id');
            $table->index('order_number');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('woocommerce_orders');
    }
};

