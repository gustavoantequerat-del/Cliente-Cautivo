<?php

namespace App\Servicios;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response as Respuesta_Http;

/**
 * PDFs de Research, webinars y píldoras. Lo normal es subir el archivo al portal: se muestra con el lector
 * de PDF del propio navegador. Si en vez de archivo hay un enlace de Drive (registros antiguos), se usa el visor de Google.
 */
class Servicio_Documentos_Pdf
{
    /** { Url_Visor, Url_Abrir } para el Frontend, o null si no hay documento */
    public static function Armar_Documento(?string $Ruta_Archivo, ?string $Enlace_Drive, string $Url_Propia): ?array
    {
        if ($Ruta_Archivo) {
            return ['Url_Visor' => $Url_Propia, 'Url_Abrir' => $Url_Propia];
        }

        return Servicio_Google_Drive::Construir_Visor($Enlace_Drive);
    }

    /** El archivo subido, para verlo en el navegador (no como descarga) */
    public static function Responder_Pdf(?string $Ruta_Archivo, ?string $Nombre_Archivo): Respuesta_Http
    {
        abort_unless($Ruta_Archivo && Storage::disk('local')->exists($Ruta_Archivo), 404);
        $Nombre_Seguro = preg_replace('/[^A-Za-z0-9._-]+/', '-', $Nombre_Archivo ?: 'documento.pdf');

        return response()->file(Storage::disk('local')->path($Ruta_Archivo), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$Nombre_Seguro.'"',
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
