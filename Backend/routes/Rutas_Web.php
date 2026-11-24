<?php

use App\Controladores\Acceso\Controlador_Inicio_Sesion;
use App\Controladores\Acceso\Controlador_Recuperar_Contrasena;
use App\Controladores\Administracion\Controlador_Auditoria;
use App\Controladores\Administracion\Controlador_Panel_Administracion;
use App\Controladores\Administracion\Controlador_Recursos_Cms;
use App\Controladores\Administracion\Controlador_Solicitudes_Horas;
use App\Controladores\Portal\Controlador_Academia;
use App\Controladores\Portal\Controlador_Avatar;
use App\Controladores\Portal\Controlador_Bolsa_Horas;
use App\Controladores\Portal\Controlador_Certificados;
use App\Controladores\Portal\Controlador_Credencial;
use App\Controladores\Portal\Controlador_Entregables;
use App\Controladores\Portal\Controlador_Facturacion;
use App\Controladores\Portal\Controlador_Inicio;
use App\Controladores\Portal\Controlador_Marca;
use App\Controladores\Portal\Controlador_Perfil;
use App\Controladores\Portal\Controlador_Proyectos;
use App\Controladores\Portal\Controlador_Research;
use App\Controladores\Publico\Controlador_Verificar_Credencial;
use App\Middleware\Middleware_Exigir_Cliente;
use App\Middleware\Middleware_Exigir_Administrador_Delegado;
use App\Middleware\Middleware_Exigir_Personal_Blockfinity;
use App\Middleware\Middleware_Exigir_Superadmin;
use App\Middleware\Middleware_Exigir_Unidad_Contratada;
use Illuminate\Support\Facades\Route;

// --- Público ---------------------------------------------------------------
Route::get('/verificar/{Codigo_Credencial}', [Controlador_Verificar_Credencial::class, 'Verificar_Credencial'])->whereUuid('Codigo_Credencial');

Route::middleware('guest')->group(function () {
    Route::get('/login', [Controlador_Inicio_Sesion::class, 'Mostrar_Formulario_Ingreso']);
    Route::post('/login', [Controlador_Inicio_Sesion::class, 'Iniciar_Sesion'])->middleware('throttle:6,1');
    Route::get('/olvide-mi-contrasena', [Controlador_Recuperar_Contrasena::class, 'Mostrar_Formulario_Recuperacion']);
    Route::post('/olvide-mi-contrasena', [Controlador_Recuperar_Contrasena::class, 'Enviar_Enlace_Recuperacion'])->middleware('throttle:3,1');
    // Laravel arma el enlace del correo de recuperación con el nombre de ruta "password.reset"
    Route::get('/restablecer/{Token}', [Controlador_Recuperar_Contrasena::class, 'Mostrar_Formulario_Nueva_Contrasena'])->name('password.reset');
    Route::post('/restablecer', [Controlador_Recuperar_Contrasena::class, 'Guardar_Nueva_Contrasena']);
});

// Acceso del personal de Blockfinity: va separado del de clientes (más intentos bloqueados y sin compartir pantalla)
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [Controlador_Inicio_Sesion::class, 'Mostrar_Formulario_Ingreso_Administracion']);
    Route::post('/admin/login', [Controlador_Inicio_Sesion::class, 'Iniciar_Sesion_Administracion'])->middleware('throttle:5,1');
});

Route::post('/logout', [Controlador_Inicio_Sesion::class, 'Cerrar_Sesion'])->middleware('auth');

