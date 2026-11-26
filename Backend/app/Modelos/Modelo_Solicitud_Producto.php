<?php

namespace App\Modelos;

use App\Modelos\Rasgos\Rasgo_Exclusivo_De_Institucion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Pedido de contratación de un producto del ecosistema; el usuario ejecutivo lo habilita en la institución */
class Modelo_Solicitud_Producto extends Model
{
    use Rasgo_Exclusivo_De_Institucion;

    public const Estados = ['pendiente' => 'Pendiente', 'habilitado' => 'Habilitado', 'descartado' => 'Descartado'];

    protected $table = 'solicitudes_productos';

    protected $fillable = ['institucion_id', 'unidad_negocio_id', 'usuario_id', 'comentario', 'estado'];

    public function Unidad_Negocio(): BelongsTo
    {
        return $this->belongsTo(Modelo_Unidad_Negocio::class, 'unidad_negocio_id');
    }

    public function Usuario(): BelongsTo
    {
        return $this->belongsTo(Modelo_Usuario::class, 'usuario_id');
    }
}
