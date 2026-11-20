<?php

namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Facturación, bolsa de horas, aprobaciones y equipo: solo el cliente institucional */
class Middleware_Exigir_Cliente_Institucional
{
    public function handle(Request $Peticion, Closure $Siguiente): Response
    {
        if (! $Peticion->user()->Es_Cliente_Institucional()) {
            return redirect('/')->with('Mensaje_Error', 'Esta sección la gestiona el responsable institucional de su organización.');
        }

        return $Siguiente($Peticion);
    }
}
