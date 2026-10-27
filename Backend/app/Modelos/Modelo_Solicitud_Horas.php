<?php

namespace App\Modelos;

use App\Modelos\Rasgos\Rasgo_Exclusivo_De_Institucion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Modelo_Solicitud_Horas extends Model
{
    use Rasgo_Exclusivo_De_Institucion;

    public const Estados = [
        'pendiente' => 'Recibida', 'en_atencion' => 'En atención', 'atendida' => 'Atendida', 'rechazada' => 'Rechazada',
    ];

    public const Modalidades = ['consulta', 'emergencia'];

    public const Especialidades = ['Cumplimiento y antilavado', 'Regulación internacional', 'Tecnología y custodia', 'Mercado y datos'];

    protected $table = 'solicitudes_horas';

    protected $fillable = [
        'institucion_id', 'usuario_id', 'modalidad', 'especialidad', 'horas_estimadas', 'descripcion', 'estado',
        'horas_cobradas', 'respuesta', 'atendido_por', 'fecha_resolucion',
    ];

    protected function casts(): array
    {
        return ['horas_estimadas' => 'decimal:2', 'horas_cobradas' => 'decimal:2', 'fecha_resolucion' => 'datetime'];
    }

    public function Usuario(): BelongsTo
    {
        return $this->belongsTo(Modelo_Usuario::class, 'usuario_id');
    }
}
