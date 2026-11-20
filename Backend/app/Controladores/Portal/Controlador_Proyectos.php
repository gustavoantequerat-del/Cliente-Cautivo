<?php

namespace App\Controladores\Portal;

use App\Modelos\Modelo_Entregable;
use App\Modelos\Modelo_Proyecto;
use App\Modelos\Modelo_Registro_Auditoria;
use App\Modelos\Modelo_Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class Controlador_Proyectos
{
    public function Listar_Proyectos(Request $Peticion): Response
    {
        $Lista_Proyectos = Modelo_Proyecto::Accesibles_Para_Usuario($Peticion->user())->with(['Unidad_Negocio', 'Fases'])
            ->orderByRaw("case when estado = 'activo' then 0 else 1 end")->latest()->get()
            ->map(fn (Modelo_Proyecto $Proyecto) => self::Formatear_Resumen_Proyecto($Proyecto));

        return Inertia::render('Portal/Pagina_Proyectos', ['Lista_Proyectos' => $Lista_Proyectos]);
    }

    /** Datos de la tarjeta de un proyecto (también se usa en Inicio) */
    public static function Formatear_Resumen_Proyecto(Modelo_Proyecto $Proyecto): array
    {
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
    }

    public function Mostrar_Proyecto(Request $Peticion, Modelo_Proyecto $Proyecto): Response
    {
        $Usuario = $Peticion->user();
        $this->Validar_Acceso_Proyecto($Usuario, $Proyecto);
        $Proyecto->load(['Unidad_Negocio', 'Fases', 'Bitacora', 'Participantes']);

        $Entregables = $Proyecto->Entregables()->Visibles_Para_Cliente()->with('Aprobador')->orderBy('created_at')->get();

        return Inertia::render('Portal/Pagina_Detalle_Proyecto', [
            'Proyecto' => [
                'Id' => $Proyecto->id,
                'Nombre' => $Proyecto->nombre,
                'Descripcion' => $Proyecto->descripcion,
                'Codigo_Unidad' => $Proyecto->Unidad_Negocio?->codigo,
                'Estado' => $Proyecto->estado,
                'Avance' => $Proyecto->avance,
                'Proximo_Hito' => $Proyecto->proximo_hito,
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
            // Solo el cliente institucional ve y gestiona quiénes del equipo participan
            'Equipo' => $Usuario->Es_Cliente_Institucional()
                ? Modelo_Usuario::where('institucion_id', $Usuario->institucion_id)->where('rol', Modelo_Usuario::Rol_Cliente_Basico)
                    ->where('esta_activo', true)->orderBy('nombre')->get()
                    ->map(fn (Modelo_Usuario $Miembro) => [
                        'Id' => $Miembro->id,
                        'Nombre' => $Miembro->nombre,
                        'Iniciales' => $Miembro->Obtener_Iniciales(),
                        'Area' => $Miembro->area,
                        'Esta_Inscrito' => $Proyecto->Participantes->contains('id', $Miembro->id),
                    ])
                : null,
        ]);
    }

    /** El cliente institucional inscribe o quita a un cliente básico de su institución */
    public function Actualizar_Participante(Request $Peticion, Modelo_Proyecto $Proyecto): RedirectResponse
    {
        $Usuario = $Peticion->user();
        $this->Validar_Acceso_Proyecto($Usuario, $Proyecto);
        $Datos = $Peticion->validate([
            'usuario_id' => ['required', 'integer', Rule::exists('usuarios', 'id')
                ->where('institucion_id', $Usuario->institucion_id)->where('rol', Modelo_Usuario::Rol_Cliente_Basico)],
            'inscrito' => ['required', 'boolean'],
        ]);

        $Miembro = Modelo_Usuario::findOrFail($Datos['usuario_id']);
        if ($Datos['inscrito']) {
            $Proyecto->Participantes()->syncWithoutDetaching([$Miembro->id]);
        } else {
            $Proyecto->Participantes()->detach($Miembro->id);
        }
        Modelo_Registro_Auditoria::Registrar_Evento('proyecto.participantes', $Proyecto,
            ($Datos['inscrito'] ? 'Inscribió a ' : 'Quitó a ').$Miembro->nombre.' en «'.$Proyecto->nombre.'»');

        return back()->with('Mensaje_Exito', $Datos['inscrito'] ? "{$Miembro->nombre} ya ve este proyecto." : "{$Miembro->nombre} ya no ve este proyecto.");
    }

    private function Validar_Acceso_Proyecto(Modelo_Usuario $Usuario, Modelo_Proyecto $Proyecto): void
    {
        abort_unless(Modelo_Proyecto::Accesibles_Para_Usuario($Usuario)->whereKey($Proyecto->id)->exists(), 404);
    }
}
