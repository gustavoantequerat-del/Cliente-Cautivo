<?php

namespace App\Modelos;

use App\Modelos\Rasgos\Rasgo_Exclusivo_De_Institucion;
use Illuminate\Database\Eloquent\Model;

class Modelo_Factura extends Model
{
    use Rasgo_Exclusivo_De_Institucion;

    protected $table = 'facturas';

    protected $fillable = [
        'institucion_id', 'numero', 'concepto', 'fecha_emision', 'fecha_vencimiento', 'monto', 'moneda', 'estado',
        'fecha_pago', 'ruta_pdf', 'ruta_xml',
    ];

    protected function casts(): array
    {
        return ['fecha_emision' => 'date', 'fecha_vencimiento' => 'date', 'fecha_pago' => 'date', 'monto' => 'decimal:2'];
    }

    /** Estado que ve el cliente: "vencida" se calcula, no se guarda */
    public function Obtener_Estado_Visible(): string
    {
        $Ya_Vencio = $this->fecha_vencimiento && $this->fecha_vencimiento->isPast() && ! $this->fecha_vencimiento->isToday();

        return $this->estado === 'pendiente' && $Ya_Vencio ? 'vencida' : $this->estado;
    }
}
