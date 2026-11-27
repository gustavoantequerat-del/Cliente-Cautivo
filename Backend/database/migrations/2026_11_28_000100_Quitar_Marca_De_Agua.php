<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Los PDF ahora se leen con el visor de Google Drive: el ajuste de marca de agua ya no se usa */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('configuraciones')->where('clave', 'texto_marca_agua')->delete();
    }

    public function down(): void
    {
        DB::table('configuraciones')->insertOrIgnore([
            'clave' => 'texto_marca_agua', 'etiqueta' => 'Texto de la marca de agua de los reportes',
            'valor' => 'Confidencial · {institucion} · {usuario} · {fecha}', 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
};
