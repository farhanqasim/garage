<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Roles & Permissions project ke actual features ke mutabiq
     */
    public function run(): void
    {
        // Super Admin user
        $admin = User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('12345678'),
            ]
        );

        // Super Admin role
        $superAdminRole = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web',
        ]);

        // Project ke actual modules ke mutabiq permissions
        $permissions = [
            // Customer
            ['name' => 'view_customer', 'group_name' => 'customer', 'guard_name' => 'web'],
            ['name' => 'add_customer', 'group_name' => 'customer', 'guard_name' => 'web'],
            ['name' => 'update_customer', 'group_name' => 'customer', 'guard_name' => 'web'],
            ['name' => 'delete_customer', 'group_name' => 'customer', 'guard_name' => 'web'],

            // Supplier
            ['name' => 'view_supplier', 'group_name' => 'supplier', 'guard_name' => 'web'],
            ['name' => 'add_supplier', 'group_name' => 'supplier', 'guard_name' => 'web'],
            ['name' => 'update_supplier', 'group_name' => 'supplier', 'guard_name' => 'web'],
            ['name' => 'delete_supplier', 'group_name' => 'supplier', 'guard_name' => 'web'],

            // Branch
            ['name' => 'view_branch', 'group_name' => 'branch', 'guard_name' => 'web'],
            ['name' => 'add_branch', 'group_name' => 'branch', 'guard_name' => 'web'],
            ['name' => 'update_branch', 'group_name' => 'branch', 'guard_name' => 'web'],
            ['name' => 'delete_branch', 'group_name' => 'branch', 'guard_name' => 'web'],

            // User
            ['name' => 'view_user', 'group_name' => 'user', 'guard_name' => 'web'],
            ['name' => 'add_user', 'group_name' => 'user', 'guard_name' => 'web'],
            ['name' => 'update_user', 'group_name' => 'user', 'guard_name' => 'web'],
            ['name' => 'delete_user', 'group_name' => 'user', 'guard_name' => 'web'],

            // Employee
            ['name' => 'view_employee', 'group_name' => 'employee', 'guard_name' => 'web'],
            ['name' => 'add_employee', 'group_name' => 'employee', 'guard_name' => 'web'],
            ['name' => 'update_employee', 'group_name' => 'employee', 'guard_name' => 'web'],
            ['name' => 'delete_employee', 'group_name' => 'employee', 'guard_name' => 'web'],

            // Category
            ['name' => 'view_category', 'group_name' => 'category', 'guard_name' => 'web'],
            ['name' => 'add_category', 'group_name' => 'category', 'guard_name' => 'web'],
            ['name' => 'update_category', 'group_name' => 'category', 'guard_name' => 'web'],
            ['name' => 'delete_category', 'group_name' => 'category', 'guard_name' => 'web'],

            // Brand
            ['name' => 'view_brand', 'group_name' => 'brand', 'guard_name' => 'web'],
            ['name' => 'add_brand', 'group_name' => 'brand', 'guard_name' => 'web'],
            ['name' => 'update_brand', 'group_name' => 'brand', 'guard_name' => 'web'],
            ['name' => 'delete_brand', 'group_name' => 'brand', 'guard_name' => 'web'],

            // Items
            ['name' => 'view_items', 'group_name' => 'items', 'guard_name' => 'web'],
            ['name' => 'add_items', 'group_name' => 'items', 'guard_name' => 'web'],
            ['name' => 'update_items', 'group_name' => 'items', 'guard_name' => 'web'],
            ['name' => 'delete_items', 'group_name' => 'items', 'guard_name' => 'web'],

            // Sales
            ['name' => 'view_sales', 'group_name' => 'sales', 'guard_name' => 'web'],
            ['name' => 'add_sales', 'group_name' => 'sales', 'guard_name' => 'web'],
            ['name' => 'update_sales', 'group_name' => 'sales', 'guard_name' => 'web'],
            ['name' => 'delete_sales', 'group_name' => 'sales', 'guard_name' => 'web'],

            // Purchases
            ['name' => 'view_purchases', 'group_name' => 'purchases', 'guard_name' => 'web'],
            ['name' => 'add_purchases', 'group_name' => 'purchases', 'guard_name' => 'web'],
            ['name' => 'update_purchases', 'group_name' => 'purchases', 'guard_name' => 'web'],
            ['name' => 'delete_purchases', 'group_name' => 'purchases', 'guard_name' => 'web'],

            // Warehouse
            ['name' => 'view_warehouse', 'group_name' => 'warehouse', 'guard_name' => 'web'],
            ['name' => 'add_warehouse', 'group_name' => 'warehouse', 'guard_name' => 'web'],
            ['name' => 'update_warehouse', 'group_name' => 'warehouse', 'guard_name' => 'web'],
            ['name' => 'delete_warehouse', 'group_name' => 'warehouse', 'guard_name' => 'web'],

            // Banks
            ['name' => 'view_banks', 'group_name' => 'banks', 'guard_name' => 'web'],
            ['name' => 'add_banks', 'group_name' => 'banks', 'guard_name' => 'web'],
            ['name' => 'update_banks', 'group_name' => 'banks', 'guard_name' => 'web'],
            ['name' => 'delete_banks', 'group_name' => 'banks', 'guard_name' => 'web'],

            // Bank Accounts
            ['name' => 'view_bank_accounts', 'group_name' => 'bank_accounts', 'guard_name' => 'web'],
            ['name' => 'add_bank_accounts', 'group_name' => 'bank_accounts', 'guard_name' => 'web'],
            ['name' => 'update_bank_accounts', 'group_name' => 'bank_accounts', 'guard_name' => 'web'],
            ['name' => 'delete_bank_accounts', 'group_name' => 'bank_accounts', 'guard_name' => 'web'],

            // Cash Accounts
            ['name' => 'view_cash_accounts', 'group_name' => 'cash_accounts', 'guard_name' => 'web'],
            ['name' => 'add_cash_accounts', 'group_name' => 'cash_accounts', 'guard_name' => 'web'],
            ['name' => 'update_cash_accounts', 'group_name' => 'cash_accounts', 'guard_name' => 'web'],
            ['name' => 'delete_cash_accounts', 'group_name' => 'cash_accounts', 'guard_name' => 'web'],

            // Bank Transactions
            ['name' => 'view_bank_transactions', 'group_name' => 'bank_transactions', 'guard_name' => 'web'],
            ['name' => 'add_bank_transactions', 'group_name' => 'bank_transactions', 'guard_name' => 'web'],
            ['name' => 'update_bank_transactions', 'group_name' => 'bank_transactions', 'guard_name' => 'web'],
            ['name' => 'delete_bank_transactions', 'group_name' => 'bank_transactions', 'guard_name' => 'web'],

            // Setting
            ['name' => 'view_setting', 'group_name' => 'setting', 'guard_name' => 'web'],
            ['name' => 'update_setting', 'group_name' => 'setting', 'guard_name' => 'web'],

            // Role
            ['name' => 'view_role', 'group_name' => 'role', 'guard_name' => 'web'],
            ['name' => 'add_role', 'group_name' => 'role', 'guard_name' => 'web'],
            ['name' => 'update_role', 'group_name' => 'role', 'guard_name' => 'web'],
            ['name' => 'delete_role', 'group_name' => 'role', 'guard_name' => 'web'],

            // Car Wash Services
            ['name' => 'view_car_wash_services', 'group_name' => 'car_wash_services', 'guard_name' => 'web'],
            ['name' => 'add_car_wash_services', 'group_name' => 'car_wash_services', 'guard_name' => 'web'],
            ['name' => 'update_car_wash_services', 'group_name' => 'car_wash_services', 'guard_name' => 'web'],
            ['name' => 'delete_car_wash_services', 'group_name' => 'car_wash_services', 'guard_name' => 'web'],

            // Car Wash Workers
            ['name' => 'view_car_wash_workers', 'group_name' => 'car_wash_workers', 'guard_name' => 'web'],
            ['name' => 'add_car_wash_workers', 'group_name' => 'car_wash_workers', 'guard_name' => 'web'],
            ['name' => 'update_car_wash_workers', 'group_name' => 'car_wash_workers', 'guard_name' => 'web'],
            ['name' => 'delete_car_wash_workers', 'group_name' => 'car_wash_workers', 'guard_name' => 'web'],

            // Car Wash Jobs
            ['name' => 'view_car_wash_jobs', 'group_name' => 'car_wash_jobs', 'guard_name' => 'web'],
            ['name' => 'add_car_wash_jobs', 'group_name' => 'car_wash_jobs', 'guard_name' => 'web'],
            ['name' => 'update_car_wash_jobs', 'group_name' => 'car_wash_jobs', 'guard_name' => 'web'],
            ['name' => 'delete_car_wash_jobs', 'group_name' => 'car_wash_jobs', 'guard_name' => 'web'],

            // Car Wash Attendance
            ['name' => 'view_car_wash_attendance', 'group_name' => 'car_wash_attendance', 'guard_name' => 'web'],
            ['name' => 'add_car_wash_attendance', 'group_name' => 'car_wash_attendance', 'guard_name' => 'web'],
            ['name' => 'update_car_wash_attendance', 'group_name' => 'car_wash_attendance', 'guard_name' => 'web'],
            ['name' => 'delete_car_wash_attendance', 'group_name' => 'car_wash_attendance', 'guard_name' => 'web'],

            // Car Wash Expenses
            ['name' => 'view_car_wash_expenses', 'group_name' => 'car_wash_expenses', 'guard_name' => 'web'],
            ['name' => 'add_car_wash_expenses', 'group_name' => 'car_wash_expenses', 'guard_name' => 'web'],
            ['name' => 'update_car_wash_expenses', 'group_name' => 'car_wash_expenses', 'guard_name' => 'web'],
            ['name' => 'delete_car_wash_expenses', 'group_name' => 'car_wash_expenses', 'guard_name' => 'web'],

            // Car Wash Payments
            ['name' => 'view_car_wash_payments', 'group_name' => 'car_wash_payments', 'guard_name' => 'web'],
            ['name' => 'add_car_wash_payments', 'group_name' => 'car_wash_payments', 'guard_name' => 'web'],
            ['name' => 'update_car_wash_payments', 'group_name' => 'car_wash_payments', 'guard_name' => 'web'],
            ['name' => 'delete_car_wash_payments', 'group_name' => 'car_wash_payments', 'guard_name' => 'web'],

            // POS
            ['name' => 'view_pos', 'group_name' => 'pos', 'guard_name' => 'web'],
            ['name' => 'add_pos', 'group_name' => 'pos', 'guard_name' => 'web'],

            // Item Types
            ['name' => 'view_parts', 'group_name' => 'parts', 'guard_name' => 'web'],
            ['name' => 'view_filters', 'group_name' => 'filters', 'guard_name' => 'web'],
            ['name' => 'view_break_pad', 'group_name' => 'break_pad', 'guard_name' => 'web'],
            ['name' => 'view_oil', 'group_name' => 'oil', 'guard_name' => 'web'],
            ['name' => 'view_battery', 'group_name' => 'battery', 'guard_name' => 'web'],
            ['name' => 'view_scrap', 'group_name' => 'scrap', 'guard_name' => 'web'],
            ['name' => 'view_services', 'group_name' => 'services', 'guard_name' => 'web'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm['name'], 'guard_name' => $perm['guard_name']],
                $perm
            );
        }

        $superAdminRole->syncPermissions(array_column($permissions, 'name'));
        $admin->assignRole('Super Admin');
    }
}
