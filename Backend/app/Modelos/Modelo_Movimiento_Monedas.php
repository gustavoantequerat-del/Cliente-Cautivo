<?php

namespace App\Modelos;

use Illuminate\Database\Eloquent\Model;

/** Libro de monedas de cada usuario: el saldo es la suma de sus movimientos */
class Modelo_Movimiento_Monedas extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'movimientos_monedas';

    protected $fillable = ['usuario_id', 'cantidad', 'motivo', 'tipo_origen', 'origen_id'];

    /** Da monedas una sola vez por origen (un video, un reporte). Devuelve si se otorgaron ahora. */
    public static function Otorgar_Una_Vez(Modelo_Usuario $Usuario, int $Cantidad, string $Tipo_Origen, int $Origen_Id, string $Motivo): bool
    {
        if ($Cantidad <= 0) {
            return false;
        }

        return static::firstOrCreate(
            ['usuario_id' => $Usuario->id, 'tipo_origen' => $Tipo_Origen, 'origen_id' => $Origen_Id],
            ['cantidad' => $Cantidad, 'motivo' => $Motivo]
        )->wasRecentlyCreated;
    }

    public static function Calcular_Saldo(int $Usuario_Id): int
    {
        return (int) static::where('usuario_id', $Usuario_Id)->sum('cantidad');
    }
}
