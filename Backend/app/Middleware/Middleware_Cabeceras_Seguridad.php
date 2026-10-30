<?php

namespace App\Middleware;

use App\Servicios\Servicio_Enlaces_Video;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Solo se permiten iframes de los proveedores de video autorizados */
class Middleware_Cabeceras_Seguridad
{
    public function handle(Request $Peticion, Closure $Siguiente): Response
    {
        $Respuesta = $Siguiente($Peticion);

        $Respuesta->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $Respuesta->headers->set('X-Content-Type-Options', 'nosniff');
        $Respuesta->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        if (! app()->environment('local')) {
            // En local Vite sirve desde otro puerto; la CSP completa se aplica en producción
            $Dominios_Video = implode(' ', Servicio_Enlaces_Video::Dominios_Permitidos_Iframe);
            $Respuesta->headers->set('Content-Security-Policy', "frame-src 'self' {$Dominios_Video}; frame-ancestors 'self'; object-src 'none'; base-uri 'self'");
        }

        return $Respuesta;
    }
}
