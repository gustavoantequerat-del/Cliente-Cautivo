<?php

namespace App\Modelos;

use Illuminate\Database\Eloquent\Model;

class Modelo_Bitacora_Proyecto extends Model
{
    protected $table = 'bitacora_proyectos';

    protected $fillable = ['proyecto_id', 'usuario_id', 'detalle', 'fecha'];

    protected function casts(): array
    {
        return ['fecha' => 'date'];
    }
}
