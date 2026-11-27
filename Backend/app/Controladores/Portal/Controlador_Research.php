<?php

namespace App\Controladores\Portal;

use App\Modelos\Modelo_Registro_Auditoria;
use App\Modelos\Modelo_Reporte_Research;
use App\Modelos\Modelo_Usuario;
use App\Servicios\Servicio_Documentos_Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as Respuesta_Http;

/**
 * Los reportes se leen en el portal con el lector de PDF del navegador (o el visor de Google si es un enlace de Drive).
 */
class Controlador_Research
{
    public function Listar_Reportes(Request $Peticion): Response
    {
        $Usuario = $Peticion->user();
        $Tema_Seleccionado = $Peticion->query('tema');

        $Consulta = Modelo_Reporte_Research::Visibles_Para_Usuario($Usuario)->where('esta_publicado', true);
        $Reporte_Destacado = (clone $Consulta)->where('es_destacado', true)->orderByDesc('fecha_publicacion')->first();
        if ($Tema_Seleccionado) {
            $Consulta->where('tema', $Tema_Seleccionado);
        }

        return Inertia::render('Portal/Pagina_Research', [
            'Reporte_Destacado' => $Reporte_Destacado ? $this->Formatear_Reporte($Reporte_Destacado) : null,
            'Reportes' => $Consulta->orderByDesc('fecha_publicacion')->paginate(12)->withQueryString()
                ->through(fn (Modelo_Reporte_Research $Reporte) => $this->Formatear_Reporte($Reporte)),
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
            'Reporte' => $this->Formatear_Reporte($Reporte) + [
                'Documento' => Servicio_Documentos_Pdf::Armar_Documento($Reporte->ruta_archivo, $Reporte->enlace_externo, "/research/{$Reporte->id}/documento"),
            ],
        ]);
    }

    /** El PDF subido, para el lector del navegador */
    public function Obtener_Documento(Request $Peticion, Modelo_Reporte_Research $Reporte): Respuesta_Http
    {
        $this->Validar_Acceso_Reporte($Peticion->user(), $Reporte);

        return Servicio_Documentos_Pdf::Responder_Pdf($Reporte->ruta_archivo, $Reporte->nombre_archivo);
    }

    private function Validar_Acceso_Reporte(Modelo_Usuario $Usuario, Modelo_Reporte_Research $Reporte): void
    {
        $Es_Visible = Modelo_Reporte_Research::Visibles_Para_Usuario($Usuario)->where('esta_publicado', true)->whereKey($Reporte->id)->exists();
        abort_unless($Es_Visible, 404);
    }

    private function Formatear_Reporte(Modelo_Reporte_Research $Reporte): array
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
        ];
    }
}
