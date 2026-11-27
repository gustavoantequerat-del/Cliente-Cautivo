<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Registro de clientes por institución:
 * - Cada institución tiene un link de registro propio (codigo_registro) que se puede cerrar o regenerar.
 * - El mismo correo puede tener una cuenta en cada institución (datos separados), pero solo una por institución.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instituciones', function (Blueprint $Tabla) {
            $Tabla->string('codigo_registro', 40)->nullable()->unique();
            $Tabla->boolean('registro_abierto')->default(true);
        });
        foreach (DB::table('instituciones')->pluck('id') as $Id_Institucion) {
            DB::table('instituciones')->where('id', $Id_Institucion)->update(['codigo_registro' => Str::random(32)]);
        }

        Schema::table('usuarios', function (Blueprint $Tabla) {
            $Tabla->dropUnique(['email']);
            $Tabla->unique(['email', 'institucion_id']);
            $Tabla->index('email');
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $Tabla) {
            $Tabla->dropIndex(['email']);
            $Tabla->dropUnique(['email', 'institucion_id']);
            $Tabla->unique('email');
        });
        Schema::table('instituciones', function (Blueprint $Tabla) {
            $Tabla->dropUnique(['codigo_registro']);
            $Tabla->dropColumn(['codigo_registro', 'registro_abierto']);
        });
    }
};
