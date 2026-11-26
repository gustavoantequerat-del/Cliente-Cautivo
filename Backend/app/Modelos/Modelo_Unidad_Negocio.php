<?php

namespace App\Modelos;

use App\Servicios\Servicio_Enlaces_Video;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Producto o servicio del Ecosistema Blockfinity. Los que tiene activos una institución
 * definen su nivel de alianza (ver Modelo_Institucion::Calcular_Nivel_Alianza).
 */
class Modelo_Unidad_Negocio extends Model
{
    private const Clave_Cache_Origenes = 'ecosistema.origenes_iframe';

    protected $table = 'unidades_negocio';

    protected $fillable = ['codigo', 'nombre', 'subtitulo', 'descripcion', 'orden', 'ruta_imagen', 'enlace_pagina', 'enlace_video', 'esta_publicado'];

    protected function casts(): array
    {
        return ['esta_publicado' => 'boolean'];
    }

    protected static function booted(): void
    {
        // La CSP permite incrustar solo las páginas de los productos: se recalcula al cambiar alguno
        static::saved(fn () => Cache::forget(self::Clave_Cache_Origenes));
        static::deleted(fn () => Cache::forget(self::Clave_Cache_Origenes));
    }

    /** Página del producto (iframe) si existe; si no, el video explicativo */
    public function Obtener_Tipo_Vista(): ?string
    {
        return match (true) {
            (bool) $this->enlace_pagina => 'pagina',
            (bool) $this->Obtener_Url_Video() => 'video',
            default => null,
        };
    }

    public function Obtener_Url_Video(): ?string
    {
        $Video = Servicio_Enlaces_Video::Analizar_Enlace($this->enlace_video);

        return Servicio_Enlaces_Video::Construir_Url_Embebida($Video['Proveedor'] ?? null, $Video['Id'] ?? null, $Video['Hash'] ?? null);
    }

    /** Orígenes (https://dominio) de las páginas de producto, para la cabecera frame-src */
    public static function Obtener_Origenes_Iframe(): array
    {
        return Cache::rememberForever(self::Clave_Cache_Origenes, fn () => static::whereNotNull('enlace_pagina')->pluck('enlace_pagina')
            ->map(function (string $Enlace) {
                $Partes = parse_url($Enlace);

                return ($Partes['scheme'] ?? null) === 'https' && isset($Partes['host'])
                    ? 'https://'.$Partes['host'].(isset($Partes['port']) ? ':'.$Partes['port'] : '') : null;
            })->filter()->unique()->values()->all());
    }
}
