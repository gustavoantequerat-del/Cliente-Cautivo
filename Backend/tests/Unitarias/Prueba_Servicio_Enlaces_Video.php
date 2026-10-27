<?php

namespace Tests\Unitarias;

use App\Servicios\Servicio_Enlaces_Video;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class Prueba_Servicio_Enlaces_Video extends TestCase
{
    public static function Enlaces_Soportados(): array
    {
        return [
            'drive view' => ['https://drive.google.com/file/d/1AbCdEfGhIjKlMnOpQrStUv/view?usp=sharing', 'drive', '1AbCdEfGhIjKlMnOpQrStUv', null],
            'drive open' => ['https://drive.google.com/open?id=1AbCdEfGhIjKlMnOpQrStUv', 'drive', '1AbCdEfGhIjKlMnOpQrStUv', null],
            'youtube watch' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=10', 'youtube', 'dQw4w9WgXcQ', null],
            'youtu.be' => ['https://youtu.be/dQw4w9WgXcQ', 'youtube', 'dQw4w9WgXcQ', null],
            'youtube live' => ['https://www.youtube.com/live/dQw4w9WgXcQ', 'youtube', 'dQw4w9WgXcQ', null],
            'vimeo' => ['https://vimeo.com/123456789', 'vimeo', '123456789', null],
            'vimeo no listado' => ['https://vimeo.com/123456789/abcdef1234', 'vimeo', '123456789', 'abcdef1234'],
        ];
    }

    #[Test]
    #[DataProvider('Enlaces_Soportados')]
    public function Reconoce_Enlaces_Soportados(string $Enlace, string $Proveedor, string $Id, ?string $Hash): void
    {
        $Datos_Enlace = Servicio_Enlaces_Video::Analizar_Enlace($Enlace);
        $this->assertSame($Proveedor, $Datos_Enlace['Proveedor']);
        $this->assertSame($Id, $Datos_Enlace['Id']);
        $this->assertSame($Hash, $Datos_Enlace['Hash'] ?? null);
    }

    #[Test]
    public function Rechaza_Enlaces_Desconocidos_Y_Construye_Url_Embebida(): void
    {
        $this->assertSame([], Servicio_Enlaces_Video::Analizar_Enlace('https://example.com/video.mp4'));
        $this->assertSame([], Servicio_Enlaces_Video::Analizar_Enlace('<iframe src="x"></iframe>'));
        $this->assertSame('https://drive.google.com/file/d/ABC1234567/preview', Servicio_Enlaces_Video::Construir_Url_Embebida('drive', 'ABC1234567'));
        $this->assertSame('https://player.vimeo.com/video/1?h=zz', Servicio_Enlaces_Video::Construir_Url_Embebida('vimeo', '1', 'zz'));
        $this->assertNull(Servicio_Enlaces_Video::Construir_Url_Embebida(null, null));
    }
}
