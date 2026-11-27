<?php

namespace App\Servicios;

use App\Modelos;
use App\Modelos\Modelo_Usuario;
use Illuminate\Database\Eloquent\Builder;

/**
 * Definición declarativa del CMS. Cada recurso genera su listado y su formulario
 * sin escribir una pantalla por tabla. Para agregar un campo: añadirlo aquí.
 *
 * Cada recurso lleva Icono (nombre de Componente_Icono), Titulo, Singular, Modelo, Columnas_Busqueda, Orden y Grupo.
 * Un campo con Visible_Si => [campo, valor] solo se muestra en el formulario cuando ese campo tiene ese valor.
 * Opcionales: Permite_Crear / Permite_Eliminar (false para ajustes fijos), Eliminar_Solo_Superadmin,
 * Vista_Previa ('marca' en instituciones). Un campo con Solo_Superadmin no se le muestra al usuario ejecutivo.
 * Además: Solo_Superadmin (contenido global que el usuario ejecutivo no ve) o Alcance (cómo se filtra por
 * institución para el ejecutivo: 'institucion' por la columna institucion_id, 'propia' por su id).
 *
 * Claves de cada campo: Nombre (columna), Etiqueta, Tipo, Obligatorio, En_Tabla, Unico, Por_Defecto,
 * Ayuda, Solo_Lectura, Opciones, Minimo, Maximo, y según el tipo: Modelo, Columna_Etiqueta, Filtro_Donde
 * (relacion) o Carpeta, Guardar_Nombre_En, Guardar_Tamano_En, Guardar_Codigo_En, Extensiones (archivo).
 *
 * Tipos de campo: texto, nombre_persona, email, telefono, color (#RRGGBB), enlace_drive (enlace de Google Drive), numerico (solo dígitos), identificador (slug),
 * codigo (MAYÚSCULAS), enlace, texto_largo, entero, decimal, fecha, fecha_hora, lista, si_no, relacion,
 * archivo, contrasena, codigo_verificacion, enlace_video, unidades. El Frontend filtra lo que se teclea según el tipo
 * y el Backend lo vuelve a validar.
 */
