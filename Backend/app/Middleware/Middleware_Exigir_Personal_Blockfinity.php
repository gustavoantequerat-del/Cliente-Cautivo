<?php

namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** El CMS es solo para el personal de Blockfinity (superadmin y usuarios ejecutivos) */
class Middleware_Exigir_Personal_Blockfinity
{
    public function handle(Request $Peticion, Closure $Siguiente): Response
    {
        abort_unless($Peticion->user()?->Es_Personal_Blockfinity(), 403);

        return $Siguiente($Peticion);
    }
}
