<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Primer acceso en producción:
 *   ADMIN_EMAIL=tu@correo php artisan db:seed --class=AdminSeeder --force
 * Imprime una contraseña aleatoria; cámbiela al ingresar.
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@blockfinityadvisors.com');
        $password = Str::password(16, symbols: false);

        User::updateOrCreate(['email' => $email], [
            'name' => 'Administrador', 'role' => 'admin', 'password' => $password, 'is_active' => true,
        ]);

        $this->command?->info("Admin: {$email} / {$password}");
    }
}
