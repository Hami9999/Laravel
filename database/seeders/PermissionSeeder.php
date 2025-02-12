<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Permissions
        $permissions = [
            'create products',
            'edit products',
            'delete products',
            'view products'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Roles
        $adminRole = Role::firstOrCreate(['name' => 'super-admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Assign Permissions to Roles
        $adminRole->syncPermissions($permissions);
        $userRole->syncPermissions(['view products']);
    }
}
