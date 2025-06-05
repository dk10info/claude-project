<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\LogoutResponse as BaseLogoutResponse;
use Illuminate\Http\RedirectResponse;

class LogoutResponse extends BaseLogoutResponse
{
    public function toResponse($request): RedirectResponse
    {
        return redirect('/');
    }
}
