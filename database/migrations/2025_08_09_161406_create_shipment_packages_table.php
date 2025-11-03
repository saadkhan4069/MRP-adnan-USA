<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shipment_packages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('shipment_id')->constrained()->cascadeOnDelete();

            $t->string('packaging')->nullable();     // your_packaging / ups_letter / dhl_flyer / fedex_box
            $t->string('reference')->nullable();     // pkg reference for carrier
            $t->decimal('weight', 10, 3)->nullable(); // kg
            $t->decimal('length', 10, 2)->nullable(); // cm
            $t->decimal('width', 10, 2)->nullable();
            $t->decimal('height', 10, 2)->nullable();
            $t->string('weight_unit')->default('kg');
            $t->string('dim_unit')->default('cm');

            $t->decimal('declared_value', 18, 2)->nullable();
            $t->string('barcode')->nullable();       // pkg-level tracking if provided

            $t->string('label_url')->nullable();     // per-package label (if split labels)
            $t->json('meta')->nullable();            // any carrier-specific stuff

            $t->string('dimensions_note')->nullable();

            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_packages');
    }
};
