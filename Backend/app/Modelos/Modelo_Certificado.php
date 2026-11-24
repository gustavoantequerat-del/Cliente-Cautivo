<?php

namespace App\Modelos;

use App\Modelos\Rasgos\Rasgo_Exclusivo_De_Institucion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Certificado de una persona. Lo sube el personal de Blockfinity desde el CMS;
 * al crearse, la persona gana su puntaje en monedas para el avatar (una sola vez).
 */
class Modelo_Certificado extends Model
{
    use Rasgo_Exclusivo_De_Institucion;

    protected $table = 'certificados';

    protected $fillable = ['usuario_id', 'titulo', 'descripcion', 'puntaje', 'ruta_archivo', 'nombre_archivo', 'codigo_verificacion'];

    protected function casts(): array
    {
        return ['puntaje' => 'integer'];
    }

    protected static function booted(): void
    {
        // La institución es siempre la de la persona (sirve para que el usuario ejecutivo solo vea las suyas)
        static::saving(fn (Modelo_Certificado $Certificado) => $Certificado->institucion_id = Modelo_Usuario::whereKey($Certificado->usuario_id)->value('institucion_id'));
        static::created(fn (Modelo_Certificado $Certificado) => Modelo_Movimiento_Monedas::Otorgar_Una_Vez(
            $Certificado->Usuario, $Certificado->puntaje, 'certificado', $Certificado->id, 'Certificado «'.$Certificado->titulo.'»'
        ));
    }

    public function Usuario(): BelongsTo
    {
        return $this->belongsTo(Modelo_Usuario::class, 'usuario_id');
    }
}
