<?php

namespace App\Controladores\Administracion;

use App\Modelos\Modelo_Institucion;
use App\Modelos\Modelo_Registro_Auditoria;
use App\Modelos\Modelo_Proyecto;
use App\Modelos\Modelo_Unidad_Negocio;
use App\Modelos\Modelo_Usuario;
use App\Servicios\Servicio_Archivos;
use App\Servicios\Servicio_Definicion_Cms;
use App\Servicios\Servicio_Enlaces_Video;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as Regla_Contrasena;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as Respuesta_Http;

/**
 * Listado, alta, edición y baja genéricos del CMS a partir de Servicio_Definicion_Cms.
 */
class Controlador_Recursos_Cms
{
    public function Listar_Registros(Request $Peticion, string $Clave_Recurso): Response
    {
        $Recurso = Servicio_Definicion_Cms::Obtener_Recurso($Clave_Recurso, $Peticion->user());
        $Consulta = Servicio_Definicion_Cms::Consultar_Registros($Recurso, $Peticion->user());

        if ($Texto_Busqueda = trim((string) $Peticion->query('buscar'))) {
            $Consulta->where(function ($Condicion) use ($Recurso, $Texto_Busqueda) {
                foreach ($Recurso['Columnas_Busqueda'] as $Columna) {
                    $Condicion->orWhere($Columna, 'like', '%'.$Texto_Busqueda.'%');
                }
            });
        }
        $Columna_Filtro = $Recurso['Columna_Filtro'] ?? null;
        if ($Columna_Filtro && $Peticion->filled('filtro')) {
            $Consulta->where($Columna_Filtro, $Peticion->query('filtro'));
        }
        [$Columna_Orden, $Direccion_Orden] = $Recurso['Orden'];
        $Consulta->orderBy($Columna_Orden, $Direccion_Orden)->orderByDesc('id');

        $Campos_En_Tabla = array_values(array_filter($Recurso['Campos'], fn ($Campo) => $Campo['En_Tabla'] ?? false));
        $Opciones = $this->Obtener_Opciones_De_Listas($Recurso['Campos'], $Peticion->user());

        $Filas = $Consulta->paginate(25)->withQueryString()->through(function (Model $Registro) use ($Campos_En_Tabla, $Opciones) {
            $Fila = ['id' => $Registro->getKey()];
            foreach ($Campos_En_Tabla as $Campo) {
                $Fila[$Campo['Nombre']] = $this->Formatear_Valor_Para_Tabla($Registro, $Campo, $Opciones);
            }

            return $Fila;
        });

        $Campo_Filtro = $Columna_Filtro ? collect($Recurso['Campos'])->firstWhere('Nombre', $Columna_Filtro) : null;

        return Inertia::render('Administracion/Pagina_Listado_Recurso', [
            'Recurso' => Arr::only($Recurso, ['Clave', 'Titulo', 'Singular', 'Icono']),
            'Columnas' => array_map(fn ($Campo) => Arr::only($Campo, ['Nombre', 'Etiqueta']), $Campos_En_Tabla),
            'Filas' => $Filas,
            'Filtro' => $Campo_Filtro ? [
                'Etiqueta' => $Campo_Filtro['Etiqueta'],
                'Opciones' => $Opciones[$Columna_Filtro] ?? [],
                'Valor' => $Peticion->query('filtro'),
            ] : null,
            'Busqueda' => $Peticion->query('buscar'),
        ]);
    }

    public function Mostrar_Formulario_Crear(Request $Peticion, string $Clave_Recurso): Response
    {
        $Recurso = Servicio_Definicion_Cms::Obtener_Recurso($Clave_Recurso, $Peticion->user());
        $Valores = [];
        foreach ($Recurso['Campos'] as $Campo) {
            $Valores[$Campo['Nombre']] = $Campo['Por_Defecto'] ?? match ($Campo['Tipo']) {
                'si_no' => false,
                'unidades' => [],
                default => null,
            };
        }

        return $this->Mostrar_Formulario($Recurso, $Valores, null, $Peticion->user());
    }

