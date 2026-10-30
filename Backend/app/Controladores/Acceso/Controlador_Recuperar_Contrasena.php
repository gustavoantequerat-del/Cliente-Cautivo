<?php

namespace App\Controladores\Acceso;

use App\Modelos\Modelo_Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as Regla_Contrasena;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/** Usa el "password broker" de Laravel: por eso los campos se llaman email, password y token */
class Controlador_Recuperar_Contrasena
{
    public function Mostrar_Formulario_Recuperacion(): Response
    {
        return Inertia::render('Acceso/Pagina_Recuperar_Contrasena');
    }

    public function Enviar_Enlace_Recuperacion(Request $Peticion): RedirectResponse
    {
        $Peticion->validate(['email' => ['required', 'email']]);
        Password::sendResetLink($Peticion->only('email'));

        // Mismo mensaje exista o no el correo (no revela usuarios)
        return back()->with('Mensaje_Exito', 'Si el correo está registrado, recibirá un enlace para restablecer su contraseña.');
    }

    public function Mostrar_Formulario_Nueva_Contrasena(Request $Peticion, string $Token): Response
    {
        return Inertia::render('Acceso/Pagina_Nueva_Contrasena', ['Token' => $Token, 'Email' => $Peticion->query('email')]);
    }

    public function Guardar_Nueva_Contrasena(Request $Peticion): RedirectResponse
    {
        $Peticion->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Regla_Contrasena::min(10)->letters()->numbers()],
        ]);

        $Login_Correspondiente = '/login';
        $Resultado = Password::reset(
            $Peticion->only('email', 'password', 'password_confirmation', 'token'),
            function (Modelo_Usuario $Usuario, string $Nueva_Contrasena) use (&$Login_Correspondiente) {
                $Usuario->forceFill(['password' => $Nueva_Contrasena])->setRememberToken(null);
                $Usuario->save();
                $Login_Correspondiente = $Usuario->Es_Staff() ? '/admin/login' : '/login';
            }
        );

        if ($Resultado !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => 'El enlace no es válido o expiró.']);
        }

        return redirect($Login_Correspondiente)->with('Mensaje_Exito', 'Contraseña actualizada. Ya puede ingresar.');
    }
}
