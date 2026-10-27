<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoProgress extends Model
{
    protected $table = 'video_progress';

    protected $fillable = ['video_id', 'user_id', 'registered_at', 'completed_at'];

    protected function casts(): array
    {
        return ['registered_at' => 'datetime', 'completed_at' => 'datetime'];
    }
}
