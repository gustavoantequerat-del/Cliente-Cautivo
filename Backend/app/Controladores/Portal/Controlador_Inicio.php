<?php

namespace App\Controladores\Portal;

use App\Modelos\Modelo_Entregable;
use App\Modelos\Modelo_Factura;
use App\Modelos\Modelo_Institucion;
use App\Modelos\Modelo_Progreso_Video;
use App\Modelos\Modelo_Proyecto;
use App\Modelos\Modelo_Reporte_Research;
use App\Modelos\Modelo_Unidad_Negocio;
use App\Modelos\Modelo_Usuario;
use App\Modelos\Modelo_Video;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Todos ven sus proyectos, la formación y su nivel. El administrador delegado además ve
 * el panel de gestión: aprobaciones, facturas, horas, equipo y unidades contratadas.
 */
class Controlador_Inicio
{
    public function Mostrar_Inicio(Request $Peticion): Response
    {
        $Usuario = $Peticion->user();
        $Institucion = $Usuario->Institucion->load('Unidades_Contratadas');

        $Proyectos_Activos = Modelo_Proyecto::Accesibles_Para_Usuario($Usuario)->where('estado', 'activo')
            ->with(['Unidad_Negocio', 'Fases'])->latest()->get()
            ->map(fn (Modelo_Proyecto $Proyecto) => Controlador_Proyectos::Formatear_Resumen_Proyecto($Proyecto));

        $Reporte_Destacado = $Institucion->Tiene_Unidad_Contratada('RESEARCH')
            ? Modelo_Reporte_Research::Visibles_Para_Usuario($Usuario)->where('esta_publicado', true)
                ->orderByDesc('es_destacado')->orderByDesc('fecha_publicacion')->first()
            : null;

        $Proximo_Webinar = Modelo_Video::Visibles_Para_Usuario($Usuario)->where('esta_publicado', true)->where('formato', 'en_vivo')
            ->where('fecha_programada', '>=', now())->orderBy('fecha_programada')->first();

        $Videos_Completados = Modelo_Progreso_Video::where('usuario_id', $Usuario->id)->whereNotNull('fecha_completado')->pluck('video_id');

        return Inertia::render('Portal/Pagina_Inicio', [
            'Fecha_Hoy' => ucfirst(now()->translatedFormat('l j \d\e F')),
            'Proyectos_Activos' => $Proyectos_Activos,
            'Reporte_Destacado' => $Reporte_Destacado ? [
                'Id' => $Reporte_Destacado->id,
                'Titulo' => $Reporte_Destacado->titulo,
                'Resumen' => $Reporte_Destacado->resumen,
                'Minutos_Lectura' => $Reporte_Destacado->minutos_lectura,
            ] : null,
            'Proximo_Webinar' => $Proximo_Webinar ? [
                'Id' => $Proximo_Webinar->id,
                'Titulo' => $Proximo_Webinar->titulo,
                'Fecha' => $Proximo_Webinar->fecha_programada->translatedFormat('j M · H:i'),
            ] : null,
            'Mi_Avance' => [
                'Completados' => $Videos_Completados->count(),
                'Horas' => round(Modelo_Video::whereIn('id', $Videos_Completados)->sum('duracion_minutos') / 60, 1),
            ],
            'Nivel_Alianza' => $Institucion->Calcular_Nivel_Alianza(),
            'Panel_Institucional' => $Usuario->Es_Administrador_Delegado() ? $this->Armar_Panel_Institucional($Usuario, $Institucion) : null,
        ]);
    }

    private function Armar_Panel_Institucional(Modelo_Usuario $Usuario, Modelo_Institucion $Institucion): array
    {
        $Facturas_Pendientes = Modelo_Factura::Visibles_Para_Usuario($Usuario)->where('estado', 'pendiente')->orderBy('fecha_vencimiento')->get();
        $Proxima_Factura = $Facturas_Pendientes->first();

        return [
            'Cantidad_Aprobaciones_Pendientes' => Modelo_Entregable::Visibles_Para_Usuario($Usuario)->where('estado', 'pendiente_aprobacion')->count(),
            'Facturas_Pendientes' => [
                'Cantidad' => $Facturas_Pendientes->count(),
                'Proxima' => $Proxima_Factura ? [
                    'Fecha_Vencimiento' => $Proxima_Factura->fecha_vencimiento?->translatedFormat('j \d\e F'),
                    'Monto' => number_format((float) $Proxima_Factura->monto, 2, ',', '.'),
                    'Moneda' => $Proxima_Factura->moneda,
                ] : null,
            ],
            'Saldo_Horas' => $Institucion->Calcular_Saldo_Horas(),
            'Cantidad_Equipo' => Modelo_Usuario::where('institucion_id', $Institucion->id)->where('rol', Modelo_Usuario::Rol_Usuario_Institucional)->where('esta_activo', true)->count(),
            'Unidades_Negocio' => Modelo_Unidad_Negocio::orderBy('orden')->get()->map(fn (Modelo_Unidad_Negocio $Unidad) => [
                'Codigo' => $Unidad->codigo,
                'Nombre' => $Unidad->nombre,
                'Descripcion' => $Unidad->descripcion,
                'Esta_Contratada' => $Institucion->Unidades_Contratadas->contains('id', $Unidad->id),
            ]),
        ];
    }
}
