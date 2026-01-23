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
        Schema::table('shipments', function (Blueprint $table) {
            if (!Schema::hasColumn('shipments', 'ship_from_dock_hours')) {
                $table->string('ship_from_dock_hours')->nullable()->after('ship_from_email');
            }
            // Add ship_to_dock_hours if it doesn't exist
            if (!Schema::hasColumn('shipments', 'ship_to_dock_hours')) {
                $table->string('ship_to_dock_hours')->nullable()->after('ship_to_email');
            }
        });
        
        // Copy data from dock_hours to ship_to_dock_hours if dock_hours exists
        if (Schema::hasColumn('shipments', 'dock_hours')) {
            \DB::statement('UPDATE shipments SET ship_to_dock_hours = dock_hours WHERE dock_hours IS NOT NULL AND ship_to_dock_hours IS NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            if (Schema::hasColumn('shipments', 'ship_from_dock_hours')) {
                $table->dropColumn('ship_from_dock_hours');
            }
            if (Schema::hasColumn('shipments', 'ship_to_dock_hours')) {
                $table->dropColumn('ship_to_dock_hours');
            }
        });
    }
};
