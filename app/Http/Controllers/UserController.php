<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('users');
        $users = User::with('roles')->latest()->paginate(10);
        $data_array['users'] = $users;
        return view('users.index', $data_array);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create-users');
        $data['roles']        = Role::get();
        return view('users.add', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        if (!auth()->user()->can('edit-users') && !auth()->user()->hasRole('Admin') && auth()->id() != $user->id) {
            abort(403, 'Unauthorized action.');
        }
        $data['roles'] = Role::get();
        $data['user'] = $user;
        $data['user_roles'] = $user->roles->pluck('id')->toArray(); // Get user's role IDs
        return view('users.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        if (!auth()->user()->can('edit-users') && !auth()->user()->hasRole('Admin') && auth()->id() != $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'first_name'      => ['required', 'string', 'max:255'],
            'last_name'       => ['required', 'string', 'max:255'],
            'load_commission' => ['nullable', 'numeric', 'between:0,99999.99'],
            'avatar'          => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ]);

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->name = trim($request->first_name . " " . $request->last_name);

        if ($request->has('load_commission') && !is_null($request['load_commission'])) {
            $user->load_commission = $request->load_commission;
        }

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $avatarFilename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('uploads/avatars');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            // Remove previous avatar file if exists
            if ($user->avatar && file_exists(public_path($user->avatar))) {
                @unlink(public_path($user->avatar));
            }
            $file->move($destinationPath, $avatarFilename);
            $user->avatar = 'uploads/avatars/' . $avatarFilename;
        }

        $result = $user->save();
        if ($result) {
            // Super Admin (User #1) role CANNOT be changed
            if ($user->id != 1 && (auth()->user()->can('edit-users') || auth()->user()->hasRole('Admin')) && $request->filled('user_type')) {
                $role_name = $user->getRoleNames();
                if (empty($role_name[0]) || $request['user_type'] != $role_name[0]) {
                    if (!empty($role_name[0])) {
                        $user->removeRole($role_name[0]);
                    }
                    $user->assignRole($request['user_type']);
                }
            }
            return redirect()->route('users.index')->with('status', 'Data Updated Successfully!');
        } else {
            return redirect()->route('users.index')->with('error', 'Something Went Wrong');
        }
    }

    public function changeStatus(Request $request, User $user)
    {
        if (!auth()->user()->can('change-users-status') && !auth()->user()->can('edit-users') && !auth()->user()->hasRole('Admin')) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized action.'
                ], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        // Prevent modifying Super Admin
        if ($user->id == 1) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Status cannot be changed.'
                ], 403);
            }
            return redirect()->route('users.index')->with('error', 'Status cannot be changed.');
        }

        // Prevent user from changing their own status
        if (auth()->check() && auth()->id() == $user->id) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => 'You cannot change your own status.'
                ], 403);
            }
            return redirect()->route('users.index')->with('error', 'You cannot change your own status.');
        }

        // Retrieve the selected status from the request or toggle existing
        $newStatus = $request->input('status');
        if (!$newStatus || !in_array($newStatus, ['active', 'inactive'])) {
            $newStatus = ($user->status === 'active') ? 'inactive' : 'active';
        }

        // Update the user's status
        $user->status = $newStatus;
        $success = $user->save();

        if ($request->ajax() || $request->wantsJson()) {
            if ($success) {
                return response()->json([
                    'status' => true,
                    'user_id' => $user->id,
                    'new_status' => $user->status,
                    'status_label' => ucfirst($user->status),
                    'badge_class' => $user->status === 'active' ? 'bg-label-success' : 'bg-label-danger',
                    'message' => 'User status updated to ' . ucfirst($user->status) . ' successfully!'
                ], 200);
            }
            return response()->json([
                'status' => false,
                'message' => 'Failed to update user status.'
            ], 500);
        }

        if ($success) {
            return redirect()->route('users.index')->with('status', 'User status updated to ' . ucfirst($user->status) . ' successfully!');
        }
        return redirect()->route('users.index')->with('error', 'Failed to update user status.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete-users');

        if ($user->id == 1) {
            return redirect()->route('users.index')->with('error', 'Super Admin cannot be deleted.');
        }

        if($user->delete()){
            return redirect()->route('users.index')->with('status', 'User Deleted Successfully!');
        }else{
            return redirect()->route('users.index')->with('error', 'User Not Deleted!');
        }
    }

    /**
     * Change the password specified resource from storage.
     */
    public function changePasswordForm(User $user)
    {
        $this->authorize('change-password');
        return view('users.change-password', ['user' => $user]);
    }


    /**
     * Change the password specified resource from storage.
     */
    public function updatePassword(Request $request, User $user)
    {
        $this->authorize('change-password');
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $user->password = Hash::make($request->password);
        $result = $user->save();
        if($result){
            return redirect()->route('users.index')->with('status', 'Password Change Successfully!');
        }else{
            return redirect()->route('users.index')->with('error', 'Password Not Change!');
        }
    }

}
