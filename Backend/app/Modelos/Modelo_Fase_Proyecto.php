<?php

namespace App\Modelos;

use Illuminate\Database\Eloquent\Model;

class Modelo_Fase_Proyecto extends Model
{
    protected $table = 'fases_proyecto';

    protected $fillable = ['proyecto_id', 'posicion', 'nombre', 'estado', 'fecha_limite'];

    protected function casts(): array
    {
        return ['fecha_limite' => 'date'];
    }
}
