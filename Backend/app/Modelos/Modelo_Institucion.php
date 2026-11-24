<?php

namespace App\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Modelo_Institucion extends Model
{
    protected $table = 'instituciones';

    protected $fillable = [
        'nombre', 'identificador', 'razon_social', 'nit', 'correo_facturacion', 'direccion_facturacion',
        'ejecutivo_cuenta_id', 'esta_activa', 'color_primario', 'color_secundario', 'ruta_logo',
    ];

    protected function casts(): array
    {
        return ['esta_activa' => 'boolean'];
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
    public function Calcular_Nivel_Alianza(): array
    {
        $Servicios_Totales = Modelo_Unidad_Negocio::count();
        $Servicios_Contratados = $this->Unidades_Contratadas->count();
        $Niveles = array_map(fn ($Nivel) => $Nivel + ['Servicios_Requeridos' => $Nivel['Servicios_Minimos'] ?? $Servicios_Totales],
            config('Configuracion_Portal.Niveles_Alianza'));

        $Posicion_Actual = 0;
        foreach ($Niveles as $Posicion => $Nivel) {
            if ($Servicios_Contratados >= $Nivel['Servicios_Requeridos']) {
                $Posicion_Actual = $Posicion;
            }
        }

        return [
            'Nivel_Actual' => $Niveles[$Posicion_Actual],
            'Nivel_Siguiente' => $Niveles[$Posicion_Actual + 1] ?? null,
            'Servicios_Contratados' => $Servicios_Contratados,
            'Servicios_Totales' => $Servicios_Totales,
            'Niveles' => $Niveles,
        ];
    }
}
