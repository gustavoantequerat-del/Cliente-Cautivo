<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Contract;
use App\Models\Deliverable;
use App\Models\Project;
use App\Support\Files;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class DeliverableController extends Controller
{
    public static function present(Deliverable $d): array
    {
        return [
            'id' => $d->id,
            'title' => $d->title,
            'kind' => $d->kind,
            'kindLabel' => config('portal.deliverable_kinds.'.$d->kind, $d->kind),
            'version' => $d->version,
            'phase' => $d->phase_label,
            'unit' => $d->unit?->code,
            'project' => $d->project?->name,
            'status' => $d->status,
            'date' => ($d->published_at ?? $d->created_at)?->translatedFormat('j M Y'),
            'sha256' => $d->sha256,
            'hasFile' => (bool) ($d->file_path || $d->external_url),
            'isExternal' => ! $d->file_path && (bool) $d->external_url,
            'approvedBy' => $d->approver?->name,
            'approvedAt' => $d->approved_at?->translatedFormat('j M Y H:i'),
        ];
    }

    public function index(Request $request): Response
    {
        $user = $request->user();
        $filters = $request->only(['kind', 'project']);

        $query = Deliverable::visibleTo($user)->clientVisible()->with(['unit', 'project', 'approver']);
        if (! empty($filters['kind'])) {
            $query->where('kind', $filters['kind']);
        }
        if (! empty($filters['project'])) {
            $query->where('project_id', $filters['project']);
        }

        $counts = Deliverable::visibleTo($user)->clientVisible()
            ->selectRaw('kind, count(*) as total')->groupBy('kind')->pluck('total', 'kind');

        $access = $user->isClientAdmin()
            ? AuditLog::with('user')->where('institution_id', $user->institution_id)
                ->whereIn('action', ['deliverable.download', 'deliverable.approve', 'login'])
                ->latest('created_at')->limit(8)->get()
                ->map(fn ($a) => [
                    'who' => $a->user?->name, 'what' => $a->description,
                    'when' => $a->created_at->translatedFormat('j M H:i'),
                ])
            : [];

        return Inertia::render('Portal/Deliverables', [
            'deliverables' => $query->latest()->paginate(20)->withQueryString()
                ->through(fn ($d) => self::present($d)),
            'kinds' => collect(config('portal.deliverable_kinds'))
                ->map(fn ($label, $key) => ['key' => $key, 'label' => $label, 'count' => $counts[$key] ?? 0])->values(),
            'projects' => Project::visibleTo($user)->orderBy('name')->get(['id', 'name']),
            'filters' => $filters,
            'access' => $access,
        ]);
    }

    public function download(Request $request, Deliverable $deliverable): HttpResponse
    {
        $this->authorizeView($request, $deliverable);
        AuditLog::record('deliverable.download', $deliverable, 'Descargó «'.$deliverable->title.'»');

        if ($deliverable->file_path) {
            return Files::download($deliverable->file_path, $deliverable->file_name);
        }
        abort_unless($deliverable->external_url, 404);

        return redirect()->away($deliverable->external_url);
    }

    /**
     * Aprobación con re-autenticación. Se registra quién, cuándo, desde qué IP y la huella
     * del documento aprobado (fase 2: además se ancla en Stamping.io).
     */
    public function approve(Request $request, Deliverable $deliverable): RedirectResponse
    {
        $this->authorizeView($request, $deliverable);
        $request->validate(['password' => ['required', 'current_password']], [
            'password.current_password' => 'La contraseña no es correcta.',
        ]);
        abort_unless($deliverable->isPendingApproval(), 422, 'Este entregable no está pendiente de aprobación.');

        // Integridad: si el archivo está en el servidor, la huella debe coincidir con la registrada
        if ($deliverable->file_path && Storage::disk('local')->exists($deliverable->file_path)) {
            $current = hash_file('sha256', Storage::disk('local')->path($deliverable->file_path));
            if ($deliverable->sha256 && $current !== $deliverable->sha256) {
                return back()->with('error', 'La huella del archivo no coincide con la registrada. Contacte a su ejecutiva.');
            }
            $deliverable->sha256 = $current;
        }

        $deliverable->forceFill([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'approval_ip' => $request->ip(),
        ])->save();

        AuditLog::record('deliverable.approve', $deliverable, 'Aprobó «'.$deliverable->title.'»', [
            'sha256' => $deliverable->sha256,
        ]);

        return back()->with('success', 'Entregable aprobado. Su aprobación quedó registrada con la huella del documento.');
    }

    /** Recibe solo la huella calculada en el navegador; el archivo nunca se sube. */
    public function verify(Request $request): RedirectResponse
    {
        $data = $request->validate(['sha256' => ['required', 'regex:/^[a-f0-9]{64}$/i']]);
        $hash = strtolower($data['sha256']);
        $user = $request->user();

        $match = Deliverable::visibleTo($user)->clientVisible()->where('sha256', $hash)->first()
            ?? Contract::visibleTo($user)->where('sha256', $hash)->first();

        return back()->with('verification', $match ? [
            'ok' => true,
            'title' => $match->title,
            'date' => $match->created_at->translatedFormat('j M Y'),
            'hash' => $hash,
        ] : ['ok' => false, 'hash' => $hash]);
    }

    private function authorizeView(Request $request, Deliverable $deliverable): void
    {
        abort_unless(
            $deliverable->institution_id == $request->user()->institution_id && $deliverable->status !== 'draft',
            404
        );
    }
}
