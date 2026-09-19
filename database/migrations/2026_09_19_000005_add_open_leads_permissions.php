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
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'assigned-carriers-list',
            'open-leads-list',
            'open-leads-notes',
            'open-leads-status',
        ];

        foreach ($permissions as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
        }

        // Assign to Admin role
        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }

        // Assign to Dispatcher role (can view assigned carriers, open leads, post notes, and change status)
        $dispatcherRole = Role::where('name', 'Dispatcher')->first();
        if ($dispatcherRole) {
            $dispatcherRole->givePermissionTo([
                'assigned-carriers-list',
                'open-leads-list',
                'open-leads-notes',
                'open-leads-status',
            ]);
        }

        // Assign to Sales Agent role (can view open leads and post notes/documents, but CANNOT change status)
        $agentRole = Role::where('name', 'Sales Agent')->first();
        if ($agentRole) {
            $agentRole->givePermissionTo([
                'open-leads-list',
                'open-leads-notes',
            ]);
        }

        // Assign to Dispatch Supervisor role
        $supervisorRole = Role::where('name', 'Dispatch Supervisor')->first();
        if ($supervisorRole) {
            $supervisorRole->givePermissionTo([
                'assigned-carriers-list',
                'open-leads-list',
                'open-leads-notes',
                'open-leads-status',
            ]);
        }

        // Assign to Manager role
        $managerRole = Role::where('name', 'Manager')->first();
        if ($managerRole) {
            $managerRole->givePermissionTo([
                'assigned-carriers-list',
                'open-leads-list',
                'open-leads-notes',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissions = [
            'assigned-carriers-list',
            'open-leads-list',
            'open-leads-notes',
            'open-leads-status',
        ];

        foreach ($permissions as $permName) {
            $perm = Permission::where('name', $permName)->first();
            if ($perm) {
                $perm->delete();
            }
        }
    }
};