// --- Portal cliente --------------------------------------------------------
Route::middleware(['auth', Middleware_Exigir_Cliente::class])->group(function () {
    Route::get('/', [Controlador_Inicio::class, 'Mostrar_Inicio']);

    Route::get('/proyectos', [Controlador_Proyectos::class, 'Listar_Proyectos']);
    Route::get('/proyectos/{Proyecto}', [Controlador_Proyectos::class, 'Mostrar_Proyecto']);
    Route::get('/entregables/{Entregable}/descargar', [Controlador_Entregables::class, 'Descargar_Entregable']);

    Route::middleware(Middleware_Exigir_Unidad_Contratada::class.':RESEARCH')->group(function () {
        Route::get('/research', [Controlador_Research::class, 'Listar_Reportes']);
        Route::get('/research/{Reporte}', [Controlador_Research::class, 'Mostrar_Reporte']);
        Route::get('/research/{Reporte}/documento', [Controlador_Research::class, 'Obtener_Documento'])->middleware('throttle:30,1');
        Route::post('/research/{Reporte}/leido', [Controlador_Research::class, 'Marcar_Reporte_Leido']);
    });

    Route::get('/academia', [Controlador_Academia::class, 'Listar_Videos']);
    Route::get('/academia/{Video}', [Controlador_Academia::class, 'Mostrar_Video']);
    Route::post('/academia/{Video}/visto', [Controlador_Academia::class, 'Marcar_Video_Visto']);
    Route::post('/academia/{Video}/inscribirme', [Controlador_Academia::class, 'Inscribirse_Webinar']);
    Route::get('/academia/{Video}/calendario.ics', [Controlador_Academia::class, 'Descargar_Calendario_Webinar']);

    Route::get('/credencial', [Controlador_Credencial::class, 'Mostrar_Credencial']);

    Route::get('/certificados', [Controlador_Certificados::class, 'Listar_Certificados']);
    Route::get('/certificados/{Certificado}/archivo', [Controlador_Certificados::class, 'Ver_Archivo']);
    Route::get('/marca/logo', [Controlador_Marca::class, 'Mostrar_Logo']);

    Route::get('/avatar', [Controlador_Avatar::class, 'Mostrar_Avatar']);
    Route::post('/avatar/{Item}/comprar', [Controlador_Avatar::class, 'Comprar_Item'])->middleware('throttle:20,1');
    Route::post('/avatar/{Item}/equipar', [Controlador_Avatar::class, 'Equipar_Item']);

    Route::get('/perfil', [Controlador_Perfil::class, 'Mostrar_Perfil']);
    Route::put('/perfil/contrasena', [Controlador_Perfil::class, 'Cambiar_Contrasena']);

    // Solo el administrador delegado: gestiona su institución y su equipo
    Route::middleware(Middleware_Exigir_Administrador_Delegado::class)->group(function () {
        Route::post('/proyectos/{Proyecto}/participantes', [Controlador_Proyectos::class, 'Actualizar_Participante']);

        Route::get('/entregables', [Controlador_Entregables::class, 'Listar_Entregables']);
        Route::post('/entregables/{Entregable}/aprobar', [Controlador_Entregables::class, 'Aprobar_Entregable'])->middleware('throttle:10,1');
        Route::post('/entregables/verificar', [Controlador_Entregables::class, 'Verificar_Codigo_Documento'])->middleware('throttle:30,1');

        Route::get('/facturacion', [Controlador_Facturacion::class, 'Mostrar_Facturacion']);
        Route::get('/facturacion/factura/{Factura}/{Tipo_Archivo}', [Controlador_Facturacion::class, 'Descargar_Archivo_Factura'])->whereIn('Tipo_Archivo', ['pdf', 'xml']);
        Route::get('/facturacion/contrato/{Contrato}', [Controlador_Facturacion::class, 'Descargar_Contrato']);

        Route::get('/bolsa-de-horas', [Controlador_Bolsa_Horas::class, 'Mostrar_Bolsa_Horas']);
        Route::post('/bolsa-de-horas', [Controlador_Bolsa_Horas::class, 'Crear_Solicitud_Horas'])->middleware('throttle:5,1');
    });
});

// --- CMS (superadmin y usuarios ejecutivos) -----------------------------------
// El usuario ejecutivo solo ve las instituciones a su cargo (ver Controlador_Recursos_Cms)
Route::middleware(['auth', Middleware_Exigir_Personal_Blockfinity::class])->prefix('admin')->group(function () {
    Route::get('/', [Controlador_Panel_Administracion::class, 'Mostrar_Resumen']);

    Route::get('/solicitudes-horas', [Controlador_Solicitudes_Horas::class, 'Listar_Solicitudes']);
    Route::put('/solicitudes-horas/{Solicitud}', [Controlador_Solicitudes_Horas::class, 'Actualizar_Solicitud']);

    Route::get('/auditoria', [Controlador_Auditoria::class, 'Listar_Registros_Auditoria'])->middleware(Middleware_Exigir_Superadmin::class);

    Route::get('/archivo/{Clave_Recurso}/{Id_Registro}/{Nombre_Campo}', [Controlador_Recursos_Cms::class, 'Descargar_Archivo_Registro']);
    Route::get('/{Clave_Recurso}', [Controlador_Recursos_Cms::class, 'Listar_Registros']);
    Route::get('/{Clave_Recurso}/crear', [Controlador_Recursos_Cms::class, 'Mostrar_Formulario_Crear']);
    Route::post('/{Clave_Recurso}', [Controlador_Recursos_Cms::class, 'Crear_Registro']);
    Route::get('/{Clave_Recurso}/{Id_Registro}/editar', [Controlador_Recursos_Cms::class, 'Mostrar_Formulario_Editar']);
    // POST (no PUT) para que funcione la subida de archivos con multipart
    Route::post('/{Clave_Recurso}/{Id_Registro}', [Controlador_Recursos_Cms::class, 'Actualizar_Registro']);
    Route::delete('/{Clave_Recurso}/{Id_Registro}', [Controlador_Recursos_Cms::class, 'Eliminar_Registro']);
});
