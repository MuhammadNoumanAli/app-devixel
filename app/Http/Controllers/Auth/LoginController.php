<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function __construct()
    {
        //
    }

    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        $remember = $request->filled('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();
            if ($user->status === 'inactive' || $user->status === '0') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your account is currently inactive. Please contact an administrator.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            // Record security audit login log with location and device
            try {
                \App\Models\UserLoginLog::recordLogin($user, $request->ip(), $request->userAgent());
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Failed to record login log for User #{$user->id}: " . $e->getMessage());
            }

            return redirect()->intended(route('home'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records or your account is inactive.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            \Illuminate\Support\Facades\Cache::forget('user-online-' . Auth::id());
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
