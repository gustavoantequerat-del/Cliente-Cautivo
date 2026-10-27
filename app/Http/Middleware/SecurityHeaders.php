<?php

namespace App\Http\Middleware;

use App\Support\VideoEmbed;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Solo se permiten iframes de los proveedores de video autorizados */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        if (! app()->environment('local')) {
            // En local Vite sirve desde otro puerto; la CSP completa se aplica en producción
            $response->headers->set('Content-Security-Policy', "frame-src 'self' ".implode(' ', VideoEmbed::FRAME_HOSTS)."; frame-ancestors 'self'; object-src 'none'; base-uri 'self'");
        }

        return $response;
    }
}
