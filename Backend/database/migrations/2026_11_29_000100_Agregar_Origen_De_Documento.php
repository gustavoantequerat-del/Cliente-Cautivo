<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** El PDF de Research, webinars y píldoras puede ser un archivo subido o un enlace de Google Drive: lo elige el CMS */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['reportes_research', 'videos'] as $Tabla_Nombre) {
            Schema::table($Tabla_Nombre, fn (Blueprint $Tabla) => $Tabla->string('origen_documento', 10)->default('archivo'));
            // Los que solo tienen enlace de Drive quedan como "drive"
            DB::table($Tabla_Nombre)->whereNull('ruta_archivo')->whereNotNull('enlace_externo')->update(['origen_documento' => 'drive']);
        }
    }

    public function down(): void
    {
        foreach (['reportes_research', 'videos'] as $Tabla_Nombre) {
            Schema::table($Tabla_Nombre, fn (Blueprint $Tabla) => $Tabla->dropColumn('origen_documento'));
        }
    }
};
