<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CustomAuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            if ($user->hasRole('employee') && $user->status !== 'active') {
                Auth::logout();
                throw ValidationException::withMessages([
                    'email' => 'Your account is inactive. Please contact support.',
                ]);
            }

            $request->session()->regenerate();

            // Redirect based on user role
            if ($user->hasRole('admin')) {
                return redirect()->intended('/admin');
            } elseif ($user->hasRole('employee')) {
                return redirect()->intended('/employee');
            }

            // Default redirect if no role
            return redirect('/');
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
