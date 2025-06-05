<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class LandingController extends Controller
{
    public function index()
    {
        // If user is already logged in, redirect to appropriate panel
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->hasRole('admin')) {
                return redirect('/admin');
            } elseif ($user->hasAnyRole(['employee'])) {
                return redirect('/employee');
            }
        }

        return view('landing');
    }
}
