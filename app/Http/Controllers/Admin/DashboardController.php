<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Resources;
use App\Http\Controllers\Controller;
use App\Models\Deliverable;
use App\Models\HourRequest;
use App\Models\Institution;
use App\Models\Invoice;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $groups = [];
        foreach (Resources::all() as $key => $def) {
            $groups[$def['group']][] = ['key' => $key, 'label' => $def['label'], 'count' => $def['model']::count()];
        }

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                ['label' => 'Instituciones activas', 'value' => Institution::where('is_active', true)->count()],
                ['label' => 'Credenciales activas', 'value' => User::where('is_active', true)->where('role', '!=', 'admin')->count()],
                ['label' => 'Solicitudes de horas pendientes', 'value' => HourRequest::whereIn('status', ['pending', 'in_progress'])->count()],
                ['label' => 'Entregables esperando aprobación', 'value' => Deliverable::where('status', 'pending_approval')->count()],
                ['label' => 'Facturas pendientes', 'value' => Invoice::where('status', 'pending')->count()],
            ],
            'groups' => $groups,
        ]);
    }
}
