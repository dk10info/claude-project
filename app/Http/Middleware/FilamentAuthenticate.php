<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class FilamentAuthenticate extends Middleware
{
    /**
     * @param  \Illuminate\Http\Request  $request
     */
    protected function redirectTo($request): ?string
    {
        return route('landing');
    }
}
