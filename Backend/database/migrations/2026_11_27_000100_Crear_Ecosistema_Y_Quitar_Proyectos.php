<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ecosistema Blockfinity: las unidades de negocio pasan a ser los productos que ve el cliente
 * (con subtítulo, imagen, página o video) y se pueden solicitar desde el portal.
 * Los proyectos dejan de existir; los entregables quedan ligados al producto.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unidades_negocio', function (Blueprint $Tabla) {
            $Tabla->string('subtitulo')->nullable();
            $Tabla->string('ruta_imagen')->nullable();
            $Tabla->string('enlace_pagina', 500)->nullable();  // landing del producto: se muestra en un iframe
            $Tabla->string('enlace_video', 500)->nullable();   // si no hay página: video explicativo (Drive, YouTube, Vimeo)
            $Tabla->boolean('esta_publicado')->default(true);
        });

        // Pedidos de contratación que hace el administrador delegado desde el portal
        Schema::create('solicitudes_productos', function (Blueprint $Tabla) {
            $Tabla->id();
            $Tabla->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $Tabla->foreignId('unidad_negocio_id')->constrained('unidades_negocio')->cascadeOnDelete();
            $Tabla->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $Tabla->text('comentario')->nullable();
            $Tabla->string('estado', 20)->default('pendiente')->index(); // pendiente | habilitado | descartado
            $Tabla->timestamps();
        });

        DB::table('configuraciones')->insertOrIgnore([
            'clave' => 'correo_contacto', 'etiqueta' => 'Correo que recibe las solicitudes de productos',
            'valor' => null, 'created_at' => now(), 'updated_at' => now(),
        ]);

        Schema::table('entregables', fn (Blueprint $Tabla) => $Tabla->dropConstrainedForeignId('proyecto_id'));
        Schema::dropIfExists('bitacora_proyectos');
        Schema::dropIfExists('fases_proyecto');
        Schema::dropIfExists('proyectos_participantes');
        Schema::dropIfExists('proyectos');
    }

    public function down(): void
    {
        Schema::create('proyectos', function (Blueprint $Tabla) {
            $Tabla->id();
            $Tabla->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $Tabla->foreignId('unidad_negocio_id')->nullable()->constrained('unidades_negocio')->nullOnDelete();
            $Tabla->string('nombre');
            $Tabla->text('descripcion')->nullable();
            $Tabla->string('estado', 20)->default('activo');
            $Tabla->unsignedTinyInteger('avance')->default(0);
            $Tabla->string('proximo_hito')->nullable();
            $Tabla->date('fecha_inicio')->nullable();
            $Tabla->date('fecha_fin')->nullable();
            $Tabla->timestamps();
        });
        Schema::table('entregables', fn (Blueprint $Tabla) => $Tabla->foreignId('proyecto_id')->nullable()->constrained('proyectos')->nullOnDelete());
        DB::table('configuraciones')->where('clave', 'correo_contacto')->delete();
        Schema::dropIfExists('solicitudes_productos');
        Schema::table('unidades_negocio', fn (Blueprint $Tabla) => $Tabla->dropColumn(['subtitulo', 'ruta_imagen', 'enlace_pagina', 'enlace_video', 'esta_publicado']));
    }
};
