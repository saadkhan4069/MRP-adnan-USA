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
        Schema::create('shipment_attachments', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('shipment_id');
    $table->string('original_name')->nullable();
    $table->string('filename');
    $table->string('path'); // relative to public/
    $table->string('mime', 100)->nullable();
    $table->unsignedBigInteger('size')->nullable();
    $table->timestamps();

    $table->foreign('shipment_id')->references('id')->on('shipments')->onDelete('cascade');
     });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_attachment');
    }
};
