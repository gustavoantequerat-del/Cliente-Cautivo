<?php

namespace App\Models;

use App\Models\Concerns\SharedOrExclusive;
use Illuminate\Database\Eloquent\Model;

class ResearchReport extends Model
{
    use SharedOrExclusive;

    protected $fillable = [
        'institution_id', 'title', 'summary', 'topic', 'author', 'reading_minutes', 'file_path',
        'file_name', 'external_url', 'is_featured', 'is_published', 'published_on',
    ];

    protected function casts(): array
    {
        return ['is_featured' => 'boolean', 'is_published' => 'boolean', 'published_on' => 'date'];
    }
}
