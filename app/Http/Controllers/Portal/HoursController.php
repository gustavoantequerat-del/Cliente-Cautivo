<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\HourMovement;
use App\Models\HourRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class HoursController extends Controller
{
    public const STATUS = ['pending' => 'Recibida', 'in_progress' => 'En atención', 'done' => 'Atendida', 'rejected' => 'Rechazada'];

    public function index(Request $request): Response
    {
        $user = $request->user();
        $institution = $user->institution;
        $fmt = fn ($h) => rtrim(rtrim(number_format((float) $h, 2, ',', '.'), '0'), ',');

        $expiring = HourMovement::visibleTo($user)->where('hours', '>', 0)
            ->whereNotNull('expires_on')->where('expires_on', '>=', today())
            ->orderBy('expires_on')->first();

        return Inertia::render('Portal/Hours', [
            'balance' => $fmt($institution->hourBalance()),
            'balanceRaw' => $institution->hourBalance(),
            'expiring' => $expiring ? ['hours' => $fmt($expiring->hours), 'date' => $expiring->expires_on->translatedFormat('j \d\e F \d\e Y')] : null,
            'movements' => HourMovement::visibleTo($user)->latest()->limit(30)->get()->map(fn ($m) => [
                'date' => $m->created_at->translatedFormat('j M Y'),
                'description' => $m->description,
                'hours' => ($m->hours > 0 ? '+' : '').$fmt($m->hours),
                'positive' => $m->hours > 0,
            ]),
            'requests' => HourRequest::visibleTo($user)->with('user')->latest()->limit(20)->get()->map(fn ($r) => [
                'id' => $r->id,
                'date' => $r->created_at->translatedFormat('j M Y'),
                'mode' => $r->mode,
                'specialty' => $r->specialty,
                'hours' => $fmt($r->charged_hours ?? $r->estimated_hours),
                'status' => $r->status,
                'statusLabel' => self::STATUS[$r->status] ?? $r->status,
                'by' => $r->user?->name,
                'response' => $r->response,
            ]),
            'specialties' => config('portal.specialties'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'mode' => ['required', Rule::in(['consulta', 'emergencia'])],
            'specialty' => ['required', Rule::in(config('portal.specialties'))],
            'estimated_hours' => ['required', 'numeric', Rule::in([0.5, 1, 2, 4])],
            'description' => ['required', 'string', 'min:20', 'max:3000'],
        ]);

        if ($data['estimated_hours'] > $user->institution->hourBalance()) {
            return back()->withErrors(['estimated_hours' => 'Su bolsa no tiene horas suficientes. Consulte a su ejecutiva para ampliarla.']);
        }

        $hourRequest = HourRequest::create($data + ['institution_id' => $user->institution_id, 'user_id' => $user->id]);
        AuditLog::record('hours.request', $hourRequest, "Solicitó {$data['estimated_hours']} h ({$data['mode']})");

        if ($to = config('portal.notify_email')) {
            // Correo vía cola (driver database + cron), no bloquea la respuesta
            $institutionName = $user->institution->name;
            dispatch(function () use ($to, $data, $institutionName, $user) {
                Mail::raw(
                    "Nueva solicitud de horas ({$data['mode']})\n\nInstitución: {$institutionName}\nSolicitante: {$user->name} <{$user->email}>\nEspecialidad: {$data['specialty']}\nHoras estimadas: {$data['estimated_hours']}\n\n{$data['description']}",
                    fn ($m) => $m->to($to)->subject("[Portal] Solicitud de horas · {$institutionName}")
                );
            });
        }

        return back()->with('success', $data['mode'] === 'emergencia'
            ? 'Solicitud de emergencia enviada. Un especialista le contactará a la brevedad.'
            : 'Solicitud enviada. Le responderemos en menos de 4 horas hábiles.');
    }
}
