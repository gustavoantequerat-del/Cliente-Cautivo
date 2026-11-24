<?php

namespace App\Servicios;

/**
 * Convierte el enlace que pega el personal de Blockfinity (Drive, YouTube, Vimeo) en proveedor + ID,
 * y construye la URL para incrustarlo. Nunca se guarda ni se imprime HTML de iframe ajeno.
 */
class Servicio_Enlaces_Video
{
    public const Proveedores = ['drive' => 'Google Drive', 'youtube' => 'YouTube', 'vimeo' => 'Vimeo'];

    /** Dominios permitidos en la cabecera CSP frame-src */
    public const Dominios_Permitidos_Iframe = [
        'https://drive.google.com',
        'https://www.youtube-nocookie.com',
        'https://www.youtube.com',
        'https://player.vimeo.com',
    ];

    public static function Analizar_Enlace(?string $Enlace): array
    {
        $Enlace = trim((string) $Enlace);
        if ($Enlace === '') {
            return [];
        }

        // Google Drive: /file/d/{id}/view, /open?id={id}, /uc?id={id}
        if (preg_match('~drive\.google\.com/(?:file/d/|open\?id=|uc\?(?:export=\w+&)?id=)([A-Za-z0-9_-]{10,})~', $Enlace, $Coincidencia)) {
            return ['Proveedor' => 'drive', 'Id' => $Coincidencia[1]];
        }

        // YouTube: watch?v=, youtu.be/, /embed/, /shorts/, /live/
        if (preg_match('~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $Enlace, $Coincidencia)) {
            return ['Proveedor' => 'youtube', 'Id' => $Coincidencia[1]];
        }

        // Vimeo: vimeo.com/{id}, vimeo.com/{id}/{hash}, player.vimeo.com/video/{id}?h={hash}
        if (preg_match('~vimeo\.com/(?:video/)?(\d+)(?:/([A-Za-z0-9]+))?(?:\?.*?h=([A-Za-z0-9]+))?~', $Enlace, $Coincidencia)) {
            return ['Proveedor' => 'vimeo', 'Id' => $Coincidencia[1], 'Hash' => ($Coincidencia[2] ?? null) ?: ($Coincidencia[3] ?? null)];
        }

        return [];
    }

    public static function Construir_Url_Embebida(?string $Proveedor, ?string $Id, ?string $Hash = null): ?string
    {
        if (! $Proveedor || ! $Id) {
            return null;
        }

        return match ($Proveedor) {
            'drive' => "https://drive.google.com/file/d/{$Id}/preview",
            'youtube' => "https://www.youtube-nocookie.com/embed/{$Id}?rel=0&modestbranding=1",
            'vimeo' => "https://player.vimeo.com/video/{$Id}".($Hash ? "?h={$Hash}" : ''),
            default => null,
        };
    }

    public static function Construir_Url_Miniatura(?string $Proveedor, ?string $Id): ?string
    {
        return match ($Proveedor) {
            'youtube' => "https://i.ytimg.com/vi/{$Id}/hqdefault.jpg",
            'drive' => "https://drive.google.com/thumbnail?id={$Id}&sz=w640",
            default => null,
        };
    }
}
