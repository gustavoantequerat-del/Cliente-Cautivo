<?php

namespace App\Controladores\Portal;

use App\Modelos\Modelo_Entregable;
use App\Modelos\Modelo_Factura;
use App\Modelos\Modelo_Institucion;
use App\Modelos\Modelo_Progreso_Video;
use App\Modelos\Modelo_Reporte_Research;
use App\Modelos\Modelo_Unidad_Negocio;
use App\Modelos\Modelo_Usuario;
use App\Modelos\Modelo_Video;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Todos ven sus productos del ecosistema, la formación y su nivel. El administrador delegado además ve
 * el panel de gestión: aprobaciones, facturas, horas y equipo.
 */
class Controlador_Inicio
{
    public function Mostrar_Inicio(Request $Peticion): Response
    {
        $Usuario = $Peticion->user();
        $Institucion = $Usuario->Institucion->load('Unidades_Contratadas');

        $Productos_Publicados = Modelo_Unidad_Negocio::where('esta_publicado', true)->orderBy('orden')->get();
        $Productos_Contratados = $Productos_Publicados->filter(fn ($Producto) => $Institucion->Unidades_Contratadas->contains('id', $Producto->id));

        $Reporte_Destacado = Modelo_Reporte_Research::Visibles_Para_Usuario($Usuario)->where('esta_publicado', true)
            ->orderByDesc('es_destacado')->orderByDesc('fecha_publicacion')->first();

        $Proximo_Webinar = Modelo_Video::Visibles_Para_Usuario($Usuario)->where('esta_publicado', true)->where('formato', 'en_vivo')
            ->where('fecha_programada', '>=', now())->orderBy('fecha_programada')->first();

        $Videos_Completados = Modelo_Progreso_Video::where('usuario_id', $Usuario->id)->whereNotNull('fecha_completado')->pluck('video_id');

        return Inertia::render('Portal/Pagina_Inicio', [
            'Fecha_Hoy' => ucfirst(now()->translatedFormat('l j \d\e F')),
            'Ecosistema' => [
                'Contratados' => $Productos_Contratados->map(fn (Modelo_Unidad_Negocio $Producto) => [
                    'Id' => $Producto->id,
                    'Titulo' => $Producto->nombre,
                    'Subtitulo' => $Producto->subtitulo,
                    'Url_Imagen' => $Producto->ruta_imagen ? "/ecosistema/{$Producto->id}/imagen?v=".$Producto->updated_at?->timestamp : null,
                ])->values(),
                'Por_Descubrir' => $Productos_Publicados->count() - $Productos_Contratados->count(),
            ],
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
        ];
    }
}
