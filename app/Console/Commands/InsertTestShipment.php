<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Shipment;
use App\Models\Customer;
use App\Models\Currency;

class InsertTestShipment extends Command
{
    protected $signature = 'shipment:insert-test';
    protected $description = 'Insert a test shipment with all new fields';

    public function handle()
    {
        $this->info('Creating test shipment...');

        // Get first customer and currency
        $customer = Customer::first();
        $currency = Currency::first();

        if (!$customer) {
            $this->error('No customer found. Please create a customer first.');
            return 1;
        }

        if (!$currency) {
            $this->error('No currency found. Please create a currency first.');
            return 1;
        }

        try {
            $shipment = Shipment::create([
                'po_no' => 'TEST-PO-' . date('YmdHis'),
                'reference_no' => 'TEST-REF-' . date('YmdHis'),
                'customer_id' => $customer->id,
                'status' => 1,

                // Shipper (From)
                'ship_from_company' => 'Test Shipper Company',
                'ship_from_first_name' => 'John Shipper',
                'ship_from_address_1' => '123 Test Street',
                'ship_from_country' => 'USA',
                'ship_from_state' => 'California',
                'ship_from_city' => 'Los Angeles',
                'ship_from_zipcode' => '90001',
                'ship_from_contact' => '+1234567890',
                'ship_from_email' => 'shipper@test.com',
                'ship_from_dock_hours' => '9:00 AM - 5:00 PM',
                'ship_from_lunch_hour' => '12:00 PM - 1:00 PM',
                'ship_from_pickup_delivery_instructions' => 'Call before pickup. Use side entrance.',
                'ship_from_appointment' => 'Appointment required',
                'ship_from_accessorial' => 'Liftgate service required',

                // Consignee (To)
                'ship_to_company' => 'Test Consignee Company',
                'ship_to_first_name' => 'Jane Consignee',
                'ship_to_address_1' => '456 Delivery Avenue',
                'ship_to_country' => 'USA',
                'ship_to_state' => 'New York',
                'ship_to_city' => 'New York',
                'ship_to_zipcode' => '10001',
                'ship_to_contact' => '+1987654321',
                'ship_to_email' => 'consignee@test.com',
                'ship_to_dock_hours' => '8:00 AM - 6:00 PM',
                'ship_to_lunch_hour' => '12:30 PM - 1:30 PM',
                'ship_to_pickup_delivery_instructions' => 'Deliver to loading dock. Contact warehouse manager.',
                'ship_to_appointment' => 'Appointment preferred',
                'ship_to_accessorial' => 'Inside delivery, Residential delivery',

                // Currency
                'currency_id' => $currency->id,
                'exchange_rate' => 1.0,

                // Financial
                'order_tax_rate' => 0,
                'order_discount' => 0,
                'shipping_cost' => 100.00,
                'total_qty' => 10,
                'total_discount' => 0,
                'total_tax' => 0,
                'total_cost' => 1000.00,
                'item' => 5,
                'order_tax' => 0,
                'grand_total' => 1100.00,
                'paid_amount' => 0,
                'payment_status' => 1,
            ]);

            $this->info('✓ Test shipment created successfully!');
            $this->info("Shipment ID: {$shipment->id}");
            $this->newLine();

            $this->info('Verifying new fields:');
            $this->line('-------------------');
            $this->line("Shipper Lunch Hour: " . ($shipment->ship_from_lunch_hour ?? 'NULL'));
            $this->line("Shipper Instructions: " . ($shipment->ship_from_pickup_delivery_instructions ?? 'NULL'));
            $this->line("Shipper Appointment: " . ($shipment->ship_from_appointment ?? 'NULL'));
            $this->line("Shipper Accessorial: " . ($shipment->ship_from_accessorial ?? 'NULL'));
            $this->newLine();
            $this->line("Consignee Lunch Hour: " . ($shipment->ship_to_lunch_hour ?? 'NULL'));
            $this->line("Consignee Instructions: " . ($shipment->ship_to_pickup_delivery_instructions ?? 'NULL'));
            $this->line("Consignee Appointment: " . ($shipment->ship_to_appointment ?? 'NULL'));
            $this->line("Consignee Accessorial: " . ($shipment->ship_to_accessorial ?? 'NULL'));
            $this->newLine();
            $this->info("View shipment at: /shipment/{$shipment->id}");

            return 0;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile() . ':' . $e->getLine());
            $this->newLine();
            $this->error('Stack trace:');
            $this->line($e->getTraceAsString());
            return 1;
        }
    }
}

