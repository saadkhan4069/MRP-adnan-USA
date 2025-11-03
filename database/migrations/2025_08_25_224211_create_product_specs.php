<?php
// database/migrations/2025_08_25_000006_create_product_specs_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('product_specs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            // LABORATORY SCALE SPECIFICATIONS
            $t->decimal('density_lbs_per_gal', 10, 3)->nullable(); // e.g., 8.570
            $t->decimal('ph', 6, 2)->nullable();                    // 2.95
            $t->decimal('brix', 6, 2)->nullable();                  // 7.60
            $t->string('taste')->nullable();                        // "Blue raspberry"
            $t->string('appearance')->nullable();                   // "Dark purple"
            // Batching Instructions
            $t->longText('batching_instructions')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('product_specs'); }
};
