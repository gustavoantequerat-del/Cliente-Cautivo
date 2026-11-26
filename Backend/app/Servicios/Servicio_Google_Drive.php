<?php

namespace App\Servicios;

/**
 * Los PDF (Research, webinars y píldoras) se leen con el visor de Google Drive incrustado en el portal.
 * El archivo debe estar compartido en Drive como "Cualquier persona con el enlace".
 */
class Servicio_Google_Drive
{
    public static function Extraer_Id_Archivo(?string $Enlace): ?string
    {
        $Datos_Enlace = Servicio_Enlaces_Video::Analizar_Enlace($Enlace);

        return ($Datos_Enlace['Proveedor'] ?? null) === 'drive' ? $Datos_Enlace['Id'] : null;
    }

    /** Visor de Google para incrustar ({Url_Visor}) y enlace para abrirlo en Drive ({Url_Abrir}); null si no es de Drive */
    public static function Construir_Visor(?string $Enlace): ?array
    {
        $Id_Archivo = static::Extraer_Id_Archivo($Enlace);

        return $Id_Archivo ? [
            'Url_Visor' => "https://drive.google.com/file/d/{$Id_Archivo}/preview",
            'Url_Abrir' => "https://drive.google.com/file/d/{$Id_Archivo}/view",
        ] : null;
    }
}
