<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** Si se revoca la credencial (is_active = false) la sesión se corta en la siguiente petición */
class EnsureActiveUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && ! $request->user()->is_active) {
            Auth::logout();
            $request->session()->invalidate();

            return redirect()->route('login')->with('error', 'Su credencial fue desactivada. Contacte a su administrador.');
        }

        return $next($request);
    }
}
