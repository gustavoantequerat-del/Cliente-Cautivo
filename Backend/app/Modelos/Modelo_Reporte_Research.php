<?php

namespace App\Modelos;

use App\Modelos\Rasgos\Rasgo_General_O_Exclusivo;
use Illuminate\Database\Eloquent\Model;

class Modelo_Reporte_Research extends Model
{
    use Rasgo_General_O_Exclusivo;

    public const Temas = ['Mercado P2P', 'Regulación', 'Adopción institucional', 'Stablecoins', 'Tokenización'];

    protected $table = 'reportes_research';

    protected $fillable = [
        'institucion_id', 'titulo', 'resumen', 'tema', 'autor', 'minutos_lectura', 'ruta_archivo',
        'nombre_archivo', 'enlace_externo', 'origen_documento', 'es_destacado', 'esta_publicado', 'fecha_publicacion',
    ];

    protected function casts(): array
    {
        return ['es_destacado' => 'boolean', 'esta_publicado' => 'boolean', 'fecha_publicacion' => 'date'];
    }
}
