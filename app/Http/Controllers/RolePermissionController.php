<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    public static function getGroupedPermissions()
    {
        return [
            'Users' => [
                'icon' => 'ti ti-users',
                'description' => 'User management, staff accounts, active status, and credentials',
                'permissions' => [
                    'users' => 'View Users List',
                    'create-users' => 'Create New User Account',
                    'edit-users' => 'Edit User Information',
                    'delete-users' => 'Delete User Account',
                    'change-users-status' => 'Toggle User Active / Inactive Status',
                    'change-password' => 'Change User Passwords',
                    'edit-profile' => 'Edit Own Profile & Avatar',
                ],
            ],
            'Carriers' => [
                'icon' => 'ti ti-truck-delivery',
                'description' => 'Carrier directory, packets, dispatch assignment, and leads workflow',
                'permissions' => [
                    'carriers-list' => 'View Carriers List',
                    'carriers-create' => 'Add New Carrier',
                    'carriers-view' => 'View Carrier Profile & Full Details',
                    'carriers-edit' => 'Edit Carrier Information',
                    'carriers-delete' => 'Delete Carrier Record',
                    'carriers-status' => 'Change Carrier Active / Inactive Status',
                    'send-email' => 'Send Setup Packet & Official Emails',
                    'assign-carrier' => 'Assign Carrier to Dispatcher',
                    'assigned-carriers-list' => 'View Assigned Carriers Dashboard',
                    'open-leads-list' => 'View Open Leads Page',
                    'open-leads-notes' => 'Post Notes & Comments on Carrier Leads',
                    'open-leads-status' => 'Update Lead Status & Follow-ups',
                    'documents-received' => 'Access Received Documents',
                    'documents-received-30-days' => 'Access Documents Received in Last 30 Days',
                ],
            ],
            'Dispatch / Loads' => [
                'icon' => 'ti ti-box',
                'description' => 'Freight dispatching, active loads management, rates, and attachments',
                'permissions' => [
                    'dispatchers-list' => 'View Loads / Dispatch List',
                    'dispatchers-create' => 'Add New Load / Dispatch',
                    'dispatchers-view' => 'View Load Details & Overview',
                    'dispatchers-edit' => 'Edit Load & Rate Information',
                    'dispatchers-delete' => 'Delete Dispatch / Load Record',
                    'can-load-cancel' => 'Cancel Dispatched Load',
                    'dispatcher-edit-attachment' => 'Upload & Manage Load Documents',
                ],
            ],
            'Invoices' => [
                'icon' => 'ti ti-file-invoice',
                'description' => 'Invoicing, payment tracking, balance reconciliations, and PDF generation',
                'permissions' => [
                    'invoices-list' => 'View All Invoices',
                    'invoices-unpaid' => 'View Unpaid / Due Invoices',
                    'invoices-view' => 'View Invoice Details & Download PDF',
                    'invoices-status' => 'Update Invoice Payment Status (Due/Partial/Paid)',
                    'invoices-payments' => 'Record & Manage Payment Transactions',
                    'invoices-dispatcher-report' => 'Generate & Download Dispatcher PDF Report',
                    'invoices-download-excel' => 'Export Invoices to Excel Spreadsheets',
                ],
            ],
            'Truck Types' => [
                'icon' => 'ti ti-tir',
                'description' => 'Equipment categories, truck configurations, and specifications',
                'permissions' => [
                    'truck-types-list' => 'View Truck Types List',
                    'truck-types-create' => 'Create New Truck Type',
                    'truck-types-edit' => 'Edit Truck Type Details',
                    'truck-types-delete' => 'Delete Truck Type Record',
                ],
            ],
            'Reports' => [
                'icon' => 'ti ti-chart-bar',
                'description' => 'Analytical dashboards, carrier/dispatcher metrics, and data exports',
                'permissions' => [
                    'carriers-report' => 'View Carrier Analytics Report',
                    'dispatchers-report' => 'View Dispatcher Performance Report',
                    'truck-types-report' => 'View Truck Types Fleet Report',
                    'reports-download' => 'Download Excel / XLS Performance Reports',
                ],
            ],
            'Roles & Permissions' => [
                'icon' => 'ti ti-shield-lock',
                'description' => 'Role hierarchies, access controls, and permission assignment',
                'permissions' => [
                    'list-roles' => 'View Roles Overview & Cards',
                    'add-role' => 'Create New Custom Role',
                    'edit-role' => 'Modify Role Name & Permissions',
                    'delete-role' => 'Delete Custom Role',
                    'list-permission' => 'View Permissions Matrix',
                    'edit-permission' => 'Manage & Save Permissions Matrix',
                ],
            ],
            'Messenger / Chat' => [
                'icon' => 'ti ti-messages',
                'description' => 'Real-time internal chat, direct messages, and document sharing',
                'permissions' => [
                    'chat' => 'Access Chat & Messenger',
                    'chat-clear-history' => 'Clear Chat History with Contacts',
                    'chat-send-file' => 'Send Documents & File Attachments in Chat',
                    'chat-search' => 'Search Messages & Filter Contacts',
                    'chat-view-profile' => 'View Contact User Profile in Messenger',
                ],
            ],
            'Settings' => [
                'icon' => 'ti ti-settings',
                'description' => 'Company profile, email credentials, and portal configurations',
                'permissions' => [
                    'settings' => 'Manage System Settings & Email Config',
                ],
            ],
        ];
    }

    public function index(Request $request)
    {
        $this->authorize('list-roles');

        $perPage = in_array((int)$request->input('per_page'), [10, 15, 20, 25, 50]) ? (int)$request->input('per_page') : 10;

        $roles = Role::withCount('users')->with('permissions')->get();
        $permissions = Permission::all();
        $groupedPermissions = self::getGroupedPermissions();
        $users = User::with('roles')->latest()->paginate($perPage)->withQueryString();

        return view('roles.index', compact('roles', 'permissions', 'groupedPermissions', 'users'));
    }

    public function permissionIndex()
    {
        $this->authorize('list-permission');
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();
        $groupedPermissions = self::getGroupedPermissions();

        return view('roles.permission', compact('roles', 'permissions', 'groupedPermissions'));
    }

    public function create()
    {
        $this->authorize('add-role');
        $permissions = Permission::all();
        $groupedPermissions = self::getGroupedPermissions();
        return view('roles.add', compact('permissions', 'groupedPermissions'));
    }

    public function store(Request $request)
    {
        $this->authorize('add-role');

        $request->validate([
            'role_name' => 'required|string|max:100|unique:roles,name',
        ]);

        $role = Role::create([
            'name' => $request->role_name,
            'guard_name' => 'web',
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('roles.index')->with('status', 'Role created successfully!');
    }

    public function edit(Role $role)
    {
        $this->authorize('edit-role');
        $permissions = Permission::all();
        $groupedPermissions = self::getGroupedPermissions();
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('roles.edit', compact('role', 'permissions', 'groupedPermissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $this->authorize('edit-role');

        $request->validate([
            'role_name' => 'required|string|max:100|unique:roles,name,' . $role->id,
        ]);

        $role->update(['name' => $request->role_name]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        } else {
            $role->syncPermissions([]);
        }

        return redirect()->route('roles.index')->with('status', 'Role updated successfully!');
    }

    public function destroy(Role $role)
    {
        $this->authorize('delete-role');

        if ($role->name === 'Admin') {
            return back()->with('error', 'Admin role cannot be deleted.');
        }

        $role->delete();
        return redirect()->route('roles.index')->with('status', 'Role deleted successfully!');
    }

    public function updateRoles(Request $request)
    {
        $this->authorize('edit-permission');
        $roles = Role::all();
        $permissions = $request->input('permissions', []);

        foreach ($roles as $role) {
            if ($role->name === 'Admin') {
                continue;
            }
            if (isset($permissions[$role->name])) {
                $role->syncPermissions($permissions[$role->name]);
            } else {
                $role->syncPermissions([]);
            }
        }

        return redirect()->route('roles.permissionIndex')->with('status', 'Permissions updated successfully!');
    }
}
