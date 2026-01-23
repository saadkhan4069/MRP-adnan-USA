<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WooCommerceApiSetting;

class AddWooCommerceApiCredentials extends Command
{
    protected $signature = 'woocommerce:add-api-credentials 
                            {--url= : Website URL}
                            {--key= : Consumer Key}
                            {--secret= : Consumer Secret}
                            {--name=WooCommerce : Platform Name}';
    
    protected $description = 'Add WooCommerce API credentials to the database';

    public function handle()
    {
        $url = $this->option('url') ?: 'https://diagnouza-hardigitalsolutions-com.stackstaging.com';
        $key = $this->option('key') ?: 'ck_16dff87c9fdf2d91504c042c29780d703bb72036';
        $secret = $this->option('secret') ?: 'cs_24912629850a8c2d33d836da4d370def0ca45bd9';
        $name = $this->option('name');

        // Check if already exists
        $existing = WooCommerceApiSetting::where('website_url', $url)
            ->where('consumer_key', $key)
            ->first();

        if ($existing) {
            $this->warn('API credentials already exist for this URL and key.');
            if ($this->confirm('Do you want to update it?')) {
                $existing->update([
                    'consumer_secret' => $secret,
                    'platform_name' => $name,
                    'is_active' => true,
                ]);
                $this->info('API credentials updated successfully!');
            }
            return 0;
        }

        $setting = WooCommerceApiSetting::create([
            'platform_name' => $name,
            'website_url' => $url,
            'consumer_key' => $key,
            'consumer_secret' => $secret,
            'is_active' => true,
            'sync_interval' => 60,
            'notes' => 'Added via command line',
        ]);

        $this->info('API credentials added successfully!');
        $this->line("ID: {$setting->id}");
        $this->line("Platform: {$setting->platform_name}");
        $this->line("URL: {$setting->website_url}");

        return 0;
    }
}

