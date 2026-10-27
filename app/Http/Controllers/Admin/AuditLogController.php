<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $query = AuditLog::with('user')->latest('created_at');
        if ($action = $request->query('accion')) {
            $query->where('action', 'like', $action.'%');
        }

        return Inertia::render('Admin/AuditLog', [
            'logs' => $query->paginate(40)->withQueryString()->through(fn (AuditLog $l) => [
                'id' => $l->id,
                'when' => $l->created_at->format('d/m/Y H:i'),
                'user' => $l->user?->name ?? '—',
                'action' => $l->action,
                'description' => $l->description,
                'meta' => $l->meta,
                'ip' => $l->ip,
            ]),
            'action' => $action,
        ]);
    }
}
