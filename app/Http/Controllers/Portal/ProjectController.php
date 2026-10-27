<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Deliverable;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(Request $request): Response
    {
        $projects = Project::visibleTo($request->user())->with(['unit', 'phases'])
            ->orderByRaw("case when status = 'active' then 0 else 1 end")->latest()->get()
            ->map(fn (Project $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'unit' => $p->unit?->code,
                'status' => $p->status,
                'progress' => $p->progress,
                'phase' => $p->phases->firstWhere('status', 'current')?->name,
                'phaseIndex' => $p->phases->search(fn ($ph) => $ph->status === 'current'),
                'phaseCount' => $p->phases->count(),
                'nextMilestone' => $p->next_milestone,
            ]);

        return Inertia::render('Portal/Projects/Index', ['projects' => $projects]);
    }

    public function show(Request $request, Project $project): Response
    {
        abort_unless($project->institution_id == $request->user()->institution_id, 404);
        $project->load(['unit', 'phases', 'updates']);

        $deliverables = $project->deliverables()->clientVisible()->with('approver')->orderBy('created_at')->get();

        return Inertia::render('Portal/Projects/Show', [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'unit' => $project->unit?->code,
                'status' => $project->status,
                'progress' => $project->progress,
                'nextMilestone' => $project->next_milestone,
                'startsOn' => $project->starts_on?->translatedFormat('j M Y'),
                'endsOn' => $project->ends_on?->translatedFormat('j M Y'),
                'phases' => $project->phases->map(fn ($ph) => [
                    'position' => $ph->position, 'name' => $ph->name, 'status' => $ph->status,
                    'due' => $ph->due_on?->translatedFormat('j M'),
                ]),
                'updates' => $project->updates->take(15)->map(fn ($u) => [
                    'date' => $u->happened_on->translatedFormat('j M'), 'body' => $u->body,
                ]),
            ],
            'deliverables' => $deliverables->map(fn (Deliverable $d) => DeliverableController::present($d)),
        ]);
    }
}
