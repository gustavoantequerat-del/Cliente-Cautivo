<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Video;
use App\Models\VideoProgress;
use App\Support\VideoEmbed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AcademyController extends Controller
{
    private const KINDS = ['live' => 'En vivo', 'recorded' => 'Grabado', 'pill' => 'Píldora'];

    public function index(Request $request): Response
    {
        $user = $request->user();
        $kind = $request->query('formato');
        $topic = $request->query('tema');

        $progress = VideoProgress::where('user_id', $user->id)->get()->keyBy('video_id');

        $query = Video::visibleTo($user)->where('is_published', true);
        if ($kind && isset(self::KINDS[$kind])) {
            $query->where('kind', $kind);
        }
        if ($topic) {
            $query->where('topic', $topic);
        }

        $nextLive = Video::visibleTo($user)->where('is_published', true)->where('kind', 'live')
            ->where('scheduled_at', '>=', now())->orderBy('scheduled_at')->first();

        $completed = $progress->whereNotNull('completed_at');
        $minutes = Video::whereIn('id', $completed->keys())->sum('duration_minutes');

        return Inertia::render('Portal/Academy/Index', [
            'nextLive' => $nextLive ? $this->present($nextLive, $progress->get($nextLive->id)) : null,
            'videos' => $query->orderByRaw("case when kind = 'live' then 0 else 1 end")
                ->orderByDesc('scheduled_at')->latest()->paginate(18)->withQueryString()
                ->through(fn ($v) => $this->present($v, $progress->get($v->id))),
            'stats' => [
                'hours' => round($minutes / 60, 1),
                'completed' => $completed->count(),
            ],
            'kinds' => self::KINDS,
            'topics' => config('portal.video_topics'),
            'filters' => ['formato' => $kind, 'tema' => $topic],
        ]);
    }

    public function show(Request $request, Video $video): Response
    {
        $this->authorizeView($request, $video);
        $progress = VideoProgress::where('user_id', $request->user()->id)->where('video_id', $video->id)->first();

        return Inertia::render('Portal/Academy/Show', [
            'video' => $this->present($video, $progress) + [
                'description' => $video->description,
                'embedUrl' => $video->embedUrl(),
                'liveUrl' => $video->kind === 'live' ? $video->live_url : null,
            ],
        ]);
    }

    /** "Marcar como visto": declarado por el usuario (Drive no permite medir la reproducción) */
    public function complete(Request $request, Video $video): RedirectResponse
    {
        $this->authorizeView($request, $video);
        $user = $request->user();

        $awarded = DB::transaction(function () use ($user, $video) {
            $progress = VideoProgress::firstOrNew(['video_id' => $video->id, 'user_id' => $user->id]);
            if ($progress->completed_at) {
                return false;
            }
            $progress->completed_at = now();
            $progress->save();
            $user->institution->increment('alliance_points', $video->points);

            return true;
        });

        if ($awarded) {
            AuditLog::record('video.complete', $video, 'Completó «'.$video->title.'»', ['points' => $video->points]);
        }

        return back()->with('success', $awarded
            ? "¡Listo! Sumó {$video->points} puntos de alianza para su institución."
            : 'Este contenido ya estaba marcado como visto.');
    }

    public function register(Request $request, Video $video): RedirectResponse
    {
        $this->authorizeView($request, $video);
        abort_unless($video->kind === 'live', 422);

        VideoProgress::updateOrCreate(
            ['video_id' => $video->id, 'user_id' => $request->user()->id],
            ['registered_at' => now()]
        );
        AuditLog::record('video.register', $video, 'Se inscribió a «'.$video->title.'»');

        return back()->with('success', 'Inscripción confirmada. Descargue el evento para su calendario.');
    }

    /** Archivo .ics para "Añadir al calendario" (sin dependencias) */
    public function ics(Request $request, Video $video)
    {
        $this->authorizeView($request, $video);
        abort_unless($video->scheduled_at, 404);

        $start = $video->scheduled_at->copy()->utc();
        $end = $start->copy()->addMinutes($video->duration_minutes ?: 60);
        $esc = fn ($s) => addcslashes(preg_replace('/\s+/', ' ', (string) $s), ',;\\');
        $lines = [
            'BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//Blockfinity//Portal Cliente//ES', 'BEGIN:VEVENT',
            'UID:video-'.$video->id.'@'.$request->getHost(),
            'DTSTAMP:'.now()->utc()->format('Ymd\THis\Z'),
            'DTSTART:'.$start->format('Ymd\THis\Z'),
            'DTEND:'.$end->format('Ymd\THis\Z'),
            'SUMMARY:'.$esc($video->title),
            'DESCRIPTION:'.$esc(trim(($video->speaker ? $video->speaker.'. ' : '').($video->live_url ?? ''))),
            'URL:'.route('portal.academy.show', $video),
            'END:VEVENT', 'END:VCALENDAR',
        ];

        return response(implode("\r\n", $lines)."\r\n", 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="webinar-'.$video->id.'.ics"',
        ]);
    }

    private function present(Video $v, ?VideoProgress $p): array
    {
        return [
            'id' => $v->id,
            'kind' => $v->kind,
            'kindLabel' => self::KINDS[$v->kind] ?? $v->kind,
            'title' => $v->title,
            'topic' => $v->topic,
            'speaker' => $v->speaker,
            'duration' => $v->duration_minutes,
            'points' => $v->points,
            'scheduledAt' => $v->scheduled_at?->translatedFormat('l j \d\e F · H:i'),
            'upcoming' => $v->kind === 'live' && $v->scheduled_at?->isFuture(),
            'thumbnail' => $v->thumbnail_url ?: VideoEmbed::thumbnail($v->provider, $v->external_id),
            'provider' => $v->provider,
            'completed' => (bool) $p?->completed_at,
            'registered' => (bool) $p?->registered_at,
        ];
    }

    private function authorizeView(Request $request, Video $video): void
    {
        abort_unless($video->is_published && (! $video->institution_id || $video->institution_id == $request->user()->institution_id), 404);
    }
}
