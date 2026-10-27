<?php

namespace App\Models\Concerns;

use App\Models\Institution;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Aísla los datos por institución: un cliente solo ve lo de su entidad.
 */
trait BelongsToInstitution
{
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->where($this->getTable().'.institution_id', $user->institution_id);
    }
}
