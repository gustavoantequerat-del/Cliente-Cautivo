<?php

namespace App\Controladores\Acceso;

use App\Modelos\Modelo_Registro_Auditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Dos puertas separadas por seguridad: /login solo para clientes y /admin/login solo para el personal de Blockfinity.
 * Una cuenta que entra por la puerta equivocada recibe el mismo error que una contraseña incorrecta.
 */
class Controlador_Inicio_Sesion
{
    public function Mostrar_Formulario_Ingreso(): Response
    {
        return Inertia::render('Acceso/Pagina_Ingresar');
    }

    public function Mostrar_Formulario_Ingreso_Administracion(): Response
    {
        return Inertia::render('Acceso/Pagina_Ingresar_Administracion');
    }

    public function Iniciar_Sesion(Request $Peticion): RedirectResponse
    {
        $this->Autenticar_Usuario($Peticion, Debe_Ser_Staff: false);

        return redirect()->intended('/');
    }

    public function Iniciar_Sesion_Administracion(Request $Peticion): RedirectResponse
    {
        $this->Autenticar_Usuario($Peticion, Debe_Ser_Staff: true);

        return redirect()->intended('/admin');
    }

    public function Cerrar_Sesion(Request $Peticion): RedirectResponse
    {
        $Era_Staff = $Peticion->user()->Es_Personal_Blockfinity();
        Auth::logout();
        $Peticion->session()->invalidate();
        $Peticion->session()->regenerateToken();

        return redirect($Era_Staff ? '/admin/login' : '/login');
    }

    private function Autenticar_Usuario(Request $Peticion, bool $Debe_Ser_Staff): void
    {
        $Credenciales = $Peticion->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $Es_Valido = Auth::attempt($Credenciales + ['esta_activo' => true], $Peticion->boolean('recordarme'));
        if ($Es_Valido && $Peticion->user()->Es_Personal_Blockfinity() !== $Debe_Ser_Staff) {
            Modelo_Registro_Auditoria::Registrar_Evento('inicio_sesion.puerta_equivocada', null, 'Intentó ingresar por el acceso que no le corresponde');
            Auth::logout();
            $Es_Valido = false;
        }
        if (! $Es_Valido) {
            throw ValidationException::withMessages([
                'email' => 'Las credenciales no coinciden o su acceso está desactivado.',
            ]);
        }

        $Peticion->session()->regenerate();
        $Peticion->user()->forceFill(['fecha_ultimo_ingreso' => now()])->saveQuietly();
        Modelo_Registro_Auditoria::Registrar_Evento('inicio_sesion', null, 'Inicio de sesión');
    }
}
