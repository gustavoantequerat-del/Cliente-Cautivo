<?php

namespace App\Modelos;

use App\Servicios\Servicio_Enlaces_Video;
use Illuminate\Database\Eloquent\Model;

/**
 * Producto o servicio del Ecosistema Blockfinity. Los que tiene activos una institución
 * definen su nivel de alianza (ver Modelo_Institucion::Calcular_Nivel_Alianza).
 */
class Modelo_Unidad_Negocio extends Model
{
    protected $table = 'unidades_negocio';

    protected $fillable = ['codigo', 'nombre', 'subtitulo', 'descripcion', 'orden', 'ruta_imagen', 'enlace_pagina', 'enlace_video', 'esta_publicado'];

    protected function casts(): array
    {
        return ['esta_publicado' => 'boolean'];
    }

    public function Obtener_Url_Video(): ?string
    {
        $Video = Servicio_Enlaces_Video::Analizar_Enlace($this->enlace_video);

        return Servicio_Enlaces_Video::Construir_Url_Embebida($Video['Proveedor'] ?? null, $Video['Id'] ?? null, $Video['Hash'] ?? null);
    }
}
