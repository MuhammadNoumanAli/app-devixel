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
            'HR Employees' => [
                'icon' => 'ti ti-user-check',
                'description' => 'Employee profiles, designations, joining dates, base salaries, and dispatcher target bonus setup',
                'permissions' => [
                    'hr-employees-list' => 'View Employee Profiles & Salary Information',
                    'hr-employees-edit' => 'Edit Base Salary, Bank Info, Thumb ID & Dispatcher Targets',
                ],
            ],
            'HR Attendance & Punches' => [
                'icon' => 'ti ti-fingerprint',
                'description' => 'Biometric thumb scans, web kiosk clock-in/out, late arrivals calculation, and supervisor regularization',
                'permissions' => [
                    'hr-attendance-list' => 'View Punch Ledger & Daily Attendance Records',
                    'hr-attendance-punch' => 'Self Clock-In / Clock-Out (Web Kiosk / Thumb Scan)',
                    'hr-attendance-regularize' => 'Approve & Regularize Missed Punches / Waive Late Deductions',
                ],
            ],
            'HR Leaves & Holidays' => [
                'icon' => 'ti ti-calendar-event',
                'description' => 'Leave applications, approvals, monthly paid leaves quota, public holidays calendar, and working weekend swaps',
                'permissions' => [
                    'hr-leaves-list' => 'View Leave Applications & Balance Quotas',
                    'hr-leaves-apply' => 'Submit Leave Application',
                    'hr-leaves-approve' => 'Approve or Reject Leave Applications',
                    'hr-holidays-manage' => 'Manage Public Holidays & Working Weekend / Compensatory Off Exceptions',
                ],
            ],
            'HR Payroll & Payslips' => [
                'icon' => 'ti ti-receipt-2',
                'description' => 'Monthly payroll calculation batches, sales lead bonuses, dispatcher targets, Normal Payslip, Detailed Payslip, and bank export',
                'permissions' => [
                    'hr-payroll-list' => 'View Monthly Payroll Cycles & Batches',
                    'hr-payroll-generate' => 'Calculate & Process Monthly Payroll Batch',
                    'hr-payroll-lock' => 'Approve & Lock Payroll Batch (Freeze Historical Records)',
                    'hr-payslip-normal' => 'View & Download Normal Payslip (Slip 1: Base + Commissions - Deductions)',
                    'hr-payslip-detailed' => 'View & Download Detailed Payslip (Slip 2: Full Itemized Audit Trail)',
                    'hr-payroll-export' => 'Export Bank Disbursement Spreadsheets (Excel / XLSX)',
                ],
            ],
            'HR Loans & Advances' => [
                'icon' => 'ti ti-cash',
                'description' => 'Staff loan applications, approval workflows, monthly installment schedule, and payroll recovery',
                'permissions' => [
                    'hr-loans-list' => 'View Staff Loans & Installment Schedules',
                    'hr-loans-manage' => 'Create, Approve, Waive & Adjust Loans',
                ],
            ],
            'HR Settings & Shifts' => [
                'icon' => 'ti ti-adjustments-alt',
                'description' => 'Grace time minutes, late salary deduction %, lead bonus threshold ($200) & days window, and work shifts',
                'permissions' => [
                    'hr-settings-manage' => 'Configure Grace Period, Late Deduction %, Lead Bonus Days & Max Leaves',
                    'hr-shifts-manage' => 'Create, Edit & Assign Work Shifts (Morning / Evening / Night US)',
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
