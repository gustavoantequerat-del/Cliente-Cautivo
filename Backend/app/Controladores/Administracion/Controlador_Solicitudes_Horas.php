<?php

namespace App\Controladores\Administracion;

use App\Modelos\Modelo_Movimiento_Horas;
use App\Modelos\Modelo_Registro_Auditoria;
use App\Modelos\Modelo_Solicitud_Horas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class Controlador_Solicitudes_Horas
{
    public function Listar_Solicitudes(Request $Peticion): Response
    {
        $Filtro_Estado = $Peticion->query('estado', 'abiertas');
        $Consulta = Modelo_Solicitud_Horas::with(['Usuario', 'Institucion'])->latest();
        if ($Filtro_Estado === 'abiertas') {
            $Consulta->whereIn('estado', ['pendiente', 'en_atencion']);
        }

        return Inertia::render('Administracion/Pagina_Solicitudes_Horas', [
            'Solicitudes' => $Consulta->paginate(20)->withQueryString()->through(fn (Modelo_Solicitud_Horas $Solicitud) => [
                'Id' => $Solicitud->id,
                'Fecha' => $Solicitud->created_at->format('d/m/Y H:i'),
                'Institucion' => $Solicitud->Institucion->nombre,
                'Saldo_Institucion' => $Solicitud->Institucion->Calcular_Saldo_Horas(),
                'Solicitante' => $Solicitud->Usuario?->nombre,
                'Email' => $Solicitud->Usuario?->email,
                'Modalidad' => $Solicitud->modalidad,
                'Especialidad' => $Solicitud->especialidad,
                'Horas_Estimadas' => (float) $Solicitud->horas_estimadas,
                'Horas_Cobradas' => $Solicitud->horas_cobradas !== null ? (float) $Solicitud->horas_cobradas : null,
                'Descripcion' => $Solicitud->descripcion,
                'Estado' => $Solicitud->estado,
                'Respuesta' => $Solicitud->respuesta,
            ]),
            'Estados' => Modelo_Solicitud_Horas::Estados,
            'Filtro_Estado' => $Filtro_Estado,
        ]);
    }

    /** Al marcarla como atendida se descuentan las horas cobradas de la bolsa (una sola vez) */
    public function Actualizar_Solicitud(Request $Peticion, Modelo_Solicitud_Horas $Solicitud): RedirectResponse
    {
        $Datos = $Peticion->validate([
            'estado' => ['required', Rule::in(array_keys(Modelo_Solicitud_Horas::Estados))],
            'horas_cobradas' => ['nullable', 'numeric', 'min:0', 'max:100', 'required_if:estado,atendida'],
            'respuesta' => ['nullable', 'string', 'max:3000'],
        ]);

        DB::transaction(function () use ($Solicitud, $Datos, $Peticion) {
            $Ya_Estaba_Atendida = $Solicitud->estado === 'atendida';
            $Solicitud->fill($Datos + ['atendido_por' => $Peticion->user()->id]);
            if ($Datos['estado'] === 'atendida' && ! $Ya_Estaba_Atendida) {
                $Solicitud->fecha_resolucion = now();
                if ((float) $Datos['horas_cobradas'] > 0) {
                    Modelo_Movimiento_Horas::create([
                        'institucion_id' => $Solicitud->institucion_id,
                        'solicitud_horas_id' => $Solicitud->id,
                        'horas' => -1 * (float) $Datos['horas_cobradas'],
                        'descripcion' => ucfirst($Solicitud->modalidad).' · '.$Solicitud->especialidad,
                        'creado_por' => $Peticion->user()->id,
                    ]);
                }
            }
            $Solicitud->save();
        });

        Modelo_Registro_Auditoria::Registrar_Evento('cms.horas', $Solicitud, 'Actualizó solicitud de horas #'.$Solicitud->id.' a '.$Datos['estado']);

        return back()->with('Mensaje_Exito', 'Solicitud actualizada.');
    }
}
