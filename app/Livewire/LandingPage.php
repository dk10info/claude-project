<?php

namespace App\Livewire;

use App\Http\Responses\LoginResponse;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LandingPage extends Component
{
    public $showLoginModal = false;

    public $email = '';

    public $password = '';

    public $remember = false;

    public $loginError = '';

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required|min:6',
    ];

    public function openLoginModal()
    {
        $this->showLoginModal = true;
        $this->resetValidation();
        $this->loginError = '';
    }

    public function closeLoginModal()
    {
        $this->showLoginModal = false;
        $this->reset(['email', 'password', 'remember', 'loginError']);
    }

    public function login()
    {
        $this->validate();

        // Optimize authentication with eager loading
        $credentials = ['email' => $this->email, 'password' => $this->password];

        if (Auth::attempt($credentials, $this->remember)) {
            $user = Auth::user()->load('roles');

            // Direct role check without caching
            $userRoles = $user->roles->pluck('name')->toArray();

            // Quick status check for employees
            if (in_array('employee', $userRoles) && $user->status !== 'active') {
                Auth::logout();
                $this->loginError = 'Your account is inactive. Please contact support.';

                return;
            }

            // Regenerate session for security
            request()->session()->regenerate();

            return app(LoginResponse::class)->toResponse(request());
        }

        $this->loginError = 'Invalid email or password.';
    }

    public function render()
    {
        return view('livewire.landing-page')
            ->layout('layouts.guest');
    }
}
