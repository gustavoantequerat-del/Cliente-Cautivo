<?php

namespace App\Controladores\Portal;

use App\Modelos\Modelo_Contrato;
use App\Modelos\Modelo_Entregable;
use App\Modelos\Modelo_Proyecto;
use App\Modelos\Modelo_Registro_Auditoria;
use App\Servicios\Servicio_Archivos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as Respuesta_Http;

class Controlador_Entregables
{
    public static function Formatear_Entregable(Modelo_Entregable $Entregable): array
    {
        return [
            'Id' => $Entregable->id,
            'Titulo' => $Entregable->titulo,
            'Nombre_Tipo' => Modelo_Entregable::Tipos[$Entregable->tipo] ?? $Entregable->tipo,
            'Version' => $Entregable->version,
            'Etiqueta_Fase' => $Entregable->etiqueta_fase,
            'Codigo_Unidad' => $Entregable->Unidad_Negocio?->codigo,
            'Nombre_Proyecto' => $Entregable->Proyecto?->nombre,
            'Estado' => $Entregable->estado,
            'Fecha' => ($Entregable->fecha_publicacion ?? $Entregable->created_at)?->translatedFormat('j M Y'),
            'Codigo_Verificacion' => $Entregable->codigo_verificacion,
            'Tiene_Archivo' => (bool) ($Entregable->ruta_archivo || $Entregable->enlace_externo),
            'Es_Enlace_Externo' => ! $Entregable->ruta_archivo && (bool) $Entregable->enlace_externo,
            'Aprobado_Por' => $Entregable->Aprobador?->nombre,
            'Fecha_Aprobacion' => $Entregable->fecha_aprobacion?->translatedFormat('j M Y H:i'),
        ];
    }

    public function Listar_Entregables(Request $Peticion): Response
    {
        $Usuario = $Peticion->user();
        $Tipo_Filtrado = $Peticion->query('tipo');
        $Proyecto_Filtrado = $Peticion->query('proyecto');

        $Consulta = Modelo_Entregable::Visibles_Para_Usuario($Usuario)->Visibles_Para_Cliente()->with(['Unidad_Negocio', 'Proyecto', 'Aprobador']);
        if ($Tipo_Filtrado) {
            $Consulta->where('tipo', $Tipo_Filtrado);
        }
        if ($Proyecto_Filtrado) {
            $Consulta->where('proyecto_id', $Proyecto_Filtrado);
        }

        $Cantidad_Por_Tipo = Modelo_Entregable::Visibles_Para_Usuario($Usuario)->Visibles_Para_Cliente()
            ->selectRaw('tipo, count(*) as total')->groupBy('tipo')->pluck('total', 'tipo');
        $Cantidad_Por_Estado = Modelo_Entregable::Visibles_Para_Usuario($Usuario)->Visibles_Para_Cliente()
            ->selectRaw('estado, count(*) as total')->groupBy('estado')->pluck('total', 'estado');

        // El administrador de la institución ve quién descargó o aprobó documentos
        $Registro_Accesos = $Usuario->Es_Cliente_Institucional()
            ? Modelo_Registro_Auditoria::with('Usuario')->where('institucion_id', $Usuario->institucion_id)
                ->whereIn('accion', ['entregable.descargar', 'entregable.aprobar', 'inicio_sesion'])
                ->latest('created_at')->limit(8)->get()
                ->map(fn (Modelo_Registro_Auditoria $Registro) => [
                    'Quien' => $Registro->Usuario?->nombre,
                    'Que' => $Registro->descripcion,
                    'Cuando' => $Registro->created_at->translatedFormat('j M H:i'),
                ])
            : [];

        return Inertia::render('Portal/Pagina_Entregables', [
            'Entregables' => $Consulta->latest()->paginate(20)->withQueryString()
                ->through(fn (Modelo_Entregable $Entregable) => self::Formatear_Entregable($Entregable)),
            'Tipos_Entregable' => collect(Modelo_Entregable::Tipos)->map(fn ($Nombre, $Clave) => [
                'Clave' => $Clave, 'Nombre' => $Nombre, 'Cantidad' => $Cantidad_Por_Tipo[$Clave] ?? 0,
            ])->values(),
            'Proyectos' => Modelo_Proyecto::Visibles_Para_Usuario($Usuario)->orderBy('nombre')->get()
                ->map(fn (Modelo_Proyecto $Proyecto) => ['Id' => $Proyecto->id, 'Nombre' => $Proyecto->nombre]),
            'Filtros_Aplicados' => ['Tipo' => $Tipo_Filtrado, 'Proyecto' => $Proyecto_Filtrado],
            'Registro_Accesos' => $Registro_Accesos,
            'Resumen' => [
                'Total' => $Cantidad_Por_Estado->sum(),
                'Por_Aprobar' => $Cantidad_Por_Estado['pendiente_aprobacion'] ?? 0,
                'Aprobados' => $Cantidad_Por_Estado['aprobado'] ?? 0,
            ],
        ]);
    }

