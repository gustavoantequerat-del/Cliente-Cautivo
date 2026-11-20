<?php

namespace App\Servicios;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Archivos pequeños (PDF, XML) en el disco privado. Los pesados van como enlace externo.
 */
class Servicio_Archivos
{
    public static function Guardar_Archivo(UploadedFile $Archivo, string $Carpeta): array
    {
        return [
            'Ruta' => $Archivo->store($Carpeta, 'local'),
            'Nombre' => $Archivo->getClientOriginalName(),
            'Tamano' => $Archivo->getSize(),
            'Codigo_Verificacion' => hash_file('sha256', $Archivo->getRealPath()),
        ];
    }

    public static function Descargar_Archivo(?string $Ruta, ?string $Nombre_Descarga = null): StreamedResponse
    {
        abort_unless($Ruta && Storage::disk('local')->exists($Ruta), 404);

        return Storage::disk('local')->download($Ruta, $Nombre_Descarga);
    }

    public static function Eliminar_Archivo(?string $Ruta): void
    {
        if ($Ruta) {
            Storage::disk('local')->delete($Ruta);
        }
    }
}
