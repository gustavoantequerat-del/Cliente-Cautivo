<?php

namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Restringe una sección a instituciones con la unidad de negocio contratada (ej. RESEARCH) */
class Middleware_Exigir_Unidad_Contratada
{
    public function handle(Request $Peticion, Closure $Siguiente, string $Codigo_Unidad): Response
    {
        if (! $Peticion->user()->Institucion->Tiene_Unidad_Contratada($Codigo_Unidad)) {
            return redirect('/')
                ->with('Mensaje_Error', 'Esta sección no está incluida en el contrato de su institución. Consulte a su ejecutiva de cuenta.');
        }

        return $Siguiente($Peticion);
    }
}
