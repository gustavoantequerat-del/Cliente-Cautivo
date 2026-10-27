<?php

namespace Tests\Unit;

use App\Support\VideoEmbed;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class VideoEmbedTest extends TestCase
{
    public static function links(): array
    {
        return [
            'drive view' => ['https://drive.google.com/file/d/1AbCdEfGhIjKlMnOpQrStUv/view?usp=sharing', 'gdrive', '1AbCdEfGhIjKlMnOpQrStUv', null],
            'drive open' => ['https://drive.google.com/open?id=1AbCdEfGhIjKlMnOpQrStUv', 'gdrive', '1AbCdEfGhIjKlMnOpQrStUv', null],
            'youtube watch' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=10', 'youtube', 'dQw4w9WgXcQ', null],
            'youtu.be' => ['https://youtu.be/dQw4w9WgXcQ', 'youtube', 'dQw4w9WgXcQ', null],
            'youtube live' => ['https://www.youtube.com/live/dQw4w9WgXcQ', 'youtube', 'dQw4w9WgXcQ', null],
            'vimeo' => ['https://vimeo.com/123456789', 'vimeo', '123456789', null],
            'vimeo unlisted' => ['https://vimeo.com/123456789/abcdef1234', 'vimeo', '123456789', 'abcdef1234'],
        ];
    }

    #[DataProvider('links')]
    public function test_parses_supported_links(string $url, string $provider, string $id, ?string $hash): void
    {
        $parsed = VideoEmbed::parse($url);
        $this->assertSame($provider, $parsed['provider']);
        $this->assertSame($id, $parsed['id']);
        $this->assertSame($hash, $parsed['hash'] ?? null);
    }

    public function test_rejects_unknown_links_and_builds_embed_urls(): void
    {
        $this->assertSame([], VideoEmbed::parse('https://example.com/video.mp4'));
        $this->assertSame([], VideoEmbed::parse('<iframe src="x"></iframe>'));
        $this->assertSame('https://drive.google.com/file/d/ABC1234567/preview', VideoEmbed::embedUrl('gdrive', 'ABC1234567'));
        $this->assertSame('https://player.vimeo.com/video/1?h=zz', VideoEmbed::embedUrl('vimeo', '1', 'zz'));
        $this->assertNull(VideoEmbed::embedUrl(null, null));
    }
}
