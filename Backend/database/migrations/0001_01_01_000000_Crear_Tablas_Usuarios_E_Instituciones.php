<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Instituciones cliente (bancos, entidades). Se crea antes que usuarios por la llave foránea.
        Schema::create('instituciones', function (Blueprint $Tabla) {
            $Tabla->id();
            $Tabla->string('nombre');
            $Tabla->string('identificador')->unique();          // slug
            $Tabla->string('razon_social')->nullable();
            $Tabla->string('nit', 40)->nullable();
            $Tabla->string('correo_facturacion')->nullable();
            $Tabla->string('direccion_facturacion')->nullable();
            $Tabla->unsignedInteger('puntos_alianza')->default(0);
            $Tabla->foreignId('ejecutivo_cuenta_id')->nullable(); // llave a usuarios, se agrega abajo
            $Tabla->boolean('esta_activa')->default(true);
            $Tabla->timestamps();
        });

        // email, password y remember_token conservan el nombre que exige el login de Laravel
        Schema::create('usuarios', function (Blueprint $Tabla) {
            $Tabla->id();
            $Tabla->foreignId('institucion_id')->nullable()->constrained('instituciones')->nullOnDelete();
            $Tabla->string('nombre');
            $Tabla->string('email')->unique();
            $Tabla->string('password');
            // Blockfinity: staff (todo el CMS) · ejecutivo_cuenta (solo sus instituciones)
            // Institución: cliente_institucional (gestiona su institución) · cliente_basico (formación y sus proyectos)
            $Tabla->string('rol', 30)->default('cliente_basico')->index();
            $Tabla->string('area')->nullable();           // Gerencia de Riesgos, Cumplimiento...
            $Tabla->string('cargo')->nullable();          // Cargo visible (ej. Client Success)
            $Tabla->string('telefono', 40)->nullable();
            $Tabla->string('enlace_agenda')->nullable();  // Calendly / Google Appointment de la ejecutiva
            $Tabla->string('numero_credencial', 20)->nullable()->unique();
            $Tabla->uuid('codigo_credencial')->nullable()->unique();
            $Tabla->timestamp('fecha_emision_credencial')->nullable();
            $Tabla->boolean('esta_activo')->default(true);
            $Tabla->timestamp('fecha_ultimo_ingreso')->nullable();
            $Tabla->rememberToken();
            $Tabla->timestamps();
        });

        Schema::table('instituciones', function (Blueprint $Tabla) {
            $Tabla->foreign('ejecutivo_cuenta_id')->references('id')->on('usuarios')->nullOnDelete();
        });

        // Tablas internas de Laravel (nombres fijos del framework)
        Schema::create('password_reset_tokens', function (Blueprint $Tabla) {
            $Tabla->string('email')->primary();
            $Tabla->string('token');
            $Tabla->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $Tabla) {
            $Tabla->string('id')->primary();
            $Tabla->foreignId('user_id')->nullable()->index();
            $Tabla->string('ip_address', 45)->nullable();
            $Tabla->text('user_agent')->nullable();
            $Tabla->longText('payload');
            $Tabla->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::table('instituciones', function (Blueprint $Tabla) {
            $Tabla->dropForeign(['ejecutivo_cuenta_id']);
        });
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('instituciones');
    }
};
