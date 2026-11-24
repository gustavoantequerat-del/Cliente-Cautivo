<?php

namespace App\Controladores\Portal;

use App\Modelos\Modelo_Configuracion;
use App\Modelos\Modelo_Movimiento_Monedas;
use App\Modelos\Modelo_Registro_Auditoria;
use App\Modelos\Modelo_Reporte_Research;
use App\Modelos\Modelo_Usuario;
use App\Servicios\Servicio_Documentos_Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as Respuesta_Http;

/**
 * Los reportes se leen en el portal (visor con PDF.js y marca de agua), sin descarga.
 */
class Controlador_Research
{
    public function Listar_Reportes(Request $Peticion): Response
    {
        $Usuario = $Peticion->user();
        $Tema_Seleccionado = $Peticion->query('tema');
        $Ids_Leidos = $this->Obtener_Ids_Leidos($Usuario);

        $Consulta = Modelo_Reporte_Research::Visibles_Para_Usuario($Usuario)->where('esta_publicado', true);
        $Reporte_Destacado = (clone $Consulta)->where('es_destacado', true)->orderByDesc('fecha_publicacion')->first();
        if ($Tema_Seleccionado) {
            $Consulta->where('tema', $Tema_Seleccionado);
        }

        return Inertia::render('Portal/Pagina_Research', [
            'Reporte_Destacado' => $Reporte_Destacado ? $this->Formatear_Reporte($Reporte_Destacado, $Ids_Leidos) : null,
            'Reportes' => $Consulta->orderByDesc('fecha_publicacion')->paginate(12)->withQueryString()
                ->through(fn (Modelo_Reporte_Research $Reporte) => $this->Formatear_Reporte($Reporte, $Ids_Leidos)),
            'Temas' => Modelo_Reporte_Research::Temas,
            'Tema_Seleccionado' => $Tema_Seleccionado,
        ]);
    }

    public function Mostrar_Reporte(Request $Peticion, Modelo_Reporte_Research $Reporte): Response
    {
        $Usuario = $Peticion->user();
        $this->Validar_Acceso_Reporte($Usuario, $Reporte);
        Modelo_Registro_Auditoria::Registrar_Evento('research.leer', $Reporte, 'Abrió el reporte «'.$Reporte->titulo.'»');

        return Inertia::render('Portal/Pagina_Detalle_Reporte', [
            'Reporte' => $this->Formatear_Reporte($Reporte, $this->Obtener_Ids_Leidos($Usuario)) + [
                'Url_Documento' => "/research/{$Reporte->id}/documento",
            ],
            'Texto_Marca_Agua' => Modelo_Configuracion::Armar_Texto_Marca_Agua($Usuario),
        ]);
    }

    /**
     * Entrega el PDF al visor del portal (inline, sin caché del navegador). Si el reporte es un enlace
     * de Drive, el Backend lo descarga por su ID: el navegador no puede pedirlo directo por CORS.
     */
    /** Entrega el PDF al visor (el navegador no puede pedirlo directo a Drive por CORS) */
    public function Obtener_Documento(Request $Peticion, Modelo_Reporte_Research $Reporte): Respuesta_Http
    {
        $this->Validar_Acceso_Reporte($Peticion->user(), $Reporte);

        return Servicio_Documentos_Pdf::Responder_Pdf($Reporte->ruta_archivo, $Reporte->enlace_externo);
    }

    /** El visor avisa cuando el usuario llegó a la última página: gana monedas una sola vez */
    public function Marcar_Reporte_Leido(Request $Peticion, Modelo_Reporte_Research $Reporte): RedirectResponse
    {
        $Usuario = $Peticion->user();
        $this->Validar_Acceso_Reporte($Usuario, $Reporte);
        $Gano_Monedas = Modelo_Movimiento_Monedas::Otorgar_Una_Vez($Usuario, $Reporte->puntos, 'research', $Reporte->id, 'Leyó «'.$Reporte->titulo.'»');

        return $Gano_Monedas
            ? back()->with('Mensaje_Exito', "¡Reporte leído! Ganó {$Reporte->puntos} monedas para su avatar.")
            : back();
    }

    private function Validar_Acceso_Reporte(Modelo_Usuario $Usuario, Modelo_Reporte_Research $Reporte): void
    {
        $Es_Visible = Modelo_Reporte_Research::Visibles_Para_Usuario($Usuario)->where('esta_publicado', true)->whereKey($Reporte->id)->exists();
        abort_unless($Es_Visible, 404);
    }

    private function Obtener_Ids_Leidos(Modelo_Usuario $Usuario): array
    {
        return Modelo_Movimiento_Monedas::where('usuario_id', $Usuario->id)->where('tipo_origen', 'research')->pluck('origen_id')->all();
    }

    private function Formatear_Reporte(Modelo_Reporte_Research $Reporte, array $Ids_Leidos): array
    {
        return [
            'Id' => $Reporte->id,
            'Titulo' => $Reporte->titulo,
            'Resumen' => $Reporte->resumen,
            'Tema' => $Reporte->tema,
            'Autor' => $Reporte->autor,
            'Minutos_Lectura' => $Reporte->minutos_lectura,
            'Fecha' => $Reporte->fecha_publicacion?->translatedFormat('M Y'),
            'Es_Exclusivo' => (bool) $Reporte->institucion_id,
            'Tiene_Archivo' => (bool) ($Reporte->ruta_archivo || $Reporte->enlace_externo),
            'Monedas' => $Reporte->puntos,
            'Esta_Leido' => in_array($Reporte->id, $Ids_Leidos),
        ];
    }
}
