<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\LoginResponse as BaseLoginResponse;
use Filament\Pages\Dashboard;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse extends BaseLoginResponse
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        if (auth()->user()->hasRole('admin')) {
            return redirect()->to(Dashboard::getUrl(panel: 'admin'));
        } else {
            return redirect()->to(Dashboard::getUrl(panel: 'employee'));
        }
    }
}
