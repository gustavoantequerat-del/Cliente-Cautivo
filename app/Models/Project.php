<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use BelongsToInstitution;

    protected $fillable = [
        'institution_id', 'business_unit_id', 'name', 'description', 'status', 'progress',
        'next_milestone', 'starts_on', 'ends_on',
    ];

    protected function casts(): array
    {
        return ['starts_on' => 'date', 'ends_on' => 'date', 'progress' => 'integer'];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class, 'business_unit_id');
    }

    public function phases(): HasMany
    {
        return $this->hasMany(ProjectPhase::class)->orderBy('position');
    }

    public function updates(): HasMany
    {
        return $this->hasMany(ProjectUpdate::class)->orderByDesc('happened_on')->orderByDesc('id');
    }

    public function deliverables(): HasMany
    {
        return $this->hasMany(Deliverable::class);
    }
}
