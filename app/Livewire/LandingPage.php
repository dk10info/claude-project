<?php

namespace App\Livewire;

use App\Http\Responses\LoginResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Livewire\Component;

class LandingPage extends Component
{
    public $showLoginModal = false;

    public $email = '';

    public $password = '';

    public $remember = false;

    public $loginError = '';

    public $isDarkMode = false;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required|min:6',
    ];

    public function mount()
    {
        // Check both our custom cookie and Filament's theme setting
        $darkModeCookie = Cookie::get('darkMode', 'false') === 'true';
        $filamentTheme = Cookie::get('filament_theme', 'system');

        // If Filament has a specific theme set, use that; otherwise use our cookie
        if ($filamentTheme === 'dark') {
            $this->isDarkMode = true;
        } elseif ($filamentTheme === 'light') {
            $this->isDarkMode = false;
        } else {
            $this->isDarkMode = $darkModeCookie;
        }
    }

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

    public function toggleTheme()
    {
        $this->isDarkMode = ! $this->isDarkMode;

        // Set both our custom cookie and Filament's theme cookie
        Cookie::queue('darkMode', $this->isDarkMode ? 'true' : 'false', 60 * 24 * 365);
        Cookie::queue('filament_theme', $this->isDarkMode ? 'dark' : 'light', 60 * 24 * 365);

        // Update localStorage to sync with Filament and trigger storage event
        $this->js("
            localStorage.setItem('theme', '".($this->isDarkMode ? 'dark' : 'light')."');
            
            // Dispatch storage event for other tabs/windows
            window.dispatchEvent(new StorageEvent('storage', {
                key: 'theme',
                newValue: '".($this->isDarkMode ? 'dark' : 'light')."',
                url: window.location.href
            }));
        ");

        // Dispatch browser event for Alpine.js
        $this->dispatch('dark-mode-toggled', ['isDark' => $this->isDarkMode]);

        // Update the class directly via JavaScript
        $this->js($this->isDarkMode
            ? "document.documentElement.classList.add('dark')"
            : "document.documentElement.classList.remove('dark')"
        );
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
