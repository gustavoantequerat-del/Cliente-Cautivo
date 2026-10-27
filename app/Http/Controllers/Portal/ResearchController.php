<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ResearchReport;
use App\Support\Files;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ResearchController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $topic = $request->query('tema');

        $query = ResearchReport::visibleTo($user)->where('is_published', true);
        $featured = (clone $query)->where('is_featured', true)->orderByDesc('published_on')->first();

        if ($topic) {
            $query->where('topic', $topic);
        }

        return Inertia::render('Portal/Research', [
            'featured' => $featured ? $this->present($featured) : null,
            'reports' => $query->orderByDesc('published_on')->paginate(12)->withQueryString()
                ->through(fn ($r) => $this->present($r)),
            'topics' => config('portal.research_topics'),
            'topic' => $topic,
        ]);
    }

    public function open(Request $request, ResearchReport $report): HttpResponse
    {
        $visible = ResearchReport::visibleTo($request->user())->where('is_published', true)->whereKey($report->id)->exists();
        abort_unless($visible, 404);
        AuditLog::record('research.open', $report, 'Abrió el reporte «'.$report->title.'»');

        if ($report->file_path) {
            return Files::download($report->file_path, $report->file_name);
        }
        abort_unless($report->external_url, 404);

        return redirect()->away($report->external_url);
    }

    private function present(ResearchReport $r): array
    {
        return [
            'id' => $r->id,
            'title' => $r->title,
            'summary' => $r->summary,
            'topic' => $r->topic,
            'author' => $r->author,
            'minutes' => $r->reading_minutes,
            'date' => $r->published_on?->translatedFormat('M Y'),
            'exclusive' => (bool) $r->institution_id,
            'hasFile' => (bool) ($r->file_path || $r->external_url),
        ];
    }
}
