<?php

namespace Database\Seeders;

use App\Models\PaymentType;
use App\Models\State;
use App\Models\TruckSize;
use App\Models\TruckType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'users',
            'create-users',
            'store-users',
            'view-users',
            'edit-users',
            'update-users',
            'delete-users',
            'edit-profile',
            'change-password',
            'forgot-password',
            'carriers-list',
            'carriers-create',
            'carriers-store',
            'carriers-view',
            'carriers-edit',
            'carriers-update',
            'carriers-delete',
            'dispatchers-list',
            'dispatchers-create',
            'dispatchers-view',
            'dispatchers-edit',
            'dispatchers-delete',
            'carriers-report',
            'dispatchers-report',
            'truck-types-report',
            'send-email',
            'assign-carrier',
            'documents-received',
            'documents-received-30-days',
            'add-role',
            'edit-role',
            'delete-role',
            'edit-permission',
            'list-permission',
            'chat',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $dispatcherRole = Role::firstOrCreate(['name' => 'Dispatcher', 'guard_name' => 'web']);
        $agentRole = Role::firstOrCreate(['name' => 'Sales Agent', 'guard_name' => 'web']);
        $managerRole = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $supervisorRole = Role::firstOrCreate(['name' => 'Dispatch Supervisor', 'guard_name' => 'web']);

        $dispatcherRole->syncPermissions([
            'dispatchers-list',
            'dispatchers-create',
            'dispatchers-view',
            'dispatchers-edit',
            'dispatchers-report',
            'chat',
        ]);

        $agentRole->syncPermissions([
            'carriers-list',
            'carriers-create',
            'carriers-store',
            'carriers-view',
            'carriers-report',
            'send-email',
            'chat',
        ]);

        $managerRole->syncPermissions([
            'carriers-list',
            'dispatchers-list',
            'carriers-report',
            'dispatchers-report',
            'chat',
        ]);

        $supervisorRole->syncPermissions([
            'dispatchers-list',
            'dispatchers-view',
            'dispatchers-edit',
            'dispatchers-report',
            'chat',
        ]);

        // Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'status' => 'active',
                'load_commission' => 0,
            ]
        );
        $admin->assignRole($adminRole);

        // Create Dispatcher User
        $dispatcher = User::firstOrCreate(
            ['email' => 'dispatcher@gmail.com'],
            [
                'first_name' => 'John',
                'last_name' => 'Dispatcher',
                'name' => 'John Dispatcher',
                'password' => Hash::make('password'),
                'status' => 'active',
                'load_commission' => 10,
            ]
        );
        $dispatcher->assignRole($dispatcherRole);

        // Create Sales Agent User
        $agent = User::firstOrCreate(
            ['email' => 'agent@gmail.com'],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Agent',
                'name' => 'Sarah Agent',
                'password' => Hash::make('password'),
                'status' => 'active',
                'load_commission' => 0,
            ]
        );
        $agent->assignRole($agentRole);

        // Seed States
        $states = [
            "AL" => 'Alabama', "AK" => 'Alaska', "AZ" => 'Arizona', "AR" => 'Arkansas', "CA" => 'California',
            "CO" => 'Colorado', "CT" => 'Connecticut', "DE" => 'Delaware', "DC" => 'District Of Columbia', "FL" => 'Florida',
            "GA" => 'Georgia', "HI" => 'Hawaii', "ID" => 'Idaho', "IL" => 'Illinois', "IN" => 'Indiana',
            "IA" => 'Iowa', "KS" => 'Kansas', "KY" => 'Kentucky', "LA" => 'Louisiana', "ME" => 'Maine',
            "MD" => 'Maryland', "MA" => 'Massachusetts', "MI" => 'Michigan', "MN" => 'Minnesota', "MS" => 'Mississippi',
            "MO" => 'Missouri', "MT" => 'Montana', "NE" => 'Nebraska', "NV" => 'Nevada', "NH" => 'New Hampshire',
            "NJ" => 'New Jersey', "NM" => 'New Mexico', "NY" => 'New York', "NC" => 'North Carolina', "ND" => 'North Dakota',
            "OH" => 'Ohio', "OK" => 'Oklahoma', "OR" => 'Oregon', "PA" => 'Pennsylvania', "RI" => 'Rhode Island',
            "SC" => 'South Carolina', "SD" => 'South Dakota', "TN" => 'Tennessee', "TX" => 'Texas', "UT" => 'Utah',
            "VT" => 'Vermont', "VA" => 'Virginia', "WA" => 'Washington', "WV" => 'West Virginia', "WI" => 'Wisconsin',
            "WY" => 'Wyoming'
        ];
        foreach ($states as $code => $name) {
            State::firstOrCreate(['code' => $code], ['name' => $name]);
        }

        // Seed Truck Types
        $truckTypes = ['Dry Van', 'Reefer', 'Flatbed', 'Power Only', 'Step Deck', 'Box Truck', 'Hotshot'];
        foreach ($truckTypes as $type) {
            TruckType::firstOrCreate(['name' => $type]);
        }

        // Seed Truck Sizes
        $truckSizes = ['48 ft', '53 ft', '26 ft', '24 ft', '40 ft'];
        foreach ($truckSizes as $size) {
            TruckSize::firstOrCreate(['name' => $size]);
        }

        // Seed Payment Types
        $paymentTypes = ['Factoring', 'QuickPay', 'Standard Net 30'];
        foreach ($paymentTypes as $pType) {
            PaymentType::firstOrCreate(['name' => $pType]);
        }
    }
}
