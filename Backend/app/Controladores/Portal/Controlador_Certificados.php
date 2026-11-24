<?php

namespace App\Controladores\Portal;

use App\Modelos\Modelo_Certificado;
use App\Modelos\Modelo_Registro_Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as Respuesta_Http;

/** Cada persona ve sus certificados; el administrador delegado ve además los de su equipo */
class Controlador_Certificados
{
    public function Listar_Certificados(Request $Peticion): Response
    {
        $Usuario = $Peticion->user();
        $Formatear = fn (Modelo_Certificado $Certificado) => [
            'Id' => $Certificado->id,
            'Titulo' => $Certificado->titulo,
            'Descripcion' => $Certificado->descripcion,
            'Puntaje' => $Certificado->puntaje,
            'Fecha' => $Certificado->created_at->translatedFormat('j M Y'),
            'Persona' => $Certificado->Usuario?->nombre,
            'Tiene_Archivo' => (bool) $Certificado->ruta_archivo,
        ];

        return Inertia::render('Portal/Pagina_Certificados', [
            'Mis_Certificados' => Modelo_Certificado::where('usuario_id', $Usuario->id)->latest()->get()->map($Formatear),
            'Certificados_Del_Equipo' => $Usuario->Es_Administrador_Delegado()
                ? Modelo_Certificado::Visibles_Para_Usuario($Usuario)->where('usuario_id', '!=', $Usuario->id)->with('Usuario')->latest()->get()->map($Formatear)
                : null,
        ]);
    }

    /** El PDF se abre en el navegador (es del usuario: puede guardarlo y compartirlo) */
    public function Ver_Archivo(Request $Peticion, Modelo_Certificado $Certificado): Respuesta_Http
    {
        $Usuario = $Peticion->user();
        $Puede_Verlo = $Certificado->usuario_id == $Usuario->id
            || ($Usuario->Es_Administrador_Delegado() && $Certificado->institucion_id == $Usuario->institucion_id);
        abort_unless($Puede_Verlo && $Certificado->ruta_archivo && Storage::disk('local')->exists($Certificado->ruta_archivo), 404);
        Modelo_Registro_Auditoria::Registrar_Evento('certificado.ver', $Certificado, 'Abrió el certificado «'.$Certificado->titulo.'»');

        return response()->file(Storage::disk('local')->path($Certificado->ruta_archivo), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.addslashes($Certificado->nombre_archivo ?: 'certificado.pdf').'"',
        ]);
    }
}
