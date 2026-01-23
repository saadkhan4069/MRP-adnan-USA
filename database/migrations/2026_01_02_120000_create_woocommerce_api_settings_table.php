<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('woocommerce_api_settings', function (Blueprint $table) {
            $table->id();
            $table->string('platform_name')->default('WooCommerce'); // WooCommerce, Shopify, etc.
            $table->string('website_url');
            $table->string('consumer_key');
            $table->string('consumer_secret');
            $table->boolean('is_active')->default(true);
            $table->integer('sync_interval')->default(60); // minutes
            $table->timestamp('last_sync_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('woocommerce_api_settings');
    }
};

