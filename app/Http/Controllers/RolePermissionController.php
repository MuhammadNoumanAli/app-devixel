<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    public function index()
    {
        $this->authorize('list-roles');

        $roles = Role::withCount('users')->with('permissions')->get();
        $permissions = Permission::all();
        $users = User::with('roles')->latest()->paginate(15);

        return view('roles.index', compact('roles', 'permissions', 'users'));
    }

    public function permissionIndex()
    {
        $this->authorize('list-permission');
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();

        return view('roles.permission', compact('roles', 'permissions'));
    }

    public function create()
    {
        $this->authorize('add-role');
        $permissions = Permission::all();
        return view('roles.add', compact('permissions'));
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
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
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
