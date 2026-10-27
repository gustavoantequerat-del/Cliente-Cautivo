<?php

namespace App\Support;

/**
 * Convierte el enlace que pega el admin (Drive, YouTube, Vimeo) en proveedor + ID,
 * y construye la URL de embed. Nunca se guarda ni se imprime HTML de iframe ajeno.
 */
class VideoEmbed
{
    public const PROVIDERS = ['gdrive' => 'Google Drive', 'youtube' => 'YouTube', 'vimeo' => 'Vimeo'];

    /** Dominios permitidos en la CSP frame-src */
    public const FRAME_HOSTS = [
        'https://drive.google.com',
        'https://www.youtube-nocookie.com',
        'https://www.youtube.com',
        'https://player.vimeo.com',
    ];

    public static function parse(?string $url): array
    {
        $url = trim((string) $url);
        if ($url === '') {
            return [];
        }

        // Google Drive: /file/d/{id}/view, /open?id={id}, /uc?id={id}
        if (preg_match('~drive\.google\.com/(?:file/d/|open\?id=|uc\?(?:export=\w+&)?id=)([A-Za-z0-9_-]{10,})~', $url, $m)) {
            return ['provider' => 'gdrive', 'id' => $m[1]];
        }

        // YouTube: watch?v=, youtu.be/, /embed/, /shorts/, /live/
        if (preg_match('~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $m)) {
            return ['provider' => 'youtube', 'id' => $m[1]];
        }

        // Vimeo: vimeo.com/{id}, vimeo.com/{id}/{hash}, player.vimeo.com/video/{id}?h={hash}
        if (preg_match('~vimeo\.com/(?:video/)?(\d+)(?:/([A-Za-z0-9]+))?(?:\?.*?h=([A-Za-z0-9]+))?~', $url, $m)) {
            return ['provider' => 'vimeo', 'id' => $m[1], 'hash' => ($m[2] ?? null) ?: ($m[3] ?? null)];
        }

        return [];
    }

    public static function embedUrl(?string $provider, ?string $id, ?string $hash = null): ?string
    {
        if (! $provider || ! $id) {
            return null;
        }

        return match ($provider) {
            'gdrive' => "https://drive.google.com/file/d/{$id}/preview",
            'youtube' => "https://www.youtube-nocookie.com/embed/{$id}?rel=0&modestbranding=1",
            'vimeo' => "https://player.vimeo.com/video/{$id}".($hash ? "?h={$hash}" : ''),
            default => null,
        };
    }

    public static function thumbnail(?string $provider, ?string $id): ?string
    {
        return match ($provider) {
            'youtube' => "https://i.ytimg.com/vi/{$id}/hqdefault.jpg",
            'gdrive' => "https://drive.google.com/thumbnail?id={$id}&sz=w640",
            default => null,
        };
    }
}
