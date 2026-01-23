<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            // Shipper (From) additional fields
            if (!Schema::hasColumn('shipments', 'ship_from_lunch_hour')) {
                if (Schema::hasColumn('shipments', 'ship_from_dock_hours')) {
                    $table->string('ship_from_lunch_hour')->nullable()->after('ship_from_dock_hours');
                } else {
                    $table->string('ship_from_lunch_hour')->nullable()->after('ship_from_email');
                }
            }
            if (!Schema::hasColumn('shipments', 'ship_from_pickup_delivery_instructions')) {
                $table->text('ship_from_pickup_delivery_instructions')->nullable()->after('ship_from_lunch_hour');
            }
            if (!Schema::hasColumn('shipments', 'ship_from_appointment')) {
                $table->string('ship_from_appointment')->nullable()->after('ship_from_pickup_delivery_instructions');
            }
            if (!Schema::hasColumn('shipments', 'ship_from_accessorial')) {
                $table->text('ship_from_accessorial')->nullable()->after('ship_from_appointment');
            }

            // Consignee (To) additional fields
            if (!Schema::hasColumn('shipments', 'ship_to_lunch_hour')) {
                if (Schema::hasColumn('shipments', 'ship_to_dock_hours')) {
                    $table->string('ship_to_lunch_hour')->nullable()->after('ship_to_dock_hours');
                } else {
                    $table->string('ship_to_lunch_hour')->nullable()->after('ship_to_email');
                }
            }
            if (!Schema::hasColumn('shipments', 'ship_to_pickup_delivery_instructions')) {
                $table->text('ship_to_pickup_delivery_instructions')->nullable()->after('ship_to_lunch_hour');
            }
            if (!Schema::hasColumn('shipments', 'ship_to_appointment')) {
                $table->string('ship_to_appointment')->nullable()->after('ship_to_pickup_delivery_instructions');
            }
            if (!Schema::hasColumn('shipments', 'ship_to_accessorial')) {
                $table->text('ship_to_accessorial')->nullable()->after('ship_to_appointment');
            }
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn([
                'ship_from_lunch_hour',
                'ship_from_pickup_delivery_instructions',
                'ship_from_appointment',
                'ship_from_accessorial',
                'ship_to_lunch_hour',
                'ship_to_pickup_delivery_instructions',
                'ship_to_appointment',
                'ship_to_accessorial',
            ]);
        });
    }
};

