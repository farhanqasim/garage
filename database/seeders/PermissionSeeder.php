<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissionGroups = User::getpermissionGroups();

        // Create all permissions
        foreach ($permissionGroups as $group => $permissions) {
            foreach ($permissions as $permission) {
                Permission::firstOrCreate(
                    ['name' => $permission, 'guard_name' => 'web'],
                    ['name' => $permission, 'guard_name' => 'web']
                );
            }
        }

        // Create Super Admin role with all permissions
        $superAdmin = Role::firstOrCreate(
            ['name' => 'Super Admin', 'guard_name' => 'web'],
            ['name' => 'Super Admin', 'guard_name' => 'web']
        );
        $superAdmin->syncPermissions(Permission::all());

        // Create Admin role with most permissions (except role management)
        $admin = Role::firstOrCreate(
            ['name' => 'Admin', 'guard_name' => 'web'],
            ['name' => 'Admin', 'guard_name' => 'web']
        );
        $adminPermissions = Permission::whereNotIn('name', [
            'view-roles',
            'create-roles',
            'edit-roles',
            'delete-roles',
            'assign-permissions'
        ])->get();
        $admin->syncPermissions($adminPermissions);

        // Create User role with limited permissions
        $user = Role::firstOrCreate(
            ['name' => 'User', 'guard_name' => 'web'],
            ['name' => 'User', 'guard_name' => 'web']
        );
        $userPermissions = Permission::whereIn('name', [
            'view-dashboard',
            'view-items',
            'view-item-details',
            'view-sales',
            'create-sales',
            'view-customers',
            'access-pos',
            'process-pos-sales',
        ])->get();
        $user->syncPermissions($userPermissions);

        // Create Branch Manager role
        $branchManager = Role::firstOrCreate(
            ['name' => 'Branch Manager', 'guard_name' => 'web'],
            ['name' => 'Branch Manager', 'guard_name' => 'web']
        );
        $branchManagerPermissions = Permission::whereIn('name', [
            'view-dashboard',
            'view-branches',
            'edit-branches',
            'view-items',
            'create-items',
            'edit-items',
            'view-sales',
            'create-sales',
            'edit-sales',
            'view-purchases',
            'create-purchases',
            'view-customers',
            'create-customers',
            'edit-customers',
            'access-pos',
            'process-pos-sales',
        ])->get();
        $branchManager->syncPermissions($branchManagerPermissions);

        $this->command->info('Permissions and roles created successfully!');
    }
}

