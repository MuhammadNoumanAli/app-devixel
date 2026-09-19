<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(AuthRequest $request)
    {
        $userData = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'name' => trim($request->first_name . " " . $request->last_name),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => 'active',
            'load_commission' => $request->filled('load_commission') ? $request->load_commission : 0,
        ];

        $user = User::create($userData);

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $avatarFilename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('uploads/avatars');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $file->move($destinationPath, $avatarFilename);
            $user->avatar = 'uploads/avatars/' . $avatarFilename;
            $user->save();
        }

        if ($request->filled('user_type')) {
            $user->assignRole($request->user_type);
        } else {
            $user->assignRole('Sales Agent');
        }

        return redirect()->route('users.index')->with('status', 'User added successfully!');
    }
}