class Servicio_Definicion_Cms
{
    public static function Obtener_Todos_Los_Recursos(): array
    {
        $Campo_Institucion = ['Tipo' => 'relacion', 'Modelo' => Modelos\Modelo_Institucion::class, 'Columna_Etiqueta' => 'nombre'];
        $Campo_Unidad = ['Tipo' => 'relacion', 'Modelo' => Modelos\Modelo_Unidad_Negocio::class, 'Columna_Etiqueta' => 'nombre'];
        $Lista_Desde_Valores = fn (array $Valores) => array_combine($Valores, $Valores);

        return [
            'instituciones' => [
                // El usuario ejecutivo ve y edita solo las suyas (y las que crea quedan a su cargo); eliminar, solo el superadmin
                'Alcance' => 'propia', 'Eliminar_Solo_Superadmin' => true, 'Vista_Previa' => 'marca',
                'Icono' => 'edificio', 'Titulo' => 'Instituciones', 'Singular' => 'institución', 'Modelo' => Modelos\Modelo_Institucion::class,
                'Columnas_Busqueda' => ['nombre', 'nit'], 'Orden' => ['nombre', 'asc'], 'Grupo' => 'Cuentas',
                'Campos' => [
                    ['Nombre' => 'nombre', 'Etiqueta' => 'Nombre', 'Tipo' => 'texto', 'Obligatorio' => true, 'En_Tabla' => true],
                    ['Nombre' => 'identificador', 'Etiqueta' => 'Identificador (slug)', 'Tipo' => 'identificador', 'Obligatorio' => true, 'Unico' => true,
                        'Ayuda' => 'Solo minúsculas, números y guiones. Ej.: banco-nacional-de-bolivia'],
                    ['Nombre' => 'razon_social', 'Etiqueta' => 'Razón social', 'Tipo' => 'texto'],
                    ['Nombre' => 'nit', 'Etiqueta' => 'NIT', 'Tipo' => 'numerico', 'En_Tabla' => true],
                    ['Nombre' => 'correo_facturacion', 'Etiqueta' => 'Correo de facturación', 'Tipo' => 'email'],
                    ['Nombre' => 'direccion_facturacion', 'Etiqueta' => 'Dirección de facturación', 'Tipo' => 'texto'],
                    ['Nombre' => 'ejecutivo_cuenta_id', 'Etiqueta' => 'Usuario ejecutivo a cargo', 'Tipo' => 'relacion', 'En_Tabla' => true, 'Solo_Superadmin' => true,
                        'Modelo' => Modelos\Modelo_Usuario::class, 'Columna_Etiqueta' => 'nombre', 'Filtro_Donde' => ['rol', [Modelos\Modelo_Usuario::Rol_Usuario_Ejecutivo, Modelos\Modelo_Usuario::Rol_Superadmin]]],
                    ['Nombre' => 'esta_activa', 'Etiqueta' => 'Acceso activo', 'Tipo' => 'si_no', 'En_Tabla' => true, 'Por_Defecto' => true],
                    ['Nombre' => 'unidades_contratadas', 'Etiqueta' => 'Productos contratados (definen el nivel de alianza)', 'Tipo' => 'unidades'],
                    ['Nombre' => 'color_primario', 'Etiqueta' => 'Color principal del portal', 'Tipo' => 'color', 'Obligatorio' => true, 'Por_Defecto' => '#1F3F73',
                        'Ayuda' => 'Menú, botones y encabezados del portal de esta institución.'],
                    ['Nombre' => 'color_secundario', 'Etiqueta' => 'Color de acento', 'Tipo' => 'color', 'Obligatorio' => true, 'Por_Defecto' => '#5AA9E6'],
                    ['Nombre' => 'ruta_logo', 'Etiqueta' => 'Logo de la institución (PNG, JPG o WEBP)', 'Tipo' => 'archivo', 'Carpeta' => 'logos',
                        'Extensiones' => 'png,jpg,jpeg,webp', 'Ayuda' => 'Se muestra en el portal junto al logo de Blockfinity.'],
                ],
            ],
            'certificados' => [
                'Alcance' => 'institucion', 'Icono' => 'certificado', 'Titulo' => 'Certificados', 'Singular' => 'certificado', 'Modelo' => Modelos\Modelo_Certificado::class,
                'Columnas_Busqueda' => ['titulo'], 'Orden' => ['created_at', 'desc'], 'Grupo' => 'Relación',
                'Campos' => [
                    ['Nombre' => 'usuario_id', 'Etiqueta' => 'Persona', 'Tipo' => 'relacion', 'Obligatorio' => true, 'En_Tabla' => true,
                        'Modelo' => Modelos\Modelo_Usuario::class, 'Columna_Etiqueta' => 'nombre',
                        'Filtro_Donde' => ['rol', [Modelos\Modelo_Usuario::Rol_Usuario_Institucional, Modelos\Modelo_Usuario::Rol_Administrador_Delegado]]],
                    ['Nombre' => 'titulo', 'Etiqueta' => 'Título', 'Tipo' => 'texto', 'Obligatorio' => true, 'En_Tabla' => true],
                    ['Nombre' => 'descripcion', 'Etiqueta' => 'Descripción', 'Tipo' => 'texto_largo'],
                    ['Nombre' => 'puntaje', 'Etiqueta' => 'Puntaje obtenido', 'Tipo' => 'entero', 'Minimo' => 0, 'Maximo' => 10000,
                        'Por_Defecto' => 0, 'En_Tabla' => true],
                    ['Nombre' => 'ruta_archivo', 'Etiqueta' => 'Certificado (PDF)', 'Tipo' => 'archivo', 'Obligatorio' => true, 'Carpeta' => 'certificados',
                        'Extensiones' => 'pdf', 'Guardar_Nombre_En' => 'nombre_archivo', 'Guardar_Codigo_En' => 'codigo_verificacion'],
                ],
            ],
            'usuarios' => [
                'Alcance' => 'institucion', 'Icono' => 'usuarios', 'Titulo' => 'Usuarios y credenciales', 'Singular' => 'usuario', 'Modelo' => Modelos\Modelo_Usuario::class,
                'Columnas_Busqueda' => ['nombre', 'email', 'area'], 'Orden' => ['nombre', 'asc'], 'Grupo' => 'Cuentas',
                'Columna_Filtro' => 'institucion_id',
                'Campos' => [
                    ['Nombre' => 'nombre', 'Etiqueta' => 'Nombre completo', 'Tipo' => 'nombre_persona', 'Obligatorio' => true, 'En_Tabla' => true],
                    ['Nombre' => 'email', 'Etiqueta' => 'Correo', 'Tipo' => 'email', 'Obligatorio' => true, 'Unico' => true, 'En_Tabla' => true],
                    ['Nombre' => 'password', 'Etiqueta' => 'Contraseña', 'Tipo' => 'contrasena',
                        'Ayuda' => 'Mínimo 10 caracteres. Déjela vacía al editar para no cambiarla.'],
                    ['Nombre' => 'rol', 'Etiqueta' => 'Rol', 'Tipo' => 'lista', 'Obligatorio' => true, 'En_Tabla' => true,
                        'Por_Defecto' => Modelos\Modelo_Usuario::Rol_Usuario_Institucional, 'Opciones' => Modelos\Modelo_Usuario::Roles,
                        // El usuario ejecutivo solo crea clientes de sus instituciones
                        'Opciones_Para_Ejecutivo' => [Modelos\Modelo_Usuario::Rol_Usuario_Institucional, Modelos\Modelo_Usuario::Rol_Administrador_Delegado]],
                    ['Nombre' => 'institucion_id', 'Etiqueta' => 'Institución', 'En_Tabla' => true,
                        'Obligatorio_Si' => 'rol,'.Modelos\Modelo_Usuario::Rol_Usuario_Institucional.','.Modelos\Modelo_Usuario::Rol_Administrador_Delegado,
                        'Ayuda' => 'Vacío solo para el personal de Blockfinity.'] + $Campo_Institucion,
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
            'ecosistema' => [
                'Solo_Superadmin' => true, 'Icono' => 'capas', 'Titulo' => 'Ecosistema Blockfinity', 'Singular' => 'producto', 'Modelo' => Modelos\Modelo_Unidad_Negocio::class,
                'Columnas_Busqueda' => ['codigo', 'nombre'], 'Orden' => ['orden', 'asc'], 'Grupo' => 'Ecosistema',
                'Campos' => [
                    ['Nombre' => 'nombre', 'Etiqueta' => 'Título', 'Tipo' => 'texto', 'Obligatorio' => true, 'En_Tabla' => true],
                    ['Nombre' => 'codigo', 'Etiqueta' => 'Código', 'Tipo' => 'codigo', 'Obligatorio' => true, 'Unico' => true, 'En_Tabla' => true,
                        'Ayuda' => 'Corto y en mayúsculas, ej. BCS. RESEARCH habilita la sección Research del portal.'],
                    ['Nombre' => 'subtitulo', 'Etiqueta' => 'Subtítulo', 'Tipo' => 'texto', 'En_Tabla' => true],
                    ['Nombre' => 'descripcion', 'Etiqueta' => 'Descripción', 'Tipo' => 'texto_largo'],
                    ['Nombre' => 'ruta_imagen', 'Etiqueta' => 'Imagen (PNG, JPG o WEBP)', 'Tipo' => 'archivo', 'Carpeta' => 'ecosistema',
                        'Extensiones' => 'png,jpg,jpeg,webp', 'Ayuda' => 'Horizontal, idealmente 1200 × 675 px.'],
                    ['Nombre' => 'enlace_pagina', 'Etiqueta' => 'Página del producto (landing, https)', 'Tipo' => 'enlace', 'Solo_Https' => true,
                        'Ayuda' => 'El botón "Ver más" abre esta página en una pestaña nueva.'],
                    ['Nombre' => 'enlace_video', 'Etiqueta' => 'Video explicativo (opcional)', 'Tipo' => 'enlace_video',
                        'Ayuda' => 'Drive, YouTube o Vimeo. Se muestra en el detalle del producto.'],
                    ['Nombre' => 'orden', 'Etiqueta' => 'Orden', 'Tipo' => 'entero', 'Minimo' => 0, 'Por_Defecto' => 0, 'En_Tabla' => true],
                    ['Nombre' => 'esta_publicado', 'Etiqueta' => 'Visible en el portal', 'Tipo' => 'si_no', 'Por_Defecto' => true, 'En_Tabla' => true],
                ],
            ],
            'solicitudes-productos' => [
                'Alcance' => 'institucion', 'Icono' => 'correo', 'Titulo' => 'Solicitudes de productos', 'Singular' => 'solicitud',
                'Modelo' => Modelos\Modelo_Solicitud_Producto::class, 'Columnas_Busqueda' => ['comentario'], 'Orden' => ['created_at', 'desc'],
                'Grupo' => 'Ecosistema', 'Columna_Filtro' => 'estado', 'Permite_Crear' => false,
                'Campos' => [
                    ['Nombre' => 'institucion_id', 'Etiqueta' => 'Institución', 'Solo_Lectura' => true, 'En_Tabla' => true] + $Campo_Institucion,
                    ['Nombre' => 'unidad_negocio_id', 'Etiqueta' => 'Producto', 'Solo_Lectura' => true, 'En_Tabla' => true] + $Campo_Unidad,
                    ['Nombre' => 'usuario_id', 'Etiqueta' => 'Solicitado por', 'Tipo' => 'relacion', 'Solo_Lectura' => true, 'En_Tabla' => true,
                        'Modelo' => Modelos\Modelo_Usuario::class, 'Columna_Etiqueta' => 'nombre'],
                    ['Nombre' => 'comentario', 'Etiqueta' => 'Comentario', 'Tipo' => 'texto_largo', 'Solo_Lectura' => true],
                    ['Nombre' => 'created_at', 'Etiqueta' => 'Fecha', 'Tipo' => 'fecha_hora', 'Solo_Lectura' => true, 'En_Tabla' => true],
                    ['Nombre' => 'estado', 'Etiqueta' => 'Estado', 'Tipo' => 'lista', 'Obligatorio' => true, 'En_Tabla' => true, 'Por_Defecto' => 'pendiente',
                        'Opciones' => Modelos\Modelo_Solicitud_Producto::Estados,
                        'Ayuda' => 'Se marca "Habilitado" sola al activar el producto en la institución.'],
                ],
            ],
            'entregables' => [
                'Alcance' => 'institucion', 'Icono' => 'boveda', 'Titulo' => 'Entregables', 'Singular' => 'entregable', 'Modelo' => Modelos\Modelo_Entregable::class,
                'Columnas_Busqueda' => ['titulo'], 'Orden' => ['created_at', 'desc'], 'Grupo' => 'Relación', 'Columna_Filtro' => 'institucion_id',
                'Campos' => [
                    ['Nombre' => 'institucion_id', 'Etiqueta' => 'Institución', 'Obligatorio' => true, 'En_Tabla' => true] + $Campo_Institucion,
                    ['Nombre' => 'unidad_negocio_id', 'Etiqueta' => 'Producto', 'En_Tabla' => true] + $Campo_Unidad,
                    ['Nombre' => 'titulo', 'Etiqueta' => 'Título', 'Tipo' => 'texto', 'Obligatorio' => true, 'En_Tabla' => true],
                    ['Nombre' => 'tipo', 'Etiqueta' => 'Tipo', 'Tipo' => 'lista', 'Opciones' => Modelos\Modelo_Entregable::Tipos,
                        'Obligatorio' => true, 'Por_Defecto' => 'informe', 'En_Tabla' => true],
                    ['Nombre' => 'version', 'Etiqueta' => 'Versión', 'Tipo' => 'texto'],
                    ['Nombre' => 'etiqueta_fase', 'Etiqueta' => 'Fase (ej. F3)', 'Tipo' => 'texto'],
                    ['Nombre' => 'ruta_archivo', 'Etiqueta' => 'Archivo (PDF/DOCX/PPTX, máx. 20 MB)', 'Tipo' => 'archivo', 'Carpeta' => 'entregables',
                        'Guardar_Nombre_En' => 'nombre_archivo', 'Guardar_Tamano_En' => 'tamano_archivo', 'Guardar_Codigo_En' => 'codigo_verificacion'],
                    ['Nombre' => 'enlace_externo', 'Etiqueta' => 'O enlace externo (Drive)', 'Tipo' => 'enlace',
                        'Ayuda' => 'Para archivos pesados. Calcule abajo el código de verificación del archivo original.'],
                    ['Nombre' => 'codigo_verificacion', 'Etiqueta' => 'Código de verificación', 'Tipo' => 'codigo_verificacion',
                        'Ayuda' => 'Se calcula solo al subir un archivo. Para enlaces externos, use el botón para calcularlo en su equipo.'],
                    ['Nombre' => 'estado', 'Etiqueta' => 'Estado', 'Tipo' => 'lista', 'Obligatorio' => true, 'En_Tabla' => true, 'Por_Defecto' => 'publicado',
                        'Opciones' => ['borrador' => 'Borrador (oculto)', 'pendiente_aprobacion' => 'Requiere aprobación del cliente',
                            'aprobado' => 'Aprobado', 'publicado' => 'Publicado']],
                    ['Nombre' => 'fecha_publicacion', 'Etiqueta' => 'Fecha de publicación', 'Tipo' => 'fecha_hora'],
                ],
            ],
            'research' => [
                'Solo_Superadmin' => true, 'Icono' => 'grafico', 'Titulo' => 'Reportes de Research', 'Singular' => 'reporte', 'Modelo' => Modelos\Modelo_Reporte_Research::class,
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
                    ['Nombre' => 'ruta_archivo', 'Etiqueta' => 'Archivo PDF', 'Tipo' => 'archivo', 'Carpeta' => 'research', 'Extensiones' => 'pdf',
                        'Guardar_Nombre_En' => 'nombre_archivo', 'Ayuda' => 'Se lee dentro del portal con el lector de PDF del navegador.'],
                    ['Nombre' => 'es_destacado', 'Etiqueta' => 'Destacado', 'Tipo' => 'si_no', 'En_Tabla' => true],
                    ['Nombre' => 'esta_publicado', 'Etiqueta' => 'Publicado', 'Tipo' => 'si_no', 'Por_Defecto' => true, 'En_Tabla' => true],
                    ['Nombre' => 'fecha_publicacion', 'Etiqueta' => 'Fecha de publicación', 'Tipo' => 'fecha', 'En_Tabla' => true],
                ],
            ],
            'videos' => [
                'Solo_Superadmin' => true, 'Icono' => 'reproducir', 'Titulo' => 'Webinars y píldoras', 'Singular' => 'video', 'Modelo' => Modelos\Modelo_Video::class,
                'Columnas_Busqueda' => ['titulo', 'expositor'], 'Orden' => ['created_at', 'desc'], 'Grupo' => 'Contenido',
                'Campos' => [
                    ['Nombre' => 'formato', 'Etiqueta' => 'Formato', 'Tipo' => 'lista', 'Obligatorio' => true, 'En_Tabla' => true, 'Por_Defecto' => 'grabado',
                        'Opciones' => ['en_vivo' => 'Webinar en vivo', 'grabado' => 'Grabado', 'pildora' => 'Píldora']],
                    ['Nombre' => 'titulo', 'Etiqueta' => 'Título', 'Tipo' => 'texto', 'Obligatorio' => true, 'En_Tabla' => true],
                    ['Nombre' => 'descripcion', 'Etiqueta' => 'Descripción', 'Tipo' => 'texto_largo'],
                    ['Nombre' => 'tipo_contenido', 'Etiqueta' => 'Contenido', 'Tipo' => 'lista', 'Obligatorio' => true, 'En_Tabla' => true, 'Por_Defecto' => 'video',
                        'Opciones' => ['video' => 'Video', 'pdf' => 'PDF'], 'Ayuda' => 'El PDF se sube aquí y se lee dentro del portal.'],
                    ['Nombre' => 'ruta_archivo', 'Etiqueta' => 'Archivo PDF', 'Tipo' => 'archivo', 'Carpeta' => 'academia', 'Extensiones' => 'pdf',
                        'Guardar_Nombre_En' => 'nombre_archivo', 'Visible_Si' => ['tipo_contenido', 'pdf']],
                    ['Nombre' => 'enlace_original', 'Etiqueta' => 'Enlace del video (Drive, YouTube o Vimeo)', 'Tipo' => 'enlace_video', 'Visible_Si' => ['tipo_contenido', 'video'],
                        'Ayuda' => 'Pegue el enlace de compartir. En Drive: "Cualquier persona con el enlace". Para webinars en vivo puede quedar vacío hasta tener la grabación.'],
                    ['Nombre' => 'tema', 'Etiqueta' => 'Tema', 'Tipo' => 'lista', 'En_Tabla' => true,
                        'Opciones' => $Lista_Desde_Valores(Modelos\Modelo_Video::Temas)],
                    ['Nombre' => 'expositor', 'Etiqueta' => 'Expositor/es', 'Tipo' => 'texto'],
                    ['Nombre' => 'duracion_minutos', 'Etiqueta' => 'Duración o tiempo de lectura (min)', 'Tipo' => 'entero', 'Minimo' => 1, 'Maximo' => 1440],
                    ['Nombre' => 'fecha_programada', 'Etiqueta' => 'Fecha y hora (en vivo)', 'Tipo' => 'fecha_hora', 'En_Tabla' => true],
                    ['Nombre' => 'enlace_sala', 'Etiqueta' => 'Enlace de la sala (Meet/Zoom/Teams)', 'Tipo' => 'enlace'],
                    ['Nombre' => 'enlace_miniatura', 'Etiqueta' => 'Miniatura (URL de imagen, opcional)', 'Tipo' => 'enlace'],
                    ['Nombre' => 'institucion_id', 'Etiqueta' => 'Exclusivo para', 'Ayuda' => 'Vacío = visible para todos.'] + $Campo_Institucion,
                    ['Nombre' => 'esta_publicado', 'Etiqueta' => 'Publicado', 'Tipo' => 'si_no', 'Por_Defecto' => true, 'En_Tabla' => true],
                ],
            ],
            'facturas' => [
                'Alcance' => 'institucion', 'Icono' => 'facturacion', 'Titulo' => 'Facturas', 'Singular' => 'factura', 'Modelo' => Modelos\Modelo_Factura::class,
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
                'Alcance' => 'institucion', 'Icono' => 'archivo', 'Titulo' => 'Contratos y presupuestos', 'Singular' => 'contrato', 'Modelo' => Modelos\Modelo_Contrato::class,
                'Columnas_Busqueda' => ['titulo'], 'Orden' => ['fecha_firma', 'desc'], 'Grupo' => 'Administración', 'Columna_Filtro' => 'institucion_id',
                'Campos' => [
                    ['Nombre' => 'institucion_id', 'Etiqueta' => 'Institución', 'Obligatorio' => true, 'En_Tabla' => true] + $Campo_Institucion,
                    ['Nombre' => 'unidad_negocio_id', 'Etiqueta' => 'Producto', 'En_Tabla' => true] + $Campo_Unidad,
                    ['Nombre' => 'tipo', 'Etiqueta' => 'Tipo', 'Tipo' => 'lista', 'Obligatorio' => true, 'Por_Defecto' => 'contrato', 'En_Tabla' => true,
                        'Opciones' => ['contrato' => 'Contrato', 'presupuesto' => 'Presupuesto firmado']],
                    ['Nombre' => 'titulo', 'Etiqueta' => 'Título', 'Tipo' => 'texto', 'Obligatorio' => true, 'En_Tabla' => true],
                    ['Nombre' => 'fecha_firma', 'Etiqueta' => 'Fecha de firma', 'Tipo' => 'fecha', 'En_Tabla' => true],
                    ['Nombre' => 'fecha_vencimiento', 'Etiqueta' => 'Vigencia hasta', 'Tipo' => 'fecha'],
                    ['Nombre' => 'monto', 'Etiqueta' => 'Monto', 'Tipo' => 'decimal', 'Minimo' => 0],
                    ['Nombre' => 'ruta_archivo', 'Etiqueta' => 'Archivo (PDF)', 'Tipo' => 'archivo', 'Carpeta' => 'contratos',
                        'Guardar_Nombre_En' => 'nombre_archivo', 'Guardar_Codigo_En' => 'codigo_verificacion'],
                    ['Nombre' => 'enlace_externo', 'Etiqueta' => 'O enlace externo', 'Tipo' => 'enlace'],
                    ['Nombre' => 'codigo_verificacion', 'Etiqueta' => 'Código de verificación', 'Tipo' => 'codigo_verificacion'],
                ],
            ],
            'horas' => [
                'Alcance' => 'institucion', 'Icono' => 'dinero', 'Titulo' => 'Movimientos de horas', 'Singular' => 'movimiento', 'Modelo' => Modelos\Modelo_Movimiento_Horas::class,
                'Columnas_Busqueda' => ['descripcion'], 'Orden' => ['created_at', 'desc'], 'Grupo' => 'Administración', 'Columna_Filtro' => 'institucion_id',
                'Campos' => [
                    ['Nombre' => 'institucion_id', 'Etiqueta' => 'Institución', 'Obligatorio' => true, 'En_Tabla' => true] + $Campo_Institucion,
                    ['Nombre' => 'horas', 'Etiqueta' => 'Horas (+ abono / − consumo)', 'Tipo' => 'decimal', 'Obligatorio' => true, 'En_Tabla' => true],
                    ['Nombre' => 'descripcion', 'Etiqueta' => 'Detalle', 'Tipo' => 'texto', 'Obligatorio' => true, 'En_Tabla' => true],
                    ['Nombre' => 'fecha_vencimiento', 'Etiqueta' => 'Vencen el (solo abonos)', 'Tipo' => 'fecha', 'En_Tabla' => true],
                ],
            ],
            'configuracion' => [
                'Solo_Superadmin' => true, 'Icono' => 'lista', 'Titulo' => 'Configuración', 'Singular' => 'ajuste', 'Modelo' => Modelos\Modelo_Configuracion::class,
                'Columnas_Busqueda' => ['etiqueta'], 'Orden' => ['etiqueta', 'asc'], 'Grupo' => 'Administración',
                'Permite_Crear' => false, 'Permite_Eliminar' => false,
                'Campos' => [
                    ['Nombre' => 'etiqueta', 'Etiqueta' => 'Ajuste', 'Tipo' => 'texto', 'Solo_Lectura' => true, 'En_Tabla' => true],
                    ['Nombre' => 'valor', 'Etiqueta' => 'Valor', 'Tipo' => 'texto_largo', 'En_Tabla' => true],
                ],
            ],
        ];
    }

    /** Los recursos que este usuario puede usar, ya ajustados a su rol */
    public static function Obtener_Recursos_Permitidos(Modelo_Usuario $Usuario): array
    {
        $Recursos = [];
        foreach (static::Obtener_Todos_Los_Recursos() as $Clave_Recurso => $Recurso) {
            if ($Usuario->Es_Superadmin() || ! ($Recurso['Solo_Superadmin'] ?? false)) {
                $Recursos[$Clave_Recurso] = static::Ajustar_Recurso_Al_Usuario($Recurso + ['Clave' => $Clave_Recurso], $Usuario);
            }
        }

        return $Recursos;
    }

    public static function Obtener_Recurso(string $Clave_Recurso, Modelo_Usuario $Usuario): array
    {
        $Recursos = static::Obtener_Todos_Los_Recursos();
        abort_unless(isset($Recursos[$Clave_Recurso]), 404);
        abort_if(($Recursos[$Clave_Recurso]['Solo_Superadmin'] ?? false) && ! $Usuario->Es_Superadmin(), 403);

        return static::Ajustar_Recurso_Al_Usuario($Recursos[$Clave_Recurso] + ['Clave' => $Clave_Recurso], $Usuario);
    }

    /** Consulta del recurso limitada a las instituciones a cargo del usuario (el superadmin ve todo) */
    public static function Consultar_Registros(array $Recurso, Modelo_Usuario $Usuario): Builder
    {
        $Consulta = $Recurso['Modelo']::query();
        $Ids_Instituciones = $Usuario->Obtener_Ids_Instituciones_A_Cargo();
        if ($Ids_Instituciones === null) {
            return $Consulta;
        }

        return match ($Recurso['Alcance'] ?? null) {
            'institucion' => $Consulta->whereIn('institucion_id', $Ids_Instituciones),
            'propia' => $Consulta->whereIn('id', $Ids_Instituciones),
            default => $Consulta->whereRaw('1 = 0'),
        };
    }

    /** Para el ejecutivo: menos roles disponibles y la institución siempre obligatoria */
    private static function Ajustar_Recurso_Al_Usuario(array $Recurso, Modelo_Usuario $Usuario): array
    {
        if ($Usuario->Es_Superadmin()) {
            return $Recurso;
        }
        $Recurso['Campos'] = array_values(array_filter($Recurso['Campos'], fn ($Campo) => ! ($Campo['Solo_Superadmin'] ?? false)));
        foreach ($Recurso['Campos'] as &$Campo) {
            if (isset($Campo['Opciones_Para_Ejecutivo'])) {
                $Campo['Opciones'] = array_intersect_key($Campo['Opciones'], array_flip($Campo['Opciones_Para_Ejecutivo']));
            }
            if ($Campo['Nombre'] === 'institucion_id') {
                $Campo['Obligatorio'] = true;
            }
        }
        unset($Campo);

        return $Recurso;
    }
}
