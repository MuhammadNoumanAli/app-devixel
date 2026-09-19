<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add avatar column to users table if not present
        if (!Schema::hasColumn('users', 'avatar')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('avatar')->nullable()->after('email');
            });
        }

        // 2. Forget cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 3. Define all permissions across all modules
        $allPermissions = [
            // Users Module
            'users',
            'create-users',
            'store-users',
            'view-users',
            'edit-users',
            'update-users',
            'delete-users',
            'change-users-status',
            'change-password',
            'edit-profile',

            // Carriers Module
            'carriers-list',
            'carriers-create',
            'carriers-store',
            'carriers-view',
            'carriers-edit',
            'carriers-update',
            'carriers-delete',
            'carriers-status',
            'send-email',
            'assign-carrier',
            'assigned-carriers-list',
            'open-leads-list',
            'open-leads-notes',
            'open-leads-status',
            'documents-received',
            'documents-received-30-days',

            // Dispatch / Loads Module
            'dispatchers-list',
            'dispatchers-create',
            'dispatchers-view',
            'dispatchers-edit',
            'dispatchers-delete',
            'can-load-cancel',
            'dispatcher-edit-attachment',

            // Invoices Module
            'invoices-list',
            'invoices-unpaid',
            'invoices-view',
            'invoices-status',
            'invoices-payments',
            'invoices-dispatcher-report',
            'invoices-download-excel',

            // Truck Types Module
            'truck-types-list',
            'truck-types-create',
            'truck-types-edit',
            'truck-types-delete',

            // Reports Module
            'carriers-report',
            'dispatchers-report',
            'truck-types-report',
            'reports-download',

            // Roles & Permissions Module
            'list-roles',
            'add-role',
            'edit-role',
            'delete-role',
            'list-permission',
            'edit-permission',

            // Chat Module
            'chat',
            'chat-clear-history',
            'chat-send-file',
            'chat-search',
            'chat-view-profile',

            // Settings Module
            'settings',
        ];

        foreach ($allPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Grant all permissions to Admin
        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $adminRole->syncPermissions(Permission::all());
        }

        // Assign additional relevant permissions to other roles
        $dispatcherRole = Role::where('name', 'Dispatcher')->first();
        if ($dispatcherRole) {
            $dispatcherRole->givePermissionTo([
                'invoices-list',
                'invoices-unpaid',
                'invoices-view',
                'invoices-dispatcher-report',
                'truck-types-list',
                'chat-send-file',
                'chat-search',
            ]);
        }

        $managerRole = Role::where('name', 'Manager')->first();
        if ($managerRole) {
            $managerRole->givePermissionTo([
                'invoices-list',
                'invoices-unpaid',
                'invoices-view',
                'invoices-status',
                'invoices-payments',
                'invoices-dispatcher-report',
                'truck-types-list',
                'chat-send-file',
                'chat-search',
            ]);
        }

        $agentRole = Role::where('name', 'Sales Agent')->first();
        if ($agentRole) {
            $agentRole->givePermissionTo([
                'chat-send-file',
                'chat-search',
            ]);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'avatar')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('avatar');
            });
        }
    }
};
