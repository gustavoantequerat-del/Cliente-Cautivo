<?php

namespace App\Modelos;

use App\Modelos\Rasgos\Rasgo_Exclusivo_De_Institucion;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    /** Clientes básicos inscritos por el cliente institucional */
    public function Participantes(): BelongsToMany
    {
        return $this->belongsToMany(Modelo_Usuario::class, 'proyectos_participantes', 'proyecto_id', 'usuario_id')->withTimestamps();
    }

    /** El cliente institucional ve todos los proyectos de su institución; el básico, solo donde está inscrito */
    #[Scope]
    protected function Accesibles_Para_Usuario(Builder $Consulta, Modelo_Usuario $Usuario): void
    {
        $Consulta->where('proyectos.institucion_id', $Usuario->institucion_id);
        if (! $Usuario->Es_Cliente_Institucional()) {
            $Consulta->whereHas('Participantes', fn (Builder $Participante) => $Participante->where('usuarios.id', $Usuario->id));
        }
    }
}
