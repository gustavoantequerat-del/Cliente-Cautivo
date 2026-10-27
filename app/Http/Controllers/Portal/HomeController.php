<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\BusinessUnit;
use App\Models\Deliverable;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ResearchReport;
use App\Models\Video;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $institution = $user->institution->load('units');

        $projects = Project::visibleTo($user)->where('status', 'active')
            ->with(['unit', 'phases'])->latest()->get()
            ->map(fn (Project $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'unit' => $p->unit?->code,
                'phase' => $p->phases->firstWhere('status', 'current')?->name,
                'progress' => $p->progress,
                'nextMilestone' => $p->next_milestone,
            ]);

        $pendingApprovals = Deliverable::visibleTo($user)->where('status', 'pending_approval')->count();

        $report = $institution->hasUnit('RESEARCH')
            ? ResearchReport::visibleTo($user)->where('is_published', true)
                ->orderByDesc('is_featured')->orderByDesc('published_on')->first()
            : null;

        $webinar = Video::visibleTo($user)->where('is_published', true)->where('kind', 'live')
            ->where('scheduled_at', '>=', now())->orderBy('scheduled_at')->first();

        $invoices = Invoice::visibleTo($user)->where('status', 'pending')->orderBy('due_on')->get();

        $units = BusinessUnit::orderBy('sort')->get()->map(function (BusinessUnit $u) use ($institution) {
            $contracted = $institution->units->firstWhere('id', $u->id);

            return [
                'code' => $u->code,
                'name' => $u->name,
                'description' => $u->description,
                'active' => (bool) $contracted,
                'note' => $contracted?->pivot->note,
            ];
        });

        return Inertia::render('Portal/Home', [
            'today' => ucfirst(now()->translatedFormat('l j \d\e F \d\e Y')),
            'projects' => $projects,
            'pendingApprovals' => $pendingApprovals,
            'report' => $report ? [
                'id' => $report->id, 'title' => $report->title, 'summary' => $report->summary,
                'author' => $report->author, 'minutes' => $report->reading_minutes,
            ] : null,
            'webinar' => $webinar ? [
                'id' => $webinar->id, 'title' => $webinar->title,
                'date' => $webinar->scheduled_at->translatedFormat('j M · H:i'),
            ] : null,
            'invoices' => [
                'count' => $invoices->count(),
                'next' => $invoices->first() ? [
                    'due' => $invoices->first()->due_on?->translatedFormat('j \d\e F'),
                    'amount' => number_format((float) $invoices->first()->amount, 2, ',', '.'),
                    'currency' => $invoices->first()->currency,
                ] : null,
            ],
            'hours' => $institution->hourBalance(),
            'level' => $institution->level(),
            'units' => $units,
        ]);
    }
}
