<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExternalServicesSeeder extends Seeder
{
    public function run()
    {
        $newAddons = ['ecommerce']; // Future add-ons

        // Fetch all rows
        $gateways = DB::table('external_services')->get();

        foreach ($gateways as $gateway) {
            // Decode existing module_status, or initialize as empty
            $moduleStatus = json_decode($gateway->module_status, true) ?? [];

            // Ensure all add-ons are present in module_status with a default of false
            foreach ($newAddons as $addon) {
                if (!isset($moduleStatus[$addon])) {
                    $moduleStatus[$addon] = false;
                }
            }

            // Update the row
            DB::table('external_services')
                ->where('id', $gateway->id)
                ->update(['module_status' => json_encode($moduleStatus)]);
        }

        // Add a new gateway if needed
        $newGateway = DB::table('external_services')
            ->where('name', 'PayPal') // Replace with the actual name
            ->first();

        if (!$newGateway) {
            DB::table('external_services')->insert([
                'name' => 'PayPal',
                'type' => 'payment',
                'details' => 'Client ID,Client Secret;YOUR_CLIENT_ID,YOUR_CLIENT_SECRET',
                'module_status' => json_encode(['ecommerce' => true, 'pos' => true]),
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Add a new gateway if needed
        $newGateway = DB::table('external_services')
            ->where('name', 'Stripe') // Replace with the actual name
            ->first();

        if (!$newGateway) {
            DB::table('external_services')->insert([
                'name' => 'Stripe',
                'type' => 'payment',
                'details' => 'Public Key,Private Key;YOUR_PUBLIC_KEY,YOUR_PRIVATE_KEY',
                'module_status' => json_encode(['ecommerce' => true, 'pos' => true]),
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Add a new gateway if needed
        $newGateway = DB::table('external_services')
            ->where('name', 'Razorpay') // Replace with the actual name
            ->first();

        if (!$newGateway) {
            DB::table('external_services')->insert([
                'name' => 'Razorpay',
                'type' => 'payment',
                'details' => 'Key,Secret;YOUR_RAZORPAY_KEY,YOUR_RAZORPAY_SECRET',
                'module_status' => json_encode(['ecommerce' => true, 'pos' => true]),
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Add a new gateway if needed
        $newGateway = DB::table('external_services')
            ->where('name', 'Paystack') // Replace with the actual name
            ->first();

        if (!$newGateway) {
            DB::table('external_services')->insert([
                'name' => 'Paystack',
                'type' => 'payment',
                'details' => 'public_Key,Secret_Key;YOUR_PAYSTACK_PUBLIC_KEY,YOUR_PAYSTACK_SECRET_KEY',
                'module_status' => json_encode(['ecommerce' => true, 'pos' => true]),
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Add a new gateway if needed
        $newGateway = DB::table('external_services')
            ->where('name', 'Mollie') // Replace with the actual name
            ->first();

        if (!$newGateway) {
            DB::table('external_services')->insert([
                'name' => 'Mollie',
                'type' => 'payment',
                'details' => 'api_key;YOUR_MOLLIE_API_KEY',
                'module_status' => json_encode(['ecommerce' => true, 'pos' => true]),
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Add a new gateway if needed
        $newGateway = DB::table('external_services')
            ->where('name', 'Xendit') // Replace with the actual name
            ->first();

        if (!$newGateway) {
            DB::table('external_services')->insert([
                'name' => 'Xendit',
                'type' => 'payment',
                'details' => 'secret_key,callback_token;YOUR_XENDIT_SECRET_KEY,YOUR_XENDIT_CALLBACK_TOKEN',
                'module_status' => json_encode(['ecommerce' => true, 'pos' => true]),
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Add a new gateway if needed
        $newGateway = DB::table('external_services')
            ->where('name', 'bkash') // Replace with the actual name
            ->first();

        if (!$newGateway) {
            DB::table('external_services')->insert([
                'name' => 'bkash',
                'type' => 'payment',
                'details' => 'Mode,app_key,app_secret,username,password;sandbox,YOUR_BKASH_APP_KEY,YOUR_BKASH_APP_SECRET,YOUR_BKASH_USERNAME,YOUR_BKASH_PASSWORD',
                'module_status' => json_encode(['ecommerce' => true, 'pos' => true]),
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Add a new gateway if needed
        $newGateway = DB::table('external_services')
            ->where('name', 'sslcommerz') // Replace with the actual name
            ->first();

        if (!$newGateway) {
            DB::table('external_services')->insert([
                'name' => 'sslcommerz',
                'type' => 'payment',
                'details' => 'appkey,appsecret;YOUR_SSLCOMMERZ_APP_KEY,YOUR_SSLCOMMERZ_APP_SECRET',
                'module_status' => json_encode(['ecommerce' => true, 'pos' => true]),
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Add a new gateway if needed
        $newGateway = DB::table('external_services')
            ->where('name', 'Mpesa') // Replace with the actual name
            ->first();

        if (!$newGateway) {
            DB::table('external_services')->insert([
                'name' => 'Mpesa',
                'type' => 'payment',
                'details' => 'consumer_Key,consumer_Secret;YOUR_MPESA_CONSUMER_KEY,YOUR_MPESA_CONSUMER_SECRET',
                'module_status' => json_encode(['ecommerce' => true, 'pos' => true]),
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Add a new gateway if needed
        $newGateway = DB::table('external_services')
            ->where('name', 'Pesapal') // Replace with the actual name
            ->first();

        if (!$newGateway) {
            DB::table('external_services')->insert([
                'name' => 'Pesapal',
                'type' => 'payment',
                'details' => 'Mode,Consumer Key,Consumer Secret;sandbox,YOUR_PESAPAL_CONSUMER_KEY,YOUR_PESAPAL_CONSUMER_SECRET',
                'module_status' => json_encode(['ecommerce' => true, 'pos' => true]),
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Add a new gateway if needed
        $newGateway = DB::table('external_services')
            ->where('name', 'Moneipoint') // Replace with the actual name
            ->first();

        if (!$newGateway) {
            DB::table('external_services')->insert([
                'name' => 'Moneipoint',
                'type' => 'payment',
                'details' => 'Mode,client_id,client_secret,terminal_serial;sandbox,YOUR_MONEIPOINT_CLIENT_ID,YOUR_MONEIPOINT_CLIENT_SECRET,YOUR_MONEIPOINT_TERMINAL_SERIAL',
                'module_status' => json_encode(['ecommerce' => true, 'pos' => true]),
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
