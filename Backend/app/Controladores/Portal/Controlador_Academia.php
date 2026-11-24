<?php

namespace App\Controladores\Portal;

use App\Modelos\Modelo_Configuracion;
use App\Modelos\Modelo_Movimiento_Monedas;
use App\Modelos\Modelo_Progreso_Video;
use App\Modelos\Modelo_Registro_Auditoria;
use App\Modelos\Modelo_Video;
use App\Servicios\Servicio_Documentos_Pdf;
use App\Servicios\Servicio_Enlaces_Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as Respuesta_Archivo;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as Respuesta_Http;

class Controlador_Academia
{
    public function Listar_Videos(Request $Peticion): Response
    {
        $Usuario = $Peticion->user();
        $Formato_Filtrado = $Peticion->query('formato');
        $Tema_Filtrado = $Peticion->query('tema');

        $Progreso_Por_Video = Modelo_Progreso_Video::where('usuario_id', $Usuario->id)->get()->keyBy('video_id');

        $Consulta = Modelo_Video::Visibles_Para_Usuario($Usuario)->where('esta_publicado', true);
        if ($Formato_Filtrado && isset(Modelo_Video::Formatos[$Formato_Filtrado])) {
            $Consulta->where('formato', $Formato_Filtrado);
        }
        if ($Tema_Filtrado) {
            $Consulta->where('tema', $Tema_Filtrado);
        }

        $Proximo_En_Vivo = Modelo_Video::Visibles_Para_Usuario($Usuario)->where('esta_publicado', true)->where('formato', 'en_vivo')
            ->where('fecha_programada', '>=', now())->orderBy('fecha_programada')->first();

        $Videos_Completados = $Progreso_Por_Video->whereNotNull('fecha_completado');
        $Minutos_Formacion = Modelo_Video::whereIn('id', $Videos_Completados->keys())->sum('duracion_minutos');

        return Inertia::render('Portal/Pagina_Academia', [
            'Proximo_En_Vivo' => $Proximo_En_Vivo ? $this->Formatear_Video($Proximo_En_Vivo, $Progreso_Por_Video->get($Proximo_En_Vivo->id)) : null,
            'Videos' => $Consulta->orderByRaw("case when formato = 'en_vivo' then 0 else 1 end")
                ->orderByDesc('fecha_programada')->latest()->paginate(18)->withQueryString()
                ->through(fn (Modelo_Video $Video) => $this->Formatear_Video($Video, $Progreso_Por_Video->get($Video->id))),
            'Mi_Avance' => [
                'Horas' => round($Minutos_Formacion / 60, 1),
                'Completados' => $Videos_Completados->count(),
            ],
            'Formatos' => Modelo_Video::Formatos,
            'Temas' => Modelo_Video::Temas,
            'Filtros_Aplicados' => ['Formato' => $Formato_Filtrado, 'Tema' => $Tema_Filtrado],
        ]);
    }

    public function Mostrar_Video(Request $Peticion, Modelo_Video $Video): Response
    {
        $this->Validar_Acceso_Video($Peticion, $Video);
        $Progreso = Modelo_Progreso_Video::where('usuario_id', $Peticion->user()->id)->where('video_id', $Video->id)->first();

        return Inertia::render('Portal/Pagina_Detalle_Video', [
            'Video' => $this->Formatear_Video($Video, $Progreso) + [
                'Descripcion' => $Video->descripcion,
                'Url_Embebida' => $Video->Construir_Url_Embebida(),
                'Url_Documento' => $Video->Es_Pdf() && ($Video->ruta_archivo || $Video->enlace_externo) ? "/academia/{$Video->id}/documento" : null,
                'Enlace_Sala' => $Video->formato === 'en_vivo' ? $Video->enlace_sala : null,
            ],
            'Texto_Marca_Agua' => $Video->Es_Pdf() ? Modelo_Configuracion::Armar_Texto_Marca_Agua($Peticion->user()) : null,
        ]);
    }

    /** PDF de un webinar o píldora para el visor protegido (sin descarga) */
    public function Obtener_Documento(Request $Peticion, Modelo_Video $Video): Respuesta_Http
    {
        $this->Validar_Acceso_Video($Peticion, $Video);
        abort_unless($Video->Es_Pdf(), 404);

        return Servicio_Documentos_Pdf::Responder_Pdf($Video->ruta_archivo, $Video->enlace_externo);
    }

