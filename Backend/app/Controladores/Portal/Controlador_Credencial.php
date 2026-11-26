<?php

namespace App\Controladores\Portal;

use App\Modelos\Modelo_Progreso_Video;
use App\Modelos\Modelo_Usuario;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class Controlador_Credencial
{
    public function Mostrar_Credencial(Request $Peticion): Response
    {
        $Usuario = $Peticion->user();
        $Institucion = $Usuario->Institucion;

        return Inertia::render('Portal/Pagina_Credencial', [
            'Tarjeta' => [
                'Nombre' => $Usuario->nombre,
                'Area' => $Usuario->area,
                'Institucion' => $Institucion->nombre,
                'Numero' => $Usuario->numero_credencial,
                'Fecha_Emision' => $Usuario->fecha_emision_credencial?->format('d·m·Y'),
                'Url_Verificacion' => $Usuario->codigo_credencial ? url('/verificar/'.$Usuario->codigo_credencial) : null,
            ],
            'Nivel_Alianza' => $Institucion->Calcular_Nivel_Alianza(),
            'Videos_Completados' => Modelo_Progreso_Video::where('usuario_id', $Usuario->id)->whereNotNull('fecha_completado')->count(),
            // Solo el administrador delegado ve las credenciales de su organización
            'Miembros_Institucion' => $Usuario->Es_Administrador_Delegado()
                ? Modelo_Usuario::where('institucion_id', $Institucion->id)->orderBy('nombre')->get()->map(fn (Modelo_Usuario $Miembro) => [
                    'Nombre' => $Miembro->nombre,
                    'Iniciales' => $Miembro->Obtener_Iniciales(),
                    'Nombre_Rol' => Modelo_Usuario::Roles[$Miembro->rol] ?? $Miembro->rol,
                    'Area' => $Miembro->area,
                    'Numero' => $Miembro->numero_credencial,
                    'Esta_Activo' => $Miembro->esta_activo,
                    'Ultimo_Ingreso' => $Miembro->fecha_ultimo_ingreso?->translatedFormat('j M Y'),
                ])
                : null,
        ]);
    }
}
