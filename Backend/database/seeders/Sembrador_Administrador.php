<?php

namespace Database\Seeders;

use App\Modelos\Modelo_Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Primer acceso en producción:
 *   ADMIN_EMAIL=tu@correo php artisan db:seed --class=Sembrador_Administrador --force
 * Imprime una contraseña aleatoria; cámbiela al ingresar.
 */
class Sembrador_Administrador extends Seeder
{
    public function run(): void
    {
        $Email_Administrador = env('ADMIN_EMAIL', 'admin@blockfinityadvisors.com');
        $Contrasena_Temporal = Str::password(16, symbols: false);

        Modelo_Usuario::updateOrCreate(['email' => $Email_Administrador, 'institucion_id' => null], [
            'nombre' => 'Administrador', 'rol' => Modelo_Usuario::Rol_Superadmin, 'password' => $Contrasena_Temporal, 'esta_activo' => true,
        ]);

        $this->command?->info("Admin: {$Email_Administrador} / {$Contrasena_Temporal}");
    }
}
