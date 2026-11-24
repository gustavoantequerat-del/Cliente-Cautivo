<?php

namespace App\Controladores\Portal;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response as Respuesta_Http;

/** Logo de la institución del usuario, para su portal con marca propia */
class Controlador_Marca
{
    public function Mostrar_Logo(Request $Peticion): Respuesta_Http
    {
        $Ruta_Logo = $Peticion->user()->Institucion?->ruta_logo;
        abort_unless($Ruta_Logo && Storage::disk('local')->exists($Ruta_Logo), 404);

        return response()->file(Storage::disk('local')->path($Ruta_Logo), [
            'Cache-Control' => 'private, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
