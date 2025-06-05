<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        if (! $request->user()) {
            abort(403, 'Unauthorized');
        }

        // Handle multiple roles separated by pipe
        $rolesArray = explode('|', $roles);

        if (! $request->user()->hasAnyRole($rolesArray)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
