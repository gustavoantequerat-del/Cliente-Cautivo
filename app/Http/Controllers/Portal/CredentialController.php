<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VideoProgress;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CredentialController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user();
        $institution = $user->institution;
        $level = $institution->level();

        return Inertia::render('Portal/Credential', [
            'card' => [
                'name' => $user->name,
                'area' => $user->area,
                'institution' => $institution->name,
                'number' => $user->credential_number,
                'issuedAt' => $user->credential_issued_at?->format('d·m·Y'),
                'verifyUrl' => $user->credential_code ? route('credential.verify', $user->credential_code) : null,
            ],
            'level' => $level,
            'levels' => config('portal.levels'),
            'completedVideos' => VideoProgress::where('user_id', $user->id)->whereNotNull('completed_at')->count(),
            'members' => $user->isClientAdmin()
                ? User::where('institution_id', $institution->id)->orderBy('name')->get()->map(fn ($u) => [
                    'name' => $u->name, 'area' => $u->area, 'number' => $u->credential_number,
                    'active' => $u->is_active, 'lastLogin' => $u->last_login_at?->translatedFormat('j M Y'),
                ])
                : null,
        ]);
    }
}
