<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Tablas internas de Laravel (nombres fijos del framework)
    public function up(): void
    {
        Schema::create('cache', function (Blueprint $Tabla) {
            $Tabla->string('key')->primary();
            $Tabla->mediumText('value');
            $Tabla->integer('expiration')->index();
        });

        Schema::create('cache_locks', function (Blueprint $Tabla) {
            $Tabla->string('key')->primary();
            $Tabla->string('owner');
            $Tabla->integer('expiration')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};
