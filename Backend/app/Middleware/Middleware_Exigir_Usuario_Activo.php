<?php

namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** Si se revoca la credencial (esta_activo = false) la sesión se corta en la siguiente petición */
class Middleware_Exigir_Usuario_Activo
{
    public function handle(Request $Peticion, Closure $Siguiente): Response
    {
        if ($Peticion->user() && ! $Peticion->user()->esta_activo) {
            $Login_Correspondiente = $Peticion->user()->Es_Staff() ? '/admin/login' : '/login';
            Auth::logout();
            $Peticion->session()->invalidate();

            return redirect($Login_Correspondiente)->with('Mensaje_Error', 'Su credencial fue desactivada. Contacte a su administrador.');
        }

        return $Siguiente($Peticion);
    }
}
