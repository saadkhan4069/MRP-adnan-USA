<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('woocommerce_orders', function (Blueprint $table) {
            $table->json('product_images')->nullable()->after('line_items'); // Store array of image URLs/paths
        });
    }

    public function down(): void
    {
        Schema::table('woocommerce_orders', function (Blueprint $table) {
            $table->dropColumn('product_images');
        });
    }
};

