<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Portal/Profile');
    }

    public function password(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()],
        ], ['current_password.current_password' => 'La contraseña actual no es correcta.']);

        $request->user()->update(['password' => $data['password']]);
        AuditLog::record('profile.password', null, 'Cambió su contraseña');

        return back()->with('success', 'Contraseña actualizada.');
    }
}