    public function Mostrar_Formulario_Editar(Request $Peticion, string $Clave_Recurso, int $Id_Registro): Response
    {
        $Recurso = Servicio_Definicion_Cms::Obtener_Recurso($Clave_Recurso, $Peticion->user());
        $Registro = Servicio_Definicion_Cms::Consultar_Registros($Recurso, $Peticion->user())->findOrFail($Id_Registro);
        $Valores = [];
        foreach ($Recurso['Campos'] as $Campo) {
            $Valor = $Campo['Tipo'] === 'unidades' ? null : $Registro->{$Campo['Nombre']};
            $Valores[$Campo['Nombre']] = match ($Campo['Tipo']) {
                'contrasena' => '',
                'unidades' => $Registro->Unidades_Contratadas->mapWithKeys(fn ($Unidad) => [$Unidad->id => [
                    'activa' => true, 'nota' => $Unidad->pivot->nota, 'activa_desde' => $Unidad->pivot->activa_desde,
                ]])->all(),
                'fecha' => $Valor ? Carbon::parse($Valor)->format('Y-m-d') : null,
                'fecha_hora' => $Valor ? Carbon::parse($Valor)->format('Y-m-d\TH:i') : null,
                'si_no' => (bool) $Valor,
                default => $Valor,
            };
        }

        return $this->Mostrar_Formulario($Recurso, $Valores, $Registro, $Peticion->user());
    }

    public function Crear_Registro(Request $Peticion, string $Clave_Recurso): RedirectResponse
    {
        $Recurso = Servicio_Definicion_Cms::Obtener_Recurso($Clave_Recurso, $Peticion->user());
        $Registro = new $Recurso['Modelo'];
        $this->Validar_Y_Guardar($Peticion, $Recurso, $Registro);
        Modelo_Registro_Auditoria::Registrar_Evento('cms.crear', $Registro, 'Creó '.$Recurso['Singular'].' #'.$Registro->getKey());

        return redirect('/admin/'.$Clave_Recurso)->with('Mensaje_Exito', ucfirst($Recurso['Singular']).' creado/a.');
    }

    public function Actualizar_Registro(Request $Peticion, string $Clave_Recurso, int $Id_Registro): RedirectResponse
    {
        $Recurso = Servicio_Definicion_Cms::Obtener_Recurso($Clave_Recurso, $Peticion->user());
        $Registro = Servicio_Definicion_Cms::Consultar_Registros($Recurso, $Peticion->user())->findOrFail($Id_Registro);
        $this->Validar_Y_Guardar($Peticion, $Recurso, $Registro);
        Modelo_Registro_Auditoria::Registrar_Evento('cms.editar', $Registro, 'Editó '.$Recurso['Singular'].' #'.$Registro->getKey());

        return redirect('/admin/'.$Clave_Recurso)->with('Mensaje_Exito', 'Cambios guardados.');
    }

    public function Eliminar_Registro(Request $Peticion, string $Clave_Recurso, int $Id_Registro): RedirectResponse
    {
        $Recurso = Servicio_Definicion_Cms::Obtener_Recurso($Clave_Recurso, $Peticion->user());
        $Registro = Servicio_Definicion_Cms::Consultar_Registros($Recurso, $Peticion->user())->findOrFail($Id_Registro);
        abort_if($Registro->is($Peticion->user()), 422, 'No puede eliminar su propio usuario.');

        foreach ($Recurso['Campos'] as $Campo) {
            if ($Campo['Tipo'] === 'archivo') {
                Servicio_Archivos::Eliminar_Archivo($Registro->{$Campo['Nombre']});
            }
        }
        Modelo_Registro_Auditoria::Registrar_Evento('cms.eliminar', null, 'Eliminó '.$Recurso['Singular'].' #'.$Registro->getKey());
        $Registro->delete();

        return redirect('/admin/'.$Clave_Recurso)->with('Mensaje_Exito', 'Registro eliminado.');
    }

