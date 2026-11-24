<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const Roles_Nuevos = [
        'cliente_basico' => 'usuario_institucional',
        'cliente_institucional' => 'administrador_delegado',
        'ejecutivo_cuenta' => 'usuario_ejecutivo',
        'staff' => 'superadmin',
    ];

    public function up(): void
    {
        // Nuevos nombres de rol
        foreach (self::Roles_Nuevos as $Rol_Anterior => $Rol_Nuevo) {
            DB::table('usuarios')->where('rol', $Rol_Anterior)->update(['rol' => $Rol_Nuevo]);
        }
        Schema::table('usuarios', function (Blueprint $Tabla) {
            $Tabla->string('rol', 30)->default('usuario_institucional')->change();
        });

        // Marca de cada institución: su portal usa sus colores y su logo (junto al de Blockfinity)
        Schema::table('instituciones', function (Blueprint $Tabla) {
            $Tabla->char('color_primario', 7)->default('#1F3F73');
            $Tabla->char('color_secundario', 7)->default('#5AA9E6');
            $Tabla->string('ruta_logo')->nullable();
        });

        // Certificados de cada persona. Los sube el personal de Blockfinity; el puntaje se da en monedas.
        Schema::create('certificados', function (Blueprint $Tabla) {
            $Tabla->id();
            $Tabla->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $Tabla->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete(); // copia de la del usuario, para filtrar
            $Tabla->string('titulo');
            $Tabla->text('descripcion')->nullable();
            $Tabla->unsignedInteger('puntaje')->default(0);
            $Tabla->string('ruta_archivo')->nullable();
            $Tabla->string('nombre_archivo')->nullable();
            $Tabla->char('codigo_verificacion', 64)->nullable();
            $Tabla->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificados');
        Schema::table('instituciones', fn (Blueprint $Tabla) => $Tabla->dropColumn(['color_primario', 'color_secundario', 'ruta_logo']));
        foreach (self::Roles_Nuevos as $Rol_Anterior => $Rol_Nuevo) {
            DB::table('usuarios')->where('rol', $Rol_Nuevo)->update(['rol' => $Rol_Anterior]);
        }
    }
};
