<?php

namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Las pantallas del portal requieren pertenecer a una institución activa.
 * El personal de Blockfinity no usa el portal: se le lleva al CMS.
 */
class Middleware_Exigir_Cliente
{
    public function handle(Request $Peticion, Closure $Siguiente): Response
    {
        $Usuario = $Peticion->user();

        if ($Usuario->Es_Personal_Blockfinity()) {
            return redirect('/admin');
        }

        abort_unless($Usuario->Institucion?->esta_activa, 403, 'Su institución no tiene acceso activo al portal.');

        return $Siguiente($Peticion);
    }
}
