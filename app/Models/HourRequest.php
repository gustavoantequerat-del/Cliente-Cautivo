<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HourRequest extends Model
{
    use BelongsToInstitution;

    protected $fillable = [
        'institution_id', 'user_id', 'mode', 'specialty', 'estimated_hours', 'description', 'status',
        'charged_hours', 'response', 'handled_by', 'resolved_at',
    ];

    protected function casts(): array
    {
        return ['estimated_hours' => 'decimal:2', 'charged_hours' => 'decimal:2', 'resolved_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
