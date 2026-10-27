<?php

namespace App\Controladores\Portal;

use App\Modelos\Modelo_Entregable;
use App\Modelos\Modelo_Factura;
use App\Modelos\Modelo_Proyecto;
use App\Modelos\Modelo_Reporte_Research;
use App\Modelos\Modelo_Unidad_Negocio;
use App\Modelos\Modelo_Video;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class Controlador_Inicio
{
    public function Mostrar_Inicio(Request $Peticion): Response
    {
        $Usuario = $Peticion->user();
        $Institucion = $Usuario->Institucion->load('Unidades_Contratadas');

        $Proyectos_Activos = Modelo_Proyecto::Visibles_Para_Usuario($Usuario)->where('estado', 'activo')
            ->with(['Unidad_Negocio', 'Fases'])->latest()->get()
            ->map(fn (Modelo_Proyecto $Proyecto) => [
                'Id' => $Proyecto->id,
                'Nombre' => $Proyecto->nombre,
                'Codigo_Unidad' => $Proyecto->Unidad_Negocio?->codigo,
                'Fase_Actual' => $Proyecto->Fases->firstWhere('estado', 'en_curso')?->nombre,
                'Avance' => $Proyecto->avance,
                'Proximo_Hito' => $Proyecto->proximo_hito,
            ]);

        $Reporte_Destacado = $Institucion->Tiene_Unidad_Contratada('RESEARCH')
            ? Modelo_Reporte_Research::Visibles_Para_Usuario($Usuario)->where('esta_publicado', true)
                ->orderByDesc('es_destacado')->orderByDesc('fecha_publicacion')->first()
            : null;

        $Proximo_Webinar = Modelo_Video::Visibles_Para_Usuario($Usuario)->where('esta_publicado', true)->where('formato', 'en_vivo')
            ->where('fecha_programada', '>=', now())->orderBy('fecha_programada')->first();

        $Facturas_Pendientes = Modelo_Factura::Visibles_Para_Usuario($Usuario)->where('estado', 'pendiente')->orderBy('fecha_vencimiento')->get();
        $Proxima_Factura = $Facturas_Pendientes->first();

        $Unidades_Negocio = Modelo_Unidad_Negocio::orderBy('orden')->get()->map(function (Modelo_Unidad_Negocio $Unidad) use ($Institucion) {
            $Unidad_Contratada = $Institucion->Unidades_Contratadas->firstWhere('id', $Unidad->id);

            return [
                'Codigo' => $Unidad->codigo,
                'Nombre' => $Unidad->nombre,
                'Descripcion' => $Unidad->descripcion,
                'Esta_Contratada' => (bool) $Unidad_Contratada,
                'Nota' => $Unidad_Contratada?->pivot->nota,
            ];
        });

        return Inertia::render('Portal/Pagina_Inicio', [
            'Fecha_Hoy' => ucfirst(now()->translatedFormat('l j \d\e F \d\e Y')),
            'Proyectos_Activos' => $Proyectos_Activos,
            'Cantidad_Aprobaciones_Pendientes' => Modelo_Entregable::Visibles_Para_Usuario($Usuario)->where('estado', 'pendiente_aprobacion')->count(),
            'Reporte_Destacado' => $Reporte_Destacado ? [
                'Id' => $Reporte_Destacado->id,
                'Titulo' => $Reporte_Destacado->titulo,
                'Resumen' => $Reporte_Destacado->resumen,
                'Autor' => $Reporte_Destacado->autor,
                'Minutos_Lectura' => $Reporte_Destacado->minutos_lectura,
            ] : null,
            'Proximo_Webinar' => $Proximo_Webinar ? [
                'Id' => $Proximo_Webinar->id,
                'Titulo' => $Proximo_Webinar->titulo,
                'Fecha' => $Proximo_Webinar->fecha_programada->translatedFormat('j M · H:i'),
            ] : null,
            'Facturas_Pendientes' => [
                'Cantidad' => $Facturas_Pendientes->count(),
                'Proxima' => $Proxima_Factura ? [
                    'Fecha_Vencimiento' => $Proxima_Factura->fecha_vencimiento?->translatedFormat('j \d\e F'),
                    'Monto' => number_format((float) $Proxima_Factura->monto, 2, ',', '.'),
                    'Moneda' => $Proxima_Factura->moneda,
                ] : null,
            ],
            'Saldo_Horas' => $Institucion->Calcular_Saldo_Horas(),
            'Nivel_Alianza' => $Institucion->Calcular_Nivel_Alianza(),
            'Unidades_Negocio' => $Unidades_Negocio,
        ]);
    }
}
