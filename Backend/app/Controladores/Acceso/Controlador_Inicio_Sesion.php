<?php

namespace App\Controladores\Acceso;

use App\Modelos\Modelo_Registro_Auditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class Controlador_Inicio_Sesion
{
    public function Mostrar_Formulario_Ingreso(): Response
    {
        return Inertia::render('Acceso/Pagina_Ingresar');
    }

    public function Iniciar_Sesion(Request $Peticion): RedirectResponse
    {
        $Credenciales = $Peticion->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($Credenciales + ['esta_activo' => true], $Peticion->boolean('recordarme'))) {
            throw ValidationException::withMessages([
                'email' => 'Las credenciales no coinciden o su acceso está desactivado.',
            ]);
        }

        $Peticion->session()->regenerate();
        $Usuario = $Peticion->user();
        $Usuario->forceFill(['fecha_ultimo_ingreso' => now()])->saveQuietly();
        Modelo_Registro_Auditoria::Registrar_Evento('inicio_sesion', null, 'Inicio de sesión');

        return redirect()->intended($Usuario->Es_Staff() && ! $Usuario->institucion_id ? '/admin' : '/');
    }

    public function Cerrar_Sesion(Request $Peticion): RedirectResponse
    {
        Auth::logout();
        $Peticion->session()->invalidate();
        $Peticion->session()->regenerateToken();

        return redirect('/login');
    }
}
