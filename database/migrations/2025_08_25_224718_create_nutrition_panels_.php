<?php
// database/migrations/2025_08_25_000007_create_nutrition_panels_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('nutrition_panels', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->decimal('serving_size_fl_oz', 6, 2)->nullable(); // e.g., 12
            $t->decimal('percent_juice', 6, 2)->nullable();       // e.g., 3.00
            $t->string('allergen')->nullable();                   // "None"
            $t->longText('ingredients_statement')->nullable();    // “Carbonated Filtered Water, …”
            // Optional nutrition facts quick fields
            $t->integer('calories')->nullable();
            $t->integer('total_sugars_g')->nullable();
            $t->integer('added_sugars_g')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('nutrition_panels'); }
};
