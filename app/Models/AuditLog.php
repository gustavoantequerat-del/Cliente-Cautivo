<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'institution_id', 'action', 'subject_type', 'subject_id', 'description', 'meta', 'ip',
    ];

    protected function casts(): array
    {
        return ['meta' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Registra un evento usando el usuario y la IP de la petición actual */
    public static function record(string $action, ?Model $subject = null, ?string $description = null, array $meta = []): self
    {
        $user = auth()->user();

        return static::create([
            'user_id' => $user?->id,
            'institution_id' => $subject?->institution_id ?? $user?->institution_id,
            'action' => $action,
            'subject_type' => $subject ? class_basename($subject) : null,
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'meta' => $meta ?: null,
            'ip' => request()->ip(),
        ]);
    }
}
