<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Las pantallas del portal requieren pertenecer a una institución activa.
 * El staff (admin) sin institución se redirige al CMS.
 */
class EnsureClient
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user->isAdmin() && ! $user->institution_id) {
            return redirect()->route('admin.dashboard');
        }

        abort_unless($user->institution && $user->institution->is_active, 403, 'Su institución no tiene acceso activo al portal.');

        return $next($request);
    }
}
