<?php

namespace App\Controladores\Portal;

use App\Modelos\Modelo_Configuracion;
use App\Modelos\Modelo_Registro_Auditoria;
use App\Modelos\Modelo_Solicitud_Producto;
use App\Modelos\Modelo_Unidad_Negocio;
use App\Modelos\Modelo_Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as Respuesta_Http;

/**
 * Ecosistema Blockfinity: catálogo de productos y servicios. Resalta los que la institución tiene
 * contratados; el administrador delegado solicita los demás y el usuario ejecutivo los habilita en el CMS.
 */
class Controlador_Ecosistema
{
    public function Listar_Productos(Request $Peticion): Response
    {
        $Usuario = $Peticion->user();
        $Ids_Contratados = $Usuario->Institucion->Unidades_Contratadas->pluck('id');
        $Ids_Solicitados = $this->Obtener_Ids_Solicitados($Usuario);

        $Productos = Modelo_Unidad_Negocio::where('esta_publicado', true)->orderBy('orden')->orderBy('nombre')->get()
            ->map(fn (Modelo_Unidad_Negocio $Producto) => $this->Formatear_Producto($Producto, $Ids_Contratados->contains($Producto->id), $Ids_Solicitados))
            // Primero lo contratado, luego lo que puede descubrir
            ->sortByDesc('Esta_Contratado')->values();

        return Inertia::render('Portal/Pagina_Ecosistema', [
            'Productos' => $Productos,
            'Nivel_Alianza' => $Usuario->Institucion->Calcular_Nivel_Alianza(),
            'Puede_Solicitar' => $Usuario->Es_Administrador_Delegado(),
        ]);
    }

    public function Mostrar_Producto(Request $Peticion, Modelo_Unidad_Negocio $Producto): Response
    {
        abort_unless($Producto->esta_publicado, 404);
        $Usuario = $Peticion->user();
        $Esta_Contratado = $Usuario->Institucion->Unidades_Contratadas->contains('id', $Producto->id);

        return Inertia::render('Portal/Pagina_Detalle_Producto', [
            'Producto' => $this->Formatear_Producto($Producto, $Esta_Contratado, $this->Obtener_Ids_Solicitados($Usuario)) + [
                'Descripcion' => $Producto->descripcion,
                'Tipo_Vista' => $Producto->Obtener_Tipo_Vista(),
                'Url_Video' => $Producto->Obtener_Url_Video(),
            ],
            'Puede_Solicitar' => $Usuario->Es_Administrador_Delegado(),
        ]);
    }

    public function Mostrar_Imagen(Modelo_Unidad_Negocio $Producto): Respuesta_Http
    {
        abort_unless($Producto->esta_publicado && $Producto->ruta_imagen && Storage::disk('local')->exists($Producto->ruta_imagen), 404);

        return response()->file(Storage::disk('local')->path($Producto->ruta_imagen), [
            'Cache-Control' => 'private, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /** Solo el administrador delegado: registra el pedido y avisa por correo a Blockfinity y al ejecutivo a cargo */
    public function Solicitar_Producto(Request $Peticion, Modelo_Unidad_Negocio $Producto): RedirectResponse
    {
        abort_unless($Producto->esta_publicado, 404);
        $Usuario = $Peticion->user();
        $Institucion = $Usuario->Institucion;
        $Datos = $Peticion->validate(['comentario' => ['nullable', 'string', 'max:1000']]);

        if ($Institucion->Unidades_Contratadas->contains('id', $Producto->id)) {
            return back()->with('Mensaje_Exito', "Su institución ya tiene {$Producto->nombre}.");
        }
        if (in_array($Producto->id, $this->Obtener_Ids_Solicitados($Usuario))) {
            return back()->with('Mensaje_Exito', 'Ya enviamos su solicitud. Su ejecutivo de cuenta le contactará.');
        }

        $Solicitud = Modelo_Solicitud_Producto::create([
            'institucion_id' => $Institucion->id, 'unidad_negocio_id' => $Producto->id,
            'usuario_id' => $Usuario->id, 'comentario' => $Datos['comentario'] ?? null,
        ]);
        Modelo_Registro_Auditoria::Registrar_Evento('producto.solicitar', $Solicitud, "Solicitó «{$Producto->nombre}»");
        $this->Notificar_Solicitud($Solicitud, $Producto, $Usuario);

        return back()->with('Mensaje_Exito', 'Solicitud enviada. Su ejecutivo de cuenta le contactará para habilitarlo.');
    }

    private function Obtener_Ids_Solicitados(Modelo_Usuario $Usuario): array
    {
        return Modelo_Solicitud_Producto::Visibles_Para_Usuario($Usuario)->where('estado', 'pendiente')->pluck('unidad_negocio_id')->all();
    }

    private function Formatear_Producto(Modelo_Unidad_Negocio $Producto, bool $Esta_Contratado, array $Ids_Solicitados): array
    {
        return [
            'Id' => $Producto->id,
            'Codigo' => $Producto->codigo,
            'Titulo' => $Producto->nombre,
            'Subtitulo' => $Producto->subtitulo,
            'Url_Imagen' => $Producto->ruta_imagen ? "/ecosistema/{$Producto->id}/imagen?v=".$Producto->updated_at?->timestamp : null,
            'Enlace_Pagina' => $Producto->enlace_pagina,
            'Esta_Contratado' => $Esta_Contratado,
            'Esta_Solicitado' => ! $Esta_Contratado && in_array($Producto->id, $Ids_Solicitados),
        ];
    }

    /** Va al correo de contacto (Configuración del CMS o PORTAL_NOTIFY_EMAIL) con copia al ejecutivo a cargo. Por la cola. */
    private function Notificar_Solicitud(Modelo_Solicitud_Producto $Solicitud, Modelo_Unidad_Negocio $Producto, Modelo_Usuario $Usuario): void
    {
        $Correo_Contacto = Modelo_Configuracion::Obtener_Valor(Modelo_Configuracion::Clave_Correo_Contacto) ?: config('Configuracion_Portal.Correo_Notificaciones');
        $Correo_Ejecutivo = $Usuario->Institucion->Ejecutivo_Cuenta?->email;
        $Destinatarios = array_values(array_unique(array_filter([trim((string) $Correo_Contacto), $Correo_Ejecutivo],
            fn ($Correo) => filter_var($Correo, FILTER_VALIDATE_EMAIL))));
        if (! $Destinatarios) {
            return;
        }

        $Nombre_Institucion = $Usuario->Institucion->nombre;
        $Texto = "Nueva solicitud de producto desde el portal\n\n"
            ."Producto: {$Producto->nombre} ({$Producto->codigo})\n"
            ."Institución: {$Nombre_Institucion}\n"
            ."Solicitante: {$Usuario->nombre} <{$Usuario->email}>".($Usuario->telefono ? " · {$Usuario->telefono}" : '')."\n"
            .($Solicitud->comentario ? "\nComentario:\n{$Solicitud->comentario}\n" : '')
            ."\nPara habilitarlo: CMS → Instituciones → {$Nombre_Institucion} → Productos contratados.\n"
            .url('/admin/solicitudes-productos');
        $Asunto = "[Portal] Solicitud de {$Producto->nombre} · {$Nombre_Institucion}";

        $Email_Solicitante = $Usuario->email;
        dispatch(function () use ($Destinatarios, $Texto, $Asunto, $Email_Solicitante) {
            Mail::raw($Texto, fn ($Mensaje) => $Mensaje->to($Destinatarios)->replyTo($Email_Solicitante)->subject($Asunto));
        });
    }
}
