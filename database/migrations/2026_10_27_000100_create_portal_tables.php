<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Unidades de negocio de Blockfinity (BCS, NEXUM, RESEARCH, LABS, BUILD, W3M)
        Schema::create('business_units', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        // Qué unidades tiene contratadas cada institución
        Schema::create('institution_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_unit_id')->constrained()->cascadeOnDelete();
            $table->date('active_since')->nullable();
            $table->string('note')->nullable(); // "50 licencias", "suscripción anual"
            $table->timestamps();
            $table->unique(['institution_id', 'business_unit_id']);
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_unit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active')->index(); // active | closed
            $table->unsignedTinyInteger('progress')->default(0);      // 0-100
            $table->string('next_milestone')->nullable();
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->timestamps();
        });

        Schema::create('project_phases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position')->default(1);
            $table->string('name');
            $table->string('status', 20)->default('pending'); // done | current | pending
            $table->date('due_on')->nullable();
            $table->timestamps();
        });

        // Bitácora del proyecto
        Schema::create('project_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body');
            $table->date('happened_on');
            $table->timestamps();
        });

        // Entregables: archivo local (pequeño) o enlace externo (Drive). Siempre con huella SHA-256.
        Schema::create('deliverables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('business_unit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('kind', 30)->default('informe'); // informe | presentacion | auditoria | plantilla | otro
            $table->string('version', 20)->nullable();
            $table->string('phase_label', 20)->nullable();  // F1, F2...
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('external_url', 500)->nullable();
            $table->char('sha256', 64)->nullable()->index();
            $table->string('status', 20)->default('published'); // draft | pending_approval | approved | published
            $table->boolean('requires_approval')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('approval_ip', 45)->nullable();
            $table->string('anchor_tx', 120)->nullable(); // fase 2: sello Stamping.io
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('research_reports', function (Blueprint $table) {
            $table->id();
            // null = visible para todas las instituciones con RESEARCH activo; si no, exclusivo
            $table->foreignId('institution_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('summary')->nullable();
            $table->string('topic', 60)->index();
            $table->string('author')->nullable();
            $table->unsignedSmallInteger('reading_minutes')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('external_url', 500)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->date('published_on')->nullable();
            $table->timestamps();
        });

        // Academia: webinars en vivo, grabados y píldoras. Los videos viven fuera (BYOS).
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kind', 20)->default('recorded'); // live | recorded | pill
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('topic', 60)->nullable();
            $table->string('speaker')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->string('provider', 20)->nullable();      // gdrive | youtube | vimeo
            $table->string('external_id', 120)->nullable();
            $table->string('external_hash', 60)->nullable(); // vimeo no listado (?h=)
            $table->string('source_url', 500)->nullable();   // enlace tal como lo pegó el admin
            $table->string('thumbnail_url', 500)->nullable();
            $table->dateTime('scheduled_at')->nullable();    // para "en vivo"
            $table->string('live_url', 500)->nullable();     // Meet / Zoom / Teams
            $table->unsignedSmallInteger('points')->default(10);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        // Progreso declarado (los iframes de Drive no permiten medir reproducción)
        Schema::create('video_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('registered_at')->nullable(); // inscrito a un en vivo
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['video_id', 'user_id']);
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('number', 40);
            $table->string('concept');
            $table->date('issued_on');
            $table->date('due_on')->nullable();
            $table->decimal('amount', 14, 2);
            $table->string('currency', 3)->default('BOB');
            $table->string('status', 20)->default('pending'); // pending | paid | void
            $table->date('paid_on')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('xml_path')->nullable();
            $table->timestamps();
        });

        // Contratos y presupuestos firmados
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_unit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kind', 20)->default('contract'); // contract | quote
            $table->string('title');
            $table->date('signed_on')->nullable();
            $table->date('expires_on')->nullable();
            $table->decimal('amount', 14, 2)->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('external_url', 500)->nullable();
            $table->char('sha256', 64)->nullable();
            $table->timestamps();
        });

        // Bolsa de horas: libro de movimientos (+ abono, - consumo)
        Schema::create('hour_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hour_request_id')->nullable();
            $table->decimal('hours', 6, 2);
            $table->string('description');
            $table->date('expires_on')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('hour_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('mode', 20)->default('consulta'); // consulta | emergencia
            $table->string('specialty', 60);
            $table->decimal('estimated_hours', 5, 2);
            $table->text('description');
            $table->string('status', 20)->default('pending'); // pending | in_progress | done | rejected
            $table->decimal('charged_hours', 5, 2)->nullable();
            $table->text('response')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        // Auditoría: inicios de sesión, descargas, aprobaciones, cambios en CMS
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('institution_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 60)->index();
            $table->string('subject_type', 60)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('description')->nullable();
            $table->json('meta')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        foreach (['audit_logs', 'hour_requests', 'hour_movements', 'contracts', 'invoices', 'video_progress',
            'videos', 'research_reports', 'deliverables', 'project_updates', 'project_phases', 'projects',
            'institution_unit', 'business_units'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
