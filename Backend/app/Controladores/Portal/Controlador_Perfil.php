<?php

namespace App\Controladores\Portal;

use App\Modelos\Modelo_Registro_Auditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password as Regla_Contrasena;
use Inertia\Inertia;
use Inertia\Response;

class Controlador_Perfil
{
    public function Mostrar_Perfil(): Response
    {
        return Inertia::render('Portal/Pagina_Perfil');
    }

    public function Cambiar_Contrasena(Request $Peticion): RedirectResponse
    {
        $Datos = $Peticion->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Regla_Contrasena::min(10)->letters()->numbers()],
        ], ['current_password.current_password' => 'La contraseña actual no es correcta.']);

        $Peticion->user()->update(['password' => $Datos['password']]);
        Modelo_Registro_Auditoria::Registrar_Evento('perfil.cambiar_contrasena', null, 'Cambió su contraseña');

        return back()->with('Mensaje_Exito', 'Contraseña actualizada.');
    }
}
