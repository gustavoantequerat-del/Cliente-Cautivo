<?php

namespace App\Modelos;

use Illuminate\Database\Eloquent\Model;

/** Ajustes generales que el superadmin edita desde el CMS */
class Modelo_Configuracion extends Model
{
    public const Clave_Texto_Marca_Agua = 'texto_marca_agua';

    protected $table = 'configuraciones';

    protected $fillable = ['clave', 'etiqueta', 'valor'];

    public static function Obtener_Valor(string $Clave, ?string $Por_Defecto = null): ?string
    {
        return static::where('clave', $Clave)->value('valor') ?? $Por_Defecto;
    }
}
