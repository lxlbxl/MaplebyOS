<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::firstOrCreate(['name' => 'view_admin']);
        Permission::firstOrCreate(['name' => 'manage_users']);
        Permission::firstOrCreate(['name' => 'manage_roles']);
        Permission::firstOrCreate(['name' => 'manage_permissions']);
        Permission::firstOrCreate(['name' => 'view_reports']);
        Permission::firstOrCreate(['name' => 'manage_settings']);

        // create roles and assign existing permissions
        $role1 = Role::firstOrCreate(['name' => 'User']);
        // User gets no admin permissions by default

        $role2 = Role::firstOrCreate(['name' => 'Staff']);
        $role2->givePermissionTo('view_admin');
        $role2->givePermissionTo('view_reports');

        $role3 = Role::firstOrCreate(['name' => 'Super Admin']);
        // Super Admin gets all permissions via Gate::before rule; see AuthServiceProvider (or we can assign all)
        // For simplicity in Spatie, we often just assign all or use a Gate intercept data
        // assigning specific permissions for clarity:
        $role3->givePermissionTo(Permission::all());

        // Create a Super Admin User
        $user = User::firstOrCreate([
            'email' => 'admin@adgod-os.com',
        ], [
            'name' => 'Super Admin',
            'password' => Hash::make('password'), // Change this in production!
        ]);

        $user->assignRole($role3);

        // Create a Staff User
        $staff = User::firstOrCreate([
            'email' => 'staff@adgod-os.com',
        ], [
            'name' => 'Staff Member',
            'password' => Hash::make('password'),
        ]);

        $staff->assignRole($role2);
    }
}
