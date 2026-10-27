<?php

namespace App\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Modelo_Registro_Auditoria extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'registros_auditoria';

    protected $fillable = [
        'usuario_id', 'institucion_id', 'accion', 'tipo_sujeto', 'sujeto_id', 'descripcion', 'datos_extra', 'ip',
    ];

    protected function casts(): array
    {
        return ['datos_extra' => 'array'];
    }

    public function Usuario(): BelongsTo
    {
        return $this->belongsTo(Modelo_Usuario::class, 'usuario_id');
    }

    /** Registra un evento con el usuario y la IP de la petición actual */
    public static function Registrar_Evento(string $Accion, ?Model $Sujeto = null, ?string $Descripcion = null, array $Datos_Extra = []): self
    {
        $Usuario_Actual = auth()->user();

        return static::create([
            'usuario_id' => $Usuario_Actual?->id,
            'institucion_id' => $Sujeto?->institucion_id ?? $Usuario_Actual?->institucion_id,
            'accion' => $Accion,
            'tipo_sujeto' => $Sujeto?->getTable(),
            'sujeto_id' => $Sujeto?->getKey(),
            'descripcion' => $Descripcion,
            'datos_extra' => $Datos_Extra ?: null,
            'ip' => request()->ip(),
        ]);
    }
}
