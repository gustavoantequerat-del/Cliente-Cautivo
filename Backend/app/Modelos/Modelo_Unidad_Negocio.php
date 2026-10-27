<?php

namespace App\Modelos;

use Illuminate\Database\Eloquent\Model;

class Modelo_Unidad_Negocio extends Model
{
    protected $table = 'unidades_negocio';

    protected $fillable = ['codigo', 'nombre', 'descripcion', 'orden'];
}
