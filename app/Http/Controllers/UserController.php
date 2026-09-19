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
        $this->authorize('edit-users');
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
        $this->authorize('edit-users');
        $validated = $request->validate([
            'first_name'    => ['required', 'string', 'max:255'],
            'last_name'     => ['required', 'string', 'max:255'],
            'load_commission' => ['nullable','numeric','between:0,99999.99'],
        ]);

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->name = $request->first_name . " " . $request->last_name;
        if ($request->has('load_commission') && !is_null($request['load_commission'])){
            $user->load_commission = $request->load_commission;
        }
        $result = $user->save();
        if($result){
            $role_name = $user->getRoleNames();
            if($request['user_type'] != $role_name[0]){
                $user->removeRole($role_name[0]);
                $user->assignRole($request['user_type']);
            }
            return redirect()->route('users.index')->with('status', 'Data Updated Successfully!');
        }else{
            return redirect()->route('users.index')->with('error', 'Something Went Wrong');
        }
    }

    public function changeStatus(Request $request, User $user)
    {
        $this->authorize('change-users-status');
        // Retrieve the selected status from the request
        $selectedStatus = $request->input('status');

        // Update the user's status
        $user->status = $selectedStatus;
        $success = $user->save();
        if($success){
            return response(['message' => 'Status Updated', 'status' => true ], 200);
        }
        return response(['message' => 'Status Updated', 'status' => false ], 200);

        // Return a response indicating the updated status
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete-users');

        if ($user->id == 1) {
            return redirect()->route('users.index')->with('error', 'Super Admin (User #1) cannot be deleted.');
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