    public function Descargar_Entregable(Request $Peticion, Modelo_Entregable $Entregable): Respuesta_Http
    {
        $this->Validar_Acceso_Entregable($Peticion, $Entregable);
        Modelo_Registro_Auditoria::Registrar_Evento('entregable.descargar', $Entregable, 'Descargó «'.$Entregable->titulo.'»');

        if ($Entregable->ruta_archivo) {
            return Servicio_Archivos::Descargar_Archivo($Entregable->ruta_archivo, $Entregable->nombre_archivo);
        }
        abort_unless($Entregable->enlace_externo, 404);

        return redirect()->away($Entregable->enlace_externo);
    }

    /**
     * Aprobación con re-autenticación. Se registra quién, cuándo, desde qué IP y el código de verificación
     * del documento aprobado (fase 2: además se ancla en Stamping.io).
     */
    public function Aprobar_Entregable(Request $Peticion, Modelo_Entregable $Entregable): RedirectResponse
    {
        $this->Validar_Acceso_Entregable($Peticion, $Entregable);
        $Peticion->validate(['password' => ['required', 'current_password']], [
            'password.current_password' => 'La contraseña no es correcta.',
        ]);
        abort_unless($Entregable->Esta_Pendiente_De_Aprobacion(), 422, 'Este entregable no está pendiente de aprobación.');

        // Integridad: si el archivo está en el servidor, su código de verificación debe coincidir con la registrada
        if ($Entregable->ruta_archivo && Storage::disk('local')->exists($Entregable->ruta_archivo)) {
            $Codigo_Actual = hash_file('sha256', Storage::disk('local')->path($Entregable->ruta_archivo));
            if ($Entregable->codigo_verificacion && $Codigo_Actual !== $Entregable->codigo_verificacion) {
                return back()->with('Mensaje_Error', 'El código de verificación del archivo no coincide con el registrado. Contacte a su ejecutiva.');
            }
            $Entregable->codigo_verificacion = $Codigo_Actual;
        }

        $Entregable->forceFill([
            'estado' => 'aprobado',
            'aprobado_por' => $Peticion->user()->id,
            'fecha_aprobacion' => now(),
            'ip_aprobacion' => $Peticion->ip(),
        ])->save();

        Modelo_Registro_Auditoria::Registrar_Evento('entregable.aprobar', $Entregable, 'Aprobó «'.$Entregable->titulo.'»', [
            'Codigo_Verificacion' => $Entregable->codigo_verificacion,
        ]);

        return back()->with('Mensaje_Exito', 'Entregable aprobado. Su aprobación quedó registrada con el código de verificación del documento.');
    }

    /** Recibe solo el código calculado en el navegador; el archivo nunca se sube. */
    public function Verificar_Codigo_Documento(Request $Peticion): RedirectResponse
    {
        $Datos = $Peticion->validate(['codigo_verificacion' => ['required', 'regex:/^[a-f0-9]{64}$/i']]);
        $Codigo = strtolower($Datos['codigo_verificacion']);
        $Usuario = $Peticion->user();

        $Documento = Modelo_Entregable::Visibles_Para_Usuario($Usuario)->Visibles_Para_Cliente()->where('codigo_verificacion', $Codigo)->first()
            ?? Modelo_Contrato::Visibles_Para_Usuario($Usuario)->where('codigo_verificacion', $Codigo)->first();

        return back()->with('Resultado_Verificacion', [
            'Es_Autentico' => (bool) $Documento,
            'Titulo' => $Documento?->titulo,
            'Fecha' => $Documento?->created_at->translatedFormat('j M Y'),
            'Codigo' => $Codigo,
        ]);
    }

    /** El cliente institucional accede a todo lo de su institución; el básico, solo a lo de sus proyectos */
    private function Validar_Acceso_Entregable(Request $Peticion, Modelo_Entregable $Entregable): void
    {
        $Usuario = $Peticion->user();
        $Es_De_Su_Institucion = $Entregable->institucion_id == $Usuario->institucion_id && $Entregable->estado !== 'borrador';
        $Puede_Verlo = $Usuario->Es_Cliente_Institucional() || ($Entregable->proyecto_id
            && Modelo_Proyecto::Accesibles_Para_Usuario($Usuario)->whereKey($Entregable->proyecto_id)->exists());
        abort_unless($Es_De_Su_Institucion && $Puede_Verlo, 404);
    }
}
