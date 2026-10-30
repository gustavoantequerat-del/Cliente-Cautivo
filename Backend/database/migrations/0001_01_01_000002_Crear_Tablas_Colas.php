<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Tablas internas de Laravel (nombres fijos del framework)
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $Tabla) {
            $Tabla->id();
            $Tabla->string('queue')->index();
            $Tabla->longText('payload');
            $Tabla->unsignedTinyInteger('attempts');
            $Tabla->unsignedInteger('reserved_at')->nullable();
            $Tabla->unsignedInteger('available_at');
            $Tabla->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $Tabla) {
            $Tabla->string('id')->primary();
            $Tabla->string('name');
            $Tabla->integer('total_jobs');
            $Tabla->integer('pending_jobs');
            $Tabla->integer('failed_jobs');
            $Tabla->longText('failed_job_ids');
            $Tabla->mediumText('options')->nullable();
            $Tabla->integer('cancelled_at')->nullable();
            $Tabla->integer('created_at');
            $Tabla->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $Tabla) {
            $Tabla->id();
            $Tabla->string('uuid')->unique();
            $Tabla->text('connection');
            $Tabla->text('queue');
            $Tabla->longText('payload');
            $Tabla->longText('exception');
            $Tabla->timestamp('failed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
    }
};
