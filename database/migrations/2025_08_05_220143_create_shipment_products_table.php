<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('shipment_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shipment_id');
            $table->unsignedBigInteger('product_id');
            $table->string('product_code');
            $table->string('product_name');
            $table->integer('qty');
            $table->integer('recieved')->nullable();
            $table->string('batch_no')->nullable();
            $table->string('lot_no')->nullable();
            $table->date('expired_date')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->decimal('net_unit_cost', 15, 2);
            $table->decimal('shipping', 15, 2)->nullable();
            $table->decimal('discount', 15, 2)->nullable();
            $table->decimal('tax', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->string('dimensions')->nullable(); // e.g. "30x20x10 cm / 2.5kg"

            $table->timestamps();

            // Foreign keys (optional but recommended)
            $table->foreign('shipment_id')->references('id')->on('shipments')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_products');
    }
};