    /** "Marcar como visto": lo declara el usuario (Drive no permite medir la reproducción) */
    public function Marcar_Video_Visto(Request $Peticion, Modelo_Video $Video): RedirectResponse
    {
        $this->Validar_Acceso_Video($Peticion, $Video);
        $Usuario = $Peticion->user();

        $Progreso = Modelo_Progreso_Video::firstOrNew(['video_id' => $Video->id, 'usuario_id' => $Usuario->id]);
        $Progreso->fecha_completado ??= now();
        $Progreso->save();
        // Las monedas van al usuario, para su avatar (una sola vez por video)
        $Gano_Monedas = Modelo_Movimiento_Monedas::Otorgar_Una_Vez($Usuario, $Video->puntos, 'video', $Video->id, 'Completó «'.$Video->titulo.'»');

        if ($Gano_Monedas) {
            Modelo_Registro_Auditoria::Registrar_Evento('video.completar', $Video, 'Completó «'.$Video->titulo.'»', ['Monedas' => $Video->puntos]);
        }

        return back()->with('Mensaje_Exito', $Gano_Monedas
            ? "¡Listo! Ganó {$Video->puntos} monedas para su avatar."
            : 'Este contenido ya estaba marcado como visto.');
    }

    public function Inscribirse_Webinar(Request $Peticion, Modelo_Video $Video): RedirectResponse
    {
        $this->Validar_Acceso_Video($Peticion, $Video);
        abort_unless($Video->formato === 'en_vivo', 422);

        Modelo_Progreso_Video::updateOrCreate(
            ['video_id' => $Video->id, 'usuario_id' => $Peticion->user()->id],
            ['fecha_inscripcion' => now()]
        );
        Modelo_Registro_Auditoria::Registrar_Evento('video.inscribirse', $Video, 'Se inscribió a «'.$Video->titulo.'»');

        return back()->with('Mensaje_Exito', 'Inscripción confirmada. Descargue el evento para su calendario.');
    }

    /** Archivo .ics para "Añadir al calendario" (sin dependencias) */
    public function Descargar_Calendario_Webinar(Request $Peticion, Modelo_Video $Video): Respuesta_Archivo
    {
        $this->Validar_Acceso_Video($Peticion, $Video);
        abort_unless($Video->fecha_programada, 404);

        $Inicio = $Video->fecha_programada->copy()->utc();
        $Fin = $Inicio->copy()->addMinutes($Video->duracion_minutos ?: 60);
        $Escapar_Texto = fn ($Texto) => addcslashes(preg_replace('/\s+/', ' ', (string) $Texto), ',;\\');
        $Lineas_Calendario = [
            'BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//Blockfinity//Portal Cliente//ES', 'BEGIN:VEVENT',
            'UID:video-'.$Video->id.'@'.$Peticion->getHost(),
            'DTSTAMP:'.now()->utc()->format('Ymd\THis\Z'),
            'DTSTART:'.$Inicio->format('Ymd\THis\Z'),
            'DTEND:'.$Fin->format('Ymd\THis\Z'),
            'SUMMARY:'.$Escapar_Texto($Video->titulo),
            'DESCRIPTION:'.$Escapar_Texto(trim(($Video->expositor ? $Video->expositor.'. ' : '').($Video->enlace_sala ?? ''))),
            'URL:'.url('/academia/'.$Video->id),
            'END:VEVENT', 'END:VCALENDAR',
        ];

        return response(implode("\r\n", $Lineas_Calendario)."\r\n", 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="webinar-'.$Video->id.'.ics"',
        ]);
    }

    private function Formatear_Video(Modelo_Video $Video, ?Modelo_Progreso_Video $Progreso): array
    {
        return [
            'Id' => $Video->id,
            'Formato' => $Video->formato,
            'Tipo_Contenido' => $Video->tipo_contenido,
            'Nombre_Formato' => Modelo_Video::Formatos[$Video->formato] ?? $Video->formato,
            'Titulo' => $Video->titulo,
            'Tema' => $Video->tema,
            'Expositor' => $Video->expositor,
            'Duracion_Minutos' => $Video->duracion_minutos,
            'Puntos' => $Video->puntos,
            'Fecha_Programada' => $Video->fecha_programada?->translatedFormat('l j \d\e F · H:i'),
            'Es_Proximo' => $Video->formato === 'en_vivo' && $Video->fecha_programada?->isFuture(),
            'Miniatura' => $Video->enlace_miniatura ?: Servicio_Enlaces_Video::Construir_Url_Miniatura($Video->proveedor, $Video->id_externo),
            'Proveedor' => $Video->proveedor,
            'Esta_Completado' => (bool) $Progreso?->fecha_completado,
            'Esta_Inscrito' => (bool) $Progreso?->fecha_inscripcion,
        ];
    }

    private function Validar_Acceso_Video(Request $Peticion, Modelo_Video $Video): void
    {
        $Es_De_Su_Institucion = ! $Video->institucion_id || $Video->institucion_id == $Peticion->user()->institucion_id;
        abort_unless($Video->esta_publicado && $Es_De_Su_Institucion, 404);
    }
}
