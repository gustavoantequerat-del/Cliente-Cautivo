<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectPhase extends Model
{
    protected $fillable = ['project_id', 'position', 'name', 'status', 'due_on'];

    protected function casts(): array
    {
        return ['due_on' => 'date'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
