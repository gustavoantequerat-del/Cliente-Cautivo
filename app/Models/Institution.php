<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Institution extends Model
{
    protected $fillable = [
        'name', 'slug', 'legal_name', 'tax_id', 'billing_email', 'billing_address',
        'alliance_points', 'account_manager_id', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'alliance_points' => 'integer'];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function accountManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'account_manager_id');
    }

    public function units(): BelongsToMany
    {
        return $this->belongsToMany(BusinessUnit::class, 'institution_unit')
            ->withPivot(['active_since', 'note'])->withTimestamps();
    }

    public function hasUnit(string $code): bool
    {
        return $this->units->contains(fn ($u) => $u->code === $code);
    }

    public function hourMovements(): HasMany
    {
        return $this->hasMany(HourMovement::class);
    }

    public function hourBalance(): float
    {
        return round((float) $this->hourMovements()->sum('hours'), 2);
    }

    /** Nivel de alianza actual y siguiente según config/portal.php */
    public function level(): array
    {
        $levels = config('portal.levels');
        $current = $levels[0];
        $next = null;
        foreach ($levels as $i => $level) {
            if ($this->alliance_points >= $level['points']) {
                $current = $level;
                $next = $levels[$i + 1] ?? null;
            }
        }

        return ['current' => $current, 'next' => $next, 'points' => $this->alliance_points];
    }
}
