<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);

        $permissions = Permission::pluck('name')->toArray();
        $superAdminRole->syncPermissions($permissions);

        $superAdmin = User::firstOrCreate([
            'email' => 'superadmin@example.com'
        ], [
            'name' => 'Super Admin',
            'password' => bcrypt('superadmin')
        ]);

        $superAdmin->assignRole($superAdminRole);
    }
}
