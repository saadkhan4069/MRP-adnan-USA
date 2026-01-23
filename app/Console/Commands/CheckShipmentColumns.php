<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CheckShipmentColumns extends Command
{
    protected $signature = 'shipment:check-columns';
    protected $description = 'Check if new shipment columns exist in database';

    public function handle()
    {
        $this->info('Checking shipment table columns...');
        $this->newLine();

        $requiredColumns = [
            'ship_from_lunch_hour',
            'ship_from_pickup_delivery_instructions',
            'ship_from_appointment',
            'ship_from_accessorial',
            'ship_to_lunch_hour',
            'ship_to_pickup_delivery_instructions',
            'ship_to_appointment',
            'ship_to_accessorial',
        ];

        $missingColumns = [];
        $existingColumns = [];

        foreach ($requiredColumns as $column) {
            if (Schema::hasColumn('shipments', $column)) {
                $this->info("✓ {$column} - EXISTS");
                $existingColumns[] = $column;
            } else {
                $this->error("✗ {$column} - MISSING");
                $missingColumns[] = $column;
            }
        }

        $this->newLine();

        if (empty($missingColumns)) {
            $this->info('All columns exist! Data should insert correctly.');
        } else {
            $this->error('Missing columns found. Run migration:');
            $this->line('php artisan migrate');
            $this->newLine();
            $this->warn('OR manually add columns with SQL:');
            $this->line('ALTER TABLE shipments');
            foreach ($missingColumns as $index => $column) {
                $type = strpos($column, 'instructions') !== false || strpos($column, 'accessorial') !== false ? 'TEXT' : 'VARCHAR(255)';
                $after = $index > 0 ? "AFTER '{$existingColumns[$index - 1]}'" : "AFTER 'ship_from_email'";
                $comma = $index < count($missingColumns) - 1 ? ',' : ';';
                $this->line("  ADD COLUMN {$column} {$type} NULL {$after}{$comma}");
            }
        }

        return empty($missingColumns) ? 0 : 1;
    }
}

