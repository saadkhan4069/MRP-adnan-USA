<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->datetime('pickup_date_time')->nullable()->after('label_format');
            $table->datetime('dropoff_date_time')->nullable()->after('pickup_date_time');
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn(['pickup_date_time', 'dropoff_date_time']);
        });
    }
};
