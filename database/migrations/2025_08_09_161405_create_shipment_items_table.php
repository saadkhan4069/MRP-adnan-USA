<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shipment_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('shipment_id')->constrained()->cascadeOnDelete();

            $t->unsignedBigInteger('product_id')->nullable();
            $t->string('product_code')->nullable();
            $t->string('description')->nullable();       // for customs line description
            $t->string('purchase_unit')->nullable();

            $t->decimal('qty', 18, 3)->default(1);
            $t->decimal('net_unit_cost', 18, 4)->default(0); // in order currency
            $t->decimal('discount', 18, 4)->default(0);
            $t->decimal('subtotal', 18, 2)->default(0);

            // customs-friendly fields (optional)
            $t->string('hs_code')->nullable();
            $t->string('country_of_origin')->nullable();
            $t->decimal('item_weight', 12, 3)->nullable(); // kg
            $t->decimal('item_value', 18, 2)->nullable();  // declared per-line

            $t->json('meta')->nullable(); // any extra

            $t->timestamps();

            $t->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_items');
    }
};