    /** Descarga para el personal de Blockfinity de un archivo cargado en el CMS */
    public function Descargar_Archivo_Registro(Request $Peticion, string $Clave_Recurso, int $Id_Registro, string $Nombre_Campo): Respuesta_Http
    {
        $Recurso = Servicio_Definicion_Cms::Obtener_Recurso($Clave_Recurso, $Peticion->user());
        $Campo = collect($Recurso['Campos'])->firstWhere('Nombre', $Nombre_Campo);
        abort_unless($Campo && $Campo['Tipo'] === 'archivo', 404);
        $Registro = Servicio_Definicion_Cms::Consultar_Registros($Recurso, $Peticion->user())->findOrFail($Id_Registro);
        $Nombre_Descarga = isset($Campo['Guardar_Nombre_En']) ? $Registro->{$Campo['Guardar_Nombre_En']} : null;

        return Servicio_Archivos::Descargar_Archivo($Registro->{$Nombre_Campo}, $Nombre_Descarga);
    }

    // ---------------------------------------------------------------------

    private function Mostrar_Formulario(array $Recurso, array $Valores, ?Model $Registro, Modelo_Usuario $Usuario): Response
    {
        $Campos = array_map(function ($Campo) use ($Registro, $Recurso) {
            $Campo_Visible = Arr::except($Campo, ['Modelo', 'Filtro_Donde', 'Opciones_Para_Ejecutivo']);
            if ($Campo['Tipo'] === 'archivo' && $Registro?->{$Campo['Nombre']}) {
                $Campo_Visible['Archivo_Actual'] = [
                    'Nombre' => isset($Campo['Guardar_Nombre_En']) ? $Registro->{$Campo['Guardar_Nombre_En']} : basename($Registro->{$Campo['Nombre']}),
                    'Url' => url("/admin/archivo/{$Recurso['Clave']}/{$Registro->getKey()}/{$Campo['Nombre']}"),
                ];
            }

            return $Campo_Visible;
        }, $Recurso['Campos']);

        return Inertia::render('Administracion/Pagina_Formulario_Recurso', [
            'Recurso' => Arr::only($Recurso, ['Clave', 'Titulo', 'Singular', 'Icono']),
            'Campos' => $Campos,
            'Valores' => $Valores,
            'Opciones' => $this->Obtener_Opciones_De_Listas($Recurso['Campos'], $Usuario) + [
                'Unidades_Negocio' => Modelo_Unidad_Negocio::orderBy('orden')->get()
                    ->map(fn ($Unidad) => ['Id' => $Unidad->id, 'Codigo' => $Unidad->codigo, 'Nombre' => $Unidad->nombre]),
            ],
            'Id_Registro' => $Registro?->getKey(),
            'Proveedores_Video' => Servicio_Enlaces_Video::Proveedores,
        ]);
    }

