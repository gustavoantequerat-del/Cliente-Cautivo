<?php

namespace App\Servicios;

use App\Modelos;

/**
 * Definición declarativa del CMS. Cada recurso genera su listado y su formulario
 * sin escribir una pantalla por tabla. Para agregar un campo: añadirlo aquí.
 *
 * Cada recurso lleva Icono (nombre de Componente_Icono), Titulo, Singular, Modelo, Columnas_Busqueda, Orden y Grupo.
 *
 * Claves de cada campo: Nombre (columna), Etiqueta, Tipo, Obligatorio, En_Tabla, Unico, Por_Defecto,
 * Ayuda, Solo_Lectura, Opciones, Minimo, Maximo, y según el tipo: Modelo, Columna_Etiqueta, Filtro_Donde
 * (relacion) o Carpeta, Guardar_Nombre_En, Guardar_Tamano_En, Guardar_Huella_En, Extensiones (archivo).
 *
 * Tipos de campo: texto, nombre_persona, email, telefono, numerico (solo dígitos), identificador (slug),
 * codigo (MAYÚSCULAS), enlace, texto_largo, entero, decimal, fecha, fecha_hora, lista, si_no, relacion,
 * archivo, contrasena, huella, enlace_video, unidades. El Frontend filtra lo que se teclea según el tipo
 * y el Backend lo vuelve a validar.
 */
