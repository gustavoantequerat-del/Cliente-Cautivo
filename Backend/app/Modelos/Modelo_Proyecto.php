<?php

namespace App\Modelos;

use App\Modelos\Rasgos\Rasgo_Exclusivo_De_Institucion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Modelo_Proyecto extends Model
{
    use Rasgo_Exclusivo_De_Institucion;

    protected $table = 'proyectos';

    protected $fillable = [
        'institucion_id', 'unidad_negocio_id', 'nombre', 'descripcion', 'estado', 'avance',
        'proximo_hito', 'fecha_inicio', 'fecha_fin',
    ];

    protected function casts(): array
    {
        return ['fecha_inicio' => 'date', 'fecha_fin' => 'date', 'avance' => 'integer'];
    }

    public function Unidad_Negocio(): BelongsTo
    {
        return $this->belongsTo(Modelo_Unidad_Negocio::class, 'unidad_negocio_id');
    }

    public function Fases(): HasMany
    {
        return $this->hasMany(Modelo_Fase_Proyecto::class, 'proyecto_id')->orderBy('posicion');
    }

    public function Bitacora(): HasMany
    {
        return $this->hasMany(Modelo_Bitacora_Proyecto::class, 'proyecto_id')->orderByDesc('fecha')->orderByDesc('id');
    }

    public function Entregables(): HasMany
    {
        return $this->hasMany(Modelo_Entregable::class, 'proyecto_id');
    }
}
