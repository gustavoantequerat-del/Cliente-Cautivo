<?php

namespace App\Controladores\Portal;

use App\Modelos\Modelo_Movimiento_Horas;
use App\Modelos\Modelo_Registro_Auditoria;
use App\Modelos\Modelo_Solicitud_Horas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class Controlador_Bolsa_Horas
{
    public function Mostrar_Bolsa_Horas(Request $Peticion): Response
    {
        $Usuario = $Peticion->user();
        $Saldo_Horas = $Usuario->Institucion->Calcular_Saldo_Horas();
        $Formatear_Horas = fn ($Horas) => rtrim(rtrim(number_format((float) $Horas, 2, ',', '.'), '0'), ',');

        $Proximo_Abono_Por_Vencer = Modelo_Movimiento_Horas::Visibles_Para_Usuario($Usuario)->where('horas', '>', 0)
            ->whereNotNull('fecha_vencimiento')->where('fecha_vencimiento', '>=', today())
            ->orderBy('fecha_vencimiento')->first();

        return Inertia::render('Portal/Pagina_Bolsa_Horas', [
            'Saldo_Horas' => $Saldo_Horas,
            'Saldo_Formateado' => $Formatear_Horas($Saldo_Horas),
            'Horas_Por_Vencer' => $Proximo_Abono_Por_Vencer ? [
                'Horas' => $Formatear_Horas($Proximo_Abono_Por_Vencer->horas),
                'Fecha' => $Proximo_Abono_Por_Vencer->fecha_vencimiento->translatedFormat('j \d\e F \d\e Y'),
            ] : null,
            'Movimientos' => Modelo_Movimiento_Horas::Visibles_Para_Usuario($Usuario)->latest()->limit(30)->get()
                ->map(fn (Modelo_Movimiento_Horas $Movimiento) => [
                    'Fecha' => $Movimiento->created_at->translatedFormat('j M Y'),
                    'Descripcion' => $Movimiento->descripcion,
                    'Horas' => ($Movimiento->horas > 0 ? '+' : '').$Formatear_Horas($Movimiento->horas),
                    'Es_Abono' => $Movimiento->horas > 0,
                ]),
            'Solicitudes' => Modelo_Solicitud_Horas::Visibles_Para_Usuario($Usuario)->with('Usuario')->latest()->limit(20)->get()
                ->map(fn (Modelo_Solicitud_Horas $Solicitud) => [
                    'Id' => $Solicitud->id,
                    'Fecha' => $Solicitud->created_at->translatedFormat('j M Y'),
                    'Modalidad' => $Solicitud->modalidad,
                    'Especialidad' => $Solicitud->especialidad,
                    'Horas' => $Formatear_Horas($Solicitud->horas_cobradas ?? $Solicitud->horas_estimadas),
                    'Estado' => $Solicitud->estado,
                    'Solicitante' => $Solicitud->Usuario?->nombre,
                    'Respuesta' => $Solicitud->respuesta,
                ]),
            'Especialidades' => Modelo_Solicitud_Horas::Especialidades,
        ]);
    }

    public function Crear_Solicitud_Horas(Request $Peticion): RedirectResponse
    {
        $Usuario = $Peticion->user();
        $Datos = $Peticion->validate([
            'modalidad' => ['required', Rule::in(Modelo_Solicitud_Horas::Modalidades)],
            'especialidad' => ['required', Rule::in(Modelo_Solicitud_Horas::Especialidades)],
            'horas_estimadas' => ['required', 'numeric', Rule::in([0.5, 1, 2, 4])],
            'descripcion' => ['required', 'string', 'min:20', 'max:3000'],
        ]);

        if ($Datos['horas_estimadas'] > $Usuario->Institucion->Calcular_Saldo_Horas()) {
            return back()->withErrors(['horas_estimadas' => 'Su bolsa no tiene horas suficientes. Consulte a su ejecutiva para ampliarla.']);
        }

        $Solicitud = Modelo_Solicitud_Horas::create($Datos + ['institucion_id' => $Usuario->institucion_id, 'usuario_id' => $Usuario->id]);
        Modelo_Registro_Auditoria::Registrar_Evento('horas.solicitar', $Solicitud, "Solicitó {$Datos['horas_estimadas']} h ({$Datos['modalidad']})");

        $this->Notificar_Solicitud_Por_Correo($Datos, $Usuario->nombre, $Usuario->email, $Usuario->Institucion->nombre);

        return back()->with('Mensaje_Exito', $Datos['modalidad'] === 'emergencia'
            ? 'Solicitud de emergencia enviada. Un especialista le contactará a la brevedad.'
            : 'Solicitud enviada. Le responderemos en menos de 4 horas hábiles.');
    }

    /** El correo va por la cola (driver database + cron), así no bloquea la respuesta */
    private function Notificar_Solicitud_Por_Correo(array $Datos, string $Nombre_Solicitante, string $Email_Solicitante, string $Nombre_Institucion): void
    {
        $Correo_Destino = config('Configuracion_Portal.Correo_Notificaciones');
        if (! $Correo_Destino) {
            return;
        }

        dispatch(function () use ($Correo_Destino, $Datos, $Nombre_Solicitante, $Email_Solicitante, $Nombre_Institucion) {
            Mail::raw(
                "Nueva solicitud de horas ({$Datos['modalidad']})\n\nInstitución: {$Nombre_Institucion}\nSolicitante: {$Nombre_Solicitante} <{$Email_Solicitante}>\nEspecialidad: {$Datos['especialidad']}\nHoras estimadas: {$Datos['horas_estimadas']}\n\n{$Datos['descripcion']}",
                fn ($Mensaje) => $Mensaje->to($Correo_Destino)->subject("[Portal] Solicitud de horas · {$Nombre_Institucion}")
            );
        });
    }
}
