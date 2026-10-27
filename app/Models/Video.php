<?php

namespace App\Models;

use App\Models\Concerns\SharedOrExclusive;
use App\Support\VideoEmbed;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Video extends Model
{
    use SharedOrExclusive;

    protected $fillable = [
        'institution_id', 'kind', 'title', 'description', 'topic', 'speaker', 'duration_minutes',
        'source_url', 'thumbnail_url', 'scheduled_at', 'live_url', 'points', 'is_published',
    ];

    protected function casts(): array
    {
        return ['scheduled_at' => 'datetime', 'is_published' => 'boolean'];
    }

    protected static function booted(): void
    {
        // Al guardar el enlace pegado en el CMS, se extrae proveedor + ID. Nunca se guarda HTML.
        static::saving(function (Video $video) {
            if ($video->isDirty('source_url')) {
                $parsed = VideoEmbed::parse($video->source_url);
                $video->provider = $parsed['provider'] ?? null;
                $video->external_id = $parsed['id'] ?? null;
                $video->external_hash = $parsed['hash'] ?? null;
            }
        });
    }

    public function progress(): HasMany
    {
        return $this->hasMany(VideoProgress::class);
    }

    public function embedUrl(): ?string
    {
        return VideoEmbed::embedUrl($this->provider, $this->external_id, $this->external_hash);
    }
}
