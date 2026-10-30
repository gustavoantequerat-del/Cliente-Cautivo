<?php

namespace App\Controladores\Portal;

use App\Modelos\Modelo_Registro_Auditoria;
use App\Modelos\Modelo_Reporte_Research;
use App\Servicios\Servicio_Archivos;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as Respuesta_Http;

class Controlador_Research
{
    public function Listar_Reportes(Request $Peticion): Response
    {
        $Tema_Seleccionado = $Peticion->query('tema');

        $Consulta = Modelo_Reporte_Research::Visibles_Para_Usuario($Peticion->user())->where('esta_publicado', true);
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

    public function Abrir_Reporte(Request $Peticion, Modelo_Reporte_Research $Reporte): Respuesta_Http
    {
        $Es_Visible = Modelo_Reporte_Research::Visibles_Para_Usuario($Peticion->user())
            ->where('esta_publicado', true)->whereKey($Reporte->id)->exists();
        abort_unless($Es_Visible, 404);
        Modelo_Registro_Auditoria::Registrar_Evento('research.abrir', $Reporte, 'Abrió el reporte «'.$Reporte->titulo.'»');

        if ($Reporte->ruta_archivo) {
            return Servicio_Archivos::Descargar_Archivo($Reporte->ruta_archivo, $Reporte->nombre_archivo);
        }
        abort_unless($Reporte->enlace_externo, 404);

        return redirect()->away($Reporte->enlace_externo);
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
