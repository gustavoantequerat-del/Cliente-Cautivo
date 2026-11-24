<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Webinars y píldoras también pueden ser un PDF (subido o enlace de Drive) en lugar de un video
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $Tabla) {
            $Tabla->string('tipo_contenido', 10)->default('video'); // video | pdf
            $Tabla->string('ruta_archivo')->nullable();
            $Tabla->string('nombre_archivo')->nullable();
            $Tabla->string('enlace_externo', 500)->nullable();      // enlace de Google Drive del PDF
        });
    }

    public function down(): void
    {
        Schema::table('videos', fn (Blueprint $Tabla) => $Tabla->dropColumn(['tipo_contenido', 'ruta_archivo', 'nombre_archivo', 'enlace_externo']));
    }
};
