<?php

namespace App\Controladores\Acceso;

use App\Modelos\Modelo_Registro_Auditoria;
use App\Modelos\Modelo_Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        return $this->Autenticar_Usuario($Peticion, Debe_Ser_Personal_Blockfinity: false) ?? redirect()->intended('/');
    }

    public function Iniciar_Sesion_Administracion(Request $Peticion): RedirectResponse
    {
        return $this->Autenticar_Usuario($Peticion, Debe_Ser_Personal_Blockfinity: true) ?? redirect()->intended('/admin');
    }

    public function Cerrar_Sesion(Request $Peticion): RedirectResponse
    {
        $Era_Personal_Blockfinity = $Peticion->user()->Es_Personal_Blockfinity();
        Auth::logout();
        $Peticion->session()->invalidate();
        $Peticion->session()->regenerateToken();

        return redirect($Era_Personal_Blockfinity ? '/admin/login' : '/login');
    }

    /**
     * El mismo correo puede tener una cuenta en cada institución (cada una con su contraseña y sus datos).
     * Si la contraseña coincide en más de una, se le pide elegir la institución y se vuelve a enviar con institucion_id.
     */
    private function Autenticar_Usuario(Request $Peticion, bool $Debe_Ser_Personal_Blockfinity): ?RedirectResponse
    {
        $Credenciales = $Peticion->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'institucion_id' => ['nullable', 'integer'],
        ]);
        $Roles_Permitidos = $Debe_Ser_Personal_Blockfinity
            ? [Modelo_Usuario::Rol_Superadmin, Modelo_Usuario::Rol_Usuario_Ejecutivo]
            : [Modelo_Usuario::Rol_Usuario_Institucional, Modelo_Usuario::Rol_Administrador_Delegado];

        $Cuentas_Validas = Modelo_Usuario::with('Institucion')->where('email', $Credenciales['email'])->where('esta_activo', true)
            ->whereIn('rol', $Roles_Permitidos)->get()
            ->filter(fn (Modelo_Usuario $Cuenta) => Hash::check($Credenciales['password'], $Cuenta->password))->values();
        if ($Cuentas_Validas->isEmpty()) {
            $this->Registrar_Puerta_Equivocada($Credenciales, $Roles_Permitidos);
            throw ValidationException::withMessages(['email' => 'Las credenciales no coinciden o su acceso está desactivado.']);
        }

        if ($Cuentas_Validas->count() > 1) {
            $Cuenta_Elegida = $Cuentas_Validas->firstWhere('institucion_id', $Credenciales['institucion_id'] ?? null);
            if (! $Cuenta_Elegida) {
                // El formulario muestra las instituciones y reenvía con la elegida (la contraseña ya se comprobó)
                return back()->with('Instituciones_Para_Elegir', $Cuentas_Validas->map(fn (Modelo_Usuario $Cuenta) => [
                    'Id' => $Cuenta->institucion_id, 'Nombre' => $Cuenta->Institucion?->nombre,
                ])->all());
            }
        }
        $Cuenta = $Cuenta_Elegida ?? $Cuentas_Validas->first();

        Auth::login($Cuenta, $Peticion->boolean('recordarme'));
        $Peticion->session()->regenerate();
        $Cuenta->forceFill(['fecha_ultimo_ingreso' => now()])->saveQuietly();
        Modelo_Registro_Auditoria::Registrar_Evento('inicio_sesion', null, 'Inicio de sesión');

        return null;
    }

    /** Una cuenta válida que intenta entrar por el acceso que no le corresponde recibe el error genérico, pero queda auditada */
    private function Registrar_Puerta_Equivocada(array $Credenciales, array $Roles_Permitidos): void
    {
        $Cuenta = Modelo_Usuario::where('email', $Credenciales['email'])->where('esta_activo', true)->whereNotIn('rol', $Roles_Permitidos)->get()
            ->first(fn (Modelo_Usuario $Otra) => Hash::check($Credenciales['password'], $Otra->password));
        if ($Cuenta) {
            Auth::login($Cuenta);
            Modelo_Registro_Auditoria::Registrar_Evento('inicio_sesion.puerta_equivocada', null, 'Intentó ingresar por el acceso que no le corresponde');
            Auth::logout();
        }
    }
}
