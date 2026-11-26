<?php

namespace App\Modelos;

use App\Modelos\Rasgos\Rasgo_Exclusivo_De_Institucion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Certificado de una persona. Lo sube el personal de Blockfinity desde el CMS;
 * el puntaje es la calificación que obtuvo la persona.
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
    }

    public function Usuario(): BelongsTo
    {
        return $this->belongsTo(Modelo_Usuario::class, 'usuario_id');
    }
}
