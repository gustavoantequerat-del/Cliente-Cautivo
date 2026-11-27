<?php

namespace App\Controladores\Publico;

use App\Modelos\Modelo_Institucion;
use App\Modelos\Modelo_Registro_Auditoria;
use App\Modelos\Modelo_Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as Regla_Contrasena;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as Respuesta_Http;

/**
 * Registro de usuarios institucionales con el link propio de cada institución (/registro/{codigo}).
 * Una persona puede registrarse en varias instituciones con el mismo correo: cada cuenta es independiente
 * (sus datos no se comparten), pero no puede tener dos cuentas en la misma institución.
 */
class Controlador_Registro
{
    public function Mostrar_Formulario_Registro(string $Codigo_Registro): Response
    {
        $Institucion = $this->Buscar_Institucion($Codigo_Registro);

        return Inertia::render('Acceso/Pagina_Registro', [
            'Institucion' => $Institucion ? [
                'Nombre' => $Institucion->nombre,
                'Url_Logo' => $Institucion->ruta_logo ? "/registro/{$Codigo_Registro}/logo" : null,
            ] : null,
            'Codigo_Registro' => $Codigo_Registro,
        ]);
    }

    public function Registrar_Usuario(Request $Peticion, string $Codigo_Registro): RedirectResponse
    {
        $Institucion = $this->Buscar_Institucion($Codigo_Registro);
        abort_unless($Institucion, 404);

        $Datos = $Peticion->validate([
            'nombre' => ['required', 'string', 'max:120', "regex:/^[\\pL\\s'.-]+$/u"],
            'email' => ['required', 'email', 'max:255',
                Rule::unique('usuarios', 'email')->where('institucion_id', $Institucion->id)],
            'area' => ['nullable', 'string', 'max:120'],
            'cargo' => ['nullable', 'string', 'max:120'],
            'telefono' => ['nullable', 'regex:/^\\+?[0-9]{6,15}$/'],
            'password' => ['required', 'confirmed', Regla_Contrasena::min(10)->letters()->numbers()],
        ], [
            'email.unique' => 'Ya tiene una cuenta en esta institución. Ingrese con su correo y contraseña.',
        ]);

        $Usuario = Modelo_Usuario::create($Datos + [
            'institucion_id' => $Institucion->id, 'rol' => Modelo_Usuario::Rol_Usuario_Institucional, 'esta_activo' => true,
        ]);

        // Si había otra sesión abierta (ej. su cuenta de otra institución), se reemplaza por la nueva
        Auth::login($Usuario);
        $Peticion->session()->regenerate();
        $Usuario->forceFill(['fecha_ultimo_ingreso' => now()])->saveQuietly();
        Modelo_Registro_Auditoria::Registrar_Evento('registro', $Usuario, 'Se registró con el link de la institución');

        return redirect('/')->with('Mensaje_Exito', "Bienvenido/a al portal de {$Institucion->nombre}. Su cuenta está lista.");
    }

    public function Mostrar_Logo(string $Codigo_Registro): Respuesta_Http
    {
        $Ruta_Logo = $this->Buscar_Institucion($Codigo_Registro)?->ruta_logo;
        abort_unless($Ruta_Logo && Storage::disk('local')->exists($Ruta_Logo), 404);

        return response()->file(Storage::disk('local')->path($Ruta_Logo), ['Cache-Control' => 'public, max-age=86400', 'X-Content-Type-Options' => 'nosniff']);
    }

    /** Solo instituciones activas y con el registro abierto */
    private function Buscar_Institucion(string $Codigo_Registro): ?Modelo_Institucion
    {
        return Modelo_Institucion::where('codigo_registro', $Codigo_Registro)->where('esta_activa', true)->where('registro_abierto', true)->first();
    }
}
