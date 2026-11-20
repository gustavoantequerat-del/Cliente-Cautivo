<?php

namespace App\Controladores\Portal;

use App\Modelos\Modelo_Inventario_Avatar;
use App\Modelos\Modelo_Item_Avatar;
use App\Modelos\Modelo_Movimiento_Monedas;
use App\Modelos\Modelo_Registro_Auditoria;
use App\Modelos\Modelo_Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * "Mi avatar": las monedas que se ganan con webinars y reportes se gastan en objetos de la Forja.
 */
class Controlador_Avatar
{
    public function Mostrar_Avatar(Request $Peticion): Response
    {
        $Usuario = $Peticion->user();
        $Inventario = Modelo_Inventario_Avatar::where('usuario_id', $Usuario->id)->get()->keyBy('item_avatar_id');

        return Inertia::render('Portal/Pagina_Avatar', [
            'Saldo_Monedas' => $Usuario->Calcular_Saldo_Monedas(),
            'Objetos_Equipados' => $Usuario->Obtener_Objetos_Equipados(),
            'Tipos' => Modelo_Item_Avatar::Tipos,
            'Items' => Modelo_Item_Avatar::where('esta_publicado', true)->orderBy('precio')->get()
                ->map(fn (Modelo_Item_Avatar $Item) => [
                    'Id' => $Item->id,
                    'Nombre' => $Item->nombre,
                    'Detalle' => $Item->detalle,
                    'Precio' => $Item->precio,
                    'Es_Propio' => $Inventario->has($Item->id),
                    'Esta_Equipado' => (bool) $Inventario->get($Item->id)?->esta_equipado,
                ] + $Item->Formatear_Para_Dibujo()),
            'Ultimas_Monedas' => Modelo_Movimiento_Monedas::where('usuario_id', $Usuario->id)->latest('created_at')->limit(6)->get()
                ->map(fn (Modelo_Movimiento_Monedas $Movimiento) => [
                    'Motivo' => $Movimiento->motivo,
                    'Cantidad' => $Movimiento->cantidad,
                    'Fecha' => $Movimiento->created_at->translatedFormat('j M'),
                ]),
        ]);
    }

    public function Comprar_Item(Request $Peticion, Modelo_Item_Avatar $Item): RedirectResponse
    {
        abort_unless($Item->esta_publicado, 404);
        $Usuario = $Peticion->user();

        $Resultado = DB::transaction(function () use ($Usuario, $Item) {
            // Bloquea al usuario para que dos compras simultáneas no gasten el mismo saldo
            Modelo_Usuario::whereKey($Usuario->id)->lockForUpdate()->first();
            if (Modelo_Inventario_Avatar::where('usuario_id', $Usuario->id)->where('item_avatar_id', $Item->id)->exists()) {
                return 'Ya tiene este objeto.';
            }
            if (Modelo_Movimiento_Monedas::Calcular_Saldo($Usuario->id) < $Item->precio) {
                return 'Le faltan monedas. Gane más viendo webinars y leyendo reportes.';
            }
            Modelo_Movimiento_Monedas::create([
                'usuario_id' => $Usuario->id, 'cantidad' => -$Item->precio, 'motivo' => 'Compró «'.$Item->nombre.'»',
                'tipo_origen' => 'item', 'origen_id' => $Item->id,
            ]);
            Modelo_Inventario_Avatar::create(['usuario_id' => $Usuario->id, 'item_avatar_id' => $Item->id]);

            return null;
        });

        if ($Resultado) {
            return back()->with('Mensaje_Error', $Resultado);
        }
        Modelo_Registro_Auditoria::Registrar_Evento('avatar.comprar', null, 'Compró «'.$Item->nombre.'» por '.$Item->precio.' monedas');

        return back()->with('Mensaje_Exito', "¡Nuevo objeto! «{$Item->nombre}» ya es suyo.");
    }

    /** Pone o quita un objeto. Solo uno por tipo, salvo los extras (máximo 2, uno por mano). */
    public function Equipar_Item(Request $Peticion, Modelo_Item_Avatar $Item): RedirectResponse
    {
        $Usuario = $Peticion->user();
        $Inventario = Modelo_Inventario_Avatar::where('usuario_id', $Usuario->id)->where('item_avatar_id', $Item->id)->firstOrFail();

        if ($Inventario->esta_equipado) {
            $Inventario->update(['esta_equipado' => false]);

            return back();
        }

        $Equipados_Del_Mismo_Tipo = Modelo_Inventario_Avatar::where('usuario_id', $Usuario->id)->where('esta_equipado', true)
            ->whereHas('Item', fn ($Consulta) => $Consulta->where('tipo', $Item->tipo))->get();
        $Maximo = Modelo_Item_Avatar::Maximo_Equipados[$Item->tipo] ?? 1;
        if ($Maximo === 1) {
            $Equipados_Del_Mismo_Tipo->each->update(['esta_equipado' => false]);
        } elseif ($Equipados_Del_Mismo_Tipo->count() >= $Maximo) {
            return back()->with('Mensaje_Error', "Solo puede llevar {$Maximo} objetos en las manos. Quítese uno primero.");
        }
        $Inventario->update(['esta_equipado' => true]);

        return back();
    }
}