    private function Validar_Y_Guardar(Request $Peticion, array $Recurso, Model $Registro): void
    {
        $Campos_Editables = array_filter($Recurso['Campos'], fn ($Campo) => ! ($Campo['Solo_Lectura'] ?? false));
        // Las listas y relaciones solo aceptan las opciones que este usuario puede ver
        $Opciones_Permitidas = $this->Obtener_Opciones_De_Listas($Recurso['Campos'], $Peticion->user());

        $Reglas = [];
        foreach ($Campos_Editables as $Campo) {
            $Reglas[$Campo['Nombre']] = $this->Obtener_Reglas_Validacion($Campo, $Registro, $Opciones_Permitidas[$Campo['Nombre']] ?? []);
            if ($Campo['Tipo'] === 'unidades') {
                $Reglas[$Campo['Nombre'].'.*.activa'] = ['nullable', 'boolean'];
                $Reglas[$Campo['Nombre'].'.*.nota'] = ['nullable', 'string', 'max:255'];
                $Reglas[$Campo['Nombre'].'.*.activa_desde'] = ['nullable', 'date'];
            }
        }
        $Datos = $Peticion->validate($Reglas);

        $Unidades_Elegidas = null;
        foreach ($Campos_Editables as $Campo) {
            $Nombre = $Campo['Nombre'];
            switch ($Campo['Tipo']) {
                case 'unidades':
                    $Unidades_Elegidas = $Datos[$Nombre] ?? [];
                    break;
                case 'contrasena':
                    if (! empty($Datos[$Nombre])) {
                        $Registro->{$Nombre} = $Datos[$Nombre];
                    }
                    break;
                case 'archivo':
                    if ($Peticion->hasFile($Nombre)) {
                        Servicio_Archivos::Eliminar_Archivo($Registro->{$Nombre});
                        $Archivo_Guardado = Servicio_Archivos::Guardar_Archivo($Peticion->file($Nombre), $Campo['Carpeta']);
                        $Registro->{$Nombre} = $Archivo_Guardado['Ruta'];
                        $Columnas_Extra = ['Guardar_Nombre_En' => 'Nombre', 'Guardar_Tamano_En' => 'Tamano', 'Guardar_Codigo_En' => 'Codigo_Verificacion'];
                        foreach ($Columnas_Extra as $Clave_Campo => $Dato_Archivo) {
                            if (isset($Campo[$Clave_Campo])) {
                                $Registro->{$Campo[$Clave_Campo]} = $Archivo_Guardado[$Dato_Archivo];
                            }
                        }
                    } elseif ($Peticion->boolean($Nombre.'_quitar')) {
                        Servicio_Archivos::Eliminar_Archivo($Registro->{$Nombre});
                        $Registro->{$Nombre} = null;
                    }
                    break;
                case 'codigo_verificacion':
                    // Si en esta misma petición se subió un archivo, manda el código calculado en el servidor
                    $Campo_Archivo = collect($Recurso['Campos'])->first(fn ($Otro) => ($Otro['Guardar_Codigo_En'] ?? null) === $Nombre);
                    if (! ($Campo_Archivo && $Peticion->hasFile($Campo_Archivo['Nombre']))) {
                        $Registro->{$Nombre} = isset($Datos[$Nombre]) ? strtolower($Datos[$Nombre]) : null;
                    }
                    break;
                case 'si_no':
                    $Registro->{$Nombre} = $Peticion->boolean($Nombre);
                    break;
                default:
                    $Registro->{$Nombre} = $Datos[$Nombre] ?? ($Campo['Tipo'] === 'entero' ? ($Campo['Por_Defecto'] ?? null) : null);
            }
        }

        $Registro->save();

        if ($Unidades_Elegidas !== null && $Registro instanceof Modelo_Institucion) {
            $Unidades_A_Sincronizar = [];
            foreach ($Unidades_Elegidas as $Id_Unidad => $Unidad) {
                if (! empty($Unidad['activa'])) {
                    $Unidades_A_Sincronizar[$Id_Unidad] = ['nota' => $Unidad['nota'] ?? null, 'activa_desde' => $Unidad['activa_desde'] ?? null];
                }
            }
            $Registro->Unidades_Contratadas()->sync($Unidades_A_Sincronizar);
        }
    }

    private function Obtener_Reglas_Validacion(array $Campo, Model $Registro, array $Opciones_Permitidas): array
    {
        $Requerido = match (true) {
            $Campo['Obligatorio'] ?? false => 'required',
            isset($Campo['Obligatorio_Si']) => 'required_if:'.$Campo['Obligatorio_Si'],
            default => 'nullable',
        };
        $Valores_Permitidos = array_column($Opciones_Permitidas, 'Valor');
        $Reglas = match ($Campo['Tipo']) {
            'email' => [$Requerido, 'email', 'max:255'],
            'nombre_persona' => [$Requerido, 'string', 'max:120', "regex:/^[\\pL\\s'.-]+$/u"],
            'telefono' => [$Requerido, 'regex:/^\\+?[0-9]{6,15}$/'],
            'numerico' => [$Requerido, 'regex:/^[0-9]{1,20}$/'],
            'identificador' => [$Requerido, 'max:80', 'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/'],
            'codigo' => [$Requerido, 'regex:/^[A-Z0-9]{2,20}$/'],
            'enlace', 'enlace_video' => [$Requerido, 'url', 'max:500'],
            'texto_largo' => [$Requerido, 'string', 'max:10000'],
            'entero' => [$Requerido, 'integer', 'min:'.($Campo['Minimo'] ?? -1000000), 'max:'.($Campo['Maximo'] ?? 1000000)],
            'decimal' => [$Requerido, 'numeric', 'min:'.($Campo['Minimo'] ?? -999999999), 'max:999999999'],
            'fecha', 'fecha_hora' => [$Requerido, 'date'],
            'si_no' => ['nullable', 'boolean'],
            'lista' => [$Requerido, Rule::in($Valores_Permitidos)],
            'relacion' => [$Requerido, 'integer', Rule::in($Valores_Permitidos)],
            'archivo' => ['nullable', 'file', 'max:'.config('Configuracion_Portal.Tamano_Maximo_Subida_Kb'),
                'mimes:'.($Campo['Extensiones'] ?? 'pdf,doc,docx,ppt,pptx,xls,xlsx,xml,zip,png,jpg')],
            'contrasena' => [$Registro->exists ? 'nullable' : 'required', Regla_Contrasena::min(10)->letters()->numbers()],
            'codigo_verificacion' => ['nullable', 'regex:/^[a-fA-F0-9]{64}$/'],
            'unidades' => ['nullable', 'array'],
            default => [$Requerido, 'string', 'max:255'],
        };

        if ($Campo['Unico'] ?? false) {
            $Reglas[] = Rule::unique($Registro->getTable(), $Campo['Nombre'])->ignore($Registro->getKey());
        }
        if ($Campo['Tipo'] === 'enlace_video') {
            $Reglas[] = function ($Atributo, $Valor, $Fallar) {
                if ($Valor && ! Servicio_Enlaces_Video::Analizar_Enlace($Valor)) {
                    $Fallar('El enlace no es de Google Drive, YouTube ni Vimeo reconocible.');
                }
            };
        }

        return $Reglas;
    }

