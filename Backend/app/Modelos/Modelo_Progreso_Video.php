<?php

namespace App\Modelos;

use Illuminate\Database\Eloquent\Model;

/** Progreso declarado por el usuario (los iframes de Drive no permiten medir la reproducción) */
class Modelo_Progreso_Video extends Model
{
    protected $table = 'progreso_videos';

    protected $fillable = ['video_id', 'usuario_id', 'fecha_inscripcion', 'fecha_completado'];

    protected function casts(): array
    {
        return ['fecha_inscripcion' => 'datetime', 'fecha_completado' => 'datetime'];
    }
}
