<?php

namespace App\Modelos;

use App\Modelos\Rasgos\Rasgo_Exclusivo_De_Institucion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Modelo_Contrato extends Model
{
    use Rasgo_Exclusivo_De_Institucion;

    protected $table = 'contratos';

    protected $fillable = [
        'institucion_id', 'unidad_negocio_id', 'tipo', 'titulo', 'fecha_firma', 'fecha_vencimiento', 'monto',
        'ruta_archivo', 'nombre_archivo', 'enlace_externo', 'huella_sha256',
    ];

    protected function casts(): array
    {
        return ['fecha_firma' => 'date', 'fecha_vencimiento' => 'date', 'monto' => 'decimal:2'];
    }

    public function Unidad_Negocio(): BelongsTo
    {
        return $this->belongsTo(Modelo_Unidad_Negocio::class, 'unidad_negocio_id');
    }
}
