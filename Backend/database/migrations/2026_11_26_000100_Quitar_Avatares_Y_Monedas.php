<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Se retiran los avatares y las monedas: el único incentivo son los 3 niveles por servicios contratados.
 * El puntaje de los certificados se queda como calificación de la persona.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('movimientos_monedas');
        Schema::dropIfExists('inventario_avatar');
        Schema::dropIfExists('items_avatar');
        Schema::table('videos', fn (Blueprint $Tabla) => $Tabla->dropColumn('puntos'));
        Schema::table('reportes_research', fn (Blueprint $Tabla) => $Tabla->dropColumn('puntos'));
    }

    public function down(): void
    {
        Schema::table('videos', fn (Blueprint $Tabla) => $Tabla->unsignedSmallInteger('puntos')->default(10));
        Schema::table('reportes_research', fn (Blueprint $Tabla) => $Tabla->unsignedSmallInteger('puntos')->default(10));
    }
};
