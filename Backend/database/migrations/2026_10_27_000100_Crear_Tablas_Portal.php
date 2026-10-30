<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Unidades de negocio de Blockfinity (BCS, NEXUM, RESEARCH, LABS, BUILD, W3M)
        Schema::create('unidades_negocio', function (Blueprint $Tabla) {
            $Tabla->id();
            $Tabla->string('codigo', 20)->unique();
            $Tabla->string('nombre');
            $Tabla->text('descripcion')->nullable();
            $Tabla->unsignedSmallInteger('orden')->default(0);
            $Tabla->timestamps();
        });

        // Qué unidades tiene contratadas cada institución
        Schema::create('instituciones_unidades', function (Blueprint $Tabla) {
            $Tabla->id();
            $Tabla->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $Tabla->foreignId('unidad_negocio_id')->constrained('unidades_negocio')->cascadeOnDelete();
            $Tabla->date('activa_desde')->nullable();
            $Tabla->string('nota')->nullable(); // "50 licencias", "suscripción anual"
            $Tabla->timestamps();
            $Tabla->unique(['institucion_id', 'unidad_negocio_id']);
        });

        Schema::create('proyectos', function (Blueprint $Tabla) {
            $Tabla->id();
            $Tabla->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $Tabla->foreignId('unidad_negocio_id')->nullable()->constrained('unidades_negocio')->nullOnDelete();
            $Tabla->string('nombre');
            $Tabla->text('descripcion')->nullable();
            $Tabla->string('estado', 20)->default('activo')->index(); // activo | cerrado
            $Tabla->unsignedTinyInteger('avance')->default(0);        // 0-100
            $Tabla->string('proximo_hito')->nullable();
            $Tabla->date('fecha_inicio')->nullable();
            $Tabla->date('fecha_fin')->nullable();
            $Tabla->timestamps();
        });

        Schema::create('fases_proyecto', function (Blueprint $Tabla) {
            $Tabla->id();
            $Tabla->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $Tabla->unsignedSmallInteger('posicion')->default(1);
            $Tabla->string('nombre');
            $Tabla->string('estado', 20)->default('pendiente'); // completada | en_curso | pendiente
            $Tabla->date('fecha_limite')->nullable();
            $Tabla->timestamps();
        });

        Schema::create('bitacora_proyectos', function (Blueprint $Tabla) {
            $Tabla->id();
            $Tabla->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $Tabla->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $Tabla->text('detalle');
            $Tabla->date('fecha');
            $Tabla->timestamps();
        });

        // Entregables: archivo local (pequeño) o enlace externo (Drive). Siempre con huella SHA-256.
        Schema::create('entregables', function (Blueprint $Tabla) {
            $Tabla->id();
            $Tabla->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $Tabla->foreignId('proyecto_id')->nullable()->constrained('proyectos')->nullOnDelete();
            $Tabla->foreignId('unidad_negocio_id')->nullable()->constrained('unidades_negocio')->nullOnDelete();
            $Tabla->string('titulo');
            $Tabla->string('tipo', 30)->default('informe'); // informe | presentacion | auditoria | plantilla | otro
            $Tabla->string('version', 20)->nullable();
            $Tabla->string('etiqueta_fase', 20)->nullable(); // F1, F2...
            $Tabla->string('ruta_archivo')->nullable();
            $Tabla->string('nombre_archivo')->nullable();
            $Tabla->unsignedBigInteger('tamano_archivo')->nullable();
            $Tabla->string('enlace_externo', 500)->nullable();
            $Tabla->char('huella_sha256', 64)->nullable()->index();
            $Tabla->string('estado', 30)->default('publicado'); // borrador | pendiente_aprobacion | aprobado | publicado
            $Tabla->foreignId('aprobado_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $Tabla->timestamp('fecha_aprobacion')->nullable();
            $Tabla->string('ip_aprobacion', 45)->nullable();
            $Tabla->timestamp('fecha_publicacion')->nullable();
            $Tabla->timestamps();
        });

        Schema::create('reportes_research', function (Blueprint $Tabla) {
            $Tabla->id();
            // vacío = visible para todas las instituciones con RESEARCH; si no, exclusivo
            $Tabla->foreignId('institucion_id')->nullable()->constrained('instituciones')->nullOnDelete();
            $Tabla->string('titulo');
            $Tabla->text('resumen')->nullable();
            $Tabla->string('tema', 60)->index();
            $Tabla->string('autor')->nullable();
            $Tabla->unsignedSmallInteger('minutos_lectura')->nullable();
            $Tabla->string('ruta_archivo')->nullable();
            $Tabla->string('nombre_archivo')->nullable();
            $Tabla->string('enlace_externo', 500)->nullable();
            $Tabla->boolean('es_destacado')->default(false);
            $Tabla->boolean('esta_publicado')->default(true);
            $Tabla->date('fecha_publicacion')->nullable();
            $Tabla->timestamps();
        });

        // Academia: webinars en vivo, grabados y píldoras. Los videos viven fuera (BYOS).
        Schema::create('videos', function (Blueprint $Tabla) {
            $Tabla->id();
            $Tabla->foreignId('institucion_id')->nullable()->constrained('instituciones')->nullOnDelete();
            $Tabla->string('formato', 20)->default('grabado'); // en_vivo | grabado | pildora
            $Tabla->string('titulo');
            $Tabla->text('descripcion')->nullable();
            $Tabla->string('tema', 60)->nullable();
            $Tabla->string('expositor')->nullable();
            $Tabla->unsignedSmallInteger('duracion_minutos')->nullable();
            $Tabla->string('proveedor', 20)->nullable();       // drive | youtube | vimeo
            $Tabla->string('id_externo', 120)->nullable();
            $Tabla->string('hash_externo', 60)->nullable();    // vimeo no listado (?h=)
            $Tabla->string('enlace_original', 500)->nullable(); // enlace tal como lo pegó el staff
            $Tabla->string('enlace_miniatura', 500)->nullable();
            $Tabla->dateTime('fecha_programada')->nullable();  // para "en vivo"
            $Tabla->string('enlace_sala', 500)->nullable();    // Meet / Zoom / Teams
            $Tabla->unsignedSmallInteger('puntos')->default(10);
            $Tabla->boolean('esta_publicado')->default(true);
            $Tabla->timestamps();
        });

        // Progreso declarado (los iframes de Drive no permiten medir la reproducción)
        Schema::create('progreso_videos', function (Blueprint $Tabla) {
            $Tabla->id();
            $Tabla->foreignId('video_id')->constrained('videos')->cascadeOnDelete();
            $Tabla->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $Tabla->timestamp('fecha_inscripcion')->nullable(); // inscrito a un en vivo
            $Tabla->timestamp('fecha_completado')->nullable();
            $Tabla->timestamps();
            $Tabla->unique(['video_id', 'usuario_id']);
        });

        Schema::create('facturas', function (Blueprint $Tabla) {
            $Tabla->id();
            $Tabla->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $Tabla->string('numero', 40);
            $Tabla->string('concepto');
            $Tabla->date('fecha_emision');
            $Tabla->date('fecha_vencimiento')->nullable();
            $Tabla->decimal('monto', 14, 2);
            $Tabla->string('moneda', 3)->default('BOB');
            $Tabla->string('estado', 20)->default('pendiente'); // pendiente | pagada | anulada
            $Tabla->date('fecha_pago')->nullable();
            $Tabla->string('ruta_pdf')->nullable();
            $Tabla->string('ruta_xml')->nullable();
            $Tabla->timestamps();
        });

        // Contratos y presupuestos firmados
        Schema::create('contratos', function (Blueprint $Tabla) {
            $Tabla->id();
            $Tabla->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $Tabla->foreignId('unidad_negocio_id')->nullable()->constrained('unidades_negocio')->nullOnDelete();
            $Tabla->string('tipo', 20)->default('contrato'); // contrato | presupuesto
            $Tabla->string('titulo');
            $Tabla->date('fecha_firma')->nullable();
            $Tabla->date('fecha_vencimiento')->nullable();
            $Tabla->decimal('monto', 14, 2)->nullable();
            $Tabla->string('ruta_archivo')->nullable();
            $Tabla->string('nombre_archivo')->nullable();
            $Tabla->string('enlace_externo', 500)->nullable();
            $Tabla->char('huella_sha256', 64)->nullable();
            $Tabla->timestamps();
        });

        // Bolsa de horas: libro de movimientos (+ abono, - consumo)
        Schema::create('movimientos_horas', function (Blueprint $Tabla) {
            $Tabla->id();
            $Tabla->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $Tabla->foreignId('solicitud_horas_id')->nullable();
            $Tabla->decimal('horas', 6, 2);
            $Tabla->string('descripcion');
            $Tabla->date('fecha_vencimiento')->nullable();
            $Tabla->foreignId('creado_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $Tabla->timestamps();
        });

        Schema::create('solicitudes_horas', function (Blueprint $Tabla) {
            $Tabla->id();
            $Tabla->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $Tabla->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $Tabla->string('modalidad', 20)->default('consulta'); // consulta | emergencia
            $Tabla->string('especialidad', 60);
            $Tabla->decimal('horas_estimadas', 5, 2);
            $Tabla->text('descripcion');
            $Tabla->string('estado', 20)->default('pendiente'); // pendiente | en_atencion | atendida | rechazada
            $Tabla->decimal('horas_cobradas', 5, 2)->nullable();
            $Tabla->text('respuesta')->nullable();
            $Tabla->foreignId('atendido_por')->nullable()->constrained('usuarios')->nullOnDelete();
            $Tabla->timestamp('fecha_resolucion')->nullable();
            $Tabla->timestamps();
        });

        // Auditoría: inicios de sesión, descargas, aprobaciones, cambios en el CMS
        Schema::create('registros_auditoria', function (Blueprint $Tabla) {
            $Tabla->id();
            $Tabla->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $Tabla->foreignId('institucion_id')->nullable()->constrained('instituciones')->nullOnDelete();
            $Tabla->string('accion', 60)->index();
            $Tabla->string('tipo_sujeto', 60)->nullable();
            $Tabla->unsignedBigInteger('sujeto_id')->nullable();
            $Tabla->string('descripcion')->nullable();
            $Tabla->json('datos_extra')->nullable();
            $Tabla->string('ip', 45)->nullable();
            $Tabla->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        foreach (['registros_auditoria', 'solicitudes_horas', 'movimientos_horas', 'contratos', 'facturas', 'progreso_videos',
            'videos', 'reportes_research', 'entregables', 'bitacora_proyectos', 'fases_proyecto', 'proyectos',
            'instituciones_unidades', 'unidades_negocio'] as $Nombre_Tabla) {
            Schema::dropIfExists($Nombre_Tabla);
        }
    }
};
