<?php

namespace App\Controladores\Portal;

use App\Modelos\Modelo_Entregable;
use App\Modelos\Modelo_Proyecto;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class Controlador_Proyectos
{
    public function Listar_Proyectos(Request $Peticion): Response
    {
        $Lista_Proyectos = Modelo_Proyecto::Visibles_Para_Usuario($Peticion->user())->with(['Unidad_Negocio', 'Fases'])
            ->orderByRaw("case when estado = 'activo' then 0 else 1 end")->latest()->get()
            ->map(function (Modelo_Proyecto $Proyecto) {
                $Posicion_Fase_Actual = $Proyecto->Fases->search(fn ($Fase) => $Fase->estado === 'en_curso');

                return [
                    'Id' => $Proyecto->id,
                    'Nombre' => $Proyecto->nombre,
                    'Codigo_Unidad' => $Proyecto->Unidad_Negocio?->codigo,
                    'Estado' => $Proyecto->estado,
                    'Avance' => $Proyecto->avance,
                    'Fase_Actual' => $Proyecto->Fases->firstWhere('estado', 'en_curso')?->nombre,
                    'Numero_Fase_Actual' => $Posicion_Fase_Actual === false ? null : $Posicion_Fase_Actual + 1,
                    'Cantidad_Fases' => $Proyecto->Fases->count(),
                    'Proximo_Hito' => $Proyecto->proximo_hito,
                ];
            });

        return Inertia::render('Portal/Pagina_Proyectos', ['Lista_Proyectos' => $Lista_Proyectos]);
    }

    public function Mostrar_Proyecto(Request $Peticion, Modelo_Proyecto $Proyecto): Response
    {
        abort_unless($Proyecto->institucion_id == $Peticion->user()->institucion_id, 404);
        $Proyecto->load(['Unidad_Negocio', 'Fases', 'Bitacora']);

        $Entregables = $Proyecto->Entregables()->Visibles_Para_Cliente()->with('Aprobador')->orderBy('created_at')->get();

        return Inertia::render('Portal/Pagina_Detalle_Proyecto', [
            'Proyecto' => [
                'Nombre' => $Proyecto->nombre,
                'Descripcion' => $Proyecto->descripcion,
                'Codigo_Unidad' => $Proyecto->Unidad_Negocio?->codigo,
                'Estado' => $Proyecto->estado,
                'Fecha_Inicio' => $Proyecto->fecha_inicio?->translatedFormat('j M Y'),
                'Fecha_Fin' => $Proyecto->fecha_fin?->translatedFormat('j M Y'),
                'Fases' => $Proyecto->Fases->map(fn ($Fase) => [
                    'Posicion' => $Fase->posicion,
                    'Nombre' => $Fase->nombre,
                    'Estado' => $Fase->estado,
                    'Fecha_Limite' => $Fase->fecha_limite?->translatedFormat('j M'),
                ]),
                'Bitacora' => $Proyecto->Bitacora->take(15)->map(fn ($Entrada) => [
                    'Fecha' => $Entrada->fecha->translatedFormat('j M'),
                    'Detalle' => $Entrada->detalle,
                ]),
            ],
            'Entregables' => $Entregables->map(fn (Modelo_Entregable $Entregable) => Controlador_Entregables::Formatear_Entregable($Entregable)),
        ]);
    }
}
