<?php

use App\Middleware\Middleware_Cabeceras_Seguridad;
use App\Middleware\Middleware_Compartir_Datos_Inertia;
use App\Middleware\Middleware_Exigir_Usuario_Activo;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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
        $Middleware->redirectGuestsTo('/login');
        $Middleware->redirectUsersTo('/');
    })
    ->withExceptions(function (Exceptions $Excepciones): void {
        //
    })->create();
