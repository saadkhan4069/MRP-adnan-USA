<?php

// Run this file directly: php create_shipment_permissions.php
// Or run via artisan: php artisan tinker < create_shipment_permissions.php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

$permissions = [
    'shipments-index',
    'shipments-add',
    'shipments-edit',
    'shipments-delete',
    'shipments-view',
];

foreach ($permissions as $permName) {
    $permission = Permission::firstOrCreate([
        'name' => $permName,
        'guard_name' => 'web'
    ]);
    echo "Created/Found permission: {$permName}\n";
}

// Assign to Admin (role_id = 1) and Owner (role_id = 2)
$adminRole = Role::find(1);
$ownerRole = Role::find(2);

if ($adminRole) {
    foreach ($permissions as $permName) {
        $perm = Permission::where('name', $permName)->first();
        if ($perm && !$adminRole->hasPermissionTo($perm)) {
            $adminRole->givePermissionTo($perm);
            echo "Assigned {$permName} to Admin\n";
        }
    }
}

if ($ownerRole) {
    foreach ($permissions as $permName) {
        $perm = Permission::where('name', $permName)->first();
        if ($perm && !$ownerRole->hasPermissionTo($perm)) {
            $ownerRole->givePermissionTo($perm);
            echo "Assigned {$permName} to Owner\n";
        }
    }
}

echo "\nDone! Permissions created and assigned.\n";

