<?php

namespace App\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Modelo_Institucion extends Model
{
    protected $table = 'instituciones';

    protected $fillable = [
        'nombre', 'identificador', 'razon_social', 'nit', 'correo_facturacion', 'direccion_facturacion',
        'ejecutivo_cuenta_id', 'esta_activa', 'color_primario', 'color_secundario', 'ruta_logo', 'registro_abierto',
    ];

    protected function casts(): array
    {
        return ['esta_activa' => 'boolean', 'registro_abierto' => 'boolean'];
    }

    /** Cada institución nace con su link de registro para usuarios institucionales */
    protected static function booted(): void
    {
        static::creating(fn (Modelo_Institucion $Institucion) => $Institucion->codigo_registro ??= Str::random(32));
    }

    public function Obtener_Url_Registro(): string
    {
        return url('/registro/'.$this->codigo_registro);
    }

    /** El link anterior deja de funcionar */
    public function Regenerar_Codigo_Registro(): void
    {
        $this->codigo_registro = Str::random(32);
    }

    public function Ejecutivo_Cuenta(): BelongsTo
    {
        return $this->belongsTo(Modelo_Usuario::class, 'ejecutivo_cuenta_id');
    }

    public function Unidades_Contratadas(): BelongsToMany
    {
        return $this->belongsToMany(Modelo_Unidad_Negocio::class, 'instituciones_unidades', 'institucion_id', 'unidad_negocio_id')
            ->withPivot(['activa_desde', 'nota'])->withTimestamps();
    }

    public function Tiene_Unidad_Contratada(string $Codigo_Unidad): bool
    {
        return $this->Unidades_Contratadas->contains('codigo', $Codigo_Unidad);
    }

    public function Calcular_Saldo_Horas(): float
    {
        return round((float) Modelo_Movimiento_Horas::where('institucion_id', $this->id)->sum('horas'), 2);
    }

    /**
     * Nivel de alianza según cuántos servicios (unidades de negocio) tiene contratados.
     * Los requisitos están en config/Configuracion_Portal.php; null = todos los servicios.
     */
    /** Nivel según la cantidad de productos o servicios activos: 1 Explorador, 2 Aliado, 3 o más Premium */
    public function Calcular_Nivel_Alianza(): array
    {
        $Servicios_Contratados = $this->Unidades_Contratadas->count();
        $Niveles = config('Configuracion_Portal.Niveles_Alianza');

        $Posicion_Actual = 0;
        foreach ($Niveles as $Posicion => $Nivel) {
            if ($Servicios_Contratados >= $Nivel['Servicios_Minimos']) {
                $Posicion_Actual = $Posicion;
            }
        }
        $Nivel_Siguiente = $Niveles[$Posicion_Actual + 1] ?? null;

        return [
            'Nivel_Actual' => $Niveles[$Posicion_Actual],
            'Nivel_Siguiente' => $Nivel_Siguiente,
            'Servicios_Contratados' => $Servicios_Contratados,
            'Servicios_Para_Premium' => end($Niveles)['Servicios_Minimos'],
            'Servicios_Faltantes' => $Nivel_Siguiente ? $Nivel_Siguiente['Servicios_Minimos'] - $Servicios_Contratados : 0,
            'Niveles' => $Niveles,
        ];
    }
}
