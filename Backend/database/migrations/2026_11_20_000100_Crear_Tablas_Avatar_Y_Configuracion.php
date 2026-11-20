<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // El nivel de alianza ya no depende de puntos sino de los servicios contratados
        Schema::table('instituciones', function (Blueprint $Tabla) {
            $Tabla->dropColumn('puntos_alianza');
        });

        // Leer un reporte también da monedas
        Schema::table('reportes_research', function (Blueprint $Tabla) {
            $Tabla->unsignedSmallInteger('puntos')->default(10);
        });

        // Ajustes generales editables desde el CMS (ej. texto de la marca de agua)
        Schema::create('configuraciones', function (Blueprint $Tabla) {
            $Tabla->id();
            $Tabla->string('clave', 60)->unique();
            $Tabla->string('etiqueta');
            $Tabla->text('valor')->nullable();
            $Tabla->timestamps();
        });
        DB::table('configuraciones')->insert([
            'clave' => 'texto_marca_agua', 'etiqueta' => 'Texto de la marca de agua de los reportes',
            'valor' => 'Confidencial · {institucion} · {usuario} · {fecha}', 'created_at' => now(), 'updated_at' => now(),
        ]);

        // Forja: objetos para el avatar. estilo = "tipo.variante" (ej. camisa.manga_larga)
        Schema::create('items_avatar', function (Blueprint $Tabla) {
            $Tabla->id();
            $Tabla->string('nombre');
            $Tabla->string('tipo', 20)->index();   // camisa | pantalon | zapato | gorra | extra | accesorio | pose
            $Tabla->string('estilo', 40);
            $Tabla->char('color_principal', 7)->default('#3B5B8C');
            $Tabla->char('color_secundario', 7)->default('#FFFFFF');
            $Tabla->unsignedInteger('precio')->default(50);
            $Tabla->text('detalle')->nullable();
            $Tabla->boolean('esta_publicado')->default(true);
            $Tabla->timestamps();
        });

        // Objetos que compró cada usuario y si los lleva puestos
        Schema::create('inventario_avatar', function (Blueprint $Tabla) {
            $Tabla->id();
            $Tabla->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $Tabla->foreignId('item_avatar_id')->constrained('items_avatar')->cascadeOnDelete();
            $Tabla->boolean('esta_equipado')->default(false);
            $Tabla->timestamps();
            $Tabla->unique(['usuario_id', 'item_avatar_id']);
        });

        // Libro de monedas: + al completar videos y leer reportes, - al comprar objetos.
        // El índice único impide ganar dos veces por el mismo video o reporte.
        Schema::create('movimientos_monedas', function (Blueprint $Tabla) {
            $Tabla->id();
            $Tabla->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $Tabla->integer('cantidad');
            $Tabla->string('motivo');
            $Tabla->string('tipo_origen', 20);     // video | research | item | bono
            $Tabla->unsignedBigInteger('origen_id');
            $Tabla->timestamp('created_at')->useCurrent();
            $Tabla->unique(['usuario_id', 'tipo_origen', 'origen_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_monedas');
        Schema::dropIfExists('inventario_avatar');
        Schema::dropIfExists('items_avatar');
        Schema::dropIfExists('configuraciones');
        Schema::table('reportes_research', fn (Blueprint $Tabla) => $Tabla->dropColumn('puntos'));
        Schema::table('instituciones', fn (Blueprint $Tabla) => $Tabla->unsignedInteger('puntos_alianza')->default(0));
    }
};
