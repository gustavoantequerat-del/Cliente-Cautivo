<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        $user = $request->user();
        $institution = $user?->institution?->loadMissing(['units', 'accountManager']);
        $manager = $institution?->accountManager;

        return array_merge(parent::share($request), [
            'appName' => config('app.name'),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'initials' => $user->initials(),
                    'area' => $user->area,
                    'role' => $user->role,
                    'isAdmin' => $user->isAdmin(),
                    'isClientAdmin' => $user->isClientAdmin(),
                    'credentialNumber' => $user->credential_number,
                ] : null,
            ],
            'institution' => $institution ? [
                'name' => $institution->name,
                'units' => $institution->units->pluck('code')->values(),
            ] : null,
            'accountManager' => $manager ? [
                'name' => $manager->name,
                'initials' => $manager->initials(),
                'position' => $manager->position,
                'email' => $manager->email,
                'phone' => $manager->phone,
                'bookingUrl' => $manager->booking_url,
            ] : null,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'verification' => fn () => $request->session()->get('verification'),
            ],
        ]);
    }
}
