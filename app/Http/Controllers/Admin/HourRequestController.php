<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\HoursController;
use App\Models\AuditLog;
use App\Models\HourMovement;
use App\Models\HourRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class HourRequestController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->query('estado', 'abiertas');
        $query = HourRequest::with(['user', 'institution'])->latest();
        if ($status === 'abiertas') {
            $query->whereIn('status', ['pending', 'in_progress']);
        }

        return Inertia::render('Admin/HourRequests', [
            'requests' => $query->paginate(20)->withQueryString()->through(fn (HourRequest $r) => [
                'id' => $r->id,
                'date' => $r->created_at->format('d/m/Y H:i'),
                'institution' => $r->institution->name,
                'balance' => $r->institution->hourBalance(),
                'user' => $r->user?->name,
                'email' => $r->user?->email,
                'mode' => $r->mode,
                'specialty' => $r->specialty,
                'estimated' => (float) $r->estimated_hours,
                'charged' => $r->charged_hours !== null ? (float) $r->charged_hours : null,
                'description' => $r->description,
                'status' => $r->status,
                'response' => $r->response,
            ]),
            'statuses' => HoursController::STATUS,
            'filter' => $status,
        ]);
    }

    /** Al marcar como atendida se descuentan las horas cobradas de la bolsa (una sola vez) */
    public function update(Request $request, HourRequest $hourRequest): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(HoursController::STATUS))],
            'charged_hours' => ['nullable', 'numeric', 'min:0', 'max:100', 'required_if:status,done'],
            'response' => ['nullable', 'string', 'max:3000'],
        ]);

        DB::transaction(function () use ($hourRequest, $data, $request) {
            $wasDone = $hourRequest->status === 'done';
            $hourRequest->fill($data + ['handled_by' => $request->user()->id]);
            if ($data['status'] === 'done' && ! $wasDone) {
                $hourRequest->resolved_at = now();
                if ((float) $data['charged_hours'] > 0) {
                    HourMovement::create([
                        'institution_id' => $hourRequest->institution_id,
                        'hour_request_id' => $hourRequest->id,
                        'hours' => -1 * (float) $data['charged_hours'],
                        'description' => ucfirst($hourRequest->mode).' · '.$hourRequest->specialty,
                        'created_by' => $request->user()->id,
                    ]);
                }
            }
            $hourRequest->save();
        });

        AuditLog::record('cms.hours', $hourRequest, 'Actualizó solicitud de horas #'.$hourRequest->id.' a '.$data['status']);

        return back()->with('success', 'Solicitud actualizada.');
    }
}
