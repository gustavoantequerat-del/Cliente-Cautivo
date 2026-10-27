<?php

namespace App\Models\Concerns;

use App\Models\Institution;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Contenido general (institution_id null) o exclusivo de una institución.
 */
trait SharedOrExclusive
{
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->where(function ($q) use ($user) {
            $q->whereNull($this->getTable().'.institution_id')
                ->orWhere($this->getTable().'.institution_id', $user->institution_id);
        });
    }
}
