<?php

namespace App\Modelos;

use Illuminate\Database\Eloquent\Model;

/** Ajustes generales que el superadmin edita desde el CMS */
class Modelo_Configuracion extends Model
{
    public const Clave_Texto_Marca_Agua = 'texto_marca_agua';

    protected $table = 'configuraciones';

    protected $fillable = ['clave', 'etiqueta', 'valor'];

    /** Texto de la marca de agua de los PDFs; acepta {usuario}, {institucion} y {fecha} para saber de quién es cada copia */
    public static function Armar_Texto_Marca_Agua(Modelo_Usuario $Usuario): string
    {
        $Plantilla = static::Obtener_Valor(self::Clave_Texto_Marca_Agua, 'Confidencial · {institucion}');

        return strtr($Plantilla, [
            '{usuario}' => $Usuario->nombre,
            '{institucion}' => $Usuario->Institucion?->nombre ?? '',
            '{fecha}' => now()->format('d/m/Y'),
        ]);
    }

    public static function Obtener_Valor(string $Clave, ?string $Por_Defecto = null): ?string
    {
        return static::where('clave', $Clave)->value('valor') ?? $Por_Defecto;
    }
}
