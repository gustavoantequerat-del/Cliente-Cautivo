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
        'puntos_alianza', 'ejecutivo_cuenta_id', 'esta_activa',
    ];

    protected function casts(): array
    {
        return ['esta_activa' => 'boolean', 'puntos_alianza' => 'integer'];
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

    /** Nivel de alianza actual y siguiente según config/Configuracion_Portal.php */
    public function Calcular_Nivel_Alianza(): array
    {
        $Niveles = config('Configuracion_Portal.Niveles_Alianza');
        $Nivel_Actual = $Niveles[0];
        $Nivel_Siguiente = null;
        foreach ($Niveles as $Posicion => $Nivel) {
            if ($this->puntos_alianza >= $Nivel['Puntos_Minimos']) {
                $Nivel_Actual = $Nivel;
                $Nivel_Siguiente = $Niveles[$Posicion + 1] ?? null;
            }
        }

        return ['Nivel_Actual' => $Nivel_Actual, 'Nivel_Siguiente' => $Nivel_Siguiente, 'Puntos' => $this->puntos_alianza];
    }
}
