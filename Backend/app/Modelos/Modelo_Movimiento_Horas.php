<?php

namespace App\Modelos;

use App\Modelos\Rasgos\Rasgo_Exclusivo_De_Institucion;
use Illuminate\Database\Eloquent\Model;

/** Libro de la bolsa de horas: + abono, − consumo */
class Modelo_Movimiento_Horas extends Model
{
    use Rasgo_Exclusivo_De_Institucion;

    protected $table = 'movimientos_horas';

    protected $fillable = ['institucion_id', 'solicitud_horas_id', 'horas', 'descripcion', 'fecha_vencimiento', 'creado_por'];

    protected function casts(): array
    {
        return ['horas' => 'decimal:2', 'fecha_vencimiento' => 'date'];
    }
}
