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
        Schema::table('shipment_attachments', function (Blueprint $table) {
            if (!Schema::hasColumn('shipment_attachments', 'title')) {
                $table->string('title')->nullable()->after('shipment_id');
            }
            if (!Schema::hasColumn('shipment_attachments', 'disk')) {
                $table->string('disk', 50)->default('public')->after('path');
            }
            if (!Schema::hasColumn('shipment_attachments', 'type')) {
                $table->string('type', 50)->nullable()->after('disk');
            }
            if (!Schema::hasColumn('shipment_attachments', 'uploaded_by')) {
                $table->unsignedBigInteger('uploaded_by')->nullable()->after('type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipment_attachments', function (Blueprint $table) {
            if (Schema::hasColumn('shipment_attachments', 'title')) {
                $table->dropColumn('title');
            }
            if (Schema::hasColumn('shipment_attachments', 'disk')) {
                $table->dropColumn('disk');
            }
            if (Schema::hasColumn('shipment_attachments', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('shipment_attachments', 'uploaded_by')) {
                $table->dropColumn('uploaded_by');
            }
        });
    }
};
