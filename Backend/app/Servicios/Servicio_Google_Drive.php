<?php

namespace App\Servicios;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Trae un PDF de Google Drive para mostrarlo en el visor del portal.
 * El navegador no puede pedirlo directo a Drive (CORS), así que lo pide al Backend,
 * que lo descarga con el ID del enlace y lo guarda en caché unas horas para no pedirlo en cada visita.
 * Requiere que el archivo esté compartido como "Cualquier persona con el enlace".
 */
class Servicio_Google_Drive
{
    private const Horas_En_Cache = 12;

    private const Tamano_Maximo_Bytes = 40 * 1024 * 1024;

    public static function Extraer_Id_Archivo(?string $Enlace): ?string
    {
        $Datos_Enlace = Servicio_Enlaces_Video::Analizar_Enlace($Enlace);

        return ($Datos_Enlace['Proveedor'] ?? null) === 'drive' ? $Datos_Enlace['Id'] : null;
    }

    /** Devuelve el contenido del PDF, o null si Drive no entregó un PDF (archivo privado, borrado u otro formato) */
    public static function Obtener_Pdf(string $Id_Archivo): ?string
    {
        if (! preg_match('/^[A-Za-z0-9_-]{10,}$/', $Id_Archivo)) {
            return null;
        }

        $Ruta_Cache = "cache_drive/{$Id_Archivo}.pdf";
        $Disco = Storage::disk('local');
        if ($Disco->exists($Ruta_Cache) && $Disco->lastModified($Ruta_Cache) > now()->subHours(self::Horas_En_Cache)->getTimestamp()) {
            return $Disco->get($Ruta_Cache);
        }

        $Respuesta = Http::timeout(30)->get('https://drive.usercontent.google.com/download', [
            'id' => $Id_Archivo, 'export' => 'download', 'confirm' => 't',
        ]);
        $Contenido = $Respuesta->successful() ? $Respuesta->body() : '';
        if (! str_starts_with($Contenido, '%PDF') || strlen($Contenido) > self::Tamano_Maximo_Bytes) {
            return null;
        }

        $Disco->put($Ruta_Cache, $Contenido);

        return $Contenido;
    }
}
