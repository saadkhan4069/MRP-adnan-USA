<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add shipment permissions
        $permissions = [
            ['name' => 'shipments-index', 'guard_name' => 'web'],
            ['name' => 'shipments-add', 'guard_name' => 'web'],
            ['name' => 'shipments-edit', 'guard_name' => 'web'],
            ['name' => 'shipments-delete', 'guard_name' => 'web'],
            ['name' => 'shipments-view', 'guard_name' => 'web'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate($permission);
        }

        // Assign all permissions to Admin (role_id = 1) and Owner (role_id = 2)
        $adminRole = Role::find(1);
        $ownerRole = Role::find(2);

        if ($adminRole) {
            foreach ($permissions as $permission) {
                $perm = Permission::where('name', $permission['name'])->first();
                if ($perm && !$adminRole->hasPermissionTo($perm)) {
                    $adminRole->givePermissionTo($perm);
                }
            }
        }

        if ($ownerRole) {
            foreach ($permissions as $permission) {
                $perm = Permission::where('name', $permission['name'])->first();
                if ($perm && !$ownerRole->hasPermissionTo($perm)) {
                    $ownerRole->givePermissionTo($perm);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissions = [
            'shipments-index',
            'shipments-add',
            'shipments-edit',
            'shipments-delete',
            'shipments-view',
        ];

        foreach ($permissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            if ($permission) {
                $permission->delete();
            }
        }
    }
};