    /** Opciones de los campos "lista" y "relacion", con forma [Valor, Etiqueta]. El ejecutivo solo ve las suyas. */
    private function Obtener_Opciones_De_Listas(array $Campos, Modelo_Usuario $Usuario): array
    {
        $Ids_Instituciones = $Usuario->Obtener_Ids_Instituciones_A_Cargo();
        $Opciones = [];
        foreach ($Campos as $Campo) {
            if ($Campo['Tipo'] === 'relacion') {
                $Consulta = $Campo['Modelo']::query()->orderBy($Campo['Columna_Etiqueta']);
                if (isset($Campo['Filtro_Donde'])) {
                    $Consulta->whereIn($Campo['Filtro_Donde'][0], (array) $Campo['Filtro_Donde'][1]);
                }
                if ($Ids_Instituciones !== null && $Campo['Modelo'] === Modelo_Institucion::class) {
                    $Consulta->whereIn('id', $Ids_Instituciones);
                }
                if ($Ids_Instituciones !== null && $Campo['Modelo'] === Modelo_Proyecto::class) {
                    $Consulta->whereIn('institucion_id', $Ids_Instituciones);
                }
                $Opciones[$Campo['Nombre']] = $Consulta->get(['id', $Campo['Columna_Etiqueta']])
                    ->map(fn ($Registro) => ['Valor' => $Registro->id, 'Etiqueta' => $Registro->{$Campo['Columna_Etiqueta']}])->all();
            } elseif ($Campo['Tipo'] === 'lista') {
                $Opciones[$Campo['Nombre']] = collect($Campo['Opciones'])
                    ->map(fn ($Etiqueta, $Valor) => ['Valor' => $Valor, 'Etiqueta' => $Etiqueta])->values()->all();
            }
        }

        return $Opciones;
    }

    private function Formatear_Valor_Para_Tabla(Model $Registro, array $Campo, array $Opciones): mixed
    {
        $Valor = $Registro->{$Campo['Nombre']};

        return match ($Campo['Tipo']) {
            'relacion', 'lista' => collect($Opciones[$Campo['Nombre']] ?? [])->firstWhere('Valor', $Valor)['Etiqueta'] ?? ($Valor ?? '—'),
            'si_no' => $Valor ? 'Sí' : 'No',
            'fecha' => $Valor ? Carbon::parse($Valor)->format('d/m/Y') : '—',
            'fecha_hora' => $Valor ? Carbon::parse($Valor)->format('d/m/Y H:i') : '—',
            'texto_largo' => $Valor ? Str::limit($Valor, 80) : '—',
            default => $Valor ?? '—',
        };
    }
}
