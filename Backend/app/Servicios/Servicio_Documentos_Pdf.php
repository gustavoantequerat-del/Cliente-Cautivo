<?php

namespace App\Servicios;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response as Respuesta_Http;

/**
 * Entrega un PDF al visor protegido del portal (Research, webinars y píldoras):
 * desde el archivo subido o, si es un enlace de Drive, descargándolo por su ID.
 */
class Servicio_Documentos_Pdf
{
    public static function Responder_Pdf(?string $Ruta_Archivo, ?string $Enlace_Drive): Respuesta_Http
    {
        $Contenido_Pdf = null;
        if ($Ruta_Archivo && Storage::disk('local')->exists($Ruta_Archivo)) {
            $Contenido_Pdf = Storage::disk('local')->get($Ruta_Archivo);
        } elseif ($Id_Drive = Servicio_Google_Drive::Extraer_Id_Archivo($Enlace_Drive)) {
            $Contenido_Pdf = Servicio_Google_Drive::Obtener_Pdf($Id_Drive);
        }
        abort_unless($Contenido_Pdf, 502, 'No se pudo obtener el PDF. Revise que en Drive esté compartido como "Cualquier persona con el enlace".');

        // Inline y sin caché del navegador: se muestra en el visor, no se descarga
        return response($Contenido_Pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Robots-Tag' => 'noindex',
        ]);
    }
}
