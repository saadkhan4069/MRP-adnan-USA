<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearRolePermissionsCache extends Command
{
    protected $signature = 'cache:clear-role-permissions';
    protected $description = 'Clear role permissions cache for all roles';

    public function handle()
    {
        // Clear cache for all roles (1-5)
        for($i = 1; $i <= 5; $i++) {
            Cache::forget('role_has_permissions_list' . $i);
            $this->line("Cleared cache for role_id: {$i}");
        }
        
        Cache::forget('permissions');
        Cache::forget('role_has_permissions');
        
        $this->info('All role permissions cache cleared!');
        return 0;
    }
}

