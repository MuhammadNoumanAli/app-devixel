<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Forget cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // HR Module Permissions (module-wise grouping in RolePermissionController)
        $hrPermissions = [
            // HR Employees
            'hr-employees-list',
            'hr-employees-edit',

            // Attendance & Punches
            'hr-attendance-list',
            'hr-attendance-punch',
            'hr-attendance-regularize',

            // Leaves & Holidays
            'hr-leaves-list',
            'hr-leaves-apply',
            'hr-leaves-approve',
            'hr-holidays-manage',

            // Payroll & Payslips
            'hr-payroll-list',
            'hr-payroll-generate',
            'hr-payroll-lock',
            'hr-payslip-normal',
            'hr-payslip-detailed',
            'hr-payroll-export',

            // Loans & Advances
            'hr-loans-list',
            'hr-loans-manage',

            // HR Settings & Shifts
            'hr-settings-manage',
            'hr-shifts-manage',
        ];

        foreach ($hrPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Grant all permissions (including new HR) to Admin role
        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $adminRole->syncPermissions(Permission::all());
        }

        // Give ESS (self-service) permissions to all other roles
        $essPermissions = ['hr-attendance-punch', 'hr-leaves-apply', 'hr-leaves-list', 'hr-payslip-normal', 'hr-attendance-list'];

        $otherRoles = Role::whereNotIn('name', ['Admin'])->get();
        foreach ($otherRoles as $role) {
            $role->givePermissionTo($essPermissions);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $hrPermissions = [
            'hr-employees-list', 'hr-employees-edit',
            'hr-attendance-list', 'hr-attendance-punch', 'hr-attendance-regularize',
            'hr-leaves-list', 'hr-leaves-apply', 'hr-leaves-approve', 'hr-holidays-manage',
            'hr-payroll-list', 'hr-payroll-generate', 'hr-payroll-lock',
            'hr-payslip-normal', 'hr-payslip-detailed', 'hr-payroll-export',
            'hr-loans-list', 'hr-loans-manage',
            'hr-settings-manage', 'hr-shifts-manage',
        ];

        foreach ($hrPermissions as $perm) {
            $permission = Permission::where('name', $perm)->first();
            if ($permission) {
                $permission->delete();
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
