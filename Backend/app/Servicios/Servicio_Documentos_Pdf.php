<?php

namespace App\Servicios;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response as Respuesta_Http;

/**
 * PDFs de Research, webinars y píldoras. En el CMS se elige el origen: archivo subido (se muestra con el lector
 * de PDF del navegador) o enlace de Google Drive (se muestra con el visor de Google).
 */
class Servicio_Documentos_Pdf
{
    /** { Url_Visor, Url_Abrir } para el Frontend, o null si no hay documento */
    public const Origenes = ['archivo' => 'Subir el archivo PDF', 'drive' => 'Enlace de Google Drive'];

    /** { Url_Visor, Url_Abrir } para el Frontend según el origen elegido, o null si no hay documento */
    public static function Armar_Documento(?string $Origen, ?string $Ruta_Archivo, ?string $Enlace_Drive, string $Url_Propia): ?array
    {
        if ($Origen === 'drive') {
            return Servicio_Google_Drive::Construir_Visor($Enlace_Drive);
        }

        return $Ruta_Archivo ? ['Url_Visor' => $Url_Propia, 'Url_Abrir' => $Url_Propia] : null;
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
