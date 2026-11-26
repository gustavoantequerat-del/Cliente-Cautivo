<?php

namespace App\Modelos;

use App\Modelos\Rasgos\Rasgo_Exclusivo_De_Institucion;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Modelo_Entregable extends Model
{
    use Rasgo_Exclusivo_De_Institucion;

    public const Tipos = [
        'informe' => 'Informe', 'presentacion' => 'Presentación', 'auditoria' => 'Auditoría',
        'plantilla' => 'Plantilla', 'otro' => 'Otro',
    ];

    protected $table = 'entregables';

    protected $fillable = [
        'institucion_id', 'unidad_negocio_id', 'titulo', 'tipo', 'version', 'etiqueta_fase',
        'ruta_archivo', 'nombre_archivo', 'tamano_archivo', 'enlace_externo', 'codigo_verificacion', 'estado',
        'fecha_publicacion',
    ];

    protected function casts(): array
    {
        return ['fecha_aprobacion' => 'datetime', 'fecha_publicacion' => 'datetime'];
    }

    public function Unidad_Negocio(): BelongsTo
    {
        return $this->belongsTo(Modelo_Unidad_Negocio::class, 'unidad_negocio_id');
    }

    public function Aprobador(): BelongsTo
    {
        return $this->belongsTo(Modelo_Usuario::class, 'aprobado_por');
    }

    /** Los borradores no se muestran al cliente */
    #[Scope]
    protected function Visibles_Para_Cliente(Builder $Consulta): void
    {
        $Consulta->where('estado', '!=', 'borrador');
    }

    public function Esta_Pendiente_De_Aprobacion(): bool
    {
        return $this->estado === 'pendiente_aprobacion';
    }
}
