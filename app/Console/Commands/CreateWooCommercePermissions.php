<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateWooCommercePermissions extends Command
{
    protected $signature = 'permissions:create-woocommerce';
    protected $description = 'Create WooCommerce permissions and assign to Admin and Owner roles';

    public function handle()
    {
        $permissions = [
            'woocommerce-index',
            'woocommerce-add',
            'woocommerce-edit',
            'woocommerce-delete',
            'woocommerce-api-settings',
        ];

        $this->info('Creating WooCommerce permissions...');

        foreach ($permissions as $permName) {
            $permission = Permission::firstOrCreate([
                'name' => $permName,
                'guard_name' => 'web'
            ]);
            $this->line("✓ Created/Found permission: {$permName}");
        }

        // Assign to Admin (role_id = 1) and Owner (role_id = 2)
        $adminRole = Role::find(1);
        $ownerRole = Role::find(2);

        if ($adminRole) {
            $this->info('Assigning permissions to Admin...');
            foreach ($permissions as $permName) {
                $perm = Permission::where('name', $permName)->first();
                if ($perm && !$adminRole->hasPermissionTo($perm)) {
                    $adminRole->givePermissionTo($perm);
                    $this->line("  ✓ Assigned {$permName} to Admin");
                }
            }
        }

        if ($ownerRole) {
            $this->info('Assigning permissions to Owner...');
            foreach ($permissions as $permName) {
                $perm = Permission::where('name', $permName)->first();
                if ($perm && !$ownerRole->hasPermissionTo($perm)) {
                    $ownerRole->givePermissionTo($perm);
                    $this->line("  ✓ Assigned {$permName} to Owner");
                }
            }
        }

        $this->info('Done! Permissions created and assigned.');
        return 0;
    }
}

