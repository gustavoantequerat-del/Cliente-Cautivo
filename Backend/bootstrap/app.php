<?php

use App\Middleware\Middleware_Cabeceras_Seguridad;
use App\Middleware\Middleware_Compartir_Datos_Inertia;
use App\Middleware\Middleware_Exigir_Usuario_Activo;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/Rutas_Web.php',
        commands: __DIR__.'/../routes/Rutas_Consola.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $Middleware): void {
        $Middleware->web(append: [
            Middleware_Exigir_Usuario_Activo::class,
            Middleware_Compartir_Datos_Inertia::class,
            Middleware_Cabeceras_Seguridad::class,
        ]);
        // Cada visitante va a su propio login: el staff a /admin/login y los clientes a /login
        $Middleware->redirectGuestsTo(fn (Request $Peticion) => $Peticion->is('admin', 'admin/*') ? '/admin/login' : '/login');
        $Middleware->redirectUsersTo(fn (Request $Peticion) => $Peticion->user()?->Es_Personal_Blockfinity() ? '/admin' : '/');
    })
    ->withExceptions(function (Exceptions $Excepciones): void {
        //
    })->create();
