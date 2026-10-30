<?php

namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** El CMS es solo para el staff de Blockfinity */
class Middleware_Exigir_Staff
{
    public function handle(Request $Peticion, Closure $Siguiente): Response
    {
        abort_unless($Peticion->user()?->Es_Staff(), 403);

        return $Siguiente($Peticion);
    }
}
