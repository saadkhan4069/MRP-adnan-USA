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
        Schema::table('shipment_packages', function (Blueprint $table) {
            if (!Schema::hasColumn('shipment_packages', 'package_class')) {
                $table->string('package_class')->nullable()->after('qty');
            }
            if (!Schema::hasColumn('shipment_packages', 'package_nmfc')) {
                $table->string('package_nmfc')->nullable()->after('package_class');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipment_packages', function (Blueprint $table) {
            if (Schema::hasColumn('shipment_packages', 'package_class')) {
                $table->dropColumn('package_class');
            }
            if (Schema::hasColumn('shipment_packages', 'package_nmfc')) {
                $table->dropColumn('package_nmfc');
            }
        });
    }
};
