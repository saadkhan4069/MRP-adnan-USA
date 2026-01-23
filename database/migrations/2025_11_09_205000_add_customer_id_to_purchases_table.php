<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('purchases', 'customer_id')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->unsignedBigInteger('customer_id')->nullable()->after('user_id');
                $table->index('customer_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('purchases', 'customer_id')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->dropIndex(['customer_id']);
                $table->dropColumn('customer_id');
            });
        }
    }
};




