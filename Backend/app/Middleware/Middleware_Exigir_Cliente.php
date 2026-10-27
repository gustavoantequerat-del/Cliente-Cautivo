<?php

namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Las pantallas del portal requieren pertenecer a una institución activa.
 * El staff sin institución se redirige al CMS.
 */
class Middleware_Exigir_Cliente
{
    public function handle(Request $Peticion, Closure $Siguiente): Response
    {
        $Usuario = $Peticion->user();

        if ($Usuario->Es_Staff() && ! $Usuario->institucion_id) {
            return redirect('/admin');
        }

        abort_unless($Usuario->Institucion?->esta_activa, 403, 'Su institución no tiene acceso activo al portal.');

        return $Siguiente($Peticion);
    }
}
