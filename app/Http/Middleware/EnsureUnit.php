<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Restringe una sección a instituciones con la unidad de negocio contratada */
class EnsureUnit
{
    public function handle(Request $request, Closure $next, string $code): Response
    {
        $institution = $request->user()->institution->loadMissing('units');

        if (! $institution->hasUnit($code)) {
            return redirect()->route('portal.home')
                ->with('error', 'Esta sección no está incluida en el contrato de su institución. Consulte a su ejecutiva de cuenta.');
        }

        return $next($request);
    }
}
