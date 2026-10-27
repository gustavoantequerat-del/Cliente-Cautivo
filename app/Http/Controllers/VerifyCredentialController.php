<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

/** Página pública a la que apunta el QR de la credencial */
class VerifyCredentialController extends Controller
{
    public function __invoke(string $code): Response
    {
        $user = User::with('institution')->where('credential_code', $code)->first();

        return Inertia::render('Public/VerifyCredential', [
            'credential' => $user ? [
                'valid' => $user->is_active && $user->institution?->is_active,
                'name' => $user->name,
                'area' => $user->area,
                'institution' => $user->institution?->name,
                'number' => $user->credential_number,
                'issuedAt' => $user->credential_issued_at?->format('d/m/Y'),
            ] : null,
        ]);
    }
}