class Servicio_Definicion_Cms
{
    public static function Obtener_Todos_Los_Recursos(): array
    {
        $Campo_Institucion = ['Tipo' => 'relacion', 'Modelo' => Modelos\Modelo_Institucion::class, 'Columna_Etiqueta' => 'nombre'];
        $Campo_Unidad = ['Tipo' => 'relacion', 'Modelo' => Modelos\Modelo_Unidad_Negocio::class, 'Columna_Etiqueta' => 'codigo'];
        $Campo_Proyecto = ['Tipo' => 'relacion', 'Modelo' => Modelos\Modelo_Proyecto::class, 'Columna_Etiqueta' => 'nombre'];
        $Lista_Desde_Valores = fn (array $Valores) => array_combine($Valores, $Valores);

        return [
            'instituciones' => [
                'Icono' => 'edificio', 'Titulo' => 'Instituciones', 'Singular' => 'institución', 'Modelo' => Modelos\Modelo_Institucion::class,
                'Columnas_Busqueda' => ['nombre', 'nit'], 'Orden' => ['nombre', 'asc'], 'Grupo' => 'Cuentas',
                'Campos' => [
                    ['Nombre' => 'nombre', 'Etiqueta' => 'Nombre', 'Tipo' => 'texto', 'Obligatorio' => true, 'En_Tabla' => true],
                    ['Nombre' => 'identificador', 'Etiqueta' => 'Identificador (slug)', 'Tipo' => 'identificador', 'Obligatorio' => true, 'Unico' => true,
                        'Ayuda' => 'Solo minúsculas, números y guiones. Ej.: banco-demo'],
                    ['Nombre' => 'razon_social', 'Etiqueta' => 'Razón social', 'Tipo' => 'texto'],
                    ['Nombre' => 'nit', 'Etiqueta' => 'NIT', 'Tipo' => 'numerico', 'En_Tabla' => true],
                    ['Nombre' => 'correo_facturacion', 'Etiqueta' => 'Correo de facturación', 'Tipo' => 'email'],
                    ['Nombre' => 'direccion_facturacion', 'Etiqueta' => 'Dirección de facturación', 'Tipo' => 'texto'],
                    ['Nombre' => 'ejecutivo_cuenta_id', 'Etiqueta' => 'Ejecutiva/o de cuenta', 'Tipo' => 'relacion', 'En_Tabla' => true,
                        'Modelo' => Modelos\Modelo_Usuario::class, 'Columna_Etiqueta' => 'nombre', 'Filtro_Donde' => ['rol', Modelos\Modelo_Usuario::Rol_Staff]],
                    ['Nombre' => 'puntos_alianza', 'Etiqueta' => 'Puntos de alianza', 'Tipo' => 'entero', 'Minimo' => 0, 'En_Tabla' => true, 'Por_Defecto' => 0],
                    ['Nombre' => 'esta_activa', 'Etiqueta' => 'Acceso activo', 'Tipo' => 'si_no', 'En_Tabla' => true, 'Por_Defecto' => true],
                    ['Nombre' => 'unidades_contratadas', 'Etiqueta' => 'Unidades contratadas', 'Tipo' => 'unidades'],
                ],
            ],
            'usuarios' => [
                'Icono' => 'usuarios', 'Titulo' => 'Usuarios y credenciales', 'Singular' => 'usuario', 'Modelo' => Modelos\Modelo_Usuario::class,
                'Columnas_Busqueda' => ['nombre', 'email', 'area'], 'Orden' => ['nombre', 'asc'], 'Grupo' => 'Cuentas',
                'Columna_Filtro' => 'institucion_id',
                'Campos' => [
                    ['Nombre' => 'nombre', 'Etiqueta' => 'Nombre completo', 'Tipo' => 'nombre_persona', 'Obligatorio' => true, 'En_Tabla' => true],
                    ['Nombre' => 'email', 'Etiqueta' => 'Correo', 'Tipo' => 'email', 'Obligatorio' => true, 'Unico' => true, 'En_Tabla' => true],
                    ['Nombre' => 'password', 'Etiqueta' => 'Contraseña', 'Tipo' => 'contrasena',
                        'Ayuda' => 'Mínimo 10 caracteres. Déjela vacía al editar para no cambiarla.'],
                    ['Nombre' => 'rol', 'Etiqueta' => 'Rol', 'Tipo' => 'lista', 'Obligatorio' => true, 'En_Tabla' => true,
                        'Por_Defecto' => Modelos\Modelo_Usuario::Rol_Funcionario, 'Opciones' => [
                            Modelos\Modelo_Usuario::Rol_Funcionario => 'Funcionario',
                            Modelos\Modelo_Usuario::Rol_Administrador_Institucion => 'Administrador de la institución',
                            Modelos\Modelo_Usuario::Rol_Staff => 'Staff Blockfinity (CMS)',
                        ]],
                    ['Nombre' => 'institucion_id', 'Etiqueta' => 'Institución', 'En_Tabla' => true,
                        'Ayuda' => 'Vacío solo para staff de Blockfinity.'] + $Campo_Institucion,
                    ['Nombre' => 'area', 'Etiqueta' => 'Área', 'Tipo' => 'texto', 'En_Tabla' => true],
                    ['Nombre' => 'cargo', 'Etiqueta' => 'Cargo', 'Tipo' => 'texto'],
                    ['Nombre' => 'telefono', 'Etiqueta' => 'Teléfono', 'Tipo' => 'telefono', 'Ayuda' => 'Solo números, con + opcional al inicio. Ej.: +59170000000'],
                    ['Nombre' => 'enlace_agenda', 'Etiqueta' => 'Enlace para agendar (Calendly, Google)', 'Tipo' => 'enlace',
                        'Ayuda' => 'Solo para ejecutivas/os de cuenta.'],
                    ['Nombre' => 'numero_credencial', 'Etiqueta' => 'N.º de credencial', 'Tipo' => 'texto', 'En_Tabla' => true, 'Solo_Lectura' => true],
                    ['Nombre' => 'esta_activo', 'Etiqueta' => 'Credencial activa', 'Tipo' => 'si_no', 'En_Tabla' => true, 'Por_Defecto' => true,
                        'Ayuda' => 'Al desactivarla se cierra su sesión y deja de verificarse el QR.'],
                ],
            ],
            'unidades' => [
                'Icono' => 'capas', 'Titulo' => 'Unidades de negocio', 'Singular' => 'unidad', 'Modelo' => Modelos\Modelo_Unidad_Negocio::class,
                'Columnas_Busqueda' => ['codigo', 'nombre'], 'Orden' => ['orden', 'asc'], 'Grupo' => 'Cuentas',
                'Campos' => [
                    ['Nombre' => 'codigo', 'Etiqueta' => 'Código', 'Tipo' => 'codigo', 'Obligatorio' => true, 'Unico' => true, 'En_Tabla' => true],
                    ['Nombre' => 'nombre', 'Etiqueta' => 'Nombre', 'Tipo' => 'texto', 'Obligatorio' => true, 'En_Tabla' => true],
                    ['Nombre' => 'descripcion', 'Etiqueta' => 'Descripción', 'Tipo' => 'texto_largo'],
                    ['Nombre' => 'orden', 'Etiqueta' => 'Orden', 'Tipo' => 'entero', 'Minimo' => 0, 'Por_Defecto' => 0, 'En_Tabla' => true],
                ],
            ],
            'proyectos' => [
                'Icono' => 'proyectos', 'Titulo' => 'Proyectos', 'Singular' => 'proyecto', 'Modelo' => Modelos\Modelo_Proyecto::class,
                'Columnas_Busqueda' => ['nombre'], 'Orden' => ['created_at', 'desc'], 'Grupo' => 'Relación', 'Columna_Filtro' => 'institucion_id',
                'Campos' => [
                    ['Nombre' => 'institucion_id', 'Etiqueta' => 'Institución', 'Obligatorio' => true, 'En_Tabla' => true] + $Campo_Institucion,
                    ['Nombre' => 'unidad_negocio_id', 'Etiqueta' => 'Unidad', 'En_Tabla' => true] + $Campo_Unidad,
                    ['Nombre' => 'nombre', 'Etiqueta' => 'Nombre', 'Tipo' => 'texto', 'Obligatorio' => true, 'En_Tabla' => true],
                    ['Nombre' => 'descripcion', 'Etiqueta' => 'Descripción', 'Tipo' => 'texto_largo'],
                    ['Nombre' => 'estado', 'Etiqueta' => 'Estado', 'Tipo' => 'lista', 'Obligatorio' => true, 'En_Tabla' => true, 'Por_Defecto' => 'activo',
                        'Opciones' => ['activo' => 'En ejecución', 'cerrado' => 'Cerrado']],
                    ['Nombre' => 'avance', 'Etiqueta' => 'Avance (%)', 'Tipo' => 'entero', 'Minimo' => 0, 'Maximo' => 100, 'Por_Defecto' => 0, 'En_Tabla' => true],
                    ['Nombre' => 'proximo_hito', 'Etiqueta' => 'Próximo hito', 'Tipo' => 'texto'],
                    ['Nombre' => 'fecha_inicio', 'Etiqueta' => 'Inicio', 'Tipo' => 'fecha'],
                    ['Nombre' => 'fecha_fin', 'Etiqueta' => 'Fin estimado', 'Tipo' => 'fecha'],
                ],
            ],
            'fases' => [
                'Icono' => 'lista', 'Titulo' => 'Fases de proyecto', 'Singular' => 'fase', 'Modelo' => Modelos\Modelo_Fase_Proyecto::class,
                'Columnas_Busqueda' => ['nombre'], 'Orden' => ['proyecto_id', 'asc'], 'Grupo' => 'Relación', 'Columna_Filtro' => 'proyecto_id',
                'Campos' => [
                    ['Nombre' => 'proyecto_id', 'Etiqueta' => 'Proyecto', 'Obligatorio' => true, 'En_Tabla' => true] + $Campo_Proyecto,
                    ['Nombre' => 'posicion', 'Etiqueta' => 'N.º de fase', 'Tipo' => 'entero', 'Minimo' => 1, 'Maximo' => 50, 'Por_Defecto' => 1, 'En_Tabla' => true],
                    ['Nombre' => 'nombre', 'Etiqueta' => 'Nombre', 'Tipo' => 'texto', 'Obligatorio' => true, 'En_Tabla' => true],
                    ['Nombre' => 'estado', 'Etiqueta' => 'Estado', 'Tipo' => 'lista', 'Obligatorio' => true, 'En_Tabla' => true, 'Por_Defecto' => 'pendiente',
                        'Opciones' => ['completada' => 'Completada', 'en_curso' => 'En curso', 'pendiente' => 'Pendiente']],
                    ['Nombre' => 'fecha_limite', 'Etiqueta' => 'Fecha objetivo', 'Tipo' => 'fecha', 'En_Tabla' => true],
                ],
            ],
            'bitacora' => [
                'Icono' => 'libro', 'Titulo' => 'Bitácora de proyectos', 'Singular' => 'entrada', 'Modelo' => Modelos\Modelo_Bitacora_Proyecto::class,
                'Columnas_Busqueda' => ['detalle'], 'Orden' => ['fecha', 'desc'], 'Grupo' => 'Relación', 'Columna_Filtro' => 'proyecto_id',
                'Campos' => [
                    ['Nombre' => 'proyecto_id', 'Etiqueta' => 'Proyecto', 'Obligatorio' => true, 'En_Tabla' => true] + $Campo_Proyecto,
                    ['Nombre' => 'fecha', 'Etiqueta' => 'Fecha', 'Tipo' => 'fecha', 'Obligatorio' => true, 'En_Tabla' => true],
                    ['Nombre' => 'detalle', 'Etiqueta' => 'Detalle', 'Tipo' => 'texto_largo', 'Obligatorio' => true, 'En_Tabla' => true],
                ],
            ],
            'entregables' => [
                'Icono' => 'boveda', 'Titulo' => 'Entregables', 'Singular' => 'entregable', 'Modelo' => Modelos\Modelo_Entregable::class,
                'Columnas_Busqueda' => ['titulo'], 'Orden' => ['created_at', 'desc'], 'Grupo' => 'Relación', 'Columna_Filtro' => 'institucion_id',
                'Campos' => [
                    ['Nombre' => 'institucion_id', 'Etiqueta' => 'Institución', 'Obligatorio' => true, 'En_Tabla' => true] + $Campo_Institucion,
                    ['Nombre' => 'proyecto_id', 'Etiqueta' => 'Proyecto'] + $Campo_Proyecto,
                    ['Nombre' => 'unidad_negocio_id', 'Etiqueta' => 'Unidad'] + $Campo_Unidad,
                    ['Nombre' => 'titulo', 'Etiqueta' => 'Título', 'Tipo' => 'texto', 'Obligatorio' => true, 'En_Tabla' => true],
                    ['Nombre' => 'tipo', 'Etiqueta' => 'Tipo', 'Tipo' => 'lista', 'Opciones' => Modelos\Modelo_Entregable::Tipos,
                        'Obligatorio' => true, 'Por_Defecto' => 'informe', 'En_Tabla' => true],
                    ['Nombre' => 'version', 'Etiqueta' => 'Versión', 'Tipo' => 'texto'],
                    ['Nombre' => 'etiqueta_fase', 'Etiqueta' => 'Fase (ej. F3)', 'Tipo' => 'texto'],
                    ['Nombre' => 'ruta_archivo', 'Etiqueta' => 'Archivo (PDF/DOCX/PPTX, máx. 10 MB)', 'Tipo' => 'archivo', 'Carpeta' => 'entregables',
                        'Guardar_Nombre_En' => 'nombre_archivo', 'Guardar_Tamano_En' => 'tamano_archivo', 'Guardar_Huella_En' => 'huella_sha256'],
                    ['Nombre' => 'enlace_externo', 'Etiqueta' => 'O enlace externo (Drive)', 'Tipo' => 'enlace',
                        'Ayuda' => 'Para archivos pesados. Calcule abajo la huella del archivo original para poder verificarlo.'],
                    ['Nombre' => 'huella_sha256', 'Etiqueta' => 'Huella SHA-256', 'Tipo' => 'huella',
                        'Ayuda' => 'Se calcula sola al subir un archivo. Para enlaces externos, use el botón para calcularla en su equipo.'],
                    ['Nombre' => 'estado', 'Etiqueta' => 'Estado', 'Tipo' => 'lista', 'Obligatorio' => true, 'En_Tabla' => true, 'Por_Defecto' => 'publicado',
                        'Opciones' => ['borrador' => 'Borrador (oculto)', 'pendiente_aprobacion' => 'Requiere aprobación del cliente',
                            'aprobado' => 'Aprobado', 'publicado' => 'Publicado']],
                    ['Nombre' => 'fecha_publicacion', 'Etiqueta' => 'Fecha de publicación', 'Tipo' => 'fecha_hora'],
                ],
            ],
            'research' => [
                'Icono' => 'grafico', 'Titulo' => 'Reportes de Research', 'Singular' => 'reporte', 'Modelo' => Modelos\Modelo_Reporte_Research::class,
                'Columnas_Busqueda' => ['titulo', 'autor'], 'Orden' => ['fecha_publicacion', 'desc'], 'Grupo' => 'Contenido',
                'Campos' => [
                    ['Nombre' => 'titulo', 'Etiqueta' => 'Título', 'Tipo' => 'texto', 'Obligatorio' => true, 'En_Tabla' => true],
                    ['Nombre' => 'resumen', 'Etiqueta' => 'Resumen', 'Tipo' => 'texto_largo'],
                    ['Nombre' => 'tema', 'Etiqueta' => 'Tema', 'Tipo' => 'lista', 'Obligatorio' => true, 'En_Tabla' => true,
                        'Opciones' => $Lista_Desde_Valores(Modelos\Modelo_Reporte_Research::Temas)],
                    ['Nombre' => 'autor', 'Etiqueta' => 'Autor/a', 'Tipo' => 'texto'],
                    ['Nombre' => 'minutos_lectura', 'Etiqueta' => 'Minutos de lectura', 'Tipo' => 'entero', 'Minimo' => 1, 'Maximo' => 600],
                    ['Nombre' => 'institucion_id', 'Etiqueta' => 'Exclusivo para', 'En_Tabla' => true,
                        'Ayuda' => 'Vacío = visible para todas las instituciones con Research.'] + $Campo_Institucion,
                    ['Nombre' => 'ruta_archivo', 'Etiqueta' => 'Archivo PDF (máx. 10 MB)', 'Tipo' => 'archivo', 'Carpeta' => 'research',
                        'Guardar_Nombre_En' => 'nombre_archivo'],
                    ['Nombre' => 'enlace_externo', 'Etiqueta' => 'O enlace externo (Drive, tablero)', 'Tipo' => 'enlace'],
                    ['Nombre' => 'es_destacado', 'Etiqueta' => 'Destacado', 'Tipo' => 'si_no', 'En_Tabla' => true],
                    ['Nombre' => 'esta_publicado', 'Etiqueta' => 'Publicado', 'Tipo' => 'si_no', 'Por_Defecto' => true, 'En_Tabla' => true],
                    ['Nombre' => 'fecha_publicacion', 'Etiqueta' => 'Fecha de publicación', 'Tipo' => 'fecha', 'En_Tabla' => true],
                ],
            ],
            'videos' => [
                'Icono' => 'reproducir', 'Titulo' => 'Videos y webinars', 'Singular' => 'video', 'Modelo' => Modelos\Modelo_Video::class,
                'Columnas_Busqueda' => ['titulo', 'expositor'], 'Orden' => ['created_at', 'desc'], 'Grupo' => 'Contenido',
                'Campos' => [
                    ['Nombre' => 'formato', 'Etiqueta' => 'Formato', 'Tipo' => 'lista', 'Obligatorio' => true, 'En_Tabla' => true, 'Por_Defecto' => 'grabado',
                        'Opciones' => ['en_vivo' => 'Webinar en vivo', 'grabado' => 'Grabado', 'pildora' => 'Píldora']],
                    ['Nombre' => 'titulo', 'Etiqueta' => 'Título', 'Tipo' => 'texto', 'Obligatorio' => true, 'En_Tabla' => true],
                    ['Nombre' => 'descripcion', 'Etiqueta' => 'Descripción', 'Tipo' => 'texto_largo'],
                    ['Nombre' => 'enlace_original', 'Etiqueta' => 'Enlace del video (Drive, YouTube o Vimeo)', 'Tipo' => 'enlace_video',
                        'Ayuda' => 'Pegue el enlace de compartir. En Drive: "Cualquier persona con el enlace". Para webinars en vivo puede quedar vacío hasta tener la grabación.'],
                    ['Nombre' => 'tema', 'Etiqueta' => 'Tema', 'Tipo' => 'lista', 'En_Tabla' => true,
                        'Opciones' => $Lista_Desde_Valores(Modelos\Modelo_Video::Temas)],
                    ['Nombre' => 'expositor', 'Etiqueta' => 'Expositor/es', 'Tipo' => 'texto'],
                    ['Nombre' => 'duracion_minutos', 'Etiqueta' => 'Duración (min)', 'Tipo' => 'entero', 'Minimo' => 1, 'Maximo' => 1440],
                    ['Nombre' => 'fecha_programada', 'Etiqueta' => 'Fecha y hora (en vivo)', 'Tipo' => 'fecha_hora', 'En_Tabla' => true],
                    ['Nombre' => 'enlace_sala', 'Etiqueta' => 'Enlace de la sala (Meet/Zoom/Teams)', 'Tipo' => 'enlace'],
                    ['Nombre' => 'enlace_miniatura', 'Etiqueta' => 'Miniatura (URL de imagen, opcional)', 'Tipo' => 'enlace'],
                    ['Nombre' => 'puntos', 'Etiqueta' => 'Puntos de alianza al completarlo', 'Tipo' => 'entero', 'Minimo' => 0, 'Maximo' => 1000, 'Por_Defecto' => 10],
                    ['Nombre' => 'institucion_id', 'Etiqueta' => 'Exclusivo para', 'Ayuda' => 'Vacío = visible para todos.'] + $Campo_Institucion,
                    ['Nombre' => 'esta_publicado', 'Etiqueta' => 'Publicado', 'Tipo' => 'si_no', 'Por_Defecto' => true, 'En_Tabla' => true],
                ],
            ],
            'facturas' => [
                'Icono' => 'facturacion', 'Titulo' => 'Facturas', 'Singular' => 'factura', 'Modelo' => Modelos\Modelo_Factura::class,
                'Columnas_Busqueda' => ['numero', 'concepto'], 'Orden' => ['fecha_emision', 'desc'], 'Grupo' => 'Administración', 'Columna_Filtro' => 'institucion_id',
                'Campos' => [
                    ['Nombre' => 'institucion_id', 'Etiqueta' => 'Institución', 'Obligatorio' => true, 'En_Tabla' => true] + $Campo_Institucion,
                    ['Nombre' => 'numero', 'Etiqueta' => 'N.º de factura', 'Tipo' => 'texto', 'Obligatorio' => true, 'En_Tabla' => true],
                    ['Nombre' => 'concepto', 'Etiqueta' => 'Concepto', 'Tipo' => 'texto', 'Obligatorio' => true, 'En_Tabla' => true],
                    ['Nombre' => 'fecha_emision', 'Etiqueta' => 'Emisión', 'Tipo' => 'fecha', 'Obligatorio' => true, 'En_Tabla' => true],
                    ['Nombre' => 'fecha_vencimiento', 'Etiqueta' => 'Vencimiento', 'Tipo' => 'fecha'],
                    ['Nombre' => 'monto', 'Etiqueta' => 'Monto', 'Tipo' => 'decimal', 'Minimo' => 0, 'Obligatorio' => true, 'En_Tabla' => true],
                    ['Nombre' => 'moneda', 'Etiqueta' => 'Moneda', 'Tipo' => 'lista', 'Opciones' => ['BOB' => 'Bolivianos', 'USD' => 'Dólares'], 'Por_Defecto' => 'BOB'],
                    ['Nombre' => 'estado', 'Etiqueta' => 'Estado', 'Tipo' => 'lista', 'Obligatorio' => true, 'En_Tabla' => true, 'Por_Defecto' => 'pendiente',
                        'Opciones' => ['pendiente' => 'Pendiente', 'pagada' => 'Pagada', 'anulada' => 'Anulada']],
                    ['Nombre' => 'fecha_pago', 'Etiqueta' => 'Fecha de pago', 'Tipo' => 'fecha'],
                    ['Nombre' => 'ruta_pdf', 'Etiqueta' => 'PDF de la factura', 'Tipo' => 'archivo', 'Carpeta' => 'facturas', 'Extensiones' => 'pdf'],
                    ['Nombre' => 'ruta_xml', 'Etiqueta' => 'XML (SIAT)', 'Tipo' => 'archivo', 'Carpeta' => 'facturas', 'Extensiones' => 'xml'],
                ],
            ],
            'contratos' => [
                'Icono' => 'archivo', 'Titulo' => 'Contratos y presupuestos', 'Singular' => 'contrato', 'Modelo' => Modelos\Modelo_Contrato::class,
                'Columnas_Busqueda' => ['titulo'], 'Orden' => ['fecha_firma', 'desc'], 'Grupo' => 'Administración', 'Columna_Filtro' => 'institucion_id',
                'Campos' => [
                    ['Nombre' => 'institucion_id', 'Etiqueta' => 'Institución', 'Obligatorio' => true, 'En_Tabla' => true] + $Campo_Institucion,
                    ['Nombre' => 'unidad_negocio_id', 'Etiqueta' => 'Unidad', 'En_Tabla' => true] + $Campo_Unidad,
                    ['Nombre' => 'tipo', 'Etiqueta' => 'Tipo', 'Tipo' => 'lista', 'Obligatorio' => true, 'Por_Defecto' => 'contrato', 'En_Tabla' => true,
                        'Opciones' => ['contrato' => 'Contrato', 'presupuesto' => 'Presupuesto firmado']],
                    ['Nombre' => 'titulo', 'Etiqueta' => 'Título', 'Tipo' => 'texto', 'Obligatorio' => true, 'En_Tabla' => true],
                    ['Nombre' => 'fecha_firma', 'Etiqueta' => 'Fecha de firma', 'Tipo' => 'fecha', 'En_Tabla' => true],
                    ['Nombre' => 'fecha_vencimiento', 'Etiqueta' => 'Vigencia hasta', 'Tipo' => 'fecha'],
                    ['Nombre' => 'monto', 'Etiqueta' => 'Monto', 'Tipo' => 'decimal', 'Minimo' => 0],
                    ['Nombre' => 'ruta_archivo', 'Etiqueta' => 'Archivo (PDF)', 'Tipo' => 'archivo', 'Carpeta' => 'contratos',
                        'Guardar_Nombre_En' => 'nombre_archivo', 'Guardar_Huella_En' => 'huella_sha256'],
                    ['Nombre' => 'enlace_externo', 'Etiqueta' => 'O enlace externo', 'Tipo' => 'enlace'],
                    ['Nombre' => 'huella_sha256', 'Etiqueta' => 'Huella SHA-256', 'Tipo' => 'huella'],
                ],
            ],
            'horas' => [
                'Icono' => 'dinero', 'Titulo' => 'Movimientos de horas', 'Singular' => 'movimiento', 'Modelo' => Modelos\Modelo_Movimiento_Horas::class,
                'Columnas_Busqueda' => ['descripcion'], 'Orden' => ['created_at', 'desc'], 'Grupo' => 'Administración', 'Columna_Filtro' => 'institucion_id',
                'Campos' => [
                    ['Nombre' => 'institucion_id', 'Etiqueta' => 'Institución', 'Obligatorio' => true, 'En_Tabla' => true] + $Campo_Institucion,
                    ['Nombre' => 'horas', 'Etiqueta' => 'Horas (+ abono / − consumo)', 'Tipo' => 'decimal', 'Obligatorio' => true, 'En_Tabla' => true],
                    ['Nombre' => 'descripcion', 'Etiqueta' => 'Detalle', 'Tipo' => 'texto', 'Obligatorio' => true, 'En_Tabla' => true],
                    ['Nombre' => 'fecha_vencimiento', 'Etiqueta' => 'Vencen el (solo abonos)', 'Tipo' => 'fecha', 'En_Tabla' => true],
                ],
            ],
        ];
    }

    public static function Obtener_Recurso(string $Clave_Recurso): array
    {
        $Recursos = static::Obtener_Todos_Los_Recursos();
        abort_unless(isset($Recursos[$Clave_Recurso]), 404);

        return $Recursos[$Clave_Recurso] + ['Clave' => $Clave_Recurso];
    }
}
