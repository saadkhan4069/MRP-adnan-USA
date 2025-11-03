<?php
// database/migrations/2025_08_25_000003_create_product_raw_material_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('product_raw_material', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->foreignId('raw_material_id')->constrained()->cascadeOnDelete();
            $t->decimal('quantity', 18, 6)->default(0);
            $t->string('unit', 16)->default('kg');
            $t->decimal('unit_price', 12, 4)->default(0);
            $t->decimal('wastage_pct', 8, 2)->default(0);
            // PDF formula specific fields (optional, per product)
            $t->decimal('percent_w_w', 10, 5)->nullable();   // “Percent (w/w)”
            $t->decimal('lbs_per_1k_gal', 12, 3)->nullable();
            $t->decimal('gal_per_1k_gal', 12, 3)->nullable();
            $t->integer('sort_order')->default(0);
            $t->timestamps();
            $t->unique(['product_id','raw_material_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('product_raw_material'); }
};
