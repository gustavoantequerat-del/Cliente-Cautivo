<?php

namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Facturación, bolsa de horas, aprobaciones y equipo: solo el administrador delegado */
class Middleware_Exigir_Administrador_Delegado
{
    public function handle(Request $Peticion, Closure $Siguiente): Response
    {
        if (! $Peticion->user()->Es_Administrador_Delegado()) {
            return redirect('/')->with('Mensaje_Error', 'Esta sección la gestiona el responsable institucional de su organización.');
        }

        return $Siguiente($Peticion);
    }
}
