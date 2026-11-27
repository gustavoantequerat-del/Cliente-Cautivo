<?php

namespace App\Modelos;

use App\Modelos\Rasgos\Rasgo_General_O_Exclusivo;
use App\Servicios\Servicio_Enlaces_Video;
use Illuminate\Database\Eloquent\Model;

class Modelo_Video extends Model
{
    use Rasgo_General_O_Exclusivo;

    public const Formatos = ['en_vivo' => 'En vivo', 'grabado' => 'Grabado', 'pildora' => 'Píldora'];

    public const Temas = ['Normativa', 'Metodologías', 'Herramientas'];

    protected $table = 'videos';

    protected $fillable = [
        'institucion_id', 'formato', 'titulo', 'descripcion', 'tema', 'expositor', 'duracion_minutos',
        'enlace_original', 'enlace_miniatura', 'fecha_programada', 'enlace_sala', 'esta_publicado',
        'tipo_contenido', 'origen_documento', 'ruta_archivo', 'nombre_archivo', 'enlace_externo',
    ];

    protected function casts(): array
    {
        return ['fecha_programada' => 'datetime', 'esta_publicado' => 'boolean'];
    }

    /** Al guardar el enlace pegado en el CMS se extrae proveedor + ID. Nunca se guarda HTML. */
    protected static function booted(): void
    {
        static::saving(function (Modelo_Video $Video) {
            if ($Video->isDirty('enlace_original')) {
                $Datos_Enlace = Servicio_Enlaces_Video::Analizar_Enlace($Video->enlace_original);
                $Video->proveedor = $Datos_Enlace['Proveedor'] ?? null;
                $Video->id_externo = $Datos_Enlace['Id'] ?? null;
                $Video->hash_externo = $Datos_Enlace['Hash'] ?? null;
            }
        });
    }

    /** Un webinar o píldora puede ser un PDF (se lee en el visor protegido) en lugar de un video */
    public function Es_Pdf(): bool
    {
        return $this->tipo_contenido === 'pdf';
    }

    public function Construir_Url_Embebida(): ?string
    {
        return Servicio_Enlaces_Video::Construir_Url_Embebida($this->proveedor, $this->id_externo, $this->hash_externo);
    }
}
