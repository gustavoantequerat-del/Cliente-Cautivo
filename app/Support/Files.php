<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Archivos pequeños (PDF, XML) en el disco privado. Los pesados van como enlace externo.
 */
class Files
{
    public static function store(UploadedFile $file, string $folder): array
    {
        $path = $file->store($folder, 'local');

        return [
            'path' => $path,
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'sha256' => hash_file('sha256', $file->getRealPath()),
        ];
    }

    public static function download(string $path, ?string $name = null): StreamedResponse
    {
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, $name);
    }

    public static function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('local')->delete($path);
        }
    }
}
