<?php

namespace Database\Seeders;

use App\Modelos\Modelo_Institucion;
use App\Modelos\Modelo_Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Alta de la institución Banco Nacional de Bolivia S.A. Seguro para producción y repetible:
 *   php artisan db:seed --class=Sembrador_Banco_Nacional_Bolivia --force
 * Opcional: ADMIN_DELEGADO_EMAIL=persona@bnb.com.bo crea su administrador delegado e imprime una contraseña temporal.
 * Los productos contratados, colores, logo y el resto del equipo se cargan desde el CMS.
 */
class Sembrador_Banco_Nacional_Bolivia extends Seeder
{
    public function run(): void
    {
        $this->call(Sembrador_Unidades_Negocio::class);

        $Banco = Modelo_Institucion::firstOrCreate(['identificador' => 'banco-nacional-de-bolivia'], [
            'nombre' => 'Banco Nacional de Bolivia S.A.', 'razon_social' => 'Banco Nacional de Bolivia S.A.',
        ]);
        $this->command?->info("Institución: {$Banco->nombre} (id {$Banco->id})");

        $Email_Administrador = env('ADMIN_DELEGADO_EMAIL');
        if (! $Email_Administrador) {
            return;
        }
        if (Modelo_Usuario::where('email', $Email_Administrador)->where('institucion_id', $Banco->id)->exists()) {
            $this->command?->warn("Ya existe un usuario con {$Email_Administrador}; no se modificó.");

            return;
        }
        $Contrasena_Temporal = Str::password(16, symbols: false);
        Modelo_Usuario::create([
            'nombre' => env('ADMIN_DELEGADO_NOMBRE', 'Administrador delegado'), 'email' => $Email_Administrador,
            'password' => $Contrasena_Temporal, 'rol' => Modelo_Usuario::Rol_Administrador_Delegado, 'institucion_id' => $Banco->id,
        ]);
        $this->command?->info("Administrador delegado: {$Email_Administrador} / {$Contrasena_Temporal} (cámbiela al ingresar)");
    }
}
