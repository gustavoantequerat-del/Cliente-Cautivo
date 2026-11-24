<?php

namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Secciones globales del CMS (auditoría, contenido general): solo el superadmin, no los usuarios ejecutivos */
class Middleware_Exigir_Superadmin
{
    public function handle(Request $Peticion, Closure $Siguiente): Response
    {
        abort_unless($Peticion->user()?->Es_Superadmin(), 403);

        return $Siguiente($Peticion);
    }
}
